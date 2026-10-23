# Icons (Material Symbols Rounded)

Public-site icons are inline SVG Blade components that come from Google's **Material Symbols Rounded** set (weight 400, 24px grid). There is no icon font, so a page only carries the icons it renders. Don't use emoji as UI icons.

## Adding icons

```powershell
php artisan icon:add arrow_forward check-circle      # snake_case or kebab-case
php artisan icon:add home --force                    # overwrite an existing component
```

The command downloads the outlined (`default`) and filled (`fill1`) variants from
`https://fonts.gstatic.com/s/i/short-term/release/materialsymbolsrounded/{name}/{variant}/24px.svg`
and writes `resources/views/components/icon/{kebab-name}.blade.php`. Browse names at https://fonts.google.com/icons?icon.style=Rounded.

- If an icon has no filled variant (an upstream 404), the outlined path is reused.
- Existing files are skipped unless `--force` is passed.
- `index` is reserved for the dynamic wrapper.
- Generated files are committed; don't hand-edit them. Re-run with `--force` instead.

## Usage

```blade
<x-icon.arrow-forward class="size-4" />                 {{-- outlined --}}
<x-icon.verified-user filled class="size-5 text-brand" /> {{-- filled --}}
<x-icon :name="$item['icon']" filled class="size-5" />   {{-- name from a data array --}}
```

- Icons use `currentColor`, default to `size-6 shrink-0`, and are `aria-hidden`. Keep the accessible text next to them.
- `x-site.button` accepts `icon` (leading), `iconFilled`, and `iconTrailing` props. Trailing icons get the `icon-nudge` hover effect from `public/css/site.css`; use `icon-nudge--down` or `icon-nudge--outward` for ↓ and ↗ arrows.
- Convention: filled for feature tiles (trust strip, process steps, badges), outlined for inline/text icons and arrows.
- `x-site.icon` is the separate hand-drawn stroke set used by the About value cards, and `<flux:icon>` belongs to the admin/auth UI.
