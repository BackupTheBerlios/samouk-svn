<?php //$Id: index.php,v 1.15 2007/07/23 22:20:08 skodak Exp $

/*
 * Compatibility redirection to reports
 */

require '../config.php';

$id = required_param('id', PARAM_INT);
redirect('report/index.php?id='.$id);

?>