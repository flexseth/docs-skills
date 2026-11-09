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


def extract_json_block(text: str):
    """Parse the JSON object that follows the first ```json marker, or None.

    A closing-fence regex is not safe here: a snippet inside the JSON can itself
    contain a ``` code fence, which would cut the JSON short. raw_decode reads
    exactly one JSON value and ignores whatever comes after it."""
    start = text.find("```json")
    if start == -1:
        return None
    try:
        value, _ = json.JSONDecoder().raw_decode(text[start + len("```json"):].lstrip())
    except json.JSONDecodeError:
        return None
    return value

# The docs-lookup helper script (agents/wp-docs.py). Its commands that fetch a
# page count against the agent's per-call budget of about 8.
SCRIPT_CALL = re.compile(r"wp-docs\.py\s+(\w+)")
PAGE_COMMANDS = {"headings", "section", "links", "sitemap"}
MAX_PAGE_LOOKUPS_PER_CALL = 8
# Index lookups (manifest, sitemaps) are not docs the model read.
INDEX_URL = re.compile(r"manifest\.json$|sitemap[^/]*\.xml$")


def clean_doc_url(url: str) -> str:
    """Drop the ?output_format=md parameter so URLs compare equal."""
    return re.sub(r"[?&]output_format=md", "", url)


