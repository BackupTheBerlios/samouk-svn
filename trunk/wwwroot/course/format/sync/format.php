<?php // $Id: format.php,v 1.72 2007/05/09 15:08:48 urs_hunkler Exp $
      // Display the whole course as "weeks" made of of modules
      // Included from "view.php"

    require_once($CFG->libdir.'/ajax/ajaxlib.php');
    
    if (!empty($THEME->customcorners)) {
        require_once($CFG->dirroot.'/lib/custom_corners_lib.php');
    }

    $week = optional_param('week', -1, PARAM_INT);

    // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);
  
    $preferred_width_left  = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]),  
                                            BLOCK_L_MAX_WIDTH);
    $preferred_width_right = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), 
                                            BLOCK_R_MAX_WIDTH);

    if ($week != -1) {
        $displaysection = course_set_display($course->id, $week);
    } else {
        if (isset($USER->display[$course->id])) {
            $displaysection = $USER->display[$course->id];
        } else {
            $displaysection = course_set_display($course->id, 0);
        }
    }

    $streditsummary  = get_string('editsummary');
    $stradd          = get_string('add');
    $stractivities   = get_string('activities');
    $strshowallweeks = get_string('showallweeks');
    $strweek         = get_string('week');
    $strgroups       = get_string('groups');
    $strgroupmy      = get_string('groupmy');
    $editing         = $PAGE->user_is_editing();

    if ($editing) {
        $strstudents = moodle_strtolower($course->students);
        $strweekhide = get_string('weekhide', '', $strstudents);
        $strweekshow = get_string('weekshow', '', $strstudents);
        $strmoveup   = get_string('moveup');
        $strmovedown = get_string('movedown');
    }

    $context = get_context_instance(CONTEXT_COURSE, $course->id);
/// Layout the whole page as three big columns.
    echo '<table id="layout-table" cellspacing="0" summary="'.get_string('layouttable').'"><tr>';
    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':
 
/// The left column ...

    if (blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $editing) {
        echo '<td style="width:'.$preferred_width_left.'px" id="left-column">';

        if (!empty($THEME->customcorners)) print_custom_corners_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        if (!empty($THEME->customcorners)) print_custom_corners_end();

        echo '</td>';
    }
            break;
            case 'middle':
/// Start main column
    echo '<td id="middle-column">';

    if (!empty($THEME->customcorners)) print_custom_corners_start();
        
    echo '<a name="startofcontent"></a>';

    print_heading_block(get_string('layout.synchronous','samouk'), 'outline');

    echo '<table class="weeks" width="100%" summary="'.get_string('layouttable').'">';
    
/// If user is teacher, print publication status and control panel 
    if (isediting($course->id) && has_capability('moodle/course:update', $context)) {
        echo '<tr id="section-viewcontrol" class="section main">';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        
        echo '<div class="summary" style="float:left; width:90%;">';
        $summaryformatoptions->noclean = true;
        if ($course->visible) {
            echo format_text(get_string('layout.showusers.yes','samouk'), FORMAT_HTML, $summaryformatoptions);        
        } else {
            echo format_text(get_string('layout.showusers.no','samouk'), FORMAT_HTML, $summaryformatoptions);
        }
        
        echo "</div>";
        echo "<div style='float:right; width:60px; text-align:right;'>"; // showhide button
        
        // Show a "Show/hide course" button
        $options=array();
        $options['id']=$course->id;
        $options['backurl']='/samouk/course/view.php?id='.$course->id;
        print_single_button($CFG->wwwroot.'/course/showhide.php', $options, ($course->visible ? get_string('hide') : get_string('view')));
        
        echo '</div></td>';   // end of a showhide button
        echo '<td class="right side">&nbsp;</td>';
        echo '</tr>';
        echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
    }

