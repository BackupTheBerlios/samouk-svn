<?php //$Id: block_admin.php,v 1.100 2007/08/26 08:24:53 skodak Exp $

class block_samouk_users extends block_list {
    function init() {
        $this->title = get_string('students');
        $this->version = 2007101000;
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

    /// Show a list of participants
        
        if (has_capability('moodle/course:viewparticipants', $context)) {
             
	        $this->content->items[] = '<a title="'.get_string('listofallpeople').'" href="'.
	                                  $CFG->wwwroot.'/user/index.php?contextid='.$context->id.'">'.get_string('participants').'</a>';
	        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';
             
        }

    /// Manage groups in this course

        if (($course->groupmode || !$course->groupmodeforce) && has_capability('moodle/course:managegroups', $context) && ($course->id!==SITEID)) {
            $strgroups = get_string('groups');
            $this->content->items[]='<a title="'.$strgroups.'" href="'.$CFG->wwwroot.'/group/index.php?id='.$this->instance->pageid.'">'.$strgroups.'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/group.gif" class="icon" alt="" />';
        }

    /// View course reports
        if (has_capability('moodle/site:viewreports', $context) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/report.php?id='.$this->instance->pageid.'">'.get_string('reports').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/stats.gif" class="icon" alt="" />';
        }
        
    /// View course grades (or just your own grades, same link)
        if ((has_capability('moodle/grade:viewall', $context) or
            (has_capability('moodle/grade:view', $context) && $course->showgrades)) && ($course->id!==SITEID)) {
            $this->content->items[]='<a href="'.$CFG->wwwroot.'/grade/report/index.php?id='.$this->instance->pageid.'">'.get_string('grades').'</a>';
            $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/grades.gif" class="icon" alt="" />';
        }

    /// Course outcomes (to help give it more prominence because it's important)
        if (!empty($CFG->enableoutcomes)) {
            if (has_capability('moodle/course:update', $context) && ($course->id!==SITEID)) {
                $this->content->items[]='<a href="'.$CFG->wwwroot.'/grade/edit/outcome/course.php?id='.$this->instance->pageid.'">'.get_string('outcomes', 'grades').'</a>';
                $this->content->icons[]='<img src="'.$CFG->pixpath.'/i/outcomes.gif" class="icon" alt="" />';
            }
        }
        
    /// Authorize hooks
        if ($course->enrol == 'authorize' || (empty($course->enrol) && $CFG->enrol == 'authorize') && ($course->id!==SITEID)) {
            require_once($CFG->dirroot.'/enrol/authorize/const.php');
            $paymenturl = '<a href="'.$CFG->wwwroot.'/enrol/authorize/index.php?course='.$course->id.'">'.get_string('payments').'</a> ';
            if (has_capability('enrol/authorize:managepayments', $context)) {
                if ($cnt = count_records('enrol_authorize', 'status', AN_STATUS_AUTH, 'courseid', $course->id)) {
                    $paymenturl .= '<a href="'.$CFG->wwwroot.'/enrol/authorize/index.php?status='.AN_STATUS_AUTH.'&amp;course='.$course->id.'">'.get_string('paymentpending', 'moodle', $cnt).'</a>';
                }
            }
            $this->content->items[] = $paymenturl;
            $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/payment.gif" class="icon" alt="" />';
        }

        return $this->content;
    }

    function applicable_formats() {
        return array('course' => true);   // Not needed on site, only on course
    }
}

?>
