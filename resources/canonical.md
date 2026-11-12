# Canonical Documentation References

## Canonical Reference Lookup Guide

This guide serves as the Canonical URL builder for the `lookup` agent.

When a calling agent invokes the `lookup` sub-agent, the primary focus is to search and find relevant documentation sections for the required resources.

Because Opus and other models serve docs from memory, this can produce inaccurate or outdated findings. For example, rapidly changing APIs or new features may have new docs updated frequently, resulting in more context being avaiable to the model that is highly relevant. 

The focus of the `lookup` agent is to check and make sure the docs are up-to-date, and provide more in-depth lookups to make sure that deprecations are being followed, all sources are provided, and accurate information is being returned to the main model. This process results in more precise, standards based, and future compliant code being written during the build.

### WordPress Developer Resources (DevHub)

For DevHub, the Fully Qualified Domain Name is `developer.wordpress.org`.

#### Canonical URL Standard
**FQDN**: https://developer.wordpress.org

DevHub is the WordPress Developer Documentation canonical resource.

In this document and all subsequent docs, looking up the DevHub URL will be represented as `<DevHub>`, which resolves to `developer.wordpress.org`.

DevHub covers the following topics:

## Primary Index

- Handbooks
- Reference Guides 

## Handbooks 

**Canonical URL Standard** 
<DevHub>/<handbook-name>

- Common APIs
- Plugins
- Themes
- Block Editor - _example_: (https://developer.wordpress.org/block-editor/)
- Coding Standards
- Rest API
- Secure Custom Fields
- Advanced Administration
- Playground
- Reprint

## Pages

Home (developer.wordpress.org)

Reference (Code Reference)
https://developer.wordpress.org/reference/

- **important** New and updated in 7.1 (good for updated APIs, new features, etc)
- API Reference (Common APIs)


### Documentation Sources examples (not an exhaustive list)

The starting point, or boilerplate, to look up URLs.

For each identified topic, determine which documentation source(s) apply:
- **Security topics** → WordPress Security Handbook
- **Coding standards** → WordPress Coding Standards
- **i18n/l10n** → Internationalization Handbook
- **Scripts & styles** → Plugin Handbook (Scripts & Styles section)
- **Roles & capabilities** → Roles and Capabilities docs
- **Block/Gutenberg** → Block Editor Handbook
- **Plugin submission** → Detailed Plugin Guidelines
- **REST API** → REST API Handbook
- **Function lookup** → Code Reference (developer.wordpress.org/reference)
- **Other topics** → Check the curated source list below

## Reference Guides and Handbooks

## Code Reference 

- functions, hooks, actions, filters
- WP-CLI Commands
