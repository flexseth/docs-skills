# Plan: `diagram`, a skill combining docs lookup with Mermaid diagrams

## Context

The Gutenberg Reference Guides diagram (`diagram/examples/gutenberg-reference-guides.md`) was built by hand. A Sonnet lookup sub-agent returned an outline, and the main agent wrote the Mermaid itself, because no diagram skill was installed. The goal is a reusable skill that takes the lookup agent's return, or an existing Markdown file, and returns a Mermaid diagram to the terminal by prompt.

This plan is a **specification only**. Nothing is implemented, and no skill is installed.

## Vetting findings so far

- **Skills installed or built in (Anthropic):** `frontend-design` (from `anthropics/claude-plugins-official`, confirmed in `~/.claude/plugins/installed_plugins.json`). The built-ins `artifact-diagramming`, `dataviz` and `skill-creator` are Anthropic skills too. I did not check their vendor locally. None is a Mermaid skill. `artifact-diagramming` targets published artifacts, and `dataviz` targets numeric charts.
- **Mermaid skills:** One web search found no official Anthropic Mermaid skill. It found only community skills from individual GitHub accounts. The owners (the first path segment of each repo, such as `pavelsimo`, `mrgoonie`, `softaworks`) look like individual accounts or small orgs. **This is not verified, because I have not queried GitHub for owner type.** None of their SKILL.md files has been read, so quality and safety are unvetted. Sites such as skills.sh, claudemarketplaces.com and skillselion.com are indexes, not publishers.
- **Skill format:** SKILL.md needs YAML frontmatter with `name` (kebab-case) and `description`. The description drives triggering. This comes from secondary sources and should be checked against `anthropics/skills`.
- **Skill creation:** The session has no `/skill` command. The generation step uses the `skill-creator` skill, which is Anthropic's (`anthropic-skills:skill-creator`).

## Decisions from you

1. An **Opus sub-agent** writes the combined skill, with all of this context passed to it.
2. **Hard gate:** if flowcharts and the other diagram types are not covered, ask you before writing any part of the Markdown file.
3. This is a **specification now**. Include project and global install steps, and a checklist note to **move** the file when it is finalized.

## 1. Vet community skills first (read-only)

- [ ] For each candidate, read `SKILL.md` and every sibling file (`scripts/`, `references/`, hooks, plugin manifests) with `gh api repos/<owner>/<repo>/contents/<path>`. Do not clone into `.claude/`, install via `/plugin`, or run scripts.
- [ ] Record results in `diagram/VETTING-mermaid-skills.md`.

| Check | Hard fail if |
| --- | --- |
| Owner (`gh api users/<owner>`) | Record only. Individual or org raises or lowers the bar. |
| Licence | None: ideas only, no copied text. |
| Last update | Over 12 months old: likely pre-v11 syntax. |
| Network behaviour | Any runtime fetch of instructions. `npx` must be opt-in. |
| Exec behaviour | Broad Bash, hooks, auto-installs. |
| File writes | Writes outside cwd or into `~/.claude`. |
| Prompt hygiene | Injection phrases, hidden Unicode, base64 blobs, telemetry. |
| Quality | Score 0–3 per diagram type. |

Review order (by published name, not by quality):
1. `mrgoonie/claudekit-skills` (mermaidjs-v11)
2. `pavelsimo/skills`
3. `softaworks/agent-toolkit`
4. `dashed/claude-marketplace` (mermaid-cli, so check exec behaviour)
5. `ccheney/robust-skills`
6. `johnlarkin1/claude-code-extensions`, `nickcrew/claude-ctx-plugin`, `hoodini/ai-agents-skills`
7. `jeremylongshore/claude-code-plugins-plus-skills` (class diagrams only)
8. Multi-skill repos: `davila7/claude-code-templates`, `rysweet/amplihack`, `agents365-ai/365-skills`, `oimiragieo/agent-studio`
9. `modu-ai/moai-adk` (uses MCP Playwright, so the largest exec surface)

Rule: borrow only from candidates that pass every hard-fail check and have a permissive licence. Borrowed text goes to the Opus writer as labelled reference material.

## 2. Skill specification

**Name:** `diagram`, invoked as `/diagram` (confirmed).

```
diagram/
  SKILL.md                          # under ~500 lines
  references/diagram-types.md       # per type: when, input shape, v11 example, pitfalls
  references/conventions.md         # labels, escaping, classDefs, direction
  references/lookup-mapping.md      # lookup JSON/outline -> nodes
  examples/gutenberg-reference-guides.mmd
```

**Frontmatter draft** (no `allowed-tools`, so no extra Bash permissions):

```yaml
---
name: diagram
description: >-
  Turn WordPress documentation lookups or existing Markdown files into Mermaid
  v11 diagrams returned in chat as a fenced mermaid code block (optionally saved
  to .md/.mmd or rendered with mmdc). Use when the user asks to diagram, map,
  draw, chart the structure of, or visualize WordPress docs or a Markdown file:
  developer.wordpress.org page hierarchies, component relationships, block API
  or data flows, PHP hook/action sequences, class or database relationships,
  post status transitions. Trigger phrases: "diagram", "mermaid", "flowchart",
  "sequence diagram", "map the docs", "hierarchy of", "visualize the flow",
  "draw how X works", ".mmd". Not for numeric charts (use dataviz) or published
  HTML/SVG artifacts (use artifact-diagramming).
---
```

