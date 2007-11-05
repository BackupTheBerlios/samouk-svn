<?php  // $Id: access.php,v 1.2 2007/09/27 06:51:56 skodak Exp $

$gradeexport_txt_capabilities = array(

    'gradeexport/txt:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'gradeexport/txt:publish' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    )

);

?>
