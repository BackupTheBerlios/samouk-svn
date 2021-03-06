<?php  //$Id: lib.php,v 1.3 2007/10/07 13:04:52 skodak Exp $

require_once $CFG->libdir.'/gradelib.php';
require_once($CFG->libdir.'/xmlize.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/import/lib.php';

function import_xml_grades($text, $course, &$error) {
    global $USER;

    $importcode = get_new_importcode();

    $status = true;

    $content = xmlize($text);

    if ($results = $content['results']['#']['result']) {

        foreach ($results as $i => $result) {
            if (!$grade_items = grade_item::fetch_all(array('idnumber'=>$result['#']['assignment'][0]['#'], 'courseid'=>$course->id))) {
                // gradeitem does not exist
                // no data in temp table so far, abort
                $status = false;
                $error  = get_string('errincorrectidnumber', 'gradeimport_xml');
                break;
            } else if (count($grade_items) != 1) {
                $status = false;
                $error  = get_string('errduplicateidnumber', 'gradeimport_xml');
                break;
            } else {
                $grade_item = reset($grade_items);
            }

            // grade item locked, abort
            if ($grade_item->is_locked()) {
                $status = false;
                $error  = get_string('gradeitemlocked', 'grades');
                break;
            }

            // check if user exist and convert idnember to user id
            if (!$user = get_record('user', 'idnumber', addslashes($result['#']['student'][0]['#']))) {
                // no user found, abort
                $status = false;
                $error = get_string('baduserid', 'grades');
                break;
            }

            // check if grade_grade is locked and if so, abort
            if ($grade_grade = new grade_grade(array('itemid'=>$grade_item->id, 'userid'=>$user->id))) {
                $grade_grade->grade_item =& $grade_item;
                if ($grade_grade->is_locked()) {
                    // individual grade locked, abort
                    $status = false;
                    $error  = get_string('gradegradeslocked', 'grades');
                    break;
                }
            }

            $newgrade = new object();
            $newgrade->itemid     = $grade_item->id;
            $newgrade->userid     = $user->id;
            $newgrade->importcode = $importcode;
            $newgrade->importer   = $USER->id;

            // check grade value exists and is a numeric grade
            if (isset($result['#']['score'][0]['#'])) {
                if (is_numeric($result['#']['score'][0]['#'])) {
                    $newgrade->finalgrade = $result['#']['score'][0]['#'];
                } else {
                    $status = false;
                    $error = get_string('badgrade', 'grades');
                    break;
                }
            } else {
                $newgrade->finalgrade = NULL;
            }

            // check grade feedback exists
            if (isset($result['#']['feedback'][0]['#'])) {
                $newgrade->feedback = $result['#']['feedback'][0]['#'];
            } else {
                $newgrade->feedback = NULL;
            }

            // insert this grade into a temp table
            if (!insert_record('grade_import_values', addslashes_recursive($newgrade))) {
                $status = false;
                // could not insert into temp table
                $error = get_string('importfailed', 'grades');
                break;
            }
        }

    } else {
        // no results section found in xml,
        // assuming bad format, abort import
        $status = false;
        $error = get_string('errbadxmlformat', 'gradeimport_xml');
    }

    if ($status) {
        return $importcode;

    } else {
        import_cleanup($importcode);
        return false;
    }
}
?>