<?php // $Id: version.php,v 1.128.2.1 2007/11/02 16:19:54 tjhunt Exp $

////////////////////////////////////////////////////////////////////////////////
//  Code fragment to define the version of quiz
//  This fragment is called by moodle_needs_upgrading() and /admin/index.php
////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007072600;   // The (date) version of this module
$module->requires = 2007101000;   // Requires this Moodle version
$module->cron     = 0;            // How often should cron check this module (seconds)?

?>
