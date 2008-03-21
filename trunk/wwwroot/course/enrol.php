<?php // $Id: enrol.php,v 1.50 2008/01/31 19:09:11 kowy Exp $
      // Depending on the current enrolment method, this page
      // presents the user with whatever they need to know when
      // they try to enrol in a course.

    require_once("../config.php");
    require_once("lib.php");
    require_once("$CFG->dirroot/enrol/enrol.class.php");

    $id           = required_param('id', PARAM_INT);
    $loginasguest = optional_param('loginasguest', 0, PARAM_BOOL); // hmm, is this still needed?

    if (!isloggedin()) {
        $wwwroot = $CFG->wwwroot;
        if (!empty($CFG->loginhttps)) {
            $wwwroot = str_replace('http:','https:', $wwwroot);
        }
        // do not use require_login here because we are usually comming from it
        redirect($wwwroot.'/login/index.php');
    }

    if (! $course = get_record('course', 'id', $id) ) {
        error("That's an invalid course id");
    }

    if (! $context = get_context_instance(CONTEXT_COURSE, $course->id) ) {
        error("That's an invalid course id");
    }

/// do not use when in course login as
    if (!empty($USER->realuser) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
        print_error('loginasnoenrol', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
    }

    // 2008/01/31 - kowy - prepare all possible enrols selected on setup page 
    $enrols = array(); 
    foreach (explode(',', $CFG->enrol_plugins_enabled) as $enrolname) {
    	$module = enrolment_factory::factory($enrolname);
    	if (method_exists($module, 'print_entry')) {
        	$enrols[$enrolname] = $module;
        }
    }
    //$enrol = enrolment_factory::factory($course->enrol); // do not use if (!$enrol... here, it can not work in PHP4 - see MDL-7529

/// Refreshing all current role assignments for the current user

    load_all_capabilities();

/// Double check just in case they are actually enrolled already and
/// thus got to this script by mistake.  This might occur if enrolments
/// changed during this session or something
    if (has_capability('moodle/course:view', $context) and !has_capability('moodle/legacy:guest', $context, NULL, false)) {
        if ($SESSION->wantsurl) {
            $destination = $SESSION->wantsurl;
            unset($SESSION->wantsurl);
        } else {
            $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
        }
        redirect($destination);   // Bye!
    }

/// Check if the course is a meta course  (bug 5734)
    if ($course->metacourse) {
        print_header_simple();
        notice(get_string('coursenotaccessible'), "$CFG->wwwroot/index.php");
    }

/// Users can't enroll to site course
    if ($course->id == SITEID) {
        print_header_simple();
        notice(get_string('enrollfirst'), "$CFG->wwwroot/index.php");
    }

/// Double check just in case they are enrolled to start in the future

    if ($course->enrolperiod) {   // Only active if the course has an enrolment period in effect
        if ($roles = get_user_roles($context, $USER->id)) {
            foreach ($roles as $role) {
                if ($role->timestart and ($role->timestart >= time())) {
                    $message = get_string('enrolmentnotyet', '', userdate($student->timestart));
                    print_header();
                    notice($message, "$CFG->wwwroot/index.php");
                }
            }
        }
    }

/// Check if the course is enrollable
	// 2008/01/31 - kowy - check all enrolment possibilities
	foreach ($enrols as $enrol) {
    	if (!method_exists($enrol, 'print_entry')) {
        	print_header_simple();
        	print_r($enrol);
	        notice(get_string('enrolmentnointernal'), "$CFG->wwwroot/index.php");
    	}
	}

    if (!$course->enrollable ||
            ($course->enrollable == 2 && $course->enrolstartdate > 0 && $course->enrolstartdate > time()) ||
            ($course->enrollable == 2 && $course->enrolenddate > 0 && $course->enrolenddate <= time())
            ) {
        print_header($course->shortname, $course->fullname, build_navigation(array(array('name'=>$course->shortname,'link'=>'','type'=>'misc'))) );
        notice(get_string('notenrollable'), "$CFG->wwwroot/index.php");
    }

/// Check the submitted enrolment information if there is any (eg could be enrolment key)

    // 2008/01/31 - kowy - in enrol_type property should by a type of enrol method - check entries based on chosen enrol method
    if ($form = data_submitted()) {
    	if (is_scalar($form['enrol_type']) && method_exists($enrols[$form['enrol_type']], 'check_entry')) {
        	$enrols[$form['enrol_type']]->check_entry($form, $course);   // Should terminate/redirect in here if it's all OK
    	}
    }

/// Otherwise, we print the entry form.

    // 2008/02/04 - kowy - print header of the page
    $strloginto = get_string('loginto', '', $course->shortname);
    $strcourses = get_string('courses');
    $navlinks = array();
    $navlinks[] = array('name' => $strcourses, 'link' => ".", 'type' => 'misc');
    $navlinks[] = array('name' => $strloginto, 'link' => null, 'type' => 'misc');
    $navigation = build_navigation($navlinks);
    print_header($strloginto, $course->fullname, $navigation);
    print_course($course, "80%");

    // 2008/01/31 - kowy - print all enrolment possibilities
    foreach ($enrols as $enrol) {
    	$enrol->print_entry($course);
    }
    
    // 2008/02/04 - kowy - print footer
    print_footer();

/// Easy!

?>
