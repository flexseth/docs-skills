# Eval v1 findings

Run on 2026-11-08 and 2026-11-09. 7 prompts plus a smoke test (`00`), Opus with the docs-lookup agent (`agent`) vs Opus with WebFetch only (`bare`), 1 repeat each.

- Prompts and rubrics used: `prompts/`, `rubrics/` in this folder
- Numbers: `summary.md`
- Tracking: [#3 v1 findings and v2 to-do](https://github.com/flexseth/docs-skills/issues/3), [#2 agent URL resolution](https://github.com/flexseth/docs-skills/issues/2)

## Result

| | Agent arm | Bare arm |
| --- | --- | --- |
| Judge score | 99% | 99% |
| Mean time | 141s | 170s |
| Mean cost | $0.61 | $0.68 |
| Prompts where docs-lookup was called | 3 of 8 | — |

Total for the run: about 3M tokens, $13 API-equivalent and 50 minutes, including the judge.

**The scores tie because the v1 prompts named the APIs** (e.g. "verified with `check_ajax_referer()`"), so the test measured instruction-following, not API knowledge. The v2 prompts describe outcomes only.

## Findings

### 1. The agent caught a deprecation that Opus and the judge missed
On prompt 05, Opus asked docs-lookup about `ProgressBar`. The agent reported from the live page that it is deprecated since WordPress 7.3, with removal planned for 7.5 and `Progress` from `@wordpress/ui` as the replacement.

- **Agent arm:** avoided it and used a native `<progress>`.
- **Bare arm:** imported the deprecated `ProgressBar`.
- **Judge:** gave both 15/15.

This is the clearest evidence so far of what the agent is for: current facts that memory gets wrong.

### 2. Opus rarely delegated
docs-lookup was called on prompts 00, 01 and 05 only. In 02, 03, 04, 06 and 07 both arms wrote everything from memory and fetched nothing. Likely causes:
- the prompts named the APIs
- the agent's description says not to use it for basics

**Decision:** wait for v2 before changing the description.

### 3. The agent couldn't resolve renamed handbook URLs
On prompt 01 it guessed Interactivity API URLs from memory: 4 fetches, all 404. Gutenberg had renamed the pages:
- `api-reference` → `directives-and-store`
- `…-and-derived-state` → `…-derived-state-and-config`

One guess even used an old typo'd filename (`undestanding-…`). It never tried the parent pages, which load and link to the right page. It also refetched a URL that had already failed.

**Decision:** fix on the `docs-lookup-agent` branch before v2. Full trace and plan in #2.

### 4. Opus fell back to the built-in `web-fetch` sub-agent
After finding 3, Opus called Claude Code's `web-fetch` agent. It found the renamed file through the GitHub directory listing. So the agent arm wasn't only "Opus + docs-lookup".

**Done:** `run.sh` now denies all built-in sub-agents in the agent arm. Tested: `Agent(web-fetch)` is refused, and docs-lookup still runs.

### 5. Compound requests
Opus packed several questions into one call (four on prompt 01). It also asked about a prop the page didn't document (`__nextHasNoMarginBottom` on the smoke test). The "only from the fetched page" rule then left empty or partial answers.

**Decision (#2):** split requests into parts. Each part gets at most 3 fetches: index, parent page, then sitemap. After that it is marked unresolved, with the URLs tried, and the agent continues with the next part.

### 6. WebFetch summarizes pages with Haiku
The agent never sees the raw page, only Haiku's answer to its fetch prompt, so "verbatim" snippets may be paraphrased.

**Decision (#2):** fetch the raw Markdown with `curl`, list headings first, then extract one section. Markdown only, never HTML. The raw text stays in the sub-agent; Opus still gets only the JSON.

### 7. What worked
- Valid JSON in the spec's format on every call.
- Clean URLs, real `last_modified` and `fetched` dates.
- Honest notes about gaps instead of filling them from memory.
- Ran on Sonnet.
- About 2k characters per lookup reached Opus.

Keep all of this when making the changes in #2.

### 8. The judge shares Opus's blind spots
Finding 1 proves it: the judge can only catch errors it already knows about.

**Decision:** give the judge current docs, fetched with docs-lookup, for the APIs each plugin used.

### 9. Nothing was executed
- PHPCS was skipped (WordPress Coding Standards not installed).
- The build was off.
- Plugin Check needs a local WordPress.

Only `php -l` ran, and everything passed.

**Decision:** set up all three before v2.

### 10. One repeat is too noisy
On prompts where the agent was never called, the two arms were effectively identical, yet times differed by 20–40% (e.g. prompt 02: 109s vs 180s). The v2 changes won't reduce this; it comes from run-to-run variation in Opus itself.

**Decision:** pilot v2 at `REPS=1` to check the fixes, then run `REPS=3` in batches across usage windows.

### 11. The 7-doc rule wasn't met
Fetches per prompt ranged from 0 to 8, because Opus relied on memory.

**Done:** the README now says prompts *depend on* 7+ docs (listed in each rubric). Fetch counts are measured, not targeted.

## Fetches per run

| Prompt | Agent arm (Opus / sub-agent) | Bare arm (Opus) |
| --- | --- | --- |
| 00 | 0 / 1 | 2 |
| 01 | 0 / 8 (incl. `web-fetch` fallback) | 1 |
| 02 | 0 / 0 | 0 |
| 03 | 0 / 0 | 0 |
| 04 | 0 / 0 | 0 |
| 05 | 0 / 1 | 1 |
| 06 | 0 / 0 | 0 |
| 07 | 0 / 0 | 0 |

No run crashed, wrote files outside `./output`, or logged errors.
