# Approval System

Simple PHP approval/reject demo for content items.

Setup

- Copy or set database credentials via environment variables `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` (default `Tourism`).
- From a browser or CLI, run `init_db.php` once to create the `contents` table.
- Optionally run `sample_data.php` to insert test rows.

Files

- `db.php` — database connection helper (uses env vars or defaults)
- `init_db.php` — creates database (if needed) and `contents` table
- `sample_data.php` — inserts demo rows
- `admin.php` — simple admin listing UI with Approve/Reject buttons
- `action.php` — endpoint to update status

Usage

1. Open `init_db.php` in browser: `http://<host>/tourism-managment/approval-system/init_db.php`
2. (Optional) Run `sample_data.php` to insert dummy items.
3. Visit `admin.php` to review and approve/reject content.

Security notes

- This is a minimal example. In production add authentication, CSRF protection, and stricter input validation.
