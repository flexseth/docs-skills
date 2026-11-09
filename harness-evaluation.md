# eval-1

- [ ] Start this process from `sanity-check`
- [ ] Clear the context window before proceeding further with instructions.
- [ ] Switch to Opus
- [ ] Ask the user - Should I proceed with Opus?

If Yes, go to the next step.
If No, abort.

# idea

I want to evaluate the efficienty of the calling process and agent skill as it stands now. 

To do this, we will emulate creating WordPress plugins with and without the docs lookup agent.

We want totest how long it takes to fetch the documentation, and how accurately the prompt creates the functionality given the fetched documentation. I also want to test the total time it takes the prompt to run, and to count tokens.

# setup

To do this, we will need multiple worktrees, with real world prompts.

There will be 7 worktrees, each based off of the head of `sanity-check`

The worktrees should be named `base-{`<run-number>`}`

<run-number> will be sequential

In each worktree, we will need two folders: 

These folders will be variables: `handler`

`handler` will be either: 

- `agent` - the work in this branch will be done by the `docs-lookup-agent` called by Opus
- `sonnet` - the work in this branch **must** be done solely by Sonnet

This should give us the directory structure of: 
/base-{<run-number>}/<handler>/

Inside of this directory, we want to store the following files: 

- `prompt.md` - the real world generated prompt, as specified below
- `tokens.md`  - the total number of tokens used
- `time.md`    - the amount of time in ms it took to run the entire session
- `/output`   - a directory of the generated WordPress plugin


So at the end of each numbered run, there should be an agent-called workflow, and one from Sonnet.

If the agent call fails, there is info what to do later in this document.

**IMPORTANT** Save prompts _verbatim_

For each numbered run, it should save the prompt that is generated **verbatim** so the models can be checked against each other for token spend, timing, and accuracy.

Read the prompt generating instructions at the end of the document, and create a prompt. Save it to memory. This prompt will change in each run, but it's important to create the prompt before starting the process of evaluating the runs.

The prompt can be saved in `prompt.tmp`

# running the evaluation

Start the runs, starting at run number 1.

- Create the two worktrees for the run, given the file structure above and run #

## Run 1

**Clear Context Window** For each numbered run

Do not count this time towards this total.

- Switch the model to Opus (if it is not already set to Opus)
- Switch to the relevant run # worktree branch, with model being `docs-lookup-agent`
- Start counting tokens and time at 0 and 0.00ms
- On Pass 1 - Use the Docs Lookup Agent. If the `docs-lookup-agent` is called, check to verify that Sonnet or Haiku is used, and note which model in `model.md`. Do not force the switch; if the prompt runs against the `docs-lookup-agent` call and does not turn on Sonnet or Haiku, we need to flag that in `model.md` - but proceed with the prompt.
- Run the prompt (see plugin specifics below)
- report results from pass 1 to the checked out worktree
- If the `docs-lookup-agent` was called, report back it's findings via a `.json` file
- clear the context window

## Run 2

- Switch to the `sonnet` model branch.
- Switch to the relevant run # worktree branch, with model being `sonnet`
- Start counting tokens and time at 0 and 0.00ms
- On Pass 2 - Run the prompt with Sonnet
- report results from pass 2 to the checked out worktree
- clear the context window

## end of pass

Increase <run-number>
Repeat Run 1 and 2 above

Do this until you get to <run-number> 8, and then stop.

# prompt specifics

The prompts should each be different, and need to call at least 7 WordPress docs across various surfaces. For each prompt, at least two of these documentation reads should not be considered "general knowledge," and one should be a complex document like: 

- Interactivity API
- JS Hooks
- REST API
- WP HTML API
- Using the Data store
- Using a nonce to check something
- the Settings and Options API


