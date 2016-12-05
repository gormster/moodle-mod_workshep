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
 * Steps definitions related to mod_workshep.
 *
 * @package    mod_workshep
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

/**
 * Steps definitions related to mod_workshep.
 *
 * @package    mod_workshep
 * @category   test
 * @copyright  2014 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_workshep extends behat_base {
    /**
     * Changes the submission phase for the workshep.
     *
     * @When /^I change phase in workshep "(?P<workshep_name_string>(?:[^"]|\\")*)" to "(?P<phase_name_string>(?:[^"]|\\")*)"$/
     * @param string $questiontype
     * @param string $workshepname
     */
    public function i_change_phase_in_workshep_to($workshepname, $phase) {
        $workshepname = $this->escape($workshepname);
        $phaseliteral = behat_context_helper::escape($phase);
        $switchphase = behat_context_helper::escape(get_string('switchphase', 'workshep'));

        $xpath = "//*[@class='userplan']/descendant::div[./span[contains(.,$phaseliteral)]]/".
                "descendant-or-self::a[./img[@alt=$switchphase]]";
        $continue = $this->escape(get_string('continue'));

        $this->execute('behat_general::click_link', $workshepname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));

        $this->execute("behat_forms::press_button", $continue);
    }

    /**
     * Adds or edits a student workshep submission.
     *
     * @When /^I add a submission in workshep "(?P<workshep_name_string>(?:[^"]|\\")*)" as:"$/
     * @param string $workshepname
     * @param TableNode $table data to fill the submission form with, must contain 'Title'
     */
    public function i_add_a_submission_in_workshep_as($workshepname, $table) {
        $workshepname = $this->escape($workshepname);
        $savechanges = $this->escape(get_string('savechanges'));
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' ownsubmission ')]/descendant::input[@type='submit']";

        $this->execute('behat_general::click_link', $workshepname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $savechanges);
    }

    /**
     * Sets the workshep assessment form.
     *
     * @When /^I edit assessment form in workshep "(?P<workshep_name_string>(?:[^"]|\\")*)" as:"$/
     * @param string $workshepname
     * @param TableNode $table data to fill the submission form with, must contain 'Title'
     */
    public function i_edit_assessment_form_in_workshep_as($workshepname, $table) {
        $workshepname = $this->escape($workshepname);
        $editassessmentform = $this->escape(get_string('editassessmentform', 'workshep'));
        $saveandclose = $this->escape(get_string('saveandclose', 'workshep'));

        $this->execute('behat_general::click_link', $workshepname);

        $this->execute('behat_general::click_link', $editassessmentform);

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $saveandclose);
    }

    /**
     * Peer-assesses a workshep submission.
     *
     * @When /^I assess submission "(?P<submission_string>(?:[^"]|\\")*)" in workshep "(?P<workshep_name_string>(?:[^"]|\\")*)" as:"$/
     * @param string $submission
     * @param string $workshepname
     * @param TableNode $table
     */
    public function i_assess_submission_in_workshep_as($submission, $workshepname, TableNode $table) {
        $workshepname = $this->escape($workshepname);
        $submissionliteral = behat_context_helper::escape($submission);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' assessment-summary ') ".
                "and contains(.,$submissionliteral)]";
        $assess = $this->escape(get_string('assess', 'workshep'));
        $saveandclose = $this->escape(get_string('saveandclose', 'workshep'));

        $this->execute('behat_general::click_link', $workshepname);

        $this->execute('behat_general::i_click_on_in_the',
            array($assess, "button", $xpath, "xpath_element")
        );

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $saveandclose);
    }

    /**
     * Checks that the user has particular grade set by his reviewing peer in workshep
     *
     * @Then /^I should see grade "(?P<grade_string>[^"]*)" for workshep participant "(?P<participant_name_string>(?:[^"]|\\")*)" set by peer "(?P<reviewer_name_string>(?:[^"]|\\")*)"$/
     * @param string $grade
     * @param string $participant
     * @param string $reviewer
     */
    public function i_should_see_grade_for_workshep_participant_set_by_peer($grade, $participant, $reviewer) {
        $participantliteral = behat_context_helper::escape($participant);
        $reviewerliteral = behat_context_helper::escape($reviewer);
        $gradeliteral = behat_context_helper::escape($grade);
        $participantselector = "contains(concat(' ', normalize-space(@class), ' '), ' participant ') ".
                "and contains(.,$participantliteral)";
        $trxpath = "//table/tbody/tr[td[$participantselector]]";
        $tdparticipantxpath = "//table/tbody/tr/td[$participantselector]";
        $tdxpath = "/td[contains(concat(' ', normalize-space(@class), ' '), ' receivedgrade ') and contains(.,$reviewerliteral)]/".
                "descendant::span[contains(concat(' ', normalize-space(@class), ' '), ' grade ') and .=$gradeliteral]";

        $tr = $this->find('xpath', $trxpath);
        $rowspan = $this->find('xpath', $tdparticipantxpath)->getAttribute('rowspan');

        $xpath = $trxpath.$tdxpath;
        if (!empty($rowspan)) {
            for ($i = 1; $i < $rowspan; $i++) {
                $xpath .= ' | '.$trxpath."/following-sibling::tr[$i]".$tdxpath;
            }
        }
        $this->find('xpath', $xpath);
    }
}
