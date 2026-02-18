# PRD: Plugin de Control de Asistencia para Moodle

## Información del Proyecto

| Campo | Valor |
|---|---|
| **Nombre del plugin** | `mod_attendancecontrol` |
| **Tipo** | Módulo de actividad (mod) |
| **Plataforma** | Moodle 4.5 LTS (última versión estable) |
| **Idioma** | Español (es) |
| **Soporte** | Solo web (sin soporte app móvil) |
| **Autor** | Kings Corner Formación Profesional |
| **Licencia** | GPL v3+ (requerido por Moodle) |

---

## 1. Visión General

### 1.1 Problema

El plugin de asistencia actual (`mod_attendance`) recoge demasiada información innecesaria, genera reportes difíciles de interpretar y carece de configurabilidad para adaptarse a las necesidades reales del día a día docente en un centro de formación profesional.

### 1.2 Solución

Un módulo de actividad de Moodle ligero, configurable e interpretable que permita:

- A los **profesores**: registrar asistencia diaria de forma rápida e intuitiva, consultar el estado de asistencia de todo el grupo con porcentajes claros y exportar datos.
- A los **alumnos**: consultar exclusivamente su propia información de asistencia (solo lectura).
- A los **gestores**: mismas capacidades que los profesores.

### 1.3 Principios de Diseño

- **Simplicidad**: el flujo diario del profesor debe requerir el mínimo número de clics posible.
- **Claridad**: los datos mostrados deben ser inmediatamente interpretables sin formación previa.
- **Configurabilidad**: cada instancia se adapta a la asignatura concreta (horas, horarios, ratios de penalización).
- **Mejores prácticas Moodle**: seguir estrictamente la Moodle Plugin Development Guide, usar la API de Moodle, Mustache templates, AMD modules, PHPUnit y Behat.

---

## 2. Arquitectura Técnica

### 2.1 Tipo de Plugin

Módulo de actividad (`mod`), se añade al curso como cualquier otra actividad (Tarea, Foro, etc.) desde "Añadir una actividad o recurso".

### 2.2 Estructura de Archivos (Moodle Plugin Standard)

```
mod/attendancecontrol/
├── db/
│   ├── install.xml              # Definición de tablas
│   ├── upgrade.php              # Migraciones
│   ├── access.php               # Definición de capabilities
│   └── services.php             # Servicios externos (si aplica)
├── classes/
│   ├── event/                   # Eventos del plugin
│   ├── output/                  # Renderizadores
│   ├── form/                    # Formularios Moodle (moodleform)
│   └── local/                   # Lógica de negocio
│       ├── session_manager.php
│       ├── attendance_calculator.php
│       └── export_manager.php
├── templates/                   # Plantillas Mustache
│   ├── session_list.mustache
│   ├── attendance_form.mustache
│   ├── summary_table.mustache
│   └── student_detail.mustache
├── amd/src/                     # JavaScript AMD modules
│   ├── attendance_form.js
│   └── session_navigation.js
├── lang/
│   └── es/
│       └── attendancecontrol.php
├── pix/
│   └── monologo.svg             # Icono del módulo
├── backup/
│   └── moodle2/                 # Backup & restore handlers
├── lib.php                      # Callbacks estándar del módulo
├── mod_form.php                 # Formulario de configuración de la instancia
├── view.php                     # Página principal al acceder a la actividad
├── attendance.php               # Página de registro de asistencia por sesión
├── report.php                   # Vista de datos completos / resumen
├── student_detail.php           # Vista detalle de un alumno
├── export.php                   # Exportación a Excel
├── index.php                    # Lista de instancias en el curso
├── version.php                  # Versión del plugin
└── settings.php                 # Configuración global (si aplica)
```

### 2.3 Modelo de Datos

