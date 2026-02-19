# Mister Asistencia — mod_attendancecontrol

Plugin de Moodle para el control de asistencia de alumnos en centros de formación profesional.

**Autor**: David Oliván Malagón (misterdavs)
**Tipo**: Módulo de actividad (`mod`)
**Plataforma**: Moodle 4.5 LTS
**Licencia**: GPL v3+
**Idioma**: Español

---

## Descripción

**Mister Asistencia** (`mod_attendancecontrol`) es un módulo de actividad ligero y configurable que resuelve las limitaciones del plugin estándar `mod_attendance`: exceso de información innecesaria, reportes difíciles de interpretar y escasa adaptabilidad al flujo docente real.

### Roles y capacidades

| Rol | Qué puede hacer |
|---|---|
| **Profesor / Gestor** | Registrar asistencia diaria, consultar el estado del grupo con porcentajes, exportar datos a Excel y gestionar sesiones |
| **Alumno** | Consultar únicamente su propia información de asistencia (solo lectura) |

---

## Principios de diseño

- **Simplicidad** — el flujo diario del profesor requiere el mínimo número de clics.
- **Claridad** — los datos son inmediatamente interpretables sin formación previa.
- **Configurabilidad** — cada instancia se adapta a la asignatura: horario semanal, festivos y ratios de penalización por retrasos o faltas justificadas.
- **Mejores prácticas Moodle** — API nativa de Moodle, Mustache templates, AMD modules, PHPUnit y Behat.

---

## Requisitos

- Moodle 4.5 LTS o superior
- PHP compatible con la versión de Moodle seleccionada
- Soporte exclusivo vía web (sin app móvil)

---

## Instalación

1. Copiar la carpeta del plugin en `<moodle_root>/mod/attendancecontrol/`.
2. Acceder como administrador y completar el proceso de actualización de base de datos.
3. Añadir la actividad **Mister Asistencia** a cualquier curso desde "Añadir una actividad o recurso".

---

## Desarrollo

El proyecto sigue la [Moodle Plugin Development Guide](https://moodledev.io/docs/apis). Consulta el documento `PRD_mod_attendance_kings.md` para la especificación completa de requisitos, modelo de datos y arquitectura técnica.
