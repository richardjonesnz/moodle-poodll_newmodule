<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 20:52
 */

namespace mod_NEWMODULE\report;

use \mod_NEWMODULE\constants;

class attempts extends basereport
{

    protected $report="attempts";
    protected $fields = array('id','username','mediafile','grade_p','timecreated','deletenow');
    protected $headingdata = null;
    protected $qcache=array();
    protected $ucache=array();


    public function fetch_formatted_field($field,$record,$withlinks)
    {
        global $DB, $CFG, $OUTPUT;
        switch ($field) {
            case 'id':
                $ret = $record->id;
                break;

            case 'username':
                $user = $this->fetch_cache('user', $record->userid);
                $ret = fullname($user);
                break;

            case 'mediafile':
                if ($withlinks) {

                    $ret = \html_writer::div('<i class="fa fa-play-circle"></i>',
                        constants::M_HIDDEN_PLAYER_BUTTON, array('data-audiosource' => $record->mediaurl));

                } else {
                    $ret = get_string('submitted', constants::M_LANG);
                }
                break;
                break;

            case 'grade_p':
                $ret = $record->sessionscore;
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
    }

    public function fetch_formatted_heading(){
        $record = $this->headingdata;
        $ret='';
        if(!$record){return $ret;}
        //$ec = $this->fetch_cache(constants::M_TABLE,$record->englishcentralid);
        return get_string('attemptsheading',constants::M_LANG);

    }

    public function process_raw_data($formdata){
        global $DB;

        //heading data
        $this->headingdata = new \stdClass();

        $emptydata = array();
        $alldata = $DB->get_records(constants::M_USERTABLE,array('NEWMODULEid'=>$formdata->NEWMODULEid));

        if($alldata){
            foreach($alldata as $thedata){
                $thedata->mediaurl  = $thedata->filename;
                $this->rawdata[] = $thedata;
            }
            $this->rawdata= $alldata;
        }else{
            $this->rawdata= $emptydata;
        }
        return true;
    }

}