#### Tabla `attendancecontrol` (instancia del módulo)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | ID de la instancia |
| `course` | INT FK | ID del curso de Moodle |
| `name` | VARCHAR(255) | Nombre de la actividad |
| `intro` | TEXT | Descripción (estándar Moodle) |
| `introformat` | INT | Formato del intro |
| `groupid` | INT FK | ID del grupo de Moodle al que aplica |
| `total_hours` | INT | Horas totales de la asignatura |
| `course_start_date` | DATE | Fecha de inicio del curso |
| `course_end_date` | DATE | Fecha de fin del curso |
| `max_unjustified_absence_pct` | DECIMAL(5,2) | Porcentaje máximo de faltas injustificadas permitido (ej: 15.00) |
| `delay_to_unjustified_ratio` | DECIMAL(5,2) | Ratio de conversión retraso → falta injustificada (ej: 0.50 = un retraso vale como media falta injustificada) |
| `justified_to_unjustified_ratio` | DECIMAL(5,2) | Ratio de conversión falta justificada → falta injustificada (ej: 0.50 = una justificada vale como media injustificada) |
| `timecreated` | INT | Timestamp de creación |
| `timemodified` | INT | Timestamp de última modificación |

#### Tabla `attendancecontrol_schedule` (franjas horarias semanales)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `attendancecontrolid` | INT FK | Instancia del módulo |
| `day_of_week` | TINYINT | Día de la semana (1=lunes, 7=domingo) |
| `start_time` | TIME | Hora de inicio de la franja |
| `end_time` | TIME | Hora de fin de la franja |

#### Tabla `attendancecontrol_holiday` (festivos configurados)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `attendancecontrolid` | INT FK | Instancia del módulo |
| `holiday_date` | DATE | Fecha del festivo |
| `description` | VARCHAR(255) | Descripción del festivo (opcional) |

#### Tabla `attendancecontrol_session` (sesiones generadas)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `attendancecontrolid` | INT FK | Instancia del módulo |
| `session_date` | DATE | Fecha de la sesión |
| `start_time` | TIME | Hora de inicio |
| `end_time` | TIME | Hora de fin |
| `duration_hours` | INT | Duración redondeada hacia arriba a la hora completa |
| `status` | TINYINT | 0 = pendiente, 1 = registrada |
| `timecreated` | INT | — |
| `timemodified` | INT | — |

#### Tabla `attendancecontrol_record` (registro de asistencia por alumno y sesión)

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `sessionid` | INT FK | Sesión |
| `userid` | INT FK | ID del usuario (alumno) |
| `status` | TINYINT | 0 = sin registrar, 1 = presente, 2 = retraso, 3 = falta justificada, 4 = falta injustificada |
| `remarks` | TEXT | Observaciones (campo de texto libre) |
| `recorded_by` | INT FK | ID del usuario que registró |
| `timecreated` | INT | — |
| `timemodified` | INT | — |

### 2.4 Capabilities (Permisos)

Definidas en `db/access.php`:

| Capability | Descripción | Profesor | Gestor | Prof. sin edición | Alumno |
|---|---|---|---|---|---|
| `mod/attendancecontrol:addinstance` | Añadir la actividad al curso | ✅ | ✅ | ❌ | ❌ |
| `mod/attendancecontrol:viewsummary` | Ver resumen completo de todos los alumnos | ✅ | ✅ | ✅ | ❌ |
| `mod/attendancecontrol:recordattendance` | Registrar y editar asistencia | ✅ | ✅ | ❌ | ❌ |
| `mod/attendancecontrol:viewownattendance` | Ver la propia asistencia | ❌ | ❌ | ❌ | ✅ |
| `mod/attendancecontrol:export` | Exportar datos a Excel | ✅ | ✅ | ✅ | ❌ |
| `mod/attendancecontrol:managesessions` | Editar/añadir/eliminar sesiones futuras | ✅ | ✅ | ❌ | ❌ |

---

## 3. Flujos de Usuario

### 3.1 Configuración Inicial (Profesor/Gestor)

**Trigger**: El profesor añade "Control de Asistencia" al curso.

**Formulario de configuración** (`mod_form.php`):

