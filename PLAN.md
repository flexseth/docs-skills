# Plan: `/write`, a skill that writes and fixes documentation

Status: **draft specification. Nothing is implemented.** Branch `docs/write`, folder `write/`.

## Context

`/write` analyzes code or files and creates documentation for them. It can also fix existing documentation. It is aimed at WordPress plugins, blocks and themes, but the procedure works on any code or Markdown.

Two sibling pieces already exist in this repo:
- **Docs lookup agent** (`docs-lookup-agent/docs-lookup-agent.md`, on the `docs-lookup-agent` branch): returns canonical URLs and short snippets from developer.wordpress.org. `/write` uses it to check WordPress API facts before documenting them.
- **`/diagram`** (`diagram/PLAN.md`, on the `docs/diagrams` branch): turns structure into Mermaid. `/write` can offer a diagram when a document describes structure or flow.

## What it does

| Mode | Input | Output |
| --- | --- | --- |
| **Document** | A file, a directory, or a git diff | New docs: README, file or class docs, function docs, usage guide, changelog entry |
| **Fix** | An existing doc, plus the code it describes | A findings list, then patches: drift from the code, wrong or deprecated API claims, broken links, missing sections, unclear wording |
| **Review** (read-only) | Docs, code, or both | The findings list only, with no patches |

What it can produce, by target:
- **PHP:** docblocks in WordPress PHP documentation standards (summary, `@param`, `@return`, `@since`, hooks).
- **JavaScript:** JSDoc in WordPress JavaScript documentation standards.
- **Blocks:** a block README covering `block.json` fields, attributes, supports and the `edit.js`, `save.js` and `view.js` split.
- **Plugins and themes:** README.md, and `readme.txt` for the WordPress.org plugin repository.
- **Markdown files:** structure, accuracy, links, consistency.

## Frontmatter draft

```yaml
---
name: write
description: >-
  Analyze code or files and write documentation for them, or fix existing
  documentation. Use when the user asks to document, write docs for, add
  docblocks or JSDoc, write or update a README or readme.txt, explain how a
  plugin, block, theme or module works in writing, or fix, update, proofread
  or audit existing docs against the code. Trigger phrases: "document this",
  "write docs", "docblock", "JSDoc", "README", "readme.txt", "fix the docs",
  "docs are out of date", "review the documentation". Not for diagrams (use
  /diagram) or for answering a quick question about code.
---
```

## When to use it, and when not

Normal situations:
- A new block or plugin has no README.
- A function or class has no docblock.
- Code changed and the docs no longer match it.
- A README promises features the code does not have.
- A `readme.txt` needs checking before submission to the WordPress.org plugin repository.

Do not use it for:
- Diagrams (`/diagram`).
- A one-line explanation, where the answer belongs in chat.
- Writing marketing copy.
- Editing generated files or vendored code (`node_modules/`, `vendor/`, `build/`).

## Procedure

1. **Scope (read-only).** Resolve the target: a file, a directory, or `git diff`. Detect the kind: PHP, JS or React, `block.json`, plugin header, theme, or Markdown. List the files read. Skip generated and vendored paths.
2. **Choose the mode.** Document, Fix or Review. If it is unclear whether the user wants Fix or Review, ask.
3. **Gather facts from the code.** Signatures, parameters, return types, hooks fired or consumed, attributes, defaults, file layout, entry points, dependencies. Everything documented must come from the files read.
4. **Verify WordPress claims.** If the doc will state something about a WordPress, Gutenberg or `@wordpress/*` API (a signature, a version, a deprecation), delegate to the docs lookup agent. Use its return fields: `url` to cite, `last_modified` and `fetched` to date it, `matches_request` to catch a mismatch, `truncated` and `section_hint` to fetch the rest. If the lookup fails or is partial, write "unverified" in the doc and tell the user. Never fill the gap from memory.
5. **Draft.** Match the existing doc's voice and structure when fixing. Use the target's own standard (PHP docblock, JSDoc, README sections).
6. **Preview.** Show a diff or the new file text. Mark each unknown as `TODO(owner): <what is missing>`.
7. **Ask, then write.** Nothing is written until the user approves the diff. Write one file at a time unless the user approves a batch.
8. **Report.** List files changed, claims verified with URLs, claims left unverified, and anything skipped.

## Fix mode: what it checks

| Check | How |
| --- | --- |
| Drift | Compare each claim in the doc (names, parameters, defaults, file paths, commands) to the code. |
| Deprecated or wrong API claims | Verify through the lookup agent. |
| Broken links | Check relative links against the file tree. Check external links through the lookup agent, not guesswork. |
| Missing sections | Compare against the target's standard section list. |
| Unclear or inconsistent wording | Flag it and propose a rewrite, without changing meaning. |
| Stale examples | Check that code samples match current signatures. |

Output a findings table first (location, problem, evidence, proposed fix). Patches come only after the user picks which findings to apply.

