# Eval v2: plan and specification

Status: **pilot approved and started 2026-11-09**; the `REPS=3` run waits for the pilot's result. Branch `eval-v2`, created from `sanity-check` at `45d9f0f`.
Items marked **Proposed** still need your decision. See "Open decisions" at the end.

## 1. Question

The project's premise is that the expensive model (Opus) should do the judgment and a cheaper model (Sonnet, as the docs-lookup agent) should do the library work. See the README. v2 asks whether that pays off when building WordPress plugins:

> When Opus can hand documentation lookups to the docs-lookup agent, does it build plugins that use more correct, current WordPress APIs, and at what cost in time and money, compared with Opus alone?

Three things have to be true for a "yes", and v2 measures each separately:

1. **Opus actually delegates.** If it doesn't, the two arms are the same experiment.
2. **The associate is reliable.** URLs resolve, snippets are exact copies, nothing is invented.
3. **The result is better or cheaper.** Fewer deprecated or invented APIs, equal or better scores, and cost and time that are no worse.

### What v1 showed
Scores tied at 99% each, because the prompts named the APIs. Mean time was 141 s (agent) against 170 s (bare) and mean cost $0.61 against $0.68, but identical setups varied by 20–40% in time, so those gaps are not distinguishable from noise. Opus delegated in only 3 of 8 prompts. The one real difference: the agent arm avoided `ProgressBar`, which the docs mark deprecated since WordPress 7.3, and the bare arm used it. The judge missed it.

## 2. What changed since v1

| Area | v1 | v2 |
| --- | --- | --- |
| Prompts | Named the APIs to use | Describe outcomes only; the rubrics score whether the model chose correct, current APIs |
| Finding pages | The agent guessed slugs from memory (4 fetches, all 404) | Resolved through the Gutenberg manifest, fixed reference URLs, sitemap or parent page, by `agents/wp-docs.py` |
| What the agent reads | WebFetch, summarized by Haiku | Raw Markdown, headings first, one section (50-line default cap), through the helper script |
| Agent arm tools | Opus fell back to the built-in `web-fetch` sub-agent | Only docs-lookup may run; every built-in sub-agent is denied |
| Cache | Not applicable | Each run starts dry: its own cache directory |
| Snippets | Unchecked | Graded exact, reflowed (whitespace only, in prose) or altered |
| Judge | Tools denied only by a permission rule; no current docs; max wrong for 3 of 7 prompts | No tools at all; automated API check as context; the rubric's own "no invented or deprecated APIs" rule enforced; max taken from the rubric |
| Scoring | `php -l` only | `php -l`, PHPCS (WordPress), build, Plugin Check, API check |
| Measurement | WebFetch counts only | Script lookups, docs read, delegation, flags for rule breaks |

## 3. Fixed setup

### Arms
| Arm | Main model | Docs access | Tools |
| --- | --- | --- | --- |
| `agent` | Opus | docs-lookup sub-agent (Sonnet) using `agents/wp-docs.py`; WebFetch stays available on purpose | Read, Write, Edit, Glob, Grep, WebFetch, WebSearch, Agent, and Bash limited to `wp-docs.py` |
| `bare` | Opus | WebFetch only, no sub-agents | Read, Write, Edit, Glob, Grep, WebFetch, WebSearch |

Keeping WebFetch in the agent arm is deliberate: it measures whether Opus *chooses* to delegate. An optional `sonnet` arm (Sonnet alone, WebFetch only) exists for the question "is the cheaper model enough on its own?". It is **not** in the plan unless you add it (decision 2).

### Prompts
| Prompt | Plugin | Complex surface |
| --- | --- | --- |
| 01 | Recipe Scaler | Interactivity API |
| 02 | Team Directory | REST API |
| 03 | Maintenance Banner | Settings and Options API |
| 04 | Smart Media Markup | WP HTML API |
| 05 | Editorial Checklist | Data store |
| 06 | Block Visibility Rules | JS Hooks |
| 07 | Event RSVP | Nonces |

"Runs 1–7" in this plan means prompts 01 to 07. Each prompt depends on at least 7 docs, at least 2 that are not general knowledge and at least 1 complex surface. Prompts and rubrics are fixed before any run and are not edited during it.

### Isolation
Every run is a fresh `claude -p` process in an empty temp folder: no conversation history, no `CLAUDE.md`, no memory, no view of other runs. The judge is a separate call with no tools. It never sees the arm.

### Environment to record before the first run
Write these into the findings document so the run can be reproduced:
- the `eval-v2` commit SHA (`git rev-parse --short HEAD`) and `claude --version`;
- the WordPress version of the Plugin Check site (`wp core version --path=<site>`) and that Plugin Check is active;
- PHPCS and WPCS versions (`phpcs -i`), Node and PHP versions;
- the date, and whether the `frontend-design` plugin is off (it was disabled on 2026-11-09).

