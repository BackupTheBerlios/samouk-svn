<?php  //$Id: item_form.php,v 1.18 2007/10/07 10:51:52 skodak Exp $

require_once $CFG->libdir.'/formslib.php';

class edit_item_form extends moodleform {
    var $displayoptions;

    function definition() {
        global $COURSE, $CFG;

        $mform =& $this->_form;

/// visible elements
        $mform->addElement('header', 'general', get_string('gradeitem', 'grades'));

        $mform->addElement('text', 'itemname', get_string('itemname', 'grades'));
        $mform->addElement('text', 'iteminfo', get_string('iteminfo', 'grades'));
        $mform->setHelpButton('iteminfo', array(false, get_string('iteminfo', 'grades'),
                false, true, false, get_string('iteminfohelp', 'grades')));

        $mform->addElement('text', 'idnumber', get_string('idnumber'));
        $mform->setHelpButton('idnumber', array(false, get_string('idnumber'),
                false, true, false, get_string('idnumberhelp', 'grades')));

        $options = array(GRADE_TYPE_NONE=>get_string('typenone', 'grades'),
                         GRADE_TYPE_VALUE=>get_string('typevalue', 'grades'),
                         GRADE_TYPE_SCALE=>get_string('typescale', 'grades'),
                         GRADE_TYPE_TEXT=>get_string('typetext', 'grades'));

        $mform->addElement('select', 'gradetype', get_string('gradetype', 'grades'), $options);
        $mform->setHelpButton('gradetype', array(false, get_string('gradetype', 'grades'),
                false, true, false, get_string('gradetypehelp', 'grades')));
        $mform->setDefault('gradetype', GRADE_TYPE_VALUE);

        //$mform->addElement('text', 'calculation', get_string('calculation', 'grades'));
        //$mform->disabledIf('calculation', 'gradetype', 'eq', GRADE_TYPE_TEXT);
        //$mform->disabledIf('calculation', 'gradetype', 'eq', GRADE_TYPE_NONE);

        $options = array(0=>get_string('usenoscale', 'grades'));
        if ($scales = get_records('scale')) {
            foreach ($scales as $scale) {
                $options[$scale->id] = format_string($scale->name);
            }
        }
        $mform->addElement('select', 'scaleid', get_string('scale'), $options);
        $mform->setHelpButton('scaleid', array(false, get_string('scaleid', 'grades'),
                false, true, false, get_string('scaleidhelp', 'grades', get_string('gradeitem', 'grades'))));
        $mform->disabledIf('scaleid', 'gradetype', 'noteq', GRADE_TYPE_SCALE);

        $mform->addElement('text', 'grademax', get_string('grademax', 'grades'));
        $mform->setHelpButton('grademax', array(false, get_string('grademax', 'grades'),
                false, true, false, get_string('grademaxhelp', 'grades')));
        $mform->disabledIf('grademax', 'gradetype', 'noteq', GRADE_TYPE_VALUE);

        $mform->addElement('text', 'grademin', get_string('grademin', 'grades'));
        $mform->setHelpButton('grademin', array(false, get_string('grademin', 'grades'),
                false, true, false, get_string('grademinhelp', 'grades')));
        $mform->disabledIf('grademin', 'gradetype', 'noteq', GRADE_TYPE_VALUE);

        $mform->addElement('text', 'gradepass', get_string('gradepass', 'grades'));
        $mform->setHelpButton('gradepass', array(false, get_string('gradepass', 'grades'),
                false, true, false, get_string('gradepasshelp', 'grades')));
        $mform->disabledIf('gradepass', 'gradetype', 'eq', GRADE_TYPE_NONE);
        $mform->disabledIf('gradepass', 'gradetype', 'eq', GRADE_TYPE_TEXT);

        $mform->addElement('text', 'multfactor', get_string('multfactor', 'grades'));
        $mform->setHelpButton('multfactor', array(false, get_string('multfactor', 'grades'),
                false, true, false, get_string('multfactorhelp', 'grades')));
        $mform->setAdvanced('multfactor');
        $mform->disabledIf('multfactor', 'gradetype', 'eq', GRADE_TYPE_NONE);
        $mform->disabledIf('multfactor', 'gradetype', 'eq', GRADE_TYPE_TEXT);

        $mform->addElement('text', 'plusfactor', get_string('plusfactor', 'grades'));
        $mform->setHelpButton('plusfactor', array(false, get_string('plusfactor', 'grades'),
                false, true, false, get_string('plusfactorhelp', 'grades')));
        $mform->setAdvanced('plusfactor');
        $mform->disabledIf('plusfactor', 'gradetype', 'eq', GRADE_TYPE_NONE);
        $mform->disabledIf('plusfactor', 'gradetype', 'eq', GRADE_TYPE_TEXT);

        $mform->addElement('text', 'aggregationcoef', get_string('aggregationcoef', 'grades'));
        $mform->setHelpButton('aggregationcoef', array(false, get_string('aggregationcoef', 'grades'),
                false, true, false, get_string('aggregationcoefhelp', 'grades')));

        /// grade display prefs
        $this->displayoptions = array(GRADE_DISPLAY_TYPE_DEFAULT => get_string('default', 'grades'),
                                      GRADE_DISPLAY_TYPE_REAL => get_string('real', 'grades'),
                                      GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                                      GRADE_DISPLAY_TYPE_LETTER => get_string('letter', 'grades'));
        $mform->addElement('select', 'display', null, $this->displayoptions);
        $mform->setHelpButton('display', array(false, get_string('gradedisplaytype', 'grades'),
                              false, true, false, get_string('configgradedisplaytype', 'grades')));


        $options = array(-1=>get_string('default', 'grades'), 0, 1, 2, 3, 4, 5);
        $mform->addElement('select', 'decimals', null, $options);
        $mform->setHelpButton('decimals', array(false, get_string('decimalpoints', 'grades'),
                              false, true, false, get_string('configdecimalpoints', 'grades')));
        $mform->setDefault('decimals', GRADE_REPORT_PREFERENCE_DEFAULT);
        $mform->disabledIf('decimals', 'display', 'eq', GRADE_DISPLAY_TYPE_LETTER);

        /// hiding
        /// advcheckbox is not compatible with disabledIf !!
        $mform->addElement('checkbox', 'hidden', get_string('hidden', 'grades'));
        $mform->setHelpButton('hidden', array('hidden', get_string('hidden', 'grades'), 'grade'));
        $mform->addElement('date_time_selector', 'hiddenuntil', get_string('hiddenuntil', 'grades'), array('optional'=>true));
        $mform->setHelpButton('hiddenuntil', array('hiddenuntil', get_string('hiddenuntil', 'grades'), 'grade'));
        $mform->disabledIf('hidden', 'hiddenuntil[off]', 'notchecked');

        /// locking
        $mform->addElement('advcheckbox', 'locked', get_string('locked', 'grades'));
        $mform->setHelpButton('locked', array('locked', get_string('locked', 'grades'), 'grade'));

        $mform->addElement('date_time_selector', 'locktime', get_string('locktime', 'grades'), array('optional'=>true));
        $mform->setHelpButton('locktime', array('locktime', get_string('locktime', 'grades'), 'grade'));
        $mform->disabledIf('locktime', 'gradetype', 'eq', GRADE_TYPE_NONE);

/// hidden params
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'itemtype', 'manual'); // all new items are manual only
        $mform->setType('itemtype', PARAM_ALPHA);

/// add return tracking info
        $gpr = $this->_customdata['gpr'];
        $gpr->add_mform_elements($mform);

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }


