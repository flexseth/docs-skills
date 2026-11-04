# Issue Template

To update each article and update them to WP release 7.1, follow the instructions below:

- [ ] Review the reference issues and PRs that have been added to the article. Some articles may only have one issue.
- [ ] Make any valid updates from previous releases. If those updates are no longer relevant, close the the issue (NOTE: we can only close issues in the Documentation Issue Tracker, if you find a PR from the Gutenberg repository that can be closed, leave a message on it.)
- [ ] Make sure to follow the [style guide](https://make.wordpress.org/docs/style-guide/) for title, headers and tone.
- [ ] Do NOT hotlink images, take the screenshots and upload them to the documentation site in WordPress
- [ ] Update the Change log and add updater's WP.org username and any other contributors that have given feedback/solution even if it wasn't used (eg. updated by @estelaris @zzap)
- [ ] When you are done updating a block article, mark them in the list below

## Sample checklist

https://github.com/WordPress/Documentation-Issue-Tracker/issues/2378

# GitHub Actions

- Create sub-issues based on Checklist (Issue Template) when issues is picked up
- Delta bot looks at deltas from sections when tagged on a section change

```YAML
name: Auto-Create Sub-issues from Checklist

on:
  issues:
    types: [opened, edited]

jobs:
  create-subissues:
    runs-on: ubuntu-latest
    permissions:
      issues: write
    steps:
      - name: Parse Checklist and Create Sub-issues
        uses: actions/github-script@v7
        with:
          script: |
            const issueBody = context.payload.issue.body;
            const parentNumber = context.payload.issue.number;
            const owner = context.repo.owner;
            const repo = context.repo.repo;

            if (!issueBody) {
              console.log("No issue body found.");
              return;
            }

            // Regular expression to match unchecked markdown checklist items: - [ ] Task Name
            const checklistRegex = /^-\s*\[\s*\]\s+(.+)\$/gm;
            let match;
            const newSubIssues = [];

            while ((match = checklistRegex.exec(issueBody)) !== null) {
              const taskTitle = match[1].trim();
              // Skip empty entries or placeholders
              if (taskTitle) {
                newSubIssues.push(taskTitle);
              }
            }

            if (newSubIssues.length === 0) {
              console.log("No new checklist items found to convert.");
              return;
            }

            console.log(`Found ${newSubIssues.length} tasks to convert into sub-issues.`);

            for (const title of newSubIssues) {
              try {
                // 1. Create the standalone child issue
                const createResponse = await github.rest.issues.create({
                  owner,
                  repo,
                  title: title,
                  body: `Sub-issue created automatically from parent issue #${parentNumber}.`
                });

                const subIssueId = createResponse.data.id;
                const subIssueNumber = createResponse.data.number;

                // 2. Link it as a sub-issue to the parent issue
                // Uses the sub-issues API endpoint: POST /repos/{owner}/{repo}/issues/{issue_number}/sub_issues
                await github.request('POST /repos/{owner}/{repo}/issues/{issue_number}/sub_issues', {
                  owner,
                  repo,
                  issue_number: parentNumber,
                  sub_issue_id: subIssueId
                });

                console.log(`Successfully created sub-issue #${subIssueNumber} for task: "${title}"`);
              } catch (error) {
                console.error(`Failed to create sub-issue for "${title}":`, error.message);
              }
            }
```



How it Works

1. Trigger: Runs whenever a main issue is opened or edited.
2. Parsing: A regex scans the issue body line-by-line looking for standard markdown checkboxes containing text (- [ ] Task Name).
3. Creation & Linking: For every match, it issues a standard POST call to create the issue, then utilizes the native sub-issue API (POST /repos/{owner}/{repo}/issues/{issue_number}/sub_issues) to instantly turn it into a child task under the parent issue.

Important Considerations

• Duplicate Prevention: This basic script runs every time the issue is edited. If you add text or modify labels, it will attempt to re-create issues for all remaining unchecked boxes. To prevent this, you could extend the script to replace the - [ ] in the parent Markdown body with a link to the newly created sub-issue (- #123) using the github.rest.issues.update API.
• Token Permissions: Ensure your repository settings allow actions to read and write to issues (permissions: { issues: write }), which is explicitly declared in the workflow file.
If you'd like to scale this up, let me know:
• Do you want the script to automatically rewrite the parent issue to replace the checklist text with a link to the new sub-issue?
• Should it copy labels or milestones from the parent issue down to the child issues?
• Should it automatically close the parent issue once all sub-issues are completed?

## Discussion

How to use sub-issues?

- How many levels deep should sub-issues go?
- How to handle updates to parent issues and propagate changes to sub-issues
- How to handle the automatic closing of parent issues once all sub-issues are completed