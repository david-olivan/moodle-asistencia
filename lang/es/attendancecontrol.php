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
 * @copyright  2026 David Oliván Malagón
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addholiday'] = 'Añadir festivo';
$string['addslot'] = 'Añadir otra franja horaria';
$string['alertthresholdreached'] = '¡Atención! Has superado el umbral máximo de faltas permitido.';
$string['attendancecontrol:addinstance'] = 'Añadir una instancia de Control de Asistencia';
$string['attendancecontrol:export'] = 'Exportar datos de asistencia a Excel';
$string['attendancecontrol:managesessions'] = 'Gestionar sesiones futuras';
$string['attendancecontrol:recordattendance'] = 'Registrar y editar asistencia';
$string['attendancecontrol:viewownattendance'] = 'Ver la propia asistencia';
$string['attendancecontrol:viewsummary'] = 'Ver resumen de asistencia del grupo';
$string['attendancepct'] = '% Asistencia';
$string['attendancesaved'] = 'Asistencia guardada correctamente.';
$string['courseenddate'] = 'Fecha de fin';
$string['coursestartdate'] = 'Fecha de inicio';
$string['daterange'] = 'Rango de fechas del curso';
$string['dayofweek'] = 'Día de la semana';
$string['delayratio'] = '¿Cuántos retrasos equivalen a 1 falta injustificada?';
$string['delayratio_help'] = 'Número de retrasos de una hora necesarios para sumar 1 hora de falta injustificada. Ejemplo: 2 significa que hacen falta 2 retrasos de 1 hora para acumular 1 hora de falta injustificada.';
$string['endtime'] = 'Hora de fin';
$string['equivalenthours'] = 'H. Equiv. Falta';
$string['err_enddatebeforestart'] = 'La fecha de fin debe ser posterior a la de inicio.';
$string['err_hourspositive'] = 'Las horas totales deben ser un número positivo.';
$string['err_invalidratio'] = 'El valor debe estar entre 0 y 100.';
$string['eventattendancerecorded'] = 'Asistencia registrada';
$string['eventcoursemodudeviewed'] = 'Control de asistencia visto';
$string['eventsessioncreated'] = 'Sesión creada';
$string['excel_col_date'] = 'Fecha';
$string['excel_col_duration'] = 'Duración (h)';
$string['excel_col_equivhours'] = 'Horas equiv. falta';
$string['excel_col_pct'] = '% Asistencia';
$string['excel_col_remarks'] = 'Observaciones';
$string['excel_col_schedule'] = 'Horario';
$string['excel_col_status'] = 'Estado';
$string['excel_col_student'] = 'Alumno/a';
$string['excel_param_delayratio'] = 'Ratio retraso';
$string['excel_param_group'] = 'Grupo';
$string['excel_param_justifiedratio'] = 'Ratio falta justificada';
$string['excel_param_maxpct'] = '% máximo faltas permitido';
$string['excel_param_subject'] = 'Asignatura';
$string['excel_param_totalhours'] = 'Horas totales';
$string['excel_sheet_config'] = 'Configuración';
$string['excel_sheet_detail'] = 'Detalle por sesión';
$string['excel_sheet_summary'] = 'Resumen';
$string['exportexcel'] = 'Exportar a Excel';
$string['group'] = 'Grupo de alumnos';
$string['group_help'] = 'Selecciona el grupo de Moodle cuyos miembros formarán parte de la lista de asistencia.';
$string['holidaydate'] = 'Fecha del festivo';
$string['holidaydescription'] = 'Descripción (opcional)';
$string['holidays'] = 'Festivos';
$string['justifiedabsences'] = 'F. Justificadas';
$string['justifiedratio'] = '¿Cuántas faltas justificadas equivalen a 1 falta injustificada?';
$string['justifiedratio_help'] = 'Número de horas de falta justificada necesarias para sumar 1 hora de falta injustificada. Ejemplo: 2 significa que hacen falta 2 horas justificadas para acumular 1 hora de falta injustificada.';
$string['lates'] = 'Retrasos';
$string['markallpresent'] = 'Marcar todos';
$string['maxunjustifiedpct'] = '% máximo de faltas permitido';
$string['maxunjustifiedpct_help'] = 'Porcentaje máximo de faltas injustificadas equivalentes sobre el total de horas de la asignatura (1 %–50 %). Por encima de este umbral el alumno se muestra en rojo.';
$string['modulename'] = 'Mister Asistencia';
$string['modulename_help'] = 'Mister Asistencia permite registrar la asistencia diaria del alumnado, consultar resúmenes con porcentajes y exportar los datos a Excel.';
$string['modulenameplural'] = 'Mister Asistencia';
$string['myattendance'] = 'Mi asistencia';
$string['nextweek'] = 'Semana siguiente ►';
$string['nosessionsthisweek'] = 'No hay sesiones programadas para esta semana.';
$string['nostudentview'] = 'No tienes permisos para ver esta actividad.';
$string['penaltyconfig'] = 'Configuración de penalización';
$string['pluginadministration'] = 'Administración de Mister Asistencia';
$string['pluginname'] = 'Mister Asistencia';
$string['presences'] = 'Presencias';
$string['previousweek'] = '◄ Semana anterior';
$string['privacy:metadata:attendancecontrol_record'] = 'Almacena el registro de asistencia de cada alumno por sesión.';
$string['privacy:metadata:attendancecontrol_record:recorded_by'] = 'ID del profesor que registró la asistencia.';
$string['privacy:metadata:attendancecontrol_record:remarks'] = 'Observaciones introducidas por el profesor.';
$string['privacy:metadata:attendancecontrol_record:status'] = 'Estado de asistencia (presente, retraso, justificada, injustificada).';
$string['privacy:metadata:attendancecontrol_record:timecreated'] = 'Marca de tiempo de creación del registro.';
$string['privacy:metadata:attendancecontrol_record:timemodified'] = 'Marca de tiempo de última modificación.';
$string['privacy:metadata:attendancecontrol_record:userid'] = 'El ID del usuario (alumno) al que corresponde el registro.';
$string['recordattendance'] = 'Registrar asistencia';
$string['registerattendancetoday'] = 'Registrar asistencia hoy';
$string['remarks'] = 'Observaciones';
$string['reporttitle'] = 'Resumen de asistencia';
$string['saveattendance'] = 'Guardar asistencia';
$string['schedule'] = 'Franjas horarias semanales';
$string['sessionbreakdown'] = 'Desglose por sesión';
$string['sessiondate'] = 'Fecha';
$string['sessionheading'] = 'Sesión: {$a}';
$string['sessionschedule'] = 'Horario';
$string['sessionstatus'] = 'Estado';
$string['sessionsthisweek'] = 'Sesiones de esta semana';
$string['starttime'] = 'Hora de inicio';
$string['statusjustified'] = 'F. Justificada';
$string['statuslate'] = 'Retraso';
$string['statuspending'] = 'Pendiente';
$string['statuspresent'] = 'Presente';
$string['statusrecorded'] = 'Registrada';
$string['statusunjustified'] = 'F. Injustificada';
$string['studentdetailtitle'] = 'Detalle de asistencia';
$string['studentname'] = 'Alumno/a';
$string['thresholdnotice'] = 'Umbral configurado: {$a->threshold}% ({$a->pct}% de faltas máximo sobre {$a->hours}h)';
$string['totalhours'] = 'Horas totales de la asignatura';
$string['unjustifiedabsences'] = 'F. Injustificadas';
$string['viewbreakdown'] = 'Ver desglose por sesión';
$string['viewfulldata'] = 'Ver datos completos';
