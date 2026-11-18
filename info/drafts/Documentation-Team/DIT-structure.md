# Discovery

The reason Issue Types are likely not able to be created for the Documentation Issue Tracker is that there are only 25 available per organization. 

- [ ] How many Issue Types are currently being used by the WordPress organization?

# Docs Suggestions
Considering `edit` access is required for all updates, make it easier on editors to select issues they are comfortable with.

## Ideas for Improvement in Repo Structure

Use built-in taxonomy and GitHub's native tools to better organize docs issues.

### Potential Improvements

- Rework labels and introduce issue types.
- Use Milestones for WordPress releases.


#### Rework HelpHub and DevHub into Issue Types

The idea is to rework the existing HelpHub and DevHub labels into dedicated Issue Types, making it easier to categorize and manage documentation issues based on their target audience.

Users can click the issue type to bring up a list of those issues, displayed more cleanly by title and label, creating a more streamlined display by surface area. Power users can then further refine search terms using additional filters and labels.

Currently, the labeling system is used as more of a broad "category" rather than a precise classification, which can make it harder to quickly identify the nature and target audience of each documentation issue.

Default labels in GitHub are `bug`, `documentation`, `duplicate`, `enhancement`, `good first issue`, `help wanted`, `invalid`, `question`, and `wontfix` - none of which are used in the Documentation Issue Tracker. And there isn't really even close to a qualifying similar label or strategy used in the tracker.

[About Default Labels](https://docs.github.com/en/issues/using-labels-and-milestones-to-track-work/managing-labels#about-default-labels)

---
**Currently available:** Task, Bug, Enhancement

![Adding missing types](drafts/assets/issue-types.png)

**Objective:** Migrate some [`labels`](https://docs.github.com/en/issues/using-labels-and-milestones-to-track-work/managing-labels) to `types`
**Thought Process:** Default GitHub labels provide a different taxonomy structure than what is used in DIT
**Differentiation:** End user documentation vs. developer documentation
**Benefit:** Removes `[HelpHub]` or `[DevHub]` prefixes from issue titles, making them cleaner and easier to read.
**Reorganization:** By migrating labels to issue types, the structure of the documentation issues becomes more intuitive and easier to navigate.
- **Label:** HelpHub > **Type:** wordpress.org/documentation
- **Label:** DevHub > **Type:** developer.wp.org
**Pitfall:** URLs may look long as issue types?

Alternatively: Link directly to URL where the documentation resides.

- **Label:** HelpHub > **Type:** End Users
- **Label:** DevHub > **Type:** Developer

**Major Issue**: Issue Types must be created at the organizational level in GitHub, and only 25 are allowed per organization. 
- [ ] Question: What organization is the Documentation Issue Tracker (DIT) associated with?

#### Better Indicating When Documentation Has Been Reviewed
A simple QOL improvement, add a new `Reviewed` status to indicate when documentation has been reviewed for accuracy and completeness. 

Alternatively, Allow users to tag with /reviewed to show they have reviewed the changes. 

A GitHub action fires when a user tags an issue or pull request with /reviewed, automatically updating the status to `Reviewed`.

`/review` is already existing in the GitHub workflow, but if multiple people have reviewed the changes, each can tag with /reviewed to update the status accordingly. If there have been two reviews on an issue and it seems simple enough to be picked up by a Docs Team Member, they can act accordingly. 

#### Rework Milestone Usage

See [milestones](drafts/milestones.md)

## References
- [Using Issues Labels, Milestones and Types](https://docs.github.com/en/issues/using-labels-and-milestones-to-track-work/managing-labels)