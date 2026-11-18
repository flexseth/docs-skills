# WordPress Documentation Skill - Planning Document

**Created:** 2026-08-18  
**Purpose:** Planning document for wp-docs skill creation  
**Status:** 📋 AWAITING REVIEW - Please provide inline feedback  

---

## 1. Skill Purpose & Scope

### Primary Purpose
Create a minimal documentation lookup skill that provides quick links to canonical WordPress.org documentation.

**Question 1.1:** Does this align with your vision for the docs skill?
<!-- YOUR FEEDBACK: -->


**Question 1.2:** Should this be called `wp-docs` or something else (e.g., `wp-docs-lookup`, `wp-reference`)?
<!-- YOUR FEEDBACK: -->


### Design Philosophy
Following repository patterns:
- Keep reference files SMALL (30-200 lines each)
- Link to canonical docs, don't duplicate content
- Provide quick lookups, not comprehensive guides
- Use minimal indexes + URL pattern matching

**Question 1.3:** Do you agree with this minimal/lookup approach vs. comprehensive documentation?
<!-- YOUR FEEDBACK: -->


---

## 2. Skill Structure

### Proposed File Structure
```
skills/wp-docs/
├── SKILL.md                    # ~100 lines - routing logic
├── references/
│   ├── search-patterns.md      # ~75 lines - URL construction rules
│   ├── functions-essential.md  # ~150 lines - top 100 functions
│   ├── hooks-essential.md      # ~150 lines - top 100 hooks
│   ├── classes-essential.md    # ~100 lines - top 50 classes
│   ├── rest-endpoints.md       # ~100 lines - REST API reference
│   ├── cli-commands.md         # ~100 lines - WP-CLI commands
│   ├── block-editor-apis.md    # ~100 lines - Block Editor APIs
│   └── docs-categories.md      # ~75 lines - documentation map
└── scripts/
    └── validate-links.mjs      # ~150 lines - URL validation
```

**Total: ~1,000 lines (~20KB)**

**Question 2.1:** Is this structure appropriate? Should we add/remove/rename any files?
<!-- YOUR FEEDBACK: -->


**Question 2.2:** Should we include a scripts/validate-links.mjs or is that overkill?
<!-- YOUR FEEDBACK: -->


---

## 3. Index Selection Strategy

### Top N Selection Criteria

**Functions Index** (Top 100):
- Post/Page functions (get_post, wp_insert_post, etc.)
- User functions (wp_get_current_user, get_user_by, etc.)
- Options (get_option, update_option, etc.)
- Meta (get_post_meta, update_post_meta, etc.)
- Scripts/Styles (wp_enqueue_script, wp_enqueue_style, etc.)
- i18n (__, _e, _x, etc.)
- Security (sanitize_*, esc_*, wp_verify_nonce, etc.)

**Hooks Index** (Top 100):
- Init hooks (init, plugins_loaded, after_setup_theme, etc.)
- Admin hooks (admin_init, admin_menu, admin_enqueue_scripts, etc.)
- Content hooks (save_post, the_content, the_excerpt, etc.)
- Template hooks (wp_head, wp_footer, template_redirect, etc.)

**Classes Index** (Top 50):
- Query classes (WP_Query, WP_User_Query, WP_Term_Query, etc.)
- Core classes (wpdb, WP_Error)
- REST classes (WP_REST_Server, WP_REST_Request, WP_REST_Response, etc.)
- Block classes (WP_Block_Type, WP_Block_Type_Registry, etc.)

**Question 3.1:** Does "Top N by usage frequency" make sense as selection criteria?
<!-- YOUR FEEDBACK: -->


**Question 3.2:** Should we base "top N" on:
- [ ] Your knowledge of common usage patterns
- [ ] Analysis of WordPress plugin repository
- [ ] WordPress Core usage statistics (if available)
- [ ] Combination of the above
<!-- YOUR FEEDBACK: -->


**Question 3.3:** Are these the right numbers (100 functions, 100 hooks, 50 classes)?
<!-- YOUR FEEDBACK: -->


---

## 4. URL Pattern Matching

### For items NOT in indexes, construct URLs using patterns:

**Example: Functions**
- Input: `get_post`
- Transform: lowercase → replace `_` with `-` → add trailing `/`
- Output: `https://developer.wordpress.org/reference/functions/get-post/`

**Example: Classes**
- Input: `WP_Query`
- Transform: lowercase → replace `_` with `-` → add trailing `/`
- Output: `https://developer.wordpress.org/reference/classes/wp-query/`

