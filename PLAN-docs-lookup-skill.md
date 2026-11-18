# Plan: WordPress Documentation Lookup Skill

Skill starting point for Agent Skills

**Created:** 2026-08-18  
**Purpose:** Create a minimal, efficient documentation lookup skill for WordPress developer reference  
**Pattern:** Following agent-skills repository conventions for small, focused reference skills  

---

## Executive Summary

Create a lightweight `wp-docs` skill that provides **quick lookups** to canonical WordPress documentation rather than duplicating documentation content. The skill will use minimal index files (top N most-used items) combined with URL pattern matching for the long tail.

**Target Size:** ~12-15KB total (550-750 lines across all reference files)  
**Coverage:** 80% of common lookups via indexes, 20% via pattern matching  
**Maintenance:** Low - just URL mappings, no content replication  

---

## Design Principles (Aligned with Repository)

Based on analysis of existing skills (`wp-rest-api`, `wp-block-development`, etc.):

1. ✅ **Keep references SMALL** - Most reference files are 30-200 lines
2. ✅ **Link, don't duplicate** - Point to canonical WordPress.org documentation
3. ✅ **Practical guidance only** - Key points and quick lookups, not comprehensive docs
4. ✅ **Topic-focused files** - One category per reference file

---

## Skill Structure

```
skills/wp-docs/
├── SKILL.md                    # Routing and lookup procedure (~100 lines)
├── references/
│   ├── search-patterns.md      # URL construction rules (~75 lines)
│   ├── functions-essential.md  # Top 100 functions (~150 lines)
│   ├── hooks-essential.md      # Top 100 hooks (~150 lines)
│   ├── classes-essential.md    # Top 50 classes (~100 lines)
│   ├── rest-endpoints.md       # REST API quick reference (~100 lines)
│   ├── cli-commands.md         # Common WP-CLI commands (~100 lines)
│   ├── block-editor-apis.md    # Block Editor API surface (~100 lines)
│   └── docs-categories.md      # Documentation surface map (~75 lines)
└── scripts/
    └── validate-links.mjs      # Validation script to check URLs (~150 lines)
```

**Total estimated size:** ~1,000 lines (~20KB)

---

## Documentation Surfaces (Categorized)

### 1. Reference Documentation (`/reference/`)

**Coverage:** Functions, Classes, Hooks - Core PHP API

#### Functions Index (`functions-essential.md`)
**Format:**
```markdown
# Essential WordPress Functions

Quick lookup for the most commonly used WordPress functions.

## Post Functions
- `get_post()` → https://developer.wordpress.org/reference/functions/get_post/
- `get_posts()` → https://developer.wordpress.org/reference/functions/get_posts/
- `wp_insert_post()` → https://developer.wordpress.org/reference/functions/wp_insert_post/
...
```

**Selection Criteria:** Top 100 functions by usage frequency
- Post/Page: get_post, wp_insert_post, wp_update_post, wp_delete_post, get_posts
- User: get_user_by, wp_get_current_user, get_userdata, wp_insert_user
- Options: get_option, update_option, add_option, delete_option
- Meta: get_post_meta, update_post_meta, add_post_meta, delete_post_meta
- Taxonomy: get_term, get_terms, wp_insert_term, wp_set_post_terms
- Scripts/Styles: wp_enqueue_script, wp_enqueue_style, wp_localize_script
- i18n: __, _e, _n, _x, esc_html__, load_textdomain
- Security: wp_verify_nonce, check_admin_referer, sanitize_text_field, esc_html
- Hooks: add_action, add_filter, do_action, apply_filters
- HTTP: wp_remote_get, wp_remote_post
- Cron: wp_schedule_event, wp_next_scheduled
- And 50+ more essential functions

**Estimated:** 150 lines

#### Hooks Index (`hooks-essential.md`)
**Format:**
```markdown
# Essential WordPress Hooks

Quick lookup for the most commonly used actions and filters.

## Init & Loading Hooks
- `init` → https://developer.wordpress.org/reference/hooks/init/
- `plugins_loaded` → https://developer.wordpress.org/reference/hooks/plugins_loaded/
- `after_setup_theme` → https://developer.wordpress.org/reference/hooks/after_setup_theme/
...
```

