#!/usr/bin/env python3
"""Deterministic helper for the docs-lookup agent.

Resolves WordPress developer documentation URLs and extracts one section of a
page as raw Markdown. The agent decides *what* to look up; this script does
the mechanical work (URL resolution, fetching, heading index, section
extraction) so the result is predictable and cheap.

Usage:
    wp-docs.py resolve <word> [<word> ...]       Find block-editor handbook pages (manifest)
    wp-docs.py reference <function|hook|class> <name>
                                                 Print the reference URL (no fetch)
    wp-docs.py headings <url>                    List a page's headings with line numbers
    wp-docs.py section <url> <heading|line>      Print one section, as JSON
    wp-docs.py links <url>                       List links to pages under a page's URL
    wp-docs.py sitemap <word> [<word> ...]       Search the developer.wordpress.org sitemaps

Rules enforced here, not left to the caller:
    - Only developer.wordpress.org and raw.githubusercontent.com are fetched.
    - Pages are fetched as Markdown (?output_format=md), never HTML.
    - A URL is fetched over the network at most once per 10 minutes; repeats
      are served from a temp-dir cache. Set $WP_DOCS_CACHE_DIR to use a
      different directory (the eval gives every run its own, so runs stay dry).
    - Every lookup is appended to $WP_DOCS_LOG (JSON lines), if set, with
      "cached": true when it came from the cache.

Run ./test_wp_docs.sh (needs network) to check the cache in both modes.

Standard library only. Exit code 0 on success, 1 on any failure (the error is
printed as JSON on stdout so the caller can record it).
"""
import hashlib
import json
import os
import re
import sys
import tempfile
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from datetime import date, datetime
from pathlib import Path

MANIFEST_URL = "https://raw.githubusercontent.com/WordPress/gutenberg/trunk/docs/manifest.json"
HANDBOOK_BASE = "https://developer.wordpress.org/block-editor/"
SITEMAP_INDEX = "https://developer.wordpress.org/sitemap-index-1.xml"
ALLOWED_HOSTS = {"developer.wordpress.org", "raw.githubusercontent.com"}
MAX_SECTION_LINES = 120
CACHE_TTL = 600  # seconds
MD_PARAM = "output_format=md"
# Override with WP_DOCS_CACHE_DIR to isolate a run (the eval gives each run its own).
CACHE_DIR = Path(os.environ.get("WP_DOCS_CACHE_DIR") or Path(tempfile.gettempdir()) / f"wp-docs-{os.getuid()}")


def fail(message, **extra):
    """Print an error as JSON and exit 1."""
    print(json.dumps({"error": message, **extra}))
    sys.exit(1)


def clean_url(url):
    """Return the canonical URL: no output_format parameter, no fragment."""
    parts = urllib.parse.urlsplit(url)
    query = [p for p in parts.query.split("&") if p and not p.startswith("output_format=")]
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, parts.path, "&".join(query), ""))


def log_fetch(url, status, cached):
    """Append one JSON line to $WP_DOCS_LOG, if set. Cache hits are logged too,
    so the log counts every lookup, not only network calls."""
    log = os.environ.get("WP_DOCS_LOG")
    if log:
        with open(log, "a", encoding="utf-8") as handle:
            handle.write(json.dumps({"url": url, "status": status, "cached": cached, "ts": int(time.time())}) + "\n")


def fetch(url):
    """Fetch a URL as text, with the host allowlist, Markdown parameter and cache."""
    canonical = clean_url(url)
    parts = urllib.parse.urlsplit(canonical)
    if parts.scheme != "https" or parts.netloc not in ALLOWED_HOSTS:
        fail("Host not allowed. Use developer.wordpress.org or raw.githubusercontent.com.", url=canonical)

    target = canonical
    if parts.netloc == "developer.wordpress.org" and not parts.path.endswith(".xml"):
        target += ("&" if parts.query else "?") + MD_PARAM

    CACHE_DIR.mkdir(parents=True, exist_ok=True)
    cache_file = CACHE_DIR / hashlib.sha256(target.encode()).hexdigest()
    if cache_file.exists() and time.time() - cache_file.stat().st_mtime < CACHE_TTL:
        log_fetch(target, 200, cached=True)
        return cache_file.read_text(encoding="utf-8")

    status, body = None, ""
    try:
        with urllib.request.urlopen(urllib.request.Request(target, headers={"User-Agent": "wp-docs-helper"}), timeout=30) as resp:
            status, body = resp.status, resp.read().decode("utf-8", errors="replace")
    except urllib.error.HTTPError as err:
        status = err.code
    except (urllib.error.URLError, TimeoutError) as err:
        status = 0
        body = str(err)

    log_fetch(target, status, cached=False)

    if status != 200:
        fail("Fetch failed", url=canonical, status=status)
    cache_file.write_text(body, encoding="utf-8")
    return body


