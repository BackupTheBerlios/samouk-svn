<?php // $Id: version.php,v 1.25 2007/02/02 13:02:26 moodler Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of chat
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007020200;   // The (date) version of this module
$module->requires = 2007020200;  // Requires this Moodle version
$module->cron     = 300;          // How often should cron check this module (seconds)?

?>
