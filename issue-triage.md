# Proposal: rework labels, types, and milestones in the Documentation Issue Tracker

**Status:** Draft for Documentation Team discussion
**Data snapshot:** 2026-10-16 (see [TRIAGE.md](TRIAGE.md)); label counts re-checked 2026-10-18
**Repository:** [WordPress/Documentation-Issue-Tracker](https://github.com/WordPress/Documentation-Issue-Tracker) (DIT)

Related drafts: [issue-types.md](issue-types.md), [drafts/labels.md](drafts/labels.md), [drafts/milestones.md](drafts/milestones.md), [DIT-structure.md](DIT-structure.md).

---

## Summary

The DIT uses labels for everything: audience, handbook, WordPress version, block name, workflow state, and who is working on an issue. The result is a large label set that is hard to browse, and a backlog where most issues have gone quiet.

This proposal moves each kind of information to the GitHub feature built for it:

| What it describes | Today | Proposed |
|---|---|---|
| Audience (HelpHub or DevHub) | Two labels, plus `[HelpHub]` or `[DevHub]` in the title | **Issue Type**: `End user docs`, `Developer docs` |
| WordPress release | Labels `5.2` to `7.1` | **Milestone**: for example `WordPress 7.1 docs` |
| Kind of change and where it lives | About 90 labels in use | A set of **26 labels** (listed below) |
| Who is working on it | `self-assigned` label | The native **assignee** field |

The goal is that a contributor can open the tracker, pick "developer docs for 7.1 that need a screenshot and have no owner", and see a short, relevant list.

---

## The problem

All figures come from [TRIAGE.md](TRIAGE.md) or a `gh` query on 2026-10-18.

### Label sprawl

- **90 distinct labels are in use** on the 469 open issues. Every open issue has at least one label.
- **41 are `[Block] …` labels.** Most are on one to four issues each. For contributors (especially new ones), it's nearly impossible to look for issues by label, which doesn't provide much value. The label picker is long without making any issue easier to find.
- **Release labels are mixed in with everything else.** `6.4` (31), `6.6` (18), `6.7` (16), `7.1` (10), `6.5` (7), and `6.9` (4) cover about 86 open issues. The release is a date-bound planning fact, not a category.
- **Audience is both a label and a title prefix.** `user documentation (HelpHub)` is on 231 issues and `developer documentation (DevHub)` on 186. Most titles also repeat the audience (`[HelpHub]`, `[6.7 HelpHub]:`, `[DevHub]`).
- **Several labels duplicate native GitHub features.** `self-assigned` (76 issues) duplicates the assignee field. The GitHub default labels (`bug`, `enhancement`, `duplicate`, `question`, and others) are almost unused; the tracker has no equivalent of them.
- **Workflow labels do not resolve.** `[Status] Review` is on 84 issues and `[Status] In progress` on 30. Neither says how long the issue has been in that state or who is on it. Automations do not remind contributors or maintainers of the repository about pending actions, or help with housekeeping tasks.

### A dormant backlog

- Median open issue age is 805 days. 87% of issues have had no human comment in 180 days. 38% have never had one.
- 60 issues have been claimed, and 48 of those have had no human comment in 90 days.
- The `/milestones` page is empty. Nothing in the tracker says which WordPress release an issue is meant for.
- WordPress 6.0 version titles make up over 400 of the open issues, yet all of these issues are no longer relevant.

### What the data does and does not show

The data supports this much: the information a contributor needs to choose an issue (audience, release, kind of change, whether someone owns it) is spread over many labels, a title prefix, and free text. It is slow to filter on.

The data does **not** show that labels caused the backlog to go stale. Other causes are visible in TRIAGE.md: claims are never released, article edit access blocks new contributors ([#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329)), and `Editor` access is needed for any change ([.spec/CONSTITUTION.md](.spec/CONSTITUTION.md)). A cleaner taxonomy removes one barrier. It will not fix the others on its own. See [Measures of success](#measures-of-success) for how to test the idea.

---

## Proposed model

Three separate axes, each answering one question.

### 1. Address Audience Types 

**Question:** who is the documentation for?

**Answer:** The documentation is for either end users or developers.

#### Existing Standard

Labels `[HelpHub]` and `[DevHub]` indicate the intended audience for the documentation. 

Proposed update: Use **Project Boards** and GitHub actions to automatically categorize issues by audience type.

When a new issue is created,

- if the URL in question contains `developer.wordpress.org` the issue is added to the **DevHub Backlog** board.
- if the URL in question contains `wordpress.org` the issue is added to the **HelpHub Backlog** board.

Each of these boards has a pinned issue that will never be resolved, with best practices for navigating the repository and finding relevant issues to work on, and how the issue will flow through the project states on that board. (Kanban style)

> Note: The pinned issue on each board serves as a guide for contributors, outlining the workflow and how to interact with issues on that board.

(use the Notice block for the callout)
{: .notice}

| Type | Replaces | Covers |
|---|---|---|
| `end user` | label `user documentation (HelpHub)` | Guides on wordpress.org/documentation for the general WordPress user |
| `developers` | label `developer documentation (DevHub)` | Developer handbooks and the Code Reference on developer.wordpress.org |

Issues about the Docs Team's own process (currently `internal tasks`, `Contributor Day`, and similar) use the existing org type `Task`.

Once the type is set, the `[HelpHub]` and `[DevHub]` title prefixes are removed.

**Constraint:** Issue Types are created at the organization level, not per repository, and GitHub limits the number per organization ([DIT-structure.md](DIT-structure.md) records the limit as 25; verify against the GitHub docs). A query on 2026-10-18 shows the WordPress organization currently defines only three: `Task`, `Bug`, and `Enhancement`. Two types need an organization owner to create them. See [Open questions](#risks-and-open-questions) for the fallback.

### 2. Milestones: which WordPress release is this for?

Create one milestone per release, titled `WordPress 7.1 docs`, `WordPress 7.2 docs`, and so on. Put the release summary from the Make post in the milestone description, as described in [drafts/milestones.md](drafts/milestones.md).

- Issues and pull requests that document a feature in a release get that milestone.
- Experimental or undated items (the `[Ex][7.X]` set, for example [#2480](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2480), [#2485](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2485)) go in a `Future (7.x)` milestone.
- Issues that are not tied to a release (typos, broken links, process work) have no milestone.
- The milestone progress bar and project boards then show "what docs work remains for this release".

### 3. Labels: what is the change, and where does it live?

Target set of **26 labels**:

| Group | Labels |
|---|---|
| Kind of change (8) | `new`, `bug`, `enhancement`, `screenshot`, `spelling and grammar`, `broken link`, `codex migration`, `feedback` |
| Surface (8) | `blocks`, `settings`, `guides`, `themes`, `plugins`, `APIs`, `code reference`, `advanced administration` |
| Workflow (5) | `in progress`, `review`, `reviewed`, `needs discussion`, `tracking issue` |
| Contributor (2) | `good first issue`, `help wanted` |
| Housekeeping (3) | `duplicate`, `wontfix`, `invalid` |

Notes:

- `new`, `bug`, `enhancement`, `screenshot`, `spelling and grammar`, `blocks`, and `settings` come from [drafts/labels.md](drafts/labels.md). `bug` means "the documentation is wrong" and `enhancement` means "the documentation could be better". They share names with GitHub's defaults on purpose.
- `feedback` replaces `HelpHub feedback` (56 issues) so the intake source stays visible.
- `blocks` replaces every `[Block] …` label. The block name stays in the issue title, where search already finds it.
- `guides` replaces `Support guides`, `Technical guides`, `WP Overview`, and `Customization`. The HelpHub overhaul tracking issues ([#2126](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2126), [#2163](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2163), [#2165](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2165), [#2181](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2181), [#2378](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2378)) already list the articles per category, so the category does not need its own label.
- `reviewed` is new. It supports the `/reviewed` idea in [DIT-structure.md](DIT-structure.md): `review` means "needs review", `reviewed` means "a reviewer approved this, an editor can pick it up".
- An issue can carry more than one surface label when the documentation spans areas, as [drafts/labels.md](drafts/labels.md) suggests.
- Surface labels can be applied automatically from the URL in the issue, as the same draft proposes. That needs a small GitHub Action and is optional for the first phase.

---

## Old label to new home

Every label in use on an open issue on 2026-10-18 is listed once. Counts are open issues.

### Move to an Issue Type

| Old label | Count | New |
|---|---|---|
| `user documentation (HelpHub)` | 231 | Type `End user docs` |
| `developer documentation (DevHub)` | 186 | Type `Developer docs` |
| `internal tasks` | 24 | Type `Task` |
| `contributor documentation`, `Contributor Day`, `workflow user story`, `WP Credits` | 4, 3, 3, 1 | Type `Task` |

Where an issue touches both audiences (for example [#1696](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1696)), pick the audience that owns the page and mention the other in the body.

### Move to a Milestone

| Old label | Count | New |
|---|---|---|
| `6.4` | 31 | Milestone `WordPress 6.4 docs` (created closed; see [release backlog](#clearing-the-release-backlog)) |
| `6.5` | 7 | Milestone `WordPress 6.5 docs` (closed) |
| `6.6` | 18 | Milestone `WordPress 6.6 docs` (closed) |
| `6.7` | 16 | Milestone `WordPress 6.7 docs` (closed) |
| `6.9` | 4 | Milestone `WordPress 6.9 docs` (closed) |
| `7.1` | 10 | Milestone `WordPress 7.1 docs` (open) |
| `misc-dev-note` | 6 | Milestone `WordPress 7.1 docs` (these are the 7.1 dev note issues, #2423 to #2429) |
| `Experimental`, `Guidelines`, `Real-time collaboration` | 4, 1, 1 | Milestone `Future (7.x)` |

Release labels with no open issues (`5.2`, `5.6` to `5.9`, `6.0` to `6.3`, `6.8`, `7.0`, `dev-note`, `Misc Dev Note`, `field-guide`, `dev note doc`) are deleted. Closed issues keep their history; if the old release is worth recording, create the milestone and attach the closed issues in the same script.

### Merge into a smaller label

| Old label | Count | New |
|---|---|---|
| `[Block] …` (41 labels), `[Blocks] Blocks list page`, `block editor` | many; `block editor` is 21 | `blocks` |
| `Support guides`, `Technical guides`, `WP Overview`, `Customization` | 41, 11, 20, 64 | `guides` |
| `HelpHub feedback` | 56 | `feedback` |
| `new document` | 51 | `new` |
| `migration from Codex` | 15 | `codex migration` |
| `external links` | 3 | `broken link` |
| `needs screenshots` | 5 | `screenshot` |
| `[Status] Review` | 84 | `review` |
| `[Status] In progress` | 30 | `in progress` |
| `Content Review` | 17 | `enhancement` |

### Keep as is

`themes` (73), `plugins` (13), `APIs` (13), `code reference` (7), `advanced administration` (20), `tracking issue` (14), `needs discussion` (16), `good first issue` (7), `bug` (1), `enhancement` (4), `duplicate` (1).

### Retire

| Old label | Count | Why |
|---|---|---|
| `self-assigned` | 76 | Use the assignee field. A script can convert each to a real assignee where the issue author or claimant is clear. |
| `handbook` | 21 | The surface is already shown by the surface label or the type. |
| `high priority`, `medium priority` | 4, 2 | Priority is not used consistently; the milestone expresses urgency better. |
| `re-categorization project` | 2 | The project is over. |
| `[Status] Done` | 1 | Close the issue. |
| `question`, `Learn` | 1, 1 | Close, or move to the Learn team's tracker. |

The same pass removes unused labels (`typo`, `performance`, `meta`, `mobile app`, `triage`, `has-screenshots`, `tutorial review`, `Spam`, `Unrelated`) after the grace period below.

---

## Clearing the release backlog

About 86 open issues carry a release label, and most are refresh requests (screenshots, videos, wording) filed per article for each release. The same article often appears in several batches. For example, Site Editor > Pages is open as [#1229](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1229) (6.4), [#1510](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1510) (6.5), [#1647](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1647) (6.6), and [#2018](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2018) (6.8).

Proposed handling during migration:

1. Create `WordPress 6.4 docs` through `WordPress 6.9 docs` and attach issues, so the history is preserved.
2. For each article with more than one open refresh issue, keep the newest, link the older ones to it, and close the older ones as duplicates. A refresh against the current release covers earlier changes.
3. Move what remains, and still applies, into the current release milestone.
4. Close the old milestones. Closed milestones drop out of the default view, so contributors see only current work.

This sits alongside the "Closeable now" list in [TRIAGE.md](TRIAGE.md#closeable-now) and the feedback clusters in its [section 4](TRIAGE.md#4-helphub-feedback-form-intake-57-issues). Do those first. They reduce the number of issues that have to be migrated.

---

## How a contributor finds work

With types, milestones, and a small label set, the common questions become one URL each. Examples for the issue list search box:

| I want to… | Search |
|---|---|
| Fix a screenshot in the next release's user docs | `is:open type:"End user docs" milestone:"WordPress 7.1 docs" label:screenshot no:assignee` |
| Pick up a quick developer docs fix | `is:open type:"Developer docs" label:"good first issue" no:assignee` |
| Review what others have drafted | `is:open label:review -label:reviewed` |
| Find broken links in the Code Reference | `is:open label:"broken link" label:"code reference"` |
| Triage new feedback | `is:open label:feedback no:milestone` |

Supporting changes:

- Issue forms set the type and ask for the surface, so new issues arrive classified.
- The Docs Handbook page on labels ([#2217](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2217)) is rewritten to match, with the saved searches above.
- A short saved-search list is linked from the repository README.

---

## Migration plan

Each phase is reversible until labels are deleted.

0. **Confirm access and limits.** Find out who owns the WordPress organization settings, confirm the Issue Type limit, and ask for `End user docs` and `Developer docs` to be created. Check whether any workflow or bot depends on the old label names, such as the label notices that `github-actions` posts.
1. **Prepare.** Close the "Closeable now" set and merge the feedback duplicates (see above).
2. **Create.** Add the new labels, create the milestones, and create the types.
3. **Migrate.** Run a `gh` script that, for each open issue, sets the type, milestone, and new labels from the tables above. Run it first as a dry run that writes a CSV of every planned change, and have a second person review a sample. Batch the edits and avoid a flood of notifications by running during a quiet period.
4. **Clean titles.** Strip `[HelpHub]`, `[DevHub]`, and `[6.x HelpHub]` prefixes once the type and milestone exist.
5. **Announce.** Post in #docs on Slack and on the Documentation Team blog, and pin the updated labels guide.
6. **Grace period, then delete.** After about 30 days with no problems, delete the retired labels. Until then they remain, but are not offered on new issues.

---

## Risks and open questions

- **Issue Types availability.** The organization has three types today, and adding two requires an owner. If that is not possible: keep two labels, renamed to `end user docs` and `developer docs`, and drop the `[HelpHub]`/`[DevHub]` title prefixes anyway. The milestone and label parts of this proposal do not depend on types.
- **Is 26 labels the right number?** The set is a starting point. A smaller one (the seven in [drafts/labels.md](drafts/labels.md)) loses surface filtering; a larger one starts to recreate the problem.
- **Block labels.** The HelpHub block overhaul ([#2126](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2126)) may benefit from per-block labels while it runs. If so, keep them as a temporary group and retire them when the overhaul closes.
- **Release categories.** `guides` loses the HelpHub category (Support, Technical, Overview, Customization). Confirm the overhaul team can live with that.
- **Existing links and automation.** Bookmarks and handbook pages that link to label filters will break.
- **Closing older issues.** Closing old refresh issues needs a human decision per article; the script can only suggest.
- **Reviewed workflow.** The `/reviewed` command in [DIT-structure.md](DIT-structure.md) needs a GitHub Action. It can wait for a later phase.
- **Milestone link.** The example in [drafts/milestones.md](drafts/milestones.md) links to `github.com/WordPress/milestones/7.1`, which is not a valid page. Link to the Make release post instead.

---

## Measures of success

Record these before the migration, then check them 30 and 90 days later. They also test whether the taxonomy was the problem.

| Measure | Baseline (2026-10-16) |
|---|---|
| Labels in use on open issues | 90 |
| Open issues with a type and a milestone (where applicable) | 2 with a type, 0 with a milestone |
| Open issues with a human comment in the last 90 days | 33 of 469 (7%) |
| Open issues with no human comment ever | 180 (38%) |
| Claimed issues with no human comment in 90 days | 48 of 60 |
| Median time from filing to first human response | Not yet measured |
| New contributors who claim and finish an issue (per release) | Not yet measured |

If the numbers improve after the migration, labels were part of the problem. If they do not, the next step is the process and access issues listed in [TRIAGE.md](TRIAGE.md#recommended-next-steps).

---

## AI disclosure

- **Agent:** Claude Code
- **Model:** Claude Sonnet 5.5 (`claude-sonnet-5-5`)
- **AI contribution:** Reviewed `TRIAGE.md`, the draft files, and the live repository labels, milestones, and organization Issue Types (read-only `gh` queries); counted label usage; drafted this document.
- **Human contribution:** Seth Miller defined the objective (types, milestones, fewer labels), supplied the source drafts and the `TRIAGE.md` analysis, and will review and decide on the proposal. The label set and mapping have not yet been reviewed by the Documentation Team.
