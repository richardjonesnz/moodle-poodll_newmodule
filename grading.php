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
 * Reports for NEWMODULE
 *
 *
 * @package    mod_NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use \mod_NEWMODULE\constants;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // NEWMODULE instance ID
$format = optional_param('format', 'html', PARAM_TEXT); //export format csv or html
$action = optional_param('action', 'grading', PARAM_TEXT); // report type
$userid = optional_param('userid', 0, PARAM_INT); // user id
$attemptid = optional_param('attemptid', 0, PARAM_INT); // attemptid

//paging details
$paging = new stdClass();
$paging->perpage = optional_param('perpage',-1, PARAM_INT);
$paging->pageno = optional_param('pageno',0, PARAM_INT);
$paging->sort  = optional_param('sort','iddsc', PARAM_TEXT);


if ($id) {
    $cm         = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $moduleinstance  = $DB->get_record(constants::M_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance(constants::M_TABLE, $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$PAGE->set_url(constants::M_URL . '/grading.php',
	array('id' => $cm->id,'format'=>$format,'action'=>$action,'userid'=>$userid,'attemptid'=>$attemptid));
require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);


//Get an admin settings 
$config = get_config(constants::M_FRANKY);

//set per page according to admin setting
if($paging->perpage==-1){
	$paging->perpage = $config->itemsperpage;
}

//Diverge logging logic at Moodle 2.7
if($CFG->version<2014051200){
	add_to_log($course->id, constants::M_MODNAME, 'reports', "reports.php?id={$cm->id}", $moduleinstance->name, $cm->id);
}else{
	// Trigger module viewed event.
	$event = \mod_NEWMODULE\event\course_module_viewed::create(array(
	   'objectid' => $moduleinstance->id,
	   'context' => $modulecontext
	));
	$event->add_record_snapshot('course_modules', $cm);
	$event->add_record_snapshot('course', $course);
	$event->add_record_snapshot(constants::M_MODNAME, $moduleinstance);
	$event->trigger();
} 

//process form submission
switch($action){
	case 'gradenowsubmit':
		$mform = new \mod_NEWMODULE\gradenowform();
		if($mform->is_cancelled()) {
			$action='grading';
			break;
		}else{
			$data = $mform->get_data();
			$submission = new \mod_NEWMODULE\submission($attemptid,$cm->id);
			$submission->update($data);
			
			//update gradebook
			NEWMODULE_update_grades($moduleinstance, $submission->fetch('userid'));
			
			//move on or return to grading
			if(property_exists($data,'submit2')){
				$attemptid = $submission->get_next_ungraded_id();
				if($attemptid){
					$action='gradenow';
				}else{
					$action='grading';
				}
			}else{
				$action='grading';
			}
		}
		break;
}



/// Set up the page header
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('course');
$PAGE->requires->jquery();


//This puts all our display logic into the renderer.php files in this plugin
$renderer = $PAGE->get_renderer(constants::M_FRANKY);
$reportrenderer = $PAGE->get_renderer(constants::M_FRANKY,'report');
$submissionrenderer = $PAGE->get_renderer(constants::M_FRANKY,'submission');

//From here we actually display the page.
$mode = "grading";
$extraheader="";
switch ($action){

	case 'gradenow':

		$submission = new \mod_NEWMODULE\submission($attemptid,$modulecontext->id);
		//prepare JS for the submission.php page
        //this inits the js for the audio players on the list of submissions
        $PAGE->requires->js_call_amd("mod_NEWMODULE/gradenowhelper", 'init', array());

        //load data
		$data=array(
			'action'=>'gradenowsubmit',
			'attemptid'=>$attemptid,
			'n'=>$moduleinstance->id,
            'feedbackaudio'=>$submission->fetch('feedbackaudio'),
            'feedbackvideo'=>$submission->fetch('feedbackvideo'),
			'feedbacktext'=>$submission->fetch('feedbacktext'),
            'feedbacktextformat'=>$submission->fetch('feedbacktextformat'),
			'sessiontime'=>$submission->fetch('sessiontime'),
			'sessionscore'=>$submission->fetch('sessionscore'));
		//get next id
		$nextid = $submission->get_next_ungraded_id();
        //fetch recorders
        $token = \mod_NEWMODULE\utils::fetch_token($config->apiuser,$config->apisecret);
        $timelimit =0;
        $audiorecid = constants::M_RECORDERID . '_' . constants::M_GRADING_FORM_FEEDBACKAUDIO;
        $videorecid = constants::M_RECORDERID . '_' . constants::M_GRADING_FORM_FEEDBACKVIDEO;
        $audiorecorderhtml = \mod_NEWMODULE\utils::fetch_recorder($moduleinstance,$audiorecid, $token, constants::M_GRADING_FORM_FEEDBACKAUDIO,$timelimit,'audio','bmr');
        $videorecorderhtml = \mod_NEWMODULE\utils::fetch_recorder($moduleinstance,$videorecid, $token, constants::M_GRADING_FORM_FEEDBACKVIDEO,$timelimit,'video','bmr');
        //create form
		$gradenowform = new \mod_NEWMODULE\gradenowform(null,array('shownext'=>$nextid !== false,'context'=>$modulecontext,'token'=>$token,
        'audiorecorderhtml'=>$audiorecorderhtml,'videorecorderhtml'=>$videorecorderhtml));

		//prepare text editor
		$edfileoptions = \mod_NEWMODULE\utils::editor_with_files_options($modulecontext);
        $editor = "feedbacktext";
        $data = file_prepare_standard_editor((object)$data,$editor, $edfileoptions, $modulecontext,constants::M_FRANKY,$editor, $attemptid);



		$gradenowform->set_data($data);
		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_LANG));
		echo $submissionrenderer->render_submission($submission);
		$gradenowform->display();
		echo $renderer->footer();
		return;


	case 'grading':
		$report = new \mod_NEWMODULE\report\grading();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->NEWMODULEid = $moduleinstance->id;
		$formdata->modulecontextid = $modulecontext->id;
		break;

	case 'gradingbyuser':
		$report = new \mod_NEWMODULE\report\gradingbyuser();
		//formdata should only have simple values, not objects
		//later it gets turned into urls for the export buttons
		$formdata = new stdClass();
		$formdata->NEWMODULEid = $moduleinstance->id;
		$formdata->userid = $userid;
		$formdata->modulecontextid = $modulecontext->id;
		break;
		
	default:
		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_LANG));
		echo "unknown action.";
		echo $renderer->footer();
		return;
}

