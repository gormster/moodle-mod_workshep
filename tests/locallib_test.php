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
 * Unit tests for workshep api class defined in mod/workshep/locallib.php
 *
 * @package    mod_workshep
 * @category   phpunit
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshep/locallib.php'); // Include the code to test
require_once(__DIR__ . '/fixtures/testable.php');


/**
 * Test cases for the internal workshep api
 */
class mod_workshep_internal_api_testcase extends advanced_testcase {

    /** workshep instance emulation */
    protected $workshep;

    /** setup testing environment */
    protected function setUp() {
        parent::setUp();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshep = $this->getDataGenerator()->create_module('workshep', array('course' => $course));
        $cm = get_coursemodule_from_instance('workshep', $workshep->id, $course->id, false, MUST_EXIST);
        $this->workshep = new testable_workshep($workshep, $cm, $course);
    }

    protected function tearDown() {
        $this->workshep = null;
        parent::tearDown();
    }

    public function test_aggregate_submission_grades_process_notgraded() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => null);
        //$DB->expectNever('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_single() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.12345);
        $expected = 10.12345;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_null_doesnt_influence() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => 45.54321);
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 1, 'grade' => null);
        $expected = 45.54321;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_weighted_single() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 12, 'submissiongrade' => null, 'weight' => 4, 'grade' => 14.00012);
        $expected = 14.00012;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 0.00000);
        $expected = 19.67750;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean_changed() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 12.57750, 'weight' => 1, 'grade' => 0.00000);
        $expected = 19.67750;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_mean_nochange() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 56.12000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 12.59000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => 19.67750, 'weight' => 1, 'grade' => 0.00000);
        //$DB->expectNever('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_rounding() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 4.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 2.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 1.00000);
        $expected = 2.33333;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_submission_grades_process_weighted_mean() {
        $this->resetAfterTest(true);

        // fixture set-up
        $batch = array();   // batch of a submission's assessments
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 3, 'grade' => 12.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 2, 'grade' => 30.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 1, 'grade' => 10.00000);
        $batch[] = (object)array('submissionid' => 45, 'submissiongrade' => null, 'weight' => 0, 'grade' => 1000.00000);
        $expected = 17.66667;
        //$DB->expectOnce('update_record');
        // exercise SUT
        $this->workshep->aggregate_submission_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_nograding() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>2, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        // expectation
        //$DB->expectNever('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_new() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>82.87670, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        // expectation
        $now = time();
        $expected = new stdclass();
        $expected->workshepid = $this->workshep->id;
        $expected->userid = 3;
        $expected->gradinggrade = 82.87670;
        $expected->timegraded = $now;
        //$DB->expectOnce('insert_record', array('workshep_aggregations', $expected));
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch, $now);
    }

    public function test_aggregate_grading_grades_process_single_grade_update() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>90.00000, 'gradinggradeover'=>null, 'aggregationid'=>1, 'aggregatedgrade'=>82.87670);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_uptodate() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>3, 'gradinggrade'=>90.00000, 'gradinggradeover'=>null, 'aggregationid'=>1, 'aggregatedgrade'=>90.00000);
        // expectation
        //$DB->expectNever('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_single_grade_overridden() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>4, 'gradinggrade'=>91.56700, 'gradinggradeover'=>82.32105, 'aggregationid'=>2, 'aggregatedgrade'=>91.56700);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_new() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>99.45670, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>null, 'aggregatedgrade'=>null);
        // expectation
        $now = time();
        $expected = new stdclass();
        $expected->workshepid = $this->workshep->id;
        $expected->userid = 5;
        $expected->gradinggrade = 79.3066;
        $expected->timegraded = $now;
        //$DB->expectOnce('insert_record', array('workshep_aggregations', $expected));
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch, $now);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_update() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>56.23400, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>79.30660);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_overriden() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>56.23400, 'gradinggradeover'=>99.45670, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>87.34311, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
        $batch[] = (object)array('reviewerid'=>5, 'gradinggrade'=>51.12000, 'gradinggradeover'=>null, 'aggregationid'=>2, 'aggregatedgrade'=>64.89904);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_one_missing() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>50.00000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>null, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>52.20000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_aggregate_grading_grades_process_multiple_grades_missing_overridden() {
        $this->resetAfterTest(true);
        // fixture set-up
        $batch = array();
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>50.00000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>null, 'gradinggradeover'=>69.00000, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        $batch[] = (object)array('reviewerid'=>6, 'gradinggrade'=>52.20000, 'gradinggradeover'=>null, 'aggregationid'=>3, 'aggregatedgrade'=>100.00000);
        // expectation
        //$DB->expectOnce('update_record');
        // excersise SUT
        $this->workshep->aggregate_grading_grades_process($batch);
    }

    public function test_percent_to_value() {
        $this->resetAfterTest(true);
        // fixture setup
        $total = 185;
        $percent = 56.6543;
        // exercise SUT
        $part = workshep::percent_to_value($percent, $total);
        // verify
        $this->assertEquals($part, $total * $percent / 100);
    }

    public function test_percent_to_value_negative() {
        $this->resetAfterTest(true);
        // fixture setup
        $total = 185;
        $percent = -7.098;
        // set expectation
        $this->setExpectedException('coding_exception');
        // exercise SUT
        $part = workshep::percent_to_value($percent, $total);
    }

    public function test_percent_to_value_over_hundred() {
        $this->resetAfterTest(true);
        // fixture setup
        $total = 185;
        $percent = 121.08;
        // set expectation
        $this->setExpectedException('coding_exception');
        // exercise SUT
        $part = workshep::percent_to_value($percent, $total);
    }

    public function test_lcm() {
        $this->resetAfterTest(true);
        // fixture setup + exercise SUT + verify in one step
        $this->assertEquals(workshep::lcm(1,4), 4);
        $this->assertEquals(workshep::lcm(2,4), 4);
        $this->assertEquals(workshep::lcm(4,2), 4);
        $this->assertEquals(workshep::lcm(2,3), 6);
        $this->assertEquals(workshep::lcm(6,4), 12);
    }

    public function test_lcm_array() {
        $this->resetAfterTest(true);
        // fixture setup
        $numbers = array(5,3,15);
        // excersise SUT
        $lcm = array_reduce($numbers, 'workshep::lcm', 1);
        // verify
        $this->assertEquals($lcm, 15);
    }

    public function test_prepare_example_assessment() {
        $this->resetAfterTest(true);
        // fixture setup
        $fakerawrecord = (object)array(
            'id'                => 42,
            'submissionid'      => 56,
            'weight'            => 0,
            'timecreated'       => time() - 10,
            'timemodified'      => time() - 5,
            'grade'             => null,
            'gradinggrade'      => null,
            'gradinggradeover'  => null,
            'feedbackauthor'    => null,
            'feedbackauthorformat' => 0,
            'feedbackauthorattachment' => 0,
        );
        // excersise SUT
        $a = $this->workshep->prepare_example_assessment($fakerawrecord);
        // verify
        $this->assertTrue($a instanceof workshep_example_assessment);
        $this->assertTrue($a->url instanceof moodle_url);

        // modify setup
        $fakerawrecord->weight = 1;
        $this->setExpectedException('coding_exception');
        // excersise SUT
        $a = $this->workshep->prepare_example_assessment($fakerawrecord);
    }

    public function test_prepare_example_reference_assessment() {
        global $USER;
        $this->resetAfterTest(true);
        // fixture setup
        $fakerawrecord = (object)array(
            'id'                => 38,
            'submissionid'      => 56,
            'weight'            => 1,
            'timecreated'       => time() - 100,
            'timemodified'      => time() - 50,
            'grade'             => 0.75000,
            'gradinggrade'      => 1.00000,
            'gradinggradeover'  => null,
            'feedbackauthor'    => null,
            'feedbackauthorformat' => 0,
            'feedbackauthorattachment' => 0,
        );
        // excersise SUT
        $a = $this->workshep->prepare_example_reference_assessment($fakerawrecord);
        // verify
        $this->assertTrue($a instanceof workshep_example_reference_assessment);

        // modify setup
        $fakerawrecord->weight = 0;
        $this->setExpectedException('coding_exception');
        // excersise SUT
        $a = $this->workshep->prepare_example_reference_assessment($fakerawrecord);
    }

    /**
     * Test normalizing list of extensions.
     */
    public function test_normalize_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame(['.odt'], workshep::normalize_file_extensions('odt'));
        $this->assertSame(['.odt'], workshep::normalize_file_extensions('.odt'));
        $this->assertSame(['.odt'], workshep::normalize_file_extensions('.ODT'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshep::normalize_file_extensions('doc, jpg, mp3'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshep::normalize_file_extensions(['.doc', '.jpg', '.mp3']));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshep::normalize_file_extensions('doc, *.jpg, mp3'));
        $this->assertSame(['.doc', '.jpg', '.mp3'], workshep::normalize_file_extensions(['doc ', ' JPG ', '.mp3']));
        $this->assertSame(['.rtf', '.pdf', '.docx'], workshep::normalize_file_extensions("RTF,.pdf\n...DocX,,,;\rPDF\trtf ...Rtf"));
        $this->assertSame(['.tgz', '.tar.gz'], workshep::normalize_file_extensions('tgz,TAR.GZ tar.gz .tar.gz tgz TGZ'));
        $this->assertSame(['.notebook'], workshep::normalize_file_extensions('"Notebook":notebook;NOTEBOOK;,\'NoTeBook\''));
        $this->assertSame([], workshep::normalize_file_extensions(''));
        $this->assertSame([], workshep::normalize_file_extensions([]));
        $this->assertSame(['.0'], workshep::normalize_file_extensions(0));
        $this->assertSame(['.0'], workshep::normalize_file_extensions('0'));
        $this->assertSame(['.odt'], workshep::normalize_file_extensions('*.odt'));
        $this->assertSame([], workshep::normalize_file_extensions('.'));
        $this->assertSame(['.foo'], workshep::normalize_file_extensions('. foo'));
        $this->assertSame([], workshep::normalize_file_extensions('*'));
        $this->assertSame([], workshep::normalize_file_extensions('*~'));
        $this->assertSame(['.pdf', '.ps'], workshep::normalize_file_extensions('* pdf *.ps foo* *bar .r??'));
    }

    /**
     * Test cleaning list of extensions.
     */
    public function test_clean_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame('', workshep::clean_file_extensions(''));
        $this->assertSame('', workshep::clean_file_extensions(null));
        $this->assertSame('', workshep::clean_file_extensions(' '));
        $this->assertSame('0', workshep::clean_file_extensions(0));
        $this->assertSame('0', workshep::clean_file_extensions('0'));
        $this->assertSame('doc, rtf, pdf', workshep::clean_file_extensions('*.Doc, RTF, PDF, .rtf'.PHP_EOL.'PDF '));
        $this->assertSame('doc, rtf, pdf', 'doc, rtf, pdf');
    }

    /**
     * Test validation of the list of file extensions.
     */
    public function test_invalid_file_extensions() {
        $this->resetAfterTest(true);

        $this->assertSame([], workshep::invalid_file_extensions('', ''));
        $this->assertSame([], workshep::invalid_file_extensions('', '.doc'));
        $this->assertSame([], workshep::invalid_file_extensions('odt', ''));
        $this->assertSame([], workshep::invalid_file_extensions('odt', '*'));
        $this->assertSame([], workshep::invalid_file_extensions('odt', 'odt'));
        $this->assertSame([], workshep::invalid_file_extensions('doc, odt, pdf', ['pdf', 'doc', 'odt']));
        $this->assertSame([], workshep::invalid_file_extensions(['doc', 'odt', 'PDF'], ['.doc', '.pdf', '.odt']));
        $this->assertSame([], workshep::invalid_file_extensions('*~ .docx, Odt PDF :doc .pdf', '*.docx *.odt *.pdf *.doc'));
        $this->assertSame(['.00001-wtf-is-this'], workshep::invalid_file_extensions('docx tgz .00001-wtf-is-this', 'tgz docx'));
        $this->assertSame(['.foobar', '.wtfisthis'], workshep::invalid_file_extensions(['.pdf', '.foobar', 'wtfisthis'], 'pdf'));
        $this->assertSame([], workshep::invalid_file_extensions('', ''));
        $this->assertSame(['.odt'], workshep::invalid_file_extensions(['.PDF', 'PDF', '.ODT'], 'jpg pdf png gif'));
        $this->assertSame(['.odt'], workshep::invalid_file_extensions(['.PDF', 'PDF', '.ODT'], '.jpg,.pdf,  .png .gif'));
        $this->assertSame(['.exe', '.bat'], workshep::invalid_file_extensions(['.exe', '.odt', '.bat', ''], 'odt'));
    }

    /**
     * Test checking file name against the list of allowed extensions.
     */
    public function test_is_allowed_file_type() {
        $this->resetAfterTest(true);

        $this->assertTrue(workshep::is_allowed_file_type('README.txt', ''));
        $this->assertTrue(workshep::is_allowed_file_type('README.txt', ['']));
        $this->assertFalse(workshep::is_allowed_file_type('README.txt', '0'));

        $this->assertFalse(workshep::is_allowed_file_type('README.txt', 'xt'));
        $this->assertFalse(workshep::is_allowed_file_type('README.txt', 'old.txt'));

        $this->assertTrue(workshep::is_allowed_file_type('README.txt', 'txt'));
        $this->assertTrue(workshep::is_allowed_file_type('README.txt', '.TXT'));
        $this->assertTrue(workshep::is_allowed_file_type('README.TXT', 'txt'));
        $this->assertTrue(workshep::is_allowed_file_type('README.txt', '.txt .md'));
        $this->assertTrue(workshep::is_allowed_file_type('README.txt', 'HTML TXT DOC RTF'));
        $this->assertTrue(workshep::is_allowed_file_type('README.txt', ['HTML', '...TXT', 'DOC', 'RTF']));

        $this->assertTrue(workshep::is_allowed_file_type('C:\Moodle\course-data.tar.gz', 'gzip zip 7z tar.gz'));
        $this->assertFalse(workshep::is_allowed_file_type('C:\Moodle\course-data.tar.gz', 'gzip zip 7z tar'));
        $this->assertTrue(workshep::is_allowed_file_type('~/course-data.tar.gz', 'gzip zip 7z gz'));
        $this->assertFalse(workshep::is_allowed_file_type('~/course-data.tar.gz', 'gzip zip 7z'));

        $this->assertFalse(workshep::is_allowed_file_type('Alice on the beach.jpg.exe', 'png gif jpg bmp'));
        $this->assertFalse(workshep::is_allowed_file_type('xfiles.exe.jpg', 'exe com bat sh'));
        $this->assertFalse(workshep::is_allowed_file_type('solution.odt~', 'odt, xls'));
        $this->assertTrue(workshep::is_allowed_file_type('solution.odt~', 'odt, odt~'));
    }
}