**SKILL.md sections:** When to use; Normal situations; Inputs; Procedure; Output rules; Do not use for.

**Normal situations:**
- Hierarchy map of a docs section (the Gutenberg case).
- A Markdown file's procedure turned into a flowchart.
- A PHP hook order turned into a sequence diagram.
- A data store or core classes turned into a class diagram.
- Core DB tables turned into an ER diagram.
- Post status turned into a state diagram.
- theme.json keys turned into a mindmap.

**Inputs (one of):**
- (a) Lookup JSON objects or a nested outline (`Title — URL` with children).
- (b) A Markdown file path.
- (c) A plain-language description plus an optional lookup.

**Procedure:**
1. Classify the input shape. Delegate to the lookup sub-agent only if the docs facts are not already in context. Ask it for titles and URLs only.
2. Pick the diagram type from the table below. Ask the user if two types fit equally.
3. Normalise to nodes `{id, label, url, status}` and edges.
4. Apply the mapping rules and conventions.
5. Emit a Source line, the block, then Notes and Sources.
6. Optionally write a file or render.

**Lookup field to diagram mapping:**

| Lookup field or condition | Diagram effect |
| --- | --- |
| `key` | Node label. ID is a lowercase slug. |
| `url` | Relative path as a second label line. The full URL goes in Sources. |
| `matches_request: false` | Class `mismatch`, plus a Notes entry quoting `note`. |
| `truncated: true` | Class `partial`, plus `section_hint` in Notes. |
| Empty `snippet` (failed) | Class `failed`, plus the reason in Notes. |
| Section not enumerated | Class `unfetched` (dashed). It means "not enumerated", not "no children". |
| Over 6 children | One summary node, for example "16 pages: A, B, C, ...". |
| Count disagrees with list | Omit the count and add a Notes entry. |
| Two pages disagree | Prefer the deeper section page and add a Notes entry. |
| `last_modified`, `fetched` | Source line only. |
| `snippet` | Used only to derive edges and participants, never pasted in. |

**Diagram type selection:**

| Input shape | Type |
| --- | --- |
| Tree or outline | `flowchart LR` (or `mindmap` for short labels without URLs) |
| Steps with decisions | `flowchart TD` |
| Ordered calls between actors | `sequenceDiagram` |
| Types with members or relations | `classDiagram` |
| Tables and keys | `erDiagram` |
| Lifecycle or status changes | `stateDiagram-v2` |
| Dated phases | `gantt` |
| Chronological events | `timeline` |
| Config keys or brainstorm | `mindmap` |
| System containers | `flowchart` with subgraphs (`architecture-beta` and C4 opt-in only) |

**Conventions (Mermaid v11):**
- `LR` for hierarchies, `TD` for processes.
- Quote all labels: `id["Label"]`. At most about 40 characters per line and 3 lines per node.
- Escape `"` as `#quot;`. Never use a bare `end` node ID.
- Default depth is 2 below the root. Collapse or split above about 40 nodes.
- Status classes with no fill colours, so light and dark themes both work:
  ```
  classDef unfetched stroke-dasharray: 4 3;
  classDef partial stroke-dasharray: 2 2;
  classDef failed stroke-dasharray: 4 3,stroke:#b32d2e;
  classDef mismatch stroke-width:3px,stroke:#dba617;
  ```
- Flag `-beta` diagram types as unstable. Record "Mermaid v11" in the Source line.

## 3. Opus delegation and the coverage gate

Spawn the writer with `model: opus`. It returns text only and writes no files. Pass it:
- This spec.
- `docs-lookup-agent/docs-lookup-agent.md`, the input contract.
- `diagram/examples/gutenberg-reference-guides.md` and `diagram/examples/HOW-THIS-DIAGRAM-WAS-MADE.md`, the golden example.
- SKILL.md format rules and `skill-creator` conventions.
- Vetted excerpts only, labelled with repo and licence. If nothing passed vetting, pass none.

It returns the SKILL.md text plus the `references/` and `examples/` files, a coverage manifest (JSON, per type: selection rule, mapping, v11 example, pitfalls, source URL or "memory"), and a list of assumptions.

### Hard gate G1: before ANY Markdown file is written (no partial writes)
- [ ] **Flowchart** has: selection rule, lookup mapping, valid v11 example in `LR` and `TD`, subgraphs, the four classDefs, escaping rules, collapse rule.
- [ ] Each of these has a selection rule, input mapping, v11 example and pitfalls:
  - [ ] `sequenceDiagram`
  - [ ] `classDiagram`
  - [ ] `erDiagram`
  - [ ] `stateDiagram-v2`
  - [ ] `gantt`
  - [ ] `mindmap`
  - [ ] `timeline`
