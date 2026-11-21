# IdleWheels: peer-to-peer car rental (PHP MVC)

IdleWheels is an e-commerce marketplace that connects owners of idle cars with people who want to rent one. It's written in plain PHP using an MVC structure, with no framework. It stores data in MySQL/MariaDB and takes online payments through **Paystack** (card and mobile money).

## Features

- **Renters**
  - Search by location and dates.
  - Filter by type, seats, transmission and price.
  - View car pages with photos, specs, an availability calendar, host info and reviews.
  - Add extras, see a booking summary, and pay with Paystack.
  - Use My account for upcoming bookings, history, reviews, saved cars and profile.
- **Hosts**
  - Apply to host.
  - Add and edit cars with photo uploads.
  - Accept or decline booking requests.
  - Track earnings after commission.
  - Pay to feature a car.
- **Admin** (`/admin`)
  - Approve or reject hosts and car listings.
  - Manage bookings and refunds, and block or promote users.
  - See a revenue report.
  - Edit extras, categories, reviews (hide/show) and contact messages.
  - Set fees and commission.
- **Pages:** home, How it works, About, FAQ, Contact, Become a host, plus Terms, Privacy, Rental and cancellation policy, and Insurance and safety. The four legal pages are **draft templates**: fill in the `[bracketed]` details and have them reviewed before launch.

### Revenue model

All the money is worked out in `app/Services/Pricing.php`:

| Stream | How it works | Where to change it |
|---|---|---|
| Booking commission | % of rental + extras, kept from the host's share | Admin → Fees & settings |
| Renter service fee | % added to the renter's total at checkout | Admin → Fees & settings |
| Featured listings | Host pays (Paystack) to put a car first in search and on the home page for N days | Admin → Fees & settings (price, days) |
| Add-ons / extras | Driver hire, delivery, child seat, GPS, fuel package… priced per booking or per day | Admin → Extras |

Each booking stores the rates it was made with, so changing a rate never changes past bookings.

## Project structure (MVC)

```
index.php              Front controller: every request comes here (via .htaccess)
.htaccess              Rewrites URLs to index.php; blocks app/, config/, database/, tools/
app/
  bootstrap.php        Loads config, autoloader, helpers, session
  routes.php           Every URL → Controller method
  helpers.php          e() escaping, url(), money(), flash(), old() …
  Core/                The mini-framework
    Router.php         Matches routes; checks the CSRF token on every POST
    Controller.php     Base controller: view(), validate()
    View.php           Renders app/views/*.php inside layouts/main.php
    Database.php       PDO wrapper, prepared statements only
    Auth.php           Login state and guards (requireLogin / requireAdmin / requireApprovedHost)
    Session.php, Csrf.php, Validator.php, Upload.php
    Paystack.php       Initialize + verify transactions, webhook signature check
  Models/              One class per table, all tables prefixed iw_ (Car, Booking, User, HostProfile, Addon, Review…)
  Services/Pricing.php The revenue model
  Controllers/         Home, Car, Booking, Payment, Auth, Account, Host, Admin, Page
  views/               PHP templates (layouts, partials, pages)
assets/                css/app.css (compiled), js/app.js, img/
uploads/               Car and category photos (scripts can't run here)
config/config.php      App settings (reads secrets from .env)
.env / .env.example    Secrets: database login, Paystack keys (.env is git-ignored)
database/              schema.sql, seed.sql, make-admin.php
tools/                 Tailwind source for the CSS, end-to-end test script
```

**How a request flows:** `index.php` → `Router` → a controller method. The method checks permissions, reads or writes data through **Models**, and renders a **View**.

## Security

- **Database:** all queries use PDO prepared statements.
- **Output:** everything printed in a view is escaped with `e()`.
- **Passwords:** stored with `password_hash()`. A session's login attempts are slowed after 8 failures.
- **Forms:** every one carries a CSRF token, which the router checks before any POST runs.
- **Sessions:** the session ID is renewed on login, and the cookie is `HttpOnly` and `SameSite=Lax`.
- **Uploads:** only real JPEG/PNG/WebP images up to 8 MB are accepted, saved with random names, and scripts can't run in `uploads/`.
- **Payments:**
  - A booking or featured listing is only marked paid after the server asks Paystack to **verify** the transaction and the amount and currency match.
  - The Paystack webhook is accepted only with a valid `x-paystack-signature`, an HMAC-SHA512 of the request body made with your secret key.
  - Each payment is applied only once.

## Run it locally (XAMPP)

