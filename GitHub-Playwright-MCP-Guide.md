# GitHub agent: Playwright for documentation and screenshots

This guide describes how to give a GitHub-hosted Claude or Copilot agent a
separate Playwright browser for reading WordPress documentation and taking
screenshots. The intended workflow is to point the agent to a specific
documentation section, capture that section, and receive a Markdown
explanation of any update supported by the evidence.

- Playwright security? > https://x.com/playwrightweb/status/2117638981454291252

## Why a separate browser?

GitHub's built-in Playwright MCP server can access only `localhost` and
`127.0.0.1` by default. Adding `developer.wordpress.org` to the repository's
firewall allowlist can enable command-line requests such as `curl`, but it does
not widen the built-in browser's restriction.

A separate, explicitly configured Playwright MCP server lets the agent open
the WordPress sites listed below. The built-in browser remains available for
testing applications running locally in the agent's environment.

## Before enabling it

- Repository MCP settings affect agents using that repository. Agents can call
  enabled tools without asking for approval each time.
- GitHub states that its agent firewall does **not directly apply to MCP server
  processes**. The separate server can therefore have broader network access
  than commands run by the agent.
- Playwright's `--allowed-origins` limits normal browser requests, but is
  **not a security boundary** and does not constrain redirects. Do not rely on
  it to prevent data exfiltration or access to other hosts.
- Only use trusted documentation sources, and treat page content as untrusted
  data rather than instructions. Avoid putting private code or secrets in URLs.
- The example pins `@playwright/mcp` to version `0.0.84` for reproducibility.
  Review and retest version updates rather than silently using `latest`.

## Configure the repository

