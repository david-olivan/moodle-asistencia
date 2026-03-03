# Changelog — mod_attendancecontrol

All notable changes to this plugin are documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

## [1.0.0] — 2026-03-03

### Added
- AMD build files (`amd/build/`) added to the repository (previously generated inside Moodle root).
- Moodle code-checker CI step and local pre-commit hook (`scripts/`).
- CI status badge in README.

### Fixed
- Resolved all Moodle PHPCS / code-style violations across PHP, JS, and Mustache files.
- Renamed camelCase PHP variables to snake_case; removed non-English inline comments.
- Eliminated N+1 DB queries by preloading attendance records before student loops.
- Switched `get_records` calls to recordsets for memory-efficient iteration over large datasets.
- Fatal errors on course module duplication resolved (issue #19).
- Replaced unsafe `innerHTML` DOM manipulation with `core/templates` for schedule and holiday rows (issue #18).
- Wrapped row templates in `<table>/<tbody>` for Mustache linter compatibility; switched to DOMParser for safe insertion.
- Resolved ESLint warnings in `mod_form.js` (`space-before-function-paren`, `camelcase`).
- `session_list.mustache`: "Record today's attendance" button now always visible (disabled when no session exists for today).
- Behat scenarios: corrected activity generator, Mustache template context, and feature step definitions.

## [0.0.1] — 2026-02-20

### Added
- Activity module `mod_attendancecontrol` for daily attendance tracking in vocational training courses.
- Configurable weekly schedule (multiple time slots per day).
- Holiday management per instance.
- Automatic session generation from course date range and weekly schedule.
- Future session regeneration on configuration update, preserving sessions with existing records.
- Five attendance statuses: Present, Late, Justified absence, Unjustified absence, Not recorded.
- Configurable penalty ratios: late arrivals and justified absences converted to equivalent unjustified hours.
- Configurable maximum absence threshold with red-flag highlighting for students who exceed it.
- Teacher view: weekly session list with navigation, per-session attendance form, group summary report.
- Student view: own attendance summary with percentage and session breakdown (read-only).
- Excel export: three-sheet workbook (Summary, Session detail, Configuration).
- Six capabilities: `addinstance`, `viewsummary`, `recordattendance`, `viewownattendance`, `export`, `managesessions`.
- Privacy API (GDPR): metadata, context discovery, data export and deletion.
- Moodle Backup/Restore API support.
- Events: `course_module_viewed`, `attendance_recorded`, `session_created`.
- PHPUnit tests: `attendance_calculator_test` (4 tests), `session_manager_test` (7 tests).
- Behat tests: 7 scenarios covering teacher and student flows, data isolation and export.
- Language strings: English (`lang/en/`) and Spanish (`lang/es/`).
