<?php // $Id: version.php,v 1.127 2007/08/09 21:52:35 jamiesensei Exp $

////////////////////////////////////////////////////////////////////////////////
//  Code fragment to define the version of quiz
//  This fragment is called by moodle_needs_upgrading() and /admin/index.php
////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007072600;   // The (date) version of this module
$module->requires = 2007072200;   // Requires this Moodle version
$module->cron     = 0;            // How often should cron check this module (seconds)?

?>