## Accuracy and safety rules

- Document only what the code or a verified lookup shows. No invented parameters, versions, defaults or behaviour.
- Mark unknowns with `TODO`, never guess.
- Cite lookup URLs for WordPress API claims.
- Preserve existing text that is still correct. A fix changes as little as it can.
- Never write secrets, tokens or personal data into docs. If the code contains any, report it and leave it out.
- Dry run by default. No file is changed without approval.
- Explanations go in `.md` files, not in code comments beyond docblocks.

## Integration

- **Docs lookup agent:** input contract is its JSON return (`key`, `url`, `matches_request`, `note`, `last_modified`, `fetched`, `snippet`, `truncated`, `section_hint`). It must work without the agent installed: the main agent applies the lookup spec in a general-purpose sub-agent prompt, as the `/diagram` example did.
- **`/diagram`:** when a doc describes a hierarchy, a hook order or a data flow, offer to add a Mermaid block through `/diagram`. Never convert an existing Mermaid block.
- **Existing skills in this session:** the `docs` skill (`anthropic-skills:docs`) is for Claude Docs documents, and `init` generates a CLAUDE.md. Neither writes code documentation. Do not duplicate them, and keep the trigger phrases distinct.

## Vetting before borrowing

Community documentation skills exist, but none have been read for this plan. Before borrowing from one, follow the same read-only vetting as `diagram/PLAN.md` section 1: read `SKILL.md` and all siblings, check owner, licence, last update, network and exec behaviour, file writes and prompt hygiene. Do not clone into a `.claude/` folder or install it.

## Writing the skill

1. An Opus sub-agent writes the skill text, given this plan, the lookup agent spec and the `diagram` plan.
2. **Coverage gate:** before any file is written, check that the draft covers every item below. If one is missing, ask the user before writing.
   - [ ] Document mode for PHP, JS, block, plugin or theme, and Markdown targets
   - [ ] Fix mode with all six checks above
   - [ ] Review mode, read-only
   - [ ] The preview-then-approve flow, with no writes before approval
   - [ ] The lookup-agent contract and the unverified fallback
   - [ ] Unknown-detail handling (`TODO`)
   - [ ] Valid frontmatter with trigger phrases and a "not for" clause
3. Use the `skill-creator` skill to scaffold the SKILL.md and check its format.

Draft layout:

```
write/
  PLAN.md
  SKILL.md
  references/modes.md           # document, fix, review: steps and outputs
  references/targets.md         # PHP, JS, block, plugin, theme, Markdown: standard sections
  references/lookup-contract.md # lookup JSON fields and how each is used
  examples/                     # before and after fixes, sanitised
```

## Installation (when finalized)

- **Project:** `<project-root>/.claude/skills/write/SKILL.md`, plus `references/` and `examples/`. Commit it so the team shares it.
- **Global:** `~/.claude/skills/write/SKILL.md`, plus the same subfolders.
- **Check it loaded:** start a new session and ask which skills are available.
- [ ] **When finalized, move the folder to its install path. Do not copy it.** Exactly one copy should exist. If this repo stays the source of truth, symlink the global path to it.

## Verification

Run each in a new session after install:
1. **Document a block:** point it at a `create-block` project. Expect a README matching `block.json`, with no invented attributes.
2. **Document PHP:** a file with undocumented functions. Expect docblocks whose `@param` and `@return` match the code. `@since` comes from the user or is marked `TODO`.
3. **Fix drift:** a README with a renamed function and a wrong default seeded in. Expect both found, with evidence, and patches only after approval.
4. **Verify an API claim:** a doc that cites a deprecated Gutenberg API. Expect a lookup, a URL, and a corrected claim, or "unverified" if the lookup fails.
5. **Review mode:** expect findings and no file changes.
6. **No writes without approval:** decline the diff and confirm that nothing changed (`git status` clean).
7. **Generated paths:** run it on a project with `node_modules/` and `build/`. Expect them skipped.
8. **Negative triggers:** "draw a diagram of this" routes to `/diagram`. "What does this function do?" answers in chat with no skill.

## Risks and open questions

**Risks**
- The model may fill gaps from memory. Mitigations: the `TODO` rule, the lookup requirement, and the verification tests.
- Large directories may overflow context. Mitigation: scope first, then work file by file.
- Triggers may overlap with other documentation skills. Mitigation: specific trigger phrases and the negative tests.
- The lookup agent is still a draft spec, so the contract may change.

**Open questions** (proposed defaults in brackets)
1. Which documentation standards should it follow for PHP and JS? [The public WordPress PHP and JavaScript documentation standards. Verify the current versions through the lookup agent before writing them into the skill.]
2. Should it support `readme.txt` for the WordPress.org plugin repository in the first version? [Yes.]
3. Should Review mode be separate from Fix, or a flag? [A flag on Fix.]
4. Should it ever write without a preview? [No.]
5. Should it add a changelog entry automatically? [Only when asked.]
