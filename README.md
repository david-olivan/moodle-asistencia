# Mister Asistencia (`mod_attendancecontrol`)

[![Moodle Plugin CI](https://github.com/david-olivan/moodle-asistencia/actions/workflows/ci.yml/badge.svg)](https://github.com/david-olivan/moodle-asistencia/actions/workflows/ci.yml)

> A lightweight, configurable attendance control activity module for Moodle, designed for vocational training centres.

**Author:** David Oliván Malagón (misterdavs)
**Plugin type:** Activity module (`mod`)
**Moodle version:** 4.5 LTS or later
**License:** [GNU GPL v3 or later](LICENSE.md)

---

## Description

**Mister Asistencia** solves the key limitations of the standard `mod_attendance` plugin: information overload, hard-to-read reports, and poor adaptability to real classroom workflows.

It gives teachers a minimal-click daily workflow: configure once, register attendance session by session, and get an instant group summary with attendance percentages and automatic threshold alerts.

---

## Features

- **Automatic session generation** from a configurable weekly schedule and course date range, skipping defined holidays.
- **Five attendance statuses**: Present, Late, Justified absence, Unjustified absence, Not recorded.
- **Configurable penalty ratios**: late arrivals and justified absences are converted to equivalent unjustified hours using teacher-defined ratios.
- **Absence threshold alerts**: students who exceed the configured maximum absence percentage are highlighted in red.
- **Group summary report** with per-student attendance percentages, accessible to teachers at a glance.
- **Student self-service view**: students can only see their own attendance data (read-only), including a full session-by-session breakdown.
- **Excel export**: three-sheet workbook (Summary, Session detail, Configuration) for administrative use.
- **Session management**: future sessions can be regenerated when the schedule changes, preserving sessions that already have attendance records.
- **Full Privacy API (GDPR)**: metadata declaration, per-user data export and deletion.
- **Backup and Restore API** support.
- **English and Spanish** language strings included.

---

## Requirements

- Moodle 4.5 LTS or later
- PHP compatible with the selected Moodle version
- No additional dependencies

---

## Installation

### From the Moodle plugin directory

1. Log in to your Moodle site as administrator.
2. Go to **Site administration → Plugins → Install plugins**.
3. Search for *Mister Asistencia* or upload the downloaded ZIP file.
4. Follow the on-screen upgrade steps.

### Manual installation

1. Download or clone this repository.
2. Copy the plugin folder into `<moodle_root>/mod/attendancecontrol/`.
3. Log in as administrator and navigate to **Site administration → Notifications** to trigger the database upgrade.

---

## Configuration

Each activity instance is configured independently:

| Setting | Description |
|---|---|
| **Student group** | Moodle group whose members appear on the attendance list |
| **Date range** | Course start and end dates used to generate sessions |
| **Weekly schedule** | One or more time slots per day of the week |
| **Holidays** | Specific dates excluded from session generation |
| **Maximum absence %** | Threshold above which a student is flagged in red |
| **Late arrival ratio** | Number of late arrivals that count as 1 unjustified absence hour |
| **Justified absence ratio** | Number of justified absence hours that count as 1 unjustified absence hour |

---

## Usage

### Teacher workflow

1. Add a **Mister Asistencia** activity to a course and complete the configuration form.
2. Sessions are generated automatically. Navigate by week to see the session list.
3. Click **Record attendance** for any session to open the attendance form.
4. Optionally use **Mark all present** for quick registration, then adjust individual students.
5. Access the **Summary report** to see group-wide attendance percentages.
6. Click any student name for a detailed session-by-session breakdown.
7. Use **Export to Excel** to download the full dataset.

### Student workflow

1. Open the **Mister Asistencia** activity in the course.
2. View your own attendance summary: percentage, equivalent absence hours, and threshold status.
3. Click **View session breakdown** for a full list of sessions and your status in each one.

---

## Capabilities

| Capability | Default roles |
|---|---|
| `mod/attendancecontrol:addinstance` | Manager, Teacher |
| `mod/attendancecontrol:viewsummary` | Manager, Teacher |
| `mod/attendancecontrol:recordattendance` | Teacher |
| `mod/attendancecontrol:viewownattendance` | Student |
| `mod/attendancecontrol:export` | Manager, Teacher |
| `mod/attendancecontrol:managesessions` | Manager, Teacher |

---

## Testing

The plugin includes automated test coverage:

- **PHPUnit** — `tests/attendance_calculator_test.php` (4 tests), `tests/session_manager_test.php` (7 tests)
- **Behat** — `tests/behat/mod_attendancecontrol.feature` (7 scenarios covering teacher flow, student isolation, export, and session management)

Run PHPUnit from your Moodle root:

```bash
vendor/bin/phpunit --testsuite mod_attendancecontrol
```

Run Behat:

```bash
php admin/tool/behat/cli/run.php --tags=@mod_attendancecontrol
```

---

## Support

- Report bugs or request features via the [GitHub issue tracker](https://github.com/david-olivan/moodle-asistencia/issues).
- For general questions, use the [Moodle plugin discussion forum](https://moodle.org/plugins).

---

## Changelog

See [CHANGES.md](CHANGES.md) for the full version history.

---

## License

This plugin is free software: you can redistribute it and/or modify it under the terms of the **GNU General Public License** as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See [LICENSE.md](LICENSE.md) for the full licence text.
