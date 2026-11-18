## `Text` Component (`__experimentalText` as Text)

- [wporg-developer: GitHub issue relating to Updated On](https://github.com/WordPress/wporg-developer/issues/456)
- [WP.org admin revision](https://developer.wordpress.org/wp-admin/revision.php?revision=182982)
- does not track whether the change was made via GitHub or the Block Editor (WP Admin)
- does not offer version control of what was changed (only a new revision of entire copy/pasted content)
- proves that docs changes are not trackable, reviewable, or able to be audited effectively

### Source and last editor

Page: https://developer.wordpress.org/block-editor/reference-guides/components/text/

The page is generated from [`packages/components/src/text/README.md`](https://github.com/WordPress/gutenberg/blob/trunk/packages/components/src/text/README.md) in `WordPress/gutenberg` (confirmed in `docs/manifest.json`). The last person to edit that source was **Lena Morita** ([@mirka](https://github.com/mirka)) on 2026-08-05.

| Date | Author | Commit |
|---|---|---|
| 2026-08-05 | Lena Morita (@mirka) | [`773a91506`](https://github.com/WordPress/gutenberg/commit/773a91506), "Use p elements for callout alerts in components docs" ([#81234](https://github.com/WordPress/gutenberg/pull/81234)) |
| 2026-07-01 | Marco Ciampini (@ciampo) | [`d69c10edd`](https://github.com/WordPress/gutenberg/commit/d69c10edd), "Components: migrate View away from Emotion" ([#79443](https://github.com/WordPress/gutenberg/pull/79443)) |
| 2025-01-06 | Viktor Szépe (@szepeviktor) | [`8ac0e7924`](https://github.com/WordPress/gutenberg/commit/8ac0e7924), "Code quality: Fix typos" ([#67304](https://github.com/WordPress/gutenberg/pull/67304)) |

- The latest edit was a bulk change across the components docs (callout alerts now use `<p>` elements), not a content edit to the Text page.
- The last content-focused edit is the View migration commit. The one before it is a typo fix from January 2025.
- The developer.wordpress.org page refreshes from the repo on its own schedule, so it may lag the latest commit.

_Data from read-only `gh api` queries on 2026-10-18. Compiled by Claude Code (Claude Sonnet 5.5, `claude-sonnet-5-5`) at Seth Miller's request._