Description of ADODB v4.96a library import into Moodle

Removed:
 * contrib/
 * cute_icons_for_site/
 * docs/
 * pear/
 * tests/
 * server.php

Added:
 * index.html - prevent directory browsing on misconfigured servers
 * readme_moodle.txt - this file ;-)

Our changes:
 * adodb-lib.inc.php - forced conversion to proper numeric type in _adodb_column_sql()
 * adodb-lib.inc.php - modify some debug output to be correct XHTML. MDL-12378.
       Reported to ADOdb at: http://phplens.com/lens/lensforum/msgs.php?id=17133
       Once fixed by adodb guys, we'll return to their official distro.
 * lang/adodb-ar.inc.php lang/adodb-bg.inc.php lang/adodb-bgutf8.inc.php 
   lang/adodb-en.inc.php lang/adodb-pl.inc.php lang/adodb-ro.inc.php
   lang/adodb_th.inc.php - Removed leading white space outside PHP open/close tags
   (see http://tracker.moodle.org/browse/MDL-11632).


skodak, iarenaza

$Id: readme_moodle.txt,v 1.14.2.2 2008/01/02 18:37:50 skodak Exp $
