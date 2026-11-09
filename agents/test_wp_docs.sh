#!/usr/bin/env bash
#
# Checks that wp-docs.py's cache behaves correctly in both modes, so the eval's
# per-run cache bypass can never hide a broken cache in normal use.
#
#   ./test_wp_docs.sh
#
# Needs network access to developer.wordpress.org. Makes about 5 small
# requests. Everything runs in a throwaway directory; nothing is left behind.
#
# Checks:
#   1. Default mode (WP_DOCS_CACHE_DIR unset): the first lookup of a URL goes
#      to the network, the second is a cache hit, and the cache lives under
#      the temp directory.
#   2. Override mode (WP_DOCS_CACHE_DIR set): two runs with different
#      directories each go to the network (this is what keeps eval runs dry).
#   3. A failed fetch (404) is never cached.
#   4. A host outside the allowlist is refused, with no network call logged.

set -uo pipefail

HERE="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SCRIPT="$HERE/wp-docs.py"
GOOD_URL="https://developer.wordpress.org/reference/functions/wp_interactivity_state/"
BAD_URL="https://developer.wordpress.org/block-editor/reference-guides/interactivity-api/api-reference/"

WORK="$( mktemp -d "${TMPDIR:-/tmp}/wp-docs-test.XXXXXX" )"
trap 'rm -rf "$WORK"' EXIT

FAILURES=0
pass() { echo "  PASS  $1"; }
fail() { echo "  FAIL  $1"; FAILURES=$(( FAILURES + 1 )); }

# Print the log's "cached" values on one line, e.g. "false true".
cached_values() {
	python3 -I -c '
import json, sys
try:
    print(" ".join(str(json.loads(l)["cached"]).lower() for l in open(sys.argv[1]) if l.strip()))
except FileNotFoundError:
    print("")
' "$1"
}

# Run the script with a clean environment apart from what the test sets.
# Usage: run_script <log> <cache dir or ""> <args...>
run_script() {
	local log="$1" cache="$2"; shift 2
	if [[ -n "$cache" ]]; then
		env -u WP_DOCS_CACHE_DIR TMPDIR="$WORK/tmp" WP_DOCS_CACHE_DIR="$cache" WP_DOCS_LOG="$log" python3 "$SCRIPT" "$@" > /dev/null
	else
		env -u WP_DOCS_CACHE_DIR TMPDIR="$WORK/tmp" WP_DOCS_LOG="$log" python3 "$SCRIPT" "$@" > /dev/null
	fi
}

mkdir -p "$WORK/tmp"

echo "1. Default mode: second lookup is a cache hit"
run_script "$WORK/default.log" "" headings "$GOOD_URL"
run_script "$WORK/default.log" "" headings "$GOOD_URL"
[[ "$( cached_values "$WORK/default.log" )" == "false true" ]] \
	&& pass "network call, then cache hit" \
	|| fail "expected 'false true', got '$( cached_values "$WORK/default.log" )'"
ls -d "$WORK"/tmp/wp-docs-* > /dev/null 2>&1 \
	&& pass "cache directory created under the temp directory" \
	|| fail "no cache directory under $WORK/tmp"

echo "2. Override mode: separate cache directories stay isolated"
run_script "$WORK/override.log" "$WORK/cache-a" headings "$GOOD_URL"
run_script "$WORK/override.log" "$WORK/cache-b" headings "$GOOD_URL"
[[ "$( cached_values "$WORK/override.log" )" == "false false" ]] \
	&& pass "each run went to the network" \
	|| fail "expected 'false false', got '$( cached_values "$WORK/override.log" )'"
[[ -d "$WORK/cache-a" && -d "$WORK/cache-b" ]] \
	&& pass "override directories were used" \
	|| fail "override directories were not created"

echo "3. A 404 is not cached"
run_script "$WORK/404.log" "$WORK/cache-404" headings "$BAD_URL"
run_script "$WORK/404.log" "$WORK/cache-404" headings "$BAD_URL"
[[ "$( cached_values "$WORK/404.log" )" == "false false" ]] \
	&& pass "both attempts went to the network" \
	|| fail "expected 'false false', got '$( cached_values "$WORK/404.log" )'"

echo "4. Disallowed host is refused without a network call"
run_script "$WORK/host.log" "$WORK/cache-host" headings "https://example.com/page"
STATUS=$?
[[ $STATUS -eq 1 ]] && pass "exit code 1" || fail "expected exit 1, got $STATUS"
[[ -z "$( cached_values "$WORK/host.log" )" ]] \
	&& pass "nothing logged" \
	|| fail "a lookup was logged for a disallowed host"

echo
if (( FAILURES > 0 )); then
	echo "$FAILURES check(s) failed."
	exit 1
fi
echo "All checks passed."
