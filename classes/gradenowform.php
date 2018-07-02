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
 * @copyright  2018 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_NEWMODULE;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/lib/formslib.php');
use \mod_NEWMODULE\constants;


/**
 * Grade form for mod_NEWMODULE
 *
 * @package    mod_NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradenowform extends \moodleform{

    /**
     * Defines forms elements
     */
    public function definition() {
    	global $CFG;

        $mform = $this->_form;
        $this->context = $this->_customdata['context'];
        $this->audiorecorderhtml = $this->_customdata['audiorecorderhtml'];
        $this->videorecorderhtml = $this->_customdata['videorecorderhtml'];
        $shownext = $this->_customdata['shownext'];
		$mform->addElement('header','General','');

		
        // adding the hidden fields which recorders write to and other bits we might/will use
		$mform->addElement('hidden', 'action');
		$mform->addElement('hidden', 'attemptid');
		$mform->addElement('hidden', 'n');
        $mform->addElement('hidden', 'sessiontime',null,
				array('class'=>constants::M_GRADING_FORM_SESSIONTIME,'id'=>constants::M_GRADING_FORM_SESSIONTIME));
		$mform->addElement('hidden', 'feedbackaudio',null,
				array('class'=>constants::M_GRADING_FORM_FEEDBACKAUDIO,'id'=>constants::M_GRADING_FORM_FEEDBACKAUDIO));
        $mform->addElement('hidden', 'feedbackvideo',null,
            array('class'=>constants::M_GRADING_FORM_FEEDBACKVIDEO,'id'=>constants::M_GRADING_FORM_FEEDBACKVIDEO));
		//$mform->addElement('hidden', 'sessionscore',null,
		//		array('class'=>constants::M_GRADING_FORM_SESSIONSCORE,'id'=>constants::M_GRADING_FORM_SESSIONSCORE));
		$mform->setType('action',PARAM_TEXT);
		$mform->setType('attemptid',PARAM_INT);
		$mform->setType('n',PARAM_INT);
		$mform->setType('sessiontime',PARAM_INT);
		//$mform->setType('sessionscore',PARAM_INT);
		$mform->setType('feedbackaudio',PARAM_TEXT);
        $mform->setType('feedbackvideo',PARAM_TEXT);

        //session score
        $mform->addElement('text', 'sessionscore', get_string('grade',constants::M_LANG), array('size'=>'12'));
        $mform->setType('sessionscore', PARAM_INT);


        //Feedback text
        $edfileoptions = \mod_NEWMODULE\utils::editor_with_files_options($this->context);
        $opts = array('rows'=>'15', 'columns'=>'80');
        $mform->addElement('editor','feedbacktext_editor',get_string('feedbacktextlabel',constants::M_LANG),$opts, $edfileoptions);
        $mform->setDefault('feedbacktext_editor',array('text'=>'', 'format'=>FORMAT_MOODLE));
        $mform->setType('feedbacktext_editor',PARAM_RAW);

        //feedback audio
        $mform->addElement('static', 'feedbackaudioholder',get_string('feedbackaudiolabel',constants::M_LANG),
            $this->audiorecorderhtml);

        //feedback video
        $mform->addElement('static', 'feedbackvideoholder',get_string('feedbackvideolabel',constants::M_LANG),
            $this->videorecorderhtml);


        //-------------------------------------------------------------------------------
        // add out buttons for submitting and cancelling
        //-------------------------------------------------------------------------------
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        if($shownext){
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('saveandnext',constants::M_LANG));
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        //	$mform->closeHeaderBefore('buttonar');
    }
}

