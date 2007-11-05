<?php // $Id: version.php,v 1.59 2007/07/22 21:43:50 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of glossary
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007072200;
$module->requires = 2007072200;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>
