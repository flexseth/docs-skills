# Agent Orchestration Overview

When a calling agent (the "Executor") invokes the `lookup` sub-agent (the "Consultant"), the primary focus is to search and find relevant documentation sections for the required resources. 

Frontier models are highly capable, but with capability comes some limitations when assumptions are made. 

Because Opus and other models read docs from memory, invalid lookups can occur. Inaccurate lookups or outdated findings, rapidly changing APIs, or new features can confuse the model. In real world testing, it was found that models can refer to incomplete, outdated, and even _act on_ deprecated functionality. In a series of batch runs it was observed that the model would implement a deprecated component in WordPress without thinking twice.

The [Lookup Agent's](../.claude/agents/docs-lookup.md) assignment is _To Look Twice._

The focus of the `lookup` agent is to serve as a **Consultant** to check and make sure the docs are up-to-date, and provide more in-depth lookups to make sure that deprecations are being followed, all sources are provided, and accurate information is being returned to the main model. This process results in more precise, standards based, and future compliant code being written during from the **Executor**.