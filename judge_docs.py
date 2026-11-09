#!/usr/bin/env python3
"""Check the WordPress APIs a generated plugin uses against current documentation.

    judge_docs.py <run-dir | plugin-dir> [--json]

Deterministic, no model. For every `@wordpress/*` import in the plugin's
JavaScript and every WordPress function its PHP calls, it asks the current docs
(through the helper script's fetching and cache): does this exist, is it
deprecated, is it experimental. The result is arm-independent, so using it as
context for the judge does not break blind scoring.

Statuses: ok, deprecated, experimental, not_found, unverified, unchecked, unknown_package.

A `not_found` is a lead, not a verdict: the docs may simply not list a symbol
(types, constants). The judge is told to treat it that way. `unverified` means the
docs page does not list its exports, so existence cannot be checked; `unchecked`
means the fetch failed for a reason other than 404 (a network error must never
become a false "not found").

Limits: only ES `import { ... } from '@wordpress/...'` is read (not `wp.data.x`
globals); PHP classes and hook names are not checked.
"""
import contextlib
import importlib.util
import io
import json
import re
import subprocess
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
_spec = importlib.util.spec_from_file_location("wp_docs", HERE / "agents" / "wp-docs.py")
wp = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(wp)

COMPONENTS_INDEX = "https://raw.githubusercontent.com/WordPress/gutenberg/trunk/packages/components/src/index.ts"
EXPERIMENTAL_PREFIXES = ("__experimental", "__unstable")
# PHP language constructs and keywords that look like function calls.
PHP_KEYWORDS = set("""if else elseif while for foreach switch case array isset empty list echo print return catch
try unset exit die include include_once require require_once fn match function new and or xor use namespace class
interface trait extends implements static self parent declare""".split())
# Tests use the WordPress test suite (tests_add_filter and friends), which is not core API.
SKIP_DIRS = {"node_modules", "vendor", "build", "tests", "test", "__tests__"}


def fetch_page(url):
    """(text, status). wp.fetch exits on failure; here a failure returns (None, HTTP status)."""
    out = io.StringIO()
    try:
        with contextlib.redirect_stdout(out):
            return wp.fetch(url), 200
    except SystemExit:
        try:
            return None, int(json.loads(out.getvalue()).get("status", 0))
        except (ValueError, TypeError):
            return None, 0


def quiet_fetch(url):
    """The page text, or None if the fetch failed."""
    return fetch_page(url)[0]


def plugin_files(root, suffixes):
    for path in sorted(Path(root).rglob("*")):
        if path.is_file() and path.suffix in suffixes and not SKIP_DIRS & set(path.parts):
            yield path


# ---------- JavaScript ----------

def js_imports(root):
    """Sorted unique (package, imported name) pairs. `X as Y` counts as X."""
    found = set()
    for path in plugin_files(root, {".js", ".jsx", ".ts", ".tsx"}):
        text = path.read_text(errors="replace")
        for names, package in re.findall(r"import\s*\{([^}]*)\}\s*from\s*['\"](@wordpress/[\w-]+)['\"]", text):
            for item in names.split(","):
                name = re.sub(r"^\s*type\s+", "", item).split(" as ")[0].strip()
                if name:
                    found.add((package, name))
    return sorted(found)


_manifest = None


def manifest():
    global _manifest
    if _manifest is None:
        _manifest = json.loads(quiet_fetch(wp.MANIFEST_URL))
    return _manifest


def page_url(entry):
    by_slug = {e["slug"]: e for e in manifest()}
    chain = []
    while entry:
        chain.append(entry["slug"])
        entry = by_slug.get(entry["parent"]) if entry["parent"] else None
    return wp.HANDBOOK_BASE + "/".join(reversed(chain)) + "/"