**Example: Hooks**
- Input: `save_post`
- Transform: lowercase → replace `_` with `-` → add trailing `/`
- Output: `https://developer.wordpress.org/reference/hooks/save-post/`

**Question 4.1:** Are these transform rules correct for WordPress.org documentation URLs?
<!-- YOUR FEEDBACK: -->


**Question 4.2:** Should we handle edge cases like:
- Dynamic hooks with `{$variable}` syntax
- Class methods (e.g., `WP_Query::get_posts()`)
- Namespaced items
<!-- YOUR FEEDBACK: -->


---

## 5. Documentation Surfaces Covered

### Planned Coverage

1. **Reference Documentation** (`/reference/`)
   - Functions, Classes, Hooks
   - Via indexes + pattern matching

2. **REST API** (`/rest-api/`)
   - All core endpoints
   - Key guides (authentication, routes, schema)

3. **Block Editor** (`/block-editor/`)
   - Block API guides
   - Key packages (@wordpress/blocks, @wordpress/components, etc.)
   - Interactivity API

4. **WP-CLI** (`/cli/commands/`)
   - Common commands (core, post, user, plugin, theme, db)

5. **Plugin Handbook** (`/plugins/`)
   - Link to main sections
   - Not comprehensive

6. **Theme Handbook** (`/themes/`)
   - Link to main sections
   - Not comprehensive

**Question 5.1:** Should we add coverage for:
- [ ] Advanced Administration docs
- [ ] Abilities API docs
- [ ] Other specialized APIs
<!-- YOUR FEEDBACK: -->


**Question 5.2:** For Plugin/Theme handbooks, should we:
- [ ] Just link to homepage and let users navigate
- [ ] Index key handbook sections
- [ ] Skip entirely (covered by other skills)
<!-- YOUR FEEDBACK: -->


---

## 6. Missing/Emerging Documentation Areas

Based on sitemap analysis, these areas have limited documentation:

### Recently Added / Emerging
1. **Abilities API** - New capability system, docs evolving
2. **Secure Custom Fields (SCF)** - May be experimental
3. **AI/Connectors** - Very new, WordPress AI integration
4. **Font Library API** - New font management
5. **Block Bindings API** - Recent Block Editor addition
6. **HTML API** - New HTML5 processor
7. **Interactivity API** - Rapidly expanding
8. **Plugin Dependencies API** - New dependency checking

**Question 6.1:** Should we include these emerging APIs in initial version or wait for stable docs?
<!-- YOUR FEEDBACK: -->


**Question 6.2:** Which of these (if any) are high priority for your use cases?
<!-- YOUR FEEDBACK: -->


---

## 7. C4WP Integration

### Research Needed

The plan mentions investigating "Claude for WordPress" (C4WP) to see if there's an existing docs agent.

**Question 7.1:** Does C4WP exist? If so, where is it located?
<!-- YOUR FEEDBACK: -->


**Question 7.2:** Should this wp-docs skill:
- [ ] Replace an existing C4WP docs agent
- [ ] Work alongside C4WP
- [ ] Be independent (no C4WP exists or is not relevant)
<!-- YOUR FEEDBACK: -->


**Question 7.3:** Are there any existing WordPress documentation tools/agents we should be aware of?
<!-- YOUR FEEDBACK: -->


---

## 8. Implementation Timeline

### Proposed Phases

**Phase 1 (Day 1):** Core Structure
- Create skill directory
- Write SKILL.md with routing logic
- Create search-patterns.md
- Create docs-categories.md

**Phase 2 (Day 2-3):** Essential Indexes
- Create functions-essential.md (top 100)
- Create hooks-essential.md (top 100)
- Create classes-essential.md (top 50)

**Phase 3 (Day 4):** API Surface Indexes
- Create rest-endpoints.md
- Create cli-commands.md
- Create block-editor-apis.md

**Phase 4 (Day 5):** Validation & Testing
- Create validation script
- Test common queries
- Test edge cases

**Phase 5 (Day 6):** Integration
- Add to wordpress-router
- Create evaluation scenarios
- Build distribution packages

**Question 8.1:** Is 6 days reasonable for this scope?
<!-- YOUR FEEDBACK: -->


**Question 8.2:** Should we prioritize certain phases over others?
<!-- YOUR FEEDBACK: -->


**Question 8.3:** Should this be implemented incrementally (1 phase at a time) or all at once?
<!-- YOUR FEEDBACK: -->


---

## 9. Format Example

### Proposed Index Format