def strip_invisible(text):
    """Remove format characters (the headings carry invisible tag characters)."""
    return "".join(ch for ch in text if unicodedata.category(ch) != "Cf")


def plain(text):
    """Turn Markdown links into their text and drop invisible characters."""
    return strip_invisible(re.sub(r"\[([^\]]*)\]\([^)]*\)", r"\1", text)).strip()


def parse_headings(lines):
    """Return [(line_number, level, text)] for headings outside code fences."""
    headings, fence = [], None  # fence = (character, length) while inside a code block
    for number, line in enumerate(lines, 1):
        # Fences can sit inside list items, e.g. " *     ```php".
        marker = re.match(r"^[\s*+\-]*(`{3,}|~{3,})(.*)$", line)
        if marker:
            char, length, rest = marker.group(1)[0], len(marker.group(1)), marker.group(2).strip()
            if fence is None:
                fence = (char, length)
            elif fence[0] == char and length >= fence[1] and not rest:
                fence = None
        elif fence is None:
            match = re.match(r"^(#{1,6})\s+(.*\S)\s*$", line)
            if match:
                headings.append((number, len(match.group(1)), plain(match.group(2))))
    return headings


def last_modified(text):
    """Read the 'Last modified' header as YYYY-MM-DD, or an empty string."""
    match = re.search(r"^Last modified:\s*(.+)$", text[:600], re.M)
    if match:
        try:
            return datetime.strptime(match.group(1).strip(), "%B %d, %Y").date().isoformat()
        except ValueError:
            pass
    return ""


def words_pattern(words):
    """Match every word, in any order, ignoring case. Hyphens count as spaces."""
    return [re.compile(re.escape(w.replace("-", " ")), re.I) for w in words]


def cmd_resolve(words):
    """Find handbook pages where every word matches the page's title or slug, or a parent's."""
    manifest = json.loads(fetch(MANIFEST_URL))
    by_slug = {entry["slug"]: entry for entry in manifest}
    patterns = words_pattern(words)

    def ancestors(entry):
        """Parent entries, nearest first."""
        found = []
        while entry["parent"] and entry["parent"] in by_slug:
            entry = by_slug[entry["parent"]]
            found.append(entry)
        return found

    def url_for(entry):
        chain = [entry["slug"]] + [a["slug"] for a in ancestors(entry)]
        return HANDBOOK_BASE + "/".join(reversed(chain)) + "/"

    def text_of(entry):
        return entry["title"].replace("-", " ") + " " + entry["slug"].replace("-", " ")

    # A word may match the page itself or any parent: a caller asking for
    # "interactivity api directives store" names the section ("Interactivity
    # API") that the page "Directives and Store" lives under. Pages that match
    # more words themselves rank first.
    matches = []
    for entry in manifest:
        own = text_of(entry)
        parents = " ".join(text_of(a) for a in ancestors(entry))
        if all(p.search(own) or p.search(parents) for p in patterns):
            own_hits = sum(bool(p.search(own)) for p in patterns)
            matches.append((-own_hits, len(ancestors(entry)), entry["title"], url_for(entry)))
    matches.sort()
    print(json.dumps([{"title": t, "url": u} for _, _, t, u in matches[:10]], indent=2))
    if not matches:
        sys.exit(1)


def cmd_reference(kind, name):
    """Print a reference URL. The pattern is fixed, so no fetch is needed."""
    folders = {"function": "functions", "hook": "hooks", "class": "classes"}
    if kind not in folders or not re.fullmatch(r"[\w.\-]+", name):
        fail("Usage: reference <function|hook|class> <name>")
    print(f"https://developer.wordpress.org/reference/{folders[kind]}/{name.lower() if kind != 'hook' else name}/")