# How the docs say it: "This function has been deprecated since 4.0.0", "This component is
# deprecated since WordPress 7.3", "> **Deprecated** Use `register` instead". The subject must
# be the API itself: sentences about some parameter values being deprecated ("Some `$show`
# values are deprecated", "'yes' and 'no' ... is deprecated") and a `$deprecated` parameter's
# bare "Deprecated." are not deprecations of the API and must not match.
DEPRECATED = re.compile(
    r"\bThis (?:function|method|component|class|hook|filter|action|API|package|feature)\b[^.\n]{0,40}\bdeprecated\b"
    r"|\*\*Deprecated\*\*",
    re.I,
)


def deprecation_in(lines):
    """First line that says the API is deprecated, trimmed, or ''."""
    for line in lines:
        if DEPRECATED.search(line):
            return re.sub(r"\s+", " ", line).strip()[:200]
    return ""


def check_js(package, name):
    """Return {status, url, note} for one imported name."""
    base = name
    experimental = name.startswith(EXPERIMENTAL_PREFIXES)
    for prefix in EXPERIMENTAL_PREFIXES:
        if base.startswith(prefix):
            base = base[len(prefix):]

    def done(status, url="", note=""):
        if experimental and status == "ok":
            status, note = "experimental", "unstable API (" + name + ")"
        return {"status": status, "url": url, "note": note}

    if package == "@wordpress/components":
        pages = [e for e in manifest() if e["title"].lower() == base.lower() and e["parent"] == "components"]
        if pages:
            url = page_url(pages[0])
            text, status = fetch_page(url)
            if text is None:
                return {"status": "unchecked", "url": url, "note": f"fetch failed ({status})"}
            note = deprecation_in(text.splitlines()[:45])
            return done("deprecated", url, note) if note else done("ok", url)
        # No page of its own (PanelBody, hooks): fall back to the package's export list.
        index, status = fetch_page(COMPONENTS_INDEX)
        if index is None:
            return {"status": "unchecked", "url": "", "note": f"fetch failed ({status})"}
        if re.search(r"\b" + re.escape(base) + r"\b", index):
            return done("ok", "", "exported; no page of its own")
        return done("not_found", "", "no components page and not in the package's exports")

    slug = "packages-" + package.split("/")[1]
    entry = next((e for e in manifest() if e["slug"] == slug), None)
    if entry is None:
        return {"status": "unknown_package", "url": "", "note": "no docs page for " + package}
    url = page_url(entry)
    text, status = fetch_page(url)
    if text is None:
        return {"status": "unchecked", "url": url, "note": f"fetch failed ({status})"}
    lines = text.splitlines()
    headings = wp.parse_headings(lines)
    for number, _, heading in headings:
        if heading == base:
            # The note sits directly under the heading, before the next heading.
            body = []
            for line in lines[number:number + 8]:
                if line.startswith("#"):
                    break
                body.append(line)
            note = deprecation_in(body)
            return done("deprecated", url, note) if note else done("ok", url)
    # No heading of its own (icons, `store` constants). A name that appears in the
    # page text is documented somewhere; an invented name would not appear at all.
    if re.search(r"\b" + re.escape(base) + r"\b", "\n".join(lines)):
        return done("ok", url, "mentioned on the package page; no heading of its own")
    if sum(1 for _, level, _ in headings if level == 3) < 3:
        # The page is a README, not an API list (interactivity, icons, notices), so a
        # missing name proves nothing.
        return {"status": "unverified", "url": url, "note": "the docs page does not list this package's exports"}
    return done("not_found", url, "not on the package page for " + package)


# ---------- PHP ----------

def php_internal_functions():
    out = subprocess.run(["php", "-r", 'echo json_encode(get_defined_functions()["internal"]);'],
                         capture_output=True, text=True).stdout
    return set(json.loads(out)) if out else set()


def strip_php_noise(text):
    """Remove comments and string contents so words inside them are not read as calls."""
    text = re.sub(r"/\*.*?\*/", " ", text, flags=re.S)
    text = re.sub(r"(^|\s)//[^\n]*", " ", text)
    text = re.sub(r"(^|\s)#[^\n]*", " ", text)
    text = re.sub(r"'(?:\\.|[^'\\])*'", "''", text)
    return re.sub(r'"(?:\\.|[^"\\])*"', '""', text)


