#!/usr/bin/env bash
#
# Run one eval: one prompt, one arm, one repeat.
#
#   ./run.sh <prompt> <arm> [rep]
#
#   prompt  01..07 (file in prompts/)
#   arm     agent   Opus + docs-lookup sub-agent + WebFetch
#           bare    Opus + WebFetch, no sub-agents
#           sonnet  Sonnet + WebFetch, no sub-agents
#   rep     repeat number (default 1)
#
# Each run is a fresh `claude -p` process in an empty temp dir outside
# ~/Developer, so it starts with no context, no CLAUDE.md, no memory and
# no view of other runs. Results land in results/<prompt>/<arm>/rep-<rep>/.
#
# Set FORCE=1 to overwrite an existing result.

set -euo pipefail

HERE="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROMPT_ID="${1:?usage: run.sh <prompt> <arm> [rep]}"
ARM="${2:?usage: run.sh <prompt> <arm> [rep]}"
REP="${3:-1}"

PROMPT_FILE="$HERE/prompts/$PROMPT_ID.md"
[[ -f "$PROMPT_FILE" ]] || { echo "No prompt: $PROMPT_FILE" >&2; exit 1; }

# Tools every arm gets. No Bash: runs write source only (see preamble).
TOOLS=( Read Write Edit Glob Grep WebFetch WebSearch )
ARM_ARGS=()

case "$ARM" in
	agent)
		MODEL=opus
		ARM_ARGS+=( --agents "$( python3 "$HERE/lib/agents_json.py" "$HERE/agents/docs-lookup.md" )" )
		# Only docs-lookup may run, so the arms differ by exactly one thing.
		# v1 used the built-in web-fetch agent as a fallback (finding 4).
		ARM_ARGS+=( --disallowedTools "Agent(web-fetch)" "Agent(general-purpose)" "Agent(claude)"
			"Agent(Explore)" "Agent(Plan)" "Agent(statusline-setup)" )
		TOOLS+=( Agent )
		;;
	bare)
		MODEL=opus
		ARM_ARGS+=( --disallowedTools Agent )
		;;
	sonnet)
		MODEL=sonnet
		ARM_ARGS+=( --disallowedTools Agent )
		;;
	*)
		echo "Unknown arm: $ARM (agent | bare | sonnet)" >&2
		exit 1
		;;
esac

OUT="$HERE/results/$PROMPT_ID/$ARM/rep-$REP"
if [[ -e "$OUT" && "${FORCE:-0}" != 1 ]]; then
	echo "Already exists: $OUT (FORCE=1 to overwrite)" >&2
	exit 1
fi
rm -rf "$OUT"
mkdir -p "$OUT"

# The exact prompt both arms receive, saved verbatim.
cat "$HERE/prompts/_preamble.md" "$PROMPT_FILE" > "$OUT/prompt.md"

WORK="$( mktemp -d "${TMPDIR:-/tmp}/wp-eval.XXXXXX" )"
trap 'rm -rf "$WORK"' EXIT

now_ms() { python3 -c 'import time; print(int(time.time() * 1000))'; }

echo "▶ prompt $PROMPT_ID · arm $ARM · rep $REP · model $MODEL"
START="$( now_ms )"

set +e
(
	cd "$WORK" &&
	claude -p \
		--model "$MODEL" \
		--output-format stream-json \
		--verbose \
		--forward-subagent-text \
		--permission-mode dontAsk \
		--allowedTools "${TOOLS[@]}" \
		--strict-mcp-config \
		--no-session-persistence \
		"${ARM_ARGS[@]}" \
		< "$OUT/prompt.md"
) > "$OUT/stream.jsonl" 2> "$OUT/stderr.log"
STATUS=$?
set -e

END="$( now_ms )"
echo "$(( END - START ))" > "$OUT/wall_ms"
echo "$STATUS" > "$OUT/exit_code"

# Keep the generated plugin. Note anything written outside ./output.
if [[ -d "$WORK/output" ]]; then
	cp -R "$WORK/output" "$OUT/output"
fi
( cd "$WORK" && find . -type f ! -path './output/*' ) > "$OUT/stray_files.txt"

python3 "$HERE/summarize.py" "$OUT"

echo "✓ done in $(( (END - START) / 1000 ))s (exit $STATUS) → $OUT"
