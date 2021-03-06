<?php // $Id: grade_grade.php,v 1.12 2007/09/28 07:55:51 nicolasconnault Exp $

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 2001-2003  Martin Dougiamas  http://dougiamas.com       //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once('grade_object.php');

class grade_grade extends grade_object {

    /**
     * The DB table.
     * @var string $table
     */
    var $table = 'grade_grades';

    /**
     * Array of required table fields, must start with 'id'.
     * @var array $required_fields
     */
    var $required_fields = array('id', 'itemid', 'userid', 'rawgrade', 'rawgrademax', 'rawgrademin',
                                 'rawscaleid', 'usermodified', 'finalgrade', 'hidden', 'locked',
                                 'locktime', 'exported', 'overridden', 'excluded', 'timecreated', 'timemodified');

    /**
     * Array of optional fields with default values (these should match db defaults)
     * @var array $optional_fields
     */
    var $optional_fields = array('feedback'=>null, 'feedbackformat'=>0, 'information'=>null, 'informationformat'=>0);

    /**
     * The id of the grade_item this grade belongs to.
     * @var int $itemid
     */
    var $itemid;

    /**
     * The grade_item object referenced by $this->itemid.
     * @var object $grade_item
     */
    var $grade_item;

    /**
     * The id of the user this grade belongs to.
     * @var int $userid
     */
    var $userid;

    /**
     * The grade value of this raw grade, if such was provided by the module.
     * @var float $rawgrade
     */
    var $rawgrade;

    /**
     * The maximum allowable grade when this grade was created.
     * @var float $rawgrademax
     */
    var $rawgrademax = 100;

    /**
     * The minimum allowable grade when this grade was created.
     * @var float $rawgrademin
     */
    var $rawgrademin = 0;

    /**
     * id of the scale, if this grade is based on a scale.
     * @var int $rawscaleid
     */
    var $rawscaleid;

    /**
     * The userid of the person who last modified this grade.
     * @var int $usermodified
     */
    var $usermodified;

    /**
     * The final value of this grade.
     * @var float $finalgrade
     */
    var $finalgrade;

    /**
     * 0 if visible, 1 always hidden or date not visible until
     * @var float $hidden
     */
    var $hidden = 0;

    /**
     * 0 not locked, date when the item was locked
     * @var float locked
     */
    var $locked = 0;

    /**
     * 0 no automatic locking, date when to lock the grade automatically
     * @var float $locktime
     */
    var $locktime = 0;

    /**
     * Exported flag
     * @var boolean $exported
     */
    var $exported = 0;

    /**
     * Overridden flag
     * @var boolean $overridden
     */
    var $overridden = 0;

    /**
     * Grade excluded from aggregation functions
     * @var boolean $excluded
     */
    var $excluded = 0;


    /**
     * Returns array of grades for given grade_item+users.
     * @param object $grade_item
     * @param array $userids
     * @param bool $include_missing include grades taht do not exist yet
     * @return array userid=>grade_grade array
     */
    function fetch_users_grades($grade_item, $userids, $include_missing=true) {

        // hmm, there might be a problem with length of sql query
        // if there are too many users requested - we might run out of memory anyway
        $limit = 2000;
        $count = count($userids);
        if ($count > $limit) {
            $half = (int)($count/2);
            $first  = array_slice($userids, 0, $half);
            $second = array_slice($userids, $half);
            return grade_grade::fetch_users_grades($grade_item, $first, $include_missing) + grade_grade::fetch_users_grades($grade_item, $second, $include_missing);
        }

        $user_ids_cvs = implode(',', $userids);
        $result = array();
        if ($grade_records = get_records_select('grade_grades', "itemid={$grade_item->id} AND userid IN ($user_ids_cvs)")) {
            foreach ($grade_records as $record) {
                $result[$record->userid] = new grade_grade($record, false);
            }
        }
        if ($include_missing) {
            foreach ($userids as $userid) {
                if (!array_key_exists($userid, $result)) {
                    $grade_grade = new grade_grade();
                    $grade_grade->userid = $userid;
                    $grade_grade->itemid = $grade_item->id;
                    $result[$userid] = $grade_grade;
                }
            }
        }

        return $result;
    }

    /**
     * Loads the grade_item object referenced by $this->itemid and saves it as $this->grade_item for easy access.
     * @return object grade_item.
     */
    function load_grade_item() {
        if (empty($this->itemid)) {
            debugging('Missing itemid');
            $this->grade_item = null;
            return null;
        }

        if (empty($this->grade_item)) {
            $this->grade_item = grade_item::fetch(array('id'=>$this->itemid));

        } else if ($this->grade_item->id != $this->itemid) {
            debugging('Itemid mismatch');
            $this->grade_item = grade_item::fetch(array('id'=>$this->itemid));
        }

        return $this->grade_item;
    }

    /**
     * Is grading object editable?
     * @return boolean
     */
    function is_editable() {
        if ($this->is_locked()) {
            return false;
        }

        $grade_item = $this->load_grade_item();

        if ($grade_item->gradetype == GRADE_TYPE_NONE) {
            return false;
        }

        return true;
    }

    /**
     * Check grade lock status. Uses both grade item lock and grade lock.
     * Internally any date in locked field (including future ones) means locked,
     * the date is stored for logging purposes only.
     *
     * @return boolean true if locked, false if not
     */
    function is_locked() {
        $this->load_grade_item();

        return !empty($this->locked) or $this->grade_item->is_locked();
    }

    /**
     * Checks if grade overridden
     * @return boolean
     */
    function is_overridden() {
        return !empty($this->overridden);
    }

