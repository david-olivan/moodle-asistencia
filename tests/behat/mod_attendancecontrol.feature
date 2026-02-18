@mod @mod_attendancecontrol
Feature: Control de Asistencia – flujos principales
  Como profesor
  Quiero configurar y registrar asistencia
  Para llevar un control preciso de la asistencia del alumnado

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | FP Test  | FP001     |
    And the following "users" exist:
      | username  | firstname | lastname  | email                     |
      | teacher1  | Ana       | García    | teacher1@example.com      |
      | student1  | Carlos    | Martínez  | student1@example.com      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | FP001  | editingteacher |
      | student1 | FP001  | student        |
    And the following "groups" exist:
      | name       | course |
      | DAM1-Alumnos | FP001 |
    And the following "group members" exist:
      | user     | group        |
      | student1 | DAM1-Alumnos |

  @javascript
  Scenario: El profesor añade la actividad al curso
    Given I log in as "teacher1"
    And I am on "FP Test" course homepage with editing mode on
    When I add a "Control de Asistencia" to section "1"
    And I set the following fields to these values:
      | Nombre de la actividad | Asistencia Programación |
      | Grupo de alumnos       | DAM1-Alumnos            |
      | Horas totales          | 100                     |
    And I press "Guardar cambios y regresar al curso"
    Then I should see "Asistencia Programación"

  @javascript
  Scenario: El alumno ve su resumen de asistencia
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "student1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "Mi asistencia"
    And I should not see "Registrar asistencia"

  @javascript
  Scenario: El profesor registra asistencia para una sesión
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "teacher1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "Registrar asistencia hoy"
    And I should see "Ver datos completos"
