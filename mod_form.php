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
 * The main NEWMODULE configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

use \mod_NEWMODULE\constants;

/**
 * Module instance settings form
 */
class mod_NEWMODULE_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
    	global $CFG;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('NEWMODULEname', constants::M_LANG), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'NEWMODULEname', constants::M_LANG);

         // Adding the standard "intro" and "introformat" fields
        if($CFG->version < 2015051100){
        	$this->add_intro_editor();
        }else{
        	$this->standard_intro_elements();
		}
		
		//time target
		$mform->addElement('duration', 'timelimit', get_string('timelimit',constants::M_LANG));
		$mform->setDefault('timelimit',60);
		
		//add other editors
		//could add files but need the context/mod info. So for now just rich text
		$config = get_config(constants::M_FRANKY);
		
		//The pasage
		//$edfileoptions = \mod_NEWMODULE\utils::editor_with_files_options($this->context);
		$ednofileoptions = \mod_NEWMODULE\utils::editor_no_files_options($this->context);
		$opts = array('rows'=>'15', 'columns'=>'80');
		$mform->addElement('editor','passage_editor',get_string('passagelabel',constants::M_LANG),$opts, $ednofileoptions);
		
		//welcome and feedback
		$opts = array('rows'=>'6', 'columns'=>'80');
		$mform->addElement('editor','welcome_editor',get_string('welcomelabel',constants::M_LANG),$opts, $ednofileoptions);
		$mform->addElement('editor','feedback_editor',get_string('feedbacklabel',constants::M_LANG),$opts, $ednofileoptions);
		
		//defaults
		$mform->setDefault('passage_editor',array('text'=>'', 'format'=>FORMAT_MOODLE));		
		$mform->setDefault('welcome_editor',array('text'=>$config->defaultwelcome, 'format'=>FORMAT_MOODLE));
		$mform->setDefault('feedback_editor',array('text'=>$config->defaultfeedback, 'format'=>FORMAT_MOODLE));
		
		//types
		$mform->setType('passage_editor',PARAM_RAW);
		$mform->setType('welcome_editor',PARAM_RAW);
		$mform->setType('feedback_editor',PARAM_RAW);


        //Enable AI
        $mform->addElement('advcheckbox', 'transcribe', get_string('transcribe', constants::M_LANG), get_string('transcribe_details', constants::M_LANG));
        $mform->setDefault('transcribe',$config->transcribe);

		//Attempts
        $attemptoptions = array(0 => get_string('unlimited', constants::M_LANG),
                            1 => '1',2 => '2',3 => '3',4 => '4',5 => '5',);
        $mform->addElement('select', 'maxattempts', get_string('maxattempts', constants::M_LANG), $attemptoptions);

        //Media Type options
        $mediatypeoptions = \mod_NEWMODULE\utils::get_mediatype_options();
        $mform->addElement('select', 'mediatype', get_string('mediatype', constants::M_LANG), $mediatypeoptions);
        $mform->setDefault('mediatype',$config->mediatype);

        //Recorder options
        $recordertypeoptions = \mod_NEWMODULE\utils::get_recordertype_options();
        $mform->addElement('select', 'recordertype', get_string('recordertype', constants::M_LANG), $recordertypeoptions);
        $mform->setDefault('recordertype',$config->recordertype);

		//Language options
        $langoptions = \mod_NEWMODULE\utils::get_lang_options();
        $mform->addElement('select', 'language', get_string('language', constants::M_LANG), $langoptions);
        $mform->setDefault('language',$config->language);

        //region
        $regionoptions = \mod_NEWMODULE\utils::get_region_options();
        $mform->addElement('select', 'region', get_string('region', constants::M_LANG), $regionoptions);
        $mform->setDefault('region',$config->awsregion);

        //expiredays
        $expiredaysoptions = \mod_NEWMODULE\utils::get_expiredays_options();
        $mform->addElement('select', 'expiredays', get_string('expiredays', constants::M_LANG), $expiredaysoptions);
        $mform->setDefault('expiredays',$config->expiredays);
		
		 // Grade.
        $this->standard_grading_coursemodule_elements();
        
        //grade options
        $gradeoptions = array(constants::M_GRADEHIGHEST => get_string('gradehighest',constants::M_LANG),
                            constants::M_GRADELOWEST => get_string('gradelowest', constants::M_LANG),
                            constants::M_GRADELATEST => get_string('gradelatest', constants::M_LANG),
                            constants::M_GRADEAVERAGE => get_string('gradeaverage', constants::M_LANG),
							constants::M_GRADENONE => get_string('gradenone', constants::M_LANG));
        $mform->addElement('select', 'gradeoptions', get_string('gradeoptions', constants::M_LANG), $gradeoptions);
		$mform->setDefault('gradeoptions',constants::M_GRADELATEST);
		
		

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
	
	
    /**
     * This adds completion rules
	 * The values here are just dummies. They won't work until you implement some sort of grading
	 * See lib.php NEWMODULE_get_completion_state()
     */
	 function add_completion_rules() {
		$mform =& $this->_form;  
		$config = get_config(constants::M_FRANKY);
    
		//timer options
        //Add a place to set a mimumum time after which the activity is recorded complete
       $mform->addElement('static', 'mingradedetails', '',get_string('mingradedetails', constants::M_LANG));
       $options= array(0=>get_string('none'),20=>'20%',30=>'30%',40=>'40%',50=>'50%',60=>'60%',70=>'70%',80=>'80%',90=>'90%',100=>'40%');
       $mform->addElement('select', 'mingrade', get_string('mingrade', constants::M_LANG), $options);
	   
		return array('mingrade');
	}
	
	function completion_rule_enabled($data) {
		return ($data['mingrade']>0);
	}
	
	public function data_preprocessing(&$form_data) {
		$ednofileoptions = \mod_NEWMODULE\utils::editor_no_files_options($this->context);
		$editors  = NEWMODULE_get_editornames();
		 if ($this->current->instance) {
			$itemid = 0;
			foreach($editors as $editor){
				$form_data = file_prepare_standard_editor((object)$form_data,$editor, $ednofileoptions, $this->context,constants::M_FRANKY,$editor, $itemid);
			}
		}
	}
}
