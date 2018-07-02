<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */

namespace mod_NEWMODULE\report;

use \mod_NEWMODULE\constants;

class grading extends basereport
{

    protected $report = "grading";
    protected $fields = array('id', 'username', 'mediafile', 'totalattempts', 'grade_p', 'gradenow', 'timecreated', 'deletenow');
    protected $headingdata = null;
    protected $qcache = array();
    protected $ucache = array();


    public function fetch_formatted_field($field, $record, $withlinks)
    {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'id':
                $ret = $record->id;
                break;

            case 'username':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = fullname($user);
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                        array('action' => 'gradingbyuser', 'n' => $record->NEWMODULEid, 'userid' => $record->userid));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'totalattempts':
                $ret = $record->totalattempts;
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php',
                        array('action' => 'gradingbyuser', 'n' => $record->NEWMODULEid, 'userid' => $record->userid));
                    $ret = \html_writer::link($link, $ret);
                }
                break;

            case 'mediafile':
                if ($withlinks) {
                    $ret = \html_writer::div('<i class="fa fa-play-circle"></i>', constants::M_HIDDEN_PLAYER_BUTTON, array('data-audiosource' => $record->mediaurl));

                } else {
                    $ret = get_string('submitted', constants::M_LANG);
                }
                break;


            case 'grade_p':
                $ret = $record->sessionscore;
                break;

            case 'gradenow':
                if ($withlinks) {
                    $link = new \moodle_url(constants::M_URL . '/grading.php', array('action' => 'gradenow', 'n' => $record->NEWMODULEid, 'attemptid' => $record->id));
                    $ret = \html_writer::link($link, get_string('gradenow', constants::M_LANG));
                } else {
                    $ret = get_string('cannotgradenow', constants::M_LANG);
                }
                break;



            case 'timecreated':
                $ret = date("Y-m-d H:i:s", $record->timecreated);
                break;

            case 'deletenow':
                $url = new \moodle_url(constants::M_URL . '/manageattempts.php',
                    array('action' => 'delete', 'n' => $record->NEWMODULEid, 'attemptid' => $record->id, 'source' => $this->report));
                $btn = new \single_button($url, get_string('delete'), 'post');
                $btn->add_confirm_action(get_string('deleteattemptconfirm', constants::M_LANG));
                $ret = $OUTPUT->render($btn);
                break;

            default:
                if (property_exists($record, $field)) {
                    $ret = $record->{$field};
                } else {
                    $ret = '';
                }
        }
        return $ret;

    } //end of function


    public function fetch_formatted_heading()
    {
        $record = $this->headingdata;
        $ret = '';
        if (!$record) {
            return $ret;
        }
        //$ec = $this->fetch_cache(constants::M_TABLE,$record->englishcentralid);
        return get_string('gradingheading', constants::M_LANG);

    }//end of function

    public function process_raw_data($formdata)
    {
        global $DB;

        //heading data
        $this->headingdata = new \stdClass();

        $emptydata = array();
        $user_attempt_totals = array();
        $alldata = $DB->get_records(constants::M_USERTABLE, array('NEWMODULEid' => $formdata->NEWMODULEid), 'id DESC, userid');

        if ($alldata) {

            foreach ($alldata as $thedata) {

                //we ony take the most recent attempt
                if (array_key_exists($thedata->userid, $user_attempt_totals)) {
                    $user_attempt_totals[$thedata->userid] = $user_attempt_totals[$thedata->userid] + 1;
                    continue;
                }
                $user_attempt_totals[$thedata->userid] = 1;

                $thedata->mediaurl = $thedata->filename;
                $this->rawdata[] = $thedata;
            }
            foreach ($this->rawdata as $thedata) {
                $thedata->totalattempts = $user_attempt_totals[$thedata->userid];
            }
        } else {
            $this->rawdata = $emptydata;
        }
        return true;
    }//end of function
}//end of class