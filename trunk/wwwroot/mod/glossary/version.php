<?php // $Id: version.php,v 1.60 2007/10/10 16:09:28 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of glossary
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007072200;
$module->requires = 2007101000;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>