**Selection Criteria:** Top 100 hooks by usage
- Init: init, plugins_loaded, after_setup_theme, wp_loaded
- Admin: admin_init, admin_menu, admin_enqueue_scripts, admin_notices
- Content: save_post, wp_insert_post, the_content, the_excerpt
- Scripts: wp_enqueue_scripts, login_enqueue_scripts
- User: wp_login, user_register, profile_update
- REST: rest_api_init
- Template: template_redirect, wp_head, wp_footer
- And 70+ more essential hooks

**Estimated:** 150 lines

#### Classes Index (`classes-essential.md`)
**Format:**
```markdown
# Essential WordPress Classes

Quick lookup for the most commonly used WordPress classes.

## Query Classes
- `WP_Query` → https://developer.wordpress.org/reference/classes/wp_query/
- `WP_User_Query` → https://developer.wordpress.org/reference/classes/wp_user_query/
...
```

**Selection Criteria:** Top 50 classes
- WP_Query, WP_User_Query, WP_Comment_Query, WP_Term_Query
- wpdb, WP_Error
- WP_REST_Server, WP_REST_Request, WP_REST_Response, WP_REST_Controller
- WP_Block_Type, WP_Block_Type_Registry
- WP_Widget, WP_Customize_Manager
- And 35+ more essential classes

**Estimated:** 100 lines

---

### 2. REST API (`/rest-api/`)

**Coverage:** REST API endpoints and guides

#### REST Endpoints (`rest-endpoints.md`)
**Format:**
```markdown
# WordPress REST API Endpoints

Quick reference to REST API endpoints.

## Content Endpoints
- **Posts** → https://developer.wordpress.org/rest-api/reference/posts/
- **Pages** → https://developer.wordpress.org/rest-api/reference/pages/
- **Media** → https://developer.wordpress.org/rest-api/reference/media/

## Guides
- **Overview** → https://developer.wordpress.org/rest-api/
- **Routes & Endpoints** → https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/
- **Authentication** → https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
...
```

**Coverage:**
- All core REST endpoints (~25 endpoints)
- Key guides (~10 guides)
- Total: ~100 lines

---

### 3. Block Editor (`/block-editor/`)

**Coverage:** Block development APIs and packages

#### Block Editor APIs (`block-editor-apis.md`)
**Format:**
```markdown
# Block Editor API Reference

Quick reference to Block Editor documentation.

## Block API
- **Block Metadata (block.json)** → https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/
- **Block Registration** → https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
- **Block Attributes** → https://developer.wordpress.org/block-editor/reference-guides/block-api/block-attributes/

## Key Packages
- **@wordpress/blocks** → https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/
- **@wordpress/block-editor** → https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/
- **@wordpress/components** → https://developer.wordpress.org/block-editor/reference-guides/components/

## Interactivity API
- **Quick Start** → https://developer.wordpress.org/block-editor/reference-guides/interactivity-api/iapi-quick-start-guide/
- **API Reference** → https://developer.wordpress.org/block-editor/reference-guides/interactivity-api/api-reference/
...
```

**Coverage:**
- Block API guides (~15 items)
- Key packages (~20 packages)
- Components (~20 most-used)
- Total: ~100 lines

---

### 4. WP-CLI Commands (`/cli/commands/`)

**Coverage:** Common WP-CLI commands

#### CLI Commands (`cli-commands.md`)
**Format:**
```markdown
# WP-CLI Commands Reference

Quick reference to WP-CLI command documentation.

## Core Commands
- `wp core download` → https://developer.wordpress.org/cli/commands/core/download/
- `wp core install` → https://developer.wordpress.org/cli/commands/core/install/
- `wp core update` → https://developer.wordpress.org/cli/commands/core/update/

## Post Management
- `wp post create` → https://developer.wordpress.org/cli/commands/post/create/
- `wp post list` → https://developer.wordpress.org/cli/commands/post/list/
...
```

**Coverage:**
- Top 50 most-used CLI commands
- Organized by category (core, post, user, plugin, theme, db, cache, etc.)
- Total: ~100 lines

---

### 5. URL Pattern Matching (`search-patterns.md`)

**Coverage:** How to construct documentation URLs for items not in indexes

