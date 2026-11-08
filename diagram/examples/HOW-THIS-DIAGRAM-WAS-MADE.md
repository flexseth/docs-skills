# How the Reference Guides diagram was made

This file records how the docs lookup agent and a diagram step produced `gutenberg-reference-guides.md` in this folder. It uses the prompts and results from the session on 2026-11-08.

## Roles

| Step | Who | Job |
| --- | --- | --- |
| 1. Plan and delegate | Calling agent (the main session) | Reads the request and decides it needs a docs lookup. Builds the sub-agent prompt. |
| 2. Lookup | Docs lookup agent (Sonnet sub-agent) | Fetches pages as Markdown and returns titles and canonical URLs. No page content. |
| 3. Diagram | Calling agent | Turns the returned outline into a Mermaid flowchart and writes the notes. |

The split follows `docs-lookup-agent/docs-lookup-agent.md`. The calling agent keeps the judgment work. The cheaper model does the page fetching, so full documentation pages stay out of the main context.

## Step 0: the user's request

> I want to create an example diagram. Research the Gutenberg block editor documentation reference guide and create a simple diagram that shows the hierarchical structure of those pages on developer.wordpress.org. Try to use the docs lookup agent skill.

## Step 1: what the spec says to do

From `docs-lookup-agent/docs-lookup-agent.md`:

- Resolve the request to a canonical URL.
- Fetch `<canonical URL>?output_format=md`. The Markdown is about 33x smaller than the HTML.
- Extract only what is needed. Never return a whole page.
- Fill fields only from the fetched page. Never add from memory.
- Strip `?output_format=md` from returned URLs.
- If the lookup fails or is incomplete, say so in `note`.

`docs-lookup-agent/docs-lookup-agent.md` is a draft spec, not an installed agent or skill. The calling agent applied it by hand in the sub-agent prompt below.

## Step 2: the sub-agent prompt (Sonnet)

> You are acting as a WordPress docs lookup sub-agent. Task: map the page hierarchy of the Gutenberg Block Editor "Reference Guides" section on developer.wordpress.org.
>
> Procedure:
> - Fetch https://developer.wordpress.org/block-editor/reference-guides/?output_format=md (every developer.wordpress.org page supports ?output_format=md, returning Markdown ~33x smaller than HTML). Use that parameter on every fetch.
> - From that page, list the top-level children (title + URL). Then fetch each top-level child's page (with ?output_format=md) and list its immediate children (title + URL). Go two levels deep below Reference Guides; for the very large "Components" section and the "Packages" section, do NOT enumerate every item: report only the child count plus 4-5 representative examples each.
> - Fill fields only from fetched pages; do not add from memory. If something fails, say so.
> - Return clean canonical URLs (strip ?output_format=md). Return only a compact result, no page content: a nested outline in this form: Title — URL / Child title — URL. Plus a note on anything that failed or was truncated, and the 'Last modified' date of the root page if given.

## Step 3: what the sub-agent returned

It was a partial result. It fetched 2 pages, the root and Block API. Both returned HTTP 200. The root page links most section landing pages as anchors, not URLs, and the sub-agent would not guess URLs.

- **Root:** Reference Guides, `https://developer.wordpress.org/block-editor/reference-guides/`. Last modified October 14, 2026.
- **Block API Reference** (`.../block-api/`, fetched, 16 children): Annotations, API Versions, Attributes, Bindings, Context, Deprecation, Edit and Save, Metadata in block.json, Patterns, Registration, Selectors, Styles, Supports, Transformations, Templates, Variations. The root page's list omits Bindings, Selectors and Styles.
- **Hooks Reference** (children under `.../filters/`, from the root page): Block Filters, Editor Hooks, i18n Hooks, Parser Hooks, Autocomplete, Global Styles Hooks.
- **SlotFills Reference** (9 children under `.../slotfills/`, from the root page): MainDashboardButton, PluginBlockSettingsMenuItem, PluginDocumentSettingPanel, PluginMoreMenuItem, PluginPostPublishPanel, PluginPostStatusInfo, PluginPrePublishPanel, PluginSidebar, PluginSidebarMoreMenuItem.
- **Theme.json Reference** (children under `.../theme-json-reference/`, from the root page): Version 3 (latest), Version 2, Version 1, Migrating to Newer Versions.
- **RichText, Component and Package Reference:** the root lists no children and gives anchors only. Not fetched, so no counts or examples.
- **Data Module Reference** (`.../data/`, from the root page): `core: WordPress Core Data`, with one child per store (block-editor, blocks, editor, edit-post, edit-site, notices, preferences and others). The sub-agent said 13 stores but listed 14.

The sub-agent offered to fetch the missing section pages if given their URLs.

## Step 4: building the diagram

Mermaid is the diagram format here because it is plain text, renders on GitHub and in many Markdown viewers, and diffs cleanly.

The calling agent wrote the Mermaid by hand. No dedicated Mermaid skill was invoked. The skills list in this session had no Mermaid skill. The closest are `artifact-diagramming` and `dataviz`, and they target published artifacts and charts. If you want a Mermaid skill, this is where it would plug in. It would take the sub-agent's outline as input and return the `flowchart` block. It would also apply conventions such as a dashed style for unfetched nodes and a cap on label length.

Choices made when turning the outline into a diagram:

- **Direction:** `flowchart LR`, so the root sits on the left and sections fan out to the right.
- **Depth:** two levels below the root. The Block API (16), SlotFills (9) and `core/*` store pages are collapsed into one summary node each, so the diagram stays simple.
- **Unfetched sections:** RichText, Components and Packages are drawn as dashed nodes. Dashed means "children not enumerated", not "has no children".
- **Disagreements between pages:** Block API uses the section page's 16 children, not the root page's shorter list. The diagram notes this.
- **Unverified counts:** the `core` store count is left out because the sub-agent's count and list disagreed.

## Output

- `gutenberg-reference-guides.md`: the Mermaid diagram with notes on gaps.
- This file: how it was produced.

## To complete the picture

1. Run a second lookup for the Components and Packages section pages, following the "Next" links from each section page. Give the sub-agent those section URLs.
2. Replace the dashed nodes with real child counts and a few examples.
3. If this becomes a repeatable workflow, turn the lookup spec into an installed agent file and add a Mermaid diagram skill that consumes its output.
