# Inferred User Journeys

**Prompt**
> Given [one recent docs team update](https://make.wordpress.org/docs/2026/09/22/documentation-team-meeting-notes-september-15-2026/), infer more user journeys based on the documentation updates needed. It is OK to do deep research on this item, and place all of the updates linked to their contributor type on this page. I will organize it later on.

Purpose: map the existing journeys to user roles, then infer new journeys and use cases from the gaps.

Sources:
- `../../roles-and-user-stories.md` (roles)
- `docs-contributors/recursive-docs.md` (Recursive Documentation Contributor)
- `docs-contributors/release-team-lead.md` (Release Lead from Docs Team)
- `../_purpose.md` (proposed flow: proxy → Playground blueprint → Gutenberg edit with Harper and Notes → Playground PR → Docs Team review → Editor applies)

Status key: **Existing** = a journey file already exists. **Inferred** = proposed here, to be validated during specification.

---

## 1. Journey-to-role map

| Journey | Status | Primary role | Also touches |
|---|---|---|---|
| Recursive issue search | Existing | Recursive Documentation Contributor | First time contributor, Contributor, Reviewer |
| Release prep for docs | Existing | Release Lead (Docs) | Developer, UX Developer, Writer |

Roles with no journey file yet: Contributor, First time contributor, Developer, UX Developer, Writer, WordPress Credits contributor, and the dummy contributor.

---

## 2. Roles that the existing journeys imply

The existing journeys mention actors that `_roles.md` does not define. Add them to the role list.

| Implied role | Where it appears | What they do |
|---|---|---|
| Docs Team reviewer | `_roles.md` ("reviewed by other members"), `_purpose.md` | Checks out a PR in Playground, compares the editor view and front end, and approves or requests changes |
| Editor (wp.org) | `_purpose.md` | The only role that can publish to HelpHub or DevHub. Applies approved PRs by hand |
| Issue triager | `_purpose.md` (DIT stats) | Labels, closes stale issues, and tags `good first issue` |
| Docs Team liaison / outgoing Release Lead | `release-team-lead.md` | Hands off release-docs context |
| Core / Field Guide author | `release-team-lead.md` | Writes Dev Notes and the Field Guide that docs work depends on |
| Maintainer of this project | `_purpose.md` | Keeps the proxy, blueprint, and checklist templates working |

---

## 3. Role-to-journey inference

### First time contributor
*Existing:* none directly (they appear inside the recursive-search journey).

- **J-FT1: Paste a URL, get a ready editor.** They paste a HelpHub URL, and the proxy and blueprint return a Gutenberg editor holding that page. *Gap to check:* what happens when the URL is not supported, or is not a block-based page?
- **J-FT2: Pick a good first issue.** They browse `good first issue` items, and the project shows each one's checklist and state. This replaces reading several linked issues.
- **J-FT3: First PR from Playground.** They finish edits, open a PR, and understand what happens next. *Gap to check:* GitHub authentication from inside Playground.
- **J-FT4: Feedback after the first PR.** A reviewer requests changes and the contributor returns to the same environment with their edits intact.

### Contributor (proof of concept)
- **J-C1: Claim and work an issue.** They find an issue, use `/assign`, and run the checklist.
- **J-C2: Suggest a change with no checklist.** They add a missing code sample or similar addition and open a PR without a template.
- **J-C3: Resume work.** They return after days away and the project shows what is already done.
- **J-C4: Abandon or hand off.** They release an issue they cannot finish, so it does not sit assigned for months. This addresses the stale-issue problem (87% with no human comment in 180 days).

### Recursive Documentation Contributor (existing)
- **J-R1 (existing): Reconstruct current state across issues.** *New use case from it:* a **state summary view** that merges the open issues, the checklist, and the last activity for one doc.
- **J-R2: Detect missing items.** The project flags unchecked checklist items, such as screenshots not updated, so the contributor does not find them by reading.
- **J-R3: Duplicate issue discovery.** For example #1205, #1755, and #2324 all cover the Term Description block. The project links duplicates and suggests consolidating them.
- **J-R4: Revive a stalled issue.** Feedback sat for 4 months, so the project surfaces the unanswered comment and its author.

### Release Lead from Docs Team (existing)
- **J-RL1 (existing): Scope docs work for a release.** *New use cases from it:*
  - **J-RL2: Release diff to docs list.** Changelog and Field Guide input produce a list of affected docs, marked as new, changed, or deprecated.
  - **J-RL3: Dev note decision.** For each change, record "dev note needed / not needed" with a reason.
  - **J-RL4: Assign work to contributors.** Turn the list into issues with checklists and `good first issue` labels where suitable.
  - **J-RL5: Release progress dashboard.** Show which docs are done, in review, or blocked.
  - **J-RL6: Handoff.** Package the process, channels, and open items for the next Release Lead.

### Developer
- **J-D1: Audit code samples against current code.** They compare samples to the current Gutenberg or Core behavior.
- **J-D2: Document a new component.** The page has no sample, and the developer adds one.
- **J-D3: Flag docs that a changelog entry invalidates.** They report affected pages from a PR or changelog.

### UX Developer
- **J-UX1: Screenshot matrix for a component.** They list each option and generate a screenshot checklist per component.
- **J-UX2: Sidebar vs. component mapping.** They note how each plugin sidebar option affects the underlying component.
- **J-UX3: Admin settings diff between releases.** They compare WP Admin settings screens across versions and flag changed screens.

### Writer
- **J-W1: Editorial pass with Harper.** They fix spelling and grammar in the editor.
- **J-W2: Style consistency pass.** They check terminology against the style guides in `technical-writing-standards/`.
- **J-W3: Replace screenshots for new UI.** They capture screenshots in Playground against the new release and attach them to the PR.

### Docs Team reviewer (implied)
- **J-RV1: Review a PR in Playground.** They see the edited page in the editor and on the front end.
- **J-RV2: Verify the checklist.** They confirm every item is done and correct, not just checked. (The recursive-docs journey notes that a contributor can miss items.)
- **J-RV3: Request changes with context.** Comments stay attached to the PR, in one place.

### Editor (wp.org, implied)
- **J-E1: Apply an approved PR.** They copy approved content into HelpHub or DevHub. *Gap to check:* can a diff or ready-to-paste block markup reduce errors?
- **J-E2: Confirm publication.** They record the live URL on the PR or issue so the loop closes.
- **J-E3: Reject unsafe or off-standard content.** They need a clear reason trail.

### Issue triager (implied)
- **J-T1: Stale issue sweep.** They find issues with no human comment in 180 days, then comment, close, or reassign them.
- **J-T2: Label for newcomers.** They apply `good first issue` when a checklist is complete and scoped.
- **J-T3: Generate a checklist for an issue that has none.**

### WordPress Credits contributor
- **J-WC1: Find a suitable issue.** They need work that is clearly scoped and verifiable. *Todo:* research the Credits program (see `_roles.md`).
- **J-WC2: Prove contribution.** The PR trail gives the audit evidence the program needs.
- **J-WC3: Manual website update case.** Issue #2354 involves an update made manually on the website. Check how this flow can improve.

### Dummy contributor (test role)
- **J-DC1: Smoke test the pipeline.** They use standardized dummy content (Heading, List, Paragraph) imported with `wp-cli` to verify the proxy, blueprint, and PR flow with low compute.

### Maintainer of this project (implied)
- **J-M1: Add support for a new doc type** (HelpHub, DevHub, Dev Notes, Release Notes).
- **J-M2: Update checklist templates** when issue templates change.
- **J-M3: Handle upstream change**, such as a WP.org markup change that breaks the proxy.

---

## 4. New journeys that cut across roles

| ID | Journey | Roles | Why it matters |
|---|---|---|---|
| X1 | **End-to-end happy path**: URL → edit → PR → review → Editor applies → issue closed | First time contributor, Reviewer, Editor | Core proof of concept |
| X2 | **Rejected PR loop**: review feedback → revision → re-review | Contributor, Reviewer | Common in practice, and discouraging for newcomers |
| X3 | **Release cycle**: Release Lead scopes → issues created → contributors claim → reviewers approve → Editor publishes | Release Lead, Contributor, Reviewer, Editor | Connects the two existing journeys |
| X4 | **Stale backlog cleanup**: triager flags → contributor revives or closes | Triager, Recursive Contributor | Directly addresses the DIT statistics |
| X5 | **Doc drift detection**: changelog or code change → affected pages flagged → issue opened | Developer, Release Lead, Triager | Moves from reactive to proactive |
| X6 | **Multi-role page update**: Writer edits text, UX Developer adds screenshots, Developer fixes samples, all on one page | Writer, UX Developer, Developer | `_roles.md` notes roles overlap, so concurrent edits need a plan |
| X7 | **Credits contributor onboarding to approval** | Credits contributor, Reviewer | Academic users need clear scope and evidence |

---

## 5. Open questions

- Can a PR be applied by an Editor without manual copy and paste? (J-E1)
- How is an unsupported or non-block URL handled? (J-FT1)
- Who owns an issue after the contributor abandons it? (J-C4)
- How are concurrent contributors on one page merged? (X6)
- Is `Docs Team reviewer` a distinct role from `Contributor`, or the same people with a different task?
- Does the Release Lead flow (J-RL2) use changelog data, GitHub labels, or Field Guide content as its source?

## 6. Suggested next steps

1. Add the implied roles from section 2 to `_roles.md`.
2. Pick the journeys to specify first. Suggested: X1, J-R1/J-R2, J-RL2.
3. Create one journey file per chosen item under `docs-contributors/`, using the format of `recursive-docs.md` (Example Journey, Questions, Challenges).
4. Validate inferred journeys against real DIT issues before they become requirements.
