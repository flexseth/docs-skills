# Eval summary

Means across repeats. Time and cost come from Claude Code's own result event.

| Prompt | Arm | Reps | Time (s) | Cost ($) | Tokens | WebFetch main/sub | Lookups | Docs read | Script net/cached | Snippets exact/reflowed/total | PHP lint errs | PHPCS errs | Plugin Check E/W | Deprecated APIs | Judge % | Flags |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 00 | agent | 1 | 33 | 0.19 | 94,620 | 1.0/0.0 | 0.0 | 1.0 | 0.0/0.0 | 0.0/0.0/0.0 | 0.0 | 0 | 1.0/0.0 | 0.0 | – | 1 |
| 00 | bare | 1 | 41 | 0.20 | 110,742 | 2.0/0.0 | 0.0 | 2.0 | 0.0/0.0 | 0.0/0.0/0.0 | 0.0 | 0 | 1.0/0.0 | 0.0 | – | 0 |

## By arm

| Arm | Runs | Mean time (s) | Mean cost ($) | Mean tokens | Mean judge % |
| --- | --- | --- | --- | --- | --- |
| agent | 1 | 33 | 0.19 | 94,620 | – |
| bare | 1 | 41 | 0.20 | 110,742 | – |
