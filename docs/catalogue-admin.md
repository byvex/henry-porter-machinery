# Catalogue administration

Open `/admin` after signing in as a verified administrator. Products, categories and brands have searchable lists and create/edit forms. Ordinary accounts cannot manage the catalogue or upload images.

## Administrator accounts

Run `php artisan catalogue:admin` and follow the email, name and password prompts. For an existing account, `php artisan catalogue:admin person@example.com` grants the admin role without changing its password. The console command also marks the account verified. No default administrator or password is seeded. Public registration always creates a regular user.

## Media

The public storage disk uses `products/YYYY/MM-DD/{uuid}/`, `categories/YYYY/MM-DD/{uuid}/` and `brands/YYYY/MM-DD/{uuid}/`. The date comes from record creation, with a persisted fallback for missing Joomla dates. Later uploads keep the original folder. Shared source assets stay in one stable owner folder; unattached legacy assets use `legacy/YYYY/MM-DD/`.

Image filenames contain a readable original name and a UUIDv7 suffix, capped at 100 characters. Original dimensions, aspect ratio and transparency are preserved. Each brand has a logo and an independent optional card photo; categories have one image. Galleries support ordering and alt text; the first image is the cover.

Uploads accept JPEG, PNG, WebP, GIF and AVIF up to 10 MB each, with at most 20 new product images per save. Configure PHP `upload_max_filesize` to at least 10M and `post_max_size` high enough for the complete request (220M for twenty maximum-size files), together with the web server request limit. Custom browser-ready CSS is in `public/css/admin.css`; Tailwind classes still require an asset build.

Removing or replacing an image removes unreferenced user-uploaded files from storage and deletes their media record. Shared or legacy-imported assets (`catalogue/joomla/`) and images referenced in descriptions or by other records remain preserved. Orphaned files or unreferenced records can be audited and purged via `php artisan catalogue:prune-orphaned-media` (with `--apply`). Product deletion is recoverable through the deleted-products filter. Brand/category deletion is blocked while referenced; deactivate these instead. Hidden template/content records remain excluded from public catalogue queries even if their publication field is changed.

## Media Library & Image Picker

Access `/admin/media` via the sidebar under **Catalogue &rarr; Media Library**.
- **Media Browsing & Filters**: Visual thumbnail grid with pagination (36 per page), search by title/alt/path, filter by source (WhatsApp/local, admin upload, remote web, legacy Joomla), attachment state (attached anywhere, products, brands/categories, unattached/orphaned), brand, and product.
- **Attachment Details Modal**: Clicking any image opens an inspector modal with full-size preview, technical metadata (storage path, MIME, file size, SHA256 checksum), editable title and alt text, and a live "Where this image is used" panel listing attached products (with Cover vs Gallery badges and quick detachment), brands, and categories.
- **Bulk Actions**: Multi-select images to perform bulk detachment from products or one-step permanent deletion (detaches and deletes from disk and database; protected legacy files and HTML-embedded images are preserved).
- **Direct Uploads**: Upload standalone media directly into `media/YYYY/MM-DD/{uuid}/` from `/admin/media`.
- **Choose from Media Library**: Product, brand, and category edit screens include a **Choose from Media Library** picker modal, allowing existing images to be linked to product galleries, brand logos/photos, or category images without re-uploading duplicate files.

Product, category and brand descriptions include a Tiptap visual editor with a **Visual / HTML** mode toggle. Existing imported HTML (including specification tables, cell spans and embedded `/storage/...` images) remains untouched until edited, and all saved markup continues to pass through `CatalogueHtml` server-side sanitisation.

## Import compatibility

UUIDv7 identifies Laravel application records; source IDs remain separate integers. Infrastructure keys retain their framework formats. These migrations target the agreed local database rebuild; they are not an in-place conversion of an existing deployed integer-key database.

An admin save sets `admin_modified_at`. Ordinary reimports preserve edited or deleted aggregates, their children and referenced media, and list skips in the private report. `--overwrite-edited` explicitly restores source-owned values and associations. Newly introduced fields without Joomla equivalents, such as brand card photos, remain locally managed.

Old flat files can be checked using `php artisan catalogue:prune-legacy-media <private-manifest.json>`. Default execution deletes nothing. `--apply` removes only manifest-listed old `catalogue/joomla` files whose checksums match and which have no database/HTML references. Never remove unrelated public storage or the Joomla source backup.

Homepage/About content remains static. Public catalogue destinations and legacy redirects remain pending.
