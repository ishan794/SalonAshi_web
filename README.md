# SalonCMS

> A full-featured salon appointment booking, billing, and customer management platform — built on CodeIgniter 4, Tailwind CSS, and Alpine.js.

**Live demo:** [https://saloncms.livezencloud.com](https://saloncms.livezencloud.com)
**Repo:** [github.com/Livezen-Technologies/saloncms](https://github.com/Livezen-Technologies/saloncms)

---

## Table of contents

1. [What it does](#what-it-does)
2. [Feature list (by module)](#feature-list-by-module)
3. [Tech stack](#tech-stack)
4. [Setup guide](#setup-guide)
   - [Local development](#local-development)
   - [Production deployment](#production-deployment)
5. [Configuration guide](#configuration-guide)
6. [User guide — admin](#user-guide--admin)
7. [User guide — customers](#user-guide--customers)
8. [Module / file structure](#module--file-structure)
9. [Database schema](#database-schema)
10. [Deployment workflow](#deployment-workflow)
11. [Troubleshooting](#troubleshooting)
12. [License & credits](#license--credits)

---

## What it does

SalonCMS is a self-hosted, owner-operated platform for hair / beauty / spa salons. Your customers book themselves online; your staff manage the day from a unified admin; you collect payments, track loyalty, and grow through reviews.

**Three audiences, one codebase:**

- 🌐 **Public website** — your brand-on-the-internet: home, services, team, booking wizard, contact, terms/privacy/refund pages. Multi-language (English / Sinhala / Tamil), dark mode, gold theme, SEO-friendly.
- 💼 **Admin dashboard** — for owners and staff: calendar, POS, billing, customer history, reports, settings.
- 👤 **Customer portal** — passwordless OTP login where customers see their upcoming bookings, treatment history, invoices, payment history, and loyalty points.

---

## Feature list (by module)

### Public site (Frontend)
- Home page with hero, services preview, team grid, latest reviews, CTA
- Services catalogue with category grouping and per-service booking links
- **4-step booking wizard:** select services → choose stylist → pick date & time → enter contact details
- Booking confirmation page with reference code, totals, "leave a review" prompt
- About, Team, Contact (with map + form), Terms, Privacy, Refund pages
- **Multi-language** (English / Sinhala / Tamil) — globe icon switcher in navbar, cookie-persistent
- **Dark mode** with system / light / dark options
- **Three layout styles** — wide, boxed (centered card with side gutters), centered (narrow)
- Configurable container width (max-w-5xl through max-w-full)
- **Google Map** on contact page (embed iframe OR auto-fallback from address — no API key needed)
- **WhatsApp floating chat widget** with optional welcome tooltip
- **SEO meta tags** per page (title / description / keywords / OG image / Twitter card / canonical / robots)
- Inter font for body, DM Serif Display for headings

### Booking & appointments
- Real-time slot availability based on stylist's schedule
- Per-stylist weekly schedule (Mon-Sun working windows)
- Time-off blocks (date-range vacations)
- Date-specific custom windows (override schedule for specific days, multiple windows per day)
- Booking statuses: pending → confirmed → checked-in → in-progress → completed
- Cancellation tracking with reasons + reliability score per customer
- No-show tracking
- Calendar widget on dashboard
- Per-stylist month calendar view

### POS (point-of-sale)
- Quick-book mode: customer + services + stylist + time slot → instant booking
- Quick-bill mode: select items → record payment → instant invoice
- Customer combobox with digit-only phone search (strips country codes)
- Date picker modal with time-slot grid
- Inline customer creation if not in DB

### Customers
- CRUD with searchable list (name / phone / email — digit-only phone match)
- Profile page with appointment history, invoices, loyalty card, reliability stats
- Cancellation / no-show counters with offender badge
- Notes, preferred stylist, gender, birthday, membership tier
- Phone country-code stripping (saves clean number, accepts any format)

### Staff
- CRUD with role, commission %, assigned services
- Weekly schedule editor (per-day open/close times)
- Time-off CRUD
- Per-date custom windows (override schedule for specific days)
- Month-view calendar of bookings

### Services
- CRUD with category grouping
- Duration, price, active toggle, description
- Category CRUD

### Billing
- Invoice CRUD with line items (services or ad-hoc items)
- Discount, tax, multiple payments per invoice
- Status tracking: draft / unpaid / partial / paid / void / cancelled
- **Actions:** print, PDF download (dompdf), email (SMTP), WhatsApp share link
- **Search filters:** by status, date range, customer, code
- Create-from-appointment flow (auto-populates from booking)
- **Loyalty redemption** at checkout

### Loyalty program
- Configurable earn rate (points per LKR spent)
- Configurable redeem rate (points → LKR off)
- Auto-tier upgrade (Bronze / Silver / Gold thresholds)
- Earn on payment recorded
- Redeem-points form on invoice (gated by available balance)
- Transaction log on customer profile

### Reports & analytics
- Date-range picker (today / 7 days / 30 days / this month / custom)
- **Overview** — KPIs + trend chart (Chart.js)
- **Sales** — revenue + payment method breakdown
- **Services** — top services by count + revenue
- **Staff** — performance per stylist
- CSV export per report

### Reviews & ratings
- In-app review submission from booking confirmation page (5-star + name + title + body)
- Moderation queue in admin (pending / approve / reject / feature)
- Auto-approve toggle in Settings
- **Google Business reviews import** — Place ID + Places API key in Settings → fetch button pulls reviews into your DB
- Mixed display on home page (in-app + Google reviews together)
- Aggregate rating + total count caching from Google

### SEO
- Per-page meta: home, services, book, about, team, contact, terms, privacy, refund
- Site-wide defaults + per-page overrides
- OG image upload (1200×630)
- Twitter handle, robots directive
- All translated tags emit correctly with selected locale

### Customer portal (self-service)
- OTP login: customer enters mobile → 6-digit code emailed → session
- Dashboard with KPIs (upcoming / past visits / invoices / loyalty points)
- Upcoming bookings with status badges + view links
- Last completed treatment with services + repeat-booking shortcut
- Booking history (last 8 visits)
- Invoice list + per-invoice detail page with line items + payment history
- Recent payments table
- Loyalty card (when loyalty enabled)
- Sign out

### Multi-language (i18n)
- English / Sinhala (සිංහල) / Tamil (தமிழ்) out of the box
- Locale resolution: `?lang=` query param → cookie → setting → default
- Locale switcher in navbar (globe dropdown) + mobile menu
- Locale persists for 1 year via cookie
- Per-language enable/disable + default language pickers in Settings
- All language files: `app/Language/{en,si,ta}/Site.php`

### Admin UI
- TailwindUI-style sidebar with **collapsible groups** (state persisted to localStorage)
- Group icons + visual hierarchy
- **Mobile bottom navigation bar** (Home / Calendar / POS / People / Menu)
- Frosted slide-in drawer for full menu on mobile
- Topbar with theme toggle + user menu
- Light / dark / system theme toggle
- Inter font, antialiased text
- Flash messages (success / error) with auto-dismiss styling
- Permission-aware navigation (items hide when user lacks permission)

### Permissions
- 4 default roles: super_admin, owner, manager, stylist
- Granular permission slugs (`customers.create`, `invoices.delete`, `settings.view`, etc.)
- Permissions assigned to roles via UI (Settings → Permissions matrix)
- Route-level enforcement via `perm:<slug>` filter
- 403 page for forbidden routes
- super_admin bypasses all checks
- UI buttons auto-hide when user lacks permission

### Communication
- SMTP for transactional email (booking confirmations, contact form, OTP, invoice emails)
- WhatsApp deep link generation for invoice share
- Contact form → SMTP to your business email
- Customer portal OTP via SMTP

---

## Tech stack

| Layer | Choice |
|---|---|
| Framework | [CodeIgniter 4.7](https://codeigniter.com) (PHP 8.1+) |
| CSS | [Tailwind CSS](https://tailwindcss.com) via CDN + forms/typography plugins |
| JS | [Alpine.js 3.x](https://alpinejs.dev) (no build step) |
| Icons | [Lucide](https://lucide.dev) (umd CDN) |
| Charts | [Chart.js](https://chartjs.org) (via CDN, lazy-loaded on reports) |
| Fonts | [Inter](https://rsms.me/inter/) + [DM Serif Display](https://fonts.google.com/specimen/DM+Serif+Display) (Google Fonts) |
| Database | MariaDB / MySQL 8 (`utf8mb4_unicode_ci`) |
| PDF | [dompdf](https://github.com/dompdf/dompdf) (composer) |
| Email | CI4 built-in mailer + your SMTP |
| Sessions | Filesystem (default CI4) |

**No build step required** — Tailwind CDN compiles classes at runtime. Just edit PHP/HTML and refresh.

---

## Setup guide

### Local development

**Prerequisites:**
- PHP 8.1+ (8.3 recommended) with `intl`, `mbstring`, `mysqli`, `gd`
- MAMP / XAMPP / Laragon (or just PHP + MySQL standalone)
- Composer
- MySQL 8 or MariaDB 10+

**Steps:**

```bash
# 1. Clone
git clone https://github.com/Livezen-Technologies/saloncms.git
cd saloncms

# 2. Install dependencies
composer install

# 3. Configure environment
cp env .env
# Edit .env — at minimum set:
#   app.baseURL = 'http://localhost:8181/'
#   database.default.hostname = 127.0.0.1
#   database.default.database = saloncms
#   database.default.username = root
#   database.default.password = root
#   database.default.port = 3306    # 8889 for MAMP
#   encryption.key = hex2bin:RUN_php_spark_key_generate_TO_GET_THIS

# 4. Generate an app key
php spark key:generate

# 5. Create database
mysql -u root -p -e "CREATE DATABASE saloncms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Import core schema
mysql -u root -p saloncms < app/Database/schema.sql

# 7. Import addon migrations (loyalty, permissions, cancellations, schedules, reviews, portal)
for f in app/Database/schema_addon_*.sql; do
  echo "Applying $f..."
  mysql -u root -p saloncms < "$f"
done

# 8. Seed admin user
mysql -u root -p saloncms < app/Database/seed_admin.sql 2>/dev/null || \
  echo "Seed file missing — register an owner via SQL or use the included default."

# 9. Run dev server
php spark serve --port 8181
```

Visit `http://localhost:8181` for the public site, `http://localhost:8181/login` for admin.

**Default admin credentials** (local only — change immediately in production):
- Email: `admin@saloncms.local`
- Password: `admin123`

### Production deployment

**Recommended stack:** Ubuntu 22.04 + nginx + PHP-FPM 8.3 + MariaDB 10.6 + Let's Encrypt SSL.

```bash
# 1. SSH to server, clone repo
ssh root@your-vps
cd /var/www
git clone https://github.com/Livezen-Technologies/saloncms.git
cd saloncms

# 2. Install deps
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp env .env
nano .env
# Set:
#   CI_ENVIRONMENT = production
#   app.baseURL = 'https://yoursalon.example.com/'
#   database.default.hostname = 127.0.0.1
#   database.default.database = saloncms
#   database.default.username = saloncms
#   database.default.password = <strong random pass>
#   encryption.key = hex2bin:...

php spark key:generate

# 4. DB setup (same as local — create DB, import schema + addons)

# 5. Permissions
chown -R www-data:www-data /var/www/saloncms
chmod -R 755 /var/www/saloncms
chmod -R 775 /var/www/saloncms/writable

# 6. nginx vhost (example)
cat > /etc/nginx/sites-available/saloncms <<'EOF'
server {
    listen 80;
    server_name yoursalon.example.com;
    root /var/www/saloncms/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    client_max_body_size 20M;
}
EOF
ln -s /etc/nginx/sites-available/saloncms /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# 7. SSL with Let's Encrypt
certbot --nginx -d yoursalon.example.com
```

**Notes:**
- `app.baseURL` *must* end with a trailing slash
- For MariaDB, prefer `127.0.0.1` over `localhost` (MariaDB socket auth quirk)
- CI4's `.env` parser is strict — keep values single-quoted, avoid backslash escaping
- `writable/` must be writable by the web user — that's where uploads, cache, session, and logs live

---

## Configuration guide

All configuration lives in **Admin → Settings**. The sidebar has these tabs:

### General
Salon name, currency code/symbol, default tax rate, invoice/booking code prefixes, time zone.

### Business & Logo
Address, phone, email, registration #, tax ID, working hours, social URLs, **logo upload**, **favicon upload**, **Location map** (Google Maps embed or address-based fallback), **WhatsApp widget** (number + position + pre-fill message + tooltip).

### SMTP / Email
Host, port, encryption (TLS/SSL), username, password, from-email, from-name. **Test button** sends a real email to verify configuration.

### Cron
Lists scheduled tasks + shows the crontab line to add (e.g., `* * * * * php /var/www/saloncms/spark cron:run`).

### Loyalty
Enable/disable, earn rate (points per LKR), redeem rate (LKR per point), silver/gold tier thresholds.

### Public site
Layout style (wide / boxed / centered), content max-width, default language, enabled languages.

### Pages
Edit content for About / Terms / Privacy / Refund pages. Title + body (HTML allowed). Leave blank for sensible defaults.

### SEO
Defaults (title, description, keywords, robots, OG image, Twitter handle) plus per-page overrides for all 9 public pages.

### Integrations
**Google Business reviews:** Place ID + Places API key → "Import reviews now" button pulls latest into your DB.
**In-app reviews:** auto-approve toggle.

### Users / Roles / Permissions
Manage staff login accounts, role definitions, and per-role permission matrix.

---

## User guide — admin

### First-time setup checklist
1. **Sign in** → Settings → **General** → set salon name, currency, time zone
2. **Business & Logo** → upload your logo, fill address/phone/email/hours
3. **SMTP** → connect your email provider, send a test
4. **Services** → add your service categories then services (name, duration, price)
5. **Staff** → add stylists, set weekly schedule, assign services they perform
6. **Public site** → pick layout style + container width, choose default language
7. **SEO** → set site-wide title/description + per-page overrides for top traffic pages
8. **WhatsApp** → toggle on, enter your number → instant chat widget on every public page

### Daily workflow
- **Dashboard** → see today's bookings at a glance, calendar widget
- **Appointments** → day/week calendar, click any slot to confirm/check-in/complete
- **POS** → fast walk-in book+bill: customer combobox → service → stylist → slot → done
- **Billing → Invoices** → create from appointment, record payment, share via WhatsApp
- **Customers** → search by name/phone/email, see history + loyalty
- **Reports** → daily revenue, top services, staff performance
- **Reviews** → moderate pending submissions, feature your best ones

### Permission roles
- **super_admin** — all permissions, never gated, can manage other users/roles
- **owner** — full operational + reports + settings (but can't override permissions)
- **manager** — full operational + reports (no settings)
- **stylist** — sees only their own calendar + customers; can't manage other staff

Customize roles in **Settings → Permissions** (checkbox matrix per role × per permission).

---

## User guide — customers

### Booking online
1. Visit `https://yoursalon.example.com`
2. Click **Book** in nav
3. Select services → choose a stylist → pick a date and an available time slot → enter name + mobile + (optional) email
4. Receive booking reference code + confirmation page
5. Email confirmation (if salon has SMTP configured)

### My account (Customer portal)
1. Click **My account** in nav / footer
2. Enter the mobile number used for previous bookings
3. Receive a 6-digit code via email (must have an email on file)
4. Enter the code → land on dashboard

The dashboard shows:
- Upcoming bookings (with view links to confirmation pages)
- Your last completed treatment with a "book again" shortcut
- Booking history (last 8)
- All invoices with status badges (click for line-item detail)
- Recent payments
- Loyalty points + tier (if loyalty enabled)

### Leaving a review
- After visiting, customers see a "Leave a review" prompt on their booking confirmation page
- 5-star rating, optional headline, review body
- Submitted reviews are pending until moderated (unless auto-approve is on in Settings)
- Approved reviews appear on the home page

### Languages
Click the globe icon (top-right) to switch between English, සිංහල, தமிழ். Your choice persists for 1 year.

### Dark mode
Click the sun/moon icon to switch between Light / Dark / System.

---

## Module / file structure

```
app/
├── Config/                     CI4 config (Routes, Filters, Database, App, Autoload, View)
├── Controllers/                Base controllers (extended by modules)
├── Database/
│   ├── schema.sql              Core schema (run first)
│   ├── schema_addon_*.sql      Feature migrations
│   └── seed_admin.sql          Default admin user
├── Helpers/
│   └── saloncms_helper.php     auth_has(), phone_local(), phone_digits()
├── Language/
│   ├── en/Site.php             English translations
│   ├── si/Site.php             Sinhala translations
│   └── ta/Site.php             Tamil translations
├── Modules/
│   ├── Appointments/           Bookings, calendar, cancellations
│   ├── Auth/                   Login, sessions, permissions filter
│   ├── Billing/                Invoices, payments
│   ├── Branches/               (multi-branch support, optional)
│   ├── Customers/              Customer CRUD + loyalty
│   ├── CustomerPortal/         Self-service customer portal (OTP login)
│   ├── Dashboard/              Admin home, calendar widget
│   ├── Frontend/               Public site (home, services, book, confirm, about, etc.)
│   ├── POS/                    Point-of-sale dashboard
│   ├── Reports/                Analytics + CSV export
│   ├── Reviews/                Customer reviews + Google import
│   ├── Services/               Service catalogue
│   ├── Settings/               All settings tabs
│   └── Staff/                  Staff management + schedules
└── Views/
    ├── components/form/        Reusable form widgets (input, listbox, datepicker, etc.)
    └── layout/
        ├── admin.php           Admin shell
        ├── _admin_sidebar.php  Collapsible nav
        └── auth.php            Login page shell
```

Each module follows the same structure:
```
ModuleName/
├── Controllers/    HTTP entry points
├── Models/         CI4 Model classes (DB access)
├── Views/          PHP templates
├── Filters/        (optional) HTTP filters
└── Services/       (optional) Business logic helpers
```

---

## Database schema

**Core tables** (in `schema.sql`):
- `users`, `roles`, `permissions`, `role_permissions`
- `customers`, `branches`
- `services`, `service_categories`
- `staff`, `staff_services`
- `appointments`, `appointment_services`
- `invoices`, `invoice_items`, `payments`
- `settings` (key-value store)

**Addon tables** (each in its own `schema_addon_*.sql`):
- `appointment_cancellations` — cancellation/no-show tracking
- `staff_schedule`, `staff_time_off`, `staff_date_windows` — staff scheduling
- `loyalty_transactions` — points history
- `reviews` — in-app + imported Google reviews
- `customer_otps` — passwordless portal login codes

To upgrade an existing install, just apply the new addon files:
```bash
mysql -u root -p saloncms < app/Database/schema_addon_NEW.sql
```

All addons are idempotent (`CREATE TABLE IF NOT EXISTS`).

---

## Deployment workflow

Once your live site is set up, the typical update flow:

```bash
# Local
git add .
git commit -m "Add feature X"
git push origin main

# On VPS
ssh root@your-vps
cd /var/www/saloncms
git pull origin main
composer install --no-dev --optimize-autoloader
chown -R www-data:www-data app/   # if any new files were added
rm -rf writable/cache/*            # bust view cache
```

**Or with rsync** (faster for selective file pushes during development):
```bash
rsync -az --exclude=.env --exclude=writable --exclude=vendor \
  /path/to/local/saloncms/ \
  root@your-vps:/var/www/saloncms/
ssh root@your-vps 'chown -R www-data:www-data /var/www/saloncms && rm -rf /var/www/saloncms/writable/cache/*'
```

---

## Troubleshooting

| Symptom | Fix |
|---|---|
| White page, no errors | Check `writable/logs/log-YYYY-MM-DD.log`. Ensure `writable/` is writable by web user. |
| "Database connection error" with MariaDB | Use `127.0.0.1` not `localhost` in `.env` |
| Tailwind classes don't apply | Hard-refresh (Cmd+Shift+R) — CDN caches aggressively. Check `dark` class is on `<html>` for dark variants. |
| Login session keeps expiring | Check `writable/session/` is writable. Try `session.savePath` in `.env`. |
| Dark mode white background on shell | `html.dark` CSS override is in `layout.php` — verify the page extends the right layout. |
| Date picker shows empty | Alpine.js must load — check for JS errors in console. Verify Alpine CDN is reachable. |
| OTP email not arriving | Test SMTP in Settings → SMTP → Send test. Check spam folder. Verify `from-email` is on a domain you own (avoids spam filters). |
| Google reviews import fails | Verify Place ID + API key in Integrations. Enable **Places API** in Google Cloud Console. Add billing (Google requires it even for free tier). |
| Frontend i18n shows English in Sinhala mode | Clear `writable/cache/`. Verify `app/Language/si/Site.php` was deployed. |

---

## License & credits

- **CodeIgniter 4** — MIT
- **Tailwind CSS** — MIT
- **Alpine.js** — MIT
- **Lucide icons** — ISC
- **Inter font** — SIL Open Font License
- **DM Serif Display** — SIL Open Font License
- **dompdf** — LGPL

SalonCMS itself is © Livezen Technologies. See `LICENSE` for terms.

---

## Getting help

- Open an issue: [github.com/Livezen-Technologies/saloncms/issues](https://github.com/Livezen-Technologies/saloncms/issues)
- Live demo: [saloncms.livezencloud.com](https://saloncms.livezencloud.com)

---

🤖 Built with [Claude Code](https://claude.com/claude-code).
