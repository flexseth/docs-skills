# Who You Are

Claude, today you are working as Me. I am @flexseth on GitHub and Slack, I help out with Developer Documentation and am looking at improving ways to make updating WordPress docs more accessible and easy to maintain.

I have extensive experience working with docs, And I'm looking to start creating agents and agent skills to work with documentation. 

# Purpose

The purpose of this project is to read a set of instructions for what is going to be simulated as a lab environment, process those instructions, and complete the lab. It also notates steps taken along the way so that the information can be added as a manifest for creating the agent and agent skill. 

Everything you need is in this project folder, so you should not be accessing any system prompts or anything from memory outside of the root project folder. For this trial run, we are not interested in improving the process so much as documenting the flow of it.

Check the scenarios folder for your [lab work today](../scenarios/).

# What You Do Not Do

**You do not install libraries**, or try to download source code. 

Spec Kit should be provided. If it is not, stop and ask.

You do not go off on tangents researching things, only official sources should be used in this project. Keep the context relevant to the task.

Official sources list: 
- documents linked directly
- Spec Kit
- Claude.ai
- developer.wordpress.org
- The Documentation Issue Tracker (**only** links provided, or directly linked to by the parent issue). Do not go down rabbit holes trying to follow context from link sources on the Issue Tracker.

You do not do things that are not the intended focus of creating the plan for the specification or the lab. 

**You do not attempt to _implement_ the plan.** 
For any reasons. This will burn through my remaining tokens for the month.

# Remove Unused Skills for Claude Code

Before proceeding, remove **all** unnecessary tools for this project.

If you need a tool, **explicitly ask me** before installing it, and state the purpose you will use the tool for. Adding too many skills and tools has bloated my context in the past and I'm tired of it.

# Worktree Organization

Each scenario that the audit agent is evaluated and tested against lives in its own folder:
`scenarios/<issue-number>/<brief-description>.md` (for example, `scenarios/1484/audit-broken-links.md`).

Each scenario is developed on its own branch and worktree, created from `docs-audit`, and merged back when complete.

Scenarios come from the [Documentation Issue Tracker](https://github.com/WordPress/Documentation-Issue-Tracker).

# Interfacing with Spec Kit

Spec Kit "features" are folders, not Git branches. Spec Kit never calls Git, so it works the same in any worktree, and branch names are set by this project's standard, not by Spec Kit:

- **Branch names (standard):** all branches are created from `docs-audit`.
  - **Scenario or lab context** (called from the `scenarios` folder, or working an Issue Tracker scenario): `lab/<issue-number>_<issue-brief-description>`, for example `lab/1484_audit-broken-links`.
  - **Any other context** (natural language from a prompt, another specification, and so on): `feat/<brief-description>`, for example `feat/audit-report-format`.
  - Spec Kit does not create or check Git branches, so neither standard needs to follow its `001-…` pattern.
- **Project root:** Spec Kit finds the project by walking up from the current folder to the nearest `.specify/`. Commit `.specify/` on `docs-audit` (do not gitignore it) so every worktree has its own copy.
- **Current feature:** tracked in `.specify/feature.json` (or the `SPECIFY_FEATURE_DIRECTORY` environment variable), not by branch name. The file is rewritten each time a feature is created and differs per worktree, so it is listed in `.gitignore`.
- **Feature numbering:** `/speckit.specify` numbers new features by scanning only the `specs/` folders in the current worktree, so two worktrees could both create `001-…` and collide on merge.
  - In a lab, pass the issue number and the same brief description as the branch: `--number <issue-number> --short-name <issue-brief-description>`, which creates `specs/<issue-number>-<issue-brief-description>/` (for example, `specs/1484-audit-broken-links/`).
  - Outside a lab there is no issue number, so pass `--timestamp --short-name <brief-description>` to avoid collisions.
- **Local vs. cloud:** specify, clarify, plan, and tasks run locally in the worktree. Push the branch, then open one issue per task for GitHub Copilot's cloud agent. Each issue names the exact files (for example, `specs/1484-audit-broken-links/tasks.md`), because the cloud agent cannot see the gitignored `feature.json`. Copilot's PRs target the `lab/…` or `feat/…` branch, not `docs-audit`.

Source: [Spec Kit](https://github.com/github/spec-kit) README, `scripts/bash/create-new-feature.sh`, and `scripts/bash/common.sh` on `main`, read 2026-11-13. The `specify init` flags and the scripts behind `/speckit.plan` and `/speckit.tasks` were not checked; confirm them with `specify init --help` after installing.

# Deliverable 

You will deliver the files needed to start with Spec Kit, to plan the specification, using Claude's built in skill for creating new skills in Claude Code.

You should become familiar with both spec kit and this relevant skill. 

The agent and skill we are looking to build is a "docs auditor" agent of sorts.

There are specifics on this in the project files. 

# Access and Authorship

You should not commit, push, or override anything unless I explicitly allow you to. 

We want to go through a series of manageable steps that someone can follow to see how the skill was created. 

# PR Flow

We are going to use GitHub Copilot's Agentic Harness on GitHub.com. 

To Copilot: You are to use the least expensive model for this process.

# Filing in Data

For Spec Kit, the Constitution and other files should be either left blank or inferred from meaningful context in this lab session. 

# Spec Driven Development

The resulting process should follow SDD as [outlined in Spec Kit.](https://github.com/github/spec-kit#spec-driven-development)

# Harnesses and Models

For Claude, use Sonnet 4.5 (`claude-sonnet-4-5-20250929`) - solely.

If there is configuration needed to ensure that this is the model used on GitHub.com in their agentic harness, you can research this, and ask me to add the file before proceeding.

**DO NOT USE OPUS WITH COPILOT IN THE GITHUB CLOUD**

In fact, don't use Opus at all for this project.

# Sample Agent

I've created a more exhaustive Docs Agent in Claude for WordPress. Do not copy anything from this file, but some items may be helpful. Such as the tools available and the harness. The advisor/executor stuff is not important for now.
https://github.com/flexseth/Claude-for-WordPress/blob/main/.claude/agents/docs-agent.md

The headings are important for defining agents, so we probably should try to use those and modify them based on provided info for the audit agent.

Do not consume any other file in this repository other than the `docs-agent` file linked above, for any reason.