/// If currently moving a file then show the current clipboard
    if (ismoving($course->id)) {
        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', addslashes($USER->activitycopyname)));
        $strcancel= get_string('cancel');
        echo '<tr class="clipboard">';
        echo '<td colspan="3">';
        echo $stractivityclipboard.'&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey='.$USER->sesskey.'">'.$strcancel.'</a>)';
        echo '</td>';
        echo '</tr>';
    }

/// Print Section 0 with general activities

    $section = 0;
    $thissection = $sections[$section];

    if ($thissection->summary or $thissection->sequence or isediting($course->id)) {
        echo '<tr id="section-0" class="section main">';
        echo '<td class="left side">&nbsp;</td>';
        echo '<td class="content">';
        
        echo '<div class="summary">';
        $summaryformatoptions->noclean = true;
        echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

        if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
            echo '<a title="'.$streditsummary.'" '.
                 ' href="editsection.php?id='.$thissection->id.'"><img src="'.$CFG->pixpath.'/t/edit.gif" '.
                 'class="iconsmall edit" alt="'.$streditsummary.'" /></a><br /><br />';
        }
        echo '</div>';
        
        print_section($course, $thissection, $mods, $modnamesused);

        if (isediting($course->id)) {
            print_section_add_menus($course, $section, $modnames);
        }

        echo '</td>';
        echo '<td class="right side">&nbsp;</td>';
        echo '</tr>';
        echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';

    }


