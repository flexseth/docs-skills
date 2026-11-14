# New Features

Ideas to build into the audit agent and its labs. Each item can be reworked into a feature later.

## Issue data from the Issue Tracker

Populate scenario variables from the issue automatically, instead of entering them by hand.

- **Issue number and brief description:** `{{ISSUE_NUMBER}}` from the issue number, `{{ISSUE_BRIEF_DESCRIPTION}}` from the issue title. These also give the lab branch and scenario file names.
- **Issue reporter:** `{{ISSUE_REPORTER}}`, shown in the scenario as "Issue reported by `@{{ISSUE_REPORTER}}`".
- **Issue date:** when the issue was opened. Once the issue is closed, check the close date back into the lab to show the round-trip time for the issue.
- **Build the issue header with a script:** compute the "Opened" line from the issue's `state`, `createdAt`, and `closedAt`, adding the Closed and Round trip parts only when the issue is closed. This replaces the rule the model follows today, so the result no longer depends on the model.
- **More fields later:** other issue details can be added as they become useful.

### Retrieve issue data with `gh`, not WebFetch

Use the GitHub CLI to pull issue data, for example:

```bash
gh issue view <issue-number> --repo WordPress/Documentation-Issue-Tracker --json number,title,author,state,createdAt,closedAt,url,body
```

`gh` returns structured JSON directly, so it is quicker and less expensive than fetching and reading the issue's web page with WebFetch.

## Issue audit template

An all-purpose issue audit template, built from the general parts of the scenarios, with the issue variables filled in automatically.

## Templates for Spec Kit

Templates Spec Kit can build from, to include in the future. They will be used when scaffolding new agents and skills with Spec Kit and Claude.

### Content pulled automatically from the GitHub issue

Templates can fill these variables from the issue with `gh issue view` (see [Retrieve issue data with `gh`, not WebFetch](#retrieve-issue-data-with-gh-not-webfetch)):

| Variable | From the issue |
|---|---|
| `{{ISSUE_NUMBER}}` | `number` |
| `{{ISSUE_URL}}` | `url` |
| `{{ISSUE_TITLE}}` | `title` |
| `{{ISSUE_BRIEF_DESCRIPTION}}` | `title`, shortened to a kebab-case slug |
| `{{ISSUE_REPORTER}}` | `author.login` |
| `{{ISSUE_STATE}}` | `state` (`OPEN` or `CLOSED`) |
| `{{ISSUE_CREATED_AT}}` | `createdAt` |
| `{{ISSUE_CLOSED_AT}}` | `closedAt`, only when closed |
| `{{ISSUE_ROUND_TRIP}}` | days from `createdAt` to `closedAt`, only when closed |
| `{{ISSUE_BRIEF}}` | `body`, the quoted summary |
| `{{CANONICAL_URL}}` | `body`, the line after the "URL of the Page with the Issue" heading |
