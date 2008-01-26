<?php // $Id: version.php,v 1.17 2007/10/10 16:09:54 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of label
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007020200;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2007101000;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>
