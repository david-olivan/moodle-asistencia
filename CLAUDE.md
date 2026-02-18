# CLAUDE.md — Development Tracker

This file tracks implementation progress against the PRD (`PRD_mod_attendance_kings.md`).

## How to use this file

When starting a new implementation task:
1. Read **only** from the `PRD resume line` onward in the PRD — everything above it is already implemented.
2. Mark sections as `[x]` when done and update `PRD resume line` to the first line of the next unimplemented section.

---

## PRD resume line: 40

> Start reading the PRD from line **40** (`## 2. Arquitectura Técnica`).
> Lines 1–37 are fully covered by existing deliverables.

---

## Implementation status

### Información del Proyecto (PRD lines 1–14) — `[x] Done`
- Covered by `README.md`: plugin name, type, platform, language, author, license.

### 1. Visión General (PRD lines 17–37) — `[x] Done`
- **1.1 Problema** — described in README.
- **1.2 Solución** — roles and capabilities table in README.
- **1.3 Principios de Diseño** — design principles section in README.

### 2. Arquitectura Técnica (PRD lines 40–169) — `[ ] Pending`
- 2.1 Tipo de Plugin
- 2.2 Estructura de Archivos
- 2.3 Modelo de Datos (5 tables)
- 2.4 Capabilities

### 3. Flujos de Usuario (PRD lines 172–271) — `[ ] Pending`
- 3.1 Configuración Inicial
- 3.2 Flujo Diario del Profesor (view, attendance, report, student_detail)
- 3.3 Flujo del Alumno

### 4. Lógica de Negocio (PRD lines 274–339) — `[ ] Pending`
- 4.1 Generación de Sesiones
- 4.2 Edición de Sesiones Futuras
- 4.3 Cálculo del Porcentaje de Asistencia
- 4.4 Estados de Asistencia

### 5. Exportación de Datos (PRD lines 342–372) — `[ ] Pending`
- 5.1 Exportación a Excel (3 hojas: Resumen, Detalle, Configuración)

### 6. Requisitos Técnicos (PRD lines 375–398) — `[ ] Pending`
- 6.1 Estándares de Desarrollo Moodle
- 6.2 Requisitos de Testing (PHPUnit + Behat)
- 6.3 Rendimiento

### 7. Interfaz de Usuario (PRD lines 401–487) — `[ ] Pending`
- 7.1 Principios UI
- 7.2 Wireframes Descriptivos (4 vistas)

### 8. Gestión de Sesiones (PRD lines 490–516) — `[ ] Pending`
- 8.1 Generación Automática
- 8.2 Edición Manual de Sesiones Futuras
- 8.3 Regeneración por Cambio de Configuración

### 9. Consideraciones de Privacidad (PRD lines 519–534) — `[ ] Pending`
- 9.1 Acceso a Datos
- 9.2 Privacy API

### 10. Fuera de Alcance v1 (PRD lines 537–548) — `[ ] Noted`
- No implementar: app móvil, gradebook, notificaciones, migración, multi-idioma, etc.

### 11. Criterios de Aceptación (PRD lines 551–597) — `[ ] Pending`
- 11.1 Configuración
- 11.2 Registro de Asistencia
- 11.3 Resumen y Cálculos
- 11.4 Vista del Alumno
- 11.5 Exportación
- 11.6 Gestión de Sesiones

### 12. Glosario (PRD lines 600–610) — `[ ] Pending`

---

## Technical Debt

- **License conflict**: `LICENSE.md` is currently "All Rights Reserved" (private project). The PRD specifies GPL v3+, which is mandatory for any plugin distributed through the Moodle plugin directory. If distribution is ever planned, `LICENSE.md` must be replaced with GPL v3+ and the license header added to every source file.
