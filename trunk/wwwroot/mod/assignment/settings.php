<?php  //$Id: settings.php,v 1.1.2.2 2007/12/19 17:38:46 skodak Exp $

require_once($CFG->dirroot.'/mod/assignment/lib.php');

$settings->add(new admin_setting_configselect('assignment_maxbytes', get_string('maximumsize', 'assignment'),
                   get_string('configmaxbytes', 'assignment'), 1048576, get_max_upload_sizes($CFG->maxbytes)));

$options = array(ASSIGNMENT_COUNT_WORDS   => trim(get_string('numwords', '')),
                 ASSIGNMENT_COUNT_LETTERS => trim(get_string('numletters', '')));
$settings->add(new admin_setting_configselect('assignment_itemstocount', get_string('itemstocount', 'assignment'),
                   get_string('configitemstocount', 'assignment'), ASSIGNMENT_COUNT_WORDS, $options));

?>
