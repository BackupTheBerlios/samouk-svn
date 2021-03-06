<?php  // $Id: tabs.php,v 1.1 2007/09/28 20:12:44 skodak Exp $
    $row = $tabs = array();

    $row[] = new tabobject('lettersview',
                           $CFG->wwwroot.'/grade/edit/letter/index.php?id='.$COURSE->id,
                           get_string('letters', 'grades'));

    if (has_capability('moodle/grade:manageletters', $context)) {
        $row[] = new tabobject('lettersedit',
                               $CFG->wwwroot.'/grade/edit/letter/edit.php?id='.$context->id,
                               get_string('edit'));
    }

    $tabs[] = $row;

    echo '<div class="letterdisplay">';
    print_tabs($tabs, $currenttab);
    echo '</div>';

?>
