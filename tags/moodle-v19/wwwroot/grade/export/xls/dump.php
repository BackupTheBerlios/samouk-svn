<?php  //$Id: dump.php,v 1.3 2007/09/27 06:51:56 skodak Exp $

$nomoodlecookie = true; // session not used here
require '../../../config.php';

$id = required_param('id', PARAM_INT); // course id
if (!$course = get_record('course', 'id', $id)) {
    print_error('nocourseid');
}

require_user_key_login('grade/export', $id); // we want different keys for each course

if (empty($CFG->gradepublishing)) {
    error('Grade publishing disabled');
}

$context = get_context_instance(CONTEXT_COURSE, $id);
require_capability('gradeexport/xls:publish', $context);

// use the same page parameters as export.php and append &key=sdhakjsahdksahdkjsahksadjksahdkjsadhksa
require 'export.php';

?>