/// Now all the normal modules by week
/// Everything below uses "section" terminology - each "section" is a week.

    $timenow = time();
    $weekdate = $course->startdate;    // this should be 0:00 Monday of that week
    $weekdate += 7200;                 // Add two hours to avoid possible DST problems
    $section = 1;
    $sectionmenu = array();
    $weekofseconds = 604800;
    $course->enddate = $course->startdate + ($weekofseconds * $course->numsections);

    $strftimedateshort = ' '.get_string('strftimedateshort');

    while ($weekdate < $course->enddate) {

        $nextweekdate = $weekdate + ($weekofseconds);
        $weekday = userdate($weekdate, $strftimedateshort);
        $endweekday = userdate($weekdate+518400, $strftimedateshort);

        if (!empty($sections[$section])) {
            $thissection = $sections[$section];

        } else {
            unset($thissection);
            $thissection->course = $course->id;   // Create a new week structure
            $thissection->section = $section;
            $thissection->summary = '';
            $thissection->visible = 1;
            if (!$thissection->id = insert_record('course_sections', $thissection)) {
                notify('Error inserting new week!');
            }
        }

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

        if (!empty($displaysection) and $displaysection != $section) {  // Check this week is visible
            if ($showsection) {
                $sectionmenu['week='.$section] = s("$strweek $section |     $weekday - $endweekday");
            }
            $section++;
            $weekdate = $nextweekdate;
            continue;
        }

        if ($showsection) {
            // user has rights to view this section
            $currentweek = (($weekdate <= $timenow) && ($timenow < $nextweekdate));

            if (!$thissection->visible) {
                $sectionstyle = ' hidden';
            } else if ($currentweek) {
                $sectionstyle = ' current';
            } else {
                $sectionstyle = '';
            }

            echo '<tr id="section-'.$section.'" class="section main'.$sectionstyle.'">';
            echo '<td class="left side">&nbsp;</td>';


            if (ajaxenabled() && $editing) {
                // Temporarily hide the dates for the weeks. We do it this way
                // for now. Eventually, we'll have to modify the javascript code
                // to handle re-calculation of dates when sections are moved
                // around. For now, just hide all the dates to avoid confusion.
                $weekperiod = '';
            } else {
                $weekperiod = $weekday.' - '.$endweekday;
            }


            echo '<td class="content">';
            if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                echo '<div class="weekdates">'.$weekperiod.' ('.get_string('notavailable').')</div>';

            } else {
                echo '<div class="summary">';
                echo '  <div class="heading">';
                echo $strweek.': '.$weekperiod;
                echo '  </div>';
                
                echo '<div class="operations">';
                if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    // show course editing icons in editing mode    
                    if ($section > 1) {                 // Add a arrow to move section up
                        echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=-1&amp;sesskey='.$USER->sesskey.'#section-'.($section-1).'" title="'.$strmoveup.'">'.
                             '<img src="'.$CFG->pixpath.'/t/up.gif" alt="'.$strmoveup.'" /></a>&nbsp;';
                    }
    
                    if ($section < $course->numsections) {    // Add a arrow to move section down
                        echo '<a href="view.php?id='.$course->id.'&amp;random='.rand(1,10000).'&amp;section='.$section.'&amp;move=1&amp;sesskey='.$USER->sesskey.'#section-'.($section+1).'" title="'.$strmovedown.'">'.
                             '<img src="'.$CFG->pixpath.'/t/down.gif" alt="'.$strmovedown.'" /></a>&nbsp;';
                    }
                    
                    if ($thissection->visible) {        // Show the hide/show eye
                        echo '<a href="view.php?id='.$course->id.'&amp;hide='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strweekhide.'">'.
                             '<img src="'.$CFG->pixpath.'/i/hide.gif" alt="'.$strweekhide.'" /></a>&nbsp;';
                    } else {
                        echo '<a href="view.php?id='.$course->id.'&amp;show='.$section.'&amp;sesskey='.$USER->sesskey.'#section-'.$section.'" title="'.$strweekshow.'">'.
                             '<img src="'.$CFG->pixpath.'/i/show.gif" alt="'.$strweekshow.'" /></a>&nbsp;';
                    }
    
                }
                echo '</div>';
                
                $summaryformatoptions->noclean = true;
                echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                if (isediting($course->id) && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                    echo ' <a title="'.$streditsummary.'" href="editsection.php?id='.$thissection->id.'">'.
                         '<img src="'.$CFG->pixpath.'/t/edit.gif" class="iconsmall edit" alt="'.$streditsummary.'" /></a><br /><br />';
                }
                echo '</div>';

                print_section($course, $thissection, $mods, $modnamesused);

                if (isediting($course->id)) {
                    print_section_add_menus($course, $section, $modnames);
                }
            }
            echo '</td>';

            echo '<td class="right side">';

            if ($USER->su_isadvanced) {
                // for advanced user show possibility to show only this section
	            if ($displaysection == $section) {
	                echo '<a href="view.php?id='.$course->id.'&amp;week=0#section-'.$section.'" title="'.$strshowallweeks.'">'.
	                     '<img src="'.$CFG->pixpath.'/i/all.gif" class="icon wkall" alt="'.$strshowallweeks.'" /></a><br />';
	            } else {
	                $strshowonlyweek = get_string("showonlyweek", "", $section);
	                echo '<a href="view.php?id='.$course->id.'&amp;week='.$section.'" title="'.$strshowonlyweek.'">'.
	                     '<img src="'.$CFG->pixpath.'/i/one.gif" class="icon wkone" alt="'.$strshowonlyweek.'" /></a><br />';
	            }
            }

            echo '</td></tr>';
            echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
        }

        $section++;
        $weekdate = $nextweekdate;
    }
    echo '</table>';

    if (!empty($sectionmenu)) {
        echo '<div align="center" class="jumpmenu">';
        echo popup_form($CFG->wwwroot.'/course/view.php?id='.$course->id.'&amp;', $sectionmenu,
                   'sectionmenu', '', get_string('jumpto'), '', '', true);
        echo '</div>';
    }

    if (!empty($THEME->customcorners)) print_custom_corners_end();

    echo '</td>';

            break;
            case 'right':
    // The right column
    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $editing) {
        echo '<td style="width: '.$preferred_width_right.'px;" id="right-column">';

        if (!empty($THEME->customcorners)) print_custom_corners_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
        if (!empty($THEME->customcorners)) print_custom_corners_end();

        echo '</td>';
    }

            break;
        }
    }
    echo '</tr></table>';

?>
