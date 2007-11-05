<?php //$Id: field.class.php,v 1.1 2007/04/28 03:07:43 ikawhero Exp $

class profile_field_textarea extends profile_field_base {

    function edit_field_add(&$mform) {
        $cols = $this->field->param1;
        $rows = $this->field->param2;

        /// Create the form field
        $mform->addElement('htmleditor', $this->inputname, format_string($this->field->name), array('cols'=>$cols, 'rows'=>$rows));
        $mform->setType($this->inputname, PARAM_CLEAN);
    }

}

?>