#### Search Patterns (`search-patterns.md`)
**Format:**
```markdown
# WordPress Documentation URL Patterns

## Reference Documentation

**Base URL:** https://developer.wordpress.org/reference/

### Functions
**Pattern:** `/reference/functions/{function-name}/`

**Transform Rules:**
1. Convert to lowercase
2. Replace underscores with hyphens
3. Append trailing slash

**Examples:**
- `get_post` → `get-post` → https://developer.wordpress.org/reference/functions/get-post/
- `wp_insert_post` → `wp-insert-post` → https://developer.wordpress.org/reference/functions/wp-insert-post/

### Classes
**Pattern:** `/reference/classes/{class-name}/`

**Transform Rules:**
1. Convert to lowercase
2. Replace underscores with hyphens
3. Append trailing slash

**Examples:**
- `WP_Query` → `wp_query` → `wp-query` → https://developer.wordpress.org/reference/classes/wp-query/
- `WP_REST_Request` → `wp-rest-request` → https://developer.wordpress.org/reference/classes/wp-rest-request/

### Hooks
**Pattern:** `/reference/hooks/{hook-name}/`

**Transform Rules:**
1. Convert to lowercase
2. Replace underscores with hyphens
3. Append trailing slash

**Examples:**
- `init` → https://developer.wordpress.org/reference/hooks/init/
- `save_post` → `save-post` → https://developer.wordpress.org/reference/hooks/save-post/

## REST API

**Base URL:** https://developer.wordpress.org/rest-api/

### Endpoints
**Pattern:** `/rest-api/reference/{endpoint-name}/`

**Examples:**
- Posts → https://developer.wordpress.org/rest-api/reference/posts/
- Users → https://developer.wordpress.org/rest-api/reference/users/

## Block Editor

**Base URL:** https://developer.wordpress.org/block-editor/

### Packages
**Pattern:** `/block-editor/reference-guides/packages/packages-{package-name}/`

**Examples:**
- @wordpress/blocks → https://developer.wordpress.org/block-editor/reference-guides/packages/packages-blocks/
- @wordpress/components → https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/

## WP-CLI

**Base URL:** https://developer.wordpress.org/cli/commands/

**Pattern:** `/cli/commands/{command}/{subcommand}/`

**Examples:**
- wp post create → https://developer.wordpress.org/cli/commands/post/create/
- wp user list → https://developer.wordpress.org/cli/commands/user/list/

## Plugin Handbook

**Base URL:** https://developer.wordpress.org/plugins/

## Theme Handbook

**Base URL:** https://developer.wordpress.org/themes/
```

**Estimated:** 75 lines

---

### 6. Documentation Categories Map (`docs-categories.md`)

**Coverage:** High-level map of documentation surfaces

#### Docs Categories (`docs-categories.md`)
**Format:**
```markdown
# WordPress Documentation Categories

Overview of documentation surfaces and when to use each.

## Reference Documentation
**URL:** https://developer.wordpress.org/reference/  
**When to use:** Looking up PHP functions, classes, hooks  
**Coverage:** Complete WordPress core API reference

## REST API
**URL:** https://developer.wordpress.org/rest-api/  
**When to use:** Building REST endpoints, consuming WordPress API  
**Coverage:** REST API guides and endpoint reference

## Block Editor
**URL:** https://developer.wordpress.org/block-editor/  
**When to use:** Block development, Gutenberg packages, Interactivity API  
**Coverage:** Block API, packages, components, how-to guides

## WP-CLI
**URL:** https://developer.wordpress.org/cli/commands/  
**When to use:** Command-line operations, automation  
**Coverage:** Complete CLI command reference

## Plugin Handbook
**URL:** https://developer.wordpress.org/plugins/  
**When to use:** Plugin development fundamentals  
**Coverage:** Plugin architecture, security, WordPress.org guidelines

## Theme Handbook
**URL:** https://developer.wordpress.org/themes/  
**When to use:** Theme development (classic and block themes)  
**Coverage:** Templates, theme.json, patterns, styling

## Advanced Administration
**URL:** https://developer.wordpress.org/advanced-administration/  
**When to use:** Server config, debugging, multisite  
**Coverage:** System administration topics
```

**Estimated:** 75 lines

---

## SKILL.md Procedure

