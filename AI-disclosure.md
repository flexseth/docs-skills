# AI Disclosure

This project was partly produced with the help of an AI assistant (Claude Code, using Claude Haiku 5.5). This file records what the AI did, what it checked, and what still needs a human review.

## What the AI did

- **`handbooks.md`:** Searched wordpress.org/documentation and the linked developer and contributor sites to find handbooks. Wrote the list of handbook names and links.
- **`guides.md`:** Pulled the page titles and article links from the four Resource Guides on wordpress.org/documentation (Technical guides, Support guides, Customization, WordPress overview). Added a source-of-truth link for each subsection.
- **`TRIAGE.md`:** A triage of all 469 open issues in WordPress/Documentation-Issue-Tracker (snapshot 2026-10-16). Produced by Claude, not reviewed issue by issue by a person.
- **`docs-assessment.png`:** An infographic summarizing `TRIAGE.md` (backlog age, staleness, release relevance, workflow stalls, and suggested fixes). Created by Claude on the desktop app from `TRIAGE.md`.
- **This file:** Written from the work listed above.

## What was verified

- Each handbook and source URL in `handbooks.md` and `guides.md` was requested over HTTP and returned a 200 response. Two candidate URLs returned 404 and were left out of the files.
- Article titles and links were taken from the content fetched from wordpress.org/documentation at the time of the work. They may have changed since.

## What was not verified

- **Source-of-truth links are inferred.** The wordpress.org/documentation article pages do not name a source. The links in `guides.md` are the closest matching handbook or reference for each section, chosen by the AI. A human should confirm them.
- **Support guides (Dashboard, Media, Publishing)** point to WordPress core code on GitHub. No developer handbook covers these screens, and this choice has not been reviewed.
- **Content accuracy:** The AI did not check the content of the articles against the sources it linked.
- **`TRIAGE.md` and `docs-assessment.png`:** The counts, dates and labels come from the Claude triage run and the infographic built from it. They were not recalculated from the GitHub issues here. The triage classifications (change needed, documentation surface, release) were assigned by the AI, and the infographic notes that some came from titles and labels with manual correction, so counts are close approximations.

## Human review

Before these files are relied on, a person should:

1. Confirm each source-of-truth link in `guides.md`, especially for the Support guides and Site Editor sections.
2. Re-check the lists against wordpress.org/documentation, since the site changes over time.
3. Spot-check a sample of issues in `TRIAGE.md` against the GitHub tracker, and confirm the "closeable now" and "claimed" lists before anyone acts on them.
