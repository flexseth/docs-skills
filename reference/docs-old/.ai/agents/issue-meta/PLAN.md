# Docs AI Agent Plan

**Building content**
- consume draft,
- consume already created files
- infer specifications
- organize


**Variable Handling** and context management

- define new variables from issue-template.md
- consume already created files to look for new variables needed
- integrate new variables into agent pipeline
- handle immutability for fetched GitHub issue data
- ensure proper context management throughout the agent pipeline

# Continuing from Copilot Chat

Issue context immutability:
> Yes—make the fetched GitHub issue data immutable for the duration of a pipeline run, but keep derived context editable

The immutability is an interesting topic, the only thing we know for sure is who submitted the issue and at what time. Everything else can change. How would you break these two items apart? Look at the existing variable structure, how they are used, and add to the plan.

1) Ok, additionally, another issue may come into context, for example, cross-checking two issues to make sure every tem on a document has been done (for a single canonical URL). 

There is one common ground we want to focus around: the document canonical URL ({{CANONICAL_URL}}). Two issues may reference the same canonical, and we may encounter more than one issue in the agent pipeline. 

# Goal for Today
My goal today is to turn this thing over to Fable and see what it can do, given a very specific context and all of the necessary information. What we need to do is determine the specification, create a plan, and ensure that the context is properly structured for the agent pipeline. Then, we need to run the lap, which will capture the output needed to define v1 of the `audit` agent. 

This content will be surfaced to Spec Kit, ensuring that all relevant information is available for review and further action. We will then use Fable on Spec Kit to see if we can create the best, functional, working specification for the `audit` agent.

Additionally, we want to notate the process so it can be duplicated later. We want to use good versioning, and we want to use good project tracking (repositiry, changes, PRs, etc). The code should be manageable and the flow should too.

You will make a plan file, with a checklist of all the steps needed to integrate the new variables, handle immutability, and ensure proper context management throughout the agent pipeline. The checklist should also show how we will capture agent creation specs when running the audit lab. For the audit lab, we are going to run a simple Claude Code session against a predefined set of issue contexts and canonical URLs to validate the integration and behavior of the new variables. We will save the output to be used in the agent and skill specification.

# New Terminology

I am calling the central agent processing system the "cortex" from here out. It is where the agents will talk to each other and will coordinate their actions based on the shared context and variables. Ideally, without an actual orchestration agent. There is some information on a sample orchestration found in the `lookup` agent [documentation](/Users/seth/Developer/WordPress/docs/skills/lookup/orchestration.md).

# New Variables
- I have added some new variables required to the Issue Meta agent, they come from the `issue-template.md` file. This is an actual Issue Template in GitHub, that we use to force users to standardize their documentation update requests. The new variables need to be named and documented. They should be implemented in to the agent pipeline and properly integrated with the existing context and variable structure. We will need to figure out all places these variables might be needed, and put in the necessary handling and documentation for them. If unsure, put a placeholder. This includes in agent frontmatter, variable tables, skill calls, and output.


**New Variables**


I am attaching the entire context into this chat window, so we can get past this design decision. Next, we are going to be organizing the repository better. 


Do not edit files, but show me the changes you want to make so as diffs. Only show diffs of sections that are similar - don't show the entire diff as one. I want to be able to see what is changing in relation to other information. 

If necessary, create a plan for this session.

# Context for this Plan

