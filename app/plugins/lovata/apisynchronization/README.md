# Lovata ApiSynchronization — Environment configuration

This plugin reads all API access credentials and connection settings from your application environment (.env). CLI options are minimized: only `--rows` is supported to control page size during sync.

## Required .env variables

Add these keys to your project .env (values are examples; use your real credentials):

```
APISYNC_BASE_URL=https://api.example.com:7713
APISYNC_USERNAME=your_username
APISYNC_PASSWORD=your_password

# Optional
APISYNC_VERIFY_SSL=false     # set to true to verify SSL certificates
APISYNC_TIMEOUT=30           # request timeout in seconds
```

Notes:
- Commands no longer accept credentials or connection options; everything except `--rows` comes from .env.
- `APISYNC_VERIFY_SSL` is controlled only via .env now.
- `APISYNC_TIMEOUT` is used by the internal HTTP client.

## Commands

Supported commands and options:
- `php artisan seipee:sync [--rows=200]` — runs the full pipeline.
- `php artisan seipee:sync.properties [--rows=200]`
- `php artisan seipee:sync.products [--rows=200]`
- `php artisan seipee:sync.product-properties [--rows=200]`

Examples:

```
php artisan seipee:sync --rows=200
php artisan seipee:sync.properties --rows=500
php artisan seipee:sync.products --rows=100
php artisan seipee:sync.product-properties --rows=200
```

## Summary of changes
- Removed all optional CLI parameters except `--rows`. Credentials, base URL, SSL verify are env-only.
- Removed `--where`, `--dry-run`, `--max-pages`, and testing caps; commands now process full datasets.
- Removed hardcoded credentials and base URL from all console commands.
- HTTP client initializes from env (`APISYNC_BASE_URL`, `APISYNC_VERIFY_SSL`, `APISYNC_TIMEOUT`).
