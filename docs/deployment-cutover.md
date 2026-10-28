# Production Deployment & Cutover Guide

This guide details the end-to-end procedure for deploying the Henry Porter Machinery Laravel site to production and executing the cutover from legacy Joomla.

---

## 1. Production Server Prerequisites

- **PHP**: 8.3 or 8.4
- **Required Extensions**: `pdo_mysql`, `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd` or `imagick`, `intl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `zip`
- **Node.js**: v20+ and `pnpm` (if compiling assets on the host, otherwise build before deploying)
- **Web Server**: Nginx or Apache
- **Process Manager**: Supervisor or systemd for the background queue worker

---

## 2. Environment Configuration (`.env`)

Set the following critical environment variables on the production server:

```dotenv
APP_NAME="Henry Porter Machinery"
APP_ENV=production
APP_KEY=base64:... # Retain the production application key; NEVER regenerate on an active site
APP_DEBUG=false
APP_URL=https://henryportermachinery.co.uk

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hpm_production
DB_USERNAME=hpm_user
DB_PASSWORD=your_strong_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

QUEUE_CONNECTION=database

# Mail & Enquiry Notifications
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@henryportermachinery.co.uk"
MAIL_FROM_NAME="${APP_NAME}"

# Enable live enquiry email notifications
ENQUIRY_NOTIFICATIONS_ENABLED=true
ENQUIRY_RECIPIENT=info@henryportermachinery.co.uk
```

---

## 3. Web Server Configuration

### Important URL Handling Rules
- **Do not prematurely redirect `.html` or query URLs with trailing slashes.**
- Legacy Joomla paths (e.g. `/about.html`, `/index.php?...`, Virtuemart URLs) must reach Laravel so the redirect middleware can match them against `legacy-sitemap.json` and permanent mappings.

### Nginx Virtual Host Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name henryportermachinery.co.uk www.henryportermachinery.co.uk;
    return 301 https://henryportermachinery.co.uk$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name www.henryportermachinery.co.uk;

    ssl_certificate /etc/letsencrypt/live/henryportermachinery.co.uk/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/henryportermachinery.co.uk/privkey.pem;

    return 301 https://henryportermachinery.co.uk$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name henryportermachinery.co.uk;
    root /var/www/hpm/public;

    ssl_certificate /etc/letsencrypt/live/henryportermachinery.co.uk/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/henryportermachinery.co.uk/privkey.pem;

    index index.php;
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Supervisor Queue Worker (for Enquiry Emails)

Create `/etc/supervisor/conf.d/hpm-worker.conf`:

```ini
[program:hpm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hpm/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hpm/storage/logs/worker.log
stopwaitsecs=3600
```

Update supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 5. Deployment & Cutover Execution Steps

Run these commands in order on the server:

```bash
# 1. Pull latest code & install composer dependencies
git pull origin main
composer install --no-dev --optimize-autoloader

# 2. Build frontend assets (or deploy pre-built public/build)
pnpm install --frozen-lockfile
pnpm run build

# 3. Run database migrations (NEVER run migrate:fresh in production)
php artisan migrate --force

# 4. Link public storage
php artisan storage:link

# 5. Import Catalogue from Joomla source backup (if not already completed)
php artisan catalogue:import-joomla /private/backups/hpm-joomla --apply

# 6. Copy legacy page assets (news, parts & media cache)
php artisan site:copy-legacy-assets /private/backups/hpm-joomla --apply

# 7. Verify legacy URL redirects and build verification report
php artisan catalogue:verify-legacy-urls --apply

# 8. Create admin user (if not already provisioned)
php artisan catalogue:admin

# 9. Cache configuration, routes, and views for optimal performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Restart queue workers to pick up new code
php artisan queue:restart
```

---

## 6. Post-Cutover Verification Checklist

- [ ] **SSL & Canonical Host**: Verify visiting `http://henryportermachinery.co.uk`, `http://www.henryportermachinery.co.uk`, and `https://www.henryportermachinery.co.uk` 301-redirect to `https://henryportermachinery.co.uk/`.
- [ ] **Legacy URLs**: Test key legacy paths such as `/about.html`, Virtuemart category paths, and news items to confirm 301 redirects to their new routes. Inspect `storage/app/private/legacy-urls/report.json`.
- [ ] **Sitemap & Robots**: Verify `https://henryportermachinery.co.uk/sitemap.xml` returns valid XML and `https://henryportermachinery.co.uk/robots.txt` points to the canonical sitemap.
- [ ] **Enquiry Form Submission**: Submit a test machinery enquiry from a product page. Verify it appears under `/admin/enquiries` and an email notification is dispatched to `ENQUIRY_RECIPIENT`.
- [ ] **Rich Results & Social Previews**:
  - Test the homepage on [Google Rich Results Test](https://search.google.com/test/rich-results) for `LocalBusiness`.
  - Test a machinery product URL for `Product` and `BreadcrumbList` schemas.
- [ ] **Google Search Console**: Submit `https://henryportermachinery.co.uk/sitemap.xml` for crawling.
