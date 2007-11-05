<?php  // $Id: access.php,v 1.3 2007/10/07 13:18:06 moodler Exp $

$gradeimport_xml_capabilities = array(

    'gradeimport/xml:view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'gradeimport/xml:publish' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    )
);

?>
