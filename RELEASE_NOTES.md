# Release Notes

## Release: Chapter and State Schema Carry-Over Update

This release updates the Oxford Houses central system so chapter and state module data structures carry over to houses that already exist in the central database.

### Highlights
- Existing house databases are now brought forward automatically when chapter/state modules are added later.
- HSC minutes support is included for older installs through a fallback table bootstrap.
- Central dashboard reporting now recognizes core HSC tables.
- Navigation is prepared for future `state/` module files.

### Best result after update
Open the system once as an admin after deploying these files so the schema sync can run across all active houses.

### Deployment reminder
Replace the project files, keep your database credentials the same, and load the application normally. The sync runs automatically and does not require recreating houses.
