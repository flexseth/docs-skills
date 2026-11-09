#!/usr/bin/env python3
"""Turn run output into metrics.

    summarize.py <run-dir>      parse one run's stream.jsonl into metrics.json
                                and agent-findings.json
    summarize.py --table <dir>  build <dir>/summary.md from every run under it

A run dir is results/<prompt>/<arm>/rep-<n>/.
"""
import json
import re
import statistics
import sys
from pathlib import Path

AGENT_TOOL_NAMES = {"Agent", "Task"}
FETCH_TOOL_NAMES = {"WebFetch"}
JSON_BLOCK = re.compile(r"```json\s*(.*?)```", re.S)


def read_events(path: Path):
    for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
        line = line.strip()
        if not line:
            continue
        try:
            yield json.loads(line)
        except json.JSONDecodeError:
            continue


def tool_result_text(block) -> str:
    content = block.get("content")
    if isinstance(content, str):
        return content
    if isinstance(content, list):
        return "\n".join(c.get("text", "") for c in content if isinstance(c, dict))
    return ""


def summarize_run(run: Path) -> dict:
    stream = run / "stream.jsonl"
    if not stream.exists():
        sys.exit(f"No stream.jsonl in {run}")

    result = {}
    fetches = []          # {url, by: "main" | "subagent"}
    agent_calls = {}      # tool_use_id -> call info

    for event in read_events(stream):
        kind = event.get("type")
        parent = event.get("parent_tool_use_id")

        if kind == "result":
            result = event

        elif kind == "assistant":
            for block in event.get("message", {}).get("content", []):
                if block.get("type") != "tool_use":
                    continue
                name, args = block.get("name"), block.get("input", {})
                if name in FETCH_TOOL_NAMES:
                    fetches.append({"url": args.get("url", ""), "by": "subagent" if parent else "main"})
                elif name in AGENT_TOOL_NAMES:
                    agent_calls[block.get("id")] = {
                        "subagent_type": args.get("subagent_type", ""),
                        "description": args.get("description", ""),
                        "request": args.get("prompt", ""),
                        "response_json": None,
                        "response_raw": "",
                    }

        elif kind == "user":
            content = event.get("message", {}).get("content", [])
            if not isinstance(content, list):
                continue
            for block in content:
                if block.get("type") != "tool_result" or block.get("tool_use_id") not in agent_calls:
                    continue
                text = tool_result_text(block)
                call = agent_calls[block["tool_use_id"]]
                call["response_raw"] = text
                match = JSON_BLOCK.search(text)
                if match:
                    try:
                        call["response_json"] = json.loads(match.group(1))
                    except json.JSONDecodeError:
                        pass

    usage = result.get("usage", {})
    model_usage = result.get("modelUsage", {})
    models = sorted(model_usage)
    arm = run.parent.name
    docs_calls = [c for c in agent_calls.values() if c["subagent_type"] == "docs-lookup"]

    # The old model.md check: did the sub-agent really run on Sonnet or Haiku?
    flags = []
    if arm == "agent":
        if not docs_calls:
            flags.append("docs-lookup agent was never called")
        elif not any(("sonnet" in m or "haiku" in m) for m in models):
            flags.append("docs-lookup called but no Sonnet/Haiku usage recorded")
    other_agents = [c["subagent_type"] for c in agent_calls.values() if c["subagent_type"] != "docs-lookup"]
    if other_agents:
        flags.append(f"other sub-agents used: {sorted(set(other_agents))}")
    if (run / "stray_files.txt").exists() and (run / "stray_files.txt").read_text().strip():
        flags.append("files written outside ./output (see stray_files.txt)")
    if not (run / "output").exists():
        flags.append("no ./output directory produced")
    if result.get("is_error") or not result:
        flags.append(f"run did not finish cleanly: {result.get('subtype', 'no result event')}")

    def read_int(name):
        p = run / name
        return int(p.read_text().strip()) if p.exists() else None

    metrics = {
        "prompt": run.parent.parent.name,
        "arm": arm,
        "rep": run.name,
        "duration_ms": result.get("duration_ms"),
        "duration_api_ms": result.get("duration_api_ms"),
        "wall_ms": read_int("wall_ms"),
        "num_turns": result.get("num_turns"),
        "total_cost_usd": result.get("total_cost_usd"),
        # `usage` covers the main session only; modelUsage also counts
        # sub-agents and WebFetch's summarizer, so totals come from it.
        "tokens": {
            "input": sum(u.get("inputTokens", 0) for u in model_usage.values()),
            "output": sum(u.get("outputTokens", 0) for u in model_usage.values()),
            "cache_read": sum(u.get("cacheReadInputTokens", 0) for u in model_usage.values()),
            "cache_creation": sum(u.get("cacheCreationInputTokens", 0) for u in model_usage.values()),
        },
        "main_session_tokens": sum(
            usage.get(k, 0) for k in ("input_tokens", "output_tokens", "cache_read_input_tokens", "cache_creation_input_tokens")
        ),
        "model_usage": model_usage,
        "models": models,
        "fetches": {
            "total": len(fetches),
            "main": sum(f["by"] == "main" for f in fetches),
            "subagent": sum(f["by"] == "subagent" for f in fetches),
            "unique_urls": sorted({f["url"] for f in fetches}),
        },
        "agent_calls": {
            "docs_lookup": len(docs_calls),
            "other": len(other_agents),
        },
        "flags": flags,
    }
    metrics["tokens"]["total"] = sum(metrics["tokens"].values())

    (run / "metrics.json").write_text(json.dumps(metrics, indent=2) + "\n")
    (run / "agent-findings.json").write_text(json.dumps(list(agent_calls.values()), indent=2) + "\n")
    return metrics