- [ ] Optional types (pie, journey, gitGraph, quadrant, xychart-beta, sankey-beta, block-beta, architecture-beta, C4, and others) are either covered or listed as "not covered" in SKILL.md.
- [ ] Every example parses with `mmdc` if it is available. Otherwise it passes the manual lint list below.
- [ ] The frontmatter is valid and the trigger phrases are present.
- [ ] The manifest matches the text, spot-checked by the main agent.

**If any box fails:** stop and ask you with AskUserQuestion, naming the missing types. The options are:
- (a) Send it back to Opus to fill the gaps.
- (b) Accept it with the gaps listed as "not covered".
- (c) Narrow the required tier.
- (d) Borrow from a vetted community skill.

## 4. Delivery to the terminal

- **Default:** a fenced `mermaid` block in chat, plus the Source line, Notes and Sources. The terminal cannot render it, so say once how to view it: GitHub, a VS Code Markdown preview, or mermaid.live.
- **File write:** only when asked. `.md` gets a title, Source line, block, Notes and Sources. `.mmd` gets the raw diagram. Ask before overwriting.
- **Render:** only when asked. Check `command -v mmdc`. If it is absent, do not install it and do not run `npx` without asking, because that downloads Chromium. Offer the install command or mermaid.live. If rendering fails, show stderr, fix, and retry once.

## 5. Installation (when finalized)

- **Draft location now:** `diagram/` on branch `docs/diagrams`. It is not active there.
- **Project install:** `<project-root>/.claude/skills/diagram/SKILL.md`, plus `references/` and `examples/`. Commit it so the team shares it.
- **Global install:** `~/.claude/skills/diagram/SKILL.md`, plus the same subfolders.
- **Lookup dependency:** preferably `.claude/agents/wp-docs-lookup.md` with `model: sonnet`, which the lookup spec lists as an open question. The skill must also work without it, by applying the lookup spec in a general-purpose sub-agent prompt.
- **Check it loaded:** start a new session and ask which skills are available.
- [ ] **When finalized, MOVE the folder** (`git mv`, or `mv` to the install path). Do not copy it. Exactly one copy should exist. If the repo stays the source of truth, symlink `~/.claude/skills/diagram` to it. Update step 3 of "To complete the picture" in `diagram/examples/HOW-THIS-DIAGRAM-WAS-MADE.md`.

## 6. Verification

Run each in a new session after install:
1. **Gutenberg rerun:** "Diagram the hierarchy of the Block Editor Reference Guides." Expect `flowchart LR`, dashed `unfetched` nodes, Block API collapsed, and Notes on disagreements. Compare against `diagram/examples/gutenberg-reference-guides.md`.
2. **Markdown to diagram:** a sequence diagram of `diagram/examples/HOW-THIS-DIAGRAM-WAS-MADE.md`, and a flowchart of the Procedure in `docs-lookup-agent.md`. Neither should trigger a lookup, because both files are local.
3. **Hook flow:** a sequence diagram of how a dynamic block renders on the front end. Every hook shown must appear in the fetched snippets.
4. **Other types:** state (post status), ER (`wp_posts`, `wp_postmeta`), class (`core/block-editor` store), mindmap (theme.json).
5. **Failure path:** a nonexistent page gives a `failed` node and no invented children.
6. **Mismatch:** "where TextArea fits in components" gives a `mismatch` node and a note.
7. **Negative triggers:** "bar chart of plugin downloads" routes to `dataviz`. "What does `register_post_type` do?" gives no diagram.
8. **Delivery:** `.md` and `.mmd` writes, and mmdc present and absent.

Syntax validation:
- `mmdc -i x.mmd -o <scratchpad>/x.svg` exits 0, or paste into mermaid.live.
- Manual lint:
  - The first line is the diagram type.
  - Every label is quoted.
  - No raw `"` inside a label.
  - No bare `end` ID.
  - Every `class` has a `classDef`.
  - One diagram per block.

## Risks and open questions

**Risks**
- Opus may write v11 syntax wrongly from memory, especially for `-beta` types. The mitigations are gate G1 and the mmdc check.
- GitHub's Mermaid renderer can lag and ignores `click` links.
- Community skills may carry licence problems or prompt injection. The vetting step addresses this.
- Triggers may overlap with `artifact-diagramming`, `dataviz` or a later community `mermaid` skill.
- Large docs sections produce unreadable diagrams. The collapse rules address this.
- The lookup spec has no hierarchy return, so outline parsing is ad hoc.

**Open questions** (defaults chosen if you don't say otherwise)
1. Name `diagram`: keep.
2. Required type tier above: keep.
3. May the Opus writer WebFetch mermaid.js.org to check syntax? Default: yes, with citations.
4. Extend `docs-lookup-agent.md` with a hierarchy mode (`children[]`, `children_count`, `enumerated`)? Default: propose it separately.
5. Text-tree preview in chat: off by default.
6. `click` links: skip, since GitHub ignores them.

## Critical files

- `docs-lookup-agent/docs-lookup-agent.md`
- `diagram/examples/gutenberg-reference-guides.md`
- `diagram/examples/HOW-THIS-DIAGRAM-WAS-MADE.md`
- Proposed draft: `diagram/SKILL.md`