```markdown
# Essential WordPress Functions

Quick lookup for the most commonly used WordPress functions.

## Post Functions
- `get_post()` → https://developer.wordpress.org/reference/functions/get-post/
- `get_posts()` → https://developer.wordpress.org/reference/functions/get_posts/
- `wp_insert_post()` → https://developer.wordpress.org/reference/functions/wp-insert-post/
- `wp_update_post()` → https://developer.wordpress.org/reference/functions/wp-update-post/
- `wp_delete_post()` → https://developer.wordpress.org/reference/functions/wp-delete-post/

## User Functions
- `wp_get_current_user()` → https://developer.wordpress.org/reference/functions/wp-get-current-user/
- `get_user_by()` → https://developer.wordpress.org/reference/functions/get-user-by/
- `get_userdata()` → https://developer.wordpress.org/reference/functions/get-userdata/
...
```

**Question 9.1:** Is this format clear and minimal enough?
<!-- YOUR FEEDBACK: -->


**Question 9.2:** Should we add any metadata (e.g., "Available since WP X.X")?
<!-- YOUR FEEDBACK: -->


**Question 9.3:** Should we group by category or just alphabetical?
<!-- YOUR FEEDBACK: -->


---

## 10. Routing Logic

### SKILL.md Procedure (Draft)

```
1. Identify the type (function, hook, class, REST, CLI, etc.)
2. Check relevant index file for direct match
3. If found → return URL
4. If not found → use search-patterns.md to construct URL
5. Return canonical documentation link
```

**Question 10.1:** Is this procedure correct?
<!-- YOUR FEEDBACK: -->


**Question 10.2:** Should the skill:
- [ ] Always check index first, then pattern match
- [ ] Try pattern matching first for speed
- [ ] Use heuristics to guess which approach to try
<!-- YOUR FEEDBACK: -->


---

## 11. Maintenance & Updates

### Upstream Sync Strategy

**High Priority (Monthly):**
- Validate all index URLs still work
- Check for new essential functions/hooks in WordPress releases

**Medium Priority (Quarterly):**
- Update Block Editor APIs with new packages
- Review REST API for new endpoints

**Low Priority (Annually):**
- Refresh "top N" selections based on usage changes

**Question 11.1:** Is this maintenance cadence appropriate?
<!-- YOUR FEEDBACK: -->


**Question 11.2:** Should upstream sync be:
- [ ] Manual (human-triggered)
- [ ] Semi-automated (script suggests changes)
- [ ] Fully automated (GitHub Actions + PR)
<!-- YOUR FEEDBACK: -->


---

## 12. Success Metrics

### How we'll measure success:

**Quantitative:**
- Skill size < 20KB total
- 80%+ of queries hit index (vs. pattern match)
- Response time < 1 second
- 95%+ URL accuracy

**Qualitative:**
- AI can find any WordPress core API documentation
- Minimal maintenance burden
- Fast enough for interactive use
- Follows repository patterns

**Question 12.1:** Are these the right metrics?
<!-- YOUR FEEDBACK: -->


**Question 12.2:** What other success criteria should we track?
<!-- YOUR FEEDBACK: -->


---

## 13. Open Questions & Decisions Needed

### Technical Decisions

**Q13.1:** Should we support version-specific documentation?
- Example: Link to WordPress 6.5 docs vs. latest?
<!-- YOUR FEEDBACK: -->


**Q13.2:** Should we support localized documentation?
- Example: French/German/Spanish developer docs?
<!-- YOUR FEEDBACK: -->


**Q13.3:** Should we handle deprecated items specially?
- Example: Flag deprecated functions, link to replacement?
<!-- YOUR FEEDBACK: -->


### Scope Decisions

**Q13.4:** Should this skill be WordPress.org docs ONLY, or include:
- [ ] WordPress.org only
- [ ] Also include developer.wordpress.com (WordPress VIP)
- [ ] Also include WooCommerce developer docs
- [ ] Also include popular plugin docs (ACF, Yoast, etc.)
<!-- YOUR FEEDBACK: -->


**Q13.5:** Should the skill provide code examples or just links?
- [ ] Links only (minimal approach)
- [ ] Links + brief description
- [ ] Links + description + code snippet
<!-- YOUR FEEDBACK: -->


---

## 14. Alternative Approaches Considered

### Alternative 1: Comprehensive Documentation
**Approach:** Replicate WordPress.org documentation content in skill files  
**Pros:** Self-contained, works offline  
**Cons:** Massive size (100s of MB), constant sync needed, violates repo patterns  
**Decision:** ❌ Rejected - doesn't fit repository philosophy

