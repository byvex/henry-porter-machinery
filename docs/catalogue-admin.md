# Catalogue administration

Open `/admin` after signing in as a verified administrator. Products, categories and brands have searchable lists and create/edit forms. Ordinary accounts cannot manage the catalogue or upload images.

## Administrator accounts

Run `php artisan catalogue:admin` and follow the email, name and password prompts. For an existing account, `php artisan catalogue:admin person@example.com` grants the admin role without changing its password. The console command also marks the account verified. No default administrator or password is seeded. Public registration always creates a regular user.

## Media

The public storage disk uses `products/YYYY/MM-DD/{uuid}/`, `categories/YYYY/MM-DD/{uuid}/` and `brands/YYYY/MM-DD/{uuid}/`. The date comes from record creation, with a persisted fallback for missing Joomla dates. Later uploads keep the original folder. Shared source assets stay in one stable owner folder; unattached legacy assets use `legacy/YYYY/MM-DD/`.

Image filenames contain a readable original name and a UUIDv7 suffix, capped at 100 characters. Original dimensions, aspect ratio and transparency are preserved. Each brand has a logo and an independent optional card photo; categories have one image. Galleries support ordering and alt text; the first image is the cover.

Uploads accept JPEG, PNG, WebP, GIF and AVIF up to 10 MB each, with at most 20 new product images per save. Configure PHP `upload_max_filesize` to at least 10M and `post_max_size` high enough for the complete request (220M for twenty maximum-size files), together with the web server request limit. Custom browser-ready CSS is in `public/css/admin.css`; Tailwind classes still require an asset build.

Removing or replacing an image detaches it without deleting shared files. Product deletion is recoverable through the deleted-products filter. Brand/category deletion is blocked while referenced; deactivate these instead. Hidden template/content records remain excluded from public catalogue queries even if their publication field is changed.

## Import compatibility

UUIDv7 identifies Laravel application records; source IDs remain separate integers. Infrastructure keys retain their framework formats. These migrations target the agreed local database rebuild; they are not an in-place conversion of an existing deployed integer-key database.

An admin save sets `admin_modified_at`. Ordinary reimports preserve edited or deleted aggregates, their children and referenced media, and list skips in the private report. `--overwrite-edited` explicitly restores source-owned values and associations. Newly introduced fields without Joomla equivalents, such as brand card photos, remain locally managed.

Old flat files can be checked using `php artisan catalogue:prune-legacy-media <private-manifest.json>`. Default execution deletes nothing. `--apply` removes only manifest-listed old `catalogue/joomla` files whose checksums match and which have no database/HTML references. Never remove unrelated public storage or the Joomla source backup.

Homepage/About content remains static. Public catalogue destinations and legacy redirects remain pending.
