# Scenario - Check and Resolve Broken Links

Issue reported by `@{{ISSUE_REPORTER}}`

Opened {{ISSUE_CREATED_AT}}
<!-- If {{ISSUE_STATE}} is CLOSED, append " · Closed {{ISSUE_CLOSED_AT}} · Round trip {{ISSUE_ROUND_TRIP}} days" to the line above. If OPEN, leave the line as it is. -->

Lab 1484 is to audit links to see if they are broken or linking to relevant files, to see if the content has been updated. It relates to an issue on the Documentation Issue Tracker, where we try to fix broken documentation for WordPress.

In this instance, we want the audit agent to review a documentation page broken links, The audit agent does not exist, so we are going to use this scenario to save knowledge as findings for agent and skill specification.

We are building the agent frontmatter for requests that could come in, as they were described in via natural language. We are also working on parameters the agent will use when processing data. 

We are also assessing the content for broken links.

This lab has three steps:
1) Discover agent and skills material
2) Perform the audit
3) Create the agent

## Variables

| Variable | Type | How Claude finds it | Saved to |
|---|---|---|---|
| `{{ISSUE_URL}}` | URL | Given in this scenario, under "Build Audit Inventory" | spec |
| `{{ISSUE_BRIEF}}` | string | The quoted summary in the issue body | spec |
| `{{ISSUE_REPORTER}}` | GitHub username | The issue's author (`author.login` from `gh issue view`), filled in at runtime | scenario, spec |
| `{{ISSUE_CREATED_AT}}` | date (YYYY-MM-DD) | The issue's `createdAt` from `gh issue view`, filled in at runtime | scenario, spec, report |
| `{{ISSUE_STATE}}` | `OPEN` or `CLOSED` | The issue's `state` from `gh issue view`, filled in at runtime | scenario, report |
| `{{ISSUE_CLOSED_AT}}` | date (YYYY-MM-DD) | The issue's `closedAt` from `gh issue view`. Only when `{{ISSUE_STATE}}` is `CLOSED`; see the rule under the "Opened" line. | scenario, report |
| `{{ISSUE_ROUND_TRIP}}` | number of days | Days from `{{ISSUE_CREATED_AT}}` to `{{ISSUE_CLOSED_AT}}`. Only when `{{ISSUE_STATE}}` is `CLOSED`. | scenario, report |
| `{{SECTION_HEADING}}` | string | The "URL of the Page with the Issue" heading in the issue body; a marker only | not saved |
| `{{CANONICAL_URL}}` | URL | The line after `{{SECTION_HEADING}}` | spec, report |
| `{{LINKS}}` | list of URLs | Every link on `{{CANONICAL_URL}}` | report (inventory JSON) |
| `{{LINK}}` | URL | One item from `{{LINKS}}`, while looping | report (inventory JSON) |
| `{{RESOLVED_URL}}` | URL | Where `{{LINK}}` ends up after redirects | report (inventory JSON) |
| `{{ANCHOR_TEXT}}` | string | The link text of `{{LINK}}` on `{{CANONICAL_URL}}` | report (inventory JSON) |
| `{{DESTINATION_SUMMARY}}` | string | A summary of the page at `{{RESOLVED_URL}}` | report (inventory JSON) |
| `{{AUDIT_REASON}}` | string, ≤ 2 sentences, ≤ 30 words | Written from this scenario | agent frontmatter |
| `{{EVAL}}` | JavaScript | Written from this scenario | evals |
| `{{AUDIT_FRONTMATTER_HOOKS}}` | Markdown | Drafted from this scenario: agent frontmatter, including `{{AUDIT_REASON}}` and the requests the agent should respond to | spec, for review |
| `{{AUDIT_RESOLVE}}` | Markdown | Every `{{LINK}}` that does not resolve directly, with its status (redirected, not found, error) and `{{RESOLVED_URL}}` if any | report file |
| `{{AUDIT_SUMMARY_REPORT}}` | Markdown table | Link counts per status and the confidence factor for `{{CANONICAL_URL}}` | report file, and shown in the terminal |

## Step 1 Deliverable - Agent Skill Spec

**Frontmatter**
- [ ] Draft `{{AUDIT_FRONTMATTER_HOOKS}}` for review

{{AUDIT_REASON}}: (string) Frontmatter (written as prose, no more than two sentences, and summarized to at most and 30 words). Agent frontmatter should be no more than 150 words, so we are adhering to part of that standard with this scenario's summary.