1. **Nombre de la actividad** (texto, obligatorio).
2. **Grupo de Moodle** (selector desplegable con los grupos del curso, obligatorio). Se selecciona el grupo cuyos miembros serán los alumnos a los que se pasa asistencia.
3. **Fecha de inicio del curso** (selector de fecha).
4. **Fecha de fin del curso** (selector de fecha).
5. **Horas totales de la asignatura** (número entero, obligatorio).
6. **Horarios semanales** (sección repetible):
   - Día de la semana (selector: Lunes a Viernes).
   - Hora de inicio (selector de hora).
   - Hora de fin (selector de hora).
   - Botón "Añadir otra franja" para múltiples franjas el mismo día o en diferentes días.
7. **Festivos** (selector múltiple de fechas dentro del rango inicio-fin):
   - Interfaz sencilla: un datepicker donde se van añadiendo fechas festivas.
   - Cada festivo permite una descripción opcional (ej: "Día del Pilar").
8. **Configuración de penalización**:
   - Porcentaje máximo de faltas injustificadas permitido (ej: 15%).
   - Ratio retraso → falta injustificada (ej: 0.5 = un retraso cuenta como media falta injustificada).
   - Ratio falta justificada → falta injustificada (ej: 0.5 = una justificada cuenta como media injustificada).

**Al guardar**: el sistema genera automáticamente todas las sesiones entre la fecha de inicio y la de fin, según los horarios configurados, excluyendo los festivos. Cada sesión recibe una `duration_hours` calculada como la duración real de la franja redondeada hacia arriba al entero (55 min → 1h, 1h40min → 2h).

### 3.2 Flujo Diario del Profesor

**Trigger**: El profesor pincha en la actividad de asistencia desde la página del curso.

#### Pantalla principal (`view.php`)

El profesor ve dos opciones claras:

- **Registrar asistencia** (acceso directo a las sesiones del día actual).
- **Ver datos completos** (resumen general).

Debajo, se muestra una **lista de sesiones filtrable por semana**, con navegación por semanas (anterior / siguiente / ir a semana concreta). Cada sesión muestra:

- Fecha.
- Horario (inicio - fin).
- Estado: "Pendiente" o "Registrada" (con indicador visual).

Las sesiones del día actual se destacan visualmente.

#### Registro de asistencia (`attendance.php`)

Al pinchar en una sesión:

1. Se carga el **listado de participantes** del grupo de Moodle configurado.
2. Por cada alumno se muestra una fila con:
   - Nombre y apellidos del alumno.
   - **Radio buttons**: Presente | Retraso | F. Justificada | F. Injustificada.
   - **Campo de texto**: Observaciones (colapsado por defecto, se expande al hacer clic).
3. **Encima de la lista**: botón radio "Todos → Presente" que marca todos los alumnos como presentes de un solo clic. El profesor luego modifica las excepciones.
4. **Botón Guardar** al final de la lista.
5. Si la sesión ya fue registrada, se cargan los valores previos y se permite editarlos.
6. Se permite registrar sesiones pasadas de forma retroactiva.

#### Vista de datos completos (`report.php`)

Al pinchar en "Ver datos completos":

1. **Tabla resumen** con una fila por alumno:
   - Nombre y apellidos.
   - Nº de Presencias.
   - Nº de Retrasos.
   - Nº de Faltas Justificadas.
   - Nº de Faltas Injustificadas.
   - **Porcentaje de asistencia** (comienza en 100%, va restando).
2. Las filas donde el porcentaje ha bajado por debajo del umbral configurado se muestran en **rojo**.
3. Al pinchar en un alumno → navega a la **vista detalle del alumno**.
4. **Botón "Exportar a Excel"** que descarga la tabla completa.

#### Vista detalle de alumno (`student_detail.php`)

Al pinchar en un alumno desde la tabla resumen:

1. **Cabecera**: Nombre del alumno, porcentaje de asistencia actual, indicador rojo si ha rebasado el umbral.
2. **Listado de todas las sesiones** de la asignatura con:
   - Fecha y horario.
   - Estado registrado (Presente / Retraso / F. Justificada / F. Injustificada / Sin registrar).
   - Observaciones (si las hay).
3. Código de colores por estado para lectura rápida.