def php_candidates(root):
    """Functions the plugin calls that it did not define and PHP does not provide."""
    internal, defined, called = php_internal_functions(), set(), set()
    for path in plugin_files(root, {".php"}):
        text = strip_php_noise(path.read_text(errors="replace"))
        defined |= set(re.findall(r"function\s+&?\s*([A-Za-z_]\w*)\s*\(", text))
        called |= {m.group(1) for m in re.finditer(r"(?<![>\w$:\\])([a-z_][a-z0-9_]*)\s*\(", text)}
    return sorted(n for n in called if n not in defined and n not in internal and n not in PHP_KEYWORDS)


def check_php(name):
    url = f"https://developer.wordpress.org/reference/functions/{name}/"
    text, status = fetch_page(url)
    if text is None:
        if status == 404:
            return {"status": "not_found", "url": url, "note": ""}
        return {"status": "unchecked", "url": url, "note": f"fetch failed ({status})"}
    # The notice sits in the description, ahead of the parameters and the source.
    note = deprecation_in(text.splitlines()[:60])
    return {"status": "deprecated" if note else "ok", "url": url, "note": note}


# ---------- Report ----------

def plugin_dirs(path):
    """A run dir holds output/<plugin>/; otherwise the path is a plugin itself."""
    out = Path(path) / "output"
    return [p for p in sorted(out.iterdir()) if p.is_dir()] if out.is_dir() else [Path(path)]


def check_plugin(root):
    js = [{"package": p, "name": n, **check_js(p, n)} for p, n in js_imports(root)]
    php = [{"name": n, **check_php(n)} for n in php_candidates(root)]
    summary = {}
    for item in js + php:
        summary[item["status"]] = summary.get(item["status"], 0) + 1
    return {"js": js, "php": php, "summary": summary}


def check_run(path):
    plugins = {p.name: check_plugin(p) for p in plugin_dirs(path)}
    total = {}
    for result in plugins.values():
        for status, count in result["summary"].items():
            total[status] = total.get(status, 0) + count
    return {"plugins": plugins, "summary": total}


def deprecated_names(result):
    """Distinct deprecated APIs used anywhere in the run, as readable names."""
    names = set()
    for plugin in result["plugins"].values():
        for item in plugin["js"] + plugin["php"]:
            if item["status"] == "deprecated":
                names.add(f"{item['package']} {item['name']}" if "package" in item else f"{item['name']}()")
    return sorted(names)


def load_or_check(run_dir):
    """The run's API check, computed once and kept in <run>/api-check.json.

    score.py and judge.py both need it; the cache keeps them consistent and
    saves repeating the lookups."""
    cache = Path(run_dir) / "api-check.json"
    if cache.exists():
        return json.loads(cache.read_text())
    result = check_run(run_dir)
    cache.write_text(json.dumps(result, indent=2) + "\n")
    return result


def to_markdown(result):
    """Compact text for the judge: counts, then only the entries that need attention."""
    lines = []
    for name, plugin in result["plugins"].items():
        checked = len(plugin["js"]) + len(plugin["php"])
        lines.append(f"Plugin `{name}`: {checked} APIs checked "
                     f"({len(plugin['js'])} `@wordpress/*` imports, {len(plugin['php'])} WordPress PHP functions). "
                     + ", ".join(f"{count} {status}" for status, count in sorted(plugin["summary"].items())) + ".")
        for item in plugin["js"] + plugin["php"]:
            if item["status"] == "ok":
                continue
            label = f"{item['package']} `{item['name']}`" if "package" in item else f"PHP `{item['name']}()`"
            detail = f" {item['note']}" if item["note"] else ""
            link = f" ({item['url']})" if item["url"] else ""
            lines.append(f"- {item['status'].upper()}: {label}.{detail}{link}")
    return "\n".join(lines)


if __name__ == "__main__":
    args = [a for a in sys.argv[1:] if a != "--json"]
    if len(args) != 1:
        sys.exit(__doc__)
    outcome = check_run(args[0])
    print(json.dumps(outcome, indent=2) if "--json" in sys.argv else to_markdown(outcome))
