# E-Report Deployment Checklist

This checklist is for preparing a production deployment. It does not authorize or perform a deployment, database migration, or key rotation.

## Server and release

- [ ] Use PHP 8.3 or newer, within the version supported by the locked dependencies.
- [ ] Install the PHP extensions required by Composer and Laravel, including PDO MySQL, OpenSSL, Mbstring, Fileinfo, Tokenizer, XML, Ctype, JSON, BCMath, and Intl when required by the lock file.
- [ ] Verify the production platform with `composer check-platform-reqs --no-dev`.
- [ ] Configure the web server's document root to the application's `public/` directory only. Do not expose the repository root, `.env`, `vendor/`, or `storage/`.
- [ ] Terminate HTTPS at the web server or trusted load balancer, redirect HTTP to HTTPS, and enable HSTS after confirming all relevant hostnames support HTTPS.
- [ ] Restrict inbound traffic to the required web and operations endpoints; do not expose database or cache ports publicly.

## Environment and application security

- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, and an HTTPS `APP_URL` in the deployment secret manager. Never copy a local `.env` or local `APP_KEY` to production.
- [ ] Provide a unique production `APP_KEY` through the approved secret-management process. If a local key may ever have been used in production, assess exposure and plan a controlled rotation; rotation can invalidate encrypted sessions, signed URLs, and other data protected by the key.
- [ ] Supply production database, mail, queue, cache, and filesystem settings through protected environment variables. Do not store credentials in Git, deployment logs, or build artifacts.
- [ ] Keep session cookies secure: `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, and `SESSION_SAME_SITE=lax` unless a documented integration requires a different policy. Use HTTPS for the entire authenticated application.
- [ ] Configure trusted proxies narrowly for the actual load balancer addresses and forwarded headers. Restrict accepted hostnames at the web server/load balancer; do not trust arbitrary proxy addresses or Host headers.
- [ ] Review authorization, registration throttling, CSRF protection, upload limits, and error responses with production settings before opening public access.

## Database and private uploads

- [ ] Provision a dedicated production MySQL database with least-privilege application credentials, supported MySQL version, and `utf8mb4` character support.
- [ ] Confirm backup retention, encryption, access controls, and a successful restore drill before the first release.
- [ ] Review all pending migrations and their expected runtime, locks, and compatibility with the deployed application. Apply them only through an approved release step after a current backup; do not use destructive reset or refresh commands.
- [ ] The application stores report evidence on Laravel's `local` disk under `storage/app/private`. Persist and back up this private storage across releases, and ensure the web server cannot serve it directly.
- [ ] Do not expose report evidence through `public/storage`. Any object-storage replacement must retain private visibility and authenticated/authorized retrieval.

## Build and release steps

- [ ] Build from the reviewed commit and lock files.
- [ ] Install PHP dependencies with `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`.
- [ ] Install frontend dependencies with `npm ci` and create production assets with `npm run build`. Publish only the resulting assets, not `node_modules/`.
- [ ] Make `storage/` and `bootstrap/cache/` writable by the application runtime account. Keep application source and secrets read-only to that account wherever practical.
- [ ] Cache Laravel configuration, routes, and views with the approved `php artisan optimize` release step after production environment variables are available.
- [ ] Ensure queue workers, scheduled tasks, and any required long-running processes are configured and restarted safely as part of the release.
- [ ] Run health checks and a minimal authenticated smoke test before directing public traffic to the new release.

## Operations, backup, and rollback

- [ ] Monitor application health, HTTP error rates, queue failures, database availability, disk capacity, and storage growth.
- [ ] Send logs to an access-controlled destination with retention and alerting. Redact credentials, tokens, personal information, and uploaded content from logs and support tooling.
- [ ] Keep encrypted, access-controlled backups of both the database and private report files; periodically verify restores in an isolated environment.
- [ ] Retain the previous known-good application release and document how to switch traffic back without overwriting user uploads.
- [ ] Prefer forward-compatible schema changes. If a release must be rolled back, restore application code first and follow the reviewed database recovery plan; do not run a rollback or restore against production without explicit approval and a verified backup.
- [ ] Record the release commit, migration set, backup reference, smoke-test result, and rollback decision in the deployment record.
