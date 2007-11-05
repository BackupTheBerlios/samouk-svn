<?php  // $Id: edit_form.php,v 1.8 2007/09/03 09:13:56 moodler Exp $

require_once($CFG->libdir.'/formslib.php');

class blog_edit_form extends moodleform {

    function definition() {

        global $CFG, $COURSE, $USER;
        $mform    =& $this->_form;

        $post = $this->_customdata['existing'];
        $sitecontext = $this->_customdata['sitecontext'];

        // the upload manager is used directly in entry processing, moodleform::save_files() is not used yet
        $this->set_upload_manager(new upload_manager('attachment', true, false, $COURSE, false, 0, true, true, false));

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'subject', get_string('entrytitle', 'blog'), 'size="60"');
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('emptytitle', 'blog'), 'required', null, 'client');

        $mform->addElement('htmleditor', 'summary', get_string('entrybody', 'blog'), array('rows'=>25));
        $mform->setType('summary', PARAM_RAW);
        $mform->addRule('summary', get_string('emptybody', 'blog'), 'required', null, 'client');
        $mform->setHelpButton('summary', array('writing', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

        $mform->addElement('file', 'attachment', get_string('attachment', 'forum'));

        $mform->addElement('select', 'publishstate', get_string('publishto', 'blog'), blog_applicable_publish_states());
        $mform->setHelpButton('publishstate', array('publish_state', get_string('helppublish', 'blog'), 'blog'));


        if (!empty($CFG->usetags)) {
            $mform->addElement('header', 'tagshdr', get_string('tags', 'blog'));
            $mform->createElement('select', 'otags', get_string('otags','blog'));

            $js_escape = array(
                "\r"    => '\r',
                "\n"    => '\n',
                "\t"    => '\t',
                "'"     => "\\'",
                '"'     => '\"',
                '\\'    => '\\\\'
            );

            $otagsselEl =& $mform->addElement('select', 'otags', get_string('otags', 'blog'), array(), 'size="5"');
            $otagsselEl->setMultiple(true);
            $this->otags_select_setup();

            if (has_capability('moodle/blog:manageofficialtags', $sitecontext)){
                $deleteotagsmsg = strtr(get_string('deleteotagswarn', 'blog'), $js_escape);
                $mform->registerNoSubmitButton('deleteotags');
                $mform->addElement('submit', 'deleteotags', get_string('delete'),
                                array('onclick'=>"return confirm('$deleteotagsmsg');"));
                $mform->disabledIf('deleteotags', 'otags[]', 'noitemselected');
                $mform->setAdvanced('deleteotags');

                $mform->registerNoSubmitButton('addotags');
                $otagsgrp = array();
                $otagsgrp[] =& $mform->createElement('text', 'otagsadd', get_string('addotags', 'blog'));
                $otagsgrp[] =& $mform->createElement('submit', 'addotags', get_string('add'));
                $mform->addGroup($otagsgrp, 'otagsgrp', get_string('addotags','blog'), array(' '), false);
                $mform->setType('otagsadd', PARAM_NOTAGS);
                $mform->setAdvanced('otagsgrp');
            }

            $mform->addElement('textarea', 'ptags', get_string('ptags', 'blog'), array('cols'=>'40', 'rows'=>'5'));
            $mform->setType('ptagsadd', PARAM_NOTAGS);
        }
        
        $this->add_action_buttons();

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ACTION);
        $mform->setDefault('action', '');

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

    }

    /**
     * This function sets up options of otag select element. This is called from definition and also
     * after adding new official tags with the add tag button.
     *
     */
    function otags_select_setup(){
        global $CFG;
        $mform =& $this->_form;
        if ($otagsselect =& $mform->getElement('otags')) {
            $otagsselect->removeOptions();
        }
        if ($otags = get_records_sql_menu('SELECT id, name from '.$CFG->prefix.'tag WHERE tagtype=\'official\' ORDER by name ASC')){
            $otagsselect->loadArray($otags);
        } else {
            // removing this causes errors
            //$mform->removeElement('otags');
        }
    }

}
?>
