# Database Portability

PasPapan is PostgreSQL-first for local development, CI, and VPS production. MySQL/MariaDB remain compatibility targets for managed database providers or legacy installs, but PostgreSQL 15+ is the default operational baseline.

## Supported Drivers

| Driver | Status | Recommended Use |
| --- | --- | --- |
| PostgreSQL 15+ | Default | Local development, CI, VPS production, larger installations |
| MySQL 8+ | Compatibility | Legacy installs or managed MySQL providers |
| MariaDB compatible | Compatibility | Legacy installs or managed MariaDB providers |
| SQLite | Test/local only | Fast local tests and lightweight smoke checks |

## PostgreSQL Environment

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=paspapan
DB_USERNAME=paspapan
DB_PASSWORD=secret
DB_SCHEMA=public
DB_SSLMODE=prefer
```

Use `DB_SSLMODE=require` when the managed provider requires TLS.

## Compatibility Rules

- New migrations must use Laravel schema builder APIs before raw SQL.
- Driver-specific SQL must branch on `DB::getDriverName()`.
- Avoid MySQL-only functions in application queries. Prefer query builder expressions that work on MySQL, MariaDB, PostgreSQL, and SQLite.
- `after()` column placement is cosmetic. Do not rely on physical column order for behavior.
- Enum-like values should be treated as application strings unless a driver-specific constraint is intentionally maintained.
- Seeders must be idempotent and use query-builder `upsert()`/`updateOrCreate()` patterns.

## Validation

Fast static guard:

```bash
composer check:database-portability
```

SQLite migration smoke:

```bash
composer check:database-portability:sqlite
```

PostgreSQL migration, real master seeder, and focused test smoke:

```bash
PASPAPAN_PG_HOST=localhost \
PASPAPAN_PG_PORT=5432 \
PASPAPAN_PG_USER=lutuk \
PASPAPAN_PG_ADMIN_DB=absensi \
composer check:database-portability:pgsql
```

The PostgreSQL smoke command creates and drops a temporary `paspapan_pg_smoke_*` database. It does not run migrations against the admin database.

CI includes PostgreSQL, SQLite, and MySQL compatibility smoke jobs so engine-specific regressions fail before merge.

Optional MySQL compatibility smoke for VPS providers that only expose managed MySQL:

```bash
PASPAPAN_MYSQL_HOST=127.0.0.1 \
PASPAPAN_MYSQL_PORT=3306 \
PASPAPAN_MYSQL_USER=root \
PASPAPAN_MYSQL_PASSWORD=secret \
composer check:database-portability:mysql
```

The MySQL smoke command creates and drops a temporary `paspapan_mysql_smoke_*` database. PostgreSQL remains the local, CI, and VPS release baseline.
