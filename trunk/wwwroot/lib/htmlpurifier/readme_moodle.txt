Description of HTML Purifier v2.1.3 Lite library import into Moodle

Changes:
 * HMLTModule/Text.php - added  <nolink>, <tex>, <lang> and <algebra> tags
 * HMLTModule/XMLCommonAttributes.php - remove xml:lang - needed for multilang
 * AttrDef/Lang.php - relax lang check - needed for multilang
 * AttrDef/URI/Email/SimpleCheck.php - deleted to prevent errors on some systems, not used anyway

skodak

$Id: readme_moodle.txt,v 1.4.2.2 2007/12/25 21:05:33 skodak Exp $
