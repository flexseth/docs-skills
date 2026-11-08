# diagram

Status: **specification and worked example. The skill is not implemented yet.**

`/diagram` will turn WordPress documentation lookups, or existing Markdown files, into Mermaid diagrams returned to the terminal as a fenced code block. It can optionally save to `.md` or `.mmd`, or render with `mmdc`.

## Where the project is

- Done: a worked example (a Mermaid hierarchy of the Block Editor Reference Guides), and a plan for the skill.
- Not done: the skill itself, the vetting of community Mermaid skills, and the Opus-written skill text.
- The example is partial. The Components and Packages sections were not enumerated.

## Contents

| Path | What it is |
| --- | --- |
| `PLAN.md` | Implementation plan: vetting of community Mermaid skills, the skill spec (front matter, when to use, diagram-type table, lookup-to-diagram mapping), the Opus delegation with a coverage gate, install steps, verification |
| `examples/gutenberg-reference-guides.md` | Worked example: hierarchy of the Block Editor Reference Guides on developer.wordpress.org, as a Mermaid flowchart |
| `examples/HOW-THIS-DIAGRAM-WAS-MADE.md` | How the lookup agent and a diagram step produced that example, with the prompts and returns |

## Dependency

`/diagram` takes the docs lookup agent's return as input. That agent is specified in `docs-lookup-agent/docs-lookup-agent.md` on the `docs-lookup-agent` branch of this repo (a separate worktree when working locally). It is not part of this branch. Until the two are merged, a calling agent can apply that spec in a sub-agent prompt, as the worked example did.

## Next steps

- [ ] Vet community Mermaid skills (`PLAN.md`, section 1).
- [ ] Have an Opus sub-agent write the skill, then pass the coverage gate (section 3).
- [ ] Move the finished folder to `.claude/skills/diagram/` (project) or `~/.claude/skills/diagram/` (global). Move it, do not copy it (section 5).
