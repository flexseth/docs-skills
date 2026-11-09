#!/usr/bin/env python3
"""Blind rubric scoring of one run's plugin by a separate Claude call.
Writes <run-dir>/judge.json.

    judge.py <run-dir>

The judge sees the original prompt, the rubric, the generated files and an
automated check of the WordPress APIs the plugin uses against current docs
(judge_docs.py, the same for every plugin). It never sees which arm produced them.
It runs with no built-in tools (--tools ""), in an empty temp dir, so it scores
from the files alone.

Every rubric has a criterion "No invented or deprecated APIs (... deduct 1 per
instance ...)". The judge is told that each DEPRECATED entry in the check is a
confirmed instance, and this script holds that criterion to the rubric's rule even
if the model forgets: its points are capped at (criterion max - confirmed
deprecated APIs). Scores are never raised. The script also takes "max" from the
rubric's stated `Max:` and recomputes "total" as the sum of the criteria, because
the model's own arithmetic was wrong for 3 of 7 prompts in v1 (it reported 15 for
rubrics that state 16). The model's own figures are kept as judge_total/judge_max.
"""
import json
import re
import subprocess
import sys
import tempfile
from pathlib import Path

HERE = Path(__file__).resolve().parent
sys.path.insert(0, str(HERE))
import judge_docs  # noqa: E402

MAX_FILE_BYTES = 40_000
TEXT_SUFFIXES = {".php", ".js", ".jsx", ".ts", ".tsx", ".json", ".css", ".scss", ".md", ".txt", ".html"}

SCHEMA = {
    "type": "object",
    "properties": {
        "criteria": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "id": {"type": "integer"},
                    "points": {"type": "number"},
                    "max": {"type": "number"},
                    "reason": {"type": "string"},
                },
                "required": ["id", "points", "max", "reason"],
            },
        },
        "invented_apis": {"type": "array", "items": {"type": "string"}},
        "total": {"type": "number"},
        "max": {"type": "number"},
    },
    "required": ["criteria", "invented_apis", "total", "max"],
}

INSTRUCTIONS = """You are grading a WordPress plugin generated from the prompt below, against a fixed rubric.

Score every rubric criterion. Give the points earned (0 up to that criterion's max; half points allowed),
the max, and a one-sentence reason that names the file and code that earns or loses the points.
List every function, hook, directive, prop or class in the code that does not exist in WordPress or
Gutenberg under "invented_apis". Be strict: a criterion is met only if the code actually does it.
"total" is the sum of points and "max" is the rubric's stated max.

An automated check of the plugin's WordPress APIs against current documentation follows the rubric.
- DEPRECATED: a confirmed use of a deprecated API. Count each one as an instance under the rubric's
  "No invented or deprecated APIs" criterion, as that criterion states.
- NOT_FOUND: a lead, not a verdict. Judge it from your own knowledge; list it under "invented_apis" only
  if it really does not exist in WordPress or Gutenberg.
- EXPERIMENTAL, UNVERIFIED, UNCHECKED: information only. Do not deduct for them.
"""


def collect_files(output: Path) -> str:
    parts = []
    for path in sorted(output.rglob("*")):
        if not path.is_file() or path.suffix not in TEXT_SUFFIXES:
            continue
        if {"node_modules", "vendor", "build"} & set(path.parts):
            continue
        text = path.read_text(encoding="utf-8", errors="replace")
        if len(text) > MAX_FILE_BYTES:
            text = text[:MAX_FILE_BYTES] + "\n…[truncated]"
        parts.append(f"### {path.relative_to(output)}\n```\n{text}\n```")
    return "\n\n".join(parts) or "(no files)"


def rubric_facts(rubric: str) -> dict:
    """What the rubric itself states, so the script can hold the judge to it."""
    stated = re.search(r"^Max:\s*(\d+)", rubric, re.M)
    api = re.search(r"^(\d+)\.\s+No invented or deprecated APIs", rubric, re.M)
    return {
        "max": int(stated.group(1)) if stated else None,
        "criteria": len(re.findall(r"^\d+\.\s", rubric, re.M)),
        "api_criterion": int(api.group(1)) if api else None,
    }


