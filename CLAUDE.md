# CLAUDE.md — Development Tracker

This file tracks implementation progress against the PRD (`PRD_mod_attendance_kings.md`).

## How to use this file

When starting a new implementation task:
1. Read **only** from the `PRD resume line` onward in the PRD — everything above it is already implemented.
2. Mark sections as `[x]` when done and update `PRD resume line` to the first line of the next unimplemented section.

---

## PRD resume line: 611

> All PRD sections (1–12) are fully covered. No further implementation required.

---

## Implementation status

### Información del Proyecto (PRD lines 1–14) — `[x] Done`
- Covered by `README.md`: plugin name, type, platform, language, author, license.

### 1. Visión General (PRD lines 17–37) — `[x] Done`
- **1.1 Problema** — described in README.
- **1.2 Solución** — roles and capabilities table in README.
- **1.3 Principios de Diseño** — design principles section in README.

### 2. Arquitectura Técnica (PRD lines 40–169) — `[x] Done`
- 2.1 Tipo de Plugin — `mod` activity, confirmed in `version.php` and `lib.php`.
- 2.2 Estructura de Archivos — all 30+ files present per PRD spec.
- 2.3 Modelo de Datos (5 tables) — `db/install.xml` defines all 5 tables with correct fields/keys/indexes.
- 2.4 Capabilities — `db/access.php` defines all 6 capabilities with correct archetypes.

### 3. Flujos de Usuario (PRD lines 172–271) — `[x] Done`
- 3.1 Configuración Inicial — `mod_form.php` covers group, date range, schedule slots, holidays, penalty config.
- 3.2 Flujo Diario del Profesor — `view.php` (session list + week nav), `attendance.php` (per-session form), `report.php` (summary table), `student_detail.php`.
- 3.3 Flujo del Alumno — `view.php` dispatches to `renderer::render_student_view()` → `student_summary.mustache`.

### 4. Lógica de Negocio (PRD lines 274–339) — `[x] Done`
- 4.1 Generación de Sesiones — `session_manager::generate_sessions()` iterates date range, skips holidays.
- 4.2 Edición de Sesiones Futuras — `session_manager::regenerate_future_sessions()` preserves sessions with records.
- 4.3 Cálculo del Porcentaje de Asistencia — `attendance_calculator::compute_attendance_pct()`.
- 4.4 Estados de Asistencia — 5 statuses (0–4) used throughout records, form, templates, export.

### 5. Exportación de Datos (PRD lines 342–372) — `[x] Done`
- 5.1 Exportación a Excel — `export_manager::send_excel()` streams 3-sheet workbook (Resumen, Detalle, Configuración).

### 6. Requisitos Técnicos (PRD lines 375–398) — `[x] Done`
- 6.1 Estándares de Desarrollo Moodle — GPL v3 headers, namespaces, XMLDB, capabilities, privacy API, AMD.
- 6.2 Requisitos de Testing — `tests/attendance_calculator_test.php` (4 tests), `tests/session_manager_test.php` (7 tests), `tests/behat/mod_attendancecontrol.feature` (7 scenarios).
- 6.3 Rendimiento — Single-query aggregation in `attendance_calculator::build_student_summary()`.

### 7. Interfaz de Usuario (PRD lines 401–487) — `[x] Done`
- 7.1 Principios UI — Bootstrap 5 classes, responsive tables, accessible badges.
- 7.2 Wireframes Descriptivos — 4 Mustache templates: `session_list`, `summary_table`, `student_detail`, `student_summary`.

### 8. Gestión de Sesiones (PRD lines 490–516) — `[x] Done`
- 8.1 Generación Automática — triggered from `attendancecontrol_add_instance()` in `lib.php`.
- 8.2 Edición Manual de Sesiones Futuras — `regenerate_future_sessions()` called from `attendancecontrol_update_instance()`.
- 8.3 Regeneración por Cambio de Configuración — schedule/holiday re-save + regenerate on update.

### 9. Consideraciones de Privacidad (PRD lines 519–534) — `[x] Done`
- 9.1 Acceso a Datos — capability checks on every page; students can only view their own data.
- 9.2 Privacy API — `classes/privacy/provider.php` implements metadata, context discovery, export, and deletion (GDPR).

### 10. Fuera de Alcance v1 (PRD lines 537–548) — `[x] Noted`
- No implementar: app móvil, gradebook, notificaciones, migración, multi-idioma, etc.

### 11. Criterios de Aceptación (PRD lines 551–597) — `[x] Done`
- 11.1 Configuración — covered by Behat scenarios 1, 3 (add activity, session generation) + `test_generate_sessions_*` + `test_compute_duration_hours`.
- 11.2 Registro de Asistencia — covered by `test_save_attendance_records_inserts_new` (AC 11.2.5), `test_save_attendance_records_updates_existing` (AC 11.2.6), `test_save_attendance_records_retroactive` (AC 11.2.7); Behat scenario 3 (teacher sees register button).
- 11.3 Resumen y Cálculos — covered by `test_prd_example_calculation` (formula, AC 11.3.3 formula), `test_student_below_threshold` (AC 11.3.3 red flag), `test_get_student_detail_returns_rows` (AC 11.3.4).
- 11.4 Vista del Alumno — covered by Behat scenarios 2 (student sees own summary, AC 11.4.1/11.4.4), 4 (student isolation, AC 11.4.3), 6 (breakdown link, AC 11.4.2).
- 11.5 Exportación — covered by Behat scenario 5 (export button visible to teacher, AC 11.5.1).
- 11.6 Gestión de Sesiones — covered by `test_regenerate_future_sessions_preserves_sessions_with_records` (AC 11.6.3) + existing `test_generate_sessions_*`.

### 12. Glosario (PRD lines 600–610) — `[x] Noted`
- Informational only; definitions are reflected in code comments, lang strings, and template contexts throughout the plugin.

---

## Technical Debt

- **License conflict**: `LICENSE.md` is currently "All Rights Reserved" (private project). The PRD specifies GPL v3+, which is mandatory for any plugin distributed through the Moodle plugin directory. If distribution is ever planned, `LICENSE.md` must be replaced with GPL v3+ and the license header added to every source file.
