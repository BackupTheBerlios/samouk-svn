<?php
/**
 * Admin-only code to change visibility of a course 
 * If course is visible change to hidden and reversely
 * 
 * @name showhide.php
 * @author kowy
 * @version 1.9.0 
 */

    require_once("../config.php");

    $id      = required_param('id', PARAM_INT);              // course id
    $backurl = optional_param('backurl', PARAM_LOCALURL);    // URL of a previous page

    require_login();

    // check if user has change visibility rights
    $context = get_context_instance(CONTEXT_COURSE, $id);
    if (!has_capability('moodle/course:visibility', $context)
            && !(has_capability('moodle/legacy:coursecreator', $context) && has_capability('moodle/course:manageactivities', $context)
               )) {
        error(get_string('error.visibility.change', 'samouk'));
    }

    if (!$site = get_site()) {
        error("Site not found!");
    }

    $stradministration = get_string("administration");
    $strcategories = get_string("categories");

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID was incorrect (can't find it)");
    }

    $category = get_record("course_categories", "id", $course->category);

    // OK checks done, change the visibility of the course

    add_to_log(SITEID, "course", ($course->visible?"hide":"publish"), "view.php?id=$course->id", 
                "$course->fullname (ID $course->id)");

    $strupdatingcourse = get_string("updatingcourse", "samouk", format_string($course->shortname));

    /*
    print_header("$site->shortname: $strdeletingcourse", $site->fullname, 
                 "<a href=\"../$CFG->admin/index.php\">$stradministration</a> -> ".
                 "<a href=\"index.php\">$strcategories</a> -> ".
                 "<a href=\"category.php?id=$course->category\">$category->name</a> -> ".
                 "$strdeletingcourse");

    print_heading($strupdatingcourse);
    */

    set_field("course", "visible", ($course->visible=='1'?'0':'1'), 'id', $course->id);
    set_field("course", "timemodified", time(), "id", $course->id);
    if ($course->visible=='0') {
    	// course is displayed at the moment
    	set_field("course", "timecreated", time(), "id", $course->id);
    }
    
    // jump to actually shown category (with a course)
    if ($backurl == "384") {
        // if backurl isn't obtained
        redirect('category.php?id='.$category->id);
    } else {
        redirect($backurl);
    }
    

    /*
    print_heading( get_string("deletedcourse", "", format_string($course->shortname)) );

    print_continue("category.php?id=$course->category");

    print_footer();
    */

?>
