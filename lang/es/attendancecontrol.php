<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Spanish language strings for mod_attendancecontrol.
 *
 * @package    mod_attendancecontrol
 * @copyright  2026 Kings Corner Formación Profesional
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ── Plugin identity ──────────────────────────────────────────────────────────
$string['modulename']        = 'Control de Asistencia';
$string['modulenameplural']  = 'Controles de Asistencia';
$string['modulename_help']   = 'El módulo Control de Asistencia permite registrar la asistencia diaria del alumnado, consultar resúmenes con porcentajes y exportar los datos a Excel.';
$string['pluginname']        = 'Control de Asistencia';
$string['pluginadministration'] = 'Administración de Control de Asistencia';

// ── mod_form: secciones y campos ─────────────────────────────────────────────
$string['group']                   = 'Grupo de alumnos';
$string['group_help']              = 'Selecciona el grupo de Moodle cuyos miembros formarán parte de la lista de asistencia.';
$string['daterange']               = 'Rango de fechas del curso';
$string['coursestartdate']         = 'Fecha de inicio';
$string['courseenddate']           = 'Fecha de fin';
$string['totalhours']              = 'Horas totales de la asignatura';
$string['schedule']                = 'Franjas horarias semanales';
$string['dayofweek']               = 'Día de la semana';
$string['starttime']               = 'Hora de inicio (HH:MM)';
$string['endtime']                 = 'Hora de fin (HH:MM)';
$string['addslot']                 = 'Añadir otra franja horaria';
$string['holidays']                = 'Festivos';
$string['holidaydate']             = 'Fecha del festivo';
$string['holidaydescription']      = 'Descripción (opcional)';
$string['addholiday']              = 'Añadir festivo';
$string['penaltyconfig']           = 'Configuración de penalización';
$string['maxunjustifiedpct']       = '% máximo de faltas permitido';
$string['maxunjustifiedpct_help']  = 'Porcentaje máximo de horas equivalentes de falta injustificada sobre el total de horas. Por encima de este umbral el alumno se muestra en rojo.';
$string['delayratio']              = 'Ratio retraso → falta injustificada';
$string['delayratio_help']         = 'Un retraso cuenta como esta fracción de una hora de falta injustificada. Ejemplo: 0,5 significa que un retraso de 1 hora cuenta como 0,5 horas de falta injustificada.';
$string['justifiedratio']          = 'Ratio falta justificada → falta injustificada';
$string['justifiedratio_help']     = 'Una falta justificada cuenta como esta fracción de una hora de falta injustificada. Ejemplo: 0,5 significa que 1 hora justificada cuenta como 0,5 horas de falta injustificada.';

// ── Validation errors ────────────────────────────────────────────────────────
$string['err_enddatebeforestart'] = 'La fecha de fin debe ser posterior a la de inicio.';
$string['err_hourspositive']      = 'Las horas totales deben ser un número positivo.';
$string['err_invalidratio']       = 'El valor debe estar entre 0 y 100.';

// ── view.php ─────────────────────────────────────────────────────────────────
$string['registerattendancetoday'] = 'Registrar asistencia hoy';
$string['viewfulldata']            = 'Ver datos completos';
$string['sessionsthisweek']        = 'Sesiones de esta semana';
$string['previousweek']           = '◄ Semana anterior';
$string['nextweek']               = 'Semana siguiente ►';
$string['nostudentview']          = 'No tienes permisos para ver esta actividad.';

// ── Session list ─────────────────────────────────────────────────────────────
$string['sessiondate']      = 'Fecha';
$string['sessionschedule']  = 'Horario';
$string['sessionstatus']    = 'Estado';
$string['statuspending']    = 'Pendiente';
$string['statusrecorded']   = 'Registrada';

// ── attendance.php ───────────────────────────────────────────────────────────
$string['recordattendance']  = 'Registrar asistencia';
$string['sessionheading']    = 'Sesión: {$a}';
$string['markallpresent']    = 'Marcar todos como:';
$string['studentname']       = 'Alumno/a';
$string['statuspresent']     = 'Presente';
$string['statuslate']        = 'Retraso';
$string['statusjustified']   = 'F. Justificada';
$string['statusunjustified'] = 'F. Injustificada';
$string['remarks']           = 'Observaciones';
$string['saveattendance']    = 'Guardar asistencia';
$string['attendancesaved']   = 'Asistencia guardada correctamente.';

