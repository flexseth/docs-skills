# Docs Meta Agent

When invoked, the Meta Agent retrieves the relevant context from the GitHub issue and returns it to the calling process.

## Input

Given an issue URL, map issue values to the variable.

Use `gh issue view --json` if the GitHub MCP Adapter is available, if not use WebFetch.

| Issue value | Variable | `gh` JSON field |
|---|---|---|
| Issue URL | `{{ISSUE_URL}}` | `url` |
| Issue title | `{{ISSUE_TITLE}}` | `title` |
| Issue labels | `{{ISSUE_LABELS}}` | `labels` |
| Issue assignees | `{{ISSUE_ASSIGNEES}}` | `assignees` |
| Issue comments | `{{ISSUE_COMMENTS}}` | `comments` |
| Issue created at | `{{ISSUE_CREATED_AT}}` | `createdAt` |
| Issue updated at | `{{ISSUE_UPDATED_AT}}` | `updatedAt` |
| Issue state | `{{ISSUE_STATE}}` | `state` (`OPEN` or `CLOSED`) |
| Issue closed at | `{{ISSUE_CLOSED_AT}}` | `closedAt`, only when closed |

### Issue Template

For the Documentation Issue Tracker, additional values can be retrieved or derived from the issue, via the [issue template](./issue-template.md).

| Issue value | Variable | Source |
|---|---|---|
| Issue number | `{{ISSUE_NUMBER}}` | `number` |
| Issue reporter | `{{ISSUE_REPORTER}}` | `author.login` |
| Issue brief description | `{{ISSUE_BRIEF_DESCRIPTION}}` | `title`, shortened to a kebab-case slug |
| Issue round trip | `{{ISSUE_ROUND_TRIP}}` | Days from `createdAt` to `closedAt`, only when closed |
| Issue brief | `{{ISSUE_BRIEF}}` | Quoted summary from `body` |
| Canonical URL | `{{CANONICAL_URL}}` | `body`, the line after the "URL of the Page with the Issue" heading |

see: the provided [Issue Template](./issue-template.md) from the Issue Tracker

## Output

Returns: 
