# Changelog

## 2026-03-14

### Added
- Automatic schema carry-over for modules stored in `chapter/` and `state/`
- Fallback creation support for `hsc_meeting_minutes_json`
- HSC-related table labels in the central dashboard
- Sidebar support for the `State` folder
- New project documentation files

### Changed
- Existing active house databases now self-heal when new chapter/state module tables are introduced
- Central configuration now synchronizes chapter/state schema across all registered active houses

### Fixed
- Existing houses no longer miss chapter/HSC/state tables simply because they were created before those files were added
- Older template databases can still support HSC meeting minutes through the new fallback table migration
