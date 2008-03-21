<?PHP
/**
 * A Samouk's extension to core table mdl_user
 * 
 * @author Kowy
 * @name upgrade.php
 * @version 1.9.0
 */

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


function xmldb_local_upgrade($oldversion=0) {

    global $CFG, $THEME, $USER, $db;

    $result = true;

    if ($oldversion < 2007101901) {
        /// Create su_user table (additional attributes to user table)
        $table  = new XMLDBTable('user');
        $field = new XMLDBField('su_isadvanced');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', false, false, false, false, null, null, 'screenreader');
//        $table->addFieldInfo('id',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, false, null, null, null);
//        $table->addFieldInfo('user_id',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, false, false, null, null, 'id');
//        $table->addFieldInfo('isAdvanced',XMLDB_TYPE_INTEGER, '1', false, false, false, false, null, null, 'user_id');
//        $table->addKeyInfo('primary',XMLDB_KEY_PRIMARY,array('id'));
//        $table->addKeyInfo('fk_su_user_user',XMLDB_KEY_FOREIGN,array('user_id'),'user',array('id'));
        // Create the table
        $result = $result && add_field($table,$field);
    } 
    
    if ($result && $oldversion < 2008031801) {

    /// Define table enrol_bank_requests to be created
        $table = new XMLDBTable('enrol_bank_requests');

    /// Adding fields to table enrol_bank_requests
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->addFieldInfo('varnum', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '4294967295');
        $table->addFieldInfo('requesttime', XMLDB_TYPE_DATETIME, null, null, null, null, null, null, null);

    /// Adding keys to table enrol_bank_requests
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('fk_enrol_bank_request_user', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->addKeyInfo('fk_enrol_bank_request_course', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));

    /// Adding indexes to table enrol_bank_requests
        $table->addIndexInfo('ind_enrol_bank_requests_varnum', XMLDB_INDEX_UNIQUE, array('varnum'));

    /// Launch create table for enrol_bank_requests
        $result = $result && create_table($table);
    }
    return $result;
    
    return $result;
}


?>
