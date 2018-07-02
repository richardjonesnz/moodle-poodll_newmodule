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
 * Grade Now for NEWMODULE plugin
 *
 * @package    mod_NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_NEWMODULE;
defined('MOODLE_INTERNAL') || die();

use \mod_NEWMODULE\constants;


/**
 * Grade Now class for mod_NEWMODULE
 *
 * @package    mod_NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission{
	protected $modulecontextid =0;
	protected $attemptid = 0;
	protected $attemptdata = null;
	protected $activitydata = null;
	
	function __construct($attemptid, $modulecontextid=0) {
		global $DB;
       $this->attemptid = $attemptid;
	   $this->modulecontextid = $modulecontextid;
	   $attemptdata = $DB->get_record(constants::M_USERTABLE,array('id'=>$attemptid));
	   if($attemptdata){
			$this->attemptdata = $attemptdata;
			$this->activitydata = $DB->get_record(constants::M_TABLE,array('id'=>$attemptdata->NEWMODULEid));
		}
   }
   
   public function get_next_ungraded_id(){
		global $DB;
		$where = "id > " .$this->attemptid . " AND sessionscore = 0 AND NEWMODULEid = " . $this->attemptdata->NEWMODULEid;
		$records = $DB->get_records_select(constants::M_USERTABLE,$where,array(),' id ASC');
		if($records){
			$rec = array_shift($records);
			return $rec->id;
		}else{
			return false;
		}
   }
   
   public function update($formdata){
		global $DB;
		$updatedattempt = new \stdClass();
		$updatedattempt->id=$this->attemptid;
		$updatedattempt->sessiontime = $formdata->sessiontime;
	//	$updatedattempt->accuracy = $formdata->accuracy;
       $updatedattempt->feedbackaudio = $formdata->feedbackaudio;
       $updatedattempt->feedbackvideo = $formdata->feedbackvideo;
		$updatedattempt->sessionscore = $formdata->sessionscore;


       $context = \context_module::instance($this->modulecontextid );
       $edoptions = \mod_NEWMODULE\utils::editor_with_files_options($context);
       $editor = "feedbacktext";
       $formdata = file_postupdate_standard_editor( $formdata, $editor, $edoptions,$context,constants::M_FRANKY,$editor,$this->attemptid);
       $updatedattempt->feedbacktext = $formdata->feedbacktext;
       $updatedattempt->feedbacktextformat = $formdata->feedbacktextformat;

		$DB->update_record(constants::M_USERTABLE,$updatedattempt);
   }
   
   public function fetch($property){
		global $DB;
		switch($property){
            case 'mediatype':
                $ret = $this->activitydata->mediatype;
                break;

			case 'userfullname':
				$user = $DB->get_record('user',array('id'=>$this->attemptdata->userid));
				$ret = fullname($user);
				break;
            case 'modulecontextid':
                $ret = $this->modulecontextid;
                break;
            case 'attemptid':
                $ret = $this->attemptdata->id;
                break;
			case 'passage': 
				$ret = $this->activitydata->passage;
				break;
            case 'mediaurl':
                $ret = $this->attemptdata->filename;
                break;
			case 'somedetails': 
				$ret= $this->attemptdata->id . ' ' . $this->activitydata->passage; 
				break;
			default: 
				$ret = $this->attemptdata->{$property};
		}
		return $ret;
   }

   /*
    * This may be called but is currently not doing anything
    */
   public function prepare_javascript($reviewmode=false,$aimode=false){
		global $PAGE;

		//here we set up any info we need to pass into javascript
		$gradingopts =Array();
		$gradingopts['reviewmode'] = $reviewmode;
		$gradingopts['timelimit'] = $this->activitydata->timelimit;
 		$gradingopts['language'] = $this->activitydata->language;
		$gradingopts['activityid'] = $this->activitydata->id;
		$gradingopts['sesskey'] = sesskey();
		$gradingopts['attemptid'] = $this->attemptdata->id;
		$gradingopts['sessiontime'] = $this->attemptdata->sessiontime;
		$gradingopts['sessionscore'] = $this->attemptdata->sessionscore;
       $gradingopts['opts_id'] = 'mod_NEWMODULE_submissionopts';


       $jsonstring = json_encode($gradingopts);
       $opts_html = \html_writer::tag('input', '', array('id' => $gradingopts['opts_id'], 'type' => 'hidden', 'value' => $jsonstring));
       $PAGE->requires->js_call_amd("mod_NEWMODULE/gradenowhelper", 'init', array(array('id'=>$gradingopts['opts_id'])));
       //these need to be returned and echo'ed to the page
       return $opts_html;

   }
}
