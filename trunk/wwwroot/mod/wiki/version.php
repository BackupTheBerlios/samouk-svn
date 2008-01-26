<?PHP // $Id: version.php,v 1.29 2007/10/10 16:09:30 skodak Exp $

/////////////////////////////////////////////////////////////////////////////////
///  Code fragment to define the version of Wiki
///  This fragment is called by moodle_needs_upgrading() and /admin/index.php
/////////////////////////////////////////////////////////////////////////////////

$module->version  = 2007020200;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2007101000;  // The current module version (Date: YYYYMMDDXX)
$module->cron     = 3600;        // Period for cron to check this module (secs)

?>
