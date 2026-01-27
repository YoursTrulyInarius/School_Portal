# Database Setup Instructions

## Quick Setup (Recommended)

1. **Create Database**: Create a database named `school_portal` in phpMyAdmin (or your SQL client).
2. **Configure App**: 
   - Rename `config.example.php` to `config.php`.
   - Update `DB_USERNAME` and `DB_PASSWORD` if they differ from the defaults (root/empty).
3. **Import Data**: Import `database.sql` into the `school_portal` database.
4. **Done!**: Navigate to `http://localhost/School_Portal/` to get started.

## Default Credentials

After importing `database.sql`, you can log in with:

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |

> [!WARNING]
> Please change these passwords immediately after your first login for security.

## Included in `database.sql`

The `database.sql` file is now a consolidated, "one-click" setup script that includes:
- **Full Schema**: Up-to-date table structures for grades, payments, and schedules (including rooms).
- **Default User**: The administrator account is pre-seeded.

## Troubleshooting

### Mismatching Database Name
Ensure your `config.php` has:
```php
define('DB_NAME', 'school_portal');
```
And that your database in phpMyAdmin is also named `school_portal`.

### "Table doesn't exist" Errors
If you recently updated the code via git clone/pull, you might need to re-import `database.sql` if new tables were added.

---
**Current Version**: 2.1 (January 2026)
- Consolidated all migrations into `database.sql`
- Standardized configuration template
- Included seed data for essential tables