def load_json(path: Path):
    return json.loads(path.read_text()) if path.exists() else None


def mean(values):
    values = [v for v in values if v is not None]
    return statistics.mean(values) if values else None


def fmt(value, spec):
    return "–" if value is None else format(value, spec)


def build_table(root: Path):
    rows = {}
    for metrics_file in sorted(root.glob("*/*/rep-*/metrics.json")):
        run = metrics_file.parent
        m = load_json(metrics_file)
        score = load_json(run / "score.json") or {}
        judge = load_json(run / "judge.json") or {}
        rows.setdefault((m["prompt"], m["arm"]), []).append({
            "secs": (m["duration_ms"] or 0) / 1000 or None,
            "cost": m["total_cost_usd"],
            "tokens": m["tokens"]["total"],
            "fetch_main": m["fetches"]["main"],
            "fetch_sub": m["fetches"]["subagent"],
            "lookups": m["agent_calls"]["docs_lookup"],
            "php_errors": score.get("php_lint_errors"),
            "phpcs_errors": score.get("phpcs_errors"),
            "judge_pct": (100 * judge["total"] / judge["max"]) if judge.get("max") else None,
            "flags": len(m["flags"]),
        })

    lines = [
        "# Eval summary",
        "",
        "Means across repeats. Time and cost come from Claude Code's own result event.",
        "",
        "| Prompt | Arm | Reps | Time (s) | Cost ($) | Tokens | Fetches main/sub | Lookups | PHP lint errs | PHPCS errs | Judge % | Flags |",
        "| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |",
    ]
    for (prompt, arm), reps in sorted(rows.items()):
        avg = {k: mean([r[k] for r in reps]) for k in reps[0]}
        lines.append(
            f"| {prompt} | {arm} | {len(reps)} | {fmt(avg['secs'], '.0f')} | {fmt(avg['cost'], '.2f')} | "
            f"{fmt(avg['tokens'], ',.0f')} | {fmt(avg['fetch_main'], '.1f')}/{fmt(avg['fetch_sub'], '.1f')} | "
            f"{fmt(avg['lookups'], '.1f')} | {fmt(avg['php_errors'], '.1f')} | {fmt(avg['phpcs_errors'], '.0f')} | "
            f"{fmt(avg['judge_pct'], '.0f')} | {fmt(avg['flags'], '.0f')} |"
        )

    # Per-arm totals across all prompts.
    lines += ["", "## By arm", "", "| Arm | Runs | Mean time (s) | Mean cost ($) | Mean tokens | Mean judge % |", "| --- | --- | --- | --- | --- | --- |"]
    by_arm = {}
    for (_, arm), reps in rows.items():
        by_arm.setdefault(arm, []).extend(reps)
    for arm, reps in sorted(by_arm.items()):
        lines.append(
            f"| {arm} | {len(reps)} | {fmt(mean([r['secs'] for r in reps]), '.0f')} | "
            f"{fmt(mean([r['cost'] for r in reps]), '.2f')} | {fmt(mean([r['tokens'] for r in reps]), ',.0f')} | "
            f"{fmt(mean([r['judge_pct'] for r in reps]), '.0f')} |"
        )

    out = root / "summary.md"
    out.write_text("\n".join(lines) + "\n")
    print(f"Wrote {out}")


if __name__ == "__main__":
    if len(sys.argv) == 3 and sys.argv[1] == "--table":
        build_table(Path(sys.argv[2]))
    elif len(sys.argv) == 2:
        m = summarize_run(Path(sys.argv[1]))
        print(json.dumps({k: m[k] for k in ("duration_ms", "total_cost_usd", "models", "fetches", "agent_calls", "flags")}, indent=2))
    else:
        sys.exit(__doc__)