def read_script_log(path: Path) -> list:
    """Lookups logged by the helper script, one JSON object per line."""
    entries = []
    if path.exists():
        for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
            try:
                entries.append(json.loads(line))
            except json.JSONDecodeError:
                continue
    return entries


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
    bash_calls = {}       # tool_use_id -> {command, by, parent, script_command, denied}

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
                elif name == "Bash":
                    command = args.get("command", "")
                    match = SCRIPT_CALL.search(command)
                    bash_calls[block.get("id")] = {
                        "command": command,
                        "by": "subagent" if parent else "main",
                        "parent": parent,  # the Agent call this ran inside, if any
                        "script_command": match.group(1) if match else None,
                        "denied": False,
                    }
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
                if block.get("type") == "tool_result" and block.get("tool_use_id") in bash_calls:
                    # A script call that exits 1 is a normal failed lookup, not a denial.
                    bash_calls[block["tool_use_id"]]["denied"] = bool(block.get("is_error")) and "denied" in tool_result_text(block).lower()
                if block.get("type") != "tool_result" or block.get("tool_use_id") not in agent_calls:
                    continue
                text = tool_result_text(block)
                call = agent_calls[block["tool_use_id"]]
                call["response_raw"] = text
                call["response_json"] = extract_json_block(text)

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

    # Helper script (agents/wp-docs.py): calls, lookups and rule breaks.
    script_calls = [b for b in bash_calls.values() if b["script_command"]]
    other_bash = [b for b in bash_calls.values() if not b["script_command"]]
    log_entries = read_script_log(run / "wp-docs.log")
    log_urls = [clean_doc_url(e.get("url", "")) for e in log_entries]
    script_pages = sorted({u for u in log_urls if u and not INDEX_URL.search(u)})

    # Is every snippet the agent returned an exact copy of text the script gave it?
    sections_file = run / "wp-docs-sections.jsonl"
    section_texts = [e.get("text", "") for e in read_script_log(sections_file)]
    snippets = {"total": 0, "verbatim": 0, "not_verbatim": []}
    for call in agent_calls.values():
        payload = call.get("response_json")
        if call["subagent_type"] != "docs-lookup" or not isinstance(payload, dict):
            continue
        for item in payload.get("results", [payload] if "snippet" in payload else []):
            snippet = (item.get("snippet") or "").strip("\n")
            if not snippet:
                continue
            snippets["total"] += 1
            if any(snippet in text for text in section_texts):
                snippets["verbatim"] += 1
            else:
                snippets["not_verbatim"].append({"key": item.get("key", ""), "chars": len(snippet)})

    page_lookups_per_call = {}   # docs-lookup Agent call id -> page-fetching script calls
    for b in script_calls:
        if b["parent"] and b["script_command"] in PAGE_COMMANDS:
            page_lookups_per_call[b["parent"]] = page_lookups_per_call.get(b["parent"], 0) + 1
    for call_id, call in agent_calls.items():
        call["script_page_lookups"] = page_lookups_per_call.get(call_id, 0)

    if arm == "agent":
        if any(b["by"] == "main" for b in script_calls):
            flags.append("main session ran the helper script directly (skipping the sub-agent)")
        if script_calls and not log_entries:
            flags.append("helper script ran but wp-docs.log is empty or missing (WP_DOCS_LOG did not reach it?)")
        over = {i: n for i, n in page_lookups_per_call.items() if n > MAX_PAGE_LOOKUPS_PER_CALL}
        if over:
            flags.append(f"docs-lookup exceeded {MAX_PAGE_LOOKUPS_PER_CALL} page lookups in one call: {sorted(over.values())}")
        if any(b["script_command"] == "section" for b in script_calls) and not section_texts:
            flags.append("section calls ran but wp-docs-sections.jsonl is empty or missing (snippets cannot be verified)")
        elif snippets["not_verbatim"]:
            keys = [s["key"] or "?" for s in snippets["not_verbatim"]]
            flags.append(f"{len(keys)} snippet(s) are not an exact copy of the script's text: {keys}")
    denied = [b for b in bash_calls.values() if b["denied"]]
    if denied:
        flags.append(f"{len(denied)} Bash call(s) denied by the allowlist (e.g. {denied[0]['command'][:60]!r})")
    if arm == "agent":
        allowed_other = [b for b in other_bash if not b["denied"]]
        if allowed_other:
            flags.append(f"{len(allowed_other)} Bash call(s) other than wp-docs.py ran (e.g. {allowed_other[0]['command'][:60]!r})")
    elif bash_calls:
        flags.append(f"Bash used in the {arm} arm, which has no Bash")
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
        # Helper script lookups, from wp-docs.log. A cache hit is a lookup that
        # made no network call; with the per-run cache dir these should be rare.
        "script": {
            "calls": len(script_calls),
            "calls_main": sum(b["by"] == "main" for b in script_calls),
            "calls_subagent": sum(b["by"] == "subagent" for b in script_calls),
            "by_command": {c: sum(b["script_command"] == c for b in script_calls) for c in sorted({b["script_command"] for b in script_calls})},
            "lookups": len(log_entries),
            "network": sum(not e.get("cached") for e in log_entries),
            "cache_hits": sum(bool(e.get("cached")) for e in log_entries),
            "failed": sum(e.get("status") != 200 for e in log_entries),
            "page_urls": script_pages,
        },
        # Snippets the agent returned vs the text the script gave it. Anything
        # not verbatim means the agent edited, dropped or invented text.
        "snippets": snippets,
        # Distinct docs pages read by any route (WebFetch or the script),
        # excluding the manifest and sitemaps. This is the finding 11 measure.
        "docs_read": len({clean_doc_url(f["url"]) for f in fetches if f["url"]} | set(script_pages)),
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
            # v1 results have no `script` or `docs_read`; fall back to WebFetch URLs.
            "docs_read": m.get("docs_read", len(m["fetches"].get("unique_urls", []))),
            "script_net": m.get("script", {}).get("network"),
            "script_cached": m.get("script", {}).get("cache_hits"),
            "snip_ok": m.get("snippets", {}).get("verbatim"),
            "snip_total": m.get("snippets", {}).get("total"),
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
        "| Prompt | Arm | Reps | Time (s) | Cost ($) | Tokens | WebFetch main/sub | Lookups | Docs read | Script net/cached | Snippets verbatim | PHP lint errs | PHPCS errs | Judge % | Flags |",
        "| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |",
    ]
    for (prompt, arm), reps in sorted(rows.items()):
        avg = {k: mean([r[k] for r in reps]) for k in reps[0]}
        lines.append(
            f"| {prompt} | {arm} | {len(reps)} | {fmt(avg['secs'], '.0f')} | {fmt(avg['cost'], '.2f')} | "
            f"{fmt(avg['tokens'], ',.0f')} | {fmt(avg['fetch_main'], '.1f')}/{fmt(avg['fetch_sub'], '.1f')} | "
            f"{fmt(avg['lookups'], '.1f')} | {fmt(avg['docs_read'], '.1f')} | "
            f"{fmt(avg['script_net'], '.1f')}/{fmt(avg['script_cached'], '.1f')} | "
            f"{fmt(avg['snip_ok'], '.1f')}/{fmt(avg['snip_total'], '.1f')} | {fmt(avg['php_errors'], '.1f')} | {fmt(avg['phpcs_errors'], '.0f')} | "
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
        print(json.dumps({k: m[k] for k in ("duration_ms", "total_cost_usd", "models", "fetches", "agent_calls", "script", "docs_read", "flags")}, indent=2))
    else:
        sys.exit(__doc__)
