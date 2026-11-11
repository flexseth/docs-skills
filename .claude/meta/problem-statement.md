 # Problem Statement

In version 1 of the `docs-lookup` skill, Opus rarely called the sub-agent because it did not find the need. As a result, evaluations showed nearly identical results, but uncovered a root cause of the v1 spec.

## Version 1.1

The objective in the next pass of the spec, is to make sure the more intelligent model will call the sub-agent for medial tasks such as looking up documentation references.

## Platform Check
 
 > Agents are still supported in claude, right?  
 
 Yes. Subagents are supported in Claude Code. They're defined as Markdown
  files with YAML frontmatter (name, description, optional tools and model)
  in .claude/agents/ for a project, or ~/.claude/agents/ for all your
  projects. Your lookup/.claude/agents/docs-lookup.md is in that format.

  They're also available through the Agent tool, which is listed in this
  session. The tool's instructions say to spawn agents only when asked. Your
  memory notes say that's why Opus rarely delegates to docs-lookup.

  I can't tell from here whether your file's frontmatter is correct. Want me
  to check it against the current docs? The claude-code-guide agent can do
  that.