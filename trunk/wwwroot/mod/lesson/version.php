<?php // $Id: version.php,v 1.38 2007/07/22 21:43:47 skodak Exp $
/**
 * Code fragment to define the version of lesson
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @version $Id: version.php,v 1.38 2007/07/22 21:43:47 skodak Exp $
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package lesson
 **/

$module->version  = 2007072200;  // The current module version (Date: YYYYMMDDXX)
$module->requires = 2007072200;  // Requires this Moodle version
$module->cron     = 0;           // Period for cron to check this module (secs)

?>
