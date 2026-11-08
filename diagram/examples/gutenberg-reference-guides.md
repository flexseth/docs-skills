# Block Editor Reference Guides: page hierarchy

Source: https://developer.wordpress.org/block-editor/reference-guides/ (root last modified 2026-10-14, fetched 2026-11-08 via the docs lookup agent pattern, `?output_format=md`).

```mermaid
flowchart LR
  root["Reference Guides<br/>/block-editor/reference-guides/"]

  root --> api["Block API Reference<br/>/block-api/"]
  root --> hooks["Hooks Reference<br/>/filters/"]
  root --> slot["SlotFills Reference<br/>/slotfills/"]
  root --> theme["Theme.json Reference<br/>/theme-json-reference/"]
  root --> rich["RichText Reference"]
  root --> comp["Component Reference"]
  root --> pkg["Package Reference"]
  root --> data["Data Module Reference<br/>/data/"]

  api --> api1["16 pages: Attributes, Bindings, Context,<br/>Deprecation, Edit and Save, block.json Metadata,<br/>Registration, Supports, Variations, ..."]
  hooks --> h1["Block Filters"]
  hooks --> h2["Editor Hooks"]
  hooks --> h3["i18n Hooks"]
  hooks --> h4["Parser Hooks"]
  hooks --> h5["Autocomplete"]
  hooks --> h6["Global Styles Hooks"]
  slot --> s1["9 pages: PluginSidebar, PluginDocumentSettingPanel,<br/>PluginPrePublishPanel, MainDashboardButton, ..."]
  theme --> t1["Version 3 (latest)"]
  theme --> t2["Version 2"]
  theme --> t3["Version 1"]
  theme --> t4["Migrating to Newer Versions"]
  data --> d1["core: WordPress Core Data"]
  d1 --> d2["core/* stores: block-editor, blocks, editor,<br/>edit-post, edit-site, notices, preferences, ..."]

  classDef unfetched stroke-dasharray: 4 3;
  class rich,comp,pkg unfetched;
```

Dashed nodes are sections whose children were not enumerated. The root page lists no children for them, and their landing-page URLs were not linked from the root.

## Notes

- Block API: the root page lists fewer items than the section page. The root omits Bindings, Selectors and Styles. The diagram uses the section page's 16.
- Hooks, SlotFills, Theme.json and Data children come from the root page only. Their section landing pages were not fetched.
- Not covered: Components and Packages, the two largest sections. Child counts are unknown. A second lookup of those section pages would fill them in.
