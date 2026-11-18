# Smart Media Markup tests

`fixtures/` holds one `*.input.html` / `*.expected.html` pair for each change.
The input is a block's rendered HTML; the expected file is what visitors get
back. The site URL is `https://mysite.test`.

| Fixture                    | Block   | Shows                                                          |
| -------------------------- | ------- | -------------------------------------------------------------- |
| `decoding-async`           | Image   | 1. `decoding="async"` is added                                 |
| `decoding-existing`        | Image   | 1. An existing `decoding="sync"` is kept                       |
| `outer-class-gallery`      | Gallery | 2. `has-smart-media` goes on the outer figure only             |
| `cover`                    | Cover   | 1–3. Wrapper class, background image decoding, external link   |
| `external-links`           | Image   | 3. `noopener` added to external links; existing `rel` kept; internal, `www.`, relative and `mailto:` links untouched |
| `caption-alt`              | Image   | 4. Toggle on + empty alt: caption plain text becomes alt       |
| `caption-alt-existing-alt` | Image   | 4. Toggle on + existing alt: alt is kept                       |
| `caption-alt-disabled`     | Image   | 4. Toggle off: alt stays empty                                 |

The Cover block's outer wrapper is a `<div>`, not a `<figure>`, so the class
goes on that `<div>`.

## Running

The tests use the WordPress core PHPUnit suite (for example via
`wp-env run tests-cli` or `bin/install-wp-tests.sh`):

```
WP_TESTS_DIR=/path/to/wordpress-tests-lib vendor/bin/phpunit
```