```markdown
---
name: wp-docs
description: Quick lookup for WordPress documentation URLs
compatibility: WordPress 4.0+ documentation
---

## When to use

- User asks "where's the documentation for X?"
- Need canonical URL for a function, hook, class, or API
- Looking up REST endpoint or CLI command reference
- Finding Block Editor package or component docs

## Inputs required

- **Item name**: Function name, hook name, class name, etc.
- **Item type** (optional): If known (function, hook, class, REST, CLI)

## Procedure

1. **Identify the type**:
   - PHP function? (has parentheses or underscores, lowercase)
   - Hook? (context: "filter", "action", or common hook name)
   - Class? (starts with WP_, capitalized)
   - REST endpoint? (context: "REST", "API", endpoint name)
   - CLI command? (starts with "wp ", context: "command line")
   - Block/Package? (context: "@wordpress/", "block", "component")

2. **Check relevant index**:
   - Functions → `references/functions-essential.md`
   - Hooks → `references/hooks-essential.md`
   - Classes → `references/classes-essential.md`
   - REST → `references/rest-endpoints.md`
   - CLI → `references/cli-commands.md`
   - Block Editor → `references/block-editor-apis.md`

3. **If found in index**:
   - Return the canonical documentation URL
   - Done

4. **If NOT found in index**:
   - Use `references/search-patterns.md` to construct the URL
   - Apply transform rules for the item type
   - Return the constructed URL

5. **If uncertain about type**:
   - Check multiple indexes
   - Or consult `references/docs-categories.md` for guidance

## Output format

Return the canonical documentation URL with a brief description:

```
Documentation for `{item}`:
https://developer.wordpress.org/reference/functions/{item}/

{Brief one-line description if available}
```

## Verification

- URL follows WordPress.org documentation structure
- URL is correctly formatted (lowercase, hyphens, trailing slash)

## Failure modes / debugging

**Problem:** Function name has different casing  
**Solution:** Convert to lowercase, replace underscores with hyphens

**Problem:** Class name transformation unclear  
**Solution:** WP_Query → wp_query → wp-query (lowercase + hyphenate)

**Problem:** Hook name with dynamic parts  
**Solution:** Search for base hook name, note dynamic parts in output

**Problem:** Item might be deprecated  
**Solution:** URL pattern still works; official docs will note deprecation

## Escalation

- If WordPress.org documentation structure has changed significantly
- If item genuinely doesn't exist in WordPress core
- If user needs comprehensive tutorial vs. quick API reference
```

---

## Missing Documentation Resources

Based on sitemap analysis, these areas have limited or emerging documentation:

### 1. **Abilities API** ⚠️
**Status:** Recently added, documentation still evolving  
**URLs:** https://developer.wordpress.org/abilities-api/  
**Action:** Monitor for documentation expansion, add to indexes when stable

### 2. **Secure Custom Fields (SCF)** ⚠️
**Status:** Appears in sitemap but may be experimental  
**URLs:** https://developer.wordpress.org/scf/  
**Action:** Verify if this is production-ready before adding to indexes

### 3. **AI/Connectors** 🆕
**Status:** Very new, documentation evolving  
**Coverage:** AI client library, connector management  
**Action:** Wait for stable documentation before indexing

### 4. **Font Library API** 🆕
**Status:** New API, limited standalone docs  
**Coverage:** WP_Font_Collection, WP_Font_Library classes  
**Action:** Document in classes-essential.md when usage increases

### 5. **Block Bindings API** 🆕
**Status:** Recent addition to Block Editor  
**Coverage:** Limited guides available  
**Action:** Add to block-editor-apis.md as documentation expands

### 6. **HTML API** 🆕
**Status:** New processor API  
**Coverage:** WP_HTML_Processor classes  
**Action:** Monitor adoption, add when usage increases

### 7. **Interactivity API** 🔄
**Status:** Rapidly expanding, documentation catching up  
**Coverage:** Good reference docs, need more examples  
**Action:** Already planned for block-editor-apis.md

### 8. **Plugin Dependencies API** 🆕
**Status:** New plugin dependency checking  
**Coverage:** Limited documentation  
**Action:** Add when WordPress.org plugin guidelines reference it

---

## Implementation Steps

### Phase 1: Core Structure (Day 1)

1. ✅ Create skill directory structure
2. ✅ Write SKILL.md with routing logic
3. ✅ Create search-patterns.md with URL construction rules
4. ✅ Create docs-categories.md with documentation surface map