### 3.3 Flujo del Alumno

**Trigger**: El alumno pincha en la actividad de asistencia desde la página del curso.

#### Pantalla del alumno (`view.php` con capability `viewownattendance`)

1. **Resumen personal**:
   - Nº de Presencias, Retrasos, F. Justificadas, F. Injustificadas.
   - Porcentaje de asistencia actual.
   - Indicador rojo si ha rebasado el umbral.
2. **Botón / enlace "Ver desglose"** → lleva al listado de sesiones con su registro individual (equivalente a `student_detail.php` pero solo con sus propios datos).
3. **Sin capacidad de edición** en ningún momento.

---

## 4. Lógica de Negocio

### 4.1 Generación de Sesiones

Al guardar la configuración de la instancia:

1. Iterar desde `course_start_date` hasta `course_end_date`.
2. Para cada día, comprobar si el día de la semana coincide con alguna franja de `attendancecontrol_schedule`.
3. Si el día está en `attendancecontrol_holiday`, no se genera sesión.
4. Para cada franja aplicable, crear un registro en `attendancecontrol_session` con:
   - `duration_hours` = `ceil((end_time - start_time) / 60)` (minutos totales, dividido entre 60, redondeado hacia arriba).

### 4.2 Edición de Sesiones Futuras

- Se pueden **modificar horarios** de sesiones futuras (fecha > hoy).
- Se pueden **añadir o eliminar** sesiones futuras manualmente.
- Las sesiones pasadas con registros **nunca se alteran**.
- Si se modifica el schedule desde la configuración, se regeneran solo las sesiones futuras sin registros.

### 4.3 Cálculo del Porcentaje de Asistencia

El porcentaje representa el nivel de asistencia del alumno, comenzando en 100% y restando conforme se acumulan incidencias.

**Fórmula**:

```
horas_falta_equivalente =
  Σ (por cada registro del alumno):
    si status = retraso:
      duration_hours_sesion × delay_to_unjustified_ratio
    si status = falta_justificada:
      duration_hours_sesion × justified_to_unjustified_ratio
    si status = falta_injustificada:
      duration_hours_sesion × 1.0
    si status = presente o sin_registrar:
      0

porcentaje_asistencia = 100 - (horas_falta_equivalente / total_hours × 100)
```

**Ejemplo con la configuración**:
- Asignatura: 100 horas totales.
- `delay_to_unjustified_ratio` = 0.50.
- `justified_to_unjustified_ratio` = 0.50.
- `max_unjustified_absence_pct` = 15%.

Un alumno tiene:
- 2 retrasos en sesiones de 1h: 2 × 1 × 0.5 = 1 hora equivalente.
- 1 falta justificada en sesión de 4h: 1 × 4 × 0.5 = 2 horas equivalentes.
- 1 falta injustificada en sesión de 2h: 1 × 2 × 1.0 = 2 horas equivalentes.
- Total horas equivalentes: 5.
- Porcentaje: 100 - (5/100 × 100) = **95%**.
- Umbral de alerta: 100% - 15% = 85%. El alumno está por encima → no se muestra en rojo.

**Indicador rojo**: cuando `porcentaje_asistencia` < (100 - `max_unjustified_absence_pct`).

### 4.4 Estados de Asistencia

| Código | Estado | Descripción | Penalización |
|---|---|---|---|
| 0 | Sin registrar | Sesión no registrada aún | Ninguna |
| 1 | Presente | Alumno asistió | Ninguna |
| 2 | Retraso | Alumno llegó tarde | `duration_hours × delay_to_unjustified_ratio` |
| 3 | Falta Justificada | Ausencia con justificante | `duration_hours × justified_to_unjustified_ratio` |
| 4 | Falta Injustificada | Ausencia sin justificante | `duration_hours × 1.0` |

---

## 5. Exportación de Datos

### 5.1 Exportación a Excel

Disponible desde la vista de datos completos (`report.php`). Utiliza la clase `\MoodleExcelWorkbook` estándar de Moodle.

**Contenido del Excel exportado**:

**Hoja 1: Resumen**

