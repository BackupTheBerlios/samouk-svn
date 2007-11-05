<?PHP  //$Id: upgrade.php,v 1.146 2007/10/07 13:04:49 skodak Exp $

// This file keeps track of upgrades to Moodle.
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php


function xmldb_main_upgrade($oldversion=0) {

    global $CFG, $THEME, $USER, $db;

    $result = true;

    if ($oldversion < 2006100401) {
        /// Only for those tracking Moodle 1.7 dev, others will have these dropped in moodle_install_roles()
        if (!empty($CFG->rolesactive)) {
            drop_table(new XMLDBTable('user_students'));
            drop_table(new XMLDBTable('user_teachers'));
            drop_table(new XMLDBTable('user_coursecreators'));
            drop_table(new XMLDBTable('user_admins'));
        }
    }

    if ($oldversion < 2006100601) {         /// Disable the exercise module because it's unmaintained
        if ($module = get_record('modules', 'name', 'exercise')) {
            if ($module->visible) {
                // Hide/disable the module entry
                set_field('modules', 'visible', '0', 'id', $module->id);
                // Save existing visible state for all activities
                set_field('course_modules', 'visibleold', '1', 'visible' ,'1', 'module', $module->id);
                set_field('course_modules', 'visibleold', '0', 'visible' ,'0', 'module', $module->id);
                // Hide all activities
                set_field('course_modules', 'visible', '0', 'module', $module->id);

                require_once($CFG->dirroot.'/course/lib.php');
                rebuild_course_cache();  // Rebuld cache for all modules because they might have changed
            }
        }
    }

    if ($oldversion < 2006101001) {         /// Disable the LAMS module by default (if it is installed)
        if (count_records('modules', 'name', 'lams') && !count_records('lams')) {
            set_field('modules', 'visible', 0, 'name', 'lams');  // Disable it by default
        }
    }

    if ($result && $oldversion < 2006102600) {

        /// Define fields to be added to user_info_field
        $table  = new XMLDBTable('user_info_field');
        $field = new XMLDBField('description');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'categoryid');
        $field1 = new XMLDBField('param1');
        $field1->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'defaultdata');
        $field2 = new XMLDBField('param2');
        $field2->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'param1');
        $field3 = new XMLDBField('param3');
        $field3->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'param2');
        $field4 = new XMLDBField('param4');
        $field4->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'param3');
        $field5 = new XMLDBField('param5');
        $field5->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'param4');

        /// Launch add fields
        $result = $result && add_field($table, $field);
        $result = $result && add_field($table, $field1);
        $result = $result && add_field($table, $field2);
        $result = $result && add_field($table, $field3);
        $result = $result && add_field($table, $field4);
        $result = $result && add_field($table, $field5);
    }

    if ($result && $oldversion < 2006112000) {

    /// Define field attachment to be added to post
        $table = new XMLDBTable('post');
        $field = new XMLDBField('attachment');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, null, null, null, null, null, 'format');

    /// Launch add field attachment
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2006112200) {

    /// Define field imagealt to be added to user
        $table = new XMLDBTable('user');
        $field = new XMLDBField('imagealt');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'trustbitmask');

    /// Launch add field imagealt
        $result = $result && add_field($table, $field);

        $table = new XMLDBTable('user');
        $field = new XMLDBField('screenreader');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, null, '0', 'imagealt');

    /// Launch add field screenreader
        $result = $result && add_field($table, $field);
    }

    if ($oldversion < 2006120300) {    /// Delete guest course section settings
        // following code can be executed repeatedly, such as when upgrading from 1.7.x - it is ok
        if ($guest = get_record('user', 'username', 'guest')) {
            execute_sql("DELETE FROM {$CFG->prefix}course_display where userid=$guest->id", true);
        }
    }

    if ($oldversion < 2006120400) {    /// Remove secureforms config setting
        execute_sql("DELETE FROM {$CFG->prefix}config where name='secureforms'", true);
    }

    if (!empty($CFG->rolesactive) && $oldversion < 2006120700) { // add moodle/user:viewdetails to all roles!
        // note: use of assign_capability() is discouraged in upgrade script!
        if ($roles = get_records('role')) {
            $context = get_context_instance(CONTEXT_SYSTEM);
            foreach ($roles as $roleid=>$role) {
                assign_capability('moodle/user:viewdetails', CAP_ALLOW, $roleid, $context->id);
            }
        }
    }

    // Move the auth plugin settings into the config_plugin table
    if ($oldversion < 2007010300) {
        if ($CFG->auth == 'email') {
            set_config('registerauth', 'email');
        } else {
            set_config('registerauth', '');
        }
        $authplugins = get_list_of_plugins('auth');
        foreach ($CFG as $k => $v) {
            if (strpos($k, 'ldap_') === 0) {
                //upgrade nonstandard ldap settings
                $setting = substr($k, 5);
                if (set_config($setting, $v, "auth/ldap")) {
                    delete_records('config', 'name', $k);
                    unset($CFG->{$k});
                }
                continue;
            }
            if (strpos($k, 'auth_') !== 0) {
                continue;
            }
            $authsetting = substr($k, 5);
            foreach ($authplugins as $auth) {
                if (strpos($authsetting, $auth) !== 0) {
                    continue;
                }
                $setting = substr($authsetting, strlen($auth));
                if (set_config($setting, $v, "auth/$auth")) {
                    delete_records('config', 'name', $k);
                    unset($CFG->{$k});
                }
                break; // don't check the rest of the auth plugin names
            }
        }
    }

    if ($oldversion < 2007010301) {
        //
        // Core MNET tables
        //
        $table = new XMLDBTable('mnet_host');
        $table->comment = 'Information about the local and remote hosts for RPC';
        // fields
        $f = $table->addFieldInfo('id',                 XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f->comment = 'Unique Host ID';
        $f = $table->addFieldInfo('deleted',            XMLDB_TYPE_INTEGER,  '1', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        $f = $table->addFieldInfo('wwwroot',            XMLDB_TYPE_CHAR,   '255', null,
                                  XMLDB_NOTNULL, null, null, null, null);
        $f = $table->addFieldInfo('ip_address',         XMLDB_TYPE_CHAR,    '39', null,
                                  XMLDB_NOTNULL, null, null, null, null);
        $f = $table->addFieldInfo('name',               XMLDB_TYPE_CHAR,    '80', null,
                                  XMLDB_NOTNULL, null, null, null, null);
        $f = $table->addFieldInfo('public_key',         XMLDB_TYPE_TEXT, 'medium', null,
                                  XMLDB_NOTNULL, null, null, null, null);
        $f = $table->addFieldInfo('public_key_expires', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        $f = $table->addFieldInfo('transport',          XMLDB_TYPE_INTEGER,  '2', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        $f = $table->addFieldInfo('portno',             XMLDB_TYPE_INTEGER,  '2', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        $f = $table->addFieldInfo('last_connect_time',  XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        $f = $table->addFieldInfo('last_log_id',  XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, null, null, null, 0);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Create the table
        $result = $result && create_table($table);

        $table = new XMLDBTable('mnet_host2service');
        $table->comment = 'Information about the services for a given host';
        // fields
        $f = $table->addFieldInfo('id',        XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('hostid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('serviceid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('publish', XMLDB_TYPE_INTEGER,  '1', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('subscribe', XMLDB_TYPE_INTEGER,  '1', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('hostid_serviceid', XMLDB_INDEX_UNIQUE, array('hostid', 'serviceid'));
        // Create the table
        $result = $result && create_table($table);

        $table = new XMLDBTable('mnet_log');
        $table->comment = 'Store session data from users migrating to other sites';
        // fields
        $f = $table->addFieldInfo('id',        XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('hostid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('remoteid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('time',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('userid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('ip',    XMLDB_TYPE_CHAR,  '15', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('course',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('coursename',    XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('module',    XMLDB_TYPE_CHAR,  '20', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('cmid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('action',    XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('url',    XMLDB_TYPE_CHAR,  '100', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('info',    XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('host_user_course', XMLDB_INDEX_NOTUNIQUE, array('hostid', 'userid', 'course'));
        // Create the table
        $result = $result && create_table($table);


        $table = new XMLDBTable('mnet_rpc');
        $table->comment = 'Functions or methods that we may publish or subscribe to';
        // fields
        $f = $table->addFieldInfo('id',        XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('function_name',    XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('xmlrpc_path',    XMLDB_TYPE_CHAR,  '80', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('parent_type',    XMLDB_TYPE_CHAR,  '6', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('parent',    XMLDB_TYPE_CHAR,  '20', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('enabled', XMLDB_TYPE_INTEGER,  '1', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('help',    XMLDB_TYPE_TEXT,  'medium', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('profile',    XMLDB_TYPE_TEXT,  'medium', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('enabled_xpath', XMLDB_INDEX_NOTUNIQUE, array('enabled', 'xmlrpc_path'));
        // Create the table
        $result = $result && create_table($table);

        $table = new XMLDBTable('mnet_service');
        $table->comment = 'A service is a group of functions';
        // fields
        $f = $table->addFieldInfo('id',        XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('name',    XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('description',    XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('apiversion',    XMLDB_TYPE_CHAR,  '10', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('offer',    XMLDB_TYPE_INTEGER,  '1', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Create the table
        $result = $result && create_table($table);

        $table = new XMLDBTable('mnet_service2rpc');
        $table->comment = 'Group functions or methods under a service';
        // fields
        $f = $table->addFieldInfo('id',        XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('serviceid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('rpcid',    XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('unique', XMLDB_INDEX_UNIQUE, array('rpcid', 'serviceid'));
        // Create the table
        $result = $result && create_table($table);

        //
        // Prime MNET configuration entries -- will be needed later by auth/mnet
        //
        include_once $CFG->dirroot . '/mnet/lib.php';
        $env = new mnet_environment();
        $env->init();
        unset($env);

        // add mnethostid to user-
        $table = new XMLDBTable('user');
        $field = new XMLDBField('mnethostid');
        $field->setType(XMLDB_TYPE_INTEGER);
        $field->setLength(10);
        $field->setNotNull(true);
        $field->setSequence(null);
        $field->setEnum(null);
        $field->setDefault('0');
        $field->setPrevious("deleted");
        $field->setNext("username");
        $result = $result && add_field($table, $field);

        // The default mnethostid is zero... we need to update this for all
        // users of the local IdP service.
        set_field('user',
                  'mnethostid', $CFG->mnet_localhost_id,
                  'mnethostid', '0');


        $index = new XMLDBIndex('username');
        $index->setUnique(true);
        $index->setFields(array('username'));
        drop_index($table, $index);
        $index->setFields(array('mnethostid', 'username'));
        if (!add_index($table, $index)) {
            notify(get_string('duplicate_usernames', 'mnet', 'http://docs.moodle.org/en/DuplicateUsernames'));
        }

        unset($table, $field, $index);

        /**
         ** auth/mnet tables
         **/
        $table = new XMLDBTable('mnet_session');
        $table->comment='Store session data from users migrating to other sites';
        // fields
        $f = $table->addFieldInfo('id',         XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL,XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('username',   XMLDB_TYPE_CHAR,  '100', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('token',      XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('mnethostid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('useragent',  XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('confirm_timeout', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('session_id',   XMLDB_TYPE_CHAR,  '40', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('expires', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('token', XMLDB_INDEX_UNIQUE, array('token'));
        // Create the table
        $result = $result && create_table($table);


        $table = new XMLDBTable('mnet_sso_access_control');
        $table->comment = 'Users by host permitted (or not) to login from a remote provider';
        $f = $table->addFieldInfo('id',         XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL,XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('username',   XMLDB_TYPE_CHAR,  '100', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('mnet_host_id', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('access',  XMLDB_TYPE_CHAR,  '20', null,
                                  XMLDB_NOTNULL, NULL, null, null, 'allow');
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('mnethostid_username', XMLDB_INDEX_UNIQUE, array('mnet_host_id', 'username'));
        // Create the table
        $result = $result && create_table($table);

        if (empty($USER->mnet_host_id)) {
            $USER->mnet_host_id = $CFG->mnet_localhost_id;    // Something for the current user to prevent warnings
        }

        /**
         ** enrol/mnet tables
         **/
        $table = new XMLDBTable('mnet_enrol_course');
        $table->comment = 'Information about courses on remote hosts';
        $f = $table->addFieldInfo('id',         XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL,XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('hostid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('remoteid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                          XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('cat_id', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('cat_name',  XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('cat_description',  XMLDB_TYPE_TEXT,  'medium', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('sortorder', XMLDB_TYPE_INTEGER,  '4', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('fullname',  XMLDB_TYPE_CHAR,  '254', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('shortname',  XMLDB_TYPE_CHAR,  '15', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('idnumber',  XMLDB_TYPE_CHAR,  '100', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('summary',  XMLDB_TYPE_TEXT,  'medium', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('startdate', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('cost',  XMLDB_TYPE_CHAR,  '10', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('currency',  XMLDB_TYPE_CHAR,  '3', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('defaultroleid', XMLDB_TYPE_INTEGER,  '4', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('defaultrolename',  XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('hostid_remoteid', XMLDB_INDEX_UNIQUE, array('hostid', 'remoteid'));
        // Create the table
        $result = $result && create_table($table);


        $table = new XMLDBTable('mnet_enrol_assignments');

        $table->comment = 'Information about enrolments on courses on remote hosts';
        $f = $table->addFieldInfo('id',         XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL,XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('hostid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('rolename',  XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('enroltime', XMLDB_TYPE_INTEGER,  '10', XMLDB_UNSIGNED,
                                  XMLDB_NOTNULL, NULL, null, null, 0);
        $f = $table->addFieldInfo('enroltype',  XMLDB_TYPE_CHAR,  '20', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);

        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('hostid_courseid', XMLDB_INDEX_NOTUNIQUE, array('hostid', 'courseid'));
        $table->addIndexInfo('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        // Create the table
        $result = $result && create_table($table);

    }

    if ($result && $oldversion < 2007010404) {

        /// Define field shortname to be added to user_info_field
        $table = new XMLDBTable('user_info_field');
        $field = new XMLDBField('shortname');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, 'shortname', 'id');

        /// Launch add field shortname
        $result = $result && add_field($table, $field);

        /// Changing type of field name on table user_info_field to text
        $table = new XMLDBTable('user_info_field');
        $field = new XMLDBField('name');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, null, null, 'shortname');

        /// Launch change of type for field name
        $result = $result && change_field_type($table, $field);

        /// For existing fields use 'name' as the 'shortname' entry
        if ($fields = get_records_select('user_info_field', '', '', 'id, name')) {
            foreach ($fields as $field) {
                $field->shortname = clean_param($field->name, PARAM_ALPHANUM);
                $result && update_record('user_info_field', $field);
            }
        }
    }

    if ($result && $oldversion < 2007011200) {

    /// Define table context_rel to be created
        $table = new XMLDBTable('context_rel');

    /// Adding fields to table context_rel
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('c1', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('c2', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table context_rel
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('c1', XMLDB_KEY_FOREIGN, array('c1'), 'context', array('id'));
        $table->addKeyInfo('c2', XMLDB_KEY_FOREIGN, array('c2'), 'context', array('id'));
        $table->addKeyInfo('c1c2', XMLDB_KEY_UNIQUE, array('c1', 'c2'));

    /// Launch create table for context_rel
        $result = $result && create_table($table);

        /// code here to fill the context_rel table
        /// use get record set to iterate slower
        /// /deprecated and gone / build_context_rel();
    }

    if ($result && $oldversion < 2007011501) {
        if (!empty($CFG->enablerecordcache) && empty($CFG->rcache) &&
            // Note: won't force-load these settings into CFG
            // we don't need or want cache during the upgrade itself
            empty($CFG->cachetype) && empty($CFG->intcachemax)) {
            set_config('cachetype',   'internal');
            set_config('rcache',      true);
            set_config('intcachemax', $CFG->enablerecordcache);
            unset_config('enablerecordcache');
            unset($CFG->enablerecordcache);
        }
    }

    if ($result && $oldversion < 2007012100) {
    /// Some old PG servers have user->firstname & user->lastname with 30cc. They must be 100cc.
    /// Fixing that conditionally. MDL-7110
        if ($CFG->dbfamily == 'postgres') {
        /// Get Metadata from user table
            $cols = array_change_key_case($db->MetaColumns($CFG->prefix . 'user'), CASE_LOWER);

        /// Process user->firstname if needed
            if ($col = $cols['firstname']) {
                if ($col->max_length < 100) {
                /// Changing precision of field firstname on table user to (100)
                    $table = new XMLDBTable('user');
                    $field = new XMLDBField('firstname');
                    $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, null, null, 'idnumber');

                /// Launch change of precision for field firstname
                    $result = $result && change_field_precision($table, $field);
                }
            }

        /// Process user->lastname if needed
            if ($col = $cols['lastname']) {
                if ($col->max_length < 100) {
                /// Changing precision of field lastname on table user to (100)
                    $table = new XMLDBTable('user');
                    $field = new XMLDBField('lastname');
                    $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, null, null, 'firstname');

                /// Launch change of precision for field lastname
                    $result = $result && change_field_precision($table, $field);
                }
            }
        }
    }

    if ($result && $oldversion < 2007012101) {

    /// Changing precision of field lang on table course to (30)
        $table = new XMLDBTable('course');
        $field = new XMLDBField('lang');
        $field->setAttributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, null, null, 'groupmodeforce');

    /// Launch change of precision for field course->lang
        $result = $result && change_field_precision($table, $field);

    /// Changing precision of field lang on table user to (30)
        $table = new XMLDBTable('user');
        $field = new XMLDBField('lang');
        $field->setAttributes(XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, null, 'en', 'country');

    /// Launch change of precision for field user->lang
        $result = $result && change_field_precision($table, $field);
    }

    if ($result && $oldversion < 2007012400) {

    /// Rename field access on table mnet_sso_access_control to accessctrl
        $table = new XMLDBTable('mnet_sso_access_control');
        $field = new XMLDBField('access');
        $field->setAttributes(XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, null, 'allow', 'mnet_host_id');

    /// Launch rename field accessctrl
        $result = $result && rename_field($table, $field, 'accessctrl');
    }

    if ($result && $oldversion < 2007012500) {
        execute_sql("DELETE FROM {$CFG->prefix}user WHERE username='changeme'", true);
    }

    if ($result && $oldversion < 2007020400) {
    /// Only for MySQL and PG, declare the user->ajax field as not null. MDL-8421.
        if ($CFG->dbfamily == 'mysql' || $CFG->dbfamily == 'postgres') {
        /// Changing nullability of field ajax on table user to not null
            $table = new XMLDBTable('user');
            $field = new XMLDBField('ajax');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '1', 'htmleditor');

        /// Launch change of nullability for field ajax
            $result = $result && change_field_notnull($table, $field);
        }
    }

    if (!empty($CFG->rolesactive) && $result && $oldversion < 2007021401) {
    /// create default logged in user role if not present - upgrade rom 1.7.x
        if (empty($CFG->defaultuserroleid) or empty($CFG->guestroleid) or $CFG->defaultuserroleid == $CFG->guestroleid) {
            if (!get_records('role', 'shortname', 'user')) {
                $userroleid = create_role(addslashes(get_string('authenticateduser')), 'user',
                                          addslashes(get_string('authenticateduserdescription')), 'moodle/legacy:user');
                if ($userroleid) {
                    reset_role_capabilities($userroleid);
                    set_config('defaultuserroleid', $userroleid);
                }
            }
        }
    }

    if ($result && $oldversion < 2007021501) {
    /// delete removed setting from config
        unset_config('tabselectedtofront');
    }


    if ($result && $oldversion < 2007032200) {

    /// Define table role_sortorder to be created
        $table = new XMLDBTable('role_sortorder');

    /// Adding fields to table role_sortorder
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('roleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('contextid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('sortoder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table role_sortorder
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->addKeyInfo('roleid', XMLDB_KEY_FOREIGN, array('roleid'), 'role', array('id'));
        $table->addKeyInfo('contextid', XMLDB_KEY_FOREIGN, array('contextid'), 'context', array('id'));

    /// Adding indexes to table role_sortorder
        $table->addIndexInfo('userid-roleid-contextid', XMLDB_INDEX_UNIQUE, array('userid', 'roleid', 'contextid'));

    /// Launch create table for role_sortorder
        $result = $result && create_table($table);
    }


    /// code to change lenghen tag field to 255, MDL-9095
    if ($result && $oldversion < 2007040400) {

    /// Define index text (not unique) to be dropped form tags
        $table = new XMLDBTable('tags');
        $index = new XMLDBIndex('text');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('text'));

    /// Launch drop index text
        $result = $result && drop_index($table, $index);

        $field = new XMLDBField('text');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null, 'userid');

    /// Launch change of type for field text
        $result = $result && change_field_type($table, $field);

        $index = new XMLDBIndex('text');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('text'));

    /// Launch add index text
        $result = $result && add_index($table, $index);
    }

    if ($result && $oldversion < 2007041100) {

    /// Define field idnumber to be added to course_modules
        $table = new XMLDBTable('course_modules');
        $field = new XMLDBField('idnumber');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, null, null, null, null, null, 'section');

    /// Launch add field idnumber
        $result = $result && add_field($table, $field);

    /// Define index idnumber (unique) to be added to course_modules
        $table = new XMLDBTable('course_modules');
        $index = new XMLDBIndex('idnumber');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('idnumber'));

    /// Launch add index idnumber
        $result = $result && add_index($table, $index);

    }

    /* Changes to the custom profile menu type - store values rather than indices.
       We could do all this with one tricky SQL statement but it's a one-off so no
       harm in using PHP loops */
    if ($result && $oldversion < 2007041600) {

    /// Get the menu fields
        if ($fields = get_records('user_info_field', 'datatype', 'menu')) {
            foreach ($fields as $field) {

            /// Get user data for the menu field
                if ($data = get_records('user_info_data', 'fieldid', $field->id)) {

                /// Get the menu options
                    $options = explode("\n", $field->param1);
                    foreach ($data as $d) {
                        $key = array_search($d->data, $options);

                    /// If the data is an integer and is not one of the options,
                    /// set the respective option value
                        if (is_int($d->data) and (($key === NULL) or ($key === false)) and isset($options[$d->data])) {
                                $d->data = $options[$d->data];
                                $result = $result && update_record('user_info_data', $d);
                        }
                    }
                }
            }
        }

    }

    /// adding new gradebook tables
    if ($result && $oldversion < 2007041800) {

    /// Define table events_handlers to be created
        $table = new XMLDBTable('events_handlers');

    /// Adding fields to table events_handlers
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('eventname', XMLDB_TYPE_CHAR, '166', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('handlermodule', XMLDB_TYPE_CHAR, '166', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('handlerfile', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('handlerfunction', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);

    /// Adding keys to table events_handlers
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table events_handlers
        $table->addIndexInfo('eventname-handlermodule', XMLDB_INDEX_UNIQUE, array('eventname', 'handlermodule'));

    /// Launch create table for events_handlers
        $result = $result && create_table($table);

    /// Define table events_queue to be created
        $table = new XMLDBTable('events_queue');

    /// Adding fields to table events_queue
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('eventdata', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('schedule', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('stackdump', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table events_queue
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

    /// Launch create table for events_queue
        $result = $result && create_table($table);

    /// Define table events_queue_handlers to be created
        $table = new XMLDBTable('events_queue_handlers');

    /// Adding fields to table events_queue_handlers
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('queuedeventid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('handlerid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('status', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null);
        $table->addFieldInfo('errormessage', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table events_queue_handlers
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('queuedeventid', XMLDB_KEY_FOREIGN, array('queuedeventid'), 'events_queue', array('id'));
        $table->addKeyInfo('handlerid', XMLDB_KEY_FOREIGN, array('handlerid'), 'events_handlers', array('id'));

    /// Launch create table for events_queue_handlers
        $result = $result && create_table($table);

    }

    if ($result && $oldversion < 2007043001) {

    /// Define field schedule to be added to events_handlers
        $table = new XMLDBTable('events_handlers');
        $field = new XMLDBField('schedule');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'handlerfunction');

    /// Launch add field schedule
        $result = $result && add_field($table, $field);

    /// Define field status to be added to events_handlers
        $table = new XMLDBTable('events_handlers');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'schedule');

    /// Launch add field status
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2007050201) {

    /// Define field theme to be added to course_categories
        $table = new XMLDBTable('course_categories');
        $field = new XMLDBField('theme');
        $field->setAttributes(XMLDB_TYPE_CHAR, '50', null, null, null, null, null, null, 'path');

    /// Launch add field theme
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2007051100) {

    /// Define field forceunique to be added to user_info_field
        $table = new XMLDBTable('user_info_field');
        $field = new XMLDBField('forceunique');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'visible');

    /// Launch add field forceunique
        $result = $result && add_field($table, $field);

    /// Define field signup to be added to user_info_field
        $table = new XMLDBTable('user_info_field');
        $field = new XMLDBField('signup');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'forceunique');

    /// Launch add field signup
        $result = $result && add_field($table, $field);
    }

    if (!empty($CFG->rolesactive) && $result && $oldversion < 2007051801) {
        // Get the role id of the "Auth. User" role and check if the default role id is different
        // note: use of assign_capability() is discouraged in upgrade script!
        $userrole = get_record( 'role', 'shortname', 'user' );
        $defaultroleid = $CFG->defaultuserroleid;

        if( $defaultroleid != $userrole->id ) {
            //  Add in the new moodle/my:manageblocks capibility to the default user role
            $context = get_context_instance(CONTEXT_SYSTEM, SITEID);
            assign_capability('moodle/my:manageblocks',CAP_ALLOW,$defaultroleid,$context->id);
        }
    }

    if ($result && $oldversion < 2007052200) {

    /// Define field schedule to be dropped from events_queue
        $table = new XMLDBTable('events_queue');
        $field = new XMLDBField('schedule');

    /// Launch drop field stackdump
        $result = $result && drop_field($table, $field);
    }

    if ($result && $oldversion < 2007052300) {
        require_once($CFG->dirroot . '/question/upgrade.php');
        $result = $result && question_remove_rqp_qtype();
    }

    if ($result && $oldversion < 2007060500) {

    /// Define field usermodified to be added to post
        $table = new XMLDBTable('post');
        $field = new XMLDBField('usermodified');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'created');

    /// Launch add field usermodified
        $result = $result && add_field($table, $field);

    /// Define key usermodified (foreign) to be added to post
        $table = new XMLDBTable('post');
        $key = new XMLDBKey('usermodified');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

    /// Launch add key usermodified
        $result = $result && add_key($table, $key);
    }

    if ($result && $oldversion < 2007070603) {
        // Small update of guest user to be 100% sure it has the correct mnethostid (MDL-10375)
        set_field('user', 'mnethostid', $CFG->mnet_localhost_id, 'username', 'guest');
    }

    if ($result && $oldversion < 2007071400) {
        /**
         ** mnet application table
         **/
        $table = new XMLDBTable('mnet_application');
        $table->comment = 'Information about applications on remote hosts';
        $f = $table->addFieldInfo('id',         XMLDB_TYPE_INTEGER,  '10', false,
                                  XMLDB_NOTNULL,XMLDB_SEQUENCE, null, null, null);
        $f = $table->addFieldInfo('name',  XMLDB_TYPE_CHAR,  '50', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('display_name',  XMLDB_TYPE_CHAR,  '50', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('xmlrpc_server_url',  XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);
        $f = $table->addFieldInfo('sso_land_url',  XMLDB_TYPE_CHAR,  '255', null,
                                  XMLDB_NOTNULL, NULL, null, null, null);

        // PK and indexes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        // Create the table
        $result = $result && create_table($table);

        // Insert initial applications (moodle and mahara)
        $application = new stdClass();
        $application->name                = 'moodle';
        $application->display_name        = 'Moodle';
        $application->xmlrpc_server_url   = '/mnet/xmlrpc/server.php';
        $application->sso_land_url        = '/auth/mnet/land.php';
        if ($result) {
            $newid  = insert_record('mnet_application', $application, false);
        }

        $application = new stdClass();
        $application->name                = 'mahara';
        $application->display_name        = 'Mahara';
        $application->xmlrpc_server_url   = '/api/xmlrpc/server.php';
        $application->sso_land_url        = '/auth/xmlrpc/land.php';
        $result = $result && insert_record('mnet_application', $application, false);

        // New mnet_host->applicationid field
        $table = new XMLDBTable('mnet_host');
        $field = new XMLDBField('applicationid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, $newid , 'last_log_id');

        $result = $result && add_field($table, $field);

    /// Define key applicationid (foreign) to be added to mnet_host
        $table = new XMLDBTable('mnet_host');
        $key = new XMLDBKey('applicationid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('applicationid'), 'mnet_application', array('id'));

    /// Launch add key applicationid
        $result = $result && add_key($table, $key);

    }

    if ($result && $oldversion < 2007071607) {
        require_once($CFG->dirroot . '/question/upgrade.php');
        $result = $result && question_remove_rqp_qtype_config_string();
    }

    if ($result && $oldversion < 2007072200) {

/// Remove all grade tables used in development phases - we need new empty tables for final gradebook upgrade
        $tables = array('grade_categories',
                        'grade_items',
                        'grade_calculations',
                        'grade_grades',
                        'grade_grades_raw',
                        'grade_grades_final',
                        'grade_grades_text',
                        'grade_outcomes',
                        'grade_outcomes_courses',
                        'grade_history',
                        'grade_import_newitem',
                        'grade_import_values');

        foreach ($tables as $table) {
            $table = new XMLDBTable($table);
            if (table_exists($table)) {
                drop_table($table);
            }
        }

        $tables = array('grade_categories_history',
                        'grade_items_history',
                        'grade_grades_history',
                        'grade_grades_text_history',
                        'grade_scale_history',
                        'grade_outcomes_history');

        foreach ($tables as $table) {
            $table = new XMLDBTable($table);
            if (table_exists($table)) {
                drop_table($table);
            }
        }


    /// Define table grade_outcomes to be created
        $table = new XMLDBTable('grade_outcomes');

    /// Adding fields to table grade_outcomes
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('fullname', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('scaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table grade_outcomes
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('scaleid', XMLDB_KEY_FOREIGN, array('scaleid'), 'scale', array('id'));
        $table->addKeyInfo('usermodified', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

    /// Launch create table for grade_outcomes
        $result = $result && create_table($table);


    /// Define table grade_categories to be created
        $table = new XMLDBTable('grade_categories');

    /// Adding fields to table grade_categories
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('parent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('depth', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('path', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('fullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('aggregation', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('keephigh', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('droplow', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregateonlygraded', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregateoutcomes', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregatesubcats', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table grade_categories
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('parent', XMLDB_KEY_FOREIGN, array('parent'), 'grade_categories', array('id'));

    /// Launch create table for grade_categories
        $result = $result && create_table($table);


    /// Define table grade_items to be created
        $table = new XMLDBTable('grade_items');

    /// Adding fields to table grade_items
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('categoryid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('itemname', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('itemtype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('itemmodule', XMLDB_TYPE_CHAR, '30', null, null, null, null, null, null);
        $table->addFieldInfo('iteminstance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('itemnumber', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('iteminfo', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('idnumber', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('calculation', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('gradetype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null, null, '1');
        $table->addFieldInfo('grademax', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '100');
        $table->addFieldInfo('grademin', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('scaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('outcomeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('gradepass', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('multfactor', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '1.0');
        $table->addFieldInfo('plusfactor', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregationcoef', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('display', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('decimals', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('hidden', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locktime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('needsupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table grade_items
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'grade_categories', array('id'));
        $table->addKeyInfo('scaleid', XMLDB_KEY_FOREIGN, array('scaleid'), 'scale', array('id'));
        $table->addKeyInfo('outcomeid', XMLDB_KEY_FOREIGN, array('outcomeid'), 'grade_outcomes', array('id'));

    /// Adding indexes to table grade_grades
        $table->addIndexInfo('locked-locktime', XMLDB_INDEX_NOTUNIQUE, array('locked', 'locktime'));
        $table->addIndexInfo('itemtype-needsupdate', XMLDB_INDEX_NOTUNIQUE, array('itemtype', 'needsupdate'));
        $table->addIndexInfo('gradetype', XMLDB_INDEX_NOTUNIQUE, array('gradetype'));

    /// Launch create table for grade_items
        $result = $result && create_table($table);


    /// Define table grade_grades to be created
        $table = new XMLDBTable('grade_grades');

    /// Adding fields to table grade_grades
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('itemid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('rawgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, null, null);
        $table->addFieldInfo('rawgrademax', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '100');
        $table->addFieldInfo('rawgrademin', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('rawscaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('finalgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, null, null);
        $table->addFieldInfo('hidden', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locked', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locktime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('exported', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('overridden', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('excluded', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('feedback', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('feedbackformat', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('information', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('informationformat', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table grade_grades
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('itemid', XMLDB_KEY_FOREIGN, array('itemid'), 'grade_items', array('id'));
        $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->addKeyInfo('rawscaleid', XMLDB_KEY_FOREIGN, array('rawscaleid'), 'scale', array('id'));
        $table->addKeyInfo('usermodified', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));

    /// Adding indexes to table grade_grades
        $table->addIndexInfo('locked-locktime', XMLDB_INDEX_NOTUNIQUE, array('locked', 'locktime'));

    /// Launch create table for grade_grades
        $result = $result && create_table($table);


    /// Define table grade_outcomes_history to be created
        $table = new XMLDBTable('grade_outcomes_history');

    /// Adding fields to table grade_outcomes_history
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('action', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('oldid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('source', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('loggeduser', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('fullname', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('scaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);

    /// Adding keys to table grade_outcomes_history
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('oldid', XMLDB_KEY_FOREIGN, array('oldid'), 'grade_outcomes', array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('scaleid', XMLDB_KEY_FOREIGN, array('scaleid'), 'scale', array('id'));
        $table->addKeyInfo('loggeduser', XMLDB_KEY_FOREIGN, array('loggeduser'), 'user', array('id'));

    /// Adding indexes to table grade_outcomes_history
        $table->addIndexInfo('action', XMLDB_INDEX_NOTUNIQUE, array('action'));

    /// Launch create table for grade_outcomes_history
        $result = $result && create_table($table);


    /// Define table grade_categories_history to be created
        $table = new XMLDBTable('grade_categories_history');

    /// Adding fields to table grade_categories_history
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('action', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('oldid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('source', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('loggeduser', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('parent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('depth', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('path', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('fullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('aggregation', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('keephigh', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('droplow', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregateonlygraded', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregateoutcomes', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregatesubcats', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

    /// Adding keys to table grade_categories_history
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('oldid', XMLDB_KEY_FOREIGN, array('oldid'), 'grade_categories', array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('parent', XMLDB_KEY_FOREIGN, array('parent'), 'grade_categories', array('id'));
        $table->addKeyInfo('loggeduser', XMLDB_KEY_FOREIGN, array('loggeduser'), 'user', array('id'));

    /// Adding indexes to table grade_categories_history
        $table->addIndexInfo('action', XMLDB_INDEX_NOTUNIQUE, array('action'));

    /// Launch create table for grade_categories_history
        $result = $result && create_table($table);


    /// Define table grade_items_history to be created
        $table = new XMLDBTable('grade_items_history');

    /// Adding fields to table grade_items_history
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('action', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('oldid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('source', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('loggeduser', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('categoryid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('itemname', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('itemtype', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('itemmodule', XMLDB_TYPE_CHAR, '30', null, null, null, null, null, null);
        $table->addFieldInfo('iteminstance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('itemnumber', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('iteminfo', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('idnumber', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('calculation', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null);
        $table->addFieldInfo('gradetype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null, null, '1');
        $table->addFieldInfo('grademax', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '100');
        $table->addFieldInfo('grademin', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('scaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('outcomeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('gradepass', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('multfactor', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '1.0');
        $table->addFieldInfo('plusfactor', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('aggregationcoef', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('display', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('decimals', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('hidden', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locktime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('needsupdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0');

    /// Adding keys to table grade_items_history
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('oldid', XMLDB_KEY_FOREIGN, array('oldid'), 'grade_items', array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'grade_categories', array('id'));
        $table->addKeyInfo('scaleid', XMLDB_KEY_FOREIGN, array('scaleid'), 'scale', array('id'));
        $table->addKeyInfo('outcomeid', XMLDB_KEY_FOREIGN, array('outcomeid'), 'grade_outcomes', array('id'));
        $table->addKeyInfo('loggeduser', XMLDB_KEY_FOREIGN, array('loggeduser'), 'user', array('id'));

    /// Adding indexes to table grade_items_history
        $table->addIndexInfo('action', XMLDB_INDEX_NOTUNIQUE, array('action'));

    /// Launch create table for grade_items_history
        $result = $result && create_table($table);


    /// Define table grade_grades_history to be created
        $table = new XMLDBTable('grade_grades_history');

    /// Adding fields to table grade_grades_history
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('action', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('oldid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('source', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('loggeduser', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('itemid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('rawgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, null, null);
        $table->addFieldInfo('rawgrademax', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '100');
        $table->addFieldInfo('rawgrademin', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('rawscaleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('finalgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, null, null);
        $table->addFieldInfo('hidden', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locked', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('locktime', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('exported', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('overridden', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('excluded', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('feedback', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('feedbackformat', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('information', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('informationformat', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

    /// Adding keys to table grade_grades_history
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('oldid', XMLDB_KEY_FOREIGN, array('oldid'), 'grade_grades', array('id'));
        $table->addKeyInfo('itemid', XMLDB_KEY_FOREIGN, array('itemid'), 'grade_items', array('id'));
        $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->addKeyInfo('rawscaleid', XMLDB_KEY_FOREIGN, array('rawscaleid'), 'scale', array('id'));
        $table->addKeyInfo('usermodified', XMLDB_KEY_FOREIGN, array('usermodified'), 'user', array('id'));
        $table->addKeyInfo('loggeduser', XMLDB_KEY_FOREIGN, array('loggeduser'), 'user', array('id'));

    /// Adding indexes to table grade_grades_history
        $table->addIndexInfo('action', XMLDB_INDEX_NOTUNIQUE, array('action'));

    /// Launch create table for grade_grades_history
        $result = $result && create_table($table);

    /// upgrade the old 1.8 gradebook - migrade data into new grade tables
        if ($result) {
            require_once($CFG->libdir.'/db/upgradelib.php');
            if ($rs = get_recordset('course')) {
                if ($rs->RecordCount() > 0) {
                    while ($course = rs_fetch_next_record($rs)) {
                        // this function uses SQL only, it must not be changed after 1.9 goes stable!!
                        if (!upgrade_18_gradebook($course->id)) {
                            $result = false;
                            break;
                        }
                    }
                }
                rs_close($rs);
            }
        }

    /// migrate grade letter table
        $result = $result && upgrade_18_letters();
    }

    if ($result && $oldversion < 2007072400) {
    /// Dropping one DEFAULT in a TEXT column. It's was only one remaining
    /// since Moodle 1.7, so new servers won't have those anymore.

    /// Changing the default of field sessdata on table sessions2 to drop it
        $table = new XMLDBTable('sessions2');
        $field = new XMLDBField('sessdata');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, null, null, 'modified');

    /// Launch change of default for field sessdata
        $result = $result && change_field_default($table, $field);
    }


    if ($result && $oldversion < 2007073100) {
    /// Define table grade_outcomes_courses to be created
        $table = new XMLDBTable('grade_outcomes_courses');

    /// Adding fields to table grade_outcomes_courses
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('outcomeid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table grade_outcomes_courses
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->addKeyInfo('outcomeid', XMLDB_KEY_FOREIGN, array('outcomeid'), 'grade_outcomes', array('id'));
        $table->addKeyInfo('courseid-outcomeid', XMLDB_KEY_UNIQUE, array('courseid', 'outcomeid'));
    /// Launch create table for grade_outcomes_courses
        $result = $result && create_table($table);

    }


    if ($result && $oldversion < 2007073101) {    // Add new tag tables

    /// Define table tag to be created
        $table = new XMLDBTable('tag');

    /// Adding fields to table tag
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('tagtype', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null);
        $table->addFieldInfo('descriptionformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('flag', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, null, '0');
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table tag
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table tag
        $table->addIndexInfo('name', XMLDB_INDEX_UNIQUE, array('name'));

    /// Launch create table for tag
        $result = $result && create_table($table);



    /// Define table tag_correlation to be created
        $table = new XMLDBTable('tag_correlation');

    /// Adding fields to table tag_correlation
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('tagid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('correlatedtags', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table tag_correlation
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table tag_correlation
        $table->addIndexInfo('tagid', XMLDB_INDEX_UNIQUE, array('tagid'));

    /// Launch create table for tag_correlation
        $result = $result && create_table($table);



    /// Define table tag_instance to be created
        $table = new XMLDBTable('tag_instance');

    /// Adding fields to table tag_instance
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('tagid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('itemtype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('itemid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table tag_instance
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Adding indexes to table tag_instance
        $table->addIndexInfo('tagiditem', XMLDB_INDEX_NOTUNIQUE, array('tagid', 'itemtype', 'itemid'));

    /// Launch create table for tag_instance
        $result = $result && create_table($table);

    }


    if ($result && $oldversion < 2007073103) {

    /// Define field rawname to be added to tag
        $table = new XMLDBTable('tag');
        $field = new XMLDBField('rawname');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null, 'name');

    /// Launch add field rawname
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2007073105) {

    /// Define field description to be added to grade_outcomes
        $table = new XMLDBTable('grade_outcomes');
        $field = new XMLDBField('description');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'scaleid');
        /// Launch add field description
            $result = $result && add_field($table, $field);
        }

        $table = new XMLDBTable('grade_outcomes_history');
        $field = new XMLDBField('description');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'scaleid');
        /// Launch add field description
            $result = $result && add_field($table, $field);
        }
    }

    // adding unique contraint on (courseid,shortname) of an outcome
    if ($result && $oldversion < 2007080100) {

    /// Define key courseid-shortname (unique) to be added to grade_outcomes
        $table = new XMLDBTable('grade_outcomes');
        $key = new XMLDBKey('courseid-shortname');
        $key->setAttributes(XMLDB_KEY_UNIQUE, array('courseid', 'shortname'));

    /// Launch add key courseid-shortname
        $result = $result && add_key($table, $key);
    }

    /// originally there was supportname and supportemail upgrade code - this is handled in upgradesettings.php instead

    /// MDL-10679, context_rel clean up
    if ($result && $oldversion < 2007080200) {
        delete_records('context_rel');
        /// /deprecated and gone / build_context_rel();
    }

    if ($result && $oldversion < 2007080202) {

    /// Define index tagiditem (not unique) to be dropped form tag_instance
        $table = new XMLDBTable('tag_instance');
        $index = new XMLDBIndex('tagiditem');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('tagid', 'itemtype', 'itemid'));

    /// Launch drop index tagiditem
        drop_index($table, $index);

   /// Define index tagiditem (unique) to be added to tag_instance
        $table = new XMLDBTable('tag_instance');
        $index = new XMLDBIndex('tagiditem');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('tagid', 'itemtype', 'itemid'));

    /// Launch add index tagiditem
        $result = $result && add_index($table, $index);

    }

    if ($result && $oldversion < 2007080300) {

    /// Define field aggregateoutcomes to be added to grade_categories
        $table = new XMLDBTable('grade_categories');
        $field = new XMLDBField('aggregateoutcomes');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'droplow');

        /// Launch add field aggregateoutcomes
            $result = $result && add_field($table, $field);
        }

    /// Define field aggregateoutcomes to be added to grade_categories
        $table = new XMLDBTable('grade_categories_history');
        $field = new XMLDBField('aggregateoutcomes');
        if (!field_exists($table, $field)) {
            $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'droplow');

        /// Launch add field aggregateoutcomes
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2007080800) { /// Normalize course->shortname MDL-10026

    /// Changing precision of field shortname on table course to (100)
        $table = new XMLDBTable('course');
        $field = new XMLDBField('shortname');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, null, null, 'fullname');

    /// Launch change of precision for field shortname
        $result = $result && change_field_precision($table, $field);
    }

    if ($result && $oldversion < 2007080900) {
    /// Add context.path & index
        $table = new XMLDBTable('context');
        $field = new XMLDBField('path');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'instanceid');
        $result = $result && add_field($table, $field);
        $table = new XMLDBTable('context');
        $index = new XMLDBIndex('path');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('path'));
        $result = $result && add_index($table, $index);

    /// Add context.depth
        $table = new XMLDBTable('context');
        $field = new XMLDBField('depth');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'path');
        $result = $result && add_field($table, $field);

    /// make sure the system context has proper data
        get_system_context(false);
    }

    if ($result && $oldversion < 2007080903) {
    /// Define index
        $table = new XMLDBTable('grade_grades');
        $index = new XMLDBIndex('locked-locktime');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('locked', 'locktime'));

        if (!index_exists($table, $index)) {
        /// Launch add index
            $result = $result && add_index($table, $index);
        }

    /// Define index
        $table = new XMLDBTable('grade_items');
        $index = new XMLDBIndex('locked-locktime');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('locked', 'locktime'));

        if (!index_exists($table, $index)) {
        /// Launch add index
            $result = $result && add_index($table, $index);
        }

    /// Define index itemtype-needsupdate (not unique) to be added to grade_items
        $table = new XMLDBTable('grade_items');
        $index = new XMLDBIndex('itemtype-needsupdate');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('itemtype', 'needsupdate'));
        if (!index_exists($table, $index)) {
        /// Launch add index itemtype-needsupdate
            $result = $result && add_index($table, $index);
        }

    /// Define index
        $table = new XMLDBTable('grade_items');
        $index = new XMLDBIndex('gradetype');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('gradetype'));

        if (!index_exists($table, $index)) {
        /// Launch add index
            $result = $result && add_index($table, $index);
        }

    }

    if ($result && $oldversion < 2007081000) {
        require_once($CFG->dirroot . '/question/upgrade.php');
        $result = $result && question_upgrade_context_etc();
    }

    if ($result && $oldversion < 2007081302) {
        require_once($CFG->libdir.'/db/upgradelib.php');

        if (table_exists(new XMLDBTable('groups_groupings'))) {
    /// IF 'groups_groupings' table exists, this is for 1.8.* only.
            $result = $result && upgrade_18_groups();

        } else {
    /// ELSE, 1.7.*/1.6.*/1.5.* - create 'groupings' and 'groupings_groups' + rename password to enrolmentkey
            $result = $result && upgrade_17_groups();
        }

    /// For both 1.8.* and 1.7.*/1.6.*..

        // delete not used fields
        $table = new XMLDBTable('groups');
        $field = new XMLDBField('theme');
        if (field_exists($table, $field)) {
            drop_field($table, $field);
        }
        $table = new XMLDBTable('groups');
        $field = new XMLDBField('lang');
        if (field_exists($table, $field)) {
            drop_field($table, $field);
        }

    /// Add groupingid field/f.key to 'course' table.
        $table = new XMLDBTable('course');
        $field = new XMLDBField('defaultgroupingid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', $prev='groupmodeforce');
        $result = $result && add_field($table, $field);


    /// Add grouping ID, grouponly field/f.key to 'course_modules' table.
        $table = new XMLDBTable('course_modules');
        $field = new XMLDBField('groupingid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', $prev='groupmode');
        $result = $result && add_field($table, $field);

        $table = new XMLDBTable('course_modules');
        $field = new XMLDBField('groupmembersonly');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', $prev='groupingid');
        $result = $result && add_field($table, $field);

        $table = new XMLDBTable('course_modules');
        $key = new XMLDBKey('groupingid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('groupingid'), 'groupings', array('id'));
        $result = $result && add_key($table, $key);

    }

    if ($result && $oldversion < 2007082300) {

    /// Define field ordering to be added to tag_instance table
        $table = new XMLDBTable('tag_instance');
        $field = new XMLDBField('ordering');

        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'itemid');

    /// Launch add field rawname
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2007082700) {

    /// Define field timemodified to be added to tag_instance
        $table = new XMLDBTable('tag_instance');
        $field = new XMLDBField('timemodified');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'ordering');

    /// Launch add field timemodified
        $result = $result && add_field($table, $field);
    }

    /// migrate all tags table to tag - this code MUST use SQL only,
    /// because if the db structure changes the library functions will fail in future
    if ($result && $oldversion < 2007082701) {
        $tagrefs = array(); // $tagrefs[$oldtagid] = $newtagid
        if ($rs = get_recordset('tags')) {
            $db->debug = false;
            if ($rs->RecordCount() > 0) {
                while ($oldtag = rs_fetch_next_record($rs)) {
                    $raw_normalized = clean_param($oldtag->text, PARAM_TAG);
                    $normalized     = moodle_strtolower($raw_normalized);
                    // if this tag does not exist in tag table yet
                    if (!$newtag = get_record('tag', 'name', $normalized, '', '', '', '', 'id')) {
                        $itag = new object();
                        $itag->name         = $normalized;
                        $itag->rawname      = $raw_normalized;
                        $itag->userid       = $oldtag->userid;
                        $itag->timemodified = time();
                        $itag->descriptionformat = 0; // default format
                        if ($oldtag->type == 'official') {
                            $itag->tagtype  = 'official';
                        } else {
                            $itag->tagtype  = 'default';
                        }

                        if ($idx = insert_record('tag', $itag)) {
                            $tagrefs[$oldtag->id] = $idx;
                        }
                    // if this tag is already used by tag table
                    } else {
                        $tagrefs[$oldtag->id] = $newtag->id;
                    }
                }
            }
            $db->debug = true;
            rs_close($rs);
        }

        // fetch all the tag instances and migrate them as well
        if ($rs = get_recordset('blog_tag_instance')) {
            $db->debug = false;
            if ($rs->RecordCount() > 0) {
                while ($blogtag = rs_fetch_next_record($rs)) {
                    if (array_key_exists($blogtag->tagid, $tagrefs)) {
                        $tag_instance = new object();
                        $tag_instance->tagid        = $tagrefs[$blogtag->tagid];
                        $tag_instance->itemtype     = 'blog';
                        $tag_instance->itemid       = $blogtag->entryid;
                        $tag_instance->ordering     = 1; // does not matter much, because originally there was no ordering in blogs
                        $tag_instance->timemodified = time();
                        insert_record('tag_instance', $tag_instance);
                    }
                }
            }
            $db->debug = true;
            rs_close($rs);
        }

        unset($tagrefs); // release memory

        $table = new XMLDBTable('tags');
        drop_table($table);
        $table = new XMLDBTable('blog_tag_instance');
        drop_table($table);
    }

    /// MDL-11015, MDL-11016
    if ($result && $oldversion < 2007082800) {

    /// Changing type of field userid on table tag to int
        $table = new XMLDBTable('tag');
        $field = new XMLDBField('userid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'id');

    /// Launch change of type for field userid
        $result = $result && change_field_type($table, $field);

    /// Changing type of field descriptionformat on table tag to int
        $table = new XMLDBTable('tag');
        $field = new XMLDBField('descriptionformat');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'description');

    /// Launch change of type for field descriptionformat
        $result = $result && change_field_type($table, $field);

    /// Define key userid (foreign) to be added to tag
        $table = new XMLDBTable('tag');
        $key = new XMLDBKey('userid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

    /// Launch add key userid
        $result = $result && add_key($table, $key);

    /// Define index tagiditem (unique) to be dropped form tag_instance
        $table = new XMLDBTable('tag_instance');
        $index = new XMLDBIndex('tagiditem');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('tagid', 'itemtype', 'itemid'));

    /// Launch drop index tagiditem
        $result = $result && drop_index($table, $index);

    /// Changing type of field tagid on table tag_instance to int
        $table = new XMLDBTable('tag_instance');
        $field = new XMLDBField('tagid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'id');

    /// Launch change of type for field tagid
        $result = $result && change_field_type($table, $field);

    /// Define key tagid (foreign) to be added to tag_instance
        $table = new XMLDBTable('tag_instance');
        $key = new XMLDBKey('tagid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('tagid'), 'tag', array('id'));

     /// Launch add key tagid
        $result = $result && add_key($table, $key);

    /// Changing sign of field itemid on table tag_instance to unsigned
        $table = new XMLDBTable('tag_instance');
        $field = new XMLDBField('itemid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'itemtype');

    /// Launch change of sign for field itemid
        $result = $result && change_field_unsigned($table, $field);

    /// Changing sign of field ordering on table tag_instance to unsigned
        $table = new XMLDBTable('tag_instance');
        $field = new XMLDBField('ordering');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'itemid');

    /// Launch change of sign for field ordering
        $result = $result && change_field_unsigned($table, $field);

    /// Define index itemtype-itemid-tagid (unique) to be added to tag_instance
        $table = new XMLDBTable('tag_instance');
        $index = new XMLDBIndex('itemtype-itemid-tagid');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('itemtype', 'itemid', 'tagid'));

    /// Launch add index itemtype-itemid-tagid
        $result = $result && add_index($table, $index);

    /// Define index tagid (unique) to be dropped form tag_correlation
        $table = new XMLDBTable('tag_correlation');
        $index = new XMLDBIndex('tagid');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('tagid'));

    /// Launch drop index tagid
        $result = $result && drop_index($table, $index);

    /// Changing type of field tagid on table tag_correlation to int
        $table = new XMLDBTable('tag_correlation');
        $field = new XMLDBField('tagid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'id');

    /// Launch change of type for field tagid
        $result = $result && change_field_type($table, $field);


    /// Define key tagid (foreign) to be added to tag_correlation
        $table = new XMLDBTable('tag_correlation');
        $key = new XMLDBKey('tagid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('tagid'), 'tag', array('id'));

    /// Launch add key tagid
        $result = $result && add_key($table, $key);

    }


    if ($result && $oldversion < 2007082801) {

    /// Define table user_private_key to be created
        $table = new XMLDBTable('user_private_key');

    /// Adding fields to table user_private_key
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('script', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('value', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('instance', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('iprestriction', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);
        $table->addFieldInfo('validuntil', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table user_private_key
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

    /// Adding indexes to table user_private_key
        $table->addIndexInfo('script-value', XMLDB_INDEX_NOTUNIQUE, array('script', 'value'));

    /// Launch create table for user_private_key
        $result = $result && create_table($table);
    }

/// Going to modify the applicationid from int(1) to int(10). Dropping and
/// re-creating the associated keys/indexes is mandatory to be cross-db. MDL-11042
    if ($result && $oldversion < 2007082803) {

    /// Define key applicationid (foreign) to be dropped form mnet_host
        $table = new XMLDBTable('mnet_host');
        $key = new XMLDBKey('applicationid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('applicationid'), 'mnet_application', array('id'));

    /// Launch drop key applicationid
        $result = $result && drop_key($table, $key);

    /// Changing type of field applicationid on table mnet_host to int
        $field = new XMLDBField('applicationid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '1', 'last_log_id');

    /// Launch change of type for field applicationid
        $result = $result && change_field_type($table, $field);

    /// Define key applicationid (foreign) to be added to mnet_host
        $key = new XMLDBKey('applicationid');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('applicationid'), 'mnet_application', array('id'));

    /// Launch add key applicationid
        $result = $result && add_key($table, $key);

    }

    if ($result && $oldversion < 2007090503) {
    /// Define field aggregatesubcats to be added to grade_categories
        $table = new XMLDBTable('grade_categories');
        $field = new XMLDBField('aggregatesubcats');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'aggregateoutcomes');

        if (!field_exists($table, $field)) {
        /// Launch add field aggregateonlygraded
            $result = $result && add_field($table, $field);
        }

    /// Define field aggregateonlygraded to be added to grade_categories
        $table = new XMLDBTable('grade_categories');
        $field = new XMLDBField('aggregateonlygraded');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'droplow');

        if (!field_exists($table, $field)) {
        /// Launch add field aggregateonlygraded
            $result = $result && add_field($table, $field);
        }

    /// Define field aggregatesubcats to be added to grade_categories_history
        $table = new XMLDBTable('grade_categories_history');
        $field = new XMLDBField('aggregatesubcats');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'aggregateoutcomes');

        if (!field_exists($table, $field)) {
        /// Launch add field aggregateonlygraded
            $result = $result && add_field($table, $field);
        }

    /// Define field aggregateonlygraded to be added to grade_categories_history
        $table = new XMLDBTable('grade_categories_history');
        $field = new XMLDBField('aggregateonlygraded');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'droplow');

        if (!field_exists($table, $field)) {
        /// Launch add field aggregateonlygraded
            $result = $result && add_field($table, $field);
        }

    /// upgrade path in grade_categrories table - now using slash on both ends
        $concat = sql_concat('path', "'/'");
        $sql = "UPDATE {$CFG->prefix}grade_categories SET path = $concat WHERE path NOT LIKE '/%/'";
        execute_sql($sql, true);

    /// convert old aggregation constants if needed
        for ($i=0; $i<=12; $i=$i+2) {
            $j = $i+1;
            $sql = "UPDATE {$CFG->prefix}grade_categories SET aggregation = $i, aggregateonlygraded = 1 WHERE aggregation = $j";
            execute_sql($sql, true);
        }
    }

/// To have UNIQUE indexes over NULLable columns isn't cross-db at all
/// so we create a non unique index and programatically enforce uniqueness
    if ($result && $oldversion < 2007090600) {

    /// Define index idnumber (unique) to be dropped form course_modules
        $table = new XMLDBTable('course_modules');
        $index = new XMLDBIndex('idnumber');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('idnumber'));

    /// Launch drop index idnumber
        $result = $result && drop_index($table, $index);

    /// Define index idnumber-course (not unique) to be added to course_modules
        $table = new XMLDBTable('course_modules');
        $index = new XMLDBIndex('idnumber-course');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('idnumber', 'course'));

    /// Launch add index idnumber-course
        $result = $result && add_index($table, $index);

    /// Define index idnumber-courseid (not unique) to be added to grade_items
        $table = new XMLDBTable('grade_items');
        $index = new XMLDBIndex('idnumber-courseid');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('idnumber', 'courseid'));

    /// Launch add index idnumber-courseid
        $result = $result && add_index($table, $index);

    }

/// Create the permanent context_temp table to be used by build_context_path()
    if ($result && $oldversion < 2007092001) {

    /// Define table context_temp to be created
        $table = new XMLDBTable('context_temp');

    /// Adding fields to table context_temp
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('path', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('depth', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table context_temp
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for context_temp
        $result = $result && create_table($table);

    /// Recalculate depths, paths and so on
        if (!empty($CFG->rolesactive)) {
            cleanup_contexts();
            build_context_path(true);
            load_all_capabilities();
        }
    }

    /**
     * Merging of grade_grades_text back into grade_grades
     */
    if ($result && $oldversion < 2007092002) {

    /// Define field feedback to be added to grade_grades
        $table = new XMLDBTable('grade_grades');
        $field = new XMLDBField('feedback');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null, 'excluded');

        if (!field_exists($table, $field)) {
        /// Launch add field feedback
            $result = $result && add_field($table, $field);
        }

    /// Define field feedbackformat to be added to grade_grades
        $table = new XMLDBTable('grade_grades');
        $field = new XMLDBField('feedbackformat');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'feedback');

        if (!field_exists($table, $field)) {
        /// Launch add field feedbackformat
            $result = $result && add_field($table, $field);
        }

    /// Define field information to be added to grade_grades
        $table = new XMLDBTable('grade_grades');
        $field = new XMLDBField('information');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null, 'feedbackformat');

        if (!field_exists($table, $field)) {
        /// Launch add field information
            $result = $result && add_field($table, $field);
        }

    /// Define field informationformat to be added to grade_grades
        $table = new XMLDBTable('grade_grades');
        $field = new XMLDBField('informationformat');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'information');

        if (!field_exists($table, $field)) {
        /// Launch add field informationformat
            $result = $result && add_field($table, $field);
        }

    /// Define field feedback to be added to grade_grades_history
        $table = new XMLDBTable('grade_grades_history');
        $field = new XMLDBField('feedback');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null, 'excluded');

        if (!field_exists($table, $field)) {
        /// Launch add field feedback
            $result = $result && add_field($table, $field);
        }

    /// Define field feedbackformat to be added to grade_grades_history
        $table = new XMLDBTable('grade_grades_history');
        $field = new XMLDBField('feedbackformat');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'feedback');

        if (!field_exists($table, $field)) {
        /// Launch add field feedbackformat
            $result = $result && add_field($table, $field);
        }

    /// Define field information to be added to grade_grades_history
        $table = new XMLDBTable('grade_grades_history');
        $field = new XMLDBField('information');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null, 'feedbackformat');

        if (!field_exists($table, $field)) {
        /// Launch add field information
            $result = $result && add_field($table, $field);
        }

    /// Define field informationformat to be added to grade_grades_history
        $table = new XMLDBTable('grade_grades_history');
        $field = new XMLDBField('informationformat');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'information');

        if (!field_exists($table, $field)) {
        /// Launch add field informationformat
            $result = $result && add_field($table, $field);
        }

        $table = new XMLDBTable('grade_grades_text');
        if ($result and table_exists($table)) {
            //migrade existing data into grade_grades table - this is slow but works for all dbs,
            //it will be executed on development sites only
            $fields = array('feedback', 'information');
            foreach ($fields as $field) {
                $sql = "UPDATE {$CFG->prefix}grade_grades
                           SET $field = (
                                SELECT $field
                                  FROM {$CFG->prefix}grade_grades_text ggt
                                 WHERE ggt.gradeid = {$CFG->prefix}grade_grades.id)";
                $result = execute_sql($sql) && $result;
            }
            $fields = array('feedbackformat', 'informationformat');
            foreach ($fields as $field) {
                $sql = "UPDATE {$CFG->prefix}grade_grades
                           SET $field = COALESCE((
                                SELECT $field
                                  FROM {$CFG->prefix}grade_grades_text ggt
                                 WHERE ggt.gradeid = {$CFG->prefix}grade_grades.id), 0)";
                $result = execute_sql($sql) && $result;
            }

            if ($result) {
                $tables = array('grade_grades_text', 'grade_grades_text_history');

                foreach ($tables as $table) {
                    $table = new XMLDBTable($table);
                    if (table_exists($table)) {
                        drop_table($table);
                    }
                }
            }
        }
    }

    if ($result && $oldversion < 2007092803) {

/// Remove obsoleted unit tests tables - they will be recreated automatically
        $tables = array('grade_categories',
                        'scale',
                        'grade_items',
                        'grade_calculations',
                        'grade_grades',
                        'grade_grades_raw',
                        'grade_grades_final',
                        'grade_grades_text',
                        'grade_outcomes',
                        'grade_outcomes_courses');

        foreach ($tables as $tablename) {
            $table = new XMLDBTable('unittest_'.$tablename);
            if (table_exists($table)) {
                drop_table($table);
            }
            $table = new XMLDBTable('unittest_'.$tablename.'_history');
            if (table_exists($table)) {
                drop_table($table);
            }
        }

    /// Define field display to be added to grade_items
        $table = new XMLDBTable('grade_items');
        $field = new XMLDBField('display');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0', 'sortorder');

    /// Launch add field display
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        } else {
            $result = $result && change_field_default($table, $field);
        }

    /// Define field display to be added to grade_items_history
        $table = new XMLDBTable('grade_items_history');
        $field = new XMLDBField('display');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, '0', 'sortorder');

    /// Launch add field display
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        }


    /// Define field decimals to be added to grade_items
        $table = new XMLDBTable('grade_items');
        $field = new XMLDBField('decimals');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, null, 'display');

    /// Launch add field decimals
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        } else {
            $result = $result && change_field_default($table, $field);
            $result = $result && change_field_notnull($table, $field);
        }

    /// Define field decimals to be added to grade_items_history
        $table = new XMLDBTable('grade_items_history');
        $field = new XMLDBField('decimals');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, null, 'display');

    /// Launch add field decimals
        if (!field_exists($table, $field)) {
            $result = $result && add_field($table, $field);
        }


    /// fix incorrect -1 default for grade_item->display
        execute_sql("UPDATE {$CFG->prefix}grade_items SET display=0 WHERE display=-1");
    }

    if ($result && $oldversion < 2007092806) {
        require_once($CFG->libdir.'/db/upgradelib.php');

        $result = upgrade_18_letters(); // executes on dev sites only

    /// Define index contextidlowerboundary (not unique) to be added to grade_letters
        $table = new XMLDBTable('grade_letters');
        $index = new XMLDBIndex('contextid-lowerboundary');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('contextid', 'lowerboundary'));

    /// Launch add index contextidlowerboundary
        if (!index_exists($table, $index)) {
            $result = $result && add_index($table, $index);
        }
    }

    if ($result && $oldversion < 2007100100) {

    /// Define table cache_flags to be created
        $table = new XMLDBTable('cache_flags');

    /// Adding fields to table cache_flags
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('flagtype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('value', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('expiry', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table cache_flags
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /*
     * Note: mysql can not create indexes on text fields larger than 333 chars! 
     */

    /// Adding indexes to table cache_flags
        $table->addIndexInfo('flagtype', XMLDB_INDEX_NOTUNIQUE, array('flagtype'));
        $table->addIndexInfo('name', XMLDB_INDEX_NOTUNIQUE, array('name'));

    /// Launch create table for cache_flags
        if (!table_exists($table)) {
            $result = $result && create_table($table);
        }
    }


    if ($oldversion < 2007100300) {
    /// MNET stuff for roaming theme
    /// Define field force_theme to be added to mnet_host
        $table = new XMLDBTable('mnet_host');
        $field = new XMLDBField('force_theme');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'last_log_id');

    /// Launch add field force_theme
        $result = $result && add_field($table, $field);

    /// Define field theme to be added to mnet_host
        $table = new XMLDBTable('mnet_host');
        $field = new XMLDBField('theme');
        $field->setAttributes(XMLDB_TYPE_CHAR, '100', null, null, null, null, null, null, 'force_theme');

    /// Launch add field theme
        $result = $result && add_field($table, $field);
    }

    if ($result && $oldversion < 2007100301) {

    /// Define table cache_flags to be created
        $table = new XMLDBTable('cache_flags');
        $index = new XMLDBIndex('typename');
        if (index_exists($table, $index)) {
            $result = $result && drop_index($table, $index);
        }
        
        $table = new XMLDBTable('cache_flags');
        $index = new XMLDBIndex('flagtype');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('flagtype'));
        if (!index_exists($table, $index)) {
            $result = $result && add_index($table, $index);
        }

        $table = new XMLDBTable('cache_flags');
        $index = new XMLDBIndex('name');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('name'));
        if (!index_exists($table, $index)) {
            $result = $result && add_index($table, $index);
        }
    }

    if ($result && $oldversion < 2007100303) {

    /// Changing nullability of field summary on table course to null
        $table = new XMLDBTable('course');
        $field = new XMLDBField('summary');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'idnumber');

    /// Launch change of nullability for field summary
        $result = $result && change_field_notnull($table, $field);
    }

    if ($result && $oldversion < 2007100500) {
    /// for dev sites - it is ok to do this repeatedly

    /// Changing nullability of field path on table context to null
        $table = new XMLDBTable('context');
        $field = new XMLDBField('path');
        $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null, 'instanceid');

    /// Launch change of nullability for field path
        $result = $result && change_field_notnull($table, $field);
    }

    if ($result && $oldversion < 2007100700) {

    /// first drop existing tables - we do not need any data from there
        $table = new XMLDBTable('grade_import_values');
        if (table_exists($table)) {
            drop_table($table);
        }

        $table = new XMLDBTable('grade_import_newitem');
        if (table_exists($table)) {
            drop_table($table);
        }

    /// Define table grade_import_newitem to be created
        $table = new XMLDBTable('grade_import_newitem');

    /// Adding fields to table grade_import_newitem
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('itemname', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('importcode', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('importer', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

    /// Adding keys to table grade_import_newitem
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('importer', XMLDB_KEY_FOREIGN, array('importer'), 'user', array('id'));

    /// Launch create table for grade_import_newitem
        $result = $result && create_table($table);


    /// Define table grade_import_values to be created
        $table = new XMLDBTable('grade_import_values');

    /// Adding fields to table grade_import_values
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('itemid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('newgradeitem', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('finalgrade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, null, null);
        $table->addFieldInfo('feedback', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, null, null);
        $table->addFieldInfo('importcode', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
        $table->addFieldInfo('importer', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null);

    /// Adding keys to table grade_import_values
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('itemid', XMLDB_KEY_FOREIGN, array('itemid'), 'grade_items', array('id'));
        $table->addKeyInfo('newgradeitem', XMLDB_KEY_FOREIGN, array('newgradeitem'), 'grade_import_newitem', array('id'));
        $table->addKeyInfo('importer', XMLDB_KEY_FOREIGN, array('importer'), 'user', array('id'));

    /// Launch create table for grade_import_values
        $result = $result && create_table($table);

    }


/* NOTE: please keep this at the end of upgrade file for now ;-)
    /// drop old gradebook tables
    if ($result && $oldversion < xxxxxxxx) {
        $tables = array('grade_category',
                        'grade_item',
                        'grade_letter',
                        'grade_preferences',
                        'grade_exceptions');

        foreach ($tables as $table) {
            $table = new XMLDBTable($table);
            if (table_exists($table)) {
                drop_table($table);
            }
        }
    }
*/


    return $result;
}


?>
