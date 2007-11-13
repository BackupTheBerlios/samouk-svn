<?php
/**
 * Step-by-step wizard to create new course
 * 
 * @name new_course.php
 * @author kowy
 * @version 1.9
 */
 
	require_once('../config.php');
	require_once('lib.php');
	 
	$categoryid = optional_param('category', 0, PARAM_INT); // course category
	 
	require_login();
	
	/// basic access control checks
	if ($categoryid) { // creating new course in this category
	    $course = null;
	    if (!$category = get_record('course_categories', 'id', $categoryid)) {
	        error('Category ID was incorrect');
	    }
	
	    // v uvedené kategorii musí mít uživatel právo vytvářet kurzy
	    require_capability('moodle/course:create', get_context_instance(CONTEXT_COURSECAT, $category->id));
	} else {
	    error('Either course id or category must be specified');
	}
	
	// save category ID for the wizard
	$_SESSION['new_course.categoryid'] = $categoryid;
		
    $site = get_site();

    $streditcoursesettings = get_string("editcoursesettings");
    $straddnewcourse = get_string("addnewcourse");
    $stradministration = get_string("administration");
    $strcategories = get_string("categories");
    $navlinks = array();
    $navlinks[] = array('name' => $stradministration, 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strcategories, 'link' => 'index.php', 'type' => 'misc');
    $navlinks[] = array('name' => $straddnewcourse, 'link' => null, 'type' => 'misc');
    print_header("$site->shortname: $straddnewcourse", $site->fullname, build_navigation($navlinks), "", "", 
                 true, print_course_search("", true, "navbar"));

    // print page heading (name)
    print_heading(get_string("heading.newcourse", "samouk"), "left", 2, "main");
?>

    <!-- generate tableless layout -->
    <form id="form1" class="mform" action="course_wizard.php" method="get">
        <fieldset id="basic_heading" class="clearfix">
		    <!--  <div class="box categorybox categoryboxcontent boxaligncenter boxwidthwide" style="background: rgb(255, 255, 255) none repeat scroll 0%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;"> -->
			    <ul>
			        <li>Co je to za wizarda</li>
			        <li>Kolik má částí</li>
			        <li>Co bude potřebovat vědět pro úspěšné dokončení</li>
				</ul>
	         <!-- </div>-->
	    </fieldset>
	    <fieldset class="hidden">
	        <div class="fitem">
	           <div class="fitemtitle">
	               <div class="fgrouplabel">&nbsp;</div>
	           </div>
	        </div>
	        <fieldset class="felement fgroup">
	            <input id="id_buttons_cancel" type="button" onclick="javascript:location.href='index.php';" value="<? echo get_string("cancel") ?>"/>
	            <input id="id_buttons_next" type="submit" value="<? echo get_string('button.next','samouk') ?>"/>
            </fieldset>	    
	    </fieldset>
	</form>
	
<?
	print_footer($course);
?>