| Alumno | Presencias | Retrasos | F. Justificadas | F. Injustificadas | Horas Equiv. Falta | Porcentaje Asistencia |
|---|---|---|---|---|---|---|
| García López, María | 45 | 2 | 1 | 1 | 5 | 95% |

**Hoja 2: Detalle por sesión**

| Alumno | Fecha | Horario | Duración (h) | Estado | Observaciones |
|---|---|---|---|---|---|
| García López, María | 2025-09-15 | 09:00-10:55 | 2 | Presente | — |

**Hoja 3: Configuración**

| Parámetro | Valor |
|---|---|
| Asignatura | Programación |
| Grupo | DAM1-Alumnos |
| Horas totales | 100 |
| Ratio retraso | 0.50 |
| Ratio falta justificada | 0.50 |
| % máximo faltas permitido | 15% |

---

## 6. Requisitos Técnicos

### 6.1 Estándares de Desarrollo Moodle

- **Coding style**: seguir Moodle Coding Style (PHP, JS, CSS).
- **API usage**: utilizar las APIs de Moodle para todo (DB, forms, output, eventos, capabilities).
- **Mustache templates**: toda la presentación HTML mediante templates Mustache con renderers.
- **AMD modules**: todo JavaScript como módulos AMD con RequireJS.
- **Eventos**: registrar eventos de Moodle para logging (`\core\event`) al registrar asistencia, modificar registros, etc.
- **Strings**: todas las cadenas de texto en `lang/es/attendancecontrol.php`.
- **PHPDoc**: documentar todas las clases, métodos y funciones.
- **Backup & Restore**: implementar handlers completos para backup y restauración del curso.
- **Privacy API**: implementar el provider de privacidad de Moodle para que los datos del alumno sean accesibles/exportables/eliminables desde las herramientas de privacidad del sitio.

### 6.2 Requisitos de Testing

- **PHPUnit**: tests unitarios para la lógica de negocio (generación de sesiones, cálculo de porcentajes, conversiones de ratios).
- **Behat**: tests de aceptación para los flujos principales (configurar instancia, registrar asistencia, ver resumen, vista alumno, exportación).

### 6.3 Rendimiento

- Las consultas de resumen deben estar optimizadas para cursos con hasta 200 alumnos y 300 sesiones.
- Usar caching de Moodle (`\cache`) para los cálculos de porcentaje si el rendimiento lo requiere.

---

## 7. Interfaz de Usuario

### 7.1 Principios UI

- Utilizar componentes estándar de Moodle (Bootstrap base del tema, componentes Moodle).
- No introducir frameworks CSS/JS externos.
- Colores semánticos: verde para presente, amarillo/naranja para retraso, azul para justificada, rojo para injustificada.
- Responsive por defecto (hereda del tema de Moodle).

### 7.2 Wireframes Descriptivos

#### Vista principal del profesor

```
┌──────────────────────────────────────────────────┐
│  Control de Asistencia: Programación (DAM1)      │
│                                                  │
│  [📋 Registrar Asistencia Hoy]  [📊 Ver Datos]  │
│                                                  │
│  ◄ Semana 3 (16-20 Sep 2025) ►                   │
│  ┌────────────┬───────────┬──────────┐           │
│  │ Fecha      │ Horario   │ Estado   │           │
│  ├────────────┼───────────┼──────────┤           │
│  │ Lun 16 Sep │ 09:00-11:00│ ✅ Reg. │           │
│  │ Lun 16 Sep │ 16:00-18:00│ ⏳ Pend.│           │
│  │ Mié 18 Sep │ 09:00-11:00│ ⏳ Pend.│           │
│  │ Vie 20 Sep │ 10:00-12:00│ ⏳ Pend.│           │
│  └────────────┴───────────┴──────────┘           │
└──────────────────────────────────────────────────┘
```

#### Registro de asistencia