**Evals**
{{EVAL}}: A JavaScript evaluation scenario to see if the audit works, given this scenario.

**Findings**: `{{AUDIT_FRONTMATTER_HOOKS}}`, `{{AUDIT_RESOLVE}}`, and `{{AUDIT_SUMMARY_REPORT}}`, kept as reference for skill tracing.

## Step 2 Deliverable - Process Audit Given Issue Context

**discussion**
- [ ] Todo: 
    - Specify: Should `discover-audit` be a helper function?
    - Specify: Should `process-audit` be a helper function?
    - Specify: Should `evaluate-audit` be a helper function?

Search the provided {{ISSUE_URL}} from the top, at most reading 20 lines.
Fill in context with the findings below:

### Build Audit Inventory

Potential helper function

`process-audit`:

{{ISSUE_URL}}
https://github.com/WordPress/Documentation-Issue-Tracker/issues/1484

{{ISSUE_BRIEF}}
> Seems like most of the links in this articles are missing

#### Find Canonical
Audit Discovery: Find the resource link, a potential `docs-skills` agent skill.

`find-canonical` - determine the doc to reference

Returns the **correct** document based on request from user or model.

A potential skill of the `docs-lookup` agent.

#### Available Context from Issue body

In the provided content, you will find:

{{SECTION_HEADING}}
"URL of the Page with the Issue"
{{CANONICAL_URL}}

Do not save the {{SECTION_HEADING}}, it is merely a page marker.

#### Discovery Findings

at this point, you should know the context of the doc audit, and which Canonical Document on either 

### Perform Audit

#### Inventory Structure
- Discover an inventory of {{LINKS}} used in the {{CANONICAL_URL}}
- Represent the inventory as a JSON object with a `canonicalUrl` and a `links` array. Include one record per unique source URL; group repeated appearances in `occurrences`.
- Set `resolutionStatus` to `same`, `redirected`, `not_found`, or `error`. Use `null` when the resolved URL, description, or relevance has not been determined.
```json
{
    "canonicalUrl": "{{CANONICAL_URL}}",
    "links": [
        {
            "sourceUrl": "{{LINK}}",
            "resolvedUrl": "{{LINK}}",
            "resolutionStatus": "same",
            "httpStatus": 200,
            "redirectChain": [],
            "occurrences": [
                {
                    "anchorText": "{{ANCHOR_TEXT}}"
                }
            ],
            "description": "{{DESTINATION_SUMMARY}}",
            "isRelevant": null
        },
        {
            "sourceUrl": "{{LINK}}",
            "resolvedUrl": "{{RESOLVED_URL}}",
            "resolutionStatus": "redirected",
            "httpStatus": 200,
            "redirectChain": [
                "{{LINK}}",
                "{{RESOLVED_URL}}"
            ],
            "occurrences": [],
            "description": "{{DESTINATION_SUMMARY}}",
            "isRelevant": null
        },
        {
            "sourceUrl": "{{LINK}}",
            "resolvedUrl": null,
            "resolutionStatus": "not_found",
            "httpStatus": 404,
            "redirectChain": [],
            "occurrences": [],
            "description": null,
            "isRelevant": null
        }
    ]
}
```

#### Audit Existing Canonical Document

- Fetch the {{LINK}}, one by one from {{LINKS}}
- **Resolves** For each page, does the page redirect (R) or 404 (404)?
- **Forward** Any pages that do not resolve directly, add to `{{AUDIT_RESOLVE}}`
- For each page that does resolve, examine the first 50 lines of the page
- Does the page content seem to match the referring link?
- Display a confidence factor of overall accuracy on the canonical page

# Reporting

## Summary of Audit Findings
Format output as a table:

- Fill in `{{AUDIT_SUMMARY_REPORT}}` 
- Fill in `{{AUDIT_RESOLVE}}` 

Display `{{AUDIT_SUMMARY_REPORT}}` in the TUI, with links to the report files.

## Agent Planning Guide

Given what you have learned in this process, research the Agent creationg process for Claude Code. This will be the

## Step 3 - Create the Agent

This step will be completed in a separate branch/PR.

Docs agent implementation plan (template)

**Resources**
- [Plan Audit Agent](../.claude/agents/PLAN_audit-agent.md) - fill with context from findings of the run on this audit
- [Audit Agent](../.claude/agents/audit-agent.md)