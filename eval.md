# Plan: make the docs-lookup-agent evaluation runnable

## Status (2026-11-08): built and smoke-tested, not committed

- Done: `agents/docs-lookup.md`, `prompts/00–07` (00 = smoke test), `rubrics/01–07`, `run.sh`, `run_all.sh`, `summarize.py`, `score.py` (replaced the planned `score.sh`), `judge.py`, `lib/agents_json.py`, rewritten `harness-evaluation.md`, `.gitignore`.
- Smoke test (prompt 00): agent arm 41s / $0.21 / 83k tokens, 1 sub-agent lookup on Sonnet. Bare arm 56s / $0.21 / 109k tokens, 2 direct fetches. Both passed `php -l`.
- Fixed during testing: token totals now sum `modelUsage` (main `usage` left out sub-agent tokens).
- Not done yet: the full 7-prompt run, the judge against a real rubric, removing the `base-1..7` worktrees, committing.

## Context

`eval-model/harness-evaluation.md` (branch `sanity-check`) describes an eval: does Opus build WordPress plugins faster, cheaper and more accurately when it hands documentation lookups to the docs-lookup-agent? Right now it is written as instructions for a model to follow inside one chat session. As written, it can't run or measure what it is meant to:

1. **The agent doesn't exist yet.** `docs-lookup-agent.md` is a spec ("Status: draft. Not yet an agent file"). No `.claude/agents/` file is installed, so nothing can call it.
2. **The model can't run the harness steps.** `/clear` and `/model` are user commands. A model can't clear its own context, switch its own model, or time and count its own tokens.
3. **The comparison is confounded.** Opus+agent vs Sonnet alone changes two things at once. The agent exists to serve Opus, so the question is "does Opus do better with it than without?" The control has to be **Opus bare** (with WebFetch allowed). Sonnet bare can be an optional third arm, but it answers a different question.
4. **The arms can see each other.** `base-N/agent/` and `base-N/sonnet/` share a worktree, so the second arm can read the first arm's output, and both can read the spec and `harness-evaluation.md`. Both also inherit `/Users/seth/Developer/CLAUDE.md` and auto-memory.
5. **"Accuracy" has no definition.** Nothing says how outputs will be scored.
6. **Smaller issues:**
   - The doc creates 7 worktrees but says to loop "until run-number 8". It should stop after 7.
   - Line 61 says "save it to memory". The prompts should be files committed before any run starts.
   - The rule that each prompt needs 7 or more docs, including a complex one, can't be checked unless fetched URLs are logged.
   - One run per prompt per arm is noisy. Plan for 2–3 repeats, or treat the results as directional only.
   - `-p` runs need non-interactive permissions, or they stall waiting for an approval.

## Approach: replace the Markdown procedure with a script

Each run becomes its own `claude -p` process. A fresh process starts with an empty context and a fixed model, so `/clear` and `/model` are no longer needed. `--output-format stream-json` reports duration, token usage, cost and per-model usage (sub-agent models included), and logs every tool call.

### Files (on `sanity-check`, under `eval-model/`)

- `agents/docs-lookup.md`: a real Claude Code agent built from the spec. Frontmatter sets `model: sonnet`, `tools: WebFetch`, and a description that tells Opus when to delegate. The body is the spec's Procedure, Rules and Return Format. The arm A run loads it with `--agents`, so it isn't installed globally.
- `prompts/01.md` … `prompts/07.md`: written and committed before any run. Each meets the "prompt specifics" rules (7+ docs, 2+ non-general, 1 complex).
- `rubrics/01.md` … `rubrics/07.md`: one per prompt. Lists the expected APIs, hooks and components, the required behaviors, and the security checks (nonce, capability, escaping). Written alongside the prompt, before any run.
- `run.sh <prompt#> <arm> <rep>`:
  - creates an empty temp dir outside `~/Developer` (no CLAUDE.md, no other arm's output)
  - copies in the prompt only
  - runs `claude -p --model opus --output-format stream-json --verbose --permission-mode acceptEdits --allowedTools "Write Edit Read Bash(npm*) Bash(npx*) WebFetch[ Agent]"`, adding `--agents agents/docs-lookup.md` for arm `agent`
  - copies the generated plugin to `results/run-<N>/<arm>/rep-<R>/output/` along with the raw `stream.jsonl`
- `summarize.py results/`: parses each `stream.jsonl` into `metrics.json`. This replaces the separate tokens.md, time.md and model.md files. Fields:
  - `duration_ms`, input/output/cache tokens, `total_cost_usd`
  - `modelUsage`, which shows whether the sub-agent really ran on Sonnet or Haiku (this is the old model.md check)
  - fetched URLs and their count
  - Agent tool calls and their returned JSON (the old "findings via .json")

  It then writes `results/summary.md`, a table comparing the arms per prompt.
- `score.sh <output-dir>`: automated checks run on each output. `php -l`, PHPCS with WordPress-Coding-Standards, `npm run build` if a `package.json` exists, and Plugin Check (`wp plugin check`) if a local WP install exists.
- `harness-evaluation.md`: rewritten as a short README covering the goal, the arms, how to run and how to score. It keeps the "prompt specifics" section.

### Worktrees

The 7 `base-N` worktrees aren't needed once each run uses an isolated temp dir. Results go in one `results/` tree on `sanity-check` instead. I'll ask before removing them.

### Arms

- `agent`: Opus, WebFetch allowed, docs-lookup agent available.
- `bare`: Opus, WebFetch allowed, no agent.
- `sonnet` (optional flag): Sonnet, WebFetch allowed, no agent.

### Accuracy scoring

Two layers:
1. The automated checks in `score.sh`.
2. Rubric scoring by a separate `claude -p` judge call. The judge gets the rubric and the output, but not which arm produced it, and returns a JSON score.

Both scores go into `metrics.json`.

## Verification

1. Dry run with one trivial prompt ("TextareaControl in a block"), arm `agent`. Confirm `stream.jsonl` shows an Agent call and that `modelUsage` lists Sonnet.
2. Same prompt, arm `bare`. Confirm there's no Agent call and that the temp dir held nothing but the prompt.
3. Run `summarize.py` and check that the duration and token numbers match what Claude Code reports for those sessions.
4. Then run all 7 prompts × 2 arms (× repeats).