```
┌──────────────────────────────────────────────────────────────┐
│  Sesión: Lunes 16 Sep 2025 — 09:00 a 11:00 (2h)            │
│                                                              │
│  ○ Marcar todos como: (•) Presente  ○ Retraso  ○ F.J  ○ F.I │
│                                                              │
│  ┌───────────────────┬─────┬─────┬─────┬─────┬────────────┐ │
│  │ Alumno            │  P  │  R  │ F.J │ F.I │ Obs.       │ │
│  ├───────────────────┼─────┼─────┼─────┼─────┼────────────┤ │
│  │ García López, M.  │ (•) │  ○  │  ○  │  ○  │ [+]        │ │
│  │ Martínez Ruiz, J. │  ○  │ (•) │  ○  │  ○  │ [+] 10min  │ │
│  │ Pérez Gómez, A.   │  ○  │  ○  │  ○  │ (•) │ [+]        │ │
│  └───────────────────┴─────┴─────┴─────┴─────┴────────────┘ │
│                                                              │
│  [💾 Guardar asistencia]                                     │
└──────────────────────────────────────────────────────────────┘
```

#### Tabla resumen (datos completos)

```
┌──────────────────────────────────────────────────────────────────────┐
│  Resumen de Asistencia — Programación (DAM1)     [📥 Exportar Excel]│
│                                                                      │
│  ┌───────────────────┬────┬────┬─────┬─────┬────────┬──────────────┐│
│  │ Alumno            │ P  │ R  │ F.J │ F.I │ H.Eq.  │ % Asistencia ││
│  ├───────────────────┼────┼────┼─────┼─────┼────────┼──────────────┤│
│  │ García López, M.  │ 45 │  2 │  1  │  1  │  5.0   │    95.0%     ││
│  │ Martínez Ruiz, J. │ 40 │  5 │  2  │  3  │ 12.5   │    87.5%     ││
│  │ Pérez Gómez, A.   │ 30 │  3 │  0  │ 10  │ 21.5   │ ██ 78.5% ██ ││ ← ROJO
│  └───────────────────┴────┴────┴─────┴─────┴────────┴──────────────┘│
│                                                                      │
│  Umbral configurado: 85% (15% de faltas máximo sobre 100h)          │
└──────────────────────────────────────────────────────────────────────┘
```

#### Vista del alumno

```
┌──────────────────────────────────────────────────────┐
│  Mi Asistencia — Programación                        │
│                                                      │
│  ┌──────────────────────────────────────────────┐    │
│  │ Presencias: 45  │  Retrasos: 2              │    │
│  │ F. Justificadas: 1  │  F. Injustificadas: 1 │    │
│  │                                              │    │
│  │ Porcentaje de asistencia: 95.0%              │    │
│  └──────────────────────────────────────────────┘    │
│                                                      │
│  [🔍 Ver desglose por sesión]                        │
│                                                      │
└──────────────────────────────────────────────────────┘
```

---

## 8. Gestión de Sesiones

### 8.1 Generación Automática

Al crear o actualizar la configuración, las sesiones se generan automáticamente:

- Se crean sesiones para cada combinación de día de semana + franja horaria dentro del rango de fechas.
- Los días marcados como festivos no generan sesiones.
- `duration_hours` se calcula redondeando hacia arriba: `ceil(minutos_totales / 60)`.

### 8.2 Edición Manual de Sesiones Futuras

Accesible desde la vista principal con capability `managesessions`:

- **Modificar** horario de una sesión futura individual.
- **Añadir** una sesión extra fuera del horario regular.
- **Eliminar** una sesión futura (ej: cancelación puntual).
- Las sesiones pasadas con status "registrada" no se pueden eliminar ni modificar en fecha/hora (sí se pueden editar los registros de asistencia de esa sesión).

### 8.3 Regeneración por Cambio de Configuración

Si el profesor edita el schedule o las fechas en la configuración:

- Las sesiones futuras **sin registros** se eliminan y regeneran.
- Las sesiones futuras **con registros** se mantienen intactas.
- Las sesiones pasadas nunca se tocan.

---

## 9. Consideraciones de Privacidad

### 9.1 Acceso a Datos

