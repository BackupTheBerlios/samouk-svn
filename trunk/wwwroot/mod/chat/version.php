<?php // $Id: version.php,v 1.26 2007/10/10 16:09:45 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of chat
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007020200;   // The (date) version of this module
$module->requires = 2007101000;  // Requires this Moodle version
$module->cron     = 300;          // How often should cron check this module (seconds)?

?>
