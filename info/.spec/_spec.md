## Why Using Playground Makes Sense
- it's load bearing - Everything is done under the hood and compute stays within the instance
- Platform agnostic - OS/In Browser: Run in `Node` or in your web browser
- isolated - ensures that changes and experiments do not affect the main environment
- extensible - anything you can do with WP
- testable - allows for easy experimentation and validation of changes
- shareable 
- import markdown to create posts for review
- barrier of entry: Low for new users - they can make edits, submit for review, and get credit
- screenshots: Built in
- versioning - easily boot up various WP versions

## Must Use Plugins and Infrastructure
- Harper
- Playground
- `WP-CLI`

## Workflow

~~**Caveat:** There's an AI layer of some sort for this to work.~~

Find an issue you want to work on, tag on GitHub
POC: Checklist is added as comment on post, in lieu of using Notes

- Find a post to edit
- Boot playground with Must Use Plugins (Harper, `WP-CLI` bootstrapped)
- Import **post for update only** as Markdown into Playground
- To-do list is brought in as individual notes (**TODO:** Attach to-do item relevant comment region)
- Contributor works through To-do list in notes
- Contributor makes PR for review (Notes carry forward)
- Documentation Team reviews changes and provides feedback
- Contributor addresses feedback and updates the PR accordingly, updating notes and content for completion
- Delta Reviewer (AI) verifies checklist vs. changes made to content (Corey working on)
- Delta Review tags docs team on GitHub, updates label through GitHub action
- Documentation Team verifies final changes
- GitHub actions formats post as Markdown, sends to WordPress.org for update
- GitHub actions ensures Props Bot tags contributor(s) for credit
- GitHub actions generates Changelog entry for updates (Copilot?)

### Entities Involved

**Humans**

- Contributor
- Documentation Team

**Bots**

- Delta Reviewer (AI)
- GitHub Actions
- PropsBot
- DeltaBot (AI)

**Infrastructure**

- WP Playground - CLI for updating documentation resources
- WordPress.org Documentation, GitHub repositories - Sources of Truth
- Harper - Must-Use Plugin for Playground Blueprint (for quick checks in browser), CLI - final pass reviews (CodeQL and Markdown analysis via Spec Kit)
- AI Connectors Plugin (future feature / extension) - **do not work on this now** - allows users to bring in docs context from existing resources, perform formatting and analysis, generate code samples, summarize changes, etc. 

## Using Your AI Harness

Future feature / extension - Needed for DeltaBot

- Use AI Connectors plugin to bring in your key via CLI (is this available as a flag?)
- Before PR, Playground CLI removes your AI credentials (you will need to re-add)

## Building the Integration

Use Playground Agent Skills to set up necessary Blueprints to make this work: 
https://developer.wordpress.org/playground/handbook/guides/agent-skill-wp-playground/


******** ZOOM MEETING NOTES

Daylight savings time - changes to meetup time
******

# Link to Docs from Block
## Core wants to be up to date with the blocks
Add link to documentation to the block in the editor

Advanced admin handbook - image updates needed
- How to update images (developer.wordpress.org vs. GitHub?)
- End user docs use the most media
- Bottleneck is access to the media library (AI compares vs. human screenshot)
- Naming schema [block-name_version] - refactor for WordPress.org
- Start with 

Compare 7.1 with 7.2
- Gutenberg getting merged secretly - watch for new components

~~## Pulling content as Markdown for Updating Documentation

For WordPress.org documentation sources

- We want to pull in the documentation resource as Markdown, using the `HTML to Markdown` [package](https://github.com/dmsnell/html-to-md), specifically using the endpoint appending `?output_format=md` which allows pulling the WordPress.org page content as Markdown. 
- [More info on WordPress.org to Markdown](https://make.wordpress.org/meta/2026/03/03/markdown-now-available-on-wordpress-org/)

For GitHub: Use the raw content endpoint of the repository to pull the Markdown file directly.~~

## For various levels of users
Non-negotiables from Milana

- 7.1 Field Guide, Dev Notes, end user docs - Milana
- She wants a release Documentation Team Lead for 7.3-4

"Noteworthy contributors"