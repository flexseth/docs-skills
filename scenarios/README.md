# Scenarios

Each scenario is a lab that the audit agent is evaluated and tested against. Scenarios come from the [Documentation Issue Tracker](https://github.com/WordPress/Documentation-Issue-Tracker).

## Adding a scenario

1. From `docs-audit`, create a branch and worktree named `lab/<issue-number>_<issue-brief-description>`, for example `lab/1484_audit-broken-links`.
2. Add the scenario at `scenarios/<issue-number>_<issue-brief-description>.md`, for example `scenarios/1484_audit-broken-links.md`.
3. Run the lab in that worktree. Spec Kit output goes in `specs/<issue-number>-<issue-brief-description>/`.
4. Merge the branch back into `docs-audit` when the lab is complete.

Links from a scenario to the agent files start with `../`, for example `../.claude/agents/audit-agent.md`.

See [`.claude/CLAUDE.md`](../.claude/CLAUDE.md) for the full branch and Spec Kit conventions.