- Los alumnos solo pueden ver sus propios datos de asistencia. La capability `viewownattendance` garantiza esto.
- Los profesores, gestores y profesores sin permiso de edición pueden ver los datos de todos los alumnos del grupo.
- Los profesores sin permiso de edición no pueden registrar ni modificar asistencia.

### 9.2 Privacy API

Implementar `\core_privacy\provider` para:

- **Metadata**: declarar qué datos personales se almacenan.
- **Export**: exportar los datos de asistencia de un usuario concreto.
- **Delete**: eliminar los datos de asistencia de un usuario concreto.

---

## 10. Fuera de Alcance (v1)

Los siguientes elementos quedan explícitamente fuera del alcance de la primera versión:

- Soporte para la app móvil de Moodle.
- Integración con el libro de calificaciones.
- Notificaciones automáticas (email, push).
- Migración de datos desde `mod_attendance` u otros plugins.
- Multi-idioma (solo español en v1).
- Indicador de color intermedio (amarillo/naranja) previo al umbral.
- Integración con calendarios externos de festivos.
- RGPD avanzado más allá de la Privacy API básica de Moodle.

---

## 11. Criterios de Aceptación

### 11.1 Configuración

- [ ] Se puede añadir la actividad a un curso desde "Añadir una actividad o recurso".
- [ ] El formulario permite configurar todos los campos descritos en la sección 3.1.
- [ ] Se pueden definir múltiples franjas horarias por día con duraciones variables.
- [ ] Los festivos se seleccionan con un datepicker sencillo.
- [ ] Al guardar, se generan las sesiones correctamente, excluyendo festivos.
- [ ] La duración de cada sesión se redondea hacia arriba al entero.

### 11.2 Registro de Asistencia

- [ ] El profesor ve las sesiones del día actual destacadas.
- [ ] El botón "Todos → Presente" marca todos los radio buttons como presente.
- [ ] Se puede registrar cada alumno individualmente con radio buttons.
- [ ] Se pueden añadir observaciones de texto libre por alumno.
- [ ] Se puede guardar y los datos persisten correctamente.
- [ ] Se puede editar un registro ya guardado.
- [ ] Se puede registrar asistencia de sesiones pasadas retroactivamente.

### 11.3 Resumen y Cálculos

- [ ] La tabla resumen muestra los conteos correctos por tipo de asistencia.
- [ ] El porcentaje de asistencia se calcula correctamente según la fórmula definida.
- [ ] Los alumnos por debajo del umbral se muestran en rojo.
- [ ] Al pinchar en un alumno se ve el desglose por sesión.

### 11.4 Vista del Alumno

- [ ] El alumno ve solo su resumen personal al acceder.
- [ ] Puede navegar al desglose por sesión de sus propios datos.
- [ ] No puede ver datos de otros alumnos.
- [ ] No puede editar ningún dato.

### 11.5 Exportación

- [ ] El botón de exportación genera un archivo Excel descargable.
- [ ] El Excel contiene las tres hojas descritas (Resumen, Detalle, Configuración).

### 11.6 Gestión de Sesiones

- [ ] Se pueden editar sesiones futuras.
- [ ] No se pueden modificar fecha/hora de sesiones pasadas registradas.
- [ ] Al cambiar el schedule, solo se regeneran sesiones futuras sin registros.

---

## 12. Glosario

| Término | Definición |
|---|---|
| **Instancia** | Una copia concreta de la actividad dentro de un curso, configurada para un grupo específico. |
| **Sesión** | Un bloque horario concreto en una fecha concreta donde se pasa asistencia. |
| **Franja horaria** | Definición recurrente de un horario semanal (ej: lunes de 9 a 11). |
| **Horas equivalentes de falta** | Resultado de convertir retrasos y faltas justificadas a su equivalente en horas de falta injustificada usando los ratios configurados. |
| **Porcentaje de asistencia** | 100% menos el porcentaje de horas equivalentes de falta sobre el total de horas de la asignatura. |
| **Umbral** | Porcentaje mínimo de asistencia por debajo del cual el alumno se marca en rojo. Calculado como 100% - max_unjustified_absence_pct. |
