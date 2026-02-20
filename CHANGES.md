# Changelog — mod_attendancecontrol

All notable changes to this plugin are documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] — 2026-02-20

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
