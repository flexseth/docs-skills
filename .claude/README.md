# Claude Code Standards

`.claude` folder standards
https://code.claude.com/docs/en/claude-directory

## Using Skills
https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview

Pre-Built Agent Skills: `BASH`, `grep`, `glop`, `WebFetch`, `AskUserQuestion`, etc. These skills come pre-configured, but can be limited in scope by agent.

Unloading skills:
- [ ] TODO: remove unnecessary skills from context on this agent load (**allow-only**)

## Creating the `lookup` agent skill
- [ ] L1: Create YAML frontmatter for agent (Metadata)
- [ ] L2: Create Instructions (load when triggered)
- [ ] L3: Resources and Code (loaded as needed)
- [ ] Use `Spec Kit` in conjunction with `/skill` command to plan and implement

## The Skills architecture
- [ ] add link for reference

Agent + Skills + Computer
- Core system prompt
- Equipped skills
- Equipped MCP servers (GitHub MCP - read only to restricted domain)

Agent configuration

Use Computer
- Agent virtual machine (tools + filesystem access)
- [ ] define filesystem
- [ ] define tools (allow and deny lists)

Agent Virtual Machine (allowlist of skills/commands)
- Sandboxes work so the agent can securely communicate with Claude Code

**References**
- [Agent Skills Cookbook](https://platform.claude.com/cookbook/skills-notebooks-01-skills-introduction)
- `wp-docs` in [WordPress Agent Skills](https://github.com/WordPress/agent-skills/compare/trunk...add/wp-docs-skill) (starter)