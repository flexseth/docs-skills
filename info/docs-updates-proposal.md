## Workflow content
Source: [Documentation Team Update – November 2, 2026](https://make.wordpress.org/updates/2026/11/02/documentation-team-update-november-2-2026/)

### Current Projects

1. **[WP Overview](https://github.com/orgs/WordPress/projects/274)**
   - Overview and foundational documentation for WordPress platform

2. **[Technical Guides](https://github.com/orgs/WordPress/projects/272)**
   - In-depth technical documentation and developer guides

3. **[Support Guides](https://github.com/orgs/WordPress/projects/273)**
   - User support and troubleshooting documentation

4. **[Customization Guides](https://github.com/orgs/WordPress/projects/275)**
   - Guides for customizing WordPress themes and functionality

5. **[Blocks Articles Overhaul](https://github.com/orgs/WordPress/projects/267)**
   - Comprehensive update and reorganization of block editor documentation

### Version-Specific Documentation

6. **[WordPress 7.1 Documentation](https://github.com/orgs/WordPress/projects/295)**
   - Documentation for WordPress 7.1 release features and updates

7. **[WordPress 7.0 Documentation](https://github.com/orgs/WordPress/projects/286)**
   - Documentation for WordPress 7.0 release features and updates

8. **[WordPress 6.9 Documentation](https://github.com/orgs/WordPress/projects/262)**
   - Documentation for WordPress 6.9 release features and updates

9. **[WordPress 6.8 Documentation](https://github.com/orgs/WordPress/projects/289)**
   - Documentation for WordPress 6.8 release features and updates

10. **[WordPress 6.7 Documentation](https://github.com/orgs/WordPress/projects/279)**
    - Documentation for WordPress 6.7 release features and updates

11. **[WordPress 6.6 Documentation](https://github.com/orgs/WordPress/projects/277)**
    - Documentation for WordPress 6.6 release features and updates

12. **[WordPress 6.5 Documentation](https://github.com/orgs/WordPress/projects/268)**
    - Documentation for WordPress 6.5 release features and updates

13. **[WordPress 6.4 Documentation](https://github.com/orgs/WordPress/projects/264)**
    - Documentation for WordPress 6.4 release features and updates

14. **[WordPress 6.3 Documentation](https://github.com/orgs/WordPress/projects/258)**
    - Documentation for WordPress 6.3 release features and updates

15. **[WordPress 6.2 Documentation](https://github.com/orgs/WordPress/projects/253)**
    - Documentation for WordPress 6.2 release features and updates

16. **[WordPress 6.1 Documentation](https://github.com/orgs/WordPress/projects/246)**
    - Documentation for WordPress 6.1 release features and updates

17. **[WordPress 6.0 Documentation](https://github.com/orgs/WordPress/projects/238)**
    - Documentation for WordPress 6.0 release features and updates

### Additional Documentation Projects

18. **[Block Editor End User Docs Update to 5.9](https://github.com/orgs/WordPress/projects/243)**
    - Update to end-user documentation for Block Editor version 5.9

19. **[Content Review](https://github.com/orgs/WordPress/projects/265)**
    - General content review and quality assurance project

20. **[Advanced Administration Handbook](https://github.com/orgs/WordPress/projects/259)**
    - Comprehensive handbook for advanced WordPress administration

21. **[WordPress Glossary Update](https://github.com/orgs/WordPress/projects/263)**
    - Updates and expansion of WordPress terminology glossary

22. **[WordPress Block Editor End-User Docs Inventory](https://github.com/orgs/WordPress/projects/260)**
    - Inventory and audit of Block Editor end-user documentation

23. **[Inventory of Technical Parts From End User Docs](https://github.com/orgs/WordPress/projects/261)**
    - Cataloging of technical components within end-user documentation

24. **[Documentation Team Handbook](https://github.com/orgs/WordPress/projects/266)**
    - Internal handbook for documentation team processes and guidelines

25. **[New Design for HelpHub in WordPress.org](https://github.com/orgs/WordPress/projects/270)**
    - Design and implementation updates for the WordPress help documentation hub

26. **[End-User Documentation](https://github.com/orgs/WordPress/projects/269)**
    - Primary project for end-user focused documentation

27. **[End-User Docs Repo – Workflows and Settings](https://github.com/orgs/WordPress/projects/271)**
    - Repository configuration and workflow management for end-user docs

## Proposal

Use GitHub and Playground to track, review, and audit changes to the WordPress documentation effectively.

## Existing System

- WP.org admin revision system does not track whether the change was made via GitHub or the Block Editor (WP Admin).
- WP.org admin revision system does not offer version control of what was changed (only a new revision of entire copy/pasted content).
- Existing workflow doesn't show diffs if they were not made in the block editor (automated changes paste full content).
- Docs changes made outside the block editor are not easily attributable to a specific user, barring new contributors.

# New System

Allows GitHub to be used to pull in block content via Playground, so anyone can review. 

Once approved the block markup is saved back to the docs repository, and the diffs or changes that are made show up in revision history because only the changes made are recorded in revision, not entire copy/pasted block content.

## Problems

Importing block content as a raw copy/paste does not show a diff in the revision history.

This doesn't allow users to use the block editor to see what has changed in a clear and trackable manner.

WordPress updates often, and new features are introduced, but often hidden. For example, Notes in Gutenberg!

Where does the content live? Example: https://github.com/WordPress/developer-plugins-handbook/tree/main is the Plugins Handbook, but making a PR to this repo will not update the content on the live site. We need a better flow or explanation of how this works.
Relevant update issue: https://github.com/WordPress/Documentation-Issue-Tracker/issues/2535

## Benefits

- Using GitHub and Playground ensures that all changes are tracked, reviewable, and auditable.
- Git diffs make easy changes very approachable, and easy to review (ie: spelling, a word, etc)
- GitHub PRs are disposable, but the revisions track _only_ the changes in the WordPress installation's revisions.
- Extensions can be made on the workflow via WordPress - for example, CodeQL and quality gate checks via GitHub Actions.
- Users can leverage the block editor to visually inspect changes, improving clarity and accountability.
- The Block Editor allows for very easy updating of screenshots as UIs change, vastly exceeding any Markdown flow for screenshot updates.
- The combination of GitHub and Playground provides a transparent and accountable workflow for documentation updates.
- Both WordPress and Playground are extensible, this is a POC not a finished product.
- Infrastructure of GitHub (milestones and labels, issue types) allows project metadata to be extracted from block content copy, improving repo structure. Enhancing tracking and context of documentation changes.