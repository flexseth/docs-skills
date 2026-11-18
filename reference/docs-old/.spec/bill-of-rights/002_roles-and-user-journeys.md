# Roles and User Journeys (AI Agents)

Base roles (Contributor, First time contributor, Release Team Leads, Developers, UX Developer, Writers, WordPress Credits Contributors) are defined in [roles-and-user-stories.md](../roles-and-user-stories.md)

These roles are in the context of the GitHub users relation to the Documentation Team, not the WordPress Admin. Info on Admin roles are at the bottom of this document.

## How This Was Determined 

The roles below are implied by the journeys and the project purpose. Each links to the page in this project where it appears. Role-to-journey detail is in [infer-user-journeys.md](./user-stories-and-journeys/infer-user-journeys.md#2-roles-that-the-existing-journeys-imply).

- [Docs Team Reviewer](./user-stories-and-journeys/infer-user-journeys.md#docs-team-reviewer-implied) - see also [Proposed flow](./_purpose.md)
- [Editor (wp.org)](./user-stories-and-journeys/infer-user-journeys.md#editor-wporg-implied) - see also [Proposed flow](./_purpose.md)
- [Issue Triager](./user-stories-and-journeys/infer-user-journeys.md#issue-triager-implied) - see also [The Unwelcomed Contributor](./user-stories-and-journeys/docs-contributors/the-unwelcomed.md)
- [Docs Team Liaison / Outgoing Release Lead](./user-stories-and-journeys/docs-contributors/release-team-lead.md)
- [Core / Field Guide Author](./user-stories-and-journeys/docs-contributors/release-team-lead.md) - see also [Dev Notes false positive](./user-stories-and-journeys/docs-contributors/dev-notes.md)
- [Project Maintainer](./user-stories-and-journeys/infer-user-journeys.md#maintainer-of-this-project-implied) - see also [Purpose](./_purpose.md)

# WordPress Admin Roles

WordPress Admin user account should be used in Playground ("Client"and "Server") installations to: 

- log in to WordPress 
- provide credentials to the REST API "client" website
- view and edit the individual doc content in the Gutenberg Block Editor
- make the PR from Playground
- - [ ] Spec Kit: We need discussion on how to do this

## Open Discussion
- [ ] For Spec Kit: Looking at the Playground CLI, ensure that the WordPress Admin user account is correctly configured and can perform all necessary actions in both "Client" and "Server" installations.