<?php  // $Id: access.php,v 1.4 2007/07/24 09:20:44 skodak Exp $

$gradereport_user_capabilities = array(

    'gradereport/user:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),
);

?>
