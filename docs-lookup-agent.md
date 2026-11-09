# WordPress Docs Lookup Agent

Spec for the sub-agent that the `wp-docs` skill hands lookups to. The calling agent (usually Opus) asks a question. This agent fetches the right page and returns a small, structured answer. The goal is to keep full documentation pages out of the calling agent's context.

Status: draft. The agent file is `eval-model/agents/docs-lookup.md`. Cache is still to be decided (see Open Questions).

## When to use it

Delegate to this agent when the caller needs:

- the exact signature, props or example for a specific component, hook, function or schema
- version-specific or fast-changing APIs (block.json, Interactivity API, `@wordpress/*` packages, deprecations)
- a canonical URL to cite

Do not use it for well-known basics the caller already knows (`register_post_type`, `$wpdb->prepare`, and similar). It costs tokens for no gain.

## Model

Sonnet by default. Haiku is enough for "find the page, copy the example". Anything that needs judgment about whether a doc applies stays with the calling agent.

## Input

A plain-language request from the caller, for example: "Show me the code for a sample TextArea component". The request may name several resources. The caller may also give a WordPress version or a topic area.

## Procedure

1. Check the cache (if enabled) for the key.
2. Split the request into **parts**, one resource each. Handle them one at a time.
3. Resolve each part to a canonical URL. **Never build a slug from memory**: handbook pages are renamed (issue #2). Use these sources:

   | Docs | Source |
   | --- | --- |
   | Block editor handbook, packages, components, block.json, Interactivity API | `https://raw.githubusercontent.com/WordPress/gutenberg/trunk/docs/manifest.json`. Entries have `title`, `slug` and `parent`. URL = `https://developer.wordpress.org/block-editor/` + the slugs from the top parent down to the entry, each followed by `/`. |
   | PHP functions, hooks, classes | Fixed pattern: `reference/functions/<name>/`, `reference/hooks/<name>/`, `reference/classes/<name>/` |
   | REST API, plugin, theme and other handbooks | `https://developer.wordpress.org/sitemap-index-1.xml` and its sub-sitemaps |
   | Any page | The parent page's Markdown lists its child pages as links |

4. Fetch the raw Markdown: `<canonical URL>?output_format=md`.
   - Every developer.wordpress.org page supports this. About 33x smaller than the HTML (TextareaControl: 8.6KB vs 287KB).
   - Other hosts are untested (see issue #1). Do not assume they support it.
   - WebFetch is not used. It passes every page through a small model, so the agent would see a summary and "verbatim" snippets could be paraphrased.
5. List the page's headings first, then print only the matching section. Raw text stays inside the sub-agent. Never return the whole page.
6. Build the return object (below).

### Helper script

Steps 3 to 5 are mechanical, so they live in a script, not in the agent's instructions: `eval-model/agents/wp-docs.py` (Python standard library only). The agent calls it through one narrow Bash permission and keeps only the judgment calls: which page matches, which heading answers the request, and what to put in `note`.

| Command | Does |
| --- | --- |
| `resolve <word> ...` | Searches the manifest and builds handbook URLs from the slug chain |
| `reference <function\|hook\|class> <name>` | Builds a reference URL (fixed pattern, no fetch) |
| `headings <url>` | Lists headings with line numbers; ignores headings inside code fences |
| `section <url> <heading or line> [--max-lines N]` | Returns one section as JSON (`text`, `url`, `last_modified`, `fetched`, `heading`, `lines`, `truncated`, `next_heading`). Caps at 50 lines, or N; a long section is cut at a blank line outside code blocks, so it never ends inside a code fence, and `next_heading` says where to continue. Every section returned is also written to `wp-docs-sections.jsonl` beside the log, so the eval can check that snippets are exact copies. `text` leaves out the heading line, whose raw Markdown carries invisible characters and a `?output_format=md` link; the cleaned heading is in `heading`. All other lines are returned exactly as they appear. |
| `links <url>` | Lists child pages, for the 404 fallback |
| `sitemap <word> ...` | Searches the sitemaps. Slow (up to 15 fetches); last resort |

The script also enforces the rules a model can forget: only developer.wordpress.org and raw.githubusercontent.com are fetched, always as Markdown, and a URL is fetched over the network at most once per 10 minutes (repeats come from a temp-dir cache). Setting `WP_DOCS_LOG` logs each network fetch as JSON lines, so fetch counts can be measured. Failures exit 1 with a JSON error.

### Give-up rule

- Each part gets at most **3 fetches**: (1) the resolved URL; (2) on a 404, the parent page, following the matching link; (3) the sitemap.
- The whole call gets a hard cap of about **8 fetches**.
- Never fetch the same URL twice in one call.
- After the third failed fetch, mark the part **unresolved** with the URLs tried and the reason, and continue with the next part.

```text
Part: "derived state closures"
  1. manifest → .../derived-state-and-config/  ✅
Part: "wp_interactivity_get_context"
  1. reference pattern → 404
  2. parent page → link found ✅
Part: "some missing thing"
  1. index: no match   2. parent: no link   3. sitemap: no match
  → unresolved: { part, tried: [3 urls], reason }
```

## Rules

- Deliver Markdown or code, never HTML. This is a hard rule.
- Return at most one snippet per part. Include a props table only if it was requested.
- Copy the snippet exactly as it appears in the page's Markdown. A snippet must be present byte for byte in the page.
- Fill fields only from the fetched page. Do not add props, versions or code from memory.
- If a page marks the resource as deprecated, say so in `note`, with the version and the replacement if given.
- If the resource found differs from what was asked for (the prompt says "TextArea", the component is `TextareaControl`), set `matches_request` to `false` and explain in `note`. Never substitute silently.
- The Markdown's anchor links all carry `?output_format=md`. Strip that parameter from the `url` you return, so it is the clean canonical URL.
- If the relevant section is too large, set `truncated` to `true` and give a `section_hint` (a heading or anchor) so the caller can fetch only that part.
- If a part cannot be found within its 3 fetches, add it to `unresolved` instead of `results`. Never fill the gap from memory.

## Return Format

One JSON object. `results` has one entry per resolved part; `unresolved` has one entry per part that was not found (an empty list if none).

```json
{
  "results": [
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
  ],
  "unresolved": [
    {
      "part": "some missing thing",
      "tried": [ "https://developer.wordpress.org/...", "https://developer.wordpress.org/..." ],
      "reason": "No manifest match; the parent page has no matching link; the sitemap has no match."
    }
  ]
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
| `snippet` | The relevant Markdown or code, copied from the page |
| `truncated` | `true` if the relevant section was too large to return |
| `section_hint` | Heading or anchor to fetch when `truncated` is `true` |
| `unresolved[].part` | The part of the request that was not found |
| `unresolved[].tried` | URLs fetched for that part (at most 3) |
| `unresolved[].reason` | Why it failed |

## Example

**Prompt:** Show me the code for a sample TextArea component

**Workflow:** Calls Sonnet sub-agent. This task is not that complicated.

**Returns:** (snippet verified against the page's Markdown on 2026-11-07)

```json
{
  "results": [
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
  ],
  "unresolved": []
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
