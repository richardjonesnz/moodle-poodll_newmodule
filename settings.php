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
 * NEWMODULE module admin settings and defaults
 *
 * @package    mod
 * @subpackage NEWMODULE
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/mod/NEWMODULE/lib.php');

use \mod_NEWMODULE\constants;

if ($ADMIN->fulltree) {

	 $settings->add(new admin_setting_configtextarea('mod_NEWMODULE/defaultwelcome',
        get_string('welcomelabel', 'NEWMODULE'), get_string('welcomelabel_details', constants::M_LANG), get_string('defaultwelcome',constants::M_LANG), PARAM_TEXT));
	 $settings->add(new admin_setting_configtextarea('mod_NEWMODULE/defaultfeedback',
        get_string('feedbacklabel', 'NEWMODULE'), get_string('feedbacklabel_details', constants::M_LANG), get_string('defaultfeedback',constants::M_LANG), PARAM_TEXT));


    $settings->add(new admin_setting_configtext('mod_NEWMODULE/apiuser',
        get_string('apiuser', constants::M_LANG), get_string('apiuser_details', constants::M_LANG), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('mod_NEWMODULE/apisecret',
        get_string('apisecret', constants::M_LANG), get_string('apisecret_details', constants::M_LANG), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('mod_NEWMODULE/transcribe',
        get_string('transcribe', constants::M_LANG), get_string('transcribe_details',constants::M_LANG), 0));

    $regions = \mod_NEWMODULE\utils::get_region_options();
    $settings->add(new admin_setting_configselect('mod_NEWMODULE/awsregion', get_string('awsregion', constants::M_LANG), '', 'useast1', $regions));

    $expiredays = \mod_NEWMODULE\utils::get_expiredays_options();
    $settings->add(new admin_setting_configselect('mod_NEWMODULE/expiredays', get_string('expiredays', constants::M_LANG), '', '365', $expiredays));

	 $langoptions = \mod_NEWMODULE\utils::get_lang_options();
	 $settings->add(new admin_setting_configselect('mod_NEWMODULE/language', get_string('language', constants::M_LANG), '', 'en', $langoptions));

    $mediaoptions = \mod_NEWMODULE\utils::get_mediatype_options();
    $settings->add(new admin_setting_configselect('mod_NEWMODULE/mediatype', get_string('mediatype', constants::M_LANG), '', 'audio', $mediaoptions));

    $recordertypeoptions = \mod_NEWMODULE\utils::get_recordertype_options();
    $settings->add(new admin_setting_configselect('mod_NEWMODULE/recordertype', get_string('recordertype', constants::M_LANG), '', 'bmr', $recordertypeoptions));
	 
	 $settings->add(new admin_setting_configtext('mod_NEWMODULE/itemsperpage',
        get_string('itemsperpage', constants::M_LANG), get_string('itemsperpage_details', constants::M_LANG), 40, PARAM_INT));

}
