<?php //$Id: block_admin.php,v 1.100 2007/08/26 08:24:53 skodak Exp $

class block_samouk_course extends block_list {
    function init() {
        $this->title = get_string('course');
        $this->version = 2007101400;
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

    /// Course editing on/off

        if (has_capability('moodle/course:update', $context) && ($course->id!==SITEID)) {
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/edit.gif" class="icon" alt="" />';
            if (isediting($this->instance->pageid)) {
                $this->content->items[]='<a href="view.php?id='.$this->instance->pageid.'&amp;edit=off&amp;sesskey='.sesskey().'">'.get_string('turneditingoff').'</a>';
            } else {
                $this->content->items[]='<a href="view.php?id='.$this->instance->pageid.'&amp;edit=on&amp;sesskey='.sesskey().'">'.get_string('turneditingon').'</a>';
            }

            $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/edit.php?id='.$this->instance->pageid.'">'.get_string('settings').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/settings.gif" class="icon" alt="" />';
        }
        
    /// Forum
        $this->content->items[] = '<a href="'.$CFG->wwwroot.'/mod/forum/index.php?id='.$this->instance->pageid.'">'.get_string('forums','forum').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->modpixpath.'/forum/icon.gif" class="icon" alt="" />';
        
        
    /// Backup this course

        if (has_capability('moodle/site:backup', $context)&& ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/backup/backup.php?id='.$this->instance->pageid.'">'.get_string('backup').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/backup.gif" class="icon" alt="" />';
        }

    /// Restore to this course
        if (has_capability('moodle/site:restore', $context) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/files/index.php?id='.$this->instance->pageid.'&amp;wdir=/backupdata">'.get_string('restore').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/restore.gif" class="icon" alt="" />';
        }

    /// Import data from other courses
        if (has_capability('moodle/site:import', $context) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/import.php?id='.$this->instance->pageid.'">'.get_string('import').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/restore.gif" class="icon" alt="" />';
        }

    /// Reset this course
        if (has_capability('moodle/course:reset', $context) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/reset.php?id='.$this->instance->pageid.'">'.get_string('reset').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/return.gif" class="icon" alt="" />';
        }
        
        
        
        return $this->content;
    }

    function applicable_formats() {
        return array('course' => true);   // Not needed on site, only on course
    }
}

?>
