#!/usr/bin/env python3
"""Automated checks on one run's generated plugin. Writes <run-dir>/score.json.

    score.py <run-dir>

Checks:
  - php -l on every PHP file (always)
  - PHPCS with the WordPress standard (only if installed: `phpcs -i` lists WordPress)
  - npm install + build (only if SCORE_BUILD=1; slow, needs network)
  - Plugin Check (only if WP_PATH points at a local WordPress install with plugin-check active)
  - API check: deprecated / experimental / not-found WordPress APIs, from current docs (judge_docs.py)

A skipped check is recorded as null with the reason in "skipped".
"""
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile
import time
from pathlib import Path

PHPCS_CANDIDATES = ["phpcs", str(Path.home() / ".composer/vendor/bin/phpcs")]


def run(cmd, cwd=None, timeout=600):
    proc = subprocess.run(cmd, cwd=cwd, capture_output=True, text=True, timeout=timeout)
    return proc.returncode, proc.stdout + proc.stderr


def find_phpcs():
    for candidate in PHPCS_CANDIDATES:
        path = shutil.which(candidate) or (candidate if Path(candidate).exists() else None)
        if path:
            _, standards = run([path, "-i"])
            if "WordPress" in standards:
                return path
    return None


def build_plugin(plugin: Path) -> dict:
    """npm install + build in a throwaway copy, so results/ never gains a node_modules.

    Install scripts are skipped (--ignore-scripts): package.json was written by a model, so
    nothing it or its dependencies declare should run on this machine at install time."""
    started, step, out = time.time(), "install", ""
    with tempfile.TemporaryDirectory(prefix="wp-build.") as tmp:
        work = Path(tmp) / plugin.name
        shutil.copytree(plugin, work, ignore=shutil.ignore_patterns("node_modules", "build"))
        try:
            code, out = run(["npm", "install", "--no-audit", "--no-fund", "--ignore-scripts"], cwd=work, timeout=900)
            if code == 0:
                step = "build"
                code, out = run(["npm", "run", "build"], cwd=work, timeout=900)
        except subprocess.TimeoutExpired:
            code, out = 1, f"timed out during npm {step}"
        built = sum(1 for f in (work / "build").rglob("*") if f.is_file()) if (work / "build").is_dir() else 0
    return {"ok": code == 0, "failed_step": None if code == 0 else step, "seconds": round(time.time() - started),
            "build_files": built, "log_tail": out[-2000:]}


def parse_plugin_check(out: str):
    """Counts and issues from `wp plugin check --format=json`, or None if it did not run.

    The output is a "FILE: <path>" line followed by a JSON list of issues, per file, with
    "Success: ..." when there are none. WP-CLI notices on stderr are ignored."""
    issues = []
    for match in re.finditer(r"^FILE: (.+)\n(\[.*\])\s*$", out, re.M):
        for issue in json.loads(match.group(2)):
            issues.append({"file": match.group(1), "line": issue.get("line"), "type": issue.get("type"),
                           "code": issue.get("code"), "message": issue.get("message")})
    if not issues and "Success" not in out:
        return None
    by_code = {}
    for issue in issues:
        key = f"{issue['type']} {issue['code']}"
        by_code[key] = by_code.get(key, 0) + 1
    return {"errors": sum(i["type"] == "ERROR" for i in issues),
            "warnings": sum(i["type"] == "WARNING" for i in issues),
            "by_code": dict(sorted(by_code.items(), key=lambda kv: -kv[1])),
            "issues": issues[:40]}


def plugin_dirs(output: Path):
    return [p for p in output.iterdir() if p.is_dir()] if output.exists() else []


def main(run_dir: Path):
    output = run_dir / "output"
    score = {"php_lint_errors": None, "phpcs_errors": None, "phpcs_warnings": None,
             "build_ok": None, "plugin_check_errors": None, "plugin_check_warnings": None, "skipped": {}, "details": {}}

    if not output.exists():
        score["skipped"]["all"] = "no output directory"
        (run_dir / "score.json").write_text(json.dumps(score, indent=2) + "\n")
        return

    php_files = [p for p in output.rglob("*.php") if "node_modules" not in p.parts and "vendor" not in p.parts]

    # 1. php -l
    lint_failures = []
    for php in php_files:
        code, out = run(["php", "-l", str(php)])
        if code != 0:
            lint_failures.append({"file": str(php.relative_to(output)), "error": out.strip()})
    score["php_lint_errors"] = len(lint_failures)
    score["details"]["php_lint"] = lint_failures

    # 2. PHPCS (WordPress standard)
    phpcs = find_phpcs()
    if phpcs and php_files:
        _, out = run([phpcs, "--standard=WordPress", "--report=json", "--extensions=php",
                      "--ignore=*/node_modules/*,*/vendor/*,*/build/*", str(output)])
        try:
            totals = json.loads(out[out.index("{"):])["totals"]
            score["phpcs_errors"], score["phpcs_warnings"] = totals["errors"], totals["warnings"]
        except (ValueError, KeyError):
            score["skipped"]["phpcs"] = "could not parse phpcs output"
    else:
        score["skipped"]["phpcs"] = "WordPress standard not installed for phpcs" if not phpcs else "no PHP files"

    # 3. Build
    if os.environ.get("SCORE_BUILD") == "1":
        results = {}
        for plugin in plugin_dirs(output):
            if (plugin / "package.json").exists():
                results[plugin.name] = build_plugin(plugin)
        score["build_ok"] = all(r["ok"] for r in results.values()) if results else None
        score["details"]["build"] = results
    else:
        score["skipped"]["build"] = "set SCORE_BUILD=1 to run npm install + build"

    # 4. Plugin Check
    wp_path = os.environ.get("WP_PATH")
    if wp_path and shutil.which("wp"):
        errors = warnings = 0
        checks = {}
        for plugin in plugin_dirs(output):
            # Checked by path, not by symlinking into wp-content/plugins: Plugin Check derives the
            # expected text domain from the folder name, so an "eval-" prefix made every plugin fail
            # it, and this way the site's plugins folder is never touched.
            _, out = run(["wp", "plugin", "check", str(plugin.resolve()), f"--path={wp_path}", "--format=json"])
            result = parse_plugin_check(out)
            if result is None:
                score["skipped"]["plugin_check"] = f"wp plugin check did not run for {plugin.name}: {out.strip()[-300:]}"
                errors = warnings = None
                break
            checks[plugin.name] = result
            errors += result["errors"]
            warnings += result["warnings"]
        score["plugin_check_errors"], score["plugin_check_warnings"] = errors, warnings
        score["details"]["plugin_check"] = checks
    else:
        score["skipped"]["plugin_check"] = "set WP_PATH to a local WordPress install with plugin-check active"

    # 5. WordPress APIs the plugin uses, checked against current docs (deterministic).
    try:
        import judge_docs
        api = judge_docs.load_or_check(run_dir)
        for status in ("deprecated", "experimental", "not_found", "unverified", "unchecked"):
            score["api_" + status] = api["summary"].get(status, 0)
        score["details"]["api_deprecated"] = judge_docs.deprecated_names(api)
    except Exception as error:  # a docs or network problem must not stop scoring
        score["skipped"]["api_check"] = f"{type(error).__name__}: {error}"

    (run_dir / "score.json").write_text(json.dumps(score, indent=2) + "\n")
    print(json.dumps({k: v for k, v in score.items() if k != "details"}, indent=2))


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(Path(sys.argv[1]))