def cmd_headings(url):
    """Print 'line<TAB>level<TAB>text' for every heading, after one header line."""
    text = fetch(url)
    lines = text.splitlines()
    print(f"{clean_url(url)}\tlast_modified={last_modified(text)}\tlines={len(lines)}")
    for number, level, title in parse_headings(lines):
        print(f"{number}\t{level}\t{title}")


def cmd_section(url, selector):
    """Print one section as JSON: the lines under a heading, up to the next heading
    of equal or higher level. `text` excludes the heading line; see `heading`."""
    text = fetch(url)
    lines = text.splitlines()
    headings = parse_headings(lines)

    if selector.isdigit():
        found = [h for h in headings if h[0] == int(selector)]
    else:
        wanted = plain(selector).lower()
        found = [h for h in headings if h[2].lower() == wanted] or [h for h in headings if wanted in h[2].lower()]
    if not found:
        fail("No heading matches", url=clean_url(url), selector=selector)
    if len(found) > 1 and not any(h[2].lower() == plain(selector).lower() for h in found):
        fail("Heading is ambiguous. Pass a line number.", url=clean_url(url),
             candidates=[{"line": n, "heading": t} for n, _, t in found[:10]])

    start, level, title = found[0]
    end = len(lines)
    for number, other_level, _ in headings:
        if number > start and other_level <= level:
            end = number - 1
            break
    # The heading line itself is left out of `text`: in the raw Markdown it carries
    # invisible characters and a link with ?output_format=md. The cleaned heading
    # is in `heading`. Every other line is returned exactly as it appears.
    body = lines[start:end]
    truncated = len(body) > MAX_SECTION_LINES
    if truncated:
        body = body[:MAX_SECTION_LINES]

    print(json.dumps({
        "url": clean_url(url),
        "last_modified": last_modified(text),
        "fetched": date.today().isoformat(),
        "heading": title,
        "lines": [start, start + len(body)],
        "truncated": truncated,
        "text": "\n".join(body).strip("\n"),
    }, indent=2, ensure_ascii=False))


def cmd_links(url):
    """List links to pages under this page's URL (children), as 'text<TAB>url'."""
    base = clean_url(url).rstrip("/") + "/"
    seen = {}
    for text_, target in re.findall(r"\[([^\]]+)\]\((https?://[^)\s]+)\)", fetch(url)):
        target = clean_url(target)
        if target.startswith(base) and target != base and target not in seen:
            seen[target] = plain(text_)
    for target, text_ in seen.items():
        print(f"{text_}\t{target}")
    if not seen:
        sys.exit(1)


def cmd_sitemap(words):
    """Search the sub-sitemaps, one at a time, and stop at the first one with a match."""
    patterns = words_pattern(words)
    sub_sitemaps = re.findall(r"<loc>([^<]+)</loc>", fetch(SITEMAP_INDEX))
    for sub in sub_sitemaps:
        urls = re.findall(r"<loc>([^<]+)</loc>", fetch(sub))
        hits = [u for u in urls if all(p.search(u.replace("-", " ").replace("/", " ")) for p in patterns)]
        if hits:
            print("\n".join(hits[:15]))
            return
    fail("No sitemap match", words=words, sitemaps_searched=len(sub_sitemaps))


def main(argv):
    """Dispatch a subcommand."""
    commands = {
        "resolve": (cmd_resolve, lambda a: len(a) >= 1, lambda a: (a,)),
        "reference": (cmd_reference, lambda a: len(a) == 2, lambda a: tuple(a)),
        "headings": (cmd_headings, lambda a: len(a) == 1, lambda a: tuple(a)),
        "section": (cmd_section, lambda a: len(a) == 2, lambda a: tuple(a)),
        "links": (cmd_links, lambda a: len(a) == 1, lambda a: tuple(a)),
        "sitemap": (cmd_sitemap, lambda a: len(a) >= 1, lambda a: (a,)),
    }
    if len(argv) < 2 or argv[1] not in commands:
        sys.exit(__doc__)
    func, valid, build = commands[argv[1]]
    if not valid(argv[2:]):
        sys.exit(__doc__)
    func(*build(argv[2:]))


if __name__ == "__main__":
    main(sys.argv)
