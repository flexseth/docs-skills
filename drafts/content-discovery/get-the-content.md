# Importing WordPress Documentation as a Single Piece of Content

For this use case, I'd recommend **retrieving the WordPress documentation article's original content and importing it as a single WordPress post or page**, rather than copying the rendered webpage.

The important distinction is what you mean by *identical content*. If you want the new post to preserve the original article's text, headings, links, lists, code examples, images, and WordPress block structure, the best approach is to retrieve the source in a format that preserves those elements.

For the article [Customize Permalinks](https://wordpress.org/documentation/article/customize-permalinks/), investigate these options in order:

| Method | Advantages | Potential problems |
|---|---|---|
| WordPress REST API | Structured data; can retrieve the article's content and metadata | The public documentation site's API may expose rendered content rather than the original editor blocks |
| WordPress WXR export (XML) | Designed to move WordPress posts, pages, taxonomies, and metadata | You may not have access to an export containing just that one article |
| Retrieve the original Gutenberg block markup | Best chance of preserving the original block structure | Requires finding a source that exposes the unrendered `post_content` |
| Scrape the rendered HTML | Can preserve the visible article content and links | May introduce theme markup, omit editor metadata, or require HTML-to-block conversion |

**Recommendation:** start with the REST API, but verify whether it returns the original block markup. If it doesn't, look for a way to retrieve the article's original `post_content` or a single-post WXR export. If the API returns only rendered HTML, check whether the page is generated from a public markdown source (see section 3a). That is a better route to a faithful import than reconstructing the article from HTML.

> **Question:** This recommendation and the "Which method is right for you?" handbook bullet put the Gutenberg markdown first. Section 4 ("Handbook and documentation pages") makes the authenticated REST route primary. Which is the primary source? Suggested resolution: authenticated route once access is granted, section 3a markdown as the no-auth fallback.

## 1. Test the REST API first

WordPress's REST API supports retrieving post content and creating posts on another WordPress site. The key field is `content.raw`, which contains the stored post content, including Gutenberg block comments when the original content uses blocks. The API's `content.rendered` field contains rendered HTML instead. Access to `raw` generally requires authentication and permission to edit the source post.

Try opening the API discovery endpoint for the documentation site:

- [WordPress.org Documentation REST API index](https://wordpress.org/documentation/wp-json/)

If that endpoint works, look for the article's post type and its collection endpoint. You can then query for the slug `customize-permalinks`.

If the API doesn't expose the underlying post, you can still use the public page's HTML, but you won't necessarily get the original block markup.

### Result of testing the REST API on a handbook page

Tested against the Text component handbook page (`text`, post ID 134509, parent 134435) on developer.wordpress.org:

- `https://developer.wordpress.org/wp-json/wp/v2/blocks-handbook?slug=text` returns only `content.rendered` and `content.protected`. There is no `raw` key.
- `?context=edit` returns HTTP 401 `rest_forbidden_context`, on both the collection and `/blocks-handbook/134509`. `content.raw` is only returned in edit context, which requires an authenticated user who can edit the post.
- `/blocks-handbook/134509/revisions` returns HTTP 401 `rest_cannot_read`.
- The post meta does not expose a source field.

The rendered HTML is converted from markdown. It contains `<h2>`, `<pre><code class="language-jsx">`, and `callout callout-alert` paragraphs. Handbook pages are not stored as block markup, so there are no `<!-- wp:... -->` comments to recover. The `post_content` route in section 3 therefore won't help for handbook pages, and the source has to come from the markdown.

[`WP.org PHP API`](https://github.com/wp-api-libraries/wp-wporg-api)


## 2. Compare the actual content

For an exact import, verify these elements after retrieving the source:

- Gutenberg block comments, such as `<!-- wp:heading -->`.
- Text, heading levels, lists, tables, and inline formatting.
- Hyperlinks and their destinations.
- Image URLs, captions, and alternative text.
- Code examples, notes, and other embedded elements.
- The article's changelog and other content at the bottom of the page.

One important caveat: **identical content isn't necessarily an identical page.** The source site's theme, styles, and templates won't automatically transfer when you import the content into a different WordPress installation.

## 3. Preferred fallback: retrieve the original database content

If you have access to a WordPress export containing the article, WXR XML is a practical way to import it as a single post or page. If you can obtain the original database record, the `wp_posts.post_content` field is an even more direct source for preserving Gutenberg markup.

If neither is available, use the rendered HTML and carefully convert it to WordPress blocks, then compare the imported result against the original.

## 3a. Source markdown in the code repository

Some handbook pages are generated from markdown files in a public repository. For the Text component, the source is `packages/components/src/text/README.md` in the Gutenberg repo, and the raw file is publicly readable (HTTP 200 confirmed):

https://raw.githubusercontent.com/WordPress/gutenberg/trunk/packages/components/src/text/README.md

This is the most faithful option for handbook content. It gives the exact markdown the page was built from, without the lossy HTML-to-markdown step. Before importing, confirm that the file is the one the handbook page is built from.

## 4. Round-trip workflow: proxy, export to a Playground blueprint, edit, PR back

This section describes a generic workflow. It is not tied to this repository's folder layout, which holds drafts only. The target GitHub repository, its folder structure, and the proxy's language and hosting are still to be decided.

> **Question:** This paragraph was written for the DocsMechanic repo ("holds drafts only"). Should it now refer to this docs project, and is this project the target GitHub repository for the exported content? If so, what folder layout and branch/PR conventions apply?
>
> **Question:** What language and hosting should the proxy use (for example a small Node service, a serverless function, or a WordPress plugin endpoint)?

The loop: fetch content through a proxy, export the raw block markup into a WordPress Playground blueprint, let contributors check out the blueprint and run it to recreate the post in a Playground instance, edit it in the block editor, and send the changes back as a pull request.

### Prerequisites

1. Editor-level access (or higher) on the source site. `content.raw` is only returned with `?context=edit`, which requires a user who can edit the post.
2. An Application Password created on the source site by that user. It only works on the site that issued it, so each source site needs its own.
3. Authorization from the source site's admin or editor to use that account and credential for this purpose. Request this first. Without it, use the public route in section 3a.

> **Question:** Who authorizes this on each source site (wordpress.org/documentation and developer.wordpress.org), and which account will hold the Application Password? Permission needs to be requested before the proxy is built.
4. The credential is held only by the proxy (environment variable or secret store). It is never committed, never sent to the browser, and never written into a blueprint or PR.
5. A content licensing check. Confirm the content can be republished and modified, and record the license with the exported content.

> **Question:** What license applies to the documentation and handbook content, and does it allow republishing and modification here? Who confirms this?
6. Node.js 20.18 or later, to run Playground CLI. The Playground agent skill is optional and is installed with `npx skills add wordpress/agent-skills --skill wp-playground`. It documents blueprint structure in its `references/blueprint.md`.

### Proxy

The proxy is a server-side endpoint that holds the Application Password so clients never need one.

- Authenticated route: it requests `/wp-json/wp/v2/<type>?slug=<slug>&context=edit` with HTTP Basic auth and reads `content.raw`, `title.raw`, `slug`, `id`, and `link`.
- Public fallback: for handbook pages, it can fetch the markdown source from the code repository (section 3a) with no credential. The primary route for handbook and documentation pages is the authenticated one above (see "Handbook and documentation pages").
- It returns only the content fields and source metadata as JSON. It must never return the `Authorization` header, the credential, or upstream error bodies that might echo it.
- It should accept only an allow-list of source hosts and post types, so it cannot be used to reach arbitrary URLs.
- Clients call the proxy, not the source site.

### Export to a blueprint

1. Take the `content.raw` block markup from the proxy, or copy it from the block editor's code editor view.
2. Generate one blueprint per content item, for example `<slug>/blueprint.json`. The path convention is a decision for the target repository.
3. Keep the raw block markup in its own file next to the blueprint, for example `<slug>/content.html`, so diffs show content changes and not JSON-escaped strings. Put the metadata in the blueprint or a small sidecar file: title, slug, source URL, source post ID, post type, retrieval date, and license.
4. The blueprint recreates the post. A sketch of the steps, to be checked against the skill's `references/blueprint.md`:
   - A `writeFile` step (or a bundled resource) places `content.html` in the instance.
   - A `runPHP` step reads it and calls `wp_insert_post()` with `post_title`, `post_name`, `post_status`, and `post_content` set to the block markup.
   - `landingPage` opens the new post in the editor.

> **Question:** Do these blueprint steps match the Playground Blueprint skill's `references/blueprint.md`? Neither the steps nor the `npx @wp-playground/cli` command used later has been run or checked. Should the skill be installed and one sample blueprint tested before this section is relied on?
5. Commit to a branch and open a PR. Check the diff for credentials before pushing.

### Handbook and documentation pages

The planned route for both developer.wordpress.org and wordpress.org/documentation is the authenticated REST API, using the `flexseth` account:

> **Question:** Is `flexseth` the right account name to keep in this doc? It was added by a subagent, and nothing in the original session shows where it came from. Confirm it, or replace it with a generic "Editor-level account".

1. The proxy requests the item with `?context=edit` as `flexseth`, using that account's Application Password (held by the proxy only).
2. It takes `content.raw` from the response.
3. It turns that raw content into a blueprint step, using the Playground Blueprint skill to author the step and blueprint structure.
4. The blueprint is committed as described above.

Open items to verify before relying on this:

- Whether `flexseth` has edit capability on the target post types (`blocks-handbook` on developer.wordpress.org, `documentation` on wordpress.org). Section 1 showed anonymous `?context=edit` returns 401, and the authenticated result has not been tested.

> **Question:** Can this account actually edit `blocks-handbook` and `documentation` posts? Test an authenticated `?context=edit` request once permission is granted. A 401 or 403 means a role upgrade or a different route (section 3a) is needed.
- Whether `content.raw` for handbook pages is block markup or markdown. The rendered handbook HTML is converted from markdown, so `content.raw` may be markdown with no `<!-- wp:... -->` comments. If so, the blueprint step must convert it to blocks, or insert it as classic content, before it is usable in the block editor.

> **Question:** If `content.raw` for handbook pages is markdown, which conversion should the blueprint use: markdown to blocks, or classic content? This affects whether contributors edit native blocks or a classic block.
- The Blueprint skill has not been run against this yet, so the step shape described above is still a sketch.

The authorization prerequisite applies here: the credential belongs to `flexseth` and works only on the site that issued it, so wordpress.org and developer.wordpress.org may each need their own.

### Checkout and edit

1. Clone the repository.
2. Run the blueprint with Playground CLI, for example `npx @wp-playground/cli server --blueprint=<slug>/blueprint.json`. The post is created in a fresh instance.
3. Open the post in the block editor. The blocks should load with no invalid-content or recovery prompts.
4. Make the edits.
5. Export the result. In the code editor view, copy the block markup, then replace the contents of `content.html`. Leave the metadata unchanged except for intended fields.

### Submit a PR back

1. Branch naming: `content/<slug>` for a new item, `content/<slug>-<short-change>` for an edit.
2. Change only the files for that item.
3. Validation before review:
   - The blueprint runs and the block markup loads in the editor with no invalid or recovery blocks.
   - Links and images still resolve, and image URLs point to the intended source.
   - The diff contains no credentials, Application Passwords, or `Authorization` headers.
   - Metadata is complete and unchanged except for intended fields.
4. Describe the change and the reason in the PR. A maintainer reviews it. An automated validation check is a to-do.

The public Gutenberg README route (section 3a) remains the no-auth fallback for handbook pages.

## Which method is right for you?

The best method depends on what must remain identical:

- **Native Gutenberg blocks, fully editable:** prioritize the original `post_content`.
- **Handbook pages (developer.wordpress.org):** prioritize the markdown source in the Gutenberg repo. Use rendered HTML only as a fallback.
- **Visible content and formatting:** rendered HTML may be sufficient.
- **Content and images/media:** verify that image URLs and any required media files are preserved.
- **Editing and contributing back through GitHub:** use the round-trip workflow in section 4.
- **Everything possible, including metadata:** use a suitable WXR export or source database access if available.

Also, make sure you have the rights and permissions needed to republish the documentation on your new site.