# Common Documentation Issues: WordPress Documentation Issue Tracker

**Snapshot:** 2026-10-16
**Source:** [WordPress/Documentation-Issue-Tracker](https://github.com/WordPress/Documentation-Issue-Tracker)

**Open issues reviewed:** 469 (all of them)

---

## Summary

- **Most of the backlog is stale.** Median issue age is 805 days (about 2.2 years). The oldest open issue, [#14](https://github.com/WordPress/Documentation-Issue-Tracker/issues/14), is about 5 years old.
- **Very little is moving.** Only 33 issues (7%) have a human comment in the last 90 days, and 14 in the last 30 days. 407 issues (87%) have had no human comment in 180 days, or ever.
- **Many issues never got a response.** 180 issues (38%) have zero human comments. Another 97 have one.
- **Claims stall.** 60 issues have been claimed (`/assign`, "I'd like to work on this"). 48 of those have no human comment in the last 90 days.
- **Some work is done but still open.** At least 9 issues look closeable from their own threads (see [Closeable now](#closeable-now)).
- **The biggest single driver is WordPress release churn.** 92 issues (20%) are tied to a release (6.4 through 7.1). They are mostly screenshot, video, and wording refreshes for the Site Editor and block articles.
- **Two large tracking efforts sit open.** The HelpHub overhaul (52 HelpHub overhaul issues) and the Theme Handbook overhaul (17 issues) are both mostly waiting for contributors to pick up work.
- **Surfaces:** HelpHub (user docs) holds about half the backlog (235). The Theme Handbook is the most dormant surface: 72 of its 76 issues have had no human comment in 180 days.

---

## How this was compiled

- All 469 open issues were fetched with their bodies, labels, and all 1,498 comments (via `gh`).
- **Human activity** excludes automation. There are 635 `github-actions` comments (label notices, "thanks for your interest" messages) and one bot comment, so 862 human comments remain.
- **Staleness** is measured from `createdAt`, and **last activity** from the last human comment. The `updatedAt` field was not used: many issues show a bulk update about 191 days ago, which does not reflect real activity.
- **Change type** was assigned per issue from its title and labels, then manually reviewed and corrected for about 60 issues. Treat the counts as close approximations, not exact figures.
- **Claimed** means a human comment asked to work on the issue or used `/assign`, `/take`, or similar. This is a heuristic.
- **Closeable** means a human comment in the thread says the work is done or recommends closing. I read those comments directly.
- **PR links** in issues mostly point to the Gutenberg or wordpress-develop feature PRs that caused the change, not to documentation PRs. Those are noted, but they do not mean docs work is underway.

---

## Most common issues (ranked)

Ranked by open issue count. Process and contributor tasks are listed separately because they are not documentation changes.

| Rank | Change needed | Open issues | Median age | No human comment in 180 days | Mostly on |
|---|---|---|---|---|---|
| 1 | Refresh for a WordPress release (screenshots, videos, wording) | 92 | ~2.3 yrs | 84 of 92 | HelpHub Site Editor and block articles |
| 2 | Finish or restructure HelpHub overhaul tracking | 69 | ~6 months | 60 of 69 | HelpHub (52), Theme Handbook (17) |
| 3 | Write missing or new documentation | 57 | ~3.2 yrs | 53 of 57 | Theme Handbook, Code Reference, HelpHub |
| 4 | Triage HelpHub feedback-form submissions | 57 | ~2 yrs | 50 of 57 | HelpHub, Advanced Admin |
| 5 | Correct inaccurate or unclear technical content | 42 | ~1.7 yrs | 35 of 42 | Code Reference, Theme Handbook |
| 6 | Refresh outdated or non-current text | 38 | ~3.2 yrs | 33 of 38 | HelpHub, Theme Handbook |
| 7 | Fix broken or outdated links | 23 | ~2.2 yrs | 15 of 23 | Code Reference, Theme Handbook |
| 8 | Finish migrating Codex content | 21 | ~3.1 yrs | 19 of 21 | DevHub, HelpHub |
| 9 | Fix code example errors | 17 | ~1.5 yrs | 15 of 17 | Theme Handbook, APIs, Code Reference |
| 10 | Fix typos, grammar, and formatting | 15 | ~2.2 yrs | 12 of 15 | HelpHub, Plugin Handbook |
| 11 | Replace broken or outdated images and media | 10 | ~1.8 yrs | 5 of 10 | HelpHub block pages |
| – | Process and contributor tasks (not doc changes) | 26 | ~3 yrs | 24 of 26 | Docs Handbook |

### 1. Refresh for a WordPress release (92 issues)

Screenshots, videos, and wording that no longer match the current release UI.

- **By version:** 6.4 (32), 6.6 (20), 6.7 (16), 6.5 (7), 7.1 (11), 7.X (4), 6.8 and 6.9 (2). The 6.4 set is the oldest; most were created in late 2023.
- **Surface:** almost all HelpHub. The 6 "Misc Dev Note" issues for 7.1 are DevHub and are counted under [DevHub: 7.1 dev notes](#devhub-71-dev-notes-6-issues).

**What's needed for each article** (taken from the issue template used in the 6.4 batch, e.g. [#1205](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1205)):

- [ ] Compare every screenshot with the current release UI. Retake any that differ, covering the same screen area.
- [ ] Re-record videos where the UI changed. For example, [#1210](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1210) needed new videos for all Command Palette clips.
- [ ] Update wording and headings to match new labels. Use sentence case for headings.
- [ ] Add ALT text to every image.
- [ ] Re-host images that are not in the WordPress.org media library (see [Images and media](#cross-cutting-systemic-problems)).
- [ ] Convert reusable blocks to regular blocks where the template asks for it.
- [ ] Update the changelog at the bottom of the article.

**Key issues and activity:**

- **6.4 batch (2023–2024).** Examples: [#1210](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1210) Command Palette (35 human comments; the most-discussed issue in the tracker; last human comment about 2.8 years ago), [#1228](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1228) Navigation (15 human comments; partly fixed upstream), [#1231](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1231) and [#1232](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1232) Templates and Styles (marked In progress). The screenshot, video, ALT, and heading sub-issues for these articles (e.g. [#1278](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1278), [#1282](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1282), [#1331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1331)) were split out, and most stalled after 2024. Revived claims appeared in 2026 (for example, [#1331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1331), claimed April 2026 and reviewed in July 2026).
- **6.6 and 6.7 batches (2024–2025).** Mostly 1–5 human comments each, with several labeled `[Status] Review`. Examples: [#1630](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1630) Synced Patterns, [#1748](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1748) Comment Template, [#1795](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1795) Block Bindings UI.
- **7.1 batch (2026).** Newest group. [#2464](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2464) media upload, [#2497](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2497) Button width, the Experimental set [#2480](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2480), [#2483](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2483), [#2484](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2484), [#2485](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2485), [#2486](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2486), and [#2515](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2515). Many link Gutenberg PRs that describe the feature.

### 2. Overhaul and rewrite tracking (69 issues)

Umbrella and per-article issues for rewriting articles, not single fixes.

- **HelpHub (52).** Tracking epics marked `READ FIRST`: [#2126](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2126) (block articles), [#2163](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2163) (Technical guides), [#2165](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2165) (Support guides), [#2181](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2181) (WordPress overview), [#2378](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2378) (Customization). Per-block issues run from [#2305](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2305) to [#2326](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2326) (Post Template, Comment blocks, Term Description, and others). Embeds are split across [#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329) to [#2338](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2338).
- **Theme Handbook (17).** Chapter-publishing issues, the umbrella [#816](https://github.com/WordPress/Documentation-Issue-Tracker/issues/816) (16 human comments), and chapters such as [#1195](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1195), [#1418](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1418), [#1419](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1419), [#1684](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1684), and [#1692](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1692).

**What's needed:** an audit of the article against the current release, a rewrite to the house style, updated screenshots and ALT text, and a check that every link works. Most tracking issues list those steps but have no owner.

**Activity:** The HelpHub overhaul issues are young (median about 6 months old), but most have 0–1 human comments. The bottleneck is access. In [#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329), a contributor who claimed the Embeds article reported (July 2026) that they had no article editing access and were waiting on a Slack reply. Several Theme Handbook chapters are published but their tracking issues are still open (see [Closeable now](#closeable-now)).

### 3. Missing or new documentation (57 issues)

Requests for pages that do not exist yet.

- **Theme Handbook (17):** e.g. [#105](https://github.com/WordPress/Documentation-Issue-Tracker/issues/105) (child themes in the block theme handbook), [#107](https://github.com/WordPress/Documentation-Issue-Tracker/issues/107) (site editing sliding scale), [#108](https://github.com/WordPress/Documentation-Issue-Tracker/issues/108) (new block theme pages; 10 human comments, partly published), [#380](https://github.com/WordPress/Documentation-Issue-Tracker/issues/380), [#381](https://github.com/WordPress/Documentation-Issue-Tracker/issues/381), [#382](https://github.com/WordPress/Documentation-Issue-Tracker/issues/382), [#661](https://github.com/WordPress/Documentation-Issue-Tracker/issues/661), [#662](https://github.com/WordPress/Documentation-Issue-Tracker/issues/662), [#665](https://github.com/WordPress/Documentation-Issue-Tracker/issues/665), [#1677](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1677) (auto-added skip links).
- **Code Reference and APIs (about 17):** [#370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/370) (action and filter reference; 6 human comments), [#379](https://github.com/WordPress/Documentation-Issue-Tracker/issues/379) (data module examples), [#2022](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2022) (Script Module API), [#2038](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2038) (`block.json` `viewScriptModule`), [#2161](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2161) (data-core entity config), [#2162](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2162) (package-admin-ui), [#1825](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1825) (REST API needs pretty permalinks).
- **HelpHub (16):** [#348](https://github.com/WordPress/Documentation-Issue-Tracker/issues/348) (multi-block keyboard selection), [#798](https://github.com/WordPress/Documentation-Issue-Tracker/issues/798) (licensing your own content), [#814](https://github.com/WordPress/Documentation-Issue-Tracker/issues/814) and [#1655](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1655) (rollbacks), [#1474](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1474) (understanding queries), [#2124](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2124) (Terms List block), [#2200](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2200) (real-time collaboration), [#2339](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2339) (accordion media), [#1791](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1791) (block bindings UI).

**What's needed:** a decision on scope (some requests overlap with articles that already exist), then a new page in the right handbook with links to the related reference page.

### 4. HelpHub feedback-form intake (57 issues)

Feedback submitted from article pages, filed one per article. Median age is about 2 years, and 29 of the 57 have zero human comments.

**What's needed:** triage each submission. Check whether the feedback still applies, merge it into the matching overhaul or release issue, and close duplicates. Turn anything valid into a concrete change list.

**Repeat clusters to merge:**

| Article | Issues |
|---|---|
| Synced patterns / reusable blocks | [#773](https://github.com/WordPress/Documentation-Issue-Tracker/issues/773), [#775](https://github.com/WordPress/Documentation-Issue-Tracker/issues/775), [#1437](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1437), [#1961](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1961) |
| Page Jumps | [#1699](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1699), [#1986](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1986) |
| Keyboard shortcuts (block editor) | [#1448](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1448), [#1979](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1979) |
| Customizer | [#1420](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1420), [#1977](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1977) |
| Site Editor | [#904](https://github.com/WordPress/Documentation-Issue-Tracker/issues/904), [#1952](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1952), [#1990](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1990) |
| Page/Post Settings sidebar | [#1704](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1704), [#1980](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1980) |
| Block editor overview | [#1080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1080), [#1951](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1951) |
| Updating WordPress | [#808](https://github.com/WordPress/Documentation-Issue-Tracker/issues/808), [#1954](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1954) |
| Settings Media screen | [#915](https://github.com/WordPress/Documentation-Issue-Tracker/issues/915), [#1975](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1975) |
| Hosting WordPress | [#1720](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1720), [#1721](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1721) (identical titles) |
| Advanced Admin: Optimization | [#824](https://github.com/WordPress/Documentation-Issue-Tracker/issues/824), [#1392](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1392) |

Several feedback items also duplicate the overhaul issues, e.g. [#1707](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1707) (reset password) and [#2171](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2171).

### 5. Inaccurate or unclear technical content (42 issues)

Mostly code reference parameters, defaults, and return values, plus theme.json details.

- **Code Reference (about 20):**
  - Wrong defaults or parameters: [#1467](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1467) and [#1470](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1470) (`WP_User_Query` `orderby`, duplicates), [#1849](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1849) (`wp_list_users` `exclude_admin` default, in review), [#2296](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2296) (`wp_list_pages` `sort_column` default), [#1384](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1384) (`WP_Query` `search_columns` undocumented).
  - Wrong descriptions: [#2291](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2291) (`is_single()` logic), [#2289](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2289) (`add_shortcode()` callback type), [#2288](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2288) (`register_rest_route()` `$route` is a regex), [#2295](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2295) (`add_submenu_page()` requirement), [#2298](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2298) (`wp_insert_post()` `tax_input`), [#2299](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2299) (screenshot extensions), [#2512](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2512) (`wp_handle_upload_prefilter`).
  - `get_user_meta()` family: [#2292](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2292), [#2293](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2293). #2293 has an open wordpress-develop PR ([#13494](https://github.com/WordPress/wordpress-develop/pull/13494)).
- **Theme Handbook (13):** [#1660](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1660) (theme.json dimensions, 6.6), [#1839](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1839) (wrong API version in the theme.json intro), [#1913](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1913) (typography `defaultFontSizes` missing), [#1469](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1469), [#1826](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1826) (`Update URI` missing), [#2009](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2009) and [#2010](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2010) (JSON style variations), and several "Just a confirmation" reviews ([#2016](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2016), [#2024](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2024), [#2025](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2025), [#2026](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2026), [#2027](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2027)).
- **APIs (5):** e.g. [#1804](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1804) (block supports spacing), [#1912](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1912) (Post API needs `DateTime`, self-assigned), [#1899](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1899) (REST JSON/XML).

**What's needed:** check each claim against the source at the current release tag. Code Reference pages are generated from DocBlocks in the wordpress-develop repo, so most of these fixes need a core DocBlock change, not a handbook edit. Theme.json pages need checking against the current schema version, and the template's `Update URI` header should be added everywhere it is missing.

### 6. Refresh outdated or non-current text (38 issues)

Pages whose facts are out of date and need a review, not just a patch.

- **HelpHub:** [#418](https://github.com/WordPress/Documentation-Issue-Tracker/issues/418) and [#724](https://github.com/WordPress/Documentation-Issue-Tracker/issues/724) (automated installers list), [#426](https://github.com/WordPress/Documentation-Issue-Tracker/issues/426) (FAQ), [#427](https://github.com/WordPress/Documentation-Issue-Tracker/issues/427) (Finding WP Help; 11 human comments of detailed review notes since 2023), [#457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/457) (raw handling), [#728](https://github.com/WordPress/Documentation-Issue-Tracker/issues/728), [#1496](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1496), [#2373](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2373) (login troubleshooting), [#2338](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2338) (outdated Embeds changelog), [#2206](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2206) (WordPress.org vs .com; 6 human comments), [#1464](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1464) (consolidate .org/.com docs).
- **Advanced Admin:** [#73](https://github.com/WordPress/Documentation-Issue-Tracker/issues/73) and [#79](https://github.com/WordPress/Documentation-Issue-Tracker/issues/79) (remove technical detail from end-user articles), [#438](https://github.com/WordPress/Documentation-Issue-Tracker/issues/438) (secure site), [#44](https://github.com/WordPress/Documentation-Issue-Tracker/issues/44) (multilingual plugins).
- **Theme Handbook:** [#465](https://github.com/WordPress/Documentation-Issue-Tracker/issues/465), [#466](https://github.com/WordPress/Documentation-Issue-Tracker/issues/466), [#467](https://github.com/WordPress/Documentation-Issue-Tracker/issues/467) (setup, `header.php`, validation), [#615](https://github.com/WordPress/Documentation-Issue-Tracker/issues/615) (classic blocks and shortcodes), [#663](https://github.com/WordPress/Documentation-Issue-Tracker/issues/663) (block theme content in the classic section), [#1634](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1634) (remove the accessibility resources pages; 11 human comments; a policy question).

**What's needed:** a fact check against the current product, then a rewrite. Several of these need a decision first, such as whether to keep a page at all.

### 7. Broken or outdated links (23 issues)

- **Code Reference:** broken `WpOrgRequests` references [#1451](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1451) and [#1457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1457), `WP_Styles` [#1454](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1454), trailing-slash anchors [#1793](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1793), `@wordpress/data` readme [#2000](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2000).
- **Theme Handbook:** Codex links on the reference page [#101](https://github.com/WordPress/Documentation-Issue-Tracker/issues/101), a broken security handbook path [#1657](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1657), a missing P2 theme link [#2032](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2032).
- **Plugin Handbook:** outdated external links [#2510](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2510) and [#2511](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2511), plus the Subversion page link [#2370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2370) (see [Closeable now](#closeable-now)).
- **HelpHub:** broken GitHub links at the bottom of articles [#2150](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2150), a broken video link [#1497](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1497), and broken images in Read More [#1807](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1807).
- **Advanced Admin:** 404s [#952](https://github.com/WordPress/Documentation-Issue-Tracker/issues/952) and [#960](https://github.com/WordPress/Documentation-Issue-Tracker/issues/960). #960's linked fix is already merged ([Advanced-administration-handbook #486](https://github.com/WordPress/Advanced-administration-handbook/pull/486)).

**What's needed:** check each link, and for Codex links, replace them with the DevHub or HelpHub equivalent (see [section 8](#8-codex-migration-and-redirects-21-issues)).

### 8. Codex migration and redirects (21 issues)

Old Codex pages still need to be moved, cleaned up, and redirected.

- **To DevHub:** [#329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/329), [#330](https://github.com/WordPress/Documentation-Issue-Tracker/issues/330), [#331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/331), [#333](https://github.com/WordPress/Documentation-Issue-Tracker/issues/333) (template pages), [#370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/370) (action and filter reference), [#380](https://github.com/WordPress/Documentation-Issue-Tracker/issues/380) (theme development), [#723](https://github.com/WordPress/Documentation-Issue-Tracker/issues/723) (WordPress.org API), [#624](https://github.com/WordPress/Documentation-Issue-Tracker/issues/624) (localisation).
- **To HelpHub:** [#327](https://github.com/WordPress/Documentation-Issue-Tracker/issues/327) (CSS), [#1096](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1096) (WordPress Versions), [#2265](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2265) and [#2266](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2266) (Twenty Thirteen and Fourteen).
- **Redirect and cleanup:** [#1506](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1506) (Codex pages still live after migration), [#1515](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1515) (`.htaccess` for subdirectories), [#2070](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2070) (language switcher redirect), [#2354](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2354) and [#2369](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2369) (outdated Codex links), [#2052](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2052) (`$paged` undocumented).

**What's needed:** per the migration template in [#329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/329): review for outdated content, check links, swap Codex links for DevHub or HelpHub ones, and open a new issue for any page with no equivalent. Some redirects are outside this repo. [#2148](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2148) notes that Codex redirects are waiting on a Meta Trac ticket ([#8194](https://meta.trac.wordpress.org/ticket/8194)).

### 9. Code example errors (17 issues)

Snippets that do not run, use the wrong function, or are labeled incorrectly.

- [#2357](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2357) (nonce example should unslash and sanitize), [#2244](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2244) (HTML error in `wp_register_ability`), [#2020](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2020) (JavaScript labeled as PHP), [#2003](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2003) (missing comma in JSON), [#2061](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2061) (missing `!`), [#2062](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2062) (wrong function), [#2218](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2218) (missing colon), [#1563](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1563) (missing braces), [#1579](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1579) (missing semicolon), [#1572](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1572) (wrong variable name in a filter), [#375](https://github.com/WordPress/Documentation-Issue-Tracker/issues/375) (escaping in user-contributed notes), [#1899](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1899) (REST JSON/XML).

**What's needed:** run each snippet, fix it, and check it follows the escaping and sanitizing rules. These are usually the fastest fixes in the tracker.

### 10. Typos, grammar, and formatting (15 issues)

- [#2245](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2245) (block editor overview: "the the", "Palete"; claimed May 2026; last human comment 164 days ago), [#2374](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2374) (Password Best Practices), [#2513](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2513) (Plugin Handbook headers), [#1895](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1895) (Plugin Handbook internationalization), [#1796](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1796) (`admin_menu` written as `adminmenu`), [#1848](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1848) (straight quotes style rule), [#1561](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1561) and [#1676](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1676) (formatting).

**What's needed:** one cleanup PR per article. Most of these have a clear before-and-after already written in the issue.

### 11. Screenshots, images, and media (10 issues)

Covered in [Cross-cutting systemic problems](#cross-cutting-systemic-problems) below.

### 12. Other (2 issues)

[#730](https://github.com/WordPress/Documentation-Issue-Tracker/issues/730) (testable, interactive documentation proposal) needs a design decision, not an edit.

---

## Cross-cutting systemic problems

These affect many surfaces at once and are worth fixing once rather than per issue.

### Images and media

- **Google-hosted images are breaking.** [#2080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2080) reports that several block pages load images from `lh3.googleusercontent.com`, which return "Your client does not have permission to get URL". [#1649](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1649) and [#2324](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2324) report the same problem, and [#1807](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1807) says its images live in a Google Doc.
- **Drive and Docs links are common.** About 27 issues mention Google Drive or Docs, mostly for video sources or working drafts. The fix is to move the final assets into the WordPress.org media library.
- **Screenshot refreshes are repeated per article.** Each release touches the same articles, so a shared checklist (see [Release refresh](#1-refresh-for-a-wordpress-release-92-issues)) would prevent repeat issues.

### Codex

- 30 issues reference a `codex.wordpress.org` URL. Codex migration is a separate workstream (21 open issues), and its redirect work is partly outside this repo.

### Feedback intake

- The feedback template generates one issue per submission, so repeat feedback on one article creates duplicates. See the [repeat clusters](#4-helphub-feedback-form-intake-57-issues).

### Claims and access

- 60 issues have been claimed. 48 of them have had no human comment in 90 or more days. Claims are not released when work stops.
- At least one claimed item was blocked by missing article edit access ([#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329)), which is a process gap for new contributors.

### Process

- 26 issues are about the docs process itself: onboarding ([#383](https://github.com/WordPress/Documentation-Issue-Tracker/issues/383), [#815](https://github.com/WordPress/Documentation-Issue-Tracker/issues/815), [#830](https://github.com/WordPress/Documentation-Issue-Tracker/issues/830)), workflow ([#1243](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1243), [#1244](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1244), [#1245](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1245)), conflicting instructions ([#2272](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2272), [#2280](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2280)), and the Slack vs. GitHub question ([#2207](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2207)). 24 of 26 have no human comment in 180 days.
- Contributor Day task lists are [#1920](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1920) and [#1922](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1922).

---

## Closeable now

These have a human comment in the thread that says the work is done, or recommends closing.

| Issue | Why it looks closeable | Last human activity |
|---|---|---|
| [#960](https://github.com/WordPress/Documentation-Issue-Tracker/issues/960) | 404 on the debug-JavaScript page. The linked fix ([#486](https://github.com/WordPress/Advanced-administration-handbook/pull/486)) is merged. Confirm the URL and close. | Last human comment about 149 days ago |
| [#2370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2370) | A comment on 30 June 2026 recommends closing as "no longer applicable" if the link is not needed. No objection recorded. | 39 days |
| [#2115](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2115) | An editor said in August 2026 the page now matches the 7.1 blocks, and that it "can be closed". | ~7 weeks |
| [#1977](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1977) | A commenter in August 2025 recommends closing (the reported issue is a user role limitation). | ~14 months |
| [#1513](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1513) | Marked resolved in November 2024. A new comment in October 2026 says the article was updated for 7.1. Confirm and close. | ~10 days |
| [#1259](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1259) | Theme Handbook chapters 1 and 2 are live (November 2023). The issue was left open for follow-up. | ~3 years |
| [#1256](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1256) | Theme Handbook chapter 5 is live (November 2023). | ~3 years |
| [#1131](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1131) | Theme Handbook chapter 3 is live (October 2023), left open "for a bit". | ~3 years |
| [#1907](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1907) | Duplicate of [#1492](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1492), noted in a May 2025 review. | ~10 months |

**Mostly done, needs a final check:** [#1312](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1312) (pagination typography; "can be closed after review", 2024), [#1290](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1290) (article is outdated, so a new issue was suggested in 2026), [#2148](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2148) (DevHub updated; waiting on Codex redirect).

---

## Breakdown by documentation surface

Counts are open issues per surface. Each section lists the common changes in that surface, the checklist for each, and the relevant activity.

| Surface | Open | Median age | No human comment in 180 days | Zero human comments |
|---|---|---|---|---|
| HelpHub (user docs) | 235 | 2.0 yrs | 199 | 92 |
| DevHub: Theme Handbook | 76 | 2.6 yrs | 72 | 24 |
| DevHub: Code Reference and other | 57 | 2.1 yrs | 44 | 25 |
| DevHub: APIs and block editor | 27 | 2.4 yrs | 26 | 11 |
| Docs process and contributor | 26 | 3.0 yrs | 24 | 7 |
| Advanced Admin | 20 | 3.3 yrs | 19 | 8 |
| DevHub: Plugin Handbook | 19 | 2.6 yrs | 14 | 6 |
| DevHub: 7.1 dev notes | 6 | 87 days | 6 | 6 |
| Codex (legacy site) | 3 | 1.0 yr | 3 | 1 |

### HelpHub (user docs): 235 issues

**Release refresh (83).** See [section 1](#1-refresh-for-a-wordpress-release-92-issues).

**Overhaul tracking (52).** See [section 2](#2-overhaul-and-rewrite-tracking-69-issues).

**Feedback (49).** See [section 4](#4-helphub-feedback-form-intake-57-issues).

**Other changes:**

- **Missing content (16):** see [section 3](#3-missing-or-new-documentation-57-issues).
- **Refresh (18):** see [section 6](#6-refresh-outdated-or-non-current-text-38-issues).
- **Images and links (9; main items shown):** Google-hosted images [#1649](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1649), [#2080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2080), [#2324](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2324), [#1807](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1807); missing images [#2118](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2118) (Comment Date); broken links [#1497](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1497), [#2150](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2150).
- **Typos (3):** [#2245](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2245), [#2374](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2374), [#52](https://github.com/WordPress/Documentation-Issue-Tracker/issues/52) (Glossary).
- **Codex migration (4):** [#327](https://github.com/WordPress/Documentation-Issue-Tracker/issues/327), [#1096](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1096), [#2265](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2265), [#2266](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2266).

**Needed:** the release-refresh checklist, and for feedback, triage and merge.

**Activity:** this is the largest surface, and most of it is dormant. Only 17 HelpHub issues had human activity in 90 days.

### DevHub: Theme Handbook: 76 issues

**Overhaul and chapter publishing (17).** See [section 2](#2-overhaul-and-rewrite-tracking-69-issues) and the [closeable list](#closeable-now) for chapters that are already live.

**Accuracy (13) and code examples (5).** See sections [5](#5-inaccurate-or-unclear-technical-content-42-issues) and [9](#9-code-example-errors-17-issues).

**Missing pages (17).** See [section 3](#3-missing-or-new-documentation-57-issues).

**Refresh (8).** See [section 6](#6-refresh-outdated-or-non-current-text-38-issues).

**Links (3):** [#101](https://github.com/WordPress/Documentation-Issue-Tracker/issues/101), [#1657](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1657), [#2032](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2032).

**Other items to note:**

- [#1634](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1634) and [#1678](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1678) need a scope decision before work can start.
- [#2148](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2148) (HTML5 theme support) is close to done; see the closeable list.

**Activity:** the most dormant surface. 72 of 76 issues have had no human comment in 180 days, and only one had a human comment in 90 days.

### DevHub: Code Reference and other: 57 issues

**Wrong parameters, defaults, and descriptions (about 20).** See [section 5](#5-inaccurate-or-unclear-technical-content-42-issues).

**Missing documentation (11).** For example [#1825](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1825), [#2172](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2172) (rotating SALT keys on live sites), [#1541](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1541) (WXR file standard), [#1860](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1860) (PHPUnit "W" output).

**Broken links (10).** See [section 7](#7-broken-or-outdated-links-23-issues). Items include [#1451](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1451), [#1454](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1454), [#1457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1457), [#1793](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1793).

**Code example errors (6).** See [section 9](#9-code-example-errors-17-issues).

**Process note:** most of the batch of 2026 accuracy items ([#2288](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2288) to [#2299](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2299)) are labeled `[Status] Review`. They are the most likely to close quickly once a reviewer picks them up.

**Activity:** 44 of 57 have had no human comment in 180 days. Only 11 had a human comment in 90 days.

### DevHub: APIs and block editor: 27 issues

- **Missing:** [#370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/370), [#379](https://github.com/WordPress/Documentation-Issue-Tracker/issues/379), [#2022](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2022), [#2038](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2038), [#2161](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2161), [#2162](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2162).
- **Inaccurate or unclear:** [#1804](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1804), [#1912](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1912), [#1899](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1899), [#2343](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2343) (streamline the Abilities API page).
- **Code examples:** [#2244](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2244), [#2020](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2020), [#1579](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1579).
- **Broken links:** [#1484](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1484) (Action reference links, self-assigned), [#2000](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2000), [#2119](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2119) (the `useEffect` import comes from `@wordpress/compose`).
- **Migration from Codex (7):** [#329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/329), [#330](https://github.com/WordPress/Documentation-Issue-Tracker/issues/330), [#331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/331), [#333](https://github.com/WordPress/Documentation-Issue-Tracker/issues/333), [#370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/370), [#723](https://github.com/WordPress/Documentation-Issue-Tracker/issues/723), [#624](https://github.com/WordPress/Documentation-Issue-Tracker/issues/624).

**Activity:** 26 of 27 have had no human comment in 180 days.

### DevHub: Plugin Handbook: 19 issues

- **Missing or new:** [#2067](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2067) (readme syntax examples), [#977](https://github.com/WordPress/Documentation-Issue-Tracker/issues/977) (activation and deactivation on multisite), [#1831](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1831) (readme requirements), [#1001](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1001) (text domain reason).
- **Links:** [#2510](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2510), [#2511](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2511), [#2370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2370) (closeable).
- **Typos and grammar:** [#1895](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1895), [#2513](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2513), [#794](https://github.com/WordPress/Documentation-Issue-Tracker/issues/794) (double "This").
- **Other:** [#35](https://github.com/WordPress/Documentation-Issue-Tracker/issues/35) (document WPCS; 9 human comments; last human comment Nov 2021), [#78](https://github.com/WordPress/Documentation-Issue-Tracker/issues/78) (Subversion best practices; waiting on the plugins team per a May 2026 comment), [#1508](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1508) (KSES on the internationalization security page; self-assigned).

**Activity:** 14 of 19 have had no human comment in 180 days.

### DevHub: 7.1 dev notes: 6 issues

[#2423](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2423) to [#2429](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2429) (responsive styles, block supports, DataViews, HTML API, hooks, and miscellaneous notes). Created about 3 months ago, with no human comments yet. The task is to write each dev note's content from the 7.1 release.

### Advanced Admin: 20 issues

- **Missing or new:** [#19](https://github.com/WordPress/Documentation-Issue-Tracker/issues/19) (moving from HTTP to HTTPS), [#656](https://github.com/WordPress/Documentation-Issue-Tracker/issues/656) (auto-update options; 4 human comments).
- **Broken or outdated:** [#14](https://github.com/WordPress/Documentation-Issue-Tracker/issues/14) (HTTPS with Caddy; 5 human comments; the oldest open issue in the tracker, about 5 years old).
- **Broken links:** [#952](https://github.com/WordPress/Documentation-Issue-Tracker/issues/952), [#960](https://github.com/WordPress/Documentation-Issue-Tracker/issues/960) (closeable).
- **Migration:** [#1385](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1385) (more robust import and migration guides; self-assigned).
- **Feedback (8):** [#824](https://github.com/WordPress/Documentation-Issue-Tracker/issues/824) and [#1392](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1392) (Optimization, duplicates), [#911](https://github.com/WordPress/Documentation-Issue-Tracker/issues/911) (multilingual), [#1055](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1055) (importing), [#1696](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1696) (block patterns), [#1707](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1707) (reset password), [#1708](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1708) (date and time format).
- **Content refresh (4):** [#44](https://github.com/WordPress/Documentation-Issue-Tracker/issues/44), [#73](https://github.com/WordPress/Documentation-Issue-Tracker/issues/73), [#79](https://github.com/WordPress/Documentation-Issue-Tracker/issues/79), [#438](https://github.com/WordPress/Documentation-Issue-Tracker/issues/438).

**Activity:** 19 of 20 have had no human comment in 180 days. This is the oldest surface (median 3.3 years).

### Codex (legacy site): 3 issues

[#1506](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1506), [#2052](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2052), [#2070](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2070). See [section 8](#8-codex-migration-and-redirects-21-issues) for the full Codex list, which also includes DevHub and HelpHub migration items.

### Docs process and contributor: 26 issues

See [Process](#process). Items: [#264](https://github.com/WordPress/Documentation-Issue-Tracker/issues/264) (working on GitHub issues), [#639](https://github.com/WordPress/Documentation-Issue-Tracker/issues/639) and [#640](https://github.com/WordPress/Documentation-Issue-Tracker/issues/640) (team badges and comment templates), [#648](https://github.com/WordPress/Documentation-Issue-Tracker/issues/648) (what HelpHub is), [#998](https://github.com/WordPress/Documentation-Issue-Tracker/issues/998) (project boards for release work), [#1070](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1070) (editing articles), [#1848](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1848) (quotes style), [#1858](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1858) (finding duplicates), [#2199](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2199) (overhaul handbook pages), [#2217](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2217) (labels guide), [#2280](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2280) (links to deleted workflow files).

---

## Recommended next steps

1. **Close the closeable set** (9 issues in [Closeable now](#closeable-now)). This is the fastest way to cut the backlog and needs only a maintainer.
2. **Merge the feedback clusters** (see [section 4](#4-helphub-feedback-form-intake-57-issues)). The 11 clusters hold 25 of the 57 feedback issues, so merging them alone removes about 14 duplicates.
3. **Re-host the Google-hosted images** in [#2080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2080), [#1649](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1649), [#2324](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2324), and [#1807](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1807).
4. **Clear the typo and code-example batch.** These are small, self-contained PRs (sections [9](#9-code-example-errors-17-issues) and [10](#10-typos-grammar-and-formatting-15-issues)).
5. **Ask for release-claim renewal.** Ask current claimants on the 48 stalled claims to confirm or release, so the work can be picked up again.
6. **Fix access for new contributors.** Document how to get article edit access, since it is blocking overhaul work ([#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329)).
7. **Decide scope for the two overhauls.** The HelpHub overhaul ([#2126](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2126)) and the Theme Handbook overhaul ([#816](https://github.com/WordPress/Documentation-Issue-Tracker/issues/816)) need an owner and a timeline before more per-article issues are opened.

---

## Limits of this review

- Change types and surfaces come from keyword matching on titles and labels, then manual correction. Expect a few misfiled items.
- Claim detection and "closeable" detection rely on comment wording. A few may be missed or over-counted.
- PR links are reported as found in the issue text and comments. I checked the state of a few but not all of them.
- The `updatedAt` field is unreliable here, so staleness uses the created date and last human comment.

---

## Appendix: all 469 open issues

Columns: **Age** is days since creation. **Human** is the number of human comments. **Last human** is days since the last human comment (`–` means none). **Signals** lists claimed, PR linked, closeable, duplicate, and status labels.

The appendix is grouped by documentation surface, then sorted by issue number.

### HelpHub (user docs) (235)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#41](https://github.com/WordPress/Documentation-Issue-Tracker/issues/41) | [HelpHub] Changelogs for Docs managed directly in WordPress | Missing / new documentation | 1845 | 1 | 1844 | – |
| [#52](https://github.com/WordPress/Documentation-Issue-Tracker/issues/52) | [HelpHub] Various small issues in the Glossary doc | Typos, grammar & formatting | 1833 | 2 | 1148 | – |
| [#327](https://github.com/WordPress/Documentation-Issue-Tracker/issues/327) | [Codex] Migrate CSS page from Codex to HelpHub | Codex migration / redirects | 1631 | 3 | 1616 | review |
| [#348](https://github.com/WordPress/Documentation-Issue-Tracker/issues/348) | [HelpHub] Keyboard shortcut for selecting text across multiple blocks is missing | Missing / new documentation | 1610 | 2 | 1592 | PR linked |
| [#372](https://github.com/WordPress/Documentation-Issue-Tracker/issues/372) | [HelpHub] Documentation licensing | Missing / new documentation | 1584 | 3 | 911 | claimed |
| [#418](https://github.com/WordPress/Documentation-Issue-Tracker/issues/418) | [HelpHub] Content review - Automated installation | Content refresh / outdated text | 1516 | 2 | 1451 | claimed, review |
| [#426](https://github.com/WordPress/Documentation-Issue-Tracker/issues/426) | [HelpHub] FAQ's review content | Content refresh / outdated text | 1508 | 3 | 1200 | claimed |
| [#427](https://github.com/WordPress/Documentation-Issue-Tracker/issues/427) | [HelpHub] Content review for Finding WP Help article | Broken / outdated links | 1508 | 11 | 1148 | claimed, review |
| [#457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/457) | [HelpHub] Content review: Raw Handling (Copy-Paste support) | Content refresh / outdated text | 1485 | 0 | – | – |
| [#722](https://github.com/WordPress/Documentation-Issue-Tracker/issues/722) | [HelpHub] How to switch to a block theme guide | Missing / new documentation | 1311 | 14 | 1165 | review |
| [#724](https://github.com/WordPress/Documentation-Issue-Tracker/issues/724) | [HelpHub] Content review - Use automated installation | Content refresh / outdated text | 1302 | 3 | 910 | review |
| [#725](https://github.com/WordPress/Documentation-Issue-Tracker/issues/725) | [HelpHub] Block Style | Missing / new documentation | 1302 | 1 | 1302 | – |
| [#728](https://github.com/WordPress/Documentation-Issue-Tracker/issues/728) | [HelpHub] Content review - Capability/Role list oddities about reusable blocks | Content refresh / outdated text | 1296 | 1 | 1296 | – |
| [#747](https://github.com/WordPress/Documentation-Issue-Tracker/issues/747) | [HelpHub] Style Overview: include new style features and more CSS info | Missing / new documentation | 1290 | 1 | 1290 | – |
| [#769](https://github.com/WordPress/Documentation-Issue-Tracker/issues/769) | [HelpHub] Feedback on Roles and Capabilities | Feedback-form intake | 1290 | 1 | 1290 | – |
| [#773](https://github.com/WordPress/Documentation-Issue-Tracker/issues/773) | [HelpHub] Feedback on Comparing Patterns, Template Parts, and Reusable Blocks | Feedback-form intake | 1290 | 1 | 1290 | dup cluster |
| [#775](https://github.com/WordPress/Documentation-Issue-Tracker/issues/775) | [HelpHub] Feedback on Reusable Blocks | Feedback-form intake | 1289 | 1 | 1289 | dup cluster |
| [#791](https://github.com/WordPress/Documentation-Issue-Tracker/issues/791) | [HelpHub] Shortcode block screenshots | Screenshots, images & media | 1280 | 4 | 49 | review |
| [#798](https://github.com/WordPress/Documentation-Issue-Tracker/issues/798) | [HelpHub] Documentation on how to license your own content | Missing / new documentation | 1270 | 3 | 1269 | – |
| [#808](https://github.com/WordPress/Documentation-Issue-Tracker/issues/808) | [HelpHub] Feedback on Updating WordPress | Feedback-form intake | 1269 | 6 | 85 | dup cluster |
| [#814](https://github.com/WordPress/Documentation-Issue-Tracker/issues/814) | [HelpHub] Rollback for plugins and themes | Missing / new documentation | 1267 | 2 | 193 | – |
| [#823](https://github.com/WordPress/Documentation-Issue-Tracker/issues/823) | [HelpHub] Feedback on Dimension Settings Overview | Feedback-form intake | 1253 | 4 | 1182 | in progress |
| [#904](https://github.com/WordPress/Documentation-Issue-Tracker/issues/904) | [HelpHub] Feedback on Site Editor | Feedback-form intake | 1201 | 0 | – | dup cluster |
| [#915](https://github.com/WordPress/Documentation-Issue-Tracker/issues/915) | [HelpHub] Feedback on Settings Media Screen | Feedback-form intake | 1200 | 0 | – | dup cluster |
| [#993](https://github.com/WordPress/Documentation-Issue-Tracker/issues/993) | [HelpHub] Feedback on Moving blocks | Feedback-form intake | 1173 | 2 | 78 | claimed |
| [#1004](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1004) | [HelpHub] Appearance Theme File Editor is missing one menu location | Content refresh / outdated text | 1169 | 3 | 1148 | review |
| [#1080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1080) | [HelpHub] Feedback on WordPress Block Editor | Feedback-form intake | 1148 | 0 | – | dup cluster |
| [#1096](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1096) | [Codex] Migrate "WordPress Versions" page from Codex to HelpHub | Codex migration / redirects | 1142 | 0 | – | in progress |
| [#1097](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1097) | [HelpHub] FAQ Troubleshooting page is a source of frustration for users | Feedback-form intake | 1142 | 2 | 968 | in progress |
| [#1202](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1202) | [HelpHub] [6.4] Site Editor | Release-cycle UI/content update | 1093 | 2 | 1072 | PR linked |
| [#1205](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1205) | [HelpHub] [6.4] Term Description Block | Release-cycle UI/content update | 1093 | 3 | 1058 | claimed, PR linked, review |
| [#1210](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1210) | [HelpHub] [6.4] Site Editor > Command Palette | Release-cycle UI/content update | 1092 | 35 | 1046 | PR linked, review |
| [#1219](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1219) | [HelpHub] [6.4] Post Content Block | Release-cycle UI/content update | 1092 | 1 | 961 | PR linked |
| [#1224](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1224) | [HelpHub] [6.4] Pagination Block | Release-cycle UI/content update | 1092 | 2 | 1066 | claimed, PR linked, in progress |
| [#1228](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1228) | [HelpHub] [6.4] Site Editor > Navigation | Release-cycle UI/content update | 1092 | 15 | 1059 | claimed, PR linked, review |
| [#1229](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1229) | [HelpHub] [6.4] Site Editor > Pages | Release-cycle UI/content update | 1092 | 0 | – | PR linked |
| [#1230](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1230) | [HelpHub] [6.4] Block Patterns | Release-cycle UI/content update | 1092 | 5 | 498 | PR linked |
| [#1231](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1231) | [HelpHub] [6.4] Site Editor > Templates | Release-cycle UI/content update | 1092 | 5 | 1068 | PR linked, in progress |
| [#1232](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1232) | [HelpHub] [6.4] Site Editor > Styles | Release-cycle UI/content update | 1092 | 4 | 723 | claimed, PR linked, in progress |
| [#1235](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1235) | [HelpHub] [6.4] WordPress Editor | Release-cycle UI/content update | 1092 | 0 | – | PR linked |
| [#1236](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1236) | [HelpHub] [6.4] Working with blocks | Release-cycle UI/content update | 1092 | 5 | 1065 | PR linked, in progress |
| [#1238](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1238) | [HelpHub] [6.4] Remove "post" from block titles | Release-cycle UI/content update | 1092 | 1 | 1050 | claimed, PR linked |
| [#1251](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1251) | [HelpHub][6.4][videos]: Working with blocks | Release-cycle UI/content update | 1086 | 3 | 415 | – |
| [#1270](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1270) | [HelpHub][6.4][videos]: WordPress Editor | Release-cycle UI/content update | 1081 | 2 | 912 | review |
| [#1277](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1277) | [HelpHub][6.4][headings]: Site Editor > Styles | Release-cycle UI/content update | 1079 | 1 | 165 | in progress |
| [#1278](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1278) | [HelpHub][6.4][screenshots]: Site Editor > Styles | Release-cycle UI/content update | 1079 | 7 | 923 | claimed, review |
| [#1279](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1279) | [HelpHub][6.4][videos]: Site Editor > Styles | Release-cycle UI/content update | 1079 | 2 | 889 | review |
| [#1280](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1280) | [HelpHub][6.4][ALT]: Site Editor > Templates | Release-cycle UI/content update | 1079 | 4 | 961 | review |
| [#1281](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1281) | [HelpHub][6.4][headings]: Site Editor > Templates | Release-cycle UI/content update | 1079 | 4 | 961 | review |
| [#1282](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1282) | [HelpHub][6.4][screenshots]: Site Editor > Templates | Release-cycle UI/content update | 1079 | 6 | 1059 | review |
| [#1283](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1283) | [HelpHub][6.4][videos]: Site Editor > Templates | Release-cycle UI/content update | 1079 | 4 | 918 | claimed, review |
| [#1287](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1287) | [HelpHub][6.4][videos]: Block Patterns | Release-cycle UI/content update | 1079 | 5 | 865 | review |
| [#1288](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1288) | [HelpHub][6.4][ALT]: Site Editor > Pages | Release-cycle UI/content update | 1079 | 5 | 942 | claimed, review |
| [#1290](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1290) | [HelpHub][6.4][screenshots]: Site Editor > Pages | Release-cycle UI/content update | 1079 | 5 | 112 | mostly done |
| [#1309](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1309) | [HelpHub][6.4][ALT]: Pagination Block | Release-cycle UI/content update | 1074 | 5 | 512 | review |
| [#1310](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1310) | [HelpHub][6.4][headings]: Pagination Block | Release-cycle UI/content update | 1074 | 5 | 512 | review |
| [#1312](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1312) | [HelpHub][6.4][videos]: Pagination Block | Release-cycle UI/content update | 1074 | 6 | 112 | mostly done, review |
| [#1329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1329) | [HelpHub][6.4][ALT]: Post Content Block | Release-cycle UI/content update | 1074 | 4 | 190 | claimed, review |
| [#1331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1331) | [HelpHub][6.4][screenshots]: Post Content Block | Release-cycle UI/content update | 1074 | 9 | 99 | claimed, review |
| [#1332](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1332) | [HelpHub][6.4][videos]: Post Content Block | Release-cycle UI/content update | 1074 | 4 | 982 | review |
| [#1381](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1381) | [HelpHub] Missing information on uploading a theme zip from wp-admin | Missing / new documentation | 1057 | 1 | 1029 | – |
| [#1389](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1389) | [HelpHub] Feedback on Protect posts with password | Feedback-form intake | 1042 | 0 | – | – |
| [#1390](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1390) | [HelpHub] Feedback on Create a Favicon | Feedback-form intake | 1042 | 0 | – | – |
| [#1393](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1393) | [HelpHub] Feedback on Post Status | Feedback-form intake | 1042 | 0 | – | – |
| [#1394](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1394) | [HelpHub] Feedback on Excerpt Block | Feedback-form intake | 1042 | 0 | – | – |
| [#1420](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1420) | [HelpHub] Feedback on Customizer | Feedback-form intake | 1000 | 0 | – | dup cluster |
| [#1422](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1422) | [HelpHub] Feedback on Write Posts (Classic Editor) | Feedback-form intake | 1000 | 0 | – | – |
| [#1432](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1432) | [HelpHub] Feedback on Understand comment spam | Feedback-form intake | 1000 | 0 | – | – |
| [#1437](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1437) | [HelpHub] Feedback on Comparing Patterns, Template Parts, and Synced Patterns (Reusable… | Feedback-form intake | 1000 | 0 | – | dup cluster |
| [#1448](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1448) | [HelpHub] Feedback on Use keyboard shortcuts (Block Editor) | Feedback-form intake | 989 | 0 | – | dup cluster |
| [#1464](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1464) | [HelpHub] Consolidate WordPress.com vs. WordPress.org documentation | Content refresh / outdated text | 969 | 3 | 822 | review |
| [#1465](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1465) | [HelpHub] User Flow Exploration: FAQ Troubleshooting | Content refresh / outdated text | 968 | 0 | – | – |
| [#1474](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1474) | [HelpHub] Understanding WordPress Queries | Missing / new documentation | 959 | 0 | – | – |
| [#1485](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1485) | [HelpHub] Default Theme list: Add missing docs | Missing / new documentation | 947 | 5 | 793 | in progress |
| [#1492](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1492) | [HelpHub] [6.5] Update content for Settings General Screen | Release-cycle UI/content update | 941 | 4 | 913 | dup cluster, review |
| [#1493](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1493) | [HelpHub] Oops! It seems that the email you used has opted out of subscriptions | Content refresh / outdated text | 940 | 2 | 266 | – |
| [#1496](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1496) | [HelpHub] delete some false content in WordPress block editor | Content refresh / outdated text | 939 | 1 | 898 | – |
| [#1497](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1497) | [HelpHub] Broken link to a video in WordPress block editor | Screenshots, images & media | 939 | 1 | 898 | – |
| [#1499](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1499) | [6.5 HelpHub] Data Views | Release-cycle UI/content update | 938 | 0 | – | – |
| [#1500](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1500) | [6.5 HelpHub] Styles Revisions | Release-cycle UI/content update | 938 | 2 | 889 | – |
| [#1510](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1510) | [6.5 HelpHub] Site Editor Pages | Release-cycle UI/content update | 935 | 4 | 844 | review |
| [#1511](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1511) | [6.5 HelpHub] Block Patterns | Release-cycle UI/content update | 935 | 4 | 837 | review |
| [#1512](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1512) | [6.5 HelpHub] Preferences overview | Release-cycle UI/content update | 935 | 2 | 904 | review |
| [#1513](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1513) | [6.5 HelpHub] Distraction free mode | Release-cycle UI/content update | 935 | 10 | 10 | claimed, **closeable**, review |
| [#1538](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1538) | [HelpHub] Changing the category "Installation" | Content refresh / outdated text | 910 | 2 | 877 | in progress |
| [#1556](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1556) | [HelpHub] Troubleshooting Login Issues | Content refresh / outdated text | 891 | 0 | – | – |
| [#1630](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1630) | [6.6 HelpHub]: Synced Patterns | Release-cycle UI/content update | 826 | 3 | 598 | PR linked, review |
| [#1632](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1632) | [6.6 HelpHub]: Styles overview | Release-cycle UI/content update | 826 | 0 | – | PR linked |
| [#1636](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1636) | [6.6 HelpHub]: Block Patterns | Release-cycle UI/content update | 825 | 0 | – | PR linked |
| [#1637](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1637) | [6.6 HelpHub]: Data Views in the Site Editor | Release-cycle UI/content update | 825 | 1 | 727 | PR linked |
| [#1638](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1638) | [6.6 HelpHub]: Site Editor navigation | Release-cycle UI/content update | 825 | 0 | – | PR linked |
| [#1640](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1640) | [6.6 HelpHub]: WordPress Block Editor | Release-cycle UI/content update | 824 | 0 | – | PR linked |
| [#1641](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1641) | [6.6 HelpHub]: Page/Post Settings sidebar | Release-cycle UI/content update | 824 | 0 | – | PR linked |
| [#1642](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1642) | [6.6 HelpHub]: Editor Command Palette | Release-cycle UI/content update | 824 | 0 | – | PR linked |
| [#1644](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1644) | [6.6 HelpHub]: Post Featured Image block | Release-cycle UI/content update | 823 | 1 | 276 | PR linked |
| [#1647](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1647) | [6.6 HelpHub]: Site Editor Pages | Release-cycle UI/content update | 823 | 2 | 385 | PR linked |
| [#1649](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1649) | [HelpHub] Broken and missing screenshots (mostly block pages) | Screenshots, images & media | 822 | 3 | 181 | – |
| [#1650](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1650) | [6.6 HelpHub]: Site Tagline block | Release-cycle UI/content update | 822 | 1 | 780 | PR linked |
| [#1652](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1652) | [6.6 HelpHub]: Child navigation blocks | Release-cycle UI/content update | 822 | 1 | 276 | PR linked |
| [#1655](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1655) | [6.6 HelpHub]: Rollbacks for plugin auto-updates | Release-cycle UI/content update | 818 | 0 | – | – |
| [#1656](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1656) | [7.1 ? HelpHub] Post Author block deprecation | Release-cycle UI/content update | 815 | 6 | 13 | PR linked, in progress |
| [#1664](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1664) | [6.6 HelpHub]: Site Editor | Release-cycle UI/content update | 807 | 1 | 394 | PR linked |
| [#1666](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1666) | [6.6 HelpHub]: Dimension controls overview | Release-cycle UI/content update | 806 | 1 | 758 | claimed, PR linked |
| [#1667](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1667) | [6.6 HelpHub]: Settings sidebar in the site editor | Release-cycle UI/content update | 806 | 2 | 391 | claimed |
| [#1669](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1669) | [6.6 HelpHub]: Adding a new bloc | Release-cycle UI/content update | 806 | 1 | 400 | PR linked |
| [#1670](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1670) | [6.6 HelpHub]: Appearance Patterns Screen | Release-cycle UI/content update | 805 | 1 | 696 | PR linked |
| [#1699](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1699) | [HelpHub] Feedback on Page Jumps | Feedback-form intake | 742 | 4 | 82 | dup cluster, review |
| [#1701](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1701) | [HelpHub] Feedback on Customize permalinks | Feedback-form intake | 742 | 2 | 623 | review |
| [#1704](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1704) | [HelpHub] Feedback on Page/Post Settings sidebar | Feedback-form intake | 742 | 6 | 23 | claimed, PR linked, dup cluster, review |
| [#1711](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1711) | [HelpHub] Feedback on First steps with WordPress (Block Editor) | Feedback-form intake | 737 | 2 | 108 | claimed |
| [#1718](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1718) | [HelpHub] Feedback on Appearance Themes Screen | Feedback-form intake | 737 | 1 | 618 | review |
| [#1719](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1719) | [HelpHub] Feedback on Pagination block | Feedback-form intake | 737 | 2 | 624 | review |
| [#1720](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1720) | [HelpHub] Feedback on Hosting WordPress | Feedback-form intake | 737 | 0 | – | dup cluster |
| [#1721](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1721) | [HelpHub] Feedback on Hosting WordPress | Feedback-form intake | 737 | 0 | – | dup cluster |
| [#1748](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1748) | [6.7 HelpHub]: Comment Template block | Release-cycle UI/content update | 719 | 2 | 610 | PR linked |
| [#1755](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1755) | [6.7 HelpHub]: Term Description block | Release-cycle UI/content update | 719 | 2 | 626 | PR linked, review |
| [#1756](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1756) | [6.7 HelpHub]: Tag Cloud block | Release-cycle UI/content update | 719 | 2 | 626 | PR linked, review |
| [#1760](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1760) | [6.7 HelpHub]: Post Comments Form block | Release-cycle UI/content update | 719 | 2 | 610 | PR linked, in progress |
| [#1765](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1765) | [6.7 HelpHub]: Post Excerpt block | Release-cycle UI/content update | 719 | 3 | 618 | PR linked, review |
| [#1768](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1768) | [6.7 HelpHub]: Site Tagline block | Release-cycle UI/content update | 719 | 3 | 618 | PR linked, review |
| [#1769](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1769) | [6.7 HelpHub]: Site Title block | Release-cycle UI/content update | 719 | 2 | 618 | PR linked, review |
| [#1770](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1770) | [6.7 HelpHub]: Comment Author Name block | Release-cycle UI/content update | 719 | 2 | 616 | PR linked, review |
| [#1771](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1771) | [6.7 HelpHub]: Comment Content | Release-cycle UI/content update | 719 | 2 | 606 | PR linked, review |
| [#1772](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1772) | [6.7 HelpHub]: Comment Date block | Release-cycle UI/content update | 719 | 2 | 617 | PR linked, review |
| [#1773](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1773) | [6.7 HelpHub]: Post Author Biography block | Release-cycle UI/content update | 719 | 2 | 619 | PR linked, review |
| [#1779](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1779) | [6.7 HelpHub]: Data Views updates | Release-cycle UI/content update | 719 | 1 | 277 | PR linked |
| [#1782](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1782) | [6.7 Helphub] Post Content Block | Release-cycle UI/content update | 718 | 2 | 603 | PR linked |
| [#1788](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1788) | [6.7 HelpHub]: Document Settings | Release-cycle UI/content update | 718 | 1 | 601 | PR linked, review |
| [#1791](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1791) | [6.7 HelpHub]: Block Bindings User Interface (Needs new page) | Release-cycle UI/content update | 717 | 0 | – | – |
| [#1795](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1795) | [6.7 HelpHub]: Block Bindings UI Updates | Release-cycle UI/content update | 711 | 6 | 458 | PR linked |
| [#1807](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1807) | [HelpHub] Broken links to images in the post Read More block | Screenshots, images & media | 672 | 3 | 154 | claimed |
| [#1906](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1906) | The block editor opens by default in fullscreen mode | Content refresh / outdated text | 534 | 1 | 470 | – |
| [#1907](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1907) | [HelpHub] Update General Settings page to show Site Icon has been added | Content refresh / outdated text | 533 | 6 | 302 | claimed, **closeable**, dup cluster, review |
| [#1951](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1951) | [HelpHub] Feedback on WordPress Block Editor | Feedback-form intake | 483 | 0 | – | dup cluster |
| [#1952](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1952) | [HelpHub] Feedback on Site Editor | Feedback-form intake | 479 | 0 | – | dup cluster |
| [#1953](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1953) | [HelpHub] Feedback on Site Logo block | Feedback-form intake | 479 | 0 | – | – |
| [#1954](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1954) | [HelpHub] Feedback on Updating WordPress | Feedback-form intake | 479 | 2 | 87 | dup cluster |
| [#1955](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1955) | [HelpHub] Feedback on Posts screen | Feedback-form intake | 479 | 0 | – | – |
| [#1957](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1957) | [HelpHub] Feedback on Revisions | Feedback-form intake | 479 | 0 | – | – |
| [#1958](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1958) | [HelpHub] Feedback on WordPress Glossary | Feedback-form intake | 479 | 0 | – | – |
| [#1959](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1959) | [HelpHub] Feedback on WordPress Semantics | Feedback-form intake | 479 | 0 | – | – |
| [#1960](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1960) | [HelpHub] Feedback on Assign custom fields | Feedback-form intake | 479 | 1 | 472 | claimed |
| [#1961](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1961) | [HelpHub] Feedback on Synced Patterns (Reusable blocks) | Feedback-form intake | 479 | 0 | – | dup cluster |
| [#1965](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1965) | [HelpHub] Feedback on FAQ Troubleshooting | Feedback-form intake | 479 | 0 | – | – |
| [#1968](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1968) | [HelpHub] Feedback on Custom Link block | Feedback-form intake | 479 | 1 | 446 | – |
| [#1972](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1972) | [HelpHub] Feedback on Users Add New screen | Feedback-form intake | 479 | 3 | 464 | – |
| [#1975](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1975) | [HelpHub] Feedback on Settings Media screen | Feedback-form intake | 479 | 4 | 448 | dup cluster, review |
| [#1976](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1976) | [HelpHub] Feedback on Settings Writing screen | Feedback-form intake | 479 | 3 | 448 | review |
| [#1977](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1977) | [HelpHub] Feedback on Customizer | Feedback-form intake | 479 | 1 | 415 | **closeable**, dup cluster |
| [#1978](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1978) | [HelpHub] Feedback on YouTube embed | Feedback-form intake | 479 | 5 | 23 | review |
| [#1979](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1979) | [HelpHub] Feedback on Use keyboard shortcuts (Block Editor) | Feedback-form intake | 479 | 0 | – | dup cluster |
| [#1980](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1980) | [HelpHub] Feedback on Page/Post Settings sidebar | Feedback-form intake | 479 | 0 | – | dup cluster |
| [#1986](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1986) | [HelpHub] Feedback on Page Jumps | Feedback-form intake | 479 | 1 | 477 | dup cluster |
| [#1988](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1988) | [HelpHub] Feedback on Blocks list | Feedback-form intake | 479 | 0 | – | – |
| [#1990](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1990) | [HelpHub] Feedback on Site Editor navigation | Feedback-form intake | 479 | 1 | 478 | dup cluster |
| [#1996](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1996) | [HelpHub] Block Editor page missing information on character shortcuts for wrapping sel… | Missing / new documentation | 465 | 1 | 431 | PR linked |
| [#2018](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2018) | [HelpHub][6.8]: Site Editor > Pages | Release-cycle UI/content update | 415 | 1 | 409 | – |
| [#2019](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2019) | [HelpHub] Bluesky embed article | Missing / new documentation | 415 | 2 | 183 | PR linked, review |
| [#2080](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2080) | [HelpHub] Theme blocks Documentation Page has many Broken Images issues | Screenshots, images & media | 324 | 1 | 148 | claimed |
| [#2094](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2094) | [HelpHub]: [6.9 HelpHub] Styles Overview | Release-cycle UI/content update | 322 | 1 | 320 | PR linked, in progress |
| [#2099](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2099) | [HelpHub] Overhaul Styles Overview article | Overhaul / rewrite tracking | 320 | 0 | – | in progress |
| [#2115](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2115) | [HelpHub] Overhaul Blocks List Page | Overhaul / rewrite tracking | 311 | 7 | 52 | **closeable** |
| [#2118](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2118) | [HelpHub][screenshots]: Broken images in Comment Date block | Screenshots, images & media | 311 | 5 | 190 | claimed, review |
| [#2124](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2124) | [HelpHub] Terms List block | Missing / new documentation | 308 | 3 | 225 | PR linked |
| [#2126](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2126) | [HelpHub] Overhauling blocks articles - READ FIRST | Overhaul / rewrite tracking | 307 | 1 | 307 | tracker |
| [#2150](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2150) | [HelpHub] Github links added at the bottom are not working | Broken / outdated links | 283 | 2 | 11 | – |
| [#2163](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2163) | [HelpHub] Overhauling articles in the Technical guides category - READ FIRST | Overhaul / rewrite tracking | 262 | 0 | – | tracker |
| [#2164](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2164) | [HelpHub] Overhaul Hosting WordPress article | Overhaul / rewrite tracking | 262 | 0 | – | – |
| [#2165](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2165) | [HelpHub] Overhauling articles in the Support guides category - READ FIRST | Overhaul / rewrite tracking | 262 | 0 | – | tracker |
| [#2166](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2166) | [HelpHub] Overhaul WordPress Block Editor article | Overhaul / rewrite tracking | 262 | 0 | – | – |
| [#2168](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2168) | [HelpHub] Overhaul Settings Discussion screen article | Overhaul / rewrite tracking | 262 | 0 | – | – |
| [#2169](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2169) | [HelpHub] Overhaul Settings General screen article | Overhaul / rewrite tracking | 262 | 2 | 105 | – |
| [#2171](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2171) | [HelpHub] Overhaul Reset your password article | Overhaul / rewrite tracking | 261 | 0 | – | dup cluster |
| [#2180](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2180) | [HelpHub] Overhaul Comment moderation article | Overhaul / rewrite tracking | 255 | 0 | – | – |
| [#2181](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2181) | [HelpHub] Overhauling articles in the WordPress overview category - READ FIRST | Overhaul / rewrite tracking | 255 | 0 | – | tracker |
| [#2182](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2182) | [HelpHub] Overhaul WordPress Glossary article | Overhaul / rewrite tracking | 255 | 0 | – | – |
| [#2183](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2183) | [HelpHub] Overhaul FAQs Troubleshooting article | Overhaul / rewrite tracking | 255 | 2 | 112 | – |
| [#2184](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2184) | [HelpHub] Overhaul Get started with WP article | Overhaul / rewrite tracking | 255 | 3 | 219 | claimed, in progress |
| [#2185](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2185) | [HelpHub] Overhaul First steps with WordPress (Block Editor) article | Overhaul / rewrite tracking | 255 | 1 | 142 | – |
| [#2191](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2191) | [HelpHub] Typography heading video and description misplaced in Styles Overview page | Screenshots, images & media | 241 | 2 | 156 | – |
| [#2200](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2200) | [HelpHub] Documentation for real-time collaboration | Missing / new documentation | 209 | 5 | 148 | in progress |
| [#2206](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2206) | [HelpHub] What’s the difference between WordPress.org and WordPress.com? | Content refresh / outdated text | 204 | 6 | 191 | claimed, in progress |
| [#2222](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2222) | [HelpHub] Overhaul Group block | Overhaul / rewrite tracking | 185 | 3 | 3 | – |
| [#2231](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2231) | [HelpHub] Overhaul Shortcode block | Overhaul / rewrite tracking | 185 | 0 | – | – |
| [#2234](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2234) | [HelpHub] Overhaul Custom link block | Overhaul / rewrite tracking | 185 | 0 | – | – |
| [#2235](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2235) | [HelpHub] Overhaul Home link block | Overhaul / rewrite tracking | 185 | 1 | 142 | – |
| [#2236](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2236) | [HelpHub] Overhaul Submenu block | Overhaul / rewrite tracking | 185 | 0 | – | – |
| [#2237](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2237) | [HelpHub] Overhaul Site logo block | Overhaul / rewrite tracking | 185 | 0 | – | – |
| [#2245](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2245) | [HelpHub] Multiple typos on WordPress Block Editor overview page | Typos, grammar & formatting | 180 | 3 | 164 | claimed |
| [#2265](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2265) | [HelpHub] Migrate article about Twenty Thirteen from Codex | Codex migration / redirects | 174 | 0 | – | – |
| [#2266](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2266) | [HelpHub] Migrate article about Twenty Fourteen from Codex | Codex migration / redirects | 174 | 1 | 174 | – |
| [#2284](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2284) | [HelpHub] Missing “Collapse Menu” functionality in “First Steps with WordPress” navigat… | Content refresh / outdated text | 149 | 1 | 148 | – |
| [#2285](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2285) | [HelpHub] Overhaul Site Title block | Overhaul / rewrite tracking | 149 | 0 | – | – |
| [#2286](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2286) | [HelpHub] Overhaul Site Tagline block | Overhaul / rewrite tracking | 149 | 0 | – | – |
| [#2305](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2305) | [HelpHub] Overhaul Post Template block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2306](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2306) | [HelpHub] Overhaul Content block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2307](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2307) | [HelpHub] Overhaul Pagination block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2308](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2308) | [HelpHub] Overhaul Excerpt block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2309](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2309) | [HelpHub] Overhaul Featured Image block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2313](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2313) | [HelpHub] Overhaul Tags block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2314](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2314) | [HelpHub] Overhaul Read More block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2315](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2315) | [HelpHub] Overhaul Comments Title block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2316](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2316) | [HelpHub] Overhaul Comment Template block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2317](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2317) | [HelpHub] Overhaul Comment Author Name block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2318](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2318) | [HelpHub] Overhaul Comment Date block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2319](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2319) | [HelpHub] Overhaul Comment Content block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2320](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2320) | [HelpHub] Overhaul Comment Reply Link block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2321](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2321) | [HelpHub] Overhaul Comment Edit Link block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2322](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2322) | [HelpHub] Overhaul Comments Pagination block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2323](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2323) | [HelpHub] Overhaul Comments Form block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2324](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2324) | [HelpHub] Overhaul Term Description block | Overhaul / rewrite tracking | 143 | 5 | 114 | claimed |
| [#2325](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2325) | [HelpHub] Overhaul Search Results Title block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2326](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2326) | [HelpHub] Overhaul Author Biography block | Overhaul / rewrite tracking | 143 | 0 | – | – |
| [#2329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2329) | [HelpHub] Overhaul Embeds article | Overhaul / rewrite tracking | 142 | 7 | 77 | claimed |
| [#2330](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2330) | [HelpHub] Overhaul YouTube embed | Overhaul / rewrite tracking | 142 | 2 | 104 | claimed |
| [#2331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2331) | [HelpHub] Overhaul WordPress emebd | Overhaul / rewrite tracking | 142 | 0 | – | – |
| [#2333](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2333) | [HelpHub] Overhaul VideoPress embed | Overhaul / rewrite tracking | 142 | 0 | – | – |
| [#2334](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2334) | [HelpHub] Overhaul Amazon Kindle embed | Overhaul / rewrite tracking | 142 | 0 | – | – |
| [#2335](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2335) | [HelpHub] Overhaul Pinterest embed | Overhaul / rewrite tracking | 142 | 0 | – | – |
| [#2338](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2338) | [HelpHub] Remove outdated release changelog on the Embeds page | Content refresh / outdated text | 138 | 6 | 107 | claimed |
| [#2339](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2339) | [HelpHub] Collapsible/Accordion Media | Missing / new documentation | 134 | 0 | – | – |
| [#2373](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2373) | [HelpHub] "Troubleshoot Login Issues" article has incorrect and outdated information | Content refresh / outdated text | 105 | 0 | – | – |
| [#2374](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2374) | [HelpHub] "Password Best Practices" article has typo and missing modern security advice | Typos, grammar & formatting | 105 | 2 | 6 | review |
| [#2378](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2378) | [HelpHub] Overhauling articles in the Customization subcategory - READ FIRST | Overhaul / rewrite tracking | 102 | 0 | – | tracker |
| [#2379](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2379) | [HelpHub] Overhaul Moving Blocks | Overhaul / rewrite tracking | 102 | 0 | – | – |
| [#2380](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2380) | [HelpHub] Overhaul "Updating WordPress" article | Overhaul / rewrite tracking | 102 | 0 | – | – |
| [#2464](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2464) | [HelpHub][7.1] Update documentation for media upload improvements | Release-cycle UI/content update | 66 | 1 | 44 | PR linked |
| [#2480](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2480) | [HelpHub][Ex][7.X] Update Real-time Collaboration documentation | Release-cycle UI/content update | 66 | 0 | – | PR linked |
| [#2483](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2483) | [HelpHub][Ex][7.1] Document the experimental custom taxonomy management UI | Release-cycle UI/content update | 66 | 0 | – | PR linked |
| [#2484](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2484) | [HelpHub][Ex][7.1] Document the experimental Settings > Connectors screen | Release-cycle UI/content update | 66 | 0 | – | PR linked |
| [#2485](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2485) | [HelpHub][Ex][7.X] Experimental: Guidelines UI | Release-cycle UI/content update | 66 | 0 | – | PR linked |
| [#2486](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2486) | [HelpHub][Ex][7.X] Experimental: hidden fields in the Forms block | Release-cycle UI/content update | 66 | 0 | – | PR linked |
| [#2497](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2497) | [HelpHub][7.1]: Button block update to Width control | Release-cycle UI/content update | 56 | 3 | 50 | PR linked, review |
| [#2514](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2514) | [HelpHub] Clarify theme ZIP file structure for updates | Inaccurate or unclear technical content | 17 | 2 | 7 | claimed |
| [#2515](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2515) | [HelpHub]:[Ex][7.X] Experimental: Add revisions panel to templates, template parts and… | Release-cycle UI/content update | 16 | 0 | – | PR linked |

### DevHub: Theme Handbook (76)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#101](https://github.com/WordPress/Documentation-Issue-Tracker/issues/101) | [Theme handbook] Links on the reference page point to codex | Codex migration / redirects | 1743 | 2 | 1332 | – |
| [#102](https://github.com/WordPress/Documentation-Issue-Tracker/issues/102) | [Theme Handbook] details/summary usage | Missing / new documentation | 1741 | 1 | 1332 | – |
| [#105](https://github.com/WordPress/Documentation-Issue-Tracker/issues/105) | [Themes] Add How to create a child theme in the Block theme handbook | Missing / new documentation | 1738 | 6 | 1729 | – |
| [#107](https://github.com/WordPress/Documentation-Issue-Tracker/issues/107) | [Themes] Add in docs somewhere about the sliding scale of site editing | Missing / new documentation | 1737 | 2 | 1332 | review |
| [#108](https://github.com/WordPress/Documentation-Issue-Tracker/issues/108) | [Themes] New Pages for the Block themes | Missing / new documentation | 1737 | 10 | 1332 | – |
| [#286](https://github.com/WordPress/Documentation-Issue-Tracker/issues/286) | [Theme Handbook] hierarchy is shown as a bulleted list | Typos, grammar & formatting | 1646 | 2 | 1257 | – |
| [#380](https://github.com/WordPress/Documentation-Issue-Tracker/issues/380) | [Themes] Moving Codex Theme Development to Theme Handbook | Codex migration / redirects | 1569 | 3 | 1334 | claimed |
| [#381](https://github.com/WordPress/Documentation-Issue-Tracker/issues/381) | [Themes] Moving Creating an Archive Index and Creating a Search Pages to Theme Handbook | Other / needs triage | 1569 | 5 | 1331 | – |
| [#382](https://github.com/WordPress/Documentation-Issue-Tracker/issues/382) | [Themes] Theme Handbook Issue Reporting | Missing / new documentation | 1569 | 4 | 1439 | – |
| [#436](https://github.com/WordPress/Documentation-Issue-Tracker/issues/436) | [Themes] Add mention of being able to enable appearance tools via theme_support to hand… | Missing / new documentation | 1501 | 8 | 1185 | claimed, PR linked |
| [#465](https://github.com/WordPress/Documentation-Issue-Tracker/issues/465) | [Theme handbook] Update "setting up a development environment" | Content refresh / outdated text | 1481 | 0 | – | – |
| [#466](https://github.com/WordPress/Documentation-Issue-Tracker/issues/466) | [Theme handbook] update header.php example | Code example errors | 1481 | 0 | – | – |
| [#467](https://github.com/WordPress/Documentation-Issue-Tracker/issues/467) | [Theme handbook] Update "Validating your theme" page | Content refresh / outdated text | 1481 | 1 | 1481 | – |
| [#481](https://github.com/WordPress/Documentation-Issue-Tracker/issues/481) | [Theme handbook] Document the Update URI header item | Missing / new documentation | 1478 | 1 | 1477 | dup cluster |
| [#524](https://github.com/WordPress/Documentation-Issue-Tracker/issues/524) | [Themes] Update theme tags description | Content refresh / outdated text | 1465 | 1 | 1332 | – |
| [#612](https://github.com/WordPress/Documentation-Issue-Tracker/issues/612) | [DevHub] Add advanced locking sub-page to theme handbook | Missing / new documentation | 1436 | 1 | 1436 | PR linked |
| [#615](https://github.com/WordPress/Documentation-Issue-Tracker/issues/615) | [Theme handbook] Replace classic block and shortcodes that contain code | Content refresh / outdated text | 1436 | 6 | 1332 | – |
| [#661](https://github.com/WordPress/Documentation-Issue-Tracker/issues/661) | [DevHub - Themes] Document what's available in theme.json vs in the Styles interface | Missing / new documentation | 1343 | 1 | 1343 | – |
| [#662](https://github.com/WordPress/Documentation-Issue-Tracker/issues/662) | [Theme developer handbook] Add theme.json information to the child theme page | Missing / new documentation | 1342 | 2 | 1337 | – |
| [#663](https://github.com/WordPress/Documentation-Issue-Tracker/issues/663) | [Theme Developer Handbook] Remove block theme content from the classic theme section | Content refresh / outdated text | 1341 | 3 | 960 | – |
| [#665](https://github.com/WordPress/Documentation-Issue-Tracker/issues/665) | [Themes handbook] Document recommendations for naming conventions for slugs in theme.js… | Missing / new documentation | 1333 | 1 | 1333 | – |
| [#813](https://github.com/WordPress/Documentation-Issue-Tracker/issues/813) | [Theme developer handbook] Improve documentation about template part areas. | Inaccurate or unclear technical content | 1268 | 1 | 1268 | – |
| [#816](https://github.com/WordPress/Documentation-Issue-Tracker/issues/816) | [Themes] Theme Handbook Overhaul (Tracking Ticket) | Overhaul / rewrite tracking | 1263 | 16 | 261 | claimed |
| [#833](https://github.com/WordPress/Documentation-Issue-Tracker/issues/833) | [Themes] Child Themes, correct reference to parent key | Inaccurate or unclear technical content | 1242 | 1 | 1242 | – |
| [#980](https://github.com/WordPress/Documentation-Issue-Tracker/issues/980) | [DevHub] Fix 'global page template' position information in the 'Theme Developer Handbo… | Inaccurate or unclear technical content | 1182 | 0 | – | – |
| [#1029](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1029) | [Themes] Convert the Email address to an email format rather than as a text | Typos, grammar & formatting | 1165 | 0 | – | – |
| [#1131](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1131) | [Theme Handbook Overhaul] Publish Chapter 3: Global Settings and Styles | Overhaul / rewrite tracking | 1113 | 3 | 1081 | **closeable** |
| [#1195](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1195) | [Theme Handbook Overhaul] Publish Chapter 4: Templates | Overhaul / rewrite tracking | 1095 | 2 | 1085 | – |
| [#1256](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1256) | [Theme Handbook Overhaul] Publish Chapter 5: Features | Overhaul / rewrite tracking | 1085 | 4 | 1074 | **closeable** |
| [#1259](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1259) | [Theme Handbook Overhaul] Publish Chapter 1: Getting Started and Chapter 2: Core Concep… | Overhaul / rewrite tracking | 1085 | 9 | 1059 | **closeable** |
| [#1357](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1357) | [Theme Handbook Overhaul] Classic Themes > Your First Theme | Overhaul / rewrite tracking | 1071 | 4 | 852 | claimed |
| [#1358](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1358) | [Theme Handbook Overhaul] Classic Themes > Basics > Template Tags | Overhaul / rewrite tracking | 1071 | 0 | – | – |
| [#1414](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1414) | [Theme Handbook] WordPress 6.4 - 6.6 updates | Release-cycle UI/content update | 1000 | 5 | 979 | PR linked |
| [#1416](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1416) | [Theme Handbook Overhaul] Publish Chapter: Advanced Topics | Overhaul / rewrite tracking | 1000 | 2 | 983 | – |
| [#1418](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1418) | [Theme Handbook Overhaul] Guides > JavaScript Best Practices | Overhaul / rewrite tracking | 1000 | 5 | 938 | review |
| [#1419](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1419) | [Theme Handbook Overhaul] Guides > UI Best Practices | Overhaul / rewrite tracking | 1000 | 5 | 952 | – |
| [#1469](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1469) | [Themes] Main Stylesheet doc page is missing Update URI | Inaccurate or unclear technical content | 966 | 0 | – | dup cluster |
| [#1481](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1481) | [Theme Handbook Overhaul] Patterns > Usage in Templates | Overhaul / rewrite tracking | 954 | 0 | – | – |
| [#1524](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1524) | [themes] Add Create Block Theme to Tools page in Theme Handbook | Missing / new documentation | 931 | 0 | – | in progress |
| [#1529](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1529) | [DevHub] Include warning against the use of is_admin in the Customizer section of the T… | Missing / new documentation | 927 | 0 | – | – |
| [#1634](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1634) | [Theme handbook] Remove Resources and Recommendations Pages in the Accessibility Ready … | Content refresh / outdated text | 826 | 11 | 825 | claimed |
| [#1635](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1635) | [Theme Handbook] Include alternative way to reference presets in theme.json | Screenshots, images & media | 826 | 1 | 813 | – |
| [#1657](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1657) | [Theme handbook] Path and Link Broken in Theme Security Handbook | Broken / outdated links | 809 | 0 | – | – |
| [#1658](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1658) | [6.6 Theme Handbook] Documentation outdated for lightbox | Release-cycle UI/content update | 809 | 2 | 387 | – |
| [#1660](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1660) | [6.6 Theme Handbook] Documentation outdated for theme.json > settings > dimensions | Release-cycle UI/content update | 808 | 1 | 452 | – |
| [#1676](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1676) | [DevHub] [themes] Inconsistent formatting in theme.json settings list | Typos, grammar & formatting | 798 | 0 | – | – |
| [#1677](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1677) | [Themes] Document the auto addition of skip links on Block Themes | Broken / outdated links | 793 | 3 | 793 | claimed |
| [#1678](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1678) | [Theme Handbook Overhaul] Outline Chapter: WordPress.org Theme Directory | Overhaul / rewrite tracking | 793 | 0 | – | – |
| [#1682](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1682) | [DevHub] [theme] Update fluid typography section in theme handbook to include min/max v… | Typos, grammar & formatting | 771 | 3 | 757 | review |
| [#1684](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1684) | [Theme Handbook Overhaul] Classic Themes > Basics > The Loop | Overhaul / rewrite tracking | 763 | 0 | – | – |
| [#1685](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1685) | [Theme Handbook Overhaul] Classic Themes > Basics > Templates | Overhaul / rewrite tracking | 763 | 0 | – | – |
| [#1692](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1692) | [Theme Handbook Overhaul] Accessibility Chapter Outline | Overhaul / rewrite tracking | 751 | 3 | 704 | claimed, in progress |
| [#1823](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1823) | [Theme Handbook Overhaul] Chapter: Classic Themes | Overhaul / rewrite tracking | 633 | 0 | – | – |
| [#1824](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1824) | [Theme Handbook Overhaul] Classic Themes > Basics > Conditional Tags | Overhaul / rewrite tracking | 633 | 0 | – | – |
| [#1826](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1826) | [DevHub] Add `Update URI` theme header info to Main Stylesheet doc | Missing / new documentation | 631 | 0 | – | dup cluster |
| [#1839](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1839) | [DevHub] Introduction to theme.json - Theme Handbook \| Developer.WordPress.org - API ve… | Inaccurate or unclear technical content | 611 | 0 | – | review |
| [#1913](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1913) | [DevHub]Typography settings miss "defaultFontSizes" option | Typos, grammar & formatting | 524 | 2 | 498 | review |
| [#2003](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2003) | Comma in JSON example code missing | Code example errors | 438 | 0 | – | – |
| [#2009](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2009) | Missing documentation for palette and typography variations via JSON files in /styles | Missing / new documentation | 425 | 1 | 401 | – |
| [#2010](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2010) | Missing and misleading documentation for JSON-based block style variations | Missing / new documentation | 425 | 1 | 416 | – |
| [#2016](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2016) | [Theme handbook] Just a confirmation about the “Header fields” section in `theme/core-c… | Inaccurate or unclear technical content | 415 | 1 | 407 | – |
| [#2024](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2024) | Just a confirmation about the “Inline JavaScript” section in `theme/core-concepts/inclu… | Inaccurate or unclear technical content | 415 | 0 | – | – |
| [#2025](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2025) | Just a confirmation about the “Unregistering block styles with PHP” section in `theme/f… | Inaccurate or unclear technical content | 415 | 0 | – | – |
| [#2026](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2026) | Just a confirmation about the “Using the WordPress scripts package” section in `theme/a… | Inaccurate or unclear technical content | 415 | 0 | – | – |
| [#2027](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2027) | Just a confirmation about the “Plugin API Hooks” section in `theme/advanced-topics/plug… | Inaccurate or unclear technical content | 414 | 1 | 406 | – |
| [#2032](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2032) | [Themes Handbook] Link to reference P2 Theme doesn't exist | Broken / outdated links | 407 | 1 | 387 | – |
| [#2061](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2061) | Theme handbook > Conditionally registering patterns in the /patterns folder > Missing n… | Code example errors | 351 | 4 | 346 | – |
| [#2062](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2062) | Theme handbook > Unregistering a pattern category > Wrong function in the PHP example | Code example errors | 351 | 1 | 346 | – |
| [#2097](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2097) | [Theme Handbook] Core Concepts/Including Assets | Inaccurate or unclear technical content | 322 | 0 | – | – |
| [#2113](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2113) | Update Theme Handbook: Add 5th border setting `radiusSizes` | Content refresh / outdated text | 314 | 1 | 314 | – |
| [#2148](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2148) | [Themes] Update theme support documentation for HTML5 script | Content refresh / outdated text | 290 | 8 | 136 | mostly done, in progress |
| [#2170](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2170) | [DevHub-themes] Accessibility Section for Theme Handbook | Missing / new documentation | 261 | 2 | 261 | in progress |
| [#2218](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2218) | [Theme Developer Handbook] Missing colon (:) in if statement in The Loop Document | Code example errors | 192 | 1 | 162 | claimed |
| [#2263](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2263) | [Themes] The problems about `wp_enqueue_(style\|script)` should not be prefixed if they … | Inaccurate or unclear technical content | 176 | 0 | – | – |
| [#2299](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2299) | [DevHub] Permitted file extensions are incomplete in `WP_Theme::get_screenshot()` descr… | Screenshots, images & media | 148 | 1 | 133 | – |
| [#2457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2457) | [Theme Handbook] Submitting Your Theme to WordPress.org - Missing information on name c… | Inaccurate or unclear technical content | 71 | 2 | 36 | review |

### DevHub: Code reference & other (57)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#29](https://github.com/WordPress/Documentation-Issue-Tracker/issues/29) | [DevHub] Zero and one argument for add_filter being "the same" | Inaccurate or unclear technical content | 1869 | 1 | 1868 | – |
| [#375](https://github.com/WordPress/Documentation-Issue-Tracker/issues/375) | [DevHub] Fixing escaping and internationalization mistakes in user contributed notes | Code example errors | 1577 | 1 | 1577 | – |
| [#406](https://github.com/WordPress/Documentation-Issue-Tracker/issues/406) | [DevHub] Page for register_post_type menu_position needs an update | Content refresh / outdated text | 1524 | 0 | – | – |
| [#730](https://github.com/WordPress/Documentation-Issue-Tracker/issues/730) | [DevHub] WordPress – testable, interactive documentation proposal | Other / needs triage | 1295 | 3 | 1252 | in progress |
| [#848](https://github.com/WordPress/Documentation-Issue-Tracker/issues/848) | [DevHub] Confusing Documentation for register_post_meta | Missing / new documentation | 1231 | 1 | 1231 | – |
| [#923](https://github.com/WordPress/Documentation-Issue-Tracker/issues/923) | [DevHub] Add system requirements for using wp_mail() | Missing / new documentation | 1197 | 0 | – | in progress |
| [#939](https://github.com/WordPress/Documentation-Issue-Tracker/issues/939) | [DevHub] Update the custom headers documenation. | Inaccurate or unclear technical content | 1192 | 0 | – | – |
| [#997](https://github.com/WordPress/Documentation-Issue-Tracker/issues/997) | [DevHub] Add that the color variable "text" is an invalid color pallette slug | Missing / new documentation | 1172 | 0 | – | – |
| [#1193](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1193) | [DevHub] `wp_safe_remote_get(` function family: redirection unexpectedly possible, secu… | Inaccurate or unclear technical content | 1096 | 0 | – | – |
| [#1255](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1255) | [Codex] WordPress.org_API Errata | Codex migration / redirects | 1086 | 0 | – | – |
| [#1379](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1379) | [DevHub] Multisite switch_to_blog blocks get_template_part | Inaccurate or unclear technical content | 1058 | 0 | – | – |
| [#1380](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1380) | [DevHub] Misleading description in Readme Header Information | Inaccurate or unclear technical content | 1057 | 0 | – | – |
| [#1384](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1384) | [Devhub] Parameter `search_columns` for `WP_Query` is not documented | Inaccurate or unclear technical content | 1046 | 1 | 1045 | – |
| [#1403](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1403) | [DevHub] "More Information" of `post_type_link` filter is incorrect | Broken / outdated links | 1032 | 2 | 3 | claimed |
| [#1407](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1407) | [DevHub] Missing Function Links In Nonces Documentation | Broken / outdated links | 1011 | 1 | 1007 | claimed |
| [#1451](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1451) | [DevHub] Broken link to `WpOrgRequestsUtilityCaseInsensitiveDictionary` documentation. | Broken / outdated links | 989 | 3 | 884 | – |
| [#1454](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1454) | [DevHub] Broken "View all references" links for `WP_Styles` class and methods | Broken / outdated links | 983 | 0 | – | – |
| [#1457](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1457) | [DevHub] Broken link to `WpOrgRequestsHooks` documentation. | Broken / outdated links | 982 | 0 | – | – |
| [#1467](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1467) | [DevHub] Incorrect orderby param in WP_User_Query | Inaccurate or unclear technical content | 966 | 0 | – | dup cluster |
| [#1470](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1470) | [DevHub] Incorrect `orderby` parameters for `WP_User_Query` | Inaccurate or unclear technical content | 965 | 3 | 961 | claimed, dup cluster, review |
| [#1515](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1515) | [Codex] Redirect Codex htaccess for subdirectories page | Codex migration / redirects | 935 | 1 | 935 | – |
| [#1521](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1521) | [DevHub] Better print stylesheets for PHP Documentation Standards page | Typos, grammar & formatting | 932 | 2 | 929 | – |
| [#1527](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1527) | [DevHub] Slightly misleading doc for should_load_separate_core_block_assets | Inaccurate or unclear technical content | 928 | 0 | – | – |
| [#1541](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1541) | [DevHub] WXR file standard | Missing / new documentation | 902 | 0 | – | – |
| [#1561](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1561) | [DevHub] Content formatting is erratic when displaying code on some pages | Typos, grammar & formatting | 881 | 2 | 879 | – |
| [#1572](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1572) | [DevHub] Wrong variable name in filter documentation for `comments_clauses` hook | Code example errors | 870 | 0 | – | – |
| [#1579](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1579) | [DevHub] Missing semicolon in register_block_bindings_source code example | Code example errors | 860 | 2 | 613 | review |
| [#1671](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1671) | [DevHub] get_var() returns NULL when empty string has been found | Inaccurate or unclear technical content | 805 | 1 | 188 | – |
| [#1693](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1693) | [DevHub] Link placeholder on `wp_insert_post()` reference page | Broken / outdated links | 749 | 5 | 740 | claimed, in progress |
| [#1793](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1793) | [DevHub] Code Reference Links Require Trailing Slash for Link to not be Broken | Broken / outdated links | 714 | 0 | – | – |
| [#1836](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1836) | [DevHub] Issue with Registering a Pattern Category – Slug Conversion Creates Duplicate … | Inaccurate or unclear technical content | 616 | 0 | – | – |
| [#1849](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1849) | [DevHub] Wrong default mentioned for `wp_list_users` (`exclude_admin`) | Inaccurate or unclear technical content | 598 | 4 | 597 | claimed, review |
| [#1860](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1860) | Add the Meaning of PHPUnit Output "W" to Docs | Missing / new documentation | 576 | 0 | – | – |
| [#1862](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1862) | The Template Hierarchy graph in your official documentation is not updated | Content refresh / outdated text | 573 | 1 | 400 | – |
| [#1863](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1863) | Specify difference between Pattern and Template/Parts files in docs (i.e. PHP vs HTML) | Missing / new documentation | 572 | 2 | 467 | – |
| [#1866](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1866) | register_taxonomy - "null" in documentation for arguments > parent_item_colon | Missing / new documentation | 567 | 0 | – | – |
| [#2000](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2000) | @wordpress/data readme broken links | Broken / outdated links | 443 | 0 | – | – |
| [#2020](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2020) | Code example in JavaScript misleadingly labelled as PHP | Code example errors | 415 | 0 | – | – |
| [#2076](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2076) | [DevHub] Documentation error under "Applying Styles: Styling elements nested in blocks"… | Code example errors | 328 | 0 | – | – |
| [#2119](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2119) | [DevHub] userefeffect not found in '@wordpress/element' ( should be '@wordpress/compose… | Inaccurate or unclear technical content | 310 | 1 | 303 | – |
| [#2210](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2210) | [DevHub] WP_Query::get_posts use case clarification | Inaccurate or unclear technical content | 203 | 2 | 3 | claimed |
| [#2216](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2216) | [DevHub] Document non-uniqueness of WordPress GUID | Missing / new documentation | 202 | 2 | 187 | claimed |
| [#2268](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2268) | [DevHub] Add missing `@since 5.4.0` rename description to `wp_get_user_request()` DocBl… | Missing / new documentation | 171 | 1 | 162 | – |
| [#2289](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2289) | [DevHub] Type of `add_shortcode()` callback's parameter is incorrect | Inaccurate or unclear technical content | 148 | 3 | 76 | review |
| [#2291](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2291) | [DevHub] Inaccurate description of logic for `is_single()` | Inaccurate or unclear technical content | 148 | 3 | 76 | claimed, review |
| [#2292](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2292) | [DevHub] Acknowledge difference between `get_user_meta()` and `get_usermeta()` | Missing / new documentation | 148 | 3 | 76 | review |
| [#2293](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2293) | [DevHub] Add missing return scenario to `get_user_meta()` | Missing / new documentation | 148 | 4 | 34 | PR linked, review |
| [#2294](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2294) | [DevHub] Correctly separate list item for `get_post_meta()` | Inaccurate or unclear technical content | 148 | 3 | 76 | review |
| [#2295](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2295) | [DevHub] Remove incorrect requirement from `add_submenu_page()` | Content refresh / outdated text | 148 | 0 | – | – |
| [#2296](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2296) | [DevHub] Correct documented default of `sort_column` parameter for `wp_list_pages()` | Inaccurate or unclear technical content | 148 | 0 | – | – |
| [#2298](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2298) | [DevHub] Update `tax_input` of `wp_insert_post` to include term IDs for non-hierarchica… | Inaccurate or unclear technical content | 148 | 0 | – | – |
| [#2344](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2344) | [DevHub] add_image_size links to an external page that has poor information | Broken / outdated links | 120 | 4 | 76 | review |
| [#2354](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2354) | [DevHub] Outdated Codex link in `set_transient()` | Codex migration / redirects | 116 | 4 | 107 | review |
| [#2357](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2357) | [DevHub] Update wp_verify_nonce example to unslash and sanitize input | Code example errors | 116 | 2 | 68 | – |
| [#2369](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2369) | [DevHub] [Codex] Outdated Codex links in the_content() documentation page. | Codex migration / redirects | 113 | 0 | – | – |
| [#2506](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2506) | [DevHub] Need to remove/update external resources links in the Shortcode Documentation … | Broken / outdated links | 39 | 3 | 34 | claimed |
| [#2512](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2512) | [DevHub] `wp_handle_upload_prefilter` reference to `upload_dir` is unclear | Inaccurate or unclear technical content | 27 | 2 | 14 | claimed |

### DevHub: APIs & block editor (27)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#53](https://github.com/WordPress/Documentation-Issue-Tracker/issues/53) | [DevHub] Conveying the importance of wp_doing_ajax during init and admin_init hooks | Inaccurate or unclear technical content | 1828 | 2 | 1821 | review |
| [#329](https://github.com/WordPress/Documentation-Issue-Tracker/issues/329) | [Codex] Migrate Templates from Codex to DevHub | Codex migration / redirects | 1621 | 1 | 1597 | – |
| [#330](https://github.com/WordPress/Documentation-Issue-Tracker/issues/330) | [Codex] Migrate Templates Tags from Codex to DevHub | Codex migration / redirects | 1621 | 0 | – | – |
| [#331](https://github.com/WordPress/Documentation-Issue-Tracker/issues/331) | [Codex] Migrate Stepping into Templates from Codex to DevHub | Codex migration / redirects | 1621 | 1 | 937 | – |
| [#333](https://github.com/WordPress/Documentation-Issue-Tracker/issues/333) | [Codex] Migrate The Loop in Action from Codex to DevHub | Codex migration / redirects | 1621 | 0 | – | – |
| [#370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/370) | [DevHub] Add Action and Filter Reference Documents to DevHub | Codex migration / redirects | 1585 | 6 | 937 | claimed, in progress |
| [#379](https://github.com/WordPress/Documentation-Issue-Tracker/issues/379) | [DevHub] Data module section needs practical code examples. | Missing / new documentation | 1570 | 5 | 1564 | review |
| [#507](https://github.com/WordPress/Documentation-Issue-Tracker/issues/507) | [DevHub] slightly wrong information in the example | Code example errors | 1470 | 0 | – | – |
| [#624](https://github.com/WordPress/Documentation-Issue-Tracker/issues/624) | [DevHub] Localisation and internationalisation on several places | Codex migration / redirects | 1428 | 0 | – | – |
| [#647](https://github.com/WordPress/Documentation-Issue-Tracker/issues/647) | [DevHub] Let developers know it's possible to filter the capability required by the Set… | Missing / new documentation | 1358 | 1 | 1358 | – |
| [#666](https://github.com/WordPress/Documentation-Issue-Tracker/issues/666) | [DevHub] API Handbook Custom Endpoint page need clarity | Inaccurate or unclear technical content | 1333 | 1 | 1333 | – |
| [#723](https://github.com/WordPress/Documentation-Issue-Tracker/issues/723) | [Codex] Migrate WordPress.ORG API from Codex to DevHub | Codex migration / redirects | 1307 | 4 | 1086 | – |
| [#1484](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1484) | [Common APIs handbook] Missing links on Action Reference article | Broken / outdated links | 947 | 5 | 937 | – |
| [#1563](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1563) | [DevHub] A code snippet is missing braces, which leads to an error in the console. | Code example errors | 877 | 1 | 849 | PR linked |
| [#1796](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1796) | [DevHub] Common APIs Handbook / Hooks / Action Reference: second 'admin_menu' should be… | Typos, grammar & formatting | 710 | 0 | – | – |
| [#1804](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1804) | [DevHub] Block Editor Handbook -> Reference Guides -> Block API Reference -> Supports -… | Inaccurate or unclear technical content | 686 | 0 | – | – |
| [#1825](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1825) | [DevHub] Doc Missing: REST API is only accessible until Permalinks configured. | Broken / outdated links | 633 | 2 | 633 | – |
| [#1899](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1899) | Issues with JSON/XML on the REST API Handbook | Code example errors | 547 | 0 | – | – |
| [#1901](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1901) | Add Reference to interactivity-api-block-833d15 in Interactivity API Docs | Missing / new documentation | 547 | 0 | – | – |
| [#1912](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1912) | Post API - Fields after / after_modified / before / before_modified require an DateTim… | Inaccurate or unclear technical content | 529 | 2 | 498 | – |
| [#2021](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2021) | REST API schema docs should be updated with new formats introduced in WP 5.9 | Content refresh / outdated text | 415 | 3 | 408 | PR linked, review |
| [#2038](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2038) | Document block.json viewScriptModule field | Missing / new documentation | 392 | 0 | – | – |
| [#2161](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2161) | [DevHub] Docs: Add detailed entity configuration reference to data-core documentation | Missing / new documentation | 267 | 3 | 266 | – |
| [#2162](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2162) | [DevHub] Missing Documentation for package-admin-ui | Missing / new documentation | 266 | 2 | 266 | – |
| [#2244](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2244) | [DevHub] HTML error on wp_register_ability | Code example errors | 182 | 0 | – | – |
| [#2288](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2288) | [DevHub] `$route` parameter of `register_rest_route()` is not identified as a regular e… | Inaccurate or unclear technical content | 148 | 0 | – | – |
| [#2343](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2343) | Streamline the Abilities API handbook page | Content refresh / outdated text | 121 | 1 | 121 | – |

### DevHub: Plugin Handbook (19)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#35](https://github.com/WordPress/Documentation-Issue-Tracker/issues/35) | [DevHub] Document using wpcs in Plugin Developer Handbook | Missing / new documentation | 1850 | 9 | 1783 | review |
| [#78](https://github.com/WordPress/Documentation-Issue-Tracker/issues/78) | [DevHub] Improve best practices in our Using Subversion guide | Content refresh / outdated text | 1774 | 3 | 143 | review |
| [#407](https://github.com/WordPress/Documentation-Issue-Tracker/issues/407) | [DevHub] Style issue in the table of contents in summary page in the doc - plugin handb… | Typos, grammar & formatting | 1524 | 0 | – | – |
| [#410](https://github.com/WordPress/Documentation-Issue-Tracker/issues/410) | [DevHub] Tidy up suggested object caching plugins | Content refresh / outdated text | 1521 | 2 | 1521 | PR linked |
| [#412](https://github.com/WordPress/Documentation-Issue-Tracker/issues/412) | [DevHub] Suggest to add new Boilerplate (maintained) to the List of Plugin Boilerplates | Missing / new documentation | 1521 | 0 | – | – |
| [#794](https://github.com/WordPress/Documentation-Issue-Tracker/issues/794) | [Plugin] Double `This` in `Plugin Readmes` documentation page | Typos, grammar & formatting | 1278 | 1 | 1278 | – |
| [#827](https://github.com/WordPress/Documentation-Issue-Tracker/issues/827) | [DevHub] Add an extra parameter 'show_in_rest' in custom taxonomy code snippet of devel… | Code example errors | 1252 | 2 | 1252 | claimed |
| [#977](https://github.com/WordPress/Documentation-Issue-Tracker/issues/977) | [DevHub] Documentation for Plugin Activation/Deactivation, especially Multisite | Missing / new documentation | 1189 | 0 | – | – |
| [#1001](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1001) | [Plugins] Update Plugin Handbook with a reason why text-domains must be identical to th… | Content refresh / outdated text | 1169 | 1 | 1164 | in progress |
| [#1494](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1494) | [DevHub] Update Examples of plugin boilerplate | Code example errors | 940 | 1 | 938 | – |
| [#1508](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1508) | [Plugins] No mention of KSES on Internationalization Security page | Inaccurate or unclear technical content | 936 | 2 | 932 | claimed |
| [#1533](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1533) | [DevHub-Plugins] Update Plugins Handbook: Plugin Headers - tags (in readme.txt) | Inaccurate or unclear technical content | 917 | 0 | – | – |
| [#1831](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1831) | [DevHub] Since Require at least and Require PHP are not mandatory to add readme header … | Inaccurate or unclear technical content | 629 | 2 | 610 | – |
| [#1895](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1895) | [Plugin handbook] Minor typos in the "How to Internationalize Your Plugin" section of t… | Typos, grammar & formatting | 553 | 0 | – | – |
| [#2067](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2067) | Add more syntax examples to Plugin Readme doc | Missing / new documentation | 343 | 0 | – | – |
| [#2370](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2370) | [DevHub] Outdated external link in Plugin Handbook - "Using Subversion" page | Broken / outdated links | 111 | 10 | 39 | claimed, **closeable**, review |
| [#2510](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2510) | Plugin Handbook contains outdated links | Broken / outdated links | 31 | 2 | 28 | review |
| [#2511](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2511) | [Plugin Handbook] Link update needed in plugin handbook | Broken / outdated links | 28 | 1 | 15 | claimed |
| [#2513](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2513) | [DevHub] Plugin Handbook: grammar and punctuation fixes on Header Requirements page | Typos, grammar & formatting | 20 | 1 | 20 | – |

### DevHub: Dev notes (7.1) (6)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#2423](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2423) | [7.1][Misc Dev Note] Responsive styles and block states | Release-cycle UI/content update | 87 | 0 | – | – |
| [#2424](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2424) | [7.1][Misc Dev Note] Block supports updates | Release-cycle UI/content update | 87 | 0 | – | – |
| [#2425](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2425) | [7.1][Misc Dev Note] DataViews and DataForm view configuration | Release-cycle UI/content update | 87 | 0 | – | – |
| [#2426](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2426) | [7.1][Misc Dev Note] HTML API parsing changes | Release-cycle UI/content update | 87 | 0 | – | – |
| [#2428](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2428) | [7.1][Misc Dev Note] New hooks and conditional APIs | Release-cycle UI/content update | 87 | 0 | – | – |
| [#2429](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2429) | [7.1][Misc Dev Note] Miscelaneous Dev Notes | Release-cycle UI/content update | 87 | 0 | – | – |

### Advanced Admin (20)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#14](https://github.com/WordPress/Documentation-Issue-Tracker/issues/14) | [Advanced Admin] HTTPS for WordPress using Caddy | Broken / outdated links | 1882 | 5 | 299 | claimed |
| [#19](https://github.com/WordPress/Documentation-Issue-Tracker/issues/19) | [Advanced Admin] New Page: How to move WordPress site from HTTP to HTTPS | Missing / new documentation | 1881 | 3 | 1864 | – |
| [#44](https://github.com/WordPress/Documentation-Issue-Tracker/issues/44) | [Advanced Admin] Multilingual WordPress support article - Update mentioned plugins | Content refresh / outdated text | 1844 | 0 | – | – |
| [#73](https://github.com/WordPress/Documentation-Issue-Tracker/issues/73) | [HelpHub-Advanced Admin] Remove technical details from End User Docs->Updating WordPress | Content refresh / outdated text | 1789 | 1 | 1774 | – |
| [#79](https://github.com/WordPress/Documentation-Issue-Tracker/issues/79) | [HelpHub-Advanced Admin] Remove tech details from 'Resetting Password' end-user doc | Content refresh / outdated text | 1773 | 1 | 1767 | – |
| [#438](https://github.com/WordPress/Documentation-Issue-Tracker/issues/438) | [Advanced Admin] Maintain a secure site | Content refresh / outdated text | 1498 | 0 | – | – |
| [#656](https://github.com/WordPress/Documentation-Issue-Tracker/issues/656) | [Advanced Admin] Document options which drive auto-update behavior. | Missing / new documentation | 1345 | 4 | 1344 | – |
| [#824](https://github.com/WordPress/Documentation-Issue-Tracker/issues/824) | [Advanced Admin] Feedback on Optimization | Feedback-form intake | 1253 | 3 | 1182 | dup cluster |
| [#911](https://github.com/WordPress/Documentation-Issue-Tracker/issues/911) | [Advanced Admin] Feedback on Multilingual WordPress | Feedback-form intake | 1200 | 0 | – | – |
| [#952](https://github.com/WordPress/Documentation-Issue-Tracker/issues/952) | [Advanced Admin] 404 link on https://wordpress.org/documentation/article/upgrading-word… | Broken / outdated links | 1191 | 0 | – | – |
| [#960](https://github.com/WordPress/Documentation-Issue-Tracker/issues/960) | [Advanced Admin] 404 on https://developer.wordpress.org/advanced-administration/debug/d… | Broken / outdated links | 1191 | 1 | 149 | PR linked, **closeable** |
| [#1055](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1055) | [Advanced Admin] Feedback on Importing Content | Feedback-form intake | 1157 | 0 | – | – |
| [#1385](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1385) | [Advanced Admin] Tracking Issue: More robust guides for importing/migration | Codex migration / redirects | 1046 | 1 | 1046 | – |
| [#1392](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1392) | [Advanced Admin] Feedback on Optimization | Feedback-form intake | 1042 | 0 | – | dup cluster |
| [#1696](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1696) | [Advanced Admin] [DevHub] Feedback on Block Patterns | Feedback-form intake | 742 | 3 | 277 | review |
| [#1707](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1707) | [HelpHub] Feedback on Reset your password | Feedback-form intake | 742 | 3 | 277 | dup cluster, review |
| [#1708](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1708) | [HelpHub] [Advanced Admin] Feedback on Customize date and time format | Feedback-form intake | 738 | 3 | 277 | review |
| [#1730](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1730) | [HelpHub] Feedback on Weblog client | Feedback-form intake | 737 | 3 | 277 | review |
| [#2022](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2022) | Formalize documentation for Script Module API | Missing / new documentation | 415 | 0 | – | – |
| [#2172](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2172) | [DevHub] Safely Rotating WordPress SALT Keys on Live Sites | Screenshots, images & media | 259 | 0 | – | – |

### Codex (legacy site) (3)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#1506](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1506) | [Codex] Codex pages still available after migration to DevHub | Codex migration / redirects | 937 | 3 | 934 | – |
| [#2052](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2052) | [Codex] global variable `$paged` isn't officially documented | Codex migration / redirects | 361 | 0 | – | – |
| [#2070](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2070) | [Codex] Language switcher redirect issue on Codex and subpages | Codex migration / redirects | 343 | 2 | 280 | – |

### Docs process & contributor (26)

| Issue | Title | Change type | Age (d) | Human | Last human (d) | Signals |
|---|---|---|---|---|---|---|
| [#264](https://github.com/WordPress/Documentation-Issue-Tracker/issues/264) | [Docs Handbook] Add guideline on how to work on GitHub issues | Process & contributor tasks | 1647 | 2 | 759 | review |
| [#383](https://github.com/WordPress/Documentation-Issue-Tracker/issues/383) | [Docs Handbook] Onboarding documentation in Handbook | Process & contributor tasks | 1563 | 1 | 1200 | in progress |
| [#474](https://github.com/WordPress/Documentation-Issue-Tracker/issues/474) | [HelpHub] Create a block pattern for Block Documentation | Process & contributor tasks | 1479 | 13 | 1445 | claimed, in progress |
| [#639](https://github.com/WordPress/Documentation-Issue-Tracker/issues/639) | [Docs Handbook] Update team badges page | Process & contributor tasks | 1386 | 1 | 1386 | – |
| [#640](https://github.com/WordPress/Documentation-Issue-Tracker/issues/640) | [Docs Handbook] Create GitHub comments templates | Process & contributor tasks | 1386 | 1 | 1386 | – |
| [#648](https://github.com/WordPress/Documentation-Issue-Tracker/issues/648) | [Docs Handbook] What is HelpHub | Process & contributor tasks | 1354 | 6 | 937 | review |
| [#649](https://github.com/WordPress/Documentation-Issue-Tracker/issues/649) | [Docs Handbook] Update HelpHub section | Process & contributor tasks | 1353 | 2 | 1206 | – |
| [#815](https://github.com/WordPress/Documentation-Issue-Tracker/issues/815) | [Docs Handbook] Onboarding video for Feedback curator role | Process & contributor tasks | 1266 | 1 | 1266 | – |
| [#830](https://github.com/WordPress/Documentation-Issue-Tracker/issues/830) | [Docs Handbook] Write description for onboarding sessions | Process & contributor tasks | 1246 | 3 | 953 | claimed, in progress |
| [#831](https://github.com/WordPress/Documentation-Issue-Tracker/issues/831) | [Docs Handbook] Add role (and docs) for writing Issues triage summary | Process & contributor tasks | 1246 | 1 | 1246 | – |
| [#998](https://github.com/WordPress/Documentation-Issue-Tracker/issues/998) | [Docs Handbook] Handbook with instructions to create Project Boards for End User Doc re… | Process & contributor tasks | 1171 | 0 | – | – |
| [#1070](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1070) | [Docs Handbook] Update "Editing Articles" Documentation Handbook User Doc | Process & contributor tasks | 1148 | 2 | 1148 | – |
| [#1243](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1243) | [Workflow user story] Successfully complete the task | Process & contributor tasks | 1087 | 1 | 1087 | – |
| [#1244](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1244) | [Workflow user story] Being able to do the whole task at once | Process & contributor tasks | 1087 | 1 | 1087 | – |
| [#1245](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1245) | [Workflow user story] Being confident in capability to complete the issue | Process & contributor tasks | 1087 | 1 | 1087 | – |
| [#1848](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1848) | [Docs Style Guide] Use straight apostrophes and quotation marks in handbook pages about… | Process & contributor tasks | 600 | 1 | 598 | – |
| [#1858](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1858) | Process Improvement: Finding similar, duplicate or related issues | Process & contributor tasks | 578 | 0 | – | – |
| [#1916](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1916) | [Core Handbook] Invalid slug for "Updating Bundled Theme Versions" page | Process & contributor tasks | 521 | 0 | – | – |
| [#1920](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1920) | Documentation Team Contributor Day | Process & contributor tasks | 507 | 1 | 485 | in progress |
| [#1922](https://github.com/WordPress/Documentation-Issue-Tracker/issues/1922) | General Task List for Contributor Days | Process & contributor tasks | 507 | 0 | – | – |
| [#2199](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2199) | [Docs Handbook] Overhaul handbook pages | Process & contributor tasks | 217 | 3 | 217 | – |
| [#2207](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2207) | [Docs Handbook] Reporting an Issue page recommends Slack over GitHub, causing confusion… | Process & contributor tasks | 204 | 1 | 177 | claimed |
| [#2217](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2217) | [Docs handbook] Understanding labels in this repo | Process & contributor tasks | 198 | 0 | – | – |
| [#2272](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2272) | [Docs handbook-GH] Docs issue tracker has conflicting badge instructions between README… | Process & contributor tasks | 165 | 1 | 162 | – |
| [#2280](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2280) | [Docs handbook] issue tracker page links to deleted workflow files | Process & contributor tasks | 164 | 0 | – | – |
| [#2345](https://github.com/WordPress/Documentation-Issue-Tracker/issues/2345) | [HelpHub] Tasks for WP Credits students | Process & contributor tasks | 120 | 0 | – | – |
