<?php  //$Id: calculation.php,v 1.7 2007/08/14 06:05:07 nicolasconnault Exp $

require_once '../../../config.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->libdir.'/mathslib.php';
require_once 'calculation_form.php';

$courseid  = required_param('courseid', PARAM_INT);
$id        = required_param('id', PARAM_INT);
$section   = optional_param('section', 'calculation', PARAM_ALPHA);
$idnumbers = optional_param('idnumbers', null, PARAM_RAW);

if (!$course = get_record('course', 'id', $courseid)) {
    print_error('nocourseid');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/grade:manage', $context);

// default return url
$gpr = new grade_plugin_return();
$returnurl = $gpr->get_return_url($CFG->wwwroot.'/grade/report.php?id='.$course->id);

if (!$grade_item = grade_item::fetch(array('id'=>$id, 'courseid'=>$course->id))) {
    error('Incorect item id');
}

// module items and items without grade can not have calculation
if (($grade_item->is_normal_item() and !$grade_item->is_outcome_item())
  or ($grade_item->gradetype != GRADE_TYPE_VALUE and $grade_item->gradetype != GRADE_TYPE_SCALE)) {
    redirect($returnurl, get_string('errornocalculationallowed', 'grades')); //TODO: localize
}

$mform = new edit_calculation_form(null, array('gpr'=>$gpr, 'itemid' => $grade_item->id));

if ($mform->is_cancelled()) {
    redirect($returnurl);

}

$calculation = calc_formula::localize($grade_item->calculation);
$calculation = grade_item::denormalize_formula($calculation, $grade_item->courseid);
$mform->set_data(array('courseid'=>$grade_item->courseid, 'calculation'=>$calculation, 'id'=>$grade_item->id, 'itemname'=>$grade_item->itemname));

$errors = array();

if ($data = $mform->get_data(false)) {
    $calculation = calc_formula::unlocalize($data->calculation);
    $grade_item->set_calculation($calculation);

    redirect($returnurl);

} elseif (!empty($section) AND $section='idnumbers' AND !empty($idnumbers)) { // Handle idnumbers separately (non-mform)
    //first validate and store the new idnumbers
    foreach ($idnumbers as $giid => $value) {
        if ($gi = grade_item::fetch(array('id' => $giid))) {
            if ($gi->itemtype == 'mod') {
                $cm = get_coursemodule_from_instance($gi->itemmodule, $gi->iteminstance, $gi->courseid);
            } else {
                $cm = null;
            }

            if (!grade_verify_idnumber($value, $gi, $cm)) {
                $errors[$giid] = get_string('idnumbertaken');
                continue;
            }

            if (empty($gi->idnumber) and !$gi->add_idnumber(stripslashes($idnumbers[$gi->id]))) {
                $errors[$giid] = get_string('error');
                continue;
            }
        } else {
            $errors[$giid] = 'Could not fetch the grade_item with id=' . $giid;
        }
    }
}

$gtree = new grade_tree($course->id, false, false);

$strgrades          = get_string('grades');
$strgraderreport    = get_string('graderreport', 'grades');
$strcalculationedit = get_string('editcalculation', 'grades');

$navigation = grade_build_nav(__FILE__, $strcalculationedit, array('courseid' => $courseid));

print_header_simple($strgrades . ': ' . $strgraderreport, ': ' . $strcalculationedit, $navigation, '', '', true, '', navmenu($course));

$mform->display();
// Now show the gradetree with the idnumbers add/edit form
echo '
<form class="mform" id="mform2" method="post" action="' . $CFG->wwwroot . '/grade/edit/tree/calculation.php?courseid='.$courseid.'&amp;id='.$id.'">
	<div style="display: none;">
        <input type="hidden" value="'.$id.'" name="id"/>
        <input type="hidden" value="'.$courseid.'" name="courseid"/>
        <input type="hidden" value="'.$gpr->type.'" name="gpr_type"/>
        <input type="hidden" value="'.$gpr->plugin.'" name="gpr_plugin"/>
        <input type="hidden" value="'.$gpr->courseid.'" name="gpr_courseid"/>
        <input type="hidden" value="'.sesskey().'" name="sesskey"/>
        <input type="hidden" value="idnumbers" name="section"/>
    </div>

	<fieldset id="idnumbers" class="clearfix">
		<legend class="ftoggler">'.get_string('idnumbers', 'grades').'</legend>
        <div class="fcontainer clearfix">
            <ul>
            ' . get_grade_tree($gtree, $gtree->top_element, $id, $errors) . '
            </ul>
        </div>
    </fieldset>
    <div class="fitem" style="text-align: center;">
        <input id="id_addidnumbers" type="submit" value="'.get_string('addidnumbers', 'grades').'" name="addidnumbers" />
    </div>
</form>';

print_footer($course);
die();


/**
 * Simplified version of the print_grade_tree() recursive function found in grade/edit/tree/index.php
 * Only prints a tree with a basic icon for each element, and an edit field for
 * items without an idnumber.
 * @param object $gtree
 * @param object $element
 * @param int $current_itemid The itemid of this page: should be excluded from the tree
 * @param array $errors An array of idnumbers => error
 * @return string
 */
function get_grade_tree(&$gtree, $element, $current_itemid=null, $errors=null) {
    global $CFG;

    $object     = $element['object'];
    $eid        = $element['eid'];
    $type       = $element['type'];
    $grade_item = $object->get_grade_item();

    $name = $object->get_name();
    $return_string = '';

    //TODO: improve outcome visualisation
    if ($type == 'item' and !empty($object->outcomeid)) {
        $name = $name.' ('.get_string('outcome', 'grades').')';
    }

    $idnumber = $object->get_idnumber();

    // Don't show idnumber or input field for current item if given to function. Highlight the item instead.
    if ($type != 'category') {
        if (is_null($current_itemid) OR $grade_item->id != $current_itemid) {
            if ($idnumber) {
                $name .= ": [[$idnumber]]";
            } else {
                $closingdiv = '';
                if (!empty($errors[$grade_item->id])) {
                    $name .= '<div class="error"><span class="error">' . $errors[$grade_item->id].'</span><br />'."\n";
                    $closingdiv = "</div>\n";
                }
                $name .= '<input class="idnumber" id="id_idnumber_'.$grade_item->id.'" type="text" name="idnumbers['.$grade_item->id.']" />' . "\n";
                $name .= $closingdiv;
            }
        } else {
            $name = "<strong>$name</strong>";
        }
    }

    $icon = '<img src="'.$CFG->wwwroot.'/pix/spacer.gif" class="icon" alt=""/>' . "\n";
    $last = '';
    $catcourseitem = false;

    switch ($type) {
        case 'item':
            if ($object->itemtype == 'mod') {
                $icon = '<img src="'.$CFG->modpixpath.'/'.$object->itemmodule.'/icon.gif" class="icon" alt="'
                      . get_string('modulename', $object->itemmodule).'"/>' . "\n";
            } else if ($object->itemtype == 'manual') {
                //TODO: add manual grading icon
                if (empty($object->outcomeid)) {
                    $icon = '<img src="'.$CFG->pixpath.'/t/edit.gif" class="icon" alt="'
                          . get_string('manualgrade', 'grades').'"/>' . "\n"; // TODO: localize
                } else {
                    $icon = '<img src="'.$CFG->pixpath.'/i/outcomes.gif" class="icon" alt="'
                          . get_string('outcome', 'grades').'"/>' . "\n";
                }
            }
            break;
        case 'courseitem':
        case 'categoryitem':
            $icon = '<img src="'.$CFG->pixpath.'/i/category_grade.gif" class="icon" alt="'.get_string('categorygrade').'"/>' . "\n"; // TODO: localize
            $catcourseitem = true;
            break;
        case 'category':
            $icon = '<img src="'.$CFG->pixpath.'/f/folder.gif" class="icon" alt="'.get_string('category').'"/>' . "\n";
            break;
    }

    if ($type != 'category') {
        $return_string .= '<li class="'.$type.'">'.$icon.$name.'</li>' . "\n";
    } else {
        $return_string .= '<li class="'.$type.'">'.$icon.$name . "\n";
        $return_string .= '<ul class="catlevel'.$element['depth'].'">'."\n";
        $last = null;
        foreach($element['children'] as $child_el) {
            $return_string .= get_grade_tree($gtree, $child_el, $current_itemid, $errors);
        }
        if ($last) {
            $return_string .= get_grade_tree($gtree, $last, $current_itemid, $errors);
        }
        $return_string .= '</ul></li>'."\n";
    }

    return $return_string;
}

?>
