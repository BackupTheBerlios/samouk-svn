<?php  // $Id: tabs.php,v 1.21 2007/09/04 13:28:50 jamiesensei Exp $
/**
 * Sets up the tabs used by the quiz pages based on the users capabilites.
 *
 * @author Tim Hunt and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package quiz
 */

if (empty($quiz)) {
    error('You cannot call this script in that way');
}
if (!isset($currenttab)) {
    $currenttab = '';
}
if (!isset($cm)) {
    $cm = get_coursemodule_from_instance('quiz', $quiz->id);
}


$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (!isset($contexts)){
    $contexts = new question_edit_contexts($context);
}
$tabs = array();
$row  = array();
$inactive = array();
$activated = array();

if (has_capability('mod/quiz:view', $context)) {
    $row[] = new tabobject('info', "$CFG->wwwroot/mod/quiz/view.php?q=$quiz->id", get_string('info', 'quiz'));
}
if (has_capability('mod/quiz:viewreports', $context)) {
    $row[] = new tabobject('reports', "$CFG->wwwroot/mod/quiz/report.php?q=$quiz->id", get_string('results', 'quiz'));
}
if (has_capability('mod/quiz:preview', $context)) {
    $row[] = new tabobject('preview', "$CFG->wwwroot/mod/quiz/attempt.php?q=$quiz->id", get_string('preview', 'quiz'));
}
if ($contexts->have_one_edit_tab_cap('editq')) {
    $row[] = new tabobject('edit', "$CFG->wwwroot/mod/quiz/edit.php?cmid=$cm->id", get_string('edit'));
}

if ($currenttab == 'info' && count($row) == 1) {
    // Don't show only an info tab (e.g. to students).
} else {
    $tabs[] = $row;
}

if ($currenttab == 'reports' and isset($mode)) {
    $inactive[] = 'reports';
    $activated[] = 'reports';

    $allreports = get_list_of_plugins("mod/quiz/report");
    $reportlist = array ('overview', 'regrade', 'grading', 'analysis');   // Standard reports we want to show first

    foreach ($allreports as $report) {
        if (!in_array($report, $reportlist)) {
            $reportlist[] = $report;
        }
    }

    $row  = array();
    $currenttab = '';
    foreach ($reportlist as $report) {
        $row[] = new tabobject($report, "$CFG->wwwroot/mod/quiz/report.php?q=$quiz->id&amp;mode=$report",
                                get_string($report, 'quiz_'.$report));
        if ($report == $mode) {
            $currenttab = $report;
        }
    }
    $tabs[] = $row;
}

if ($currenttab == 'edit' and isset($mode)) {
    $inactive[] = 'edit';
    $activated[] = 'edit';

    $row  = array();
    $currenttab = $mode;

    $strquiz = get_string('modulename', 'quiz');
    $streditingquiz = get_string("editinga", "moodle", $strquiz);

    if ($contexts->have_one_edit_tab_cap('editq')) {
        $row[] = new tabobject('editq', "$CFG->wwwroot/mod/quiz/edit.php?".$thispageurl->get_query_string(), $strquiz, $streditingquiz);
    }
    questionbank_navigation_tabs($row, $contexts, $thispageurl->get_query_string());
    $tabs[] = $row;

}

print_tabs($tabs, $currenttab, $inactive, $activated);

?>
