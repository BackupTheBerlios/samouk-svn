<?php  //$Id: lib.php,v 1.1.2.2 2007/11/13 09:02:12 skodak Exp $

require_once($CFG->dirroot.'/user/filters/lib.php');

if (!defined('MAX_BULK_USERS')) {
    define('MAX_BULK_USERS', 2000);
}

function add_selection_all($ufiltering) {
    global $SESSION;

    $guest = get_guest();
    $sqlwhere = $ufiltering->get_sql_filter("id<>{$guest->id} AND deleted <> 1");

    if ($rs = get_recordset_select('user', $sqlwhere, 'fullname', 'id,'.sql_fullname().' AS fullname')) {
        while ($user = rs_fetch_next_record($rs)) {
            if (!in_array($user->id, $SESSION->bulk_users)) {
                $SESSION->bulk_users[] = $user->id;
            }
        }
        rs_close($rs);
    }
}

function get_selection_data($ufiltering) {
    global $SESSION;

    // get the SQL filter
    $guest = get_guest();
    $sqlwhere = $ufiltering->get_sql_filter("id<>{$guest->id} AND deleted <> 1");

    $total  = count_records_select('user', "id<>{$guest->id} AND deleted <> 1");
    $acount = count_records_select('user', $sqlwhere);
    $scount = count($SESSION->bulk_users);

    $userlist = array('acount'=>$acount, 'scount'=>$scount, 'ausers'=>false, 'susers'=>false, 'total'=>$total);
    $userlist['ausers'] = get_records_select_menu('user', $sqlwhere, 'fullname', 'id,'.sql_fullname().' AS fullname', 0, MAX_BULK_USERS);

    if ($scount) {
        if ($scount < MAX_BULK_USERS) {
            $in = implode(',', $SESSION->bulk_users);
        } else {
            $bulkusers = array_slice($SESSION->bulk_users, 0, MAX_BULK_USERS);
            $in = implode(',', $bulkusers);
        }
        $userlist['susers'] = get_records_select_menu('user', "id IN ($in)", 'fullname', 'id,'.sql_fullname().' AS fullname');
    }

    return $userlist;
}


?>
