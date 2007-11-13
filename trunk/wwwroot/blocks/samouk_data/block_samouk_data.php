<?php //$Id: block_admin.php,v 1.100 2007/08/26 08:24:53 skodak Exp $

class block_samouk_data extends block_list {
    function init() {
        $this->title = get_string('blok.samouk_data.name','samouk');
        $this->version = 2007101500;
    }

    function get_content() {

        global $CFG, $USER, $SITE, $COURSE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content = '';
        } else if ($this->instance->pageid == SITEID) {
            // return $this->content = '';
        }

        if (!empty($this->instance->pageid)) {
            $context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
            if ($COURSE->id == $this->instance->pageid) {
                $course = $COURSE;
            } else {
                $course = get_record('course', 'id', $this->instance->pageid);
            }
        } else {
            $context = get_context_instance(CONTEXT_SYSTEM);
            $course = $SITE;
        }

        if (!has_capability('moodle/course:view', $context)) {  // Just return
            return $this->content;
        }

        if (empty($CFG->loginhttps)) {
            $securewwwroot = $CFG->wwwroot;
        } else {
            $securewwwroot = str_replace('http:','https:',$CFG->wwwroot);
        }

        /// Manage files
        if (has_capability('moodle/course:managefiles', $context) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/files/index.php?id='.$this->instance->pageid.'">'.get_string('files').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/files.gif" class="icon" alt="" />';
        }
        
        /// Manage questions
        if ($course->id!==SITEID){
            $questioncaps = array(
                                    'moodle/question:add',
                                    'moodle/question:editmine',
                                    'moodle/question:editall',
                                    'moodle/question:viewmine',
                                    'moodle/question:viewall',
                                    'moodle/question:movemine',
                                    'moodle/question:moveall');
            $questionpermission = false;
            foreach ($questioncaps as $questioncap){
                if (has_capability($questioncap, $context)){
                    $questionpermission = true;
                    break;
                }
            }
            if ($questionpermission) {
                $this->content->items[]='<a href="'.$CFG->wwwroot.'/question/edit.php?courseid='.$this->instance->pageid.'">'.get_string('questions', 'quiz').'</a>';
                $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/questions.gif" class="icon" alt="" />';
            }
        }
        
        
        return $this->content;
    }

    function applicable_formats() {
        return array('course' => true);   // Not needed on site, only on course
    }
}

?>
