<?php  //$Id: version.php,v 1.113 2007/09/05 23:12:31 stronk7 Exp $

/// This file defines the current version of the
/// backup/restore code that is being used.  This can be
/// compared against the values stored in the 
/// database (backup_version) to determine whether upgrades should
/// be performed (see db/backup_*.php)

    $backup_version = 2007090500;   // The current version is a date (YYYYMMDDXX)
    $backup_release = '1.9 beta +';     // User-friendly version number

?>
