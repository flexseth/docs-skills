## Blurry screenshots
https://developer.wordpress.org/themes/classic-themes/basics/categories-tags-custom-taxonomies/

I want to make a github action that rejects an attachment if the word "screenshot" is found in the attachment. I've included a sample workflow below for a PR,  however, this context will be different.

## Rejecting User Submitted Photos with "screenshot" in the Filename
If any user uploads a file containing the word "screenshot" in the filename, we want to automatically tag the user and create a checklist for them to correct the error.

It should assign the issue to the user who submitted the file and add a comment explaining that files with "screenshot" in the filename are not allowed.

It should also link to how to download the raw file on Apple Photos and also from iPhone. 

It should keep the screenshot but add a notice with a warning that the file is not allowed and needs to be corrected.

## sample workflow 
The use case below is for a PR. We want the workflow to run any time a user comments on an issue with an attachment. If the attachment contains the word "screenshot" in the filename, it should trigger the action to assign the issue to the user, add a comment, and provide links for correcting the error.

## rework this workflow to work for an issue comment with an attachment instead of a PR.
To create a GitHub Action that rejects an attachment if the word "screenshot" is found in it, you can follow these steps:
1. Create a new directory in your repository called `.github/workflows` if it doesn't already exist.
2. Inside the `.github/workflows` directory, create a new YAML file for your action, for example, `reject_screenshot.yml`.
3. In the YAML file, define the workflow and specify the trigger for the action. You can use the `pull_request` event to trigger the action when a pull request is created or updated. Here's an example of how the YAML file might look:```yaml
name: Reject Screenshot Attachments
on:
  pull_request:
jobs:
  check-attachments:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v2
      - name: Check for screenshot in attachments
        run: |
          # Get the list of attachments from the pull request
          attachments=$(gh pr view ${{ github.event.pull_request.number }} --json files --jq '.files[].path')
          for attachment in $attachments; do
            # Check if the attachment contains the word "screenshot"
            if grep -qi "screenshot" "$attachment"; then
              echo "Attachment '$attachment' contains the word 'screenshot'. Rejecting the pull request."
              exit 1
            fi
          done
          echo "No attachments contain the word 'screenshot'."
4. Save the YAML file and commit it to your repository.
5. Now, whenever a pull request is created or updated, the action will run and check the attachments for the word "screenshot". If it finds any attachment containing that word, it will reject the pull request by exiting with a non-zero status code, which will cause the action to fail.