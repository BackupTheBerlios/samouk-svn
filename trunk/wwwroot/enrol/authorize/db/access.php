<?php // $Id: access.php,v 1.6 2007/03/05 11:27:19 skodak Exp $

$enrol_authorize_capabilities = array(

    'enrol/authorize:managepayments' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),

    'enrol/authorize:uploadcsv' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    )

);

?>
