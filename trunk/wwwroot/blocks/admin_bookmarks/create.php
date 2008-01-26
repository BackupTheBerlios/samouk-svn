<?php // $Id: create.php,v 1.9.2.2 2007/12/19 17:38:45 skodak Exp $

require('../../config.php');

require_once($CFG->libdir.'/adminlib.php');
require_login();
$adminroot =& admin_get_root(false, false);  // settings not required - only pages

if ($section = optional_param('section', '', PARAM_SAFEDIR) and confirm_sesskey()) {

    if (get_user_preferences('admin_bookmarks')) {
        $bookmarks = explode(',', get_user_preferences('admin_bookmarks'));

        if (in_array($section, $bookmarks)) {
            error(get_string('bookmarkalreadyexists','admin'));
            die;
        }

    } else {
        $bookmarks = array();
    }

    $temp = $adminroot->locate($section);

    if (is_a($temp, 'admin_settingpage') || is_a($temp, 'admin_externalpage')) {
        $bookmarks[] = $section;
        $bookmarks = implode(',', $bookmarks);
        set_user_preference('admin_bookmarks', $bookmarks);

    } else {
        error(get_string('invalidsection','admin'));
        die;
    }

    if (is_a($temp, 'admin_settingpage')) {
        redirect($CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=' . $section);

    } elseif (is_a($temp, 'admin_externalpage')) {
        redirect($temp->url);
    }

} else {
    error(get_string('invalidsection','admin'));
    die;
}


?>