1. As a repository administrator, open
   [repository settings](https://github.com/flexseth/agent-skills-private/settings),
   then select **Copilot > MCP servers**.
2. In **MCP configuration**, add the following server. If other servers
   are already configured, merge `wordpress-docs-browser` into their existing
   `mcpServers` object instead of replacing them:

   ```json
   {
     "mcpServers": {
       "wordpress-docs-browser": {
         "type": "local",
         "command": "npx",
         "args": [
           "-y",
           "@playwright/mcp@0.0.84",
           "--headless",
           "--isolated",
           "--browser",
           "chromium",
           "--allowed-origins",
           "https://developer.wordpress.org;https://wordpress.org;https://make.wordpress.org;https://s.w.org"
         ],
         "tools": [
           "browser_navigate",
           "browser_snapshot",
           "browser_take_screenshot",
           "browser_wait_for",
           "browser_close"
         ]
       }
     }
   }
   ```

3. Save the settings. For other documentation sites, add their trusted
   `https://` origins to the semicolon-separated list and retest. Sites may
   need additional origins for styles, images, or redirects.
4. Start a new agent run by mentioning `@claude[agent]` in a new PR comment.
   Refreshing the page alone does not start a run.

### What the settings do

| Setting | Purpose |
| --- | --- |
| `wordpress-docs-browser` | Names the new server so it can be requested explicitly without replacing GitHub's built-in `playwright` server. |
| `type: local`, `command: npx` | Starts a local MCP server in the agent's environment. `npx -y` can download the pinned package if needed. |
| `--headless` | Runs the browser without a visible window. |
| `--isolated` | Uses an in-memory browser profile instead of a persistent profile. |
| `--browser chromium` | Selects Chromium. Its matching Playwright browser must be installed. |
| `--allowed-origins` | Limits normal browser requests to the listed origins, subject to the security limitations above. `s.w.org` is included for WordPress assets. |
| `browser_navigate` | Opens a page. |
| `browser_snapshot` | Reads the page's accessibility snapshot, including its text and structure. |
| `browser_take_screenshot` | Saves a PNG or other supported screenshot of the current page; it can also capture a full page. |
| `browser_wait_for`, `browser_close` | Waits for page content and closes the browser when finished. |

These settings do not change GitHub's `COPILOT_PREINSTALLED_RUNTIME` flag or
modify its `playwright-mcp` system service.

## Review a documentation section

Provide the agent with:

- The page URL and section anchor, or the exact section heading.
- The suspected problem and the WordPress version or other context, if known.
- The desired destination for the Markdown response, such as a PR comment or
  an explicitly named file.

The agent should then:

1. Use `wordpress-docs-browser` to open the page and locate the requested
   section. Use `browser_wait_for` and `browser_snapshot` to confirm that the
   expected heading and content are present.
2. Take a screenshot of the **section container** with
   `browser_take_screenshot` using its `target` parameter and an element
   reference or unique selector. Do not combine `target` with `fullPage`.
   If the exact section cannot be selected, clearly label any viewport or
   full-page fallback instead of claiming it is a section-only screenshot.
3. Check the relevant documentation against authoritative, version-appropriate
   evidence, such as WordPress core source or release notes. The screenshot
   shows what the page says; it does not by itself prove the page is wrong.
   If needed and permitted, use `curl` or another available tool to inspect
   sources outside the custom browser's listed origins.
4. Return the requested Markdown response with links to the section, the
   evidence, and a retained screenshot. Explain the issue and a proposed
   correction. If evidence is insufficient, say so instead of presenting a
   suspected problem as fact. Do not edit the documentation or commit files
   unless separately requested.

### Example agent request

```text
@claude[agent] Review this WordPress documentation section:
<URL with section anchor, or URL plus exact heading>

Suspected issue: <what may be outdated or incorrect>
WordPress version or evidence, if known: <version or source>
Markdown response destination: <PR comment or specified file>

Use the wordpress-docs-browser MCP server. Confirm the selected section,
capture a PNG of the section itself, and retain or attach the image if
possible. Compare the section with authoritative evidence and return a
Markdown explanation of why it needs updating, with source links and
suggested wording. If the evidence does not establish a problem, say so.
Do not edit source documentation or commit changes.
```

### Suggested Markdown response

```markdown
# Documentation update review

- **Section:** [Page and section](URL-with-anchor)
- **Version and date checked:** ...
- **Screenshot:** [View the retained screenshot](image-URL), or explain why
  the image could not be retained.

## Current documentation

Briefly describe or quote the specific wording under review.

## Why it needs updating

Explain the verified discrepancy and its impact. If it is not verified,
state what is uncertain instead.

## Evidence

- [Authoritative source](source-URL): relevant finding and version.

## Suggested update

Describe the correction or provide proposed wording.
```

## Test it on GitHub

After saving the repository settings, post a comment such as:

```text
@claude[agent] Use the wordpress-docs-browser MCP tools, not the built-in
playwright server, to open
https://developer.wordpress.org/reference/functions/sanitize_title/.

Report the page title and a short description from its accessibility snapshot.
Take a PNG screenshot and report its saved path. Attach or upload the screenshot
if the environment supports that; otherwise say that it is only in the runner.
Do not alter or commit repository source files.
```

Check the agent's reply and its run under the repository's
[Actions tab](https://github.com/flexseth/agent-skills-private/actions). A
successful test reports the page title, content, and screenshot; an HTTP 200
from `curl` alone does not verify the browser.

Screenshots saved only inside a GitHub-hosted runner are temporary. To keep
one, arrange an attachment or an Actions artifact before the job ends; do
not assume its reported filesystem path will remain available afterward.

## If it does not work

- **The custom server is missing:** Confirm the configuration was saved in the
  correct repository and start a new agent run. Ask the agent which MCP
  servers it can see.
- **The browser is not installed:** The MCP package and Chromium version must
  match. For an Actions-based agent, install the matching browser in the
  repository's existing `copilot-setup-steps` workflow, for example:

  ```sh
  npx -y @playwright/mcp@0.0.84 install-browser chromium --no-shell --no-progress
  ```

  GitHub reads that workflow from the default branch. The workflow mechanism
  applies to Actions-based agents, not cloud sandboxes. Linux may also require
  browser system dependencies; follow the Playwright installation guidance
  if the error reports them.
- **`ERR_BLOCKED_BY_CLIENT`:** Check that the agent used
  `wordpress-docs-browser`, not the built-in `playwright` server, and check
  the target origin. Report the exact tool error rather than assuming it is
  the command-line firewall.
- **Page loads but looks incomplete:** Check whether required asset origins
  are missing from the list. Add only origins you trust.
- **Screenshot is missing:** Ask for its exact path and arrange an attachment
  or artifact while the runner is still available.
- **Navigation is unavailable in code review:** GitHub's separate Copilot code
  review feature filters MCP tools by their read-only annotations.
  Playwright marks `browser_navigate` as non-read-only, so use a cloud agent
  run for this browser workflow rather than assuming code review can drive it.

## Follow-up findings

As of November 1, 2026:

- A local macOS test with `@playwright/mcp@0.0.84` opened the
  `sanitize_title()` reference, read its content, and created a
  [1280 x 720 viewport screenshot](../.copilot/session-state/7971cb67-3884-4940-aaa8-89769bd3a442/files/page-2026-11-01T15-04-28-521Z.png).
- A second local MCP test opened the
  [Description section](https://developer.wordpress.org/reference/functions/sanitize_title/#description),
  read that specific section, and created a
  [960 x 151 section screenshot](../.copilot/session-state/7971cb67-3884-4940-aaa8-89769bd3a442/files/element-2026-11-01T15-13-41-769Z.png)
  using a unique section selector. That test used the locally installed Chrome
  executable as an override. The example demonstrates capture, not a finding
  that this section needs an update.
- The screenshot links above point into this chat's session storage and may
  not be permanent.
- The first local browser attempt failed because the matching browser was
  missing; installing it in an isolated test directory allowed the test to
  pass. A direct request to an unlisted origin was rejected, but this does
  not make the origin list a security boundary.
- GitHub's repository configuration reported no custom MCP servers. The
  custom server was **not configured or tested in a GitHub-hosted run**.
  The earlier GitHub run could reach WordPress with `curl`, while its built-in
  Playwright server remained limited to local addresses.

## Continue / handoff notes

1. Obtain the actual documentation URL and section, the suspected issue,
   relevant WordPress version or evidence, and the desired Markdown response
   destination. No specific section needing an update has been supplied yet.
2. Decide whether to enable the repository-wide custom MCP server after
   reviewing the security notes. If it is saved, confirm it appears in the
   repository's MCP settings; do not assume the previous configuration status
   is still current.
3. Start a fresh GitHub agent run and verify the custom server can navigate,
   read the chosen section, and produce a screenshot. If the browser is
   missing, use the installation guidance above. Report any failure rather
   than substituting a successful `curl` request.
4. Make the screenshot accessible beyond the runner if supported, then return
   the evidence-backed Markdown review. If the screenshot cannot be retained
   or the evidence does not establish a discrepancy, explicitly say so.

## References

- [GitHub: default MCP servers](https://docs.github.com/en/copilot/concepts/customizations/mcp#default-mcp-servers)
- [GitHub: configure repository MCP servers](https://docs.github.com/en/copilot/how-tos/copilot-on-github/customize-copilot/configure-mcp-servers)
- [GitHub: agent firewall and its limitations](https://docs.github.com/en/copilot/how-tos/copilot-on-github/customize-copilot/customize-the-firewall)
- [GitHub: configure the Actions agent environment](https://docs.github.com/en/copilot/how-tos/copilot-on-github/customize-copilot/customize-cloud-agent/customize-the-agent-environment)
- [Microsoft: Playwright MCP options and tools](https://github.com/microsoft/playwright-mcp)