/// tweak the form - depending on existing data
    function definition_after_data() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        if ($id = $mform->getElementValue('id')) {
            $grade_item = grade_item::fetch(array('id'=>$id));

            if (!$grade_item->is_raw_used()) {
                $mform->removeElement('plusfactor');
                $mform->removeElement('multfactor');
            }

            if ($grade_item->is_outcome_item()) {
                // we have to prevent incompatible modifications of outcomes if outcomes disabled
                $mform->removeElement('grademax');
                $mform->removeElement('grademin');
                $mform->removeElement('gradetype');
                $mform->removeElement('display');
                $mform->removeElement('decimals');
                $mform->hardFreeze('scaleid');

            } else {
                if ($grade_item->is_normal_item()) {
                    // following items are set up from modules and should not be overrided by user
                    $mform->hardFreeze('itemname,idnumber,gradetype,grademax,grademin,scaleid');
                    //$mform->removeElement('calculation');
                }
            }
            //remove the aggregation coef element if not needed
            if ($grade_item->is_course_item()) {
                $mform->removeElement('aggregationcoef');

            } else if ($grade_item->is_category_item()) {
                $category = $grade_item->get_item_category();
                $parent_category = $category->get_parent_category();
                if (!$parent_category->is_aggregationcoef_used()) {
                    $mform->removeElement('aggregationcoef');
                }

            } else {
                $parent_category = $grade_item->get_parent_category();
                if (!$parent_category->is_aggregationcoef_used()) {
                    $mform->removeElement('aggregationcoef');
                }
            }

        } else {
            // all new items are manual, children of course category
            $mform->removeElement('plusfactor');
            $mform->removeElement('multfactor');

            $course_category = grade_category::fetch_course_category($COURSE->id);
            if (!$course_category->is_aggregationcoef_used()) {
                $mform->removeElement('aggregationcoef');
            }
        }

        // setup defaults and extra locking based on it
        $course_item = grade_item::fetch_course_item($COURSE->id);
        $default_gradedisplaytype = $course_item->get_displaytype();
        $default_gradedecimals    = $course_item->get_decimals();

        $option_value = 'error';
        foreach ($this->displayoptions as $key => $option) {
            if ($key == $default_gradedisplaytype) {
                $option_value = $option;
                break;
            }
        }
        $displaytypeEl =& $mform->getElement('display');
        $displaytypeEl->setLabel(get_string('gradedisplaytype', 'grades').' ('.get_string('default', 'grades').': '.$option_value.')');

        $decimalsEl =& $mform->getElement('decimals');
        $decimalsEl->setLabel(get_string('decimalpoints', 'grades').' ('.get_string('default', 'grades').': '.$default_gradedecimals.')');

        // Disable decimals if displaytype is DEFAULT and course or site displaytype is LETTER
        if ($default_gradedisplaytype == GRADE_DISPLAY_TYPE_LETTER) {
            $mform->disabledIf('decimals', 'display', "eq", GRADE_DISPLAY_TYPE_DEFAULT);
        }
    }