    /**
     * Set the overridden status of grade
     * @param boolean $state requested overridden state
     * @return boolean true is db state changed
     */
    function set_overridden($state) {
        if (empty($this->overridden) and $state) {
            $this->overridden = time();
            $this->update();
            return true;

        } else if (!empty($this->overridden) and !$state) {
            $this->overridden = 0;
            $this->update();
            return true;
        }
        return false;
    }

    /**
     * Checks if grade excluded from aggregation functions
     * @return boolean
     */
    function is_excluded() {
        return !empty($this->excluded);
    }

    /**
     * Set the excluded status of grade
     * @param boolean $state requested excluded state
     * @return boolean true is db state changed
     */
    function set_excluded($state) {
        if (empty($this->excluded) and $state) {
            $this->excluded = time();
            $this->update();
            return true;

        } else if (!empty($this->excluded) and !$state) {
            $this->excluded = 0;
            $this->update();
            return true;
        }
        return false;
    }

    /**
     * Lock/unlock this grade.
     *
     * @param int $locked 0, 1 or a timestamp int(10) after which date the item will be locked.
     * @param boolean $cascade ignored param
     * @param boolean $refresh refresh grades when unlocking
     * @return boolean true if sucessful, false if can not set new lock state for grade
     */
    function set_locked($lockedstate, $cascade=false, $refresh=true) {
        $this->load_grade_item();

        if ($lockedstate) {
            if ($this->grade_item->needsupdate) {
                //can not lock grade if final not calculated!
                return false;
            }

            $this->locked = time();
            $this->update();

            return true;

        } else {
            if (!empty($this->locked) and $this->locktime < time()) {
                //we have to reset locktime or else it would lock up again
                $this->locktime = 0;
            }

            // remove the locked flag
            $this->locked = 0;
            $this->update();

            if ($refresh) {
                //refresh when unlocking
                $this->grade_item->refresh_grades($this->userid);
            }

            return true;
        }
    }

    /**
     * Lock the grade if needed - make sure this is called only when final grades are valid
     * @param array $items array of all grade item ids
     * @return void
     */
    function check_locktime_all($items) {
        global $CFG;

        $items_sql = implode(',', $items);

        $now = time(); // no rounding needed, this is not supposed to be called every 10 seconds

        if ($rs = get_recordset_select('grade_grades', "itemid IN ($items_sql) AND locked = 0 AND locktime > 0 AND locktime < $now")) {
            if ($rs->RecordCount() > 0) {
                while ($grade = rs_fetch_next_record($rs)) {
                    $grade_grade = new grade_grade($grade, false);
                    $grade_grade->locked = time();
                    $grade_grade->update('locktime');
                }
            }
            rs_close($rs);
        }
    }

    /**
     * Set the locktime for this grade.
     *
     * @param int $locktime timestamp for lock to activate
     * @return void
     */
    function set_locktime($locktime) {
        $this->locktime = $locktime;
        $this->update();
    }

    /**
     * Set the locktime for this grade.
     *
     * @return int $locktime timestamp for lock to activate
     */
    function get_locktime() {
        $this->load_grade_item();

        $item_locktime = $this->grade_item->get_locktime();

        if (empty($this->locktime) or ($item_locktime and $item_locktime < $this->locktime)) {
            return $item_locktime;

        } else {
            return $this->locktime;
        }
    }

    /**
     * Check grade hidden status. Uses data from both grade item and grade.
     * @return boolean true if hidden, false if not
     */
    function is_hidden() {
        $this->load_grade_item();

        return $this->hidden == 1 or ($this->hidden != 0 and $this->hidden > time()) or $this->grade_item->is_hidden();
    }

    /**
     * Check grade hidden status. Uses data from both grade item and grade.
     * @return int 0 means visible, 1 hidden always, timestamp hidden until
     */
    function get_hidden() {
        $this->load_grade_item();

        $item_hidden = $this->grade_item->get_hidden();

        if ($item_hidden == 1) {
            return 1;

        } else if ($item_hidden == 0) {
            return $this->hidden;

        } else {
            if ($this->hidden == 0) {
                return $item_hidden;
            } else if ($this->hidden == 1) {
                return 1;
            } else if ($this->hidden > $item_hidden) {
                return $this->hidden;
            } else {
                return $item_hidden;
            }
        }
    }

    /**
     * Set the hidden status of grade, 0 mean visible, 1 always hidden, number means date to hide until.
     * @param boolean $cascade ignored
     * @param int $hidden new hidden status
     */
    function set_hidden($hidden, $cascade=false) {
       $this->hidden = $hidden;
       $this->update();
    }

    /**
     * Finds and returns a grade_grade instance based on params.
     * @static
     *
     * @param array $params associative arrays varname=>value
     * @return object grade_grade instance or false if none found.
     */
    function fetch($params) {
        return grade_object::fetch_helper('grade_grades', 'grade_grade', $params);
    }

    /**
     * Finds and returns all grade_grade instances based on params.
     * @static
     *
     * @param array $params associative arrays varname=>value
     * @return array array of grade_grade insatnces or false if none found.
     */
    function fetch_all($params) {
        return grade_object::fetch_all_helper('grade_grades', 'grade_grade', $params);
    }

    /**
     * Given a float value situated between a source minimum and a source maximum, converts it to the
     * corresponding value situated between a target minimum and a target maximum. Thanks to Darlene
     * for the formula :-)
     *
     * @static
     * @param float $rawgrade
     * @param float $source_min
     * @param float $source_max
     * @param float $target_min
     * @param float $target_max
     * @return float Converted value
     */
    function standardise_score($rawgrade, $source_min, $source_max, $target_min, $target_max) {
        if (is_null($rawgrade)) {
          return null;
        }

        $factor = ($rawgrade - $source_min) / ($source_max - $source_min);
        $diff = $target_max - $target_min;
        $standardised_value = $factor * $diff + $target_min;
        return $standardised_value;
    }
}
?>
