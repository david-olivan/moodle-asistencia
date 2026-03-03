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
      | name         | course | idnumber     |
      | DAM1-Alumnos | FP001  | DAM1-Alumnos |
    And the following "group members" exist:
      | user     | group        |
      | student1 | DAM1-Alumnos |

  @javascript
  Scenario: El profesor añade la actividad al curso
    Given I log in as "teacher1"
    And I am on "FP Test" course homepage with editing mode on
    When I add a "Mister Asistencia" activity to section "1"
    And I set the following fields to these values:
      | Name               | Asistencia Programación |
      | Student group      | DAM1-Alumnos            |
      | Total subject hours | 100                    |
    And I press "Save changes and return to course"
    Then I should see "Asistencia Programación"

  @javascript
  Scenario: El alumno ve su resumen de asistencia
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "student1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "My attendance"
    And I should not see "Record attendance"

  @javascript
  Scenario: El profesor registra asistencia para una sesión
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "teacher1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "Record today's attendance"
    And I should see "View full data"

  @javascript
  Scenario: El alumno solo ve sus propios datos y no puede editar
    # AC 11.4.3 — student cannot see another student's data.
    # AC 11.4.4 — student cannot edit any data.
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    And the following "users" exist:
      | username  | firstname | lastname | email                |
      | student2  | Laura     | López    | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student2 | FP001  | student |
    When I log in as "student1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "My attendance"
    And I should not see "Laura"
    And I should not see "Record attendance"
    And I should not see "Save attendance"

  @javascript
  Scenario: El profesor ve el botón de exportación en la página de resumen
    # AC 11.5.1 — export button is accessible to teachers.
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "teacher1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    And I follow "View full data"
    Then I should see "Export to Excel"

  @javascript
  Scenario: El alumno puede navegar al desglose de sus propias sesiones
    # AC 11.4.2 — student can navigate to their own session breakdown.
    Given the following "activities" exist:
      | activity           | course | name                    | groupid | total_hours |
      | attendancecontrol  | FP001  | Asistencia Programación | 0       | 100         |
    When I log in as "student1"
    And I am on "FP Test" course homepage
    And I follow "Asistencia Programación"
    Then I should see "My attendance"
    And I follow "View session breakdown"
    Then I should see "Session breakdown"
