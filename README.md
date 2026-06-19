# FruGo — Fresh Fruits Delivery Platform

> **Get fresh fruits at better prices from trusted local vendors delivered to your doorstep.**

A full-stack Laravel 11 e-commerce application for fresh produce — featuring passwordless OTP login, a product catalogue with search and filtering, session-based cart, order tracking with live status tabs, and an integrated vendor approval workflow. Deployable via Docker locally or Vercel for production.

---

## Quick Start (Docker)

```bash
git clone <repo-url>
cd frugo
bash deploy.sh   # or: make deploy
```

| Role | How to sign in | Notes |
|------|---------------|-------|
| Customer | OTP login with any email | Account auto-created on first OTP |
| Product Manager | Email `manager@example.com` / `Manager@1234` | Lists products for admin approval |
| Admin | Email `admin@example.com` / `Admin@1234` | Full access |

- Shop: [http://localhost:8080](http://localhost:8080)
- OTP inbox (MailHog): [http://localhost:8025](http://localhost:8025)

---

## Manual Setup

```bash
cp .env.example .env
# Edit .env — set DB_*, MAIL_*, APP_KEY

composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

## Makefile Commands

| Command | Description |
|---------|-------------|
| `make up` | Start Docker containers |
| `make down` | Stop containers |
| `make deploy` | Full bootstrap (build + migrate + seed) |
| `make fresh` | Drop all tables and re-seed |
| `make shell` | Open shell inside app container |
| `make test` | Run PHPUnit |
| `make lint` | Run Laravel Pint (PSR-12) |
| `make cache-clear` | Clear all Laravel caches |

---

## Vercel Deployment

> FruGo uses the community [`vercel-php`](https://github.com/juicyfx/vercel-php) runtime (PHP 8.2).

### Prerequisites

- External MySQL: [PlanetScale](https://planetscale.com) (free tier) or [Railway](https://railway.app)
- Transactional email: [Resend](https://resend.com) or [Mailgun](https://mailgun.com)

### Deploy

```bash
npm i -g vercel
vercel login
vercel --prod
```

### Required environment variables (set in Vercel dashboard)

| Variable | Value |
|----------|-------|
| `APP_KEY` | `base64:...` (`php artisan key:generate --show`) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-project.vercel.app` |
| `DB_HOST` | PlanetScale / Railway host |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | your database |
| `DB_USERNAME` | db user |
| `DB_PASSWORD` | db password |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `LOG_CHANNEL` | `stderr` |
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | your SMTP host |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | SMTP user |
| `MAIL_PASSWORD` | SMTP password |
| `MAIL_FROM_ADDRESS` | `noreply@yourdomain.com` |
| `MAIL_FROM_NAME` | `FruGo` |

After first deploy, run migrations against your external DB:

```bash
vercel env pull .env.vercel.local
php artisan migrate --seed   # point DB_* to PlanetScale and run locally
```

---

## Architecture Decisions

### Why Laravel 11?

Laravel 11's streamlined `bootstrap/app.php` eliminates the old `Http/Kernel.php` proliferation — one place for middleware aliases, providers, and exception handling. Its built-in Auth scaffolding, Eloquent ORM, Blade templating, and Mailable classes covered every FruGo requirement without extra packages.

### OTP Passwordless Authentication

Customers sign in with just their email. A 6-digit OTP is generated, stored in the `otp_tokens` table (10-minute expiry), and emailed via `OtpMail`. On success the token is deleted immediately (replay protection). `User::firstOrCreate()` auto-registers new customers — zero friction signup.

In `APP_DEBUG` mode the OTP is shown on-screen in a highlighted box so development works without an SMTP server.

### Repository + Service Pattern

```
Controller → Service → Repository → Eloquent Model
```

- **Repository** (`ProductRepository`) owns all query construction — pagination, eager loading, search scopes.
- **Service** (`ProductService`) owns business rules: approval state transitions, ownership checks.
- **Controller** is an HTTP adapter only — validates input, calls the service, returns a view or redirect.

This keeps each layer independently testable and prevents query logic from leaking into views or controllers.

### Product Approval Workflow

Products added by `product_manager` users enter a `pending` state. Only `admin` users can approve or reject. Only `approved` products appear in the FruGo shop. This ensures product quality without a separate moderation app.

### Sticky Navbar + Scrolling Brand Bar

The green topbar sits in normal page flow and scrolls away with content. The white FruGo navbar uses `position: sticky; top: 0` so it pins to the viewport once the brand bar is gone. A `$(window).scroll` handler adds a deeper box-shadow as the user scrolls, giving depth feedback.

### Custom Bootstrap 5 Pagination

Laravel's default pagination view uses Bootstrap Icons that rendered at an inherited large font size (producing huge arrows). A published `resources/views/vendor/pagination/bootstrap-5.blade.php` replaces them with FontAwesome `fa-chevron-left/right` at `0.75rem`, styled as circular pill buttons.

### Order Status Tabs

`OrderController::index()` accepts `?status=all|pending|processing|completed|cancelled`. The base query is cloned five times to compute per-tab counts in a single pass — no GROUP BY, no raw SQL. Pagination preserves the filter via `->appends()`.

### PSR-4 / PSR-12

`composer.json` maps `App\` → `app/` per PSR-4. All files carry `declare(strict_types=1)`. Code style enforced by Laravel Pint (`make lint`).

---

## Challenges & Solutions

### Carousel Navigation Arrows Appearing Under Products

**Challenge:** Owl Carousel testimonial and vegetable carousels rendered large `<` `>` nav buttons that overlapped product listings.

**Solution:** Both carousel instances use `nav: false` in `public/js/main.js`. A CSS safety net — `display: none !important` on `.owl-carousel .owl-nav` in the shop layout — catches any future carousel that opts in to navigation.

### OTP Email Not Delivered Locally

**Challenge:** The default `MAIL_MAILER=log` wrote OTPs to `laravel.log` — invisible to testers.

**Solution:** Added MailHog as a Docker service (`pm_mailhog`, ports `8025`/`1025`). All local mail is captured at `http://localhost:8025`. For Vercel/production, configure a transactional SMTP provider in the Vercel env vars.

### Vercel Read-Only Filesystem

**Challenge:** Vercel's serverless filesystem is read-only except for `/tmp`. Laravel needs writable `storage/` and `bootstrap/cache/` paths.

**Solution (two parts):**
1. `api/index.php` (the Vercel entry point) creates `/tmp/storage/...` directories before bootstrapping.
2. `bootstrap/app.php` calls `$app->useStoragePath('/tmp/storage')` when the `VERCEL` env var is set, redirecting all framework file I/O to `/tmp`.
3. Session and cache are set to `cookie` / `array` in Vercel env vars, so no file writes are needed at runtime.

### Session-Based Cart Across Pages

**Challenge:** Cart state needed to survive page navigations and partial updates without a database.

**Solution:** Cart stored as a PHP session array (keyed by product ID). `CartController` methods (add/update/remove) manipulate `session('cart', [])` and redirect back. The cart count in the navbar reads `array_sum(session('cart', []))` — one expression, no query.

### Rich-Text Description XSS

**Challenge:** Product descriptions come from a Quill rich-text editor — user-supplied HTML that must be stored but not weaponised.

**Solution:** `ProductRequest::prepareForValidation()` calls `strip_tags()` with an explicit safe-tag allow-list before validation. In views `{{ }}` HTML-encodes all output by default; `{!! !!}` is used only for the pre-sanitised description field.

---

## Security

| Threat | Mitigation |
|--------|------------|
| **SQL Injection** | All queries use Eloquent's parameterised builder. No raw SQL from user input. |
| **Stored XSS** | Rich-text HTML strip-tagged on ingest. Blade `{{ }}` encodes all output by default. |
| **Reflected XSS** | Blade encodes search terms, flash messages, and all reflected values. |
| **CSRF** | `@csrf` on every state-changing form. `VerifyCsrfToken` middleware rejects requests without a valid token. |
| **Brute force** | `RateLimiter` in `LoginRequest` — 5 attempts per email/IP, then lockout. |
| **OTP replay** | Token deleted immediately on successful verification. Expires after 10 minutes. |
| **Password storage** | Bcrypt via Laravel's `hashed` cast (12 rounds). |
| **Session fixation** | Session regenerated on login; invalidated + token regenerated on logout. |
| **Privilege escalation** | Role checked in middleware for route access AND in `ProductService::canModify()` for ownership. `abort(403)` on failure. |
| **Sensitive file exposure** | Nginx config denies `/.*` (dotfiles). Docker image never exposes `.env`. |
| **Security headers** | Nginx adds `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`. |

---

## Performance

- **Eager loading** — `ProductRepository::paginate()` uses `->with('creator:id,name')` to eliminate N+1 on the product index.
- **Spinner** — CSS overlay hides the page until JS has loaded, preventing flash-of-unstyled-content.
- **Clone-based counts** — Order tab counts are computed by cloning the base Builder query (not re-building from scratch) for each status.
- **`withQueryString()`** — Pagination links carry `?status=` and `?search=` parameters automatically.
- **OPcache** — Compiled into the Docker image; caches PHP opcodes in shared memory.
- **Deploy caches** — `deploy.sh` runs `config:cache`, `route:cache`, and `view:cache` after every deployment.
- **FULLTEXT index** — `products(title, description)` supports keyword search without full table scans.

---

## Future Improvements

| Area | What to add |
|------|-------------|
| **Real-time order tracking** | Broadcast status changes to customers via Laravel Reverb / Pusher WebSockets. |
| **Image uploads** | Product photo gallery using S3 driver + `spatie/laravel-medialibrary` for resizing. |
| **Delivery integration** | Shiprocket / Delhivery API for live courier tracking inside the orders page. |
| **Vendor portal** | Self-service vendor registration so suppliers can list their own produce. |
| **Full-text search** | Replace `LIKE` with Laravel Scout + Meilisearch for relevance-ranked, typo-tolerant results. |
| **Mobile app** | Versioned JSON API with Sanctum tokens to power a React Native or Flutter client. |
| **Review & ratings** | Star ratings and customer reviews per product, with spam detection. |
| **Loyalty rewards** | Points system: earn on purchase, redeem at checkout. |
| **Test suite** | Feature tests for OTP auth, cart lifecycle, order filtering, and approval workflow. |
| **CI/CD** | GitHub Actions: Pint lint → PHPUnit → Docker build → Vercel deploy. |

---

## Project Structure

```
.
├── api/
│   └── index.php                           ← Vercel PHP runtime entry point
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/OtpLoginController.php ← OTP request / verify / resend
│   │   │   ├── Shop/
│   │   │   │   ├── ShopController.php      ← product browsing + search
│   │   │   │   ├── CartController.php      ← session-based cart
│   │   │   │   └── OrderController.php     ← order history + status tabs
│   │   │   └── ProductController.php       ← CRUD + approval actions
│   │   ├── Middleware/CheckRole.php        ← RBAC enforcement
│   │   └── Requests/ProductRequest.php    ← validation + XSS sanitisation
│   ├── Models/
│   │   ├── User.php                        ← role constants, OTP relation
│   │   ├── Product.php                     ← approval scope, soft deletes
│   │   ├── OtpToken.php                    ← time-limited OTP records
│   │   ├── Order.php
│   │   └── OrderItem.php
│   ├── Mail/OtpMail.php                    ← OTP email Mailable
│   ├── Repositories/ProductRepository.php ← all DB query logic
│   ├── Services/ProductService.php         ← business rules + approval
│   └── Providers/
│       ├── AppServiceProvider.php          ← Bootstrap5 pagination, password rules
│       └── RepositoryServiceProvider.php
├── bootstrap/app.php                       ← middleware, providers, Vercel storage override
├── database/migrations/
├── database/seeders/
├── resources/views/
│   ├── layouts/shop.blade.php              ← sticky nav, FruGo branding, owl-nav override
│   ├── auth/
│   │   ├── otp-request.blade.php          ← email entry form
│   │   └── otp-verify.blade.php           ← 6-digit input + dev OTP display
│   └── shop/
│       ├── index.blade.php                ← product grid + filters
│       ├── show.blade.php                 ← product detail + add-to-cart
│       ├── cart.blade.php                 ← cart summary
│       ├── checkout.blade.php             ← address + place order
│       └── orders.blade.php               ← order history + status tabs
├── routes/web.php
├── docker-compose.yml                      ← app + nginx + mysql + mailhog
├── Dockerfile
├── Makefile
├── deploy.sh
└── vercel.json                             ← Vercel routing + PHP runtime config
```
