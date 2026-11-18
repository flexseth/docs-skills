What changed in drafts/content-discovery/get-the-content.md:
  - In section 4, I replaced the open question about turning handbook markdown into blocks with a new subsection, "Handbook and
    documentation pages".
  - The subsection sets out the flow for developer.wordpress.org and wordpress.org/documentation:
    a. The proxy requests the item with ?context=edit as flexseth, using that account's Application Password. Only the proxy holds
       the password.
    b. It takes content.raw from the response.
    c. It turns that into a blueprint step, authored with the Playground Blueprint skill.
    d. The blueprint is committed as described earlier in the section.
  - The proxy bullet for the public Gutenberg README route is now a "Public fallback". The authenticated route is the primary one.

  Not verified:
  - flexseth access: I haven't tested the account's edit access on blocks-handbook or documentation. Anonymous ?context=edit
    returned 401.
  - Format of content.raw: it may be markdown rather than block markup for handbook pages. If so, the blueprint step has to convert
    it to blocks or insert it as classic content.
  - Blueprint skill: I haven't run it or installed it. It isn't in this session's skill list, and installing it would change the
    project. The blueprint step shape is still a sketch.
  - Application Password scope: each site may need its own, which the section notes.

  If you want the skill installed and the flow tested, tell me once plan mode is off.