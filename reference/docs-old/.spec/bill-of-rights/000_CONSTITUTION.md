# Constitution

Project Principles (The Non-Negotiables)

The Bill of Rights, An Index of Files Governing this Specification

- 000_CONSTITUTION:     Constitution - You are here.
- 001_ignore:           Ignore These Files
- 002_purpose:          Purpose
- 003_what:             About This Project
- 004_why:              The Need
- 005_how:              The Scope

## Technical Writing Standards

**important**

Use Sonnet 4.5 for technical writing. The command to switch is `model claude-sonnet-4-5-20250929`.

When I say "technical writing" - I mean
- explaining the project
- README and other informational files
- forward facing assets for the presentation
- other generated content

**writing standards**

See `./technical-writing-standards/` for detailed guidelines on technical writing within this project.

You should include the [WordPress Brand Style Guide](./technical-writing-standards/wordpress-brand-style-guide.md) as an outline in your memory, and consult back to it section-by-section as needed.


## Purpose

The purpose of this project is to provide round-trip editing of block markup via the Gutenberg Block Editor.

A user should be able to create and edit block markup, and propose changes to documentation for a given URL as block markup.

## The Source of Truth

**Block Markup** is the format for content used across this project. Block Markup is content format from the source of truth.

Under no circumstances should **content for the WordPress editor** be interpreted, attempted to be fetched, created or consumed as any other format. 

AI agents can fetch and use Markdown, but this should be limited to creating the specification and implementation plan, and for reference purposes only.

This includes Markdown. All content must be handled exclusively in its block markup form within the Playground instance, via the Gutenberg Block Editor.

For AI agents: We are not to interpret, fetch, create, or consume content in any format other than block markup.

For a given post, GitHub is the source of truth. This content will be generated on-the-fly and edited using the Playground instance.

Using PR comments and the PR review process, the original contributor to the PR will be able to use round trip editing in the block editor to push changes of the block markup to GitHub.

Users will then be able to see the most recent, up to date block editor content in their Playground instance.

# Project Infrastructure

## Working with Images

Images should be included as part of the block content and referenced appropriately within the Playground instance. If a new image is added via the Media Library, it should be included with the playground instance in the appropriate folder and referenced correctly in the block markup.

Images should **NEVER BE HOTLINKED** - they should always be served via the playground instance.

## Spelling and Grammar

Harper should be included via the `playground-cli` blueprint installation steps, and should be downloaded and installed via a zip file. 

The Harper WordPress Plugin should be used to check spelling and grammar in the Block Editor. 

## External Dependencies

Should not be used for any reason. Because the purpose of this project is merely content related, under no circumstances should any `NPM` packages or other external dependencies be installed or included during the setup or during roundtrip editing in the Playground instance.

## Translation Support

TBD: This should be discussed in when creating the specification using Spec Kit. 

## Theme Specifics Don't Matter

We are in no way concerned with CSS, how the WordPress.org theme is set up, or how the user's environment is set up, because we will be defining the playground.

Under no circumstances should any compute be used thinking about CSS or any style related matters. Only the block content matters.

No part of the specification should try to improve the theme, suggest new features for the theme, modify the underlying functionality or otherwise be concerned about how the website "works" or "looks." We will explicity specificy that, and iterate later if needed.

The relevant theme, however, should be included as a feature for this project down the road. The relevant theme will relate to whether the docs live on WordPress.org or developer.wordpress.org, ensuring that the documentation is displayed correctly within the context of the appropriate theme. Please do not worry about this, except making it a feature on a checklist - do not attempt to ideate or implement it at the specification stage. If it seems like we want to work on the theme via a feature, ask if we should update the constitution at that time.

## Compute Spend During Project Initialization, During Round Trip Editing
- Keep compute low - use `wp-cli` to import raw block markup
- Do not Import Theme Unit Testing data or attempt to run large scale test suites for WordPress
- Use the same content for each item: Heading, List, Paragraph, etc - standardize text

## 001 - Purpose

Docs drift, and updating the documentation on wordpress.org or developer.wordpress.org (IE: "HelpHub" or "DevHub", respectively) requires `Editor` role or better to do. This means that small docs errors such as spelling or grammar, updating user interfaces, adding code samples and more require a rigirous and often impossible review process.

See infographic for more info on how this is currently bottlenecked. 

This project proposes a way to get the content of a documentation page, as **raw block markup**, which can be brought into a Playground instance and edited. 

The user can then make the necessary modifications, and submit a PR for review by the Docs Team, which can be reviewed and then assigned to an editor to make the update. 

Reducing the existing backlog and improving flow as WordPress versions (and interfaces) change, making docs updates a necessary requirement.

## 002 - Why?

Edit access to the .org properties is highly guarded. But if we could have a flow that took the friction away, by allowing edits on a sandboxed environment, and then relayed for review, documentation can get updated easier with a new process flow.

Additionally, modern tools such as Harper and the WordPress AI plugin, the WordPress Style Guide, and other intelligent documentation toolkits can be added to the flow over time. Creating a standalone mechanism to update docs without disrupting existing workflows. 

This ensures that documentation remains accurate, up-to-date, and easily maintainable while leveraging the latest technological advancements.

## 002 - How

We will use the Playground Skill from WordPress Agent Skills to build out the blueprint creation process. The Playground CLI should facilitate the import and export of raw block markup, allowing users to seamlessly bring documentation content into the sandboxed environment for editing - via the `Playground CLI`.

## 003 - THE MVP

The Minimum Viable Product (MVP) for this project includes:
- A mechanism to extract raw block markup from a documentation page.
- A Playground instance where the extracted content can be edited.
- A workflow to submit changes for review by the Docs Team.
- Assignment of an editor to finalize and update the documentation.
- Integration with AI tools like Harper and the WordPress AI plugin for enhanced editing capabilities.

### AI Disclosure
- Any commits or steps produced with the help of AI should be clearly disclosed in the commit message or documentation. This includes Spec Kit and it's involvement in the project.
- Make sure to append at the end of each issue, commit, or message, which agent or agents participated. 
- Indicate the model which was used, including the version in the documentation or commit message.
- Include human participation in all steps where AI is involved, and clearly indicate the nature of the human contribution.

#### AI Roles - Claude

**Claude Code**

- Use Claude Sonnet for research and planning. 
- Opus should **only** be used in implementation tasks if the nature of the problem is hard to solve.

### AI Provided Files for Context

Do not use ANY files in a project unless explicitly provided or approved for use within the context of the project.

The file structure should be carefully laid out in the primary project `README.md` file. Do not go searching through folders looking for extra context in this project, as these files may be for draft purposes only. Don't assume an entire folder is for your review unless it is explicity provided or approved for use.

#### Technical Writing
- Adhere to the WordPress Style Guide and the Documentation Team Style Guide **AS IF IT WERE YOUR BIBLE.**

## Specification Questions

Items marked as todo in Markdown are represented as: `- [ ]`

Unless otherwise noted, it is to be assumed that Spec Kit should request more information and iterate on these tasks until they are marked as complete and removed as todo items.