def enforce_rubric(verdict: dict, facts: dict, deprecated: list) -> dict:
    """Make the verdict's numbers follow the rubric. Returns notes on what changed.

    - The invented/deprecated-API criterion is capped at (its max - confirmed deprecated
      APIs), the rubric's "deduct 1 per instance, minimum 0". It is never raised.
    - total is the sum of the criteria and max is the rubric's stated Max.
    """
    notes = {}
    criteria = verdict["criteria"]
    api = next((c for c in criteria if c["id"] == facts["api_criterion"]), None)
    if api and deprecated:
        cap = max(0, api["max"] - len(deprecated))
        if api["points"] > cap:
            notes["api_criterion_capped"] = {"criterion": api["id"], "judge_points": api["points"], "points": cap}
            api["points"] = cap
            api["reason"] += (f" [Held to {cap} by the script: {len(deprecated)} deprecated API(s) "
                              f"confirmed against current docs: {', '.join(deprecated)}.]")
    verdict["judge_total"], verdict["judge_max"] = verdict["total"], verdict["max"]
    verdict["total"] = sum(c["points"] for c in criteria)
    if facts["max"] is not None:
        verdict["max"] = facts["max"]
    if len(criteria) != facts["criteria"]:
        notes["criteria_count"] = {"rubric": facts["criteria"], "judge": len(criteria)}
    if facts["max"] is not None and sum(c["max"] for c in criteria) != facts["max"]:
        notes["criteria_max_sum"] = {"rubric_max": facts["max"], "judge_criteria_max_sum": sum(c["max"] for c in criteria)}
    return notes


def main(run_dir: Path):
    prompt_id = run_dir.parent.parent.name
    rubric_file = HERE / "rubrics" / f"{prompt_id}.md"
    if not rubric_file.exists():
        print(f"{run_dir}: no rubric for prompt {prompt_id}, skipping")
        return
    rubric = rubric_file.read_text()
    task = (run_dir / "prompt.md").read_text()
    files = collect_files(run_dir / "output")

    api = judge_docs.load_or_check(run_dir)
    deprecated = judge_docs.deprecated_names(api)
    facts = rubric_facts(rubric)

    judge_prompt = (
        f"{INSTRUCTIONS}\n\n## Prompt given to the developer\n\n{task}\n\n"
        f"## Rubric\n\n{rubric}\n\n"
        f"## Automated API documentation check\n\n{judge_docs.to_markdown(api)}\n\n"
        f"## Generated files\n\n{files}\n"
    )

    with tempfile.TemporaryDirectory(prefix="wp-judge.") as empty:
        proc = subprocess.run(
            # --tools "" removes every built-in tool, so the judge cannot read
            # results/ (or anything else) even if a permission rule ever allowed it.
            ["claude", "-p", "--model", "opus", "--output-format", "json",
             "--json-schema", json.dumps(SCHEMA), "--permission-mode", "dontAsk",
             "--tools", "", "--strict-mcp-config", "--no-session-persistence"],
            input=judge_prompt, capture_output=True, text=True, cwd=empty, timeout=900,
        )

    try:
        envelope = json.loads(proc.stdout)
    except json.JSONDecodeError:
        sys.exit(f"Judge returned non-JSON output:\n{proc.stdout[-2000:]}\n{proc.stderr[-2000:]}")

    verdict = envelope.get("structured_output")
    if verdict is None:
        try:
            verdict = json.loads(envelope.get("result", ""))
        except json.JSONDecodeError:
            sys.exit(f"Could not read the judge's verdict:\n{envelope.get('result', '')[-2000:]}")

    verdict["judge_cost_usd"] = envelope.get("total_cost_usd")
    verdict["deprecated_apis"] = deprecated
    verdict["script_notes"] = enforce_rubric(verdict, facts, deprecated)
    (run_dir / "judge.json").write_text(json.dumps(verdict, indent=2) + "\n")
    notes = verdict["script_notes"]
    extra = f" | deprecated: {', '.join(deprecated)}" if deprecated else ""
    extra += f" | script notes: {', '.join(notes)}" if notes else ""
    print(f"{run_dir}: {verdict['total']}/{verdict['max']}{extra}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(Path(sys.argv[1]))
