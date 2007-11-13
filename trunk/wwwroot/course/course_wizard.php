<?php 
/**
 * Wizard creating new course
 * 
 * @name course_wizard.php
 * @author Kowy
 * @version 1.9.0
 */

    require_once('../config.php');
    require_once($CFG->dirroot.'/enrol/enrol.class.php');
/*    require_once($CFG->libdir.'/blocklib.php');*/
    require_once('lib.php');

    //require_once($CFG->libdir.'/formslib.php');
    require_once($CFG->libdir.'/pear/HTML/QuickForm/Controller.php');
    require_once($CFG->libdir.'/pear/HTML/QuickForm/Action/Display.php');

    // read required param
    $categoryid = $_SESSION['new_course.categoryid'];

	if ($categoryid) { // creating new course in this category
	    $course = null;
	    if (!$category = get_record('course_categories', 'id', $categoryid)) {
	        error(get_string('newcourse.error.badcategory', 'samouk'));
	    }
	    require_capability('moodle/course:create', get_context_instance(CONTEXT_COURSECAT, $category->id));
	} else {
	    error(get_string('newcourse.error.nocategory','samouk'));
	}

	// Start the session
	//session_start();

	/**
	 * Class for the first page of the wizard (opening info)
	 */
	class NewCourseInfo extends HTML_QuickForm_Page {
	
	    /**
	     * Build form content
	     *
	     */
	    function buildForm() {
	        $this->_formBuilt = true;
	        
	        require_login();
	    }
	} // class NewCourseInfo

	/**
	 * @uses $USER
	 * @uses $CFG
	 * Class for the second page of the wizard (basic setting)
	 */
	class NewCourseBasic extends HTML_QuickForm_Page {
		
		/**
		 * Check if given ShortName is unique
		 *
		 * @return True if is unique False in the contre
		 */
		function _checkFullNameUnicity($fullname) {
            if ($foundcourses = get_records('course', 'shortname', substr($fullname, 0, 99))) {
                // 2007-09-07 kowy - id is not used in Samouk => do not check it
                //if (!empty($data['id'])) {
                //    unset($foundcourses[$data['id']]);
                //
                if (!empty($foundcourses)) {
                	return false;
                }
                return true;
            }
            
            return true;
		}
	
		function definition() {
	        global $USER, $CFG;
	
	        $this->_category = $this->_customdata['category'];
		} // definition
		
		/**
		 * Build form content
		 *
		 */
		function buildForm() {
			require_login();
			
			$this->_formBuilt = true;
			
			global $category, $USER, $CFG;
			
			$systemcontext = get_context_instance(CONTEXT_SYSTEM);
	        $categorycontext = get_context_instance(CONTEXT_COURSECAT, $category->id);
	        $disable_meta = false; // basic meta course state protection; server-side security checks not needed
	        $coursecontext = null;
	        $context = $categorycontext;
	        
	        //////// create formset around basic setting
	        $this->addElement('header','basic_heading', get_string('heading.basic', 'samouk'));
	
	        //must have create course capability in categories in order to create course
	        if (has_capability('moodle/course:create', $categorycontext)) {
	            $displaylist = array();
	            $parentlist = array();
	            make_categories_list($displaylist, $parentlist);
	            foreach ($displaylist as $key=>$val) {
	                if (!has_capability('moodle/course:create', get_context_instance(CONTEXT_COURSECAT, $key))) {
	                    unset($displaylist[$key]);
	                }
	            }
	            $el = $this->addElement('select', 'category', get_string('category'), $displaylist);
	            $el->setSelected(array($category->id));
	        } else {
	            $this->addElement('hidden', 'category', null);
	        }
	        $this->setHelpButton('category', array('coursecategory', get_string('category')));
	        $this->setType('category', PARAM_INT);
	        
	        // generate form for a Fullname of the new course 
	        $this->addElement('text','fullname', get_string('fullname'),'maxlength="254" size="50"');
	        $this->setDefault('fullname', get_string('defaultcoursefullname'));
	        $this->setHelpButton('fullname', array('coursefullname', get_string('fullname')), true);
	        $this->registerRule("fullNameUnicity","callback","_checkFullNameUnicity", get_class($this));
	        $this->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
	        $this->addRule("fullname", get_string("newcourse.error.usedfullname","samouk"), "fullNameUnicity");
	        
	        $this->setType('fullname', PARAM_MULTILANG);
	        
	        // generate form for a Summary of the new course
	        $this->addElement('htmleditor','summary', get_string('summary'), array('rows'=> '10', 'cols'=>'65'));
	        $this->setHelpButton('summary', array('text', get_string('helptext')), true);
	        $this->addRule('summary', get_string('missingsummary'), 'required', null, 'client');
	        $this->setType('summary', PARAM_RAW);
	        $this->setDefault('summary', get_string('defaultcoursesummary'));
	        
		    // If we are creating a course, its enrol method isn't yet chosen, BUT the site has a default enrol method which we can use here
	        $enrol_object = $CFG;
	        if (!empty($course)) {
	            $enrol_oject = $course;
	        }
	        // generate form for a Price of the new course
	        if (method_exists(enrolment_factory::factory($enrol_object->enrol), 'print_entry') && $enrol_object->enrol != 'manual'){
	            $costgroup=array();
	            $currencies = get_list_of_currencies();
	            $costgroup[0]= & HTML_QuickForm::createElement('text','cost', '', 'maxlength="6" size="6"');
	            $costgroup[1]= & HTML_QuickForm::createElement('select', 'currency', '', $currencies);
	            $this->addGroup($costgroup, 'costgrp', get_string('cost'), '&nbsp;', false);
	            $this->setDefault('cost', '');
	            
	            //defining a rule for a form element within a group :
	            $costgrprules=array();
	            //set the message to null to tell Moodle to use a default message
	            //available for most rules, fetched from language pack (err_{rulename}).
	            $costgrprules['cost'][]=array(null, 'numeric', null, 'client');
	            $this->addGroupRule('costgrp',$costgrprules);
	            $this->setHelpButton('costgrp', array('cost', get_string('cost')), true);
	            $this->setDefault('currency', (empty($CFG->enrol_currency) ? 'CZK' : $CFG->enrol_currency));
	        }
	        
			
			$buttons[0] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
	        $buttons[1] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
	        $this->addGroup($buttons, 'buttons', '', array(' '), true);
            $this->closeHeaderBefore('buttons');   // print close tag for element Fieldset (heading)
	        
	        $this->setDefaultAction('next');
		}
		
		/**
		 * Add extra validation rules to the first page
		 * TODO zjisti, jak zobrazit chybove hlasky (ve $errors - ukladaji se do $data['valid'][$pageName])
		 */
		/*function validate() {
			$errors = array();
			
	        if ($foundcourses = get_records('course', 'shortname', substr($this->exportValue('fullname'), 0, 99))) {
	            // 2007-09-07 kowy - id is not used in Samouk => do not check it
                //if (!empty($data['id'])) {
	            //    unset($foundcourses[$data['id']]);
	            //}
	            if (!empty($foundcourses)) {
	                foreach ($foundcourses as $foundcourse) {
	                    $foundcoursenames[] = $foundcourse->fullname;
	                }
	                $foundcoursenamestring = implode(',', $foundcoursenames);
	                $errors['shortname']= get_string('shortnametaken', '', $foundcoursenamestring);
	            }
	        }
	        
	        if (0 == count($errors)){
	            return true;
	        } else {
	            return $errors;
	        }
		  }*/
	} // class NewCourseBasic

	/**
	 * @uses $USER
     * @uses $CFG
	 * Class for the third page of the wizard (select technology)
	 **/
	class NewCourseTechnology extends HTML_QuickForm_Page {
	    function definition() {
	        global $USER, $CFG;
	
	        $this->_category = $this->_customdata['category'];
	    } // definition
	    
	    /**
	     * Build form content
	     *
	     */
	    function buildForm() {
	    	require_login();
	    	$this->_formBuilt = true;
	
	    	global $category;
	    	// generate form for a checkbox list Technology of the new course
	        $this->addElement('header', 'tech_header', get_string('heading.technology', 'samouk'));
	        $technologies = array('async'=>get_string('formatasync'), 'sync'=>get_string('formatsync'));
	        $this->addElement('select', 'format', get_string('newcourse.selecttech','samouk'), $technologies);
	        
	        $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
	        $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
	        $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
	        $this->addGroup($buttons, 'buttons', '', '&nbsp', false);
	        
	        $this->setDefaultAction('next');
	    }
	} // class NewCourseTechnology

    /**
     * Class for the fourth page of the wizard (different for an async and sync)
     */
    class NewCourseSyncAsync extends HTML_QuickForm_Page {
    	
    	/**
    	 * Build form content
    	 */
    	function buildForm() {
    	   global $USER;
    	    
    		// add course start day
            $this->addElement('date_selector', 'startdate', get_string('startdate'));
            $this->setHelpButton('startdate', array('coursestartdate', get_string('startdate')), true);
            // in default start after seven days
            $this->setDefault('startdate', time() + 3600 * 24 * 7);
                
            $selectedTechnology = $this->controller->exportValue($this->controller->getPrevName($this->getAttribute('id')), 'format');
    		if ($selectedTechnology == 'async') {
    			// Asynchronous was chosen in the previous step
	            
	            // add course student limit
	            $maxstudents = array("5"=>"5", "10"=>"10", "15"=>"15", "20"=>"20", "25"=>"25", "30"=>"30", "1000000"=>get_string('unlimited'));
	            $this->addElement('select', 'maxstudents', get_string('newcourse.maxstudents', 'samouk'), $maxstudents);
	            $this->setDefault('maxstudents', "1000000");
	            
    		} else if ($USER->su_isadvanced) {
    			// Synchronous was chosen in the previos step

                // add a start enrolment day	    
	            $enroldatestartgrp = array();
	            $enroldatestartgrp[] = &MoodleQuickForm::createElement('date_selector', 'enrolstartdate');
	            $enroldatestartgrp[] = &MoodleQuickForm::createElement('checkbox', 'enrolstartdisabled', null, get_string('disable'));
	            $this->addGroup($enroldatestartgrp, 'enrolstartdategrp', get_string('enrolstartdate'), ' ', false);
	            $this->setDefault('enrolstartdate', 0);
	            $this->setDefault('enrolstartdisabled', 1);
	            $this->disabledIf('enrolstartdategrp', 'enrolstartdisabled', 'checked');
	    
	            // add a stop enrolment day
	            $enroldateendgrp = array();
	            $enroldateendgrp[] = &MoodleQuickForm::createElement('date_selector', 'enrolenddate');
	            $enroldateendgrp[] = &MoodleQuickForm::createElement('checkbox', 'enrolenddisabled', null, get_string('disable'));
	            $this->addGroup($enroldateendgrp, 'enroldateendgrp', get_string('enrolenddate'), ' ', false);
	            $this->setDefault('enrolenddate', 0);
	            $this->setDefault('enrolenddisabled', 1);
	            $this->disabledIf('enroldateendgrp', 'enrolenddisabled', 'checked');	    
    		}
    		
    		//--------------------------------------------------------------------------------
            // add hidden fields
	        $this->addElement('hidden', 'id', null);
	        $this->setType('id', PARAM_INT);
	
	        // fill in default teacher and student names to keep backwards compatibility for a while
	        $this->addElement('hidden', 'teacher', get_string('defaultcourseteacher'));
	        $this->addElement('hidden', 'teachers', get_string('defaultcourseteachers'));
	        $this->addElement('hidden', 'student', get_string('defaultcoursestudent'));
	        $this->addElement('hidden', 'students', get_string('defaultcoursestudents'));
    		
    		// add control buttons
    		$buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp', false);
            
            $this->setDefaultAction('next');
    	}
    	
        /**
         * Add extra validation rules to the first page
         */
        function validation() {
            $errors = array();
            $data = $this->exportValues();
            if (empty($data['enrolenddisabled']) and !empty($data['enrolenddate']) and !empty($data['enrolstartdate'])){
                if ($data['enrolenddate'] <= $data['enrolstartdate']) {
                    $errors['enroldateendgrp'] = get_string('enrolenddaterror');
                }
            }
            
            if (0 == count($errors)){
                return true;
            } else {
                return $errors;
            }
        }
    	
    }
	
	
	/*
	class course_edit_form extends moodleform {
	
		function definition() {
	    /// form definition with new course defaults
	    //--------------------------------------------------------------------------------
	
	        $mform->addElement('text','shortname', get_string('shortname'),'maxlength="15" size="10"');
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('shortname', array('courseshortname', get_string('shortname')), true);
	        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
	        $mform->setType('shortname', PARAM_MULTILANG);
	
	        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('idnumber', array('courseidnumber', get_string('idnumbercourse')), true);
	        $mform->setType('idnumber', PARAM_RAW);
	
	        $courseformats = get_list_of_plugins('course/format');
	        $formcourseformats = array();
	        foreach ($courseformats as $courseformat) {
	            $formcourseformats["$courseformat"] = get_string("format$courseformat","format_$courseformat");
	            if($formcourseformats["$courseformat"]=="[[format$courseformat]]") {
	                $formcourseformats["$courseformat"] = get_string("format$courseformat");
	            }
	        }
	        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('format', array('courseformats', get_string('courseformats')), true);
	        $mform->setDefault('format', 'weeks');
	
	        for ($i=1; $i<=52; $i++) {
	          $sectionmenu[$i] = "$i";
	        }
	        $mform->addElement('select', 'numsections', get_string('numberweeks'), $sectionmenu);
	        $mform->setDefault('numsections', 10);
	
	        $choices = array();
	        $choices['0'] = get_string('hiddensectionscollapsed');
	        $choices['1'] = get_string('hiddensectionsinvisible');
	        $mform->addElement('select', 'hiddensections', get_string('hiddensections'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('hiddensections', array('coursehiddensections', get_string('hiddensections')), true);
	        $mform->setDefault('hiddensections', 0);
	
	        $options = range(0, 10);
	        $mform->addElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('newsitems', array('coursenewsitems', get_string('newsitemsnumber')), true);
	        $mform->setDefault('newsitems', 5);
	
	        $mform->addElement('selectyesno', 'showgrades', get_string('showgrades'));
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('showgrades', array('coursegrades', get_string('grades')), true);
	        $mform->setDefault('showgrades', 1);
	
	        $mform->addElement('selectyesno', 'showreports', get_string('showreports'));
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('showreports', array('coursereports', get_string('activityreport')), true);
	        $mform->setDefault('showreports', 0);
	
	        $choices = get_max_upload_sizes($CFG->maxbytes);
	        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('maxbytes', array('courseuploadsize', get_string('maximumupload')), true);
	
	        if (!empty($CFG->allowcoursethemes)) {
	            $themes=array();
	            $themes[''] = get_string('forceno');
	            $themes += get_list_of_themes();
	            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
	        }
	
	        $meta=array();
	        $meta[0] = get_string('no');
	        $meta[1] = get_string('yes');
	        if ($disable_meta === false) {
	            $mform->addElement('select', 'metacourse', get_string('managemeta'), $meta);
	            // TODO dodelej HELP tlacitka
	            //$mform->setHelpButton('metacourse', array('metacourse', get_string('metacourse')), true);
	            $mform->setDefault('metacourse', 0);
	        } else {
	            // no metacourse element - we do not want to change it anyway!
	            $mform->addElement('static', 'nometacourse', get_string('managemeta'),
	                ((empty($course->metacourse)) ? $meta[0] : $meta[1]) . " - $disable_meta ");
	            // TODO dodelej HELP tlacitka
	            //$mform->setHelpButton('nometacourse', array('metacourse', get_string('metacourse')), true);
	        }
	
	        $roles = get_assignable_roles($context);
	        if (!empty($course)) {
	            // add current default role, so that it is selectable even when user can not assign it
	            if ($current_role = get_record('role', 'id', $course->defaultrole)) {
	                $roles[$current_role->id] = strip_tags(format_string($current_role->name, true));
	            }
	        }
	        $choices = array();
	        if ($sitedefaultrole = get_record('role', 'id', $CFG->defaultcourseroleid)) {
	            $choices[0] = get_string('sitedefault').' ('.$sitedefaultrole->name.')';
	        } else {
	            $choices[0] = get_string('sitedefault');
	        }
	        $choices = $choices + $roles;
	
	        // fix for MDL-9197
	        foreach ($choices as $choiceid => $choice) {
	            $choices[$choiceid] = format_string($choice);
	        }
	
	        $mform->addElement('select', 'defaultrole', get_string('defaultrole', 'role'), $choices);
	        $mform->setDefault('defaultrole', 0);
	
	        //--------------------------------------------------------------------------------
	        $mform->addElement('header','enrolhdr', get_string('enrolments'));
	
	        $choices = array();
	        $modules = explode(',', $CFG->enrol_plugins_enabled);
	        foreach ($modules as $module) {
	            $name = get_string('enrolname', "enrol_$module");
	            $plugin = enrolment_factory::factory($module);
	            if (method_exists($plugin, 'print_entry')) {
	                $choices[$name] = $module;
	            }
	        }
	        asort($choices);
	        $choices = array_flip($choices);
	        $choices = array_merge(array('' => get_string('sitedefault').' ('.get_string('enrolname', "enrol_$CFG->enrol").')'), $choices);
	        $mform->addElement('select', 'enrol', get_string('enrolmentplugins'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('enrol', array('courseenrolmentplugins', get_string('enrolmentplugins')), true);
	
	//--------------------------------------------------------------------------------
	        $mform->addElement('header','expirynotifyhdr', get_string('expirynotify'));
	
	        $choices = array();
	        $choices['0'] = get_string('no');
	        $choices['1'] = get_string('yes');
	        $mform->addElement('select', 'expirynotify', get_string('notify'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('expirynotify', array('expirynotify', get_string('expirynotify')), true);
	        $mform->setDefault('expirynotify', 0);
	
	        $mform->addElement('select', 'notifystudents', get_string('expirynotifystudents'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('notifystudents', array('expirynotifystudents', get_string('expirynotifystudents')), true);
	        $mform->setDefault('notifystudents', 0);
	
	        $thresholdmenu=array();
	        for ($i=1; $i<=30; $i++) {
	            $seconds = $i * 86400;
	            $thresholdmenu[$seconds] = get_string('numdays', '', $i);
	        }
	        $mform->addElement('select', 'expirythreshold', get_string('expirythreshold'), $thresholdmenu);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('expirythreshold', array('expirythreshold', get_string('expirythreshold')), true);
	        $mform->setDefault('expirythreshold', 10 * 86400);
	
	//--------------------------------------------------------------------------------
	        $mform->addElement('header','', get_string('groups', 'group'));
	
	        $choices = array();
	        $choices[NOGROUPS] = get_string('no');
	        $choices[SEPARATEGROUPS] = get_string('separate');
	        $choices[VISIBLEGROUPS] = get_string('visible');
	        $mform->addElement('select', 'groupmode', get_string('groupmode'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('groupmode', array('groupmode', get_string('groupmode')), true);
	        $mform->setDefault('groupmode', 0);
	
	        $choices = array();
	        $choices['0'] = get_string('no');
	        $choices['1'] = get_string('yes');
	        $mform->addElement('select', 'groupmodeforce', get_string('force'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('groupmodeforce', array('groupmodeforce', get_string('groupmodeforce')), true);
	        $mform->setDefault('groupmodeforce', 0);
	
	//--------------------------------------------------------------------------------
	        $mform->addElement('header','', get_string('availability'));
	
	        $choices = array();
	        $choices['0'] = get_string('courseavailablenot');
	        $choices['1'] = get_string('courseavailable');
	        $mform->addElement('select', 'visible', get_string('availability'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('visible', array('courseavailability', get_string('availability')), true);
	        $mform->setDefault('visible', 1);
	
	        $mform->addElement('passwordunmask', 'enrolpassword', get_string('enrolmentkey'), 'size="25"');
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('enrolpassword', array('enrolmentkey', get_string('enrolmentkey')), true);
	        $mform->setDefault('enrolpassword', '');
	        $mform->setType('enrolpassword', PARAM_RAW);
	
	        $choices = array();
	        $choices['0'] = get_string('guestsno');
	        $choices['1'] = get_string('guestsyes');
	        $choices['2'] = get_string('guestskey');
	        $mform->addElement('select', 'guest', get_string('opentoguests'), $choices);
	        // TODO dodelej HELP tlacitka
	        //$mform->setHelpButton('guest', array('guestaccess', get_string('opentoguests')), true);
	        $mform->setDefault('guest', 0);
	        
	        
	
	//--------------------------------------------------------------------------------
	        $mform->addElement('header','', get_string('language'));
	
	        $languages=array();
	        $languages[''] = get_string('forceno');
	        $languages += get_list_of_languages();
	        $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);
	
	//--------------------------------------------------------------------------------
	        if (has_capability('moodle/site:config', $systemcontext) && ((!empty($course->requested) && $CFG->restrictmodulesfor == 'requested') || $CFG->restrictmodulesfor == 'all')) {
	            $mform->addElement('header', '', get_string('restrictmodules'));
	
	            $options = array();
	            $options['0'] = get_string('no');
	            $options['1'] = get_string('yes');
	            $mform->addElement('select', 'restrictmodules', get_string('restrictmodules'), $options);
	            $mods = array(0=>get_string('allownone'));
	            $mods += get_records_menu('modules', '','','','id, name');
	
	
	            $mform->addElement('select', 'allowedmods', get_string('to'), $mods,
	                            array('multiple'=>'multiple', 'size'=>'10'));
	            $mform->disabledIf('allowedmods', 'restrictmodules', 'eq', 0);
	        } else {
	            $mform->addElement('hidden', 'restrictmodules', null);
	        }
	        if ($CFG->restrictmodulesfor == 'all') {
	            $mform->setDefault('allowedmods', explode(',',$CFG->defaultallowedmodules));
	            if (!empty($CFG->restrictbydefault)) {
	                $mform->setDefault('restrictmodules', 1);
	            }
	        }
	        $mform->setType('restrictmodules', PARAM_INT);
	
	/// customizable role names in this course
	//--------------------------------------------------------------------------------
	        $mform->addElement('header','', get_string('roles'));
	
	        if ($roles = get_records('role')) {
	            foreach ($roles as $role) {
	                $mform->addElement('text', 'role_'.$role->id, $role->name);
	                if ($coursecontext) {
	                    if ($rolename = get_record('role_names', 'roleid', $role->id, 'contextid', $coursecontext->id)) {
	                        $mform->setDefault('role_'.$role->id, $rolename->text); 
	                    }  
	                }
	            }
	        }
	
	//--------------------------------------------------------------------------------
	        $this->add_action_buttons();
	//--------------------------------------------------------------------------------
	        $mform->addElement('hidden', 'id', null);
	        $mform->setType('id', PARAM_INT);
	
	        // fill in default teacher and student names to keep backwards compatibility for a while
	        $mform->addElement('hidden', 'teacher', get_string('defaultcourseteacher'));
	        $mform->addElement('hidden', 'teachers', get_string('defaultcourseteachers'));
	        $mform->addElement('hidden', 'student', get_string('defaultcoursestudent'));
	        $mform->addElement('hidden', 'students', get_string('defaultcoursestudents'));
	    }
	
	
	/// perform some extra moodle validation
	    function validation($data){
	        $errors= array();
	        if ($foundcourses = get_records('course', 'shortname', $data['shortname'])) {
	            if (!empty($data['id'])) {
	                unset($foundcourses[$data['id']]);
	            }
	            if (!empty($foundcourses)) {
	                foreach ($foundcourses as $foundcourse) {
	                    $foundcoursenames[] = $foundcourse->fullname;
	                }
	                $foundcoursenamestring = implode(',', $foundcoursenames);
	                $errors['shortname']= get_string('shortnametaken', '', $foundcoursenamestring);
	            }
	        }
	
	        if (empty($data['enrolenddisabled'])){
	            if ($data['enrolenddate'] <= $data['enrolstartdate']){
	                $errors['enroldateendgrp'] = get_string('enrolenddaterror');
	            }
	        }
	
	        if (0 == count($errors)){
	            return true;
	        } else {
	            return $errors;
	        }
	    }
	}
	*/
	
	/**
	 * Class for form rendering
	 */ 
	class ActionDisplay extends HTML_QuickForm_Action_Display
	{
	    function _renderForm(&$page) 
	    {
	        $renderer =& $page->defaultRenderer();
	
	        //$page->setRequiredNote('<font color="#FF0000">*</font> shows the required fields.');
	        $page->setJsWarnings('Those fields have errors :', 'Thanks for correcting them.');
	        
	        //$renderer->setFormTemplate('<table width="450" border="0" cellpadding="3" cellspacing="2" bgcolor="#CCCC99"><form{attributes}>{content}</form></table>');
	        //$renderer->setHeaderTemplate('<tr><td style="white-space:nowrap;background:#996;color:#ffc;" align="left" colspan="2"><b>{header}</b></td></tr>');
	        //$renderer->setGroupTemplate('<table><tr>{content}</tr></table>', 'name');
	        //$renderer->setGroupElementTemplate('<td>{element}<br /><span style="font-size:10px;"><!-- BEGIN required --><span style="color: #f00">*</span><!-- END required --><span style="color:#996;">{label}</span></span></td>', 'name');
	
	        $page->accept($renderer);
	        echo $renderer->toHtml();
	    }
	}
	
	/**
	 * Class for form processing collected values (creating new course)
	 */ 
	class ActionProcess extends HTML_QuickForm_Action
	{
		function _get_data(&$page, $slashed=true) {
	        if ($page->controller->isValid()) {
	            $data = $page->controller->exportValues();
	            unset($data['sesskey']); // we do not need to return sesskey
	            if (empty($data)) {
	                return NULL;
	            } else {
	                return (object)$data;
	            }
	        } else {
	            return NULL;
	        }
	    }
	    
		/**
		 * perform action
		 * @uses CFG
		 * @uses USER
		 */ 
	    function perform(&$page, $actionName)
	    {   
	    	// try to read a filled data from the form and set them to the course
	        if ($data = $this->_get_data($page)) {
                // data was correctly read
	        	
                $data->password = $data->enrolpassword = '';  // we need some other name for password field MDL-9929
                // create a shortname from a fullname
                $data->shortname = substr($data->fullname, 0, 99);

                // if enrolstartdate is disabled, do not use it
	        	if (empty($data->enrolstartdisabled)) {
		            $data->enrolstartdate = 0;
		        }

		        // if enrolstopdate is disabled, do not use it
		        if (empty($data->enrolenddisabled)) {
		            $data->enrolenddate = 0;
		        }

		        // remember creation datetime
                $data->timemodified = time();
                // first, the course is invisible
                $data->visible = 0;
	            
	            global $CFG, $USER;

                if (empty($course)) {
		            if (!$course = create_course($data)) {
		                print_error('coursenotcreated');
		            }

                    $context = get_context_instance(CONTEXT_COURSE, $course->id);

                    // assign default role to creator if not already having permission to manage course assignments
		            if (!has_capability('moodle/course:view', $context) or !has_capability('moodle/role:assign', $context)) {
		                role_assign($CFG->creatornewroleid, $USER->id, 0, $context->id);
		            }        

//		            if ($data->metacourse and has_capability('moodle/course:managemetacourse', $context)) {
//		                // Redirect users with metacourse capability to student import
//		                redirect($CFG->wwwroot."/course/importstudents.php?id=$course->id");
//		            } else {
//		                // Redirect to roles assignment
//		                redirect($CFG->wwwroot."/$CFG->admin/roles/assign.php?contextid=$context->id");
//		            }
                } // if (empty($course))
            
            } else {
            	// no data has been taken from session (wizard) 
                print_error('coursenotcreated');
            }
            
            redirect($CFG->wwwroot."/course/view.php?id=$course->id");
	    } // function perform
	} //class ActionProcess
	
	$wizard = new HTML_QuickForm_Controller('courseWizard', true);
	$wizard->addPage(new NewCourseBasic('page1'));
	$wizard->addPage(new NewCourseTechnology('page2'));
	$wizard->addPage(new NewCourseSyncAsync('page3'));
	
	// read values from the previous step
    $wizard->setDefaults($wizard->exportValues());
	
	$wizard->addAction('display', new ActionDisplay());
	$wizard->addAction('process', new ActionProcess());
	
	// generate heading
	$site = get_site();
    $streditcoursesettings = get_string("editcoursesettings");
    $straddnewcourse = get_string("addnewcourse");
    $stradministration = get_string("administration");
    $strcategories = get_string("categories");
    $navlinks = array();
    $navlinks[] = array('name' => $stradministration, 'link' => "$CFG->wwwroot/$CFG->admin/index.php", 'type' => 'misc');
    $navlinks[] = array('name' => $strcategories, 'link' => 'index.php', 'type' => 'misc');
    $navlinks[] = array('name' => $straddnewcourse, 'link' => null, 'type' => 'misc');
    print_header("$site->shortname: $straddnewcourse", $site->fullname, build_navigation($navlinks), "", "", 
                 true, print_course_search("", true, "navbar"));
    print_heading(get_string("heading.newcourse", "samouk"));
	
	$wizard->run();
	
	print_footer($course);
?>