//if we got to here we are loading the report on screen
//so we need our audio player loaded
//here we set up any info we need to pass into javascript
$aph_opts =Array();
$aph_opts['hiddenplayerclass'] = constants::M_HIDDEN_PLAYER;
$aph_opts['hiddenplayerbuttonclass'] = constants::M_HIDDEN_PLAYER_BUTTON;
$aph_opts['hiddenplayerbuttonactiveclass'] =constants::M_HIDDEN_PLAYER_BUTTON_ACTIVE;
$aph_opts['hiddenplayerbuttonplayingclass'] =constants::M_HIDDEN_PLAYER_BUTTON_PLAYING;
$aph_opts['hiddenplayerbuttonpausedclass'] =constants::M_HIDDEN_PLAYER_BUTTON_PAUSED;

//prepare JS for the grading.php page, mainly hidden audio recorder
$PAGE->requires->js_call_amd("mod_NEWMODULE/gradinghelper", 'init', array($aph_opts));


/*
1) load the class
2) call report->process_raw_data
3) call $rows=report->fetch_formatted_records($withlinks=true(html) false(print/excel))
5) call $reportrenderer->render_section_html($sectiontitle, $report->name, $report->get_head, $rows, $report->fields);
*/

$report->process_raw_data($formdata, $moduleinstance);
$reportheading = $report->fetch_formatted_heading();

switch($format){
	case 'html':
	default:
		
		$reportrows = $report->fetch_formatted_rows(true,$paging);
		$allrowscount = $report->fetch_all_rows_count();
		$pagingbar = $reportrenderer->show_paging_bar($allrowscount, $paging,$PAGE->url);
		echo $renderer->header($moduleinstance, $cm, $mode, null, get_string('grading', constants::M_LANG));
		echo $submissionrenderer->render_hiddenaudioplayer();
		echo $extraheader;
		echo $pagingbar;
		echo $reportrenderer->render_section_html($reportheading, $report->fetch_name(), $report->fetch_head(), $reportrows, $report->fetch_fields());
		echo $pagingbar;
		echo $reportrenderer->show_grading_footer($moduleinstance,$cm,$formdata);
		echo $renderer->footer();
}