/// perform extra validation before submission
    function validation($data){
        $errors = array();

        if (array_key_exists('idnumber', $data)) {
            if ($data['id']) {
                $grade_item = new grade_item(array('id'=>$data['id'], 'courseid'=>$data['courseid']));
                if ($grade_item->itemtype == 'mod') {
                    $cm = get_coursemodule_from_instance($grade_item->itemmodule, $grade_item->iteminstance, $grade_item->courseid);
                } else {
                    $cm = null;
                }
            } else {
                $grade_item = null;
                $cm = null;
            }
            if (!grade_verify_idnumber($data['idnumber'], $grade_item, $cm)) {
                $errors['idnumber'] = get_string('idnumbertaken');
            }
        }

        /*
        if (array_key_exists('calculation', $data) and $data['calculation'] != '') {
            $grade_item = new grade_item(array('id'=>$data['id'], 'itemtype'=>$data['itemtype'], 'courseid'=>$data['courseid']));
            $result = $grade_item->validate_formula($data['calculation']);
            if ($result !== true) {
                $errors['calculation'] = $result;
            }
        }
        */

        if (array_key_exists('grademin', $data) and array_key_exists('grademax', $data)) {
            if ($data['grademax'] == $data['grademin'] or $data['grademax'] < $data['grademin']) {
                $errors['grademin'] = get_String('incorrectminmax', 'grades');
                $errors['grademax'] = get_String('incorrectminmax', 'grades');
            }
        }

        if (0 == count($errors)){
            return true;
        } else {
            return $errors;
        }
    }

}
?>
