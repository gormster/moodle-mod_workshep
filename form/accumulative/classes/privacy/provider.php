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
 * Provides the class {@link workshepform_accumulative\privacy\provider}
 *
 * @package     workshepform_accumulative
 * @category    privacy
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace workshepform_accumulative\privacy;

use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy API implementation for the Accumulative grading strategy.
 *
 * @copyright 2018 David Mudrák <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider, \mod_workshep\privacy\workshepform_provider {

    /**
     * Explain that this plugin stores no personal data.
     *
     * @return string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

    /**
     * Return details of the filled assessment form.
     *
     * @param stdClass $user User we are exporting data for
     * @param context $context The workshep activity context
     * @param array $subcontext Subcontext within the context to export to
     * @param int $assessmentid ID of the assessment
     */
    public static function export_assessment_form(\stdClass $user, \context $context, array $subcontext, int $assessmentid) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Unexpected context provided');
        }

        $sql = "SELECT dim.id, dim.description, dim.descriptionformat, dim.grade AS dimgrade, dim.weight,
                       wg.grade, wg.peercomment, wg.peercommentformat
                  FROM {course_modules} cm
                  JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
                  JOIN {workshep} w ON cm.instance = w.id
                  JOIN {workshepform_accumulative} dim ON dim.workshepid = w.id
             LEFT JOIN {workshep_grades} wg ON wg.strategy = :strategy
                       AND wg.dimensionid = dim.id AND wg.assessmentid = :assessmentid
                 WHERE ctx.id = :contextid
              ORDER BY dim.sort";

        $params = [
            'strategy' => 'accumulative',
            'contextlevel' => CONTEXT_MODULE,
            'contextid' => $context->id,
            'assessmentid' => $assessmentid,
        ];

        $writer = \core_privacy\local\request\writer::with_context($context);
        $data = [];
        $hasdata = false;
        $dimensionids = [];

        foreach ($DB->get_records_sql($sql, $params) as $record) {
            if ($record->grade !== null) {
                $hasdata = true;
            }
            $record->description = $writer->rewrite_pluginfile_urls($subcontext, 'workshepform_accumulative',
                'description', $record->id, $record->description);
            $dimensionids[] = $record->id;
            unset($record->id);
            $data[] = $record;
        }

        if ($hasdata) {
            $writer->export_data($subcontext, (object) ['aspects' => $data]);
            foreach ($dimensionids as $dimensionid) {
                $writer->export_area_files($subcontext, 'workshepform_accumulative', 'description', $dimensionid);
            }
        }
    }
}