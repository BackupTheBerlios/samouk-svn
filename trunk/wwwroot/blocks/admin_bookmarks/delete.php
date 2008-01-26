<?php // $Id: delete.php,v 1.9.2.1 2007/12/19 17:38:44 skodak Exp $

require('../../config.php');

require_once($CFG->libdir.'/adminlib.php');

require_login();
$adminroot =& admin_get_root(false, false); // settings not required - only pages

if ($section = optional_param('section', '', PARAM_SAFEDIR) and confirm_sesskey()) {

    if (get_user_preferences('admin_bookmarks')) {

        $bookmarks = explode(',', get_user_preferences('admin_bookmarks'));

        $key = array_search($section, $bookmarks);

        if ($key === false) {
            error(get_string('nonexistentbookmark','admin'));
            die;
        }

        unset($bookmarks[$key]);
        $bookmarks = implode(',', $bookmarks);
        set_user_preference('admin_bookmarks', $bookmarks);

        $temp = $adminroot->locate($section);

        if (is_a($temp, 'admin_externalpage')) {
            redirect($temp->url, get_string('bookmarkdeleted','admin'));
        } elseif (is_a($temp, 'admin_settingpage')) {
            redirect($CFG->wwwroot . '/' . $CFG->admin . '/settings.php?section=' . $section);
        } else {
            redirect($CFG->wwwroot);
        }
        die;


    }

    error(get_string('nobookmarksforuser','admin'));
    die;

} else {
    error(get_string('invalidsection', 'admin'));
    die;
}

?>