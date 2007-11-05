<?php  // $Id: tabs.php,v 1.2 2007/09/24 21:55:16 mattc-catalyst Exp $
    $row = $tabs = array();
    $row[] = new tabobject('groups',
                           $CFG->wwwroot.'/group/index.php?id='.$courseid,
                           get_string('groups'));

    $row[] = new tabobject('groupings',
                           $CFG->wwwroot.'/group/groupings.php?id='.$courseid,
                           get_string('groupings', 'group'));
    $row[] = new tabobject('overview',
                           $CFG->wwwroot.'/group/overview.php?id='.$courseid,
                           get_string('overview', 'group'));
    $tabs[] = $row;
    echo '<div class="groupdisplay">';
    print_tabs($tabs, $currenttab);
    echo '</div>';
?>
