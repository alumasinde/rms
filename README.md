# RMS — Enterprise Rental Management System (Core Foundation)

This is the `app/Core/` foundation for the multi-tenant RMS: DI container,
router, request/response, session, auth (with lockout), CSRF, validation,
view rendering, cache, logger, events, and the exception handler — wired
together in `public/index.php`.

## Setup

1. `composer install` (or `composer dump-autoload` if you add real deps
   later — this ships with a minimal hand-written `vendor/autoload.php`
   PSR-4 autoloader standing in for Composer's, so it boots without
   Composer installed first)
2. Copy `.env.example` to `.env` and fill in DB credentials
3. Create the `users` table (see schema below) and any others your
   modules need
4. Point your webserver's document root at `public/`

## Minimal `users` table to boot Auth

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    display_name VARCHAR(200) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

## What's verified

Every PHP file passes `php -l`. An integration smoke test (not shipped
in this zip) booted the full DI chain against a real SQLite `users`
table and exercised: Container autowiring, Config, Auth login success/
failure/lockout accounting, CSRF token issue+verify, Cache put/get/
remember, Logger writes, Validator pass/fail paths, and Router dispatch
including regex route params and 404 handling on no match. All passed.

## Not yet built

Organizations/Users/Roles/Permissions module, RBAC enforcement,
onboarding wizard, and every domain module (Properties, Leases,
Billing, etc.) per the master spec.
