# PROJECT-REVIEW plan

**Status:** Draft for Seth to annotate. Nothing in this plan has been executed.
**Written:** 2026-10-18, by Claude Code (Claude Sonnet 5.5, `claude-sonnet-5-5`) at Seth Miller's request. Human review: pending.

How to use this file: fill in the `**Your answer:**` lines, edit anything that is wrong, then tell me to refine or run it.

---

## 1. What you asked for

1. Review the whole project and guess its purpose.
2. Fill in `PROJECT-SUMMARY.md` (currently 0 bytes): completeness of files, and what is next for each step.
3. Outline the files for the Meetup presentation (the deck will be built in Claude Design from these files).
4. Create a printable `PROJECT-REVIEW.pdf` to review on paper.
5. Context: the Meetup is next week. You will also try to build the spec live with Spec Kit.

---

## 2. Best guess at the purpose

**Round-trip documentation editing for WordPress.org, built on Playground and GitHub, specified with Spec Kit.**

- **Problem:** Docs drift between WordPress releases. Only users with `Editor` access can change HelpHub (wordpress.org/documentation) or DevHub (developer.wordpress.org), so small fixes pile up. The Documentation Issue Tracker (DIT) has 469 open issues, a median age of 805 days, and 87% have had no human comment in 180 days (`TRIAGE.md`).
- **Proposed flow:** A proxy fetches a page's raw block markup → a Playground blueprint recreates the post → a contributor edits in Gutenberg (Harper checks spelling and grammar; Notes carry the checklist) → "Suggest changes" opens a GitHub PR → the Docs Team reviews → an Editor applies it. GitHub gives the audit trail that WP.org revisions lack (`docs-updates-proposal.md`).
- **Second thread (separate from the Playground flow):** Rework DIT labels into Issue Types (End user docs, Developer docs), Milestones for releases, 26 labels, and native assignees (`issue-triage.md`, `DIT-structure.md`, `issue-types.md`).
- **Roles:** Human (Seth, `@flexseth`) decides. Spec Kit plans and manages tasks. Claude plans, implements, and runs security review (`README.md`).
- **Side goal:** A showcase for spec-driven development with clear AI disclosure (`AI-disclosure.md`).

**Your answer (is this right? what did I miss?):**

---

## 3. Completeness review

| File or folder | Size | State | What is missing or wrong |
|---|---|---|---|
| `README.md` | 2.2 KB | Good draft | Spec Kit link text and href disagree. No file map, although the constitution says the README holds it. No description of the Playground flow. |
| `.spec/CONSTITUTION.md` | 5.7 KB | Strong draft, mixed | Principles, spec content (001 Purpose, 002 Why), AI rules and writing rules all live in one file. Typos: "rigirous", "explicity". Translation is `TBD`. |
| `.spec/README.md` | 0 B | Empty | Needs a purpose line. |
| `.ai/skill.md` | 0.8 KB | Proof of concept | `Text` component notice. Good first vertical slice. |
| `.ai/quality-gates.md` | 0.4 KB | Stub | Harper and style guide only. Could become the Spec Kit checklist and analyze gates. |
| `.cli/README.md` | 25 B | Stub | Heading only. Unclear how it relates to `playground-cli`. |
| `blueprint.md` | 0 B | **Empty** | `playground.md` points to it (link is also malformed). This is the core demo artifact. |
| `playground.md` | 0.3 KB | Stub | Remove and lean-on lists only. No WP version, no block set, no steps. |
| `PROJECT-SUMMARY.md` | 0 B | **Empty** | This plan fills it. |
| `docs-updates-proposal.md` | 1.8 KB | Coherent draft | States an unresolved gap: raw copy and paste shows no diff in revision history. |
| `use-cases.md` | 0.3 KB | Stub | "How could this process flow be improved?" has no answer. |
| `issue-template.md` | 1.4 KB | Draft | Mixes a generic template with one issue's state (CalolanC). Split into template and example. |
| `issue-triage.md` | 18 KB | Complete proposal | For Docs Team discussion. Org currently has 3 Issue Types; creating more needs an org owner. |
| `DIT-structure.md` | 3.9 KB | Draft, overlapping | Its open questions are answered in `issue-triage.md`. Overlaps `issue-types.md`. |
| `issue-types.md` | 0.6 KB | Micro-spec | Consolidate with the two files above. |
| `TRIAGE.md` | 145 KB | Complete data | AI-generated. Not spot-checked by a human. |
| `docs-assessment.png` | 1.8 MB | Complete | Ready as a slide visual. |
| `guides.md` | 19 KB | Mostly complete | Source-of-truth links are inferred. Two open TODOs. |
| `handbooks.md` | 1.6 KB | Complete | URLs were checked over HTTP. |
| `AI-disclosure.md` | 3.7 KB | Good | Does not cover newer files. Spec Kit bullets describe work not yet done. No model versions for Haiku/Sonnet. |
| `notes.md`, `SCRATCH.md` | small | Scratch | `SCRATCH.md` is a pasted changelog with "not verified" items. Fold in or delete. |
| `drafts/` (gitignored) | — | Rich | `content-discovery/get-the-content.md` is the technical core (proxy, blueprint, PR flow, many open questions). `resources/DocsBot/` holds roles, the Delta Reviewer idea and the 14-step workflow. |
| `presentation-files/` (gitignored) | 0.4 KB | 1 file | `001.md` is a 7-question poll. No outline, slides or demo script. |
| **Spec Kit scaffolding** | — | **Not started** | No `.specify/`, no `/speckit.*` commands, no `spec.md`, `plan.md` or `tasks.md`. `.spec/` is a custom folder, not Spec Kit's layout. |

