https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview

**Guiding Principles**

# Progressive Disclosure
Claude loads information in stages as needed, rather than consuming context upfront.

- [ ] Ensure progressive disclosure in filesystem setup.

## L1: Metadata

The Skill’s YAML frontmatter provides discovery information:

```YAML
---
name: pdf-processing
description: Extract text and tables from PDF files, fill forms, merge documents. Use when working with PDF files or when the user mentions PDFs, forms, or document extraction.
---
```
- [ ] Add Metadata

## L2: Instructions (Loaded when triggered)
https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview#level-2-instructions-loaded-when-triggered

> The main body of SKILL.md contains procedural knowledge: workflows, best practices, and guidance:

- [ ] Add SKILL main file.

## L3: Resources and code (loaded as needed)
https://platform.claude.com/docs/en/agents-and-tools/agent-skills/overview#level-3-resources-and-code-loaded-as-needed

Skills can bundle additional materials:

```
pdf-processing/
├── SKILL.md (main instructions)
├── FORMS.md (form-filling guide)
├── REFERENCE.md (detailed API reference)
└── scripts/
    └── fill_form.py (utility script)
```

**Instructions:** Additional Markdown files (FORMS.md, REFERENCE.md) containing specialized guidance and workflows

**Code:** Executable scripts (fill_form.py, validate.py) that Claude runs using bash, providing deterministic operations without loading their code into context

**Resources:** Reference materials such as database schemas, API documentation, templates, or examples

Claude accesses these files only when referenced. The filesystem model means each content type has different strengths: instructions for flexible guidance, code for reliability, resources for factual lookup.

<!-- Filled in at the end of each lab from the specify-cli line of `uv tool list`. Keep this as the last line. -->
Spec Kit version: {{SPEC_KIT_VERSION}}
