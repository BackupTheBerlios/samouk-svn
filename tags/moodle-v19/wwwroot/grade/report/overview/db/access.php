<?php  // $Id: access.php,v 1.2 2007/08/10 14:58:21 nicolasconnault Exp $

$gradereport_overview_capabilities = array(

    'gradereport/overview:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    )

);

?>
