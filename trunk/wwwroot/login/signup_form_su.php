<?php
	/**
	 * Wizard creating new user
	 * This file define forms for all steps in wizard
	 * Using PEAR framework
	 * 
	 * @name signup_form_su.php
	 * @author Kowy
	 * @version 1.9.0
	 */

	require_once('../config.php');
	require_once($CFG->dirroot.'/user/profile/lib.php');
	
	require_once($CFG->libdir.'/pear/HTML/QuickForm/Controller.php');
	require_once($CFG->libdir.'/pear/HTML/QuickForm/Action/Display.php');
	
	/**
	 * Because first part of summary is generated on two pages (fourth and seventh) so prevent from 
	 * duplication of a code this method generate shared text elements 
	 *
	 * @param HTML_QuickForm_Page $aform Form on which elements should be added
	 */
	function CreateFirstPartOfSummary($aform) {
        // rectangle around elements
        $aform->addElement('header', 'top_header', get_string('summary'));
        $aform->addElement('static', 'strUsername', get_string('username'), $aform->controller->exportValue('WizardStepOne', 'username'));
        $aform->addElement('static', 'strEmail', get_string('email'), $aform->controller->exportValue('WizardStepTwo', 'email'));

        $nameordercheck->firstname = 'a';
        $nameordercheck->lastname  = 'b';
        if (fullname($nameordercheck) == 'b a' ) {  // See MDL-4325
           $aform->addElement('static', 'strLastname',  get_string('lastname'),  
                             $aform->controller->exportValue('WizardStepTwo', 'lastname').'&nbsp;'.$aform->controller->exportValue('WizardStepTwo', 'firstname'));
        } else {
            $aform->addElement('static', 'strFirstname', get_string('firstname'), 
                              $aform->controller->exportValue('WizardStepTwo', 'firstname').'&nbsp;'.$aform->controller->exportValue('WizardStepTwo', 'lastname'));
        }
            
        $aform->addElement('static', 'strAddress', get_string('address'), $aform->controller->exportValue('WizardStepTwo', 'address'));
        $aform->addElement('static', 'strCity', get_string('city'), $aform->controller->exportValue('WizardStepTwo', 'city'));
        // full country name must be taken from L10n string repository 
        $aform->addElement('static', 'strCountry', get_string('country'), get_string($aform->controller->exportValue('WizardStepTwo', 'country'), 'countries'));
        $aform->addElement('static', 'strPhone', get_string('phone'), $aform->controller->exportValue('WizardStepTwo', 'phone1'));
	}
	
	/**
	 * Insert static element containing prediction how many step (pages) user must   
	 *
	 * @param HTML_QuickForm_Page $aform Form on which element should be added
	 * @param int $current
	 * @param int $total
	 * @return HTML_QuickForm_static Reference to created element
	 */
	function ShowStepPrediction($aform, $current, $total) {
		$data->current = $current;
		$data->total = $total;
		
		return $aform->addElement('static', 'stepPrediction', '', get_string('newuser.step', 'samouk', $data));
	}
	
	/**
	 * Class creating first page of creating new user wizard
	 * @author kowy
	 *
	 */
	class WizardStepOne extends HTML_QuickForm_Page 
	{
		/**
		 * Create new instance of first wizard's step 
		 * In this constructor name(id) of this page is set to WizardStepOne
		 */
		function __construct() {
			parent::__construct('WizardStepOne');
		}
		
        /**
         * Build form content
         *
         */
        function buildForm() {
            $this->_formBuilt = true;
            
            // add reactangle around next elements
	        $this->addElement('header', 'top_header', get_string('createuserandpass'));
	
	        // add username textbox
	        $userNameElement = $this->addElement('text', 'username', get_string('username'), 'maxlength="100" size="20"');
	        $this->setType('username', PARAM_NOTAGS);
	        $this->addRule('username', get_string('missingusername'), 'required', null, 'server');
	        $this->applyFilter('username','trim');
	        $this->applyFilter('username', 'moodle_strtolower');
	        
	        // add password textbox
	        $this->addElement('password', 'password', get_string('password'), 'maxlength="32" size="20"');
	        $this->setType('password', PARAM_RAW);
	        $this->addRule('password', get_string('missingpassword'), 'required', null, 'server');
	        
	        $this->addElement('password', 'retypepassword', get_string('password').' ('.get_String('again').')', 'maxlength="32" size="20"');
	        $this->setType('retypepassword', PARAM_RAW);
	        $this->addRule('retypepassword', get_string('missingpassword'), 'required', null, 'server');
	        //$this->addRule(array('password','retypepassword'), get_string('passwordsdiffer'), 'compare', null, 'client');
	        $this->addRule(array('retypepassword','password'), get_string('passwordsdiffer'), 'compare', null, 'server');
	        
	        ShowStepPrediction($this, 1, 4);
	        
	        // add command buttons
	        $buttons[0] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[1] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
        }
        
	    /**
         * Add extra validation rules to the first page
         * Check if both passwords are same
         */
        function validate() {
        	global $CFG;
            $this->_errors = array();
            $authplugin = get_auth_plugin($CFG->registerauth);
            $data = $this->exportValues();
            
            // check if username is unique
	        if (record_exists('user', 'username', $data['username'], 'mnethostid', $CFG->mnet_localhost_id)) {
	            $this->_errors['username'] = get_string('usernameexists');
	        } else {
	        	// check if username contain only alphanumeric chars
	            if (empty($CFG->extendedusernamechars)) {
	                $string = eregi_replace("[^(-\.[:alnum:])]", '', $data['username']);
	                if (strcmp($data['username'], $string)) {
	                    $this->_errors['username'] = get_string('alphanumerical');
	                }
	            }
	        }
            
            //check if user exists in external db
	        //TODO: maybe we should check all enabled plugins instead
	        if ($authplugin->user_exists($data['username'])) {
	            $this->_errors['username'] = get_string('usernameexists');
	        }
            
	        if (!check_password_policy($data['password'], $errmsg)) {
	            $this->_errors['password'] = $errmsg;
	        }
	        
	        // validate standard rules
	        parent::validate();
            
            return (0 == count($this->_errors));
        }
 }

    /**
     * Class creating second page of creating new user wizard
     * @author kowy
     *
     */
    class WizardStepTwo extends HTML_QuickForm_Page 
    {
        /**
         * Create new instance of first wizard's step 
         * In this constructor name(id) of this page is set to WizardStepTwo
         */
        function __construct() {
            parent::__construct('WizardStepTwo');
        }
        
    	/**
    	 * Build form content
    	 */
    	function buildForm()
    	{
    		$this->_formBuilt = true;
    		
    		// rectangle around elements
    		$this->addElement('header', 'top_header', get_string('newuser.contactform', 'samouk'));

	        $this->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"');
	        $this->setType('email', PARAM_NOTAGS);
	        $this->addRule('email', get_string('missingemail'), 'required', null, 'server');
	
	        $nameordercheck = new object();
	        $nameordercheck->firstname = 'a';
	        $nameordercheck->lastname  = 'b';
	        if (fullname($nameordercheck) == 'b a' ) {  // See MDL-4325
	            $this->addElement('text', 'lastname',  get_string('lastname'),  'maxlength="100" size="30"');
	            $this->addElement('text', 'firstname', get_string('firstname'), 'maxlength="100" size="30"');
	        } else {
	            $this->addElement('text', 'firstname', get_string('firstname'), 'maxlength="100" size="30"');
	            $this->addElement('text', 'lastname',  get_string('lastname'),  'maxlength="100" size="30"');
	        }
	
	        $this->setType('firstname', PARAM_TEXT);
	        $this->addRule('firstname', get_string('missingfirstname'), 'required', null, 'server');
	
	        $this->setType('lastname', PARAM_TEXT);
	        $this->addRule('lastname', get_string('missinglastname'), 'required', null, 'server');
	        
	        // add an address textbox
	        $this->addElement('text', 'address', get_string('address'), 'maxlength="70" size="30"');
	        $this->setType('address', PARAM_TEXT);
	
	        // add a city textbox
	        $this->addElement('text', 'city', get_string('city'), 'maxlength="20" size="30"');
	        $this->setType('city', PARAM_TEXT);
	        $this->addRule('city', get_string('missingcity'), 'required', null, 'server');
	
	        $country = get_list_of_countries();
	        $default_country[''] = get_string('selectacountry');
	        $country = array_merge($default_country, $country);
	        $this->addElement('select', 'country', get_string('country'), $country, 'width="130"');
	        $this->addRule('country', get_string('missingcountry'), 'required', null, 'server');
	        $this->setDefault('country', 'CZ');
	        
	        // show phone number input box
            $this->addElement('text', 'phone1', get_string('phone'), 'maxlength="20" size="30"');
            $this->setType('phone1', PARAM_TEXT);
	
	        // add custom profile elements
	        profile_signup_fields($this);
	
	        if (!empty($CFG->sitepolicy)) {
	            $this->addElement('header', 'top_header', get_string('policyagreement'));
	            $this->addElement('static', 'policylink', '', '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
	            $this->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
	            $this->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
	        }
	        
	        ShowStepPrediction($this, 2, 4);
	        
	        // add command buttons
	        $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
    	}
    	
    	/**
         * Add extra validation rules to the first page
         * Check if both passwords are same
         */
        function validate() {
        	$this->_errors = array();
            $data = $this->exportValues();
            
            if (! validate_email($data['email'])) {
	            $this->_errors['email'] = get_string('invalidemail');
	
	        } else if (record_exists('user', 'email', $data['email'])) {
	            $this->_errors['email'] = get_string('emailexists').' <a href="forgot_password.php">'.get_string('newpassword').'?</a>';
	        }
	        
	        // validate standard rules
	        parent::validate();
	        
        	return (0 == count($this->_errors));
        }
    }
    
    /**
     * Class creating third page of creating new user wizard
     * @author kowy
     *
     */
    class WizardStepThree extends HTML_QuickForm_Page 
    {
    	
        /**
         * Create new instance of third wizard's step 
         * In this constructor name(id) of this page is set to WizardStepThree
         */
        function __construct() {
            parent::__construct('WizardStepThree');
        }
        
    	/**
         * Build form content
         */
    	function buildForm() {
    		$this->_formBuilt = true;
    		
    		// rectangle around elements
            $this->addElement('header', 'top_header', get_string('newuser.accountingform', 'samouk'));
    		
    		// show all elements in category 'accounting' (this page)
    	    if ($fields = get_record('user_info_field', "shortname='accounting'", 'sortorder ASC')) {
                //$this->addElement('header', 'category_'.$category->id, format_string($category->name));
	            foreach ($fields as $field) {
	               require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
		           $newfield = 'profile_field_'.$field->datatype;
		           $formfield = new $newfield($field->id);
		           $formfield->edit_field($this);
		        }
		    }
		    
		    ShowStepPrediction($this, 3, 4);
		    
            // add command buttons
            $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
    	}
    }
    
    /**
     * Class creating fourth page of creating new user wizard
     * Show summary of all previous steps
     * @author kowy
     *
     */
    class WizardStepFour extends HTML_QuickForm_Page 
    {
    	
        /**
         * Create new instance of fourth wizard's step 
         * In this constructor name(id) of this page is set to WizardStepFour
         */
        function __construct() {
            parent::__construct('WizardStepFour');
        }
        
    	/*
    	 * Build form content
         */
        function buildForm() {
            $this->_formBuilt = true;
            
            CreateFirstPartOfSummary($this);
            
            ShowStepPrediction($this, 4, 4);
    
            // add command buttons
            $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('submit', $this->getButtonName('submit'), get_string('newuser.button.finish','samouk'));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('newuser.button.teacher','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
        }
    }
    
    /**
     * Class creating fifth page of creating new user wizard
     * Extended contact information for a teacher
     * @author kowy
     *
     */
    class WizardStepFive extends HTML_QuickForm_Page 
    {
        /**
         * Create new instance of fifth wizard's step 
         * In this constructor name(id) of this page is set to WizardStepFive
         */
        function __construct() {
            parent::__construct('WizardStepFive');
        }
        
    	/*
         * Build form content
         */
        function buildForm() {
        	$this->_formBuilt = true;
        	
        	// rectangle around elements
            $this->addElement('header', 'top_header', get_string('newuser.extendedcontactform', 'samouk'));
            
            $this->addElement('text', 'url', get_string('webpage'), 'maxlength="255" size="50"');
		    $this->setType('url', PARAM_URL);
		
		    $this->addElement('text', 'icq', get_string('icqnumber'), 'maxlength="15" size="25"');
		    $this->setType('icq', PARAM_CLEAN);
		
		    $this->addElement('text', 'skype', get_string('skypeid'), 'maxlength="50" size="25"');
		    $this->setType('skype', PARAM_CLEAN);
		
		    $this->addElement('text', 'aim', 'Jabber', 'maxlength="50" size="25"');
		    $this->setType('aim', PARAM_CLEAN);

		    ShowStepPrediction($this, 5, 7);
		    
		    // add command buttons
            $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
        }
    }
    
    /**
     * Class creating sixth page of creating new user wizard
     * Short and long description of teachers abilities and his photo
     * @author kowy
     *
     */
    class WizardStepSix extends HTML_QuickForm_Page 
    {
        /**
         * Create new instance of sixth wizard's step 
         * In this constructor name(id) of this page is set to WizardStepSix
         */
        function __construct() {
            parent::__construct('WizardStepSix');
        }
        
        /*
         * Build form content
         */
        function buildForm() {
        	$this->_formBuilt = true;
        	
            // rectangle around elements
            $this->addElement('header', 'top_header', get_string('newuser.teacherdescform', 'samouk'));
            
            $this->addElement('htmleditor', 'description', get_string('userdescription'));
		    $this->setType('description', PARAM_CLEAN);
		    $this->setHelpButton('description', array('text', get_string('helptext')));     
		    
            if (!empty($CFG->gdversion)) {
                $mform->addElement('header', 'moodle_picture', get_string('pictureof'));//TODO: Accessibility fix fieldset legend
        
                $mform->addElement('static', 'currentpicture', get_string('currentpicture'));
        
                $mform->addElement('checkbox', 'deletepicture', get_string('delete'));
                $mform->setDefault('deletepicture',false);
        
                $mform->addElement('file', 'imagefile', get_string('newpicture'));
                $mform->setHelpButton('imagefile', array('picture', get_string('helppicture')));
        
                $mform->addElement('text', 'imagealt', get_string('imagealt'), 'maxlength="100" size="30"');
                $mform->setType('imagealt', PARAM_MULTILANG);
            }
            
            ShowStepPrediction($this, 6, 7);
		    
		    // add command buttons
            $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('button', 'cancel', get_string('cancel'), array('onclick'=>"javascript:location.href='index.php';"));
            $buttons[2] =& HTML_QuickForm::createElement('submit', $this->getButtonName('next'), get_string('button.next','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
        }
    }
    
    /**
     * Class creating seventh page of creating new user wizard
     * Show summary of all previous steps
     * @author kowy
     *
     */
    class WizardStepSeven extends HTML_QuickForm_Page 
    {
        /**
         * Create new instance of seventh wizard's step 
         * In this constructor name(id) of this page is set to WizardStepSeven
         */
        function __construct() {
            parent::__construct('WizardStepSeven');
        }
        
        /*
         * Build form content
         */
        function buildForm() {
            $this->_formBuilt = true;
            
            CreateFirstPartOfSummary($this);
            
            // rectangle around teacher's elements
            $this->addElement('header', 'top_header', get_string('summary').'&nbsp;('.get_string('newuser.teachers','samouk').')');
            
            $this->addElement('static', 'strUrl', get_string('webpage'), $this->controller->exportValue('WizardStepFive', 'url'));
            $this->addElement('static', 'strIcq', get_string('icqnumber'), $this->controller->exportValue('WizardStepFive', 'icq'));
            $this->addElement('static', 'strSkype', get_string('skypeid'), $this->controller->exportValue('WizardStepFive', 'skype'));
            $this->addElement('static', 'strAim', 'Jabber', $this->controller->exportValue('WizardStepFive', 'aim'));
            
            // tell that a new teacher is created
            $this->addElement('hidden', 'isTeacher');
            $this->setDefault('isTeacher', 'true');
            
            ShowStepPrediction($this, 7, 7);
    
            // add command buttons
            $buttons[0] =& HTML_QuickForm::createElement('submit', $this->getButtonName('back'), get_string('button.prev','samouk'));
            $buttons[1] =& HTML_QuickForm::createElement('submit', $this->getButtonName('submit'), get_string('button.done','samouk'));
            $this->addGroup($buttons, 'buttons', '', '&nbsp;', false);
            
            $this->setDefaultAction('next');
        }
    }
    
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
        	global $CFG;
        	$authplugin = get_auth_plugin($CFG->registerauth);

			if (!$authplugin->can_signup()) {
			    error("Sorry, you may not use this page.");
			}
			
			if ($user = $this->_get_data($page)) {

		        $user->confirmed   = 0;
		        $user->lang        = current_language();
		        $user->firstaccess = time();
		        $user->mnethostid  = $CFG->mnet_localhost_id;
		        $user->secret      = random_string(15);
		        $user->auth        = $CFG->registerauth;

		        $authplugin->user_signup($user, false); // prints notice and link to login/index.php
		        
		        // set proper global role for teacher
		        if ($user->isTeacher != '') {
		        	$userid = get_record_select('user', "username='$user->username'", 'id' );
		        	$context = get_context_instance(CONTEXT_USER, $userid->id);
		        	$roleid = get_record_select('role', "shortname='coursecreator'", 'id');
		        	role_assign($roleid->id, $userid->id, 0, $context->id);
		        }
		        
//		        // show notification supressed in user_signup
//            	$emailconfirm = get_string('emailconfirm');
//            	$navlinks = array();
//            	$navlinks[] = array('name' => $emailconfirm, 'link' => null, 'type' => 'misc');
//            	$navigation = build_navigation($navlinks);
//
//            	print_header($emailconfirm, $emailconfirm, $navigation);
            	notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
			}

			// probably never reached
            redirect($CFG->wwwroot."/course/view.php?id=$course->id");
        } // function perform
    } //class ActionProcess
    
 ?>