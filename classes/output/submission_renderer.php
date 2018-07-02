<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_NEWMODULE\output;

use \mod_NEWMODULE\constants;


class submission_renderer extends \plugin_renderer_base {

    protected $submission=null;

    public function render_submission($submission) {
        $this->submission = $submission;

        $ret = $this->render_header($submission->fetch('userfullname'));
        $ret .= $this->render_sessionscore($submission->fetch('sessionscore'));

        $mediatype = $submission->fetch('mediatype');
        $mediaurl = $submission->fetch('mediaurl');
        $submissionplayer = $this->render_submissionplayer($mediaurl,$mediatype);
        $ret .= $submissionplayer;

        $ret .= $this->render_feedbacktext($submission->fetch('feedbacktext'));
        $ret .= $this->render_feedbackaudio($submission->fetch('feedbackaudio'));
        $ret .= $this->render_feedbackvideo($submission->fetch('feedbackvideo'));
        return $ret;
    }

    public function render_header($username) {
        $ret = $this->output->heading(get_string('showingattempt',constants::M_LANG,$username),3);
        return $ret;
    }
    public function render_sessionscore($sessionscore) {
        $ret = get_string('grade',constants::M_LANG) . ' ' . $sessionscore . '<br>';
        return $ret;
    }
    public function render_submissionplayer($mediaurl,$submissiontype){
        switch ($submissiontype){
            case 'video':
                $tag = 'video';
                break;
            case 'audio':
            default:
                $tag = 'audio';
        }
        $audioplayer = \html_writer::tag($tag,'',
            array('controls'=>'','src'=>$mediaurl,'id'=>constants::M_GRADING_PLAYER));
        $ret = \html_writer::div($audioplayer,constants::M_GRADING_PLAYER_CONTAINER,array('id'=>constants::M_GRADING_PLAYER_CONTAINER));
        return $ret;
    }

    public function render_hiddenaudioplayer(){
        $audioplayer = \html_writer::tag('audio','',array('src'=>'','id'=>constants::M_HIDDEN_PLAYER,'class'=>constants::M_HIDDEN_PLAYER));
        return $audioplayer;
    }

    public function render_feedbackaudio($mediaurl){
        $label = $this->output->heading(get_string('feedbackaudiolabel',constants::M_LANG),5);
        $audioplayer = \html_writer::tag('audio','',
            array('controls'=>'','src'=>$mediaurl,'id'=>constants::M_GRADING_PLAYER));
        $ret = \html_writer::div($label . $audioplayer,constants::M_GRADING_PLAYER_CONTAINER,array('id'=>constants::M_GRADING_PLAYER_CONTAINER));
        return $ret;
    }
    public function render_feedbackvideo($mediaurl){
        $label = $this->output->heading(get_string('feedbackvideolabel',constants::M_LANG),5);
        $videoplayer = \html_writer::tag('video','',
            array('controls'=>'','src'=>$mediaurl,'id'=>constants::M_GRADING_PLAYER));
        $ret = \html_writer::div($label .$videoplayer,constants::M_GRADING_PLAYER_CONTAINER,array('id'=>constants::M_GRADING_PLAYER_CONTAINER));
        return $ret;
    }

    public function render_feedbacktext($text){
        $label = $this->output->heading(get_string('feedbacktextlabel',constants::M_LANG),5);
        $contextid = $this->submission->fetch('modulecontextid');
        $attemptid = $this->submission->fetch('attemptid');
        $text = file_rewrite_pluginfile_urls($text,'pluginfile.php',$contextid,constants::M_FRANKY,'feedbacktext',$attemptid);
        $ret = \html_writer::div($label .format_text($text),constants::M_GRADING_PLAYER_CONTAINER,array('id'=>constants::M_GRADING_PLAYER_CONTAINER));
        return $ret;
    }
}