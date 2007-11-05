<?php  // $Id: access.php,v 1.3 2007/10/07 13:18:05 moodler Exp $

$gradeimport_csv_capabilities = array(

    'gradeimport/csv:view' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    )
);

?>
