#!/usr/bin/env bash
#
# Run the full eval, then score, judge and summarize.
#
#   ./run_all.sh
#
# Environment:
#   ARMS     arms to run (default "agent bare"; add "sonnet" for the third arm)
#   REPS     repeats per prompt per arm (default 1; 2–3 gives steadier numbers)
#   PROMPTS  prompt ids (default 01..07)
#   JUDGE    set to 0 to skip the blind rubric judge
#
# Existing results are skipped, so a stopped run can be resumed.

set -uo pipefail

HERE="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
read -r -a ARMS <<< "${ARMS:-agent bare}"
REPS="${REPS:-1}"
read -r -a PROMPTS <<< "${PROMPTS:-01 02 03 04 05 06 07}"

i=0
for prompt in "${PROMPTS[@]}"; do
	for rep in $( seq 1 "$REPS" ); do
		# Alternate arm order so neither arm always runs first.
		order=( "${ARMS[@]}" )
		if (( i % 2 == 1 )); then
			order=( $( printf '%s\n' "${ARMS[@]}" | tail -r ) )
		fi
		i=$(( i + 1 ))

		for arm in "${order[@]}"; do
			dir="$HERE/results/$prompt/$arm/rep-$rep"
			if [[ -e "$dir/metrics.json" ]]; then
				echo "• skip $prompt/$arm/rep-$rep (exists)"
				continue
			fi
			FORCE=1 "$HERE/run.sh" "$prompt" "$arm" "$rep" || echo "✗ run failed: $prompt/$arm/rep-$rep"
		done
	done
done

for dir in "$HERE"/results/*/*/rep-*/; do
	dir="${dir%/}"
	[[ -e "$dir/score.json" ]] || python3 "$HERE/score.py" "$dir" > /dev/null
	if [[ "${JUDGE:-1}" == 1 && ! -e "$dir/judge.json" ]]; then
		python3 "$HERE/judge.py" "$dir" || echo "✗ judge failed: $dir"
	fi
done

python3 "$HERE/summarize.py" --table "$HERE/results"
