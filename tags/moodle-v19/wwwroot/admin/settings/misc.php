<?php // $Id: misc.php,v 1.14 2007/08/16 21:14:09 skodak Exp $

// * Miscellaneous settings

// Experimental settings page
$temp = new admin_settingpage('experimental', get_string('experimental', 'admin'));
$temp->add(new admin_setting_configcheckbox('enableglobalsearch', get_string('enableglobalsearch', 'admin'), get_string('configenableglobalsearch', 'admin'), 0));
$temp->add(new admin_setting_configcheckbox('smartpix', get_string('smartpix', 'admin'), get_string('configsmartpix', 'admin'), 0));
$temp->add(new admin_setting_configcheckbox('enablehtmlpurifier', get_string('enablehtmlpurifier', 'admin'), get_string('configenablehtmlpurifier', 'admin'), 0));
$temp->add(new admin_setting_configcheckbox('enablegroupings', get_string('enablegroupings', 'admin'), get_string('configenablegroupings', 'admin'), 0));

$ADMIN->add('misc', $temp);

// XMLDB editor
$ADMIN->add('misc', new admin_externalpage('xmldbeditor', get_string('xmldbeditor'), "$CFG->wwwroot/$CFG->admin/xmldb/"));


// hidden scripts linked from elsewhere
$ADMIN->add('misc', new admin_externalpage('oacleanup', 'Online Assignment Cleanup', $CFG->wwwroot.'/'.$CFG->admin.'/oacleanup.php', 'moodle/site:config', true));
$ADMIN->add('misc', new admin_externalpage('upgradeforumread', 'Upgrade forum', $CFG->wwwroot.'/'.$CFG->admin.'/upgradeforumread.php', 'moodle/site:config', true));
$ADMIN->add('misc', new admin_externalpage('upgradelogs', 'Upgrade logs', $CFG->wwwroot.'/'.$CFG->admin.'/upgradelogs.php', 'moodle/site:config', true));
$ADMIN->add('misc', new admin_externalpage('multilangupgrade', get_string('multilangupgrade', 'admin'), $CFG->wwwroot.'/'.$CFG->admin.'/multilangupgrade.php', 'moodle/site:config', !empty($CFG->filter_multilang_converted)));

?>
