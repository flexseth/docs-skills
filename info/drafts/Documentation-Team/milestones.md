## Current View
https://github.com/WordPress/Documentation-Issue-Tracker/milestones

Doesn't show any context, no milestones, nothing.

### Use Milestones for WordPress Versions

One major objective of the Documentation Team is to track changes across WordPress versions, yet there is no easy mechanism for doing so within the current GitHub workflow.

### Proposal - Using Milestones for WordPress Versions

![Registering a Milestone](assets/milestone.png)

**Existing Infrastructure:** Currently Milestones are not used, but they can be leveraged to track documentation updates corresponding to specific WordPress versions. This helps in organizing and prioritizing tasks based on the version they affect.

As new features are created and slated for various milestones on Trac, corresponding documentation updates can be planned and tracked using GitHub milestones, ensuring that the documentation stays in sync with the development cycle.

Example: 
> [HelpHub][Ex][7.1] Document the experimental custom taxonomy management UI #2483
- ~~[WordPress 7.1 Milestone](https://github.com/WordPress/milestones/7.1)~~
- [Sample Make RC post](https://make.wordpress.org/core/2025/12/01/wordpress-6-9-release-candidate-4/)

**Benefit:** Having docs ready before releases makes it easier for end users, and allows contributors to be part of the release team!

#### Execution
1. Create the Milestone, IE: WordPress 7.1 docs
2. Assign relevant issues and pull requests to the created milestone.
3. Monitor the progress of the milestone and update as necessary to ensure documentation aligns with the corresponding WordPress version.
4. Use the milestone to track on project progress (via project boards)
5. Add a milestone description from the Make Post
> The forth Release Candidate (“RC4”) for WordPress 6.9 is ready for download and testing!

> This version of the WordPress software is under development. Please do not install, run, or test this version of WordPress on production or mission-critical websites. Instead, it’s recommended that you evaluate RC4 on a test server and site.

> Reaching this phase of the release cycle is an important milestone. While release candidates are considered ready for release, testing remains crucial to ensure that everything in WordPress 6.9 is the best it can be.

> More info: https://make.wordpress.org/core/2025/12/01/wordpress-6-9-release-candidate-4/