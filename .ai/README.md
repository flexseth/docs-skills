# Agent Orchestration

This directory contains AI agents that process GitHub Issues from the [Documentation Issue Tracker](https://github.com/WordPress/Documentation-Issue-Tracker/issues). Agents work in a pipeline: each receives a shared set of variables derived from one or more issues, and uses them to perform its task.

## How It Works

A GitHub Issue enters the pipeline as a URL. The first agent in the chain (typically the meta agent) fetches the issue data and populates a shared variable set, stored as `.json` data. Downstream agents read those variables to perform their own work — generating reports, running audits, consulting on content, or any other task. Some agents may update or enrich variable values in transit before passing them along; others simply consume them.

## Requirements

- GitHub CLI (`gh`) must be installed and authenticated for agents to retrieve issue data. See [installing `gh`](./assets/references/requirements.md#installation-github-cli) and [authentication](./assets/references/requirements.md#authentication) for details.
- Claude Code must be used for AI processing within the agent pipeline. See [Claude Code requirements](./assets/references/requirements.md#claude-code) for details.

# GitHub Issue Common Context

Variables below are part of the shared context that flows through the agent pipeline. The first agent populates these values from a GitHub Issue (via `gh`). Each downstream agent receives this same structure and may reference any variable it needs. Other agents may add new variables as required for their specific tasks.

## Variable Standard

- Variable Names

`{{ VARIABLE_NAME }}` : Description of the variable and its expected format.

- Variable Formats

Variables may be of different types, such as `string`, `number`, `array`, `boolean`, `null`, `object`, or `enum`, depending on the nature of the data they represent.

```json
{
    "{{ISSUE_URL}}": "https://github.com/org/repo/issues/1",
    "{{ISSUE_TITLE}}": "Issue title",
    "{{ISSUE_LABELS}}": ["label1", "label2"],
    "{{ISSUE_ASSIGNEES}}": ["assignee1", "assignee2"],
    "{{ISSUE_COMMENTS}}": 5,
    "{{ISSUE_CREATED_AT}}": "2024-06-01T12:00:00Z",
    "{{ISSUE_UPDATED_AT}}": "2024-06-02T12:00:00Z",
    "{{ISSUE_STATE}}": "OPEN",
    "{{ISSUE_CLOSED_AT}}": null,
    "{{ISSUE_NUMBER}}": 1,
    "{{ISSUE_REPORTER}}": "reporter",
    "{{ISSUE_BRIEF_DESCRIPTION}}": "issue-brief-description",
    "{{ISSUE_ROUND_TRIP}}": null,
    "{{ISSUE_BRIEF}}": "Quoted summary from body",
    "{{CANONICAL_URL}}": "https://example.com/page-with-issue"
}
```

## Populate Variables from GitHub Issue

Using the GitHub CLI is the preferred way to work with agent skills in this project.

## Retrieve issue data with `gh`, falling back to WebFetch

Try the GitHub CLI first to pull issue data, for example:

```bash
gh issue view <issue-number> --repo WordPress/Documentation-Issue-Tracker --json number,title,author,state,createdAt,closedAt,url,body
```

`gh` returns structured JSON directly, so it is quicker and less expensive than fetching and reading the issue's web page with WebFetch.

Many developers already use `gh`, so it is a good option for them. Other contributors may be using AI tools only, without `gh` installed. If `gh` is not available (not installed, or not signed in), use WebFetch on the issue URL instead.

To best use the AI agents and skills in this repository, use the `gh` CLI for retrieving issue data whenever possible.

Benefits: 
- Variable data is handled in the same format as returned by the GitHub API, ensuring consistency and reducing parsing errors.
- Reduces the need for custom parsing logic in AI agents and skills.
- Respond to issues programmatically using the retrieved data, enabling automated workflows and integrations.

**To do:** add `gh` to the allowed tools, so issue lookups run without a permission prompt. Scope it to read-only issue commands, for example `Bash(gh issue view:*)` in `permissions.allow` in `.claude/settings.json`, depending on the agent. Most agents will not write back directly to GitHub - as this is part of the human review process. 

- If `gh` is not available, WebFetch can be used as a fallback to retrieve issue data from the issue URL.

## Output

The variable set is persisted as a `.json` file. Agents that only consume the variables produce their own output (reports, audits, recommendations) without modifying the file. Agents that enrich or correct values write them back to the same `.json` file so subsequent agents in the chain see the updated context.
- [ ] **Todo** Discussion: Will this confuse users if they have changes in their file browser or IDE that are not yet reflected in the persisted `.json` file? Could this lead to conflicts or unexpected behavior when agents read and write the `.json` file? Consider adding guidance or mechanisms to handle such scenarios.

**File output**
Each agent's output will be in the form of files, making it easier on end-user to understand what the agents are doing.

The file name, type, purpose, and content will be clearly indicated to help users understand the context and relevance of each output file.

Reports should be returned as Markdown, with clear headings, sections, and formatting to enhance readability and comprehension.

Final outputs should have actionable steps, clearly indicating what actions need to be taken by the user or subsequent agents.

Calling agent model names and versions should be indicated at the top of the file to provide context on which models generated the output.

If the agent hands the file off to another agent or model, that should be indicated in a trailer in the file.

## CHANGELOG

## 2026-11-14
- Initial creation of the AI agents documentation for handling GitHub issues.