Git: 8 commits (Oct 16–18). Untracked: `.ai/`, `.cli/`, `.spec/` and most root drafts. `.gitignore` is modified (adds `presentation-files`).

**Your answer (corrections to the table):**

---

## 4. Inconsistencies and risks

1. **Markdown vs block markup (biggest).** The constitution says block markup only. `DocsBot/README.md` imports Markdown. `get-the-content.md` shows developer.wordpress.org pages are Markdown-sourced (REST returns only rendered HTML; `?context=edit` returns 401).
2. **"No external dependencies"** conflicts with the Harper zip, Playground CLI via `npx`, and a proxy service.
3. **Access and permissions.** The proxy needs an Editor-level Application Password. Nobody has authorized it on either .org site. `flexseth` edit access is untested. The content license is unconfirmed.
4. **Two threads in one repo.** Playground round trip vs DIT taxonomy. They share a goal but not an implementation.
5. **Hidden folders.** `drafts/` and `presentation-files/` are gitignored, and the constitution says not to use files that are not approved. Spec Kit and Claude Design will not see them unless you point them there.
6. **Spec Kit command names.** The README says `specify-plan` and `specify-implement`. Current Spec Kit uses `/speckit.plan` and `/speckit.implement` (confirm against the installed version).
7. **Model rules.** The constitution says Haiku for writing and Sonnet for planning. Newer files were written with Sonnet 5.5.
8. **`DELTA.md` says "My solution does not use AI"** while `DocsBot/README.md` includes an AI Delta Reviewer.
9. **Stale content.** `milestones.md` has a struck-through link and a 2025 RC4 sample. `DIT-structure.md` has questions that are already answered.
10. **Local path in `SOURCES.md`** (`/Users/seth/Developer/Claude-for-WordPress`).

**Your answer (which of these matter most? any I should drop?):**

---

## 5. What is next, by Spec Kit step

| Step | Command | Input from this repo | Next action | Ready? |
|---|---|---|---|---|
| 0. Install | `specify init --here --ai claude` | — | Confirm the current CLI syntax (`uvx` is installed). Decide `.spec/` vs `.specify/`. | No |
| 1. Constitution | `/speckit.constitution` | `.spec/CONSTITUTION.md` | Keep principles only. Move 001/002 to the spec. Fix typos, add version and date. Settle the Markdown rule. | Mostly |
| 2. Specify | `/speckit.specify` | `README.md`, `docs-updates-proposal.md`, `use-cases.md`, `DocsBot/README.md` | Write user stories: contributor, Docs Team reviewer, Editor, WordPress Credits student. State non-goals. | Partly |
| 3. Clarify | `/speckit.clarify` | Open questions in `get-the-content.md` | Resolve auth owner, license, proxy hosting, `flexseth` access, markdown vs blocks, translation. | No |
| 4. Plan | `/speckit.plan` | `playground.md`, `blueprint.md` | Fill both files. Test one blueprint. Choose proxy tech and repo layout (`<slug>/blueprint.json` + `content.html`). | No |
| 5. Tasks | `/speckit.tasks` | The plan | Proxy, blueprint generator, "Suggest changes" PR flow, validation check, `/reviewed` Action. | No |
| 6. Analyze | `/speckit.analyze` | `.ai/quality-gates.md` | Turn quality gates into checklist items. | No |
| 7. Implement | `/speckit.implement` | The `Text` component proof of concept (`.ai/skill.md`, `drafts/update-samples/text.md`) | First vertical slice. | No |
| 8. Housekeeping | — | — | Fill `.spec/README.md`, `.cli/README.md`, README file map. Update `AI-disclosure.md`. Delete or fold in `SCRATCH.md`. Commit. | Partly |

