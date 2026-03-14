# Oxford Houses Central System

This package provides a multi-house Oxford House document platform with centralized login, house isolation, central administration, and shared reporting across house databases.

## What changed

This update adds automatic chapter/state schema carry-over for existing houses already registered in the central database.

### Included improvements
- Auto-syncs tables used by files inside the `chapter/` and `state/` folders into every active house database.
- Keeps current houses from missing newly added chapter/HSC/state tables.
- Adds fallback creation for `hsc_meeting_minutes_json` so HSC minutes continue working even when the template database is older.
- Adds HSC table visibility in the central dashboard summary.
- Adds `State` to the sidebar ordering and icon map so future state-level files show naturally in navigation.

## How the carry-over now works

On load, the system now:
1. Reads all active houses from `oxford_master_houses`
2. Ensures each house database exists
3. Scans PHP files inside `chapter/` and `state/`
4. Runs any `CREATE TABLE IF NOT EXISTS` and `ALTER TABLE` statements it finds against every active house database
5. Applies a fallback HSC table migration for older installs

This means new chapter/state modules can be dropped into the project and their table setup will propagate to existing house databases without needing to recreate houses.

## Updated files
- `extras/master_config.php`
- `extras/header.php`
- `central_admin.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE_NOTES.md`

## Notes
- The `state/` folder is ready for future modules. Any PHP file added there with table bootstrap SQL will now be included in the same sync behavior.
- The sync uses idempotent schema statements, so repeated page loads are safe.
- Existing data remains intact.
