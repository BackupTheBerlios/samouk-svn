<?php // $Id: lib.php,v 1.1 2004/08/21 10:12:08 julmis Exp $

function speller_enable (&$CFG) {
    echo "function spellClickHandler(editor, buttonId) {\n";
    echo "\teditor._textArea.value = editor.getHTML();\n";
    echo "\tvar speller = new spellChecker( editor._textArea );\n";
    echo "\tspeller.popUpUrl = \"" . $CFG->wwwroot ."/lib/speller/spellchecker.html\";\n";
    echo "\tspeller.spellCheckScript = \"". $CFG->wwwroot ."/lib/speller/server-scripts/spellchecker.php?id=$courseid\";\n";
    echo "\tspeller._moogle_edit=1;\n";
    echo "\tspeller._editor=editor;\n";
    echo "\tspeller.openChecker();\n";
    echo "}\n";
    echo "config.registerButton(\"spell-check\",  \"spell-check\", \"". $CFG->wwwroot ."/lib/speller/spell.gif\", false, spellClickHandler);\n";
    echo "config.toolbar.push([\"spell-check\"]);\n";
}

?>