# PLAN: docs-lookup agent, spec v1.1

## The request (verbatim)

> We need to narrow the scope of this project to the docs lookup agent, and finding the relevant docs sections via sub-agent. The purpose is to see if this is more efficient than having Opus do the lookups (either from memory, or from the docs.)
>
> The purpose is to eventually have the skill land in the WordPress Agent Skills repo. Update the README file in docs-lookup to reflect this. Go over with me the README file so we can trim that to this specification only.
>
> The agent I believe is correctly set up, we need to make sure it is wired in correctly, and can find the pertinent docs sections (function signatures, reference sections of code, etc) - and return only that context as Markdown. That is the sole purpose - to remove documentation lookups.
>
> The lookup agent should be able to find what "Opus already knows well" with relative ease, and scope it to the decision making process. This should happen via helper scripts and the URL building mechanism.
>
> Make a plan for this spec, v. 1.1 of the docs-lookup agent.
>
> The evaulation findings are not relevant in this new spec, but should be kept for reference going forward. So I think the work should be done off the primary docs-lookup branch, changes to the agent .md files, and given this information.

The delegation findings that motivate the wiring section stay in [`v2/PLAN.md` section 12 on `eval-v2`](https://github.com/flexseth/docs-skills/blob/eval-v2/v2/PLAN.md).

## Context

- The project is now only the docs-lookup sub-agent: find the pertinent docs section (signature, reference, props, deprecation) and return only that (a JSON object whose snippets are the page's Markdown), so the caller does no documentation lookups itself. The eval is out of scope; its code and results stay on `sanity-check` and `eval-v2` for reference.
- Goal: land as a skill in `WordPress/agent-skills`. Checked upstream (read-only): skills live at `skills/<name>/SKILL.md` with `references/` and `scripts/`. Existing helper scripts are Node (`.mjs`); there is no Python and no sub-agent usage in the repo. PR #127 sets the installed-path rule: helper paths are quoted `<skill-name-dir>` placeholders resolved from the directory holding the installed `SKILL.md`, never relative to the working directory. PR #30 (`wp-docs`) is about writing docs, not looking them up.
- Where the files are now: the primary branch `docs-lookup-agent` (worktree `docs-lookup-agent/`, one commit `ece3326`) has only `README.md`, `docs-lookup-agent.md` and `LICENSE`. The agent file, `wp-docs.py` and its test exist only on `sanity-check`/`eval-v2`, which sit 23 commits ahead.

## Branching

1. New branch `docs-lookup-v1.1` from `docs-lookup-agent`. Do not touch `sanity-check` or `eval-v2`.
2. Bring over only the product files with `git checkout sanity-check -- <path>`: `agents/docs-lookup.md`, `agents/wp-docs.py`, `agents/test_wp_docs.sh`. No harness, prompts, rubrics or results.
3. The sparse checkout on `docs-lookup-agent` lists only some paths; add the new ones before `git add`.

## Work, in order

### 1. README: walk through it with you, then trim (first, no code)
The current README (89 lines) is mostly the lawyer analogy, the delegation premise and the evaluation. Proposed cut, section by section, to confirm with you before writing:

| Current section | Proposal |
| --- | --- |
| Law-firm analogy and table | Cut to 2-3 lines or drop; your call |
| "The premise: pay the lawyer only for lawyering" | Cut to one sentence: the aim is to test whether a sub-agent lookup is cheaper and more accurate than Opus looking up docs or answering from memory |
| "Why have an associate at all" | Keep, shortened: context size, APIs change, caller keeps judgment |
| "How the associate works" + script examples | Keep; this is the spec's core |
| "The evaluation" | Remove; one line linking to the `eval-v2` branch |
| "Where things stand" (v1/v2 results) | Remove; link to issues #2/#3 and `results-v1` |
| "What is in this branch" | Rewrite for the skill layout |
| License | Keep |

New README opens with: what it is, who calls it, what it returns, how to install, and the upstream-landing goal.

### 2. Spec v1.1 (`docs-lookup-agent.md`)
- **Fetch Markdown, return JSON.** The script fetches Markdown from the upstream sources only (developer.wordpress.org with `?output_format=md`, and raw.githubusercontent.com for the Gutenberg docs and manifest). The agent's reply is the existing JSON object (`results` / `unresolved`), whose `snippet` field is that Markdown copied exactly. No HTML is fetched or returned.
- Scope statement: return the decision-making facts (signature, parameters/props, return value, since/deprecated, one example). Not tutorials or full pages.
- Remove the "do not use it for basics you already know" rule. Replace with the scoping idea below.
- Keep the give-up rule, the exact-copy rule and the host allowlist.

### 3. Scoping "what Opus already knows" via a helper script
Rather than the agent deciding what is basic, make the script cheap and decisive, so a lookup of a well-known function costs one call:
- New `wp-docs.py find <query>`: resolve (manifest or reference pattern) and return the best URL plus the **decision sections** in one call. It picks headings from a fixed list (Parameters, Props, Return, Usage/Signature, Deprecated/Changelog, Example) and returns only those, capped by the existing `--max-lines` logic.
- Keep `resolve`, `reference`, `headings`, `section`, `links`, `sitemap` as the low-level steps for the unusual cases.
- Reuse the existing `fence_step()`, the section cutter, the cache and the host allowlist in `agents/wp-docs.py`.

### 4. Wiring (the main risk)
- **Caller instruction that names the agent.** The Agent tool only spawns a sub-agent when the user asks or names the agent type, so the skill ships a short rule that names `docs-lookup`: use it for every WordPress/Gutenberg API lookup before writing code, batch resources in one call, do not fetch developer.wordpress.org directly. It goes in `SKILL.md` and as a snippet to append to `CLAUDE.md`. (From the v2 pilot: Opus delegated 1 of 5 times without it. How well this works is untested.)
- **Agent description:** open with a directive ("Use for every WordPress or Gutenberg documentation lookup"), drop the "basics" line.
- **Script path:** replace `{{WP_DOCS_PY}}` with the upstream `<skill-name-dir>` convention (PR #127), quoted, resolved from the installed `SKILL.md`.
- **Permissions:** the agent declares `tools: Bash`; document the narrow allow rule `Bash(python3 "<skill-name-dir>/scripts/wp-docs.py" *)`. Opus keeps Bash in its tool set because the sub-agent will not start without it.
- **Batching:** the sub-agent batched commands with shell variables and the allow rule denied them. Add a multi-heading option to `section` so one call can fetch several, and tell the agent to run one command per call.

### 5. Skill layout for upstream
```
skills/wp-docs-lookup/
  SKILL.md                 caller instructions and when to call the agent
  agents/docs-lookup.md    sub-agent definition
  scripts/wp-docs.py       helper (see decision below)
  scripts/test_wp_docs.sh
  references/              url-sources.md (manifest, reference patterns, sitemaps)
```
The exact folder name and layout is checked against `docs/authoring-guide.md` and `shared/scripts/validate-skills.mjs` before opening an upstream PR.

## Decisions for you
1. **Script language.** Upstream scripts and CI are Node; ours is Python (stdlib). Recommend keeping Python through v1.1 and treating a Node port as a separate step before the upstream PR, so the spec work is not blocked. Say if you want the port inside v1.1.
2. **Return format.** Settled: the agent's reply stays JSON, and the documentation it carries is Markdown fetched from developer.wordpress.org or GitHub. No decision needed.
3. **How much of the analogy survives** in the README.

## Verification
- Free: `agents/test_wp_docs.sh` (cache, allowlist) still passes on the new branch.
- Free: a fixed list of about 15 lookups run through `wp-docs.py find`, mixing well-known basics (`register_post_type`, `$wpdb->prepare`, `wp_enqueue_script`) and fast-changing items (`TextareaControl`, `block.json` `viewScriptModule`, Interactivity `wp_interactivity_state`, a deprecated component). Pass means each returns the right URL and the decision sections in one call, and a made-up name returns not-found with what was tried.
- Free: check the installed-path wiring by copying the skill folder elsewhere and running the documented command from another directory.
- One cheap live check: a session told "use docs-lookup" returns the JSON object only, with each `snippet` identical to the page's Markdown. No eval runs.

## Out of scope
Evaluation harness, prompts, rubrics, judging, results, the cache beyond the existing temp-dir cache, and any change to `sanity-check`/`eval-v2`.

## Branching (open question, for discussion on the PR)

The primary branch `docs-lookup-agent` holds only the initial spec (`ece3326`) and is not on `origin`. The agent file, helper script and test live on `sanity-check` and `eval-v2`, which are 23 commits ahead. This PR is based on `main`, so it also carries the one initial-spec commit; `sanity-check` and `eval-v2` are untouched and stay as the evaluation record. Alternatives: push `docs-lookup-agent` and base on it, or cut v1.1 from `sanity-check`. Which is right depends on how you want the eval history to relate to the skill history.

## Links (full URLs, so nothing has to be guessed)

Repository and issues
- Repo: https://github.com/flexseth/docs-skills
- Issue #1, other sources with Markdown output: https://github.com/flexseth/docs-skills/issues/1
- Issue #2, renamed handbook URLs and the lookup rework: https://github.com/flexseth/docs-skills/issues/2
- Issue #3, eval v1 findings and v2 to-do: https://github.com/flexseth/docs-skills/issues/3

Current spec and agent files (on the eval branches; not on `docs-lookup-agent`)
- Spec: https://github.com/flexseth/docs-skills/blob/sanity-check/docs-lookup-agent.md
- Agent definition: https://github.com/flexseth/docs-skills/blob/sanity-check/agents/docs-lookup.md
- Helper script: https://github.com/flexseth/docs-skills/blob/sanity-check/agents/wp-docs.py
- Script test: https://github.com/flexseth/docs-skills/blob/sanity-check/agents/test_wp_docs.sh
- README being trimmed: https://github.com/flexseth/docs-skills/blob/sanity-check/README.md
- Harness and eval description (reference only): https://github.com/flexseth/docs-skills/blob/sanity-check/harness-evaluation.md
- v1 results (reference only): https://github.com/flexseth/docs-skills/tree/sanity-check/results-v1

Delegation findings (why the plan names the agent)
- v2 plan, section 12 (delegation remedy and root causes): https://github.com/flexseth/docs-skills/blob/eval-v2/v2/PLAN.md
- Commit that added it: https://github.com/flexseth/docs-skills/commit/8b6f31e48ba410a955d8ac7ed72f0f143ace617f
- Smoke-run commit: https://github.com/flexseth/docs-skills/commit/45d9f0f63150b61dde253bf7db559a3e4be91cb2

Upstream (WordPress/agent-skills)
- Repo: https://github.com/WordPress/agent-skills
- Skills folder (layout `SKILL.md`, `references/`, `scripts/`): https://github.com/WordPress/agent-skills/tree/main/skills
- Example skill with a helper script (`wp-project-triage`): https://github.com/WordPress/agent-skills/tree/main/skills/wp-project-triage
- Authoring guide: https://github.com/WordPress/agent-skills/blob/main/docs/authoring-guide.md
- Contributing: https://github.com/WordPress/agent-skills/blob/main/CONTRIBUTING.md
- PR #127, installed script-path convention: https://github.com/WordPress/agent-skills/pull/127
- PR #30, upstream `wp-docs` skill (writing docs, not lookup): https://github.com/WordPress/agent-skills/pull/30