**Deliverable:** Functional skill that can construct any documentation URL

---

### Phase 2: Essential Indexes (Day 2-3)

5. ✅ Create functions-essential.md (top 100 functions)
   - **Source:** WordPress.org reference + usage analysis
   - **Priority:** Post, User, Options, Meta, Scripts, i18n, Security
   
6. ✅ Create hooks-essential.md (top 100 hooks)
   - **Source:** WordPress.org reference + plugin analysis
   - **Priority:** Init, Admin, Content, Scripts, User, REST
   
7. ✅ Create classes-essential.md (top 50 classes)
   - **Source:** WordPress.org reference
   - **Priority:** Query classes, wpdb, WP_Error, REST, Blocks

**Deliverable:** 80% of common lookups covered by direct index

---

### Phase 3: API Surface Indexes (Day 4)

8. ✅ Create rest-endpoints.md
   - **Source:** REST API reference sitemap
   - All core endpoints + key guides
   
9. ✅ Create cli-commands.md
   - **Source:** WP-CLI commands sitemap
   - Top 50 most-used commands
   
10. ✅ Create block-editor-apis.md
    - **Source:** Block Editor reference sitemap
    - Block API, packages, Interactivity API

**Deliverable:** Complete coverage of all major documentation surfaces

---

### Phase 4: Validation & Testing (Day 5)

11. ✅ Create validation script (scripts/validate-links.mjs)
    - Test all URLs return 200 OK
    - Check URL format correctness
    - Validate transform rules
    
12. ✅ Test with common queries:
    - "Where's the documentation for get_post?"
    - "Link to WP_Query docs"
    - "REST API posts endpoint"
    - "wp post create command"
    
13. ✅ Test edge cases:
    - Deprecated functions
    - Dynamic hook names
    - Class method lookups

**Deliverable:** Validated, tested skill ready for use

---

### Phase 5: Integration & Documentation (Day 6)

14. ✅ Add skill to wordpress-router decision tree
15. ✅ Create evaluation scenarios (eval/scenarios/docs-lookup.json)
16. ✅ Update main README.md with wp-docs skill
17. ✅ Build and test distribution packages

**Deliverable:** Integrated skill in repository

---

## Evaluation Scenarios

Create test scenarios in `eval/scenarios/docs-lookup.json`:

```json
{
  "name": "Documentation lookup - common function",
  "skills": ["wp-docs"],
  "query": "Where's the documentation for wp_insert_post?",
  "expected_behavior": [
    "Identify as function lookup",
    "Check functions-essential.md",
    "Return https://developer.wordpress.org/reference/functions/wp-insert-post/"
  ],
  "success_criteria": [
    "Correct URL returned",
    "URL format validated",
    "Response time < 1 second"
  ]
}
```

Additional test cases:
- Hook lookup (init, save_post)
- Class lookup (WP_Query, WP_REST_Request)
- REST endpoint (posts, users)
- CLI command (wp post create)
- Block Editor package (@wordpress/blocks)
- Pattern-matched item not in index

---

## C4WP (Claude for WordPress) Integration

### Research Needed

**Action:** Investigate existing C4WP documentation agent to:

1. ✅ Identify overlapping functionality
2. ✅ Extract proven patterns
3. ✅ Determine if C4WP agent should be:
   - Replaced by this skill
   - Used alongside this skill
   - Integrated with this skill

**C4WP Agent Location:** [Need to locate in WordPress/claude-for-wordpress or similar]

**Questions to Answer:**
- Does C4WP have a docs lookup mechanism?
- What format does it use?
- Can we reuse any of its indexes?
- Should wp-docs skill reference C4WP or replace it?

**TODO:** Conduct C4WP repository analysis (separate task)

---

## Maintenance Plan

### Upstream Sync Automation

**High Priority (Monthly):**
- Validate all index URLs still resolve correctly
- Check for new "essential" functions/hooks in WordPress releases
- Update Block Editor APIs with new packages

**Medium Priority (Quarterly):**
- Review REST API for new endpoints
- Update WP-CLI commands from official docs
- Add newly stable APIs (Abilities, Font Library, etc.)

**Low Priority (Annually):**
- Review and refresh "top N" selections based on usage
- Remove deprecated items that no longer resolve

### Automation Script

