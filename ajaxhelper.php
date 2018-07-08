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
 * Ajax helper for Read Aloud
 *
 *
 * @package    mod_NEWMODULE
 * @copyright  Justin Hunt (justin@poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_NEWMODULE\constants;

$cmid = required_param('cmid',  PARAM_INT); // course_module ID, or
//$sessionid = required_param('sessionid',  PARAM_INT); // course_module ID, or
$filename= required_param('filename',  PARAM_TEXT); // data baby yeah
$ret =new stdClass();

if ($cmid) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $cmid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $themodule  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $ret->success=false;
    $ret->message="You must specify a course_module ID or an instance ID";
    return json_encode($ret);
}

require_login($course, false, $cm);
$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

//make database items and adhoc tasks
$success = false;
$attemptid = save_to_moodle($filename, $themodule);
if($attemptid){
    if(\mod_NEWMODULE\utils::can_transcribe($themodule)) {
        $success = register_aws_task($themodule->id, $attemptid, $modulecontext->id);
        if(!$success){
            $message = "Unable to create adhoc task";
        }
    }else{
        $success = true;
    }
}else{
    $message = "Unable to add update database with submission";
}

//handle return to Moodle
$ret =new stdClass();
if($success){
    $ret->success=true;
}else{
    $ret->success=false;
    $ret->message=$message;
}
echo json_encode($ret);
return;

//save the data to Moodle.
function save_to_moodle($filename,$themodule){
    global $USER,$DB;

    //Add a blank attempt with just the filename  and essential details
    $newattempt = new stdClass();
    $newattempt->courseid=$themodule->course;
    $newattempt->NEWMODULEid=$themodule->id;
    $newattempt->userid=$USER->id;
    $newattempt->status=0;
    $newattempt->filename=$filename;
    $newattempt->sessionscore=0;
    $newattempt->timecreated=time();
    $newattempt->timemodified=time();
    $attemptid = $DB->insert_record(constants::M_USERTABLE,$newattempt);
    if(!$attemptid){
        return false;
    }
    return $attemptid;
}

//register an adhoc task to pick up transcripts
function register_aws_task($activityid, $attemptid,$modulecontextid){
    $s3_task = new \mod_NEWMODULE\task\s3_adhoc();
    $s3_task->set_component(constants::M_FRANKY);

    $customdata = new \stdClass();
    $customdata->activityid = $activityid;
    $customdata->attemptid = $attemptid;
    $customdata->modulecontextid = $modulecontextid;

    $s3_task->set_custom_data($customdata);
    // queue it
    \core\task\manager::queue_adhoc_task($s3_task);
    return true;
}