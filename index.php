<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints the list of all worksheps in the course
 *
 * @package    mod_workshep
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/workshep/index.php', array('id' => $course->id));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('modulenameplural', 'workshep'));

/// Output starts here

echo $OUTPUT->header();

$params = array('context' => context_course::instance($course->id));
$event = \mod_workshep\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

/// Get all the appropriate data

if (! $worksheps = get_all_instances_in_course('workshep', $course)) {
    echo $OUTPUT->heading(get_string('modulenameplural', 'workshep'));
    notice(get_string('noworksheps', 'workshep'), new moodle_url('/course/view.php', array('id' => $course->id)));
    echo $OUTPUT->footer();
    die();
}

$usesections = course_format_uses_sections($course->format);

$timenow        = time();
$strname        = get_string('name');
$table          = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname);
    $table->align = array ('center', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left');
}

foreach ($worksheps as $workshep) {
    if (empty($workshep->visible)) {
        $link = html_writer::link(new moodle_url('/mod/workshep/view.php', array('id' => $workshep->coursemodule)),
                                  $workshep->name, array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(new moodle_url('/mod/workshep/view.php', array('id' => $workshep->coursemodule)),
                                  $workshep->name);
    }

    if ($usesections) {
        $table->data[] = array(get_section_name($course, $workshep->section), $link);
    } else {
        $table->data[] = array($link);
    }
}
echo $OUTPUT->heading(get_string('modulenameplural', 'workshep'), 3);
echo html_writer::table($table);
echo $OUTPUT->footer();