1. Put the folder at `C:\xampp\htdocs\idlewheels` and start **Apache** and **MySQL** in the XAMPP control panel.
2. Create the database. Either:
   - In phpMyAdmin: create a database `idlewheels` (utf8mb4), then import `database/schema.sql` and then `database/seed.sql`.
   - From the command line, using port 3307 if your XAMPP MySQL uses it:

     ```bash
     C:\xampp\mysql\bin\mysql.exe -u root -P 3307 -e "CREATE DATABASE idlewheels CHARACTER SET utf8mb4"
     C:\xampp\mysql\bin\mysql.exe -u root -P 3307 idlewheels < database/schema.sql
     C:\xampp\mysql\bin\mysql.exe -u root -P 3307 idlewheels < database/seed.sql
     ```

3. Copy `.env.example` to `.env` and fill it in. One `.env` serves both places: the app uses the `LOCAL_*` settings on `localhost`/`127.0.0.1` (and for command-line scripts) and the `LIVE_*` settings on any other host. Set `APP_ENV=local` or `APP_ENV=live` to force one. XAMPP on this machine uses port **3307**. The `.env` file holds your secrets (database password, Paystack keys). It's listed in `.gitignore` and blocked from the web by `.htaccess`, and `config/config.php` reads from it.
4. Open http://localhost/idlewheels, register an account, then make it an admin:

   ```bash
   C:\xampp\php\php.exe database/make-admin.php you@example.com
   ```

## Paystack (card + mobile money)

1. Sign up at paystack.com and open **Settings → API Keys & Webhooks**.
2. Copy the **test** public and secret keys (`pk_test_…`, `sk_test_…`) into `.env` as `PAYSTACK_PUBLIC_KEY` and `PAYSTACK_SECRET_KEY`.
3. While testing, use Paystack's test card or test mobile money details shown on their checkout page. No real money moves.
4. **Webhook (recommended on the live host):** set the webhook URL in the Paystack dashboard to `https://YOUR-SITE/payment/webhook`. Payments still complete without it, because the callback also verifies.
5. **Refunds:** when a host declines or a renter cancels a paid booking, it shows under **Admin → Bookings → Refunds due**. Refund it in the Paystack dashboard, then click **Mark refunded**.

Going live needs Paystack's business verification and your **live** keys.

## Deploy to the school host

1. **Upload files:** upload the whole folder (FTP or the host's file manager) into your web folder, e.g. `public_html/idlewheels`. You can skip `tools/tailwind/node_modules`.
2. **Create the database:** create a MySQL database and user in the host's control panel, then import `database/schema.sql` and `database/seed.sql` with phpMyAdmin.
3. **Create `.env` on the host:** copy `.env.example` to `.env` and fill it in. Upload it separately, because it isn't committed.
   - `LIVE_DB_HOST`, `LIVE_DB_PORT` (usually 3306), `LIVE_DB_NAME`, `LIVE_DB_USER`, `LIVE_DB_PASS` → from the control panel.
   - `APP_URL` → the full public address, e.g. `https://yourhost.edu/~you/idlewheels`. It can also be left empty to auto-detect.
   - `APP_DEBUG=false`.
   - `PAYSTACK_PUBLIC_KEY` / `PAYSTACK_SECRET_KEY` → your keys.

   Then open `https://YOUR-SITE/.env` in a browser. It must show **403 Forbidden**.
4. **Allow uploads:** make sure `uploads/` is writable by PHP (permissions 755, or 775 if needed).
5. **Make yourself admin:** register on the live site, then run this in phpMyAdmin:

   ```sql
   UPDATE iw_users SET role = 'admin' WHERE email = 'you@example.com';
   ```

6. **Requirements:**
   - PHP 7.4 or 8.x with the `pdo_mysql`, `curl`, `mbstring` and `openssl` extensions, which are standard on most hosts.
   - Apache with `mod_rewrite`, so `.htaccess` works.

   If pretty URLs give 404s, ask the host to enable `AllowOverride All`.

## Demo content

`database/seed.sql` adds:
- 4 cars from demo hosts. Their accounts can't log in.
- 4 categories, 5 extras and 11 FAQs.
- The fee settings: 15% commission, 7% service fee, featured listing GHS 50 for 7 days.

The car photos are hotlinked from the earlier Wix site. Replace them by editing the cars or uploading your own.

## Changing the design

Styles are written with Tailwind classes in the views and compiled to `assets/css/app.css`, so the host needs no build tools. If you add new classes, rebuild the CSS (needs Node.js):

```bash
cd tools/tailwind
npm install
npm run build
```

## Tests

`tools/e2e-test.sh` checks the main flows against the local site:
- registration and host application,
- admin approval and car upload,
- booking pricing and date holds,
- host accept, reviews, featuring and revenue,
- every admin tab,
- CSRF and webhook security.

It creates and then deletes its own test accounts. Run it with Git Bash from the project folder:

```bash
bash tools/e2e-test.sh
```