// ── report.php ───────────────────────────────────────────────────────────────
$string['reporttitle']          = 'Resumen de asistencia';
$string['presences']            = 'Presencias';
$string['lates']                = 'Retrasos';
$string['justifiedabsences']    = 'F. Justificadas';
$string['unjustifiedabsences']  = 'F. Injustificadas';
$string['equivalenthours']      = 'H. Equiv. Falta';
$string['attendancepct']        = '% Asistencia';
$string['exportexcel']          = 'Exportar a Excel';
$string['thresholdnotice']      = 'Umbral configurado: {$a->threshold}% ({$a->pct}% de faltas máximo sobre {$a->hours}h)';

// ── student_detail.php ───────────────────────────────────────────────────────
$string['studentdetailtitle']    = 'Detalle de asistencia';
$string['myattendance']          = 'Mi asistencia';
$string['viewbreakdown']         = 'Ver desglose por sesión';
$string['alertthresholdreached'] = '¡Atención! Has superado el umbral máximo de faltas permitido.';
$string['sessionbreakdown']      = 'Desglose por sesión';

// ── export.php ───────────────────────────────────────────────────────────────
$string['excel_sheet_summary']       = 'Resumen';
$string['excel_sheet_detail']        = 'Detalle por sesión';
$string['excel_sheet_config']        = 'Configuración';
$string['excel_col_student']         = 'Alumno/a';
$string['excel_col_date']            = 'Fecha';
$string['excel_col_schedule']        = 'Horario';
$string['excel_col_duration']        = 'Duración (h)';
$string['excel_col_status']          = 'Estado';
$string['excel_col_remarks']         = 'Observaciones';
$string['excel_col_equivhours']      = 'Horas equiv. falta';
$string['excel_col_pct']             = '% Asistencia';
$string['excel_param_subject']       = 'Asignatura';
$string['excel_param_group']         = 'Grupo';
$string['excel_param_totalhours']    = 'Horas totales';
$string['excel_param_delayratio']    = 'Ratio retraso';
$string['excel_param_justifiedratio']= 'Ratio falta justificada';
$string['excel_param_maxpct']        = '% máximo faltas permitido';

// ── Capabilities (shown in role administration) ──────────────────────────────
$string['attendancecontrol:addinstance']       = 'Añadir una instancia de Control de Asistencia';
$string['attendancecontrol:viewsummary']       = 'Ver resumen de asistencia del grupo';
$string['attendancecontrol:recordattendance']  = 'Registrar y editar asistencia';
$string['attendancecontrol:viewownattendance'] = 'Ver la propia asistencia';
$string['attendancecontrol:export']            = 'Exportar datos de asistencia a Excel';
$string['attendancecontrol:managesessions']    = 'Gestionar sesiones futuras';

// ── Events ───────────────────────────────────────────────────────────────────
$string['eventattendancerecorded']   = 'Asistencia registrada';
$string['eventcoursemodudeviewed']   = 'Control de asistencia visto';
$string['eventsessioncreated']       = 'Sesión creada';

// ── Privacy ──────────────────────────────────────────────────────────────────
$string['privacy:metadata:attendancecontrol_record']              = 'Almacena el registro de asistencia de cada alumno por sesión.';
$string['privacy:metadata:attendancecontrol_record:userid']       = 'El ID del usuario (alumno) al que corresponde el registro.';
$string['privacy:metadata:attendancecontrol_record:status']       = 'Estado de asistencia (presente, retraso, justificada, injustificada).';
$string['privacy:metadata:attendancecontrol_record:remarks']      = 'Observaciones introducidas por el profesor.';
$string['privacy:metadata:attendancecontrol_record:recorded_by']  = 'ID del profesor que registró la asistencia.';
$string['privacy:metadata:attendancecontrol_record:timecreated']  = 'Marca de tiempo de creación del registro.';
$string['privacy:metadata:attendancecontrol_record:timemodified'] = 'Marca de tiempo de última modificación.';
