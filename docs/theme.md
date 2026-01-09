# Crazy Systems Dark Theme

## Theme tokens
Theme tokens live in `public/lb-faveo/css/crazy-theme.css` under the `:root` selector.
Key tokens to adjust:

- `--cs-bg`, `--cs-surface`, `--cs-surface-alt`, `--cs-surface-elevated`: base backgrounds.
- `--cs-text`, `--cs-text-muted`: primary text colors.
- `--cs-primary`, `--cs-primary-strong`, `--cs-secondary`: brand accents.
- `--cs-border`, `--cs-shadow-sm`, `--cs-shadow-lg`: borders/shadows.
- `--cs-radius-*`: global radius.

## Logos & favicon
- Favicon: `public/lb-faveo/css/crazy-favicon.svg`.
- Hero image: `public/lb-faveo/css/crazy-hero.svg`.
- To swap assets, replace the SVG files or update the `--cs-hero-image` token in `public/lb-faveo/css/crazy-theme.css`.

## Parallax hero
The landing page hero uses the `.cs-hero` component on `resources/views/themes/default1/client/helpdesk/guest-user/index.blade.php`.

- Parallax script: `public/lb-faveo/js/crazy-parallax.js`.
- The background image is controlled by `--cs-hero-image` (default points to `crazy-hero.svg`).
- To adjust speed, change `data-parallax-speed` on the `.cs-hero--parallax` element.
- Reduced motion: when `prefers-reduced-motion: reduce` is enabled, the script disables motion and keeps the hero static.

## Layout entry points
The dark theme stylesheet is loaded in the main layouts:

- Client portal: `resources/views/themes/default1/client/layout/client.blade.php`
- Client login: `resources/views/themes/default1/client/layout/logclient.blade.php`
- Admin backend: `resources/views/themes/default1/admin/layout/admin.blade.php`
- Agent backend: `resources/views/themes/default1/agent/layout/agent.blade.php`