**Your answer (order, scope, anything to cut for next week):**

---

## 6. Deliverables I will produce once you approve

1. **`PROJECT-SUMMARY.md`** at the repo root: purpose, the two threads, roles, a repo map (the one the README promises), the completeness table, risks, and next steps by Spec Kit step. WordPress Style Guide tone, sentence-case headings, AI-disclosure footer.
2. **`presentation-files/`** (gitignored, input for Claude Design):
   - `README.md`: file list and a brief to paste into Claude Design (audience, tone, assets).
   - `000-deck-outline.md`: about 15 slides, each with a headline, 2–3 bullets, source file, visual suggestion and speaker notes.
   - `001.md`: keep the poll, add an answer key.
   - `003-demo-script.md`: live run of show, with a fallback if the network or Spec Kit fails.
3. **`PROJECT-REVIEW.pdf`** at the repo root, US Letter, built with reportlab (already installed). Contents: purpose and flow diagram, file inventory with next steps, Spec Kit readiness checklist, risks, open decisions as checkboxes with space to write, a 7-day countdown, the deck outline, and an appendix of stats from `TRIAGE.md`.
4. **One edit to an existing file:** append the new files to `AI-disclosure.md` (model, human review pending). No commit unless you ask.

Proposed deck outline (for your reaction):

1. Title
2. The problem: docs drift (infographic)
3. Poll the audience (HelpHub vs DevHub, issue count)
4. Why the backlog stalls (TRIAGE stats)
5. The idea: round-trip editing
6. How it works (flow diagram)
7. Why Playground
8. Spec-driven development in one slide
9. Roles: Human, Spec Kit, Claude
10. The constitution (block markup, no hotlinking, Harper)
11. Live: building the spec
12. The DIT taxonomy proposal
13. Open questions
14. What you can do
15. Thank you and links

**Your answer (change the deck, add or drop slides):**

---

## 7. Questions for you

**Meetup**

1. How long is your slot, and who is the audience (docs contributors, developers, mixed)?
   **Your answer:**
2. Is the live Spec Kit build the main event, or a segment inside a talk?
   **Your answer:**
3. Do you want the Corey / Delta Reviewer collaboration mentioned in the deck?
   **Your answer:**

**Scope**

4. Is the Meetup spec only the Playground round trip, or does it include the DIT taxonomy proposal?
   **Your answer:**
5. Which page is the demo content: the `Text` component (`.ai/skill.md`), or Customize Permalinks (`get-the-content.md`)?
   **Your answer:**
6. Markdown vs block markup: for DevHub pages that are Markdown-sourced, should the constitution allow a Markdown-to-blocks conversion step?
   **Your answer:**

**Spec Kit**

7. Should I run `specify init` in this repo (creates `.specify/` and commands), or keep `.spec/` as the hidden demo spec and init in a scratch folder?
   **Your answer:**
8. Should the spec be built live from a blank slate, or pre-built with the live run being a replay?
   **Your answer:**

**Files and privacy**

9. Should `drafts/` and `presentation-files/` stay gitignored? Which of them may Claude Design read?
   **Your answer:**
10. Is `flexseth` the right account name to mention anywhere? It is in `get-the-content.md`, flagged as unverified.
    **Your answer:**

**PDF**

11. Paper size and print style: US Letter or A4, color or black and white, one-sided with notes margin?
    **Your answer:**
12. Include the `docs-assessment.png` infographic in the PDF?
    **Your answer:**

**Housekeeping**

13. May I append to `AI-disclosure.md` and fix the typos in `CONSTITUTION.md`, or leave existing files alone?
    **Your answer:**
14. Commit the new files when done, or leave everything untracked?
    **Your answer:**

---

## 8. Verification (after execution)

- Render each PDF page to an image and check for clipping, overflow and print margins.
- Re-check every size and state in section 3 against `wc -c` and `git status` at write time.
- Confirm the figures (469 issues, 805 days, 26 labels) against `TRIAGE.md` and `issue-triage.md`.
- Confirm `.gitignore` still ignores `presentation-files`.
- Mark anything inferred as "inferred" in the summary and PDF.

---

*AI disclosure: Drafted by Claude Code (Claude Sonnet 5.5) from a read of the repository on 2026-10-18. Human contribution: request, direction and review by Seth Miller. Not yet reviewed.*