Extend `scripts/validate-links.mjs` to:
1. Check all URLs in index files
2. Report 404s or redirects
3. Suggest additions based on WordPress changelog
4. Flag new API documentation surfaces

---

## Success Metrics

### Quantitative
- **Skill size:** < 20KB total
- **Lookup coverage:** 80%+ of queries hit index (not pattern match)
- **Response time:** < 1 second for any lookup
- **URL accuracy:** 95%+ success rate (valid URLs)

### Qualitative
- AI can find documentation for any WordPress core API
- Minimal maintenance burden (just URL updates)
- Fast enough for interactive use
- Follows repository patterns and conventions

---

## Future Enhancements

### v1.1 - Method Lookups
Add support for class method lookups:
- `WP_Query::get_posts()` → class method documentation

### v1.2 - Code Examples
Link to WordPress Code Reference examples:
- https://developer.wordpress.org/reference/functions/get_post/#examples

### v1.3 - Version Context
Add "available since" information for version-aware routing:
- "This function requires WordPress 6.5+"

### v1.4 - Related Documentation
Suggest related documentation:
- "See also: wp_update_post(), wp_delete_post()"

### v2.0 - Smart Search
Fuzzy matching and typo correction:
- "wp_inser_post" → "Did you mean wp_insert_post?"

---

## Dependencies

### Required Tools
- Node.js 20+ (for validation script)
- Bash (for detection if needed)

### Skill Dependencies
- `wordpress-router`: Routes to wp-docs skill
- `wp-project-triage`: May inform which docs are relevant

### No External Dependencies
- No network calls during lookup (all URLs are static patterns)
- No API keys or authentication needed

---

## Open Questions

1. **C4WP Integration:** How does this relate to existing Claude for WordPress documentation agent?
2. **Versioning:** Should we include WordPress version context in URLs?
3. **Localization:** Should we support non-English documentation URLs?
4. **Code Examples:** Should indexes link to code examples tab?
5. **Search Fallback:** Should we suggest WordPress.org search when pattern matching fails?

---

## Appendix: Top Function Candidates

Based on master documentation analysis, these functions should be in functions-essential.md:

**Post/Page Functions:**
- get_post, get_posts, wp_insert_post, wp_update_post, wp_delete_post
- get_the_ID, the_title, the_content, the_excerpt, the_permalink
- register_post_type, get_post_type

**User Functions:**
- wp_get_current_user, get_user_by, get_userdata
- wp_insert_user, wp_update_user, wp_delete_user
- current_user_can, user_can

**Options Functions:**
- get_option, update_option, add_option, delete_option
- get_site_option, update_site_option

**Meta Functions:**
- get_post_meta, update_post_meta, add_post_meta, delete_post_meta
- get_user_meta, update_user_meta, get_term_meta

**Taxonomy/Term Functions:**
- get_term, get_terms, wp_insert_term, wp_update_term
- wp_set_post_terms, get_the_terms

**Scripts/Styles:**
- wp_enqueue_script, wp_register_script, wp_enqueue_style, wp_register_style
- wp_localize_script, wp_add_inline_script

**i18n Functions:**
- __, _e, _n, _x, _ex, _nx
- load_textdomain, load_plugin_textdomain, load_theme_textdomain

**Security/Sanitization:**
- sanitize_text_field, sanitize_title, sanitize_email
- esc_html, esc_attr, esc_url, esc_js
- wp_verify_nonce, check_admin_referer

**Hooks:**
- add_action, remove_action, do_action
- add_filter, remove_filter, apply_filters

**HTTP:**
- wp_remote_get, wp_remote_post, wp_remote_request

**Cron:**
- wp_schedule_event, wp_next_scheduled, wp_clear_scheduled_hook

**Database:**
- $wpdb methods (via WP_Query class)

---

## Next Steps

1. **Review this plan** - Get feedback on approach
2. **Conduct C4WP analysis** - Research existing Claude for WordPress docs agent
3. **Begin Phase 1** - Create core skill structure
4. **Iterate** - Build incrementally, test each phase

---

**Plan Status:** 📋 DRAFT - Ready for Review  
**Estimated Effort:** 6 days (incremental implementation)  
**Risk Level:** Low (simple URL mapping, no complex logic)  
**Maintenance:** Low (periodic URL validation only)
