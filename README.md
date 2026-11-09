# WordPress Docs Skills: the docs-lookup agent

Think of a law firm.

The **lawyer** does the hard part: reading the situation, interpreting the law and deciding what to argue. The **associate** does the legwork. They go to the library, pull the cases and statutes that support the lawyer's argument, and come back with exact citations. The associate does not interpret the law and does not cite from memory. The lawyer should never have to read the whole library to make a decision.

This project applies the same split to building WordPress plugins with an AI model.

| In the law firm | In this project |
| --- | --- |
| The lawyer | The main model (Opus). It decides how to build the plugin and writes the code. |
| The associate | The **docs-lookup agent** (Sonnet). It finds the right documentation and returns a small, exact answer. |
| The law library | developer.wordpress.org and the Gutenberg handbook sources. |
| The library's research tools | [`agents/wp-docs.py`](agents/wp-docs.py), a helper script that finds pages, lists headings and pulls one section. |
| A citation | A canonical URL, the page's last-modified date, and a snippet copied word for word. |
| "This case has been overruled" | A deprecation note. The agent reported `ProgressBar` as deprecated; the lawyer avoided it. |
| "I couldn't find it, here is where I looked" | An `unresolved` entry. The agent says what it tried instead of inventing a citation. |

## Why have an associate at all

- **The lawyer's attention is limited.** A model's context window is its attention. A whole documentation page can be 50–80 KB. The associate reads it and returns about 2 KB.
- **APIs change.** Documentation pages are renamed, and APIs get deprecated, like cases that are later overturned. The associate checks the current documentation. A model citing from memory may cite something that no longer exists.
- **Interpretation stays with the lawyer.** The associate fills in nothing from memory. If a page doesn't cover something, the answer says so.

## How the associate works

1. **Find the page, never guess.** Handbook pages are found by searching Gutenberg's `docs/manifest.json`. PHP functions, hooks and classes use a fixed URL pattern. Anything else goes through the sitemap or the parent page. Slugs are never built from memory, because they get renamed.
2. **Read the table of contents first**, then pull only the one section that answers the question.
3. **Quote exactly.** The snippet must be an exact copy of the page's Markdown, not a paraphrase.
4. **Know when to stop.** Each part of a request gets at most 3 page lookups. If it still isn't found, it is reported as `unresolved`, and the associate moves on to the next part.

The mechanical steps (finding, fetching, extracting) live in a script, not in the agent's instructions. Scripts behave the same way every time, and the agent keeps only the judgment calls. Try it:

```sh
python3 agents/wp-docs.py resolve interactivity directives store   # find a page
python3 agents/wp-docs.py headings <url>                            # its table of contents
python3 agents/wp-docs.py section <url> "Heading" --max-lines 40    # one section, as JSON
```

## The evaluation

The open question is whether the firm does better work with an associate than with a lawyer who researches alone. Seven plugin-building prompts are run two ways:

| Arm | Who does the research |
| --- | --- |
| `agent` | Opus, with the docs-lookup associate |
| `bare` | Opus alone, fetching pages itself |

Every run starts completely cold, like an associate with no notes. It has a fresh process, an empty folder, no memory and no view of other runs. Time, tokens and cost come from Claude Code's own event log, not from the model. Generated plugins are checked with `php -l` and PHPCS, and a separate model scores them against a rubric without knowing which arm produced them.

Full details are in [`harness-evaluation.md`](harness-evaluation.md).

## Where things stand (2026-11-09)

- **v1 is done.** The scores tied, 99% each, because the prompts named the APIs. That measured instruction-following, not research. One real difference showed up: the associate caught a deprecated component that the lawyer working alone used. Opus also delegated in only 3 of 8 prompts. See [issue #3](https://github.com/flexseth/docs-skills/issues/3).
- **v2 changes the prompts to describe outcomes only**, so the evaluation tests research and not instruction-following.
- **The associate is reworked.** v1 showed it guessing renamed URLs and failing. It now finds pages through the manifest and the script. In live checks it found both renamed Interactivity API pages without a failed fetch, and it correctly reported a made-up page as `unresolved`. See [issue #2](https://github.com/flexseth/docs-skills/issues/2).
- **Not solved yet:** the associate does not always quote exactly. In a repeated check of the same section, it once dropped a paragraph and once unwrapped a line. The evaluation now measures this, but the fix is still being worked out.
- **Not run yet:** the full v2 evaluation. A pilot run is the next step.
- **Later:** let the associate keep case notes, a cache of pages it has already verified, so repeat lookups are faster. It is kept off during the evaluation so every run starts cold.

## What is in this branch

| Path | What it is |
| --- | --- |
| [`agents/docs-lookup.md`](agents/docs-lookup.md) | The associate's instructions: judgment calls and the answer format |
| [`agents/wp-docs.py`](agents/wp-docs.py) | The research tools (standard library only) |
| [`agents/test_wp_docs.sh`](agents/test_wp_docs.sh) | Checks the tools' cache in both modes |
| [`docs-lookup-agent.md`](docs-lookup-agent.md) | The design and spec for the associate |
| [`harness-evaluation.md`](harness-evaluation.md) | How the evaluation works and how to run it |
| `run.sh`, `run_all.sh` | Run one comparison, or all of them |
| `summarize.py`, `score.py`, `judge.py` | Measure, check and score the results |
| `prompts/`, `rubrics/` | The seven prompts and how each is scored |
| `results-v1/` | The raw data and write-up from the first run |

## License

See [LICENSE](LICENSE).
