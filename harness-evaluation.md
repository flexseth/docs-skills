# docs-lookup-agent evaluation

Does Opus build WordPress plugins faster, cheaper and more accurately when it hands documentation lookups to the docs-lookup sub-agent?

## Arms

| Arm | Main model | Docs access |
| --- | --- | --- |
| `agent` | Opus | docs-lookup sub-agent (Sonnet) + WebFetch |
| `bare` | Opus | WebFetch only, no sub-agents |
| `sonnet` (optional) | Sonnet | WebFetch only, no sub-agents |

`agent` vs `bare` is the main comparison: same model, only the sub-agent differs. `sonnet` answers a separate question, whether a cheaper model alone is enough.

Opus is not forced to use the agent. If it never calls it, the run is flagged in `metrics.json`.

## How a run works

`run.sh` starts a fresh `claude -p` process for every run, in an empty temp dir outside `~/Developer`. Each run therefore:

- starts with a clean context, so no `/clear` is needed
- uses a fixed model, so no `/model` switch is needed
- loads no CLAUDE.md or memory
- can't see other runs' output or this folder

Time, tokens, cost and per-model usage come from Claude Code's own `stream-json` result event, not from the model.

## Files

| Path | What it is |
| --- | --- |
| `agents/docs-lookup.md` | The agent, built from `docs-lookup-agent.md`. Loaded only in the `agent` arm via `--agents` |
| `prompts/_preamble.md` | Shared instructions prepended to every prompt (write to `./output/`, standards, no shell) |
| `prompts/01.md`–`07.md` | The 7 plugin prompts, fixed before any run |
| `rubrics/01.md`–`07.md` | Scoring criteria per prompt, plus the doc surfaces each prompt depends on |
| `run.sh` | One run: `./run.sh <prompt> <arm> [rep]` |
| `run_all.sh` | Every prompt × arm × repeat, then scoring and the summary |
| `summarize.py` | Parses a run's `stream.jsonl` into `metrics.json`; `--table` builds `results/summary.md` |
| `score.py` | Automated checks: `php -l`, PHPCS (WordPress), optional build and Plugin Check, and the API check below |
| `judge.py` | Blind rubric scoring by a separate Claude call with no tools. It is given the API check as context, holds the rubric's "no invented or deprecated APIs" criterion to its own rule, and takes the max from the rubric |
| `judge_docs.py` | Checks every `@wordpress/*` import and WordPress PHP function in a generated plugin against current developer.wordpress.org docs: deprecated, experimental, not found. No model; the result is the same for both arms |

## Running it

```sh
# Smoke test first
./run.sh 01 agent
./run.sh 01 bare

# Everything (resumable; existing results are skipped)
./run_all.sh
REPS=3 ARMS="agent bare sonnet" ./run_all.sh
```

Optional scoring extras:

- `SCORE_BUILD=1` runs `npm install` + `npm run build` per plugin (slow).
- `WP_PATH=/path/to/wordpress` runs Plugin Check (needs `wp` and the plugin-check plugin active).
- PHPCS runs only if the WordPress standard is installed: `composer global require wp-coding-standards/wpcs`.

## Results

Each run writes `results/<prompt>/<arm>/rep-<n>/`:

| File | Contents |
| --- | --- |
| `prompt.md` | Exact prompt sent, verbatim, identical across arms |
| `stream.jsonl` | Full event log, including every tool call |
| `output/` | The generated plugin |
| `metrics.json` | Duration, tokens, cost, models used, fetch counts (main vs sub-agent), lookups, flags |
| `agent-findings.json` | Every sub-agent request and the JSON it returned |
| `score.json` | Automated check results |
| `judge.json` | Rubric score per criterion and invented APIs |

`results/summary.md` compares the arms per prompt and overall.

## Prompt rules

Prompts describe outcomes only: what the plugin must do, never which functions or APIs to use. The rubrics then score whether the model chose correct, current APIs.

The first run (`results-v1/`) used prompts that named the APIs. Both arms scored 99%, because the prompts gave away the answers. Its prompts and rubrics are kept alongside it for reference.

Every prompt must:

- depend on at least 7 WordPress docs across different surfaces, listed under "Expected doc surfaces" in its rubric. How many of those the model actually fetches is a measurement, not a target: getting an API right from memory is a valid outcome.
- include at least 2 docs that aren't general knowledge
- include at least 1 complex doc:
  - Interactivity API
  - JS Hooks
  - REST API
  - WP HTML API
  - Data stores
  - Nonces
  - Settings and Options API

| Prompt | Plugin | Complex surface |
| --- | --- | --- |
| 01 | Recipe Scaler | Interactivity API |
| 02 | Team Directory | REST API |
| 03 | Maintenance Banner | Settings and Options API |
| 04 | Smart Media Markup | WP HTML API |
| 05 | Editorial Checklist | Data store |
| 06 | Block Visibility Rules | JS Hooks |
| 07 | Event RSVP | Nonces |

`metrics.json` lists the URLs each run fetched, so you can check how many docs were read.

## Caveats

- One repeat per prompt is noisy: in v1, identical setups differed by 20–40% in time. Pilot a new setup at `REPS=1` to check it works, then use `REPS=3` for conclusions. To stay inside a 5-hour usage window, split the prompts into batches (the script resumes and skips finished runs):

  ```sh
  REPS=3 PROMPTS="01 02 03" ./run_all.sh
  REPS=3 PROMPTS="04 05" ./run_all.sh
  REPS=3 PROMPTS="06 07" ./run_all.sh
  ```

- The `agent` arm can only call the docs-lookup sub-agent; the built-in sub-agents are denied.
- The judge is a model and can be wrong. Spot-check a few `judge.json` files against the code.
- The `agent` arm can still use WebFetch directly. That is deliberate: it measures whether Opus chooses to delegate.
