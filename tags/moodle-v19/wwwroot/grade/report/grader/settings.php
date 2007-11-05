<?php // $Id: settings.php,v 1.31 2007/10/07 18:15:58 skodak Exp $
require_once($CFG->libdir.'/grade/constants.php');

$strinherit             = get_string('inherit', 'grades');
$strpercentage          = get_string('percentage', 'grades');
$strreal                = get_string('real', 'grades');
$strletter              = get_string('letter', 'grades');
$strdefault             = get_string('default');

/// Add settings for this module to the $settings object (it's already defined)
$settings->add(new admin_setting_configtext('grade_report_studentsperpage', get_string('studentsperpage', 'grades'),
                                        get_string('configstudentsperpage', 'grades'), 20));

$settings->add(new admin_setting_configcheckbox('grade_report_quickgrading', get_string('quickgrading', 'grades'),
                                            get_string('configquickgrading', 'grades'), 1));

$settings->add(new admin_setting_configcheckbox('grade_report_quickfeedback', get_string('quickfeedback', 'grades'),
                                            get_string('configquickfeedback', 'grades'), 1));

$settings->add(new admin_setting_configselect('grade_report_aggregationposition', get_string('aggregationposition', 'grades'),
                                          get_string('configaggregationposition', 'grades'), GRADE_REPORT_AGGREGATION_POSITION_RIGHT,
                                          array(GRADE_REPORT_AGGREGATION_POSITION_LEFT => get_string('left', 'grades'),
                                                GRADE_REPORT_AGGREGATION_POSITION_RIGHT => get_string('right', 'grades'))));

$settings->add(new admin_setting_configselect('grade_report_aggregationview', get_string('aggregationview', 'grades'),
                                          get_string('configaggregationview', 'grades'), GRADE_REPORT_AGGREGATION_VIEW_FULL,
                                          array(GRADE_REPORT_AGGREGATION_VIEW_FULL => get_string('full', 'grades'),
                                                GRADE_REPORT_AGGREGATION_VIEW_AGGREGATES_ONLY => get_string('aggregatesonly', 'grades'),
                                                GRADE_REPORT_AGGREGATION_VIEW_GRADES_ONLY => get_string('gradesonly', 'grades'))));

$settings->add(new admin_setting_configselect('grade_report_meanselection', get_string('meanselection', 'grades'),
                                          get_string('configmeanselection', 'grades'), GRADE_REPORT_MEAN_ALL,
                                          array(GRADE_REPORT_MEAN_ALL => get_string('meanall', 'grades'),
                                                GRADE_REPORT_MEAN_GRADED => get_string('meangraded', 'grades'))));

// $settings->add(new admin_setting_configcheckbox('grade_report_enableajax', get_string('enableajax', 'grades'),
//                                            get_string('configenableajax', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showcalculations', get_string('showcalculations', 'grades'),
                                            get_string('configshowcalculations', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showeyecons', get_string('showeyecons', 'grades'),
                                            get_string('configshoweyecons', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showaverages', get_string('showaverages', 'grades'),
                                            get_string('configshowaverages', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showgroups', get_string('showgroups', 'grades'),
                                            get_string('configshowgroups', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showlocks', get_string('showlocks', 'grades'),
                                            get_string('configshowlocks', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showranges', get_string('showranges', 'grades'),
                                            get_string('configshowranges', 'grades'), 0));

$settings->add(new admin_setting_configcheckbox('grade_report_showuserimage', get_string('showuserimage', 'grades'),
                                            get_string('configshowuserimage', 'grades'), 1));

$settings->add(new admin_setting_configcheckbox('grade_report_showactivityicons', get_string('showactivityicons', 'grades'),
                                            get_string('configshowactivityicons', 'grades'), 1));

$settings->add(new admin_setting_configcheckbox('grade_report_shownumberofgrades', get_string('shownumberofgrades', 'grades'),
                                            get_string('configshownumberofgrades', 'grades'), 0));

$settings->add(new admin_setting_configselect('grade_report_averagesdisplaytype', get_string('averagesdisplaytype', 'grades'),
                                          get_string('configaveragesdisplaytype', 'grades'), GRADE_REPORT_PREFERENCE_DEFAULT,
                                          array(GRADE_REPORT_PREFERENCE_DEFAULT => $strdefault,
                                                GRADE_DISPLAY_TYPE_REAL => $strreal,
                                                GRADE_DISPLAY_TYPE_PERCENTAGE => $strpercentage,
                                                GRADE_DISPLAY_TYPE_LETTER => $strletter)));

$settings->add(new admin_setting_configselect('grade_report_rangesdisplaytype', get_string('rangesdisplaytype', 'grades'),
                                          get_string('configrangesdisplaytype', 'grades'), GRADE_REPORT_PREFERENCE_DEFAULT,
                                          array(GRADE_REPORT_PREFERENCE_DEFAULT => $strdefault,
                                                GRADE_DISPLAY_TYPE_REAL => $strreal,
                                                GRADE_DISPLAY_TYPE_PERCENTAGE => $strpercentage,
                                                GRADE_DISPLAY_TYPE_LETTER => $strletter)));

$settings->add(new admin_setting_configselect('grade_report_averagesdecimalpoints', get_string('averagesdecimalpoints', 'grades'),
                                          get_string('configaveragesdecimalpoints', 'grades'), 2,
                                          array(GRADE_REPORT_PREFERENCE_DEFAULT => $strdefault,
                                                 '0' => '0',
                                                 '1' => '1',
                                                 '2' => '2',
                                                 '3' => '3',
                                                 '4' => '4',
                                                 '5' => '5')));
$settings->add(new admin_setting_configselect('grade_report_rangesdecimalpoints', get_string('rangesdecimalpoints', 'grades'),
                                          get_string('configrangesdecimalpoints', 'grades'), 2,
                                          array(GRADE_REPORT_PREFERENCE_DEFAULT => $strdefault,
                                                 '0' => '0',
                                                 '1' => '1',
                                                 '2' => '2',
                                                 '3' => '3',
                                                 '4' => '4',
                                                 '5' => '5')));


?>