**Run record for the pilot** (2026-11-09, 18:20 EST):

| Item | Value |
| --- | --- |
| Code | `eval-v2`, based on `45d9f0f`; the only later change is this plan |
| Claude Code | 2.1.321 (v1 ran on 2.1.319) |
| Models | Opus 5.5 (main), Sonnet 5.5 (docs-lookup), Haiku 5.5 (WebFetch's page summaries) |
| WordPress (Plugin Check site) | 7.1.4, a local WordPress Studio site (SQLite) |
| Plugin Check | 2.2.0, active |
| PHPCS | 3.13.6, with the WordPress, WordPress-Core, WordPress-Docs and WordPress-Extra standards |
| Node / npm / PHP | 24.19.0 / 11.17.0 / 8.5.12 |
| `frontend-design` plugin | off |
| Site before the pilot | 59 tables, 39 plugins, no stray `eval-*` entries (snapshot kept to compare afterwards) |

## 4. Procedure

### Step 0: pre-flight
- [ ] Working tree clean, on `eval-v2`, and the commit pushed.
- [ ] `bash -n run.sh run_all.sh` passes.
- [ ] `phpcs -i` lists WordPress; `wp plugin list --path=<site>` shows Plugin Check active.
- [ ] `results/` is empty or absent (the smoke test lives in `results-smoke/`).
- [ ] Snapshot the Plugin Check site (table list and plugin list), so it can be compared afterwards.
- [ ] The environment above is recorded.

### Step 1: pilot (`REPS=1`, all 7 prompts, both arms)
```sh
SCORE_BUILD=1 WP_PATH=<local WordPress site> ./run_all.sh
```
14 runs, then scoring, judging and the summary. **Estimate: about 70 minutes and $11–14.** That is v1's 50 minutes and $13 (judge included), plus about 19 minutes of builds; the judge now costs about $0.17 a call.

The pilot is not for conclusions. It passes only if all of these hold:
- [ ] All 14 runs exit 0 and have `metrics.json`, `score.json` and (for 01–07) `judge.json`.
- [ ] **The agent was called** in at least one agent-arm run. See "The delegation decision" below.
- [ ] In runs that delegated: the helper script ran, `wp-docs.log` and `wp-docs-sections.jsonl` exist, and no snippet is `altered`.
- [ ] No Bash call outside `wp-docs.py`, and the main session never ran the script itself (both are flagged in `metrics.json`).
- [ ] No `unchecked` API results (a fetch failing for a reason other than 404).
- [ ] The Plugin Check site is identical to its snapshot.

### Step 2: main run (`REPS=3`)
Split into batches so each stays inside one 5-hour usage window. The script resumes, because it skips runs that already have results.
```sh
REPS=3 PROMPTS="01 02 03" SCORE_BUILD=1 WP_PATH=<site> ./run_all.sh
REPS=3 PROMPTS="04 05"    SCORE_BUILD=1 WP_PATH=<site> ./run_all.sh
REPS=3 PROMPTS="06 07"    SCORE_BUILD=1 WP_PATH=<site> ./run_all.sh
```
**Estimate: about 3.5 hours and $33–42 for all three repeats, pilot included.** The pilot's `rep-1` results count as repeat 1, because the script skips runs that already have results, so after the pilot the main run adds roughly 2.3 hours and $22–28. The estimate is the pilot's figure × 3.

After each batch, run the checks under "Checks between batches" before starting the next.

### Step 3: analysis and write-up
Build `results/summary.md`, write `v2/FINDINGS.md` in the style of `results-v1/FINDINGS.md`, update issues #2 and #3, and commit the raw runs to `eval-v2` as v1 did.

## 5. What is measured

| Measure | Source | Role |
| --- | --- | --- |
| Judge score (%, rubric max 14–16) | `judge.json` | **Primary**: did the code choose correct, current APIs and meet the spec |
| Deprecated APIs used | API check (`score.json`) | **Primary**: objective, identical for both arms |
| Invented APIs | `judge.json` `invented_apis` | **Primary** |
| PHPCS errors and warnings | `score.json` | Primary, objective |
| Build passes | `score.json` | Primary, objective (exit code and output files only) |
| Plugin Check errors and warnings | `score.json` | Primary, objective; depends on the site's WordPress version |
| Time, cost, tokens (all models, including the associate's) | `metrics.json` | Secondary; noisy, 20–40% in v1 |
| Delegation (docs-lookup calls per run) | `metrics.json` | **Process**: decides whether the comparison means anything |
| Page lookups, `unresolved` count, docs read | `metrics.json` | Process |
| Snippets exact, reflowed, altered | `metrics.json` | Process: how reliable the associate is |
| Flags | `metrics.json` | Process: rule breaks (see below) |

## 6. The delegation decision (the main design risk)

If Opus rarely delegates, the agent arm is mostly the bare arm and the eval measures nothing about the premise. What we know, and what we don't, as of 2026-11-09:

**Known: the Agent tool carries a built-in rule against delegating.** Its own description tells Opus "Do not spawn agents unless the user asks… Only use this tool when the user explicitly says to use a subagent, or names one of the available agent types." This was confirmed by asking an eval-style Opus session to quote its tool description, on Claude Code 2.1.321 and on 2.1.319 (v1's version), so it was not introduced by an update. Opus, asked how it would route a lookup, said it would use WebFetch unless the user named `docs-lookup`. The agent file's description cannot override a rule at that level, so rewording it alone is unlikely to help. Opus's self-report is a hint, not proof: v1 delegated 3 of 3 times a lookup was needed.

**Not known: how often Opus delegates on the v2 prompts.** v1's evidence (delegated in 3 of 8 runs, nothing looked up in 5) comes from prompts that *named the APIs*, which left Opus with nothing to be unsure about, so it overstates how often v2 will skip lookups. The smoke prompt (00) names `TextareaControl` and says to look it up, so it is not representative either. **The pilot is therefore the baseline** for natural delegation on outcome-only prompts, which is why no separate test is run first.

**Two views of the agent arm will be reported, always side by side:**
- **As assigned:** every agent-arm run. This is what someone using the setup would get.
- **As delegated:** only runs with at least one docs-lookup call. This is the cleanest test of whether the associate helps.

**No change to the agent's description or the arms before the pilot,** so that only one thing changes at a time, as issue #3 says.

**Proposed decision rule after the pilot:** if Opus delegated in **fewer than 3 of the 7 prompts**, pause. Do not start `REPS=3`. Decide first how to supply the instruction layer. The leading option is an operator-level instruction in the agent arm only (`--append-system-prompt`, so the prompts stay identical across arms), with a matching instruction for the bare arm so the arms differ only in who does the lookup:
- **A, route only:** "When you need WordPress or Gutenberg documentation, get it from the docs-lookup agent. Don't fetch developer.wordpress.org yourself." (Bare arm: "fetch it yourself with WebFetch.")
- **B, route and verify:** A, plus "Before using a WordPress or Gutenberg API you haven't just verified, ask docs-lookup for its current signature, props and deprecation status."

Note this would change what the agent arm measures: from "does Opus choose to delegate" to "does delegating help when Opus does it", which is the project's actual premise. The alternative, denying Opus WebFetch, forces delegation but still allows writing from memory.

## 7. How conclusions will be drawn

- Report per prompt and overall, paired by prompt and repeat.
- A time or cost difference smaller than the v1 noise (20–40%) is reported as **not distinguishable**, not as a win.
- Deprecated and invented API counts, PHPCS, build and Plugin Check are the cleanest evidence, because no model judged them.
- With 3 repeats and 7 prompts these are small samples. Results are described as "suggestive" unless a difference is large and consistent across prompts.
- The judge is a model. Spot-check at least 3 `judge.json` files against the code before quoting any score.
- v1 and v2 percentages are **not directly comparable**: v1's judge reported the wrong max for prompts 04, 05 and 07.

## 8. Checks between batches

Run `python3 summarize.py --table results` and read the flags. Stop and investigate before the next batch if any run shows:
- a non-zero exit code, or no `metrics.json`;
- a Bash call that was denied or ran outside `wp-docs.py`;
- the main session running the helper script directly;
- `wp-docs.log` or `wp-docs-sections.jsonl` missing when the script ran;
- any `altered` snippet;
- more than 8 page lookups in one docs-lookup call;
- `unchecked` API results.

## 9. Known limits

- **Noise.** Identical setups varied 20–40% in time in v1. Three repeats help, but only so much.
- **One machine and one site.** Plugin Check's `outdated_tested_upto_header` compares each plugin with the site's WordPress version, so compare arms on the same site, not across machines or dates. It was 12 of the 26 Plugin Check errors in v1.
- **The API check is limited.** It reads only ES `import` statements and WordPress PHP function calls, not `wp.data.x` globals, PHP classes or hook names.
- **The build check is shallow.** It confirms the build exits 0 and emits files, not that the block works.
- **The judge is a model**, and shares Opus's blind spots except where the API check supplies facts.
- **Plugin Check scans everything that would ship**, `tests/` and `phpunit.xml.dist` included.
- **v1 results were not re-judged** with the new judge.

## 10. Decisions

| # | Decision | Status |
| --- | --- | --- |
| 1 | Delegation threshold after the pilot | **Proposed:** pause if Opus delegates in fewer than 3 of 7 prompts. Applies after the pilot; not needed to start it |
| 2 | The `sonnet` arm | **Decided (2026-11-09):** not in the pilot; revisit afterwards |
| 3 | Budget | **Pilot approved (2026-11-09)**, estimated $11–14 and about 70 minutes. `REPS=3` (about $22–28 more) needs a separate go after the pilot |
| 4 | Where results go | **Decided (2026-11-09):** `results/` on `eval-v2`, raw runs committed as v1 did |
| 5 | What to do if delegation is low | **Open until the pilot:** see section 6 |

## 11. Outputs

- `results/` with the raw runs, and `results/summary.md`.
- `v2/FINDINGS.md`, written after the runs.
- Updates on issues #2 and #3.

## 12. Delegation remedy (added 2026-11-10, not yet implemented)

### Context
In the pilot Opus delegated to `docs-lookup` in 1 of 5 prompts (05). In 01-04 it made no lookup of any kind. It never used Bash to bypass the agent: its 3 Bash calls were mkdir/cd file setup, all denied, and every `wp-docs.py` call came from the sub-agent. Bash is not the cause.

Root causes, in order of weight:
1. Opus judges it does not need docs and writes from memory.
2. The Agent tool's built-in description says: do not spawn agents unless the user asks or **names an available agent type**.
3. The agent's own description says "Do not use it for basics you already know well", which licenses skipping.

An agent description cannot override (2). The lever is an instruction, from the user or operator layer, that **names `docs-lookup`** and makes lookup a required step, not an option.

### Approach (strongest first)
**A. Instruction that names the agent** (the real fix; delivered by whatever the user installs)
- Real use: the skill ships a short rule block that goes in `CLAUDE.md` (or the skill body). Skills load on demand, so a skill description alone is weak; the rule belongs in always-loaded context. Install step: append the block to `CLAUDE.md`.
- Eval: the same text via `--append-system-prompt-file` (agent arm only), so prompts stay identical across arms. The bare arm gets the same text with "fetch it yourself with WebFetch" instead.
- Wording is a procedure, not a suggestion. It names the agent, sets a trigger (any `@wordpress/*` import, block.json field, hook, REST/Interactivity API, or deprecation question) and a required step before writing code ("ask docs-lookup for current signature, props, deprecation status; do not rely on memory for these"). It also says to batch resources in one call and not to fetch developer.wordpress.org itself.

**B. Make the agent description pull, not push away**
- Delete "Do not use it for basics you already know well".
- Open with a directive: "Use this agent for every WordPress or Gutenberg documentation lookup." Keep the "describe each resource in plain language" line.

**C. Remove the shortcuts (eval agent arm only, second variant)**
- Deny WebFetch and WebSearch to Opus so the only route to docs is the agent. Opus can still write from memory, so C works only together with A.

**Bash.** Keep Bash in `--tools` (the sub-agent will not start without it). Add a `--via-agent` guard in `agents/wp-docs.py`, passed by the agent file, so a direct call by Opus is refused. `summarize.py` already flags direct script calls; use that to confirm.

### Eval changes
- `prompts/_system-agent.md`, `prompts/_system-bare.md`: the two instruction texts (variant "route + verify").
- `run.sh`: `--append-system-prompt-file` per arm; trimmed `--tools` (agent arm `Write,Edit,WebFetch,Agent,Read,Bash`; bare arm `Write,Edit,WebFetch,Read`). This makes v2 figures non-comparable with the 10 full-tool baseline runs.
- `agents/docs-lookup.md`: description edit (B).
- `agents/wp-docs.py`: `--via-agent` guard.
- Three measurement fixes: exclude `wp-docs-cache` from the stray-file check in `run.sh`; stop grading whitespace-only code changes as "altered" in `summarize.py`; scale the lookup cap with the number of parts in the agent file.
- Update the estimates in this plan: pilot about $15-17 and 90 min; `REPS=3` about $45-50 and 4.5 h.

### Verification (cheap before expensive)
1. Free: confirm `--append-system-prompt-file` loads (check the init/system event) and the `--via-agent` guard refuses a direct call.
2. One Haiku-priced probe of how an eval-style session would route a ToolsPanel prompt under the new instruction. Opus self-report is only a hint.
3. The real test is the delegation count over prompts 01-07 (one rep, about $15-17). Stop rule: if Opus delegates in fewer than 3 of 7, move to variant C before `REPS=3`.
4. Keep the 10 finished runs as the no-instruction baseline.

### Open risk
Whether an always-loaded instruction counts as "the user asked" under the Agent tool's rule is untested. Naming the agent type satisfies the rule's wording, but the pilot is the proof.
