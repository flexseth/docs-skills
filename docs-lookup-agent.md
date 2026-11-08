# WordPress Docs Lookup Agent

Spec for the sub-agent that the `wp-docs` skill hands lookups to. The calling agent (usually Opus) asks a question. This agent fetches the right page and returns a small, structured answer. The goal is to keep full documentation pages out of the calling agent's context.

Status: draft. Not yet an agent file. Model choice, tools and cache are still to be decided (see Open Questions).

## When to use it

Delegate to this agent when the caller needs:

- the exact signature, props or example for a specific component, hook, function or schema
- version-specific or fast-changing APIs (block.json, Interactivity API, `@wordpress/*` packages, deprecations)
- a canonical URL to cite

Do not use it for well-known basics the caller already knows (`register_post_type`, `$wpdb->prepare`, and similar). It costs tokens for no gain.

## Model

Sonnet by default. Haiku is enough for "find the page, copy the example". Anything that needs judgment about whether a doc applies stays with the calling agent.

## Input

A plain-language request from the caller, for example: "Show me the code for a sample TextArea component". The caller may also give a WordPress version or a topic area.

## Procedure

1. Check the cache (if enabled) for the key.
2. Resolve the request to a canonical URL: the index first, then URL pattern matching.
3. Fetch `<canonical URL>?output_format=md`.
   - Every developer.wordpress.org page supports this. About 33x smaller than the HTML (TextareaControl: 8.6KB vs 287KB).
   - Other hosts are untested (see issue #1). Do not assume they support it.
4. Extract the relevant section or snippet. Never return the whole page.
5. Build the return object (below).

## Rules

- Deliver Markdown or code, never HTML.
- Return at most one snippet. Include a props table only if it was requested.
- Fill fields only from the fetched page. Do not add props, versions or code from memory.
- If the resource found differs from what was asked for (the prompt says "TextArea", the component is `TextareaControl`), set `matches_request` to `false` and explain in `note`. Never substitute silently.
- The Markdown's anchor links all carry `?output_format=md`. Strip that parameter from the `url` you return, so it is the clean canonical URL.
- If the relevant section is too large, set `truncated` to `true` and give a `section_hint` (a heading or anchor) so the caller can fetch only that part.
- If the lookup fails, return an empty `snippet` and say why in `note`.

## Return Format

One JSON object per resource.

```json
{
  "key": "TextareaControl",
  "url": "https://developer.wordpress.org/block-editor/reference-guides/components/textarea-control/",
  "matches_request": true,
  "note": "",
  "last_modified": "2026-10-14",
  "fetched": "2026-11-07",
  "snippet_lang": "js",
  "snippet": "<Markdown or code>",
  "truncated": false,
  "section_hint": ""
}
```

| Field | Meaning |
| --- | --- |
| `key` | Canonical name of the resource (component, hook, function, etc.) |
| `url` | Clean canonical URL (no `?output_format=md`), so the caller can cite or re-fetch it |
| `matches_request` | `false` if the resource differs from what the prompt asked for |
| `note` | Reason for a mismatch, or other caveats. Empty if none |
| `last_modified` | "Last modified" date from the page's Markdown header (YYYY-MM-DD). Empty if the page gives none |
| `fetched` | Date the page was fetched (YYYY-MM-DD) |
| `snippet_lang` | Language of the snippet (`js`, `php`, `json`, etc.) |
| `snippet` | The relevant Markdown or code, copied from the page. Empty only if the lookup failed |
| `truncated` | `true` if the relevant section was too large to return |
| `section_hint` | Heading or anchor to fetch when `truncated` is `true` |

## Example

**Prompt:** Show me the code for a sample TextArea component

**Workflow:** Calls Sonnet sub-agent. This task is not that complicated.

**Returns:** (snippet verified against the page's Markdown on 2026-11-07)

```json
{
  "key": "TextareaControl",
  "url": "https://developer.wordpress.org/block-editor/reference-guides/components/textarea-control/",
  "matches_request": true,
  "note": "Asked for 'TextArea'; the WordPress component is named TextareaControl.",
  "last_modified": "2026-10-14",
  "fetched": "2026-11-07",
  "snippet_lang": "js",
  "snippet": "import { useState } from 'react';\nimport { TextareaControl } from '@wordpress/components';\n\nconst MyTextareaControl = () => {\n\tconst [ text, setText ] = useState( '' );\n\n\treturn (\n\t\t<TextareaControl\n\t\t\tlabel=\"Text\"\n\t\t\thelp=\"Enter some text\"\n\t\t\tvalue={ text }\n\t\t\tonChange={ ( value ) => setText( value ) }\n\t\t/>\n\t);\n};",
  "truncated": false,
  "section_hint": ""
}
```

## Cache (optional)

Sub-agents keep no memory between calls, so a file is the only way to persist results. If enabled, write entries to `.claude/cache/wp-docs.json` keyed by `key`, check it first, and discard entries whose `last_modified` or `fetched` is stale.

## Open Questions

- Which hosts besides developer.wordpress.org offer Markdown? Tracked in issue #1.
- Should this be a Claude Code agent file (`.claude/agents/`) with `model:` frontmatter, or guidance only, so the skill also works with other agents?
- Does the cache earn its complexity? Measure repeat-lookup rates first.
- Size cap for `truncated`: to be set once real pages have been sampled.
- Overlap with the upstream `wp-docs` skill on `WordPress/agent-skills` (`add/wp-docs-skill`). Compare before building a second one.
- Token measurement: run 10-15 realistic prompts with and without the skill. Count the sub-agent's tokens too.
