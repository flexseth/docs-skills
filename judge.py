#!/usr/bin/env python3
"""Blind rubric scoring of one run's plugin by a separate Claude call.
Writes <run-dir>/judge.json.

    judge.py <run-dir>

The judge sees the original prompt, the rubric and the generated files.
It never sees which arm produced them. It runs with no built-in tools
(--tools ""), in an empty temp dir, so it scores from the files alone.
"""
import json
import subprocess
import sys
import tempfile
from pathlib import Path

HERE = Path(__file__).resolve().parent
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


def main(run_dir: Path):
    prompt_id = run_dir.parent.parent.name
    rubric_file = HERE / "rubrics" / f"{prompt_id}.md"
    if not rubric_file.exists():
        print(f"{run_dir}: no rubric for prompt {prompt_id}, skipping")
        return
    rubric = rubric_file.read_text()
    task = (run_dir / "prompt.md").read_text()
    files = collect_files(run_dir / "output")

    judge_prompt = (
        f"{INSTRUCTIONS}\n\n## Prompt given to the developer\n\n{task}\n\n"
        f"## Rubric\n\n{rubric}\n\n## Generated files\n\n{files}\n"
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
    (run_dir / "judge.json").write_text(json.dumps(verdict, indent=2) + "\n")
    print(f"{run_dir}: {verdict['total']}/{verdict['max']}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    main(Path(sys.argv[1]))