### Alternative 2: API-Based Lookup
**Approach:** Query WordPress.org API for documentation in real-time  
**Pros:** Always up-to-date  
**Cons:** Network dependency, slower, API may not exist  
**Decision:** ❌ Rejected - skills should be network-free

### Alternative 3: Full Sitemap Index
**Approach:** Index ALL 3,000+ documentation pages  
**Pros:** Complete coverage via index  
**Cons:** Too large (~100KB+), violates "minimal" principle  
**Decision:** ❌ Rejected - top N + pattern matching is better

### Alternative 4: Chosen Approach
**Approach:** Top N indexes + URL pattern matching  
**Pros:** Minimal size, fast lookups, easy maintenance  
**Cons:** Pattern matching might fail for edge cases  
**Decision:** ✅ Selected - best balance

**Question 14.1:** Do you agree with this decision?
<!-- YOUR FEEDBACK: -->


**Question 14.2:** Are there other approaches we should consider?
<!-- YOUR FEEDBACK: -->


---

## 15. Dependencies & Integration

### Dependencies

**Requires:**
- `wordpress-router` - for routing to this skill
- Node.js 20+ - for validation script (optional)

**Optional:**
- `wp-project-triage` - could inform which docs are relevant

**No External Dependencies:**
- No network calls
- No API keys
- No authentication

**Question 15.1:** Should this skill have any other dependencies?
<!-- YOUR FEEDBACK: -->


### Integration Points

**With wordpress-router:**
- Add decision tree entry for documentation lookups

**With other skills:**
- `wp-block-development` could reference block-editor-apis.md
- `wp-rest-api` could reference rest-endpoints.md
- `wp-wpcli-and-ops` could reference cli-commands.md

**Question 15.2:** Should other skills reference wp-docs, or is that coupling too tight?
<!-- YOUR FEEDBACK: -->


---

## 16. Risk Assessment

### Low Risk Items
✅ URL pattern matching (well-defined, testable)  
✅ Index creation (straightforward data collection)  
✅ File structure (follows existing patterns)

### Medium Risk Items
⚠️ Choosing "right" top N items (subjective, may need iteration)  
⚠️ Keeping URLs in sync with WordPress.org changes  
⚠️ Handling edge cases (dynamic hooks, deprecated items)

### High Risk Items
❌ None identified

**Question 16.1:** Do you see any high-risk items we're missing?
<!-- YOUR FEEDBACK: -->


**Question 16.2:** How should we mitigate the medium-risk items?
<!-- YOUR FEEDBACK: -->


---

## 17. Future Enhancements (Post-v1.0)

### Potential v1.1+ Features

**v1.1:** Class method lookups
- Example: `WP_Query::get_posts()` → method documentation

**v1.2:** Code example links
- Link directly to code examples tab on WordPress.org

**v1.3:** Version-aware routing
- "This requires WordPress 6.5+" context

**v1.4:** Related documentation
- "See also: wp_update_post(), wp_delete_post()"

**v2.0:** Smart search with fuzzy matching
- "wp_inser_post" → "Did you mean wp_insert_post?"

**Question 17.1:** Which of these should be prioritized for v1.1?
<!-- YOUR FEEDBACK: -->


**Question 17.2:** Are there other enhancements you'd like to see?
<!-- YOUR FEEDBACK: -->


---

## 18. Final Questions

**Q18.1:** Should we proceed with this plan as-is, or make significant changes?
<!-- YOUR FEEDBACK: -->


**Q18.2:** What's the priority level for this skill?
- [ ] High priority - start immediately
- [ ] Medium priority - start within 1-2 weeks
- [ ] Low priority - nice to have, no rush
<!-- YOUR FEEDBACK: -->


**Q18.3:** Who should implement this?
- [ ] You (Claude) should implement following this plan
- [ ] Human will implement using this as a guide
- [ ] Collaborative implementation
<!-- YOUR FEEDBACK: -->


**Q18.4:** Any other feedback, concerns, or suggestions?
<!-- YOUR FEEDBACK: -->


---

## Next Steps After Review

Once you've provided inline feedback:

1. ✅ Review and incorporate your feedback
2. ✅ Update PLAN-docs-skill.md with approved approach
3. ✅ Begin implementation (if approved to proceed)
4. ✅ Create evaluation scenarios
5. ✅ Integrate with repository

---

**Status:** 📋 AWAITING YOUR REVIEW  
**Please provide feedback inline using the `<!-- YOUR FEEDBACK: -->` markers**

---

**Document Version:** 1.0 - Initial Draft  
**Last Updated:** 2026-08-18
