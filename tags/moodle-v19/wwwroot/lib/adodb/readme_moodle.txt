Description of ADODB v4.95a library import into Moodle

Removed:
 * contrib/
 * cute_icons_for_site/
 * docs/
 * pear/
 * tests/
 * server.php

Added:
 * index.html - prevent directory browsing on misconfigured servers
 * tests/tmssql.php - detection of attack attempts and overwrite on incorrectly upgraded sites
 * readme_moodle.txt - this file ;-)

Our changes:
 * adodb-lib.inc.php - forced conversion to proper numeric type in _adodb_column_sql()
 * drivers/adodb-mssql_n.inc.php - Fixed one bug in the N' parser when one value start by '
        Once fixed by adodb guys, we'll return to their official distro.
 * drivers/adodb-odbc_mssql.inc.php - Fixed one buggy function (ServerInfo) that was not
       working properly. Simplified logic (now that we are FETCH_ASSOC). Work in progress
       for the annoying http://tracker.moodle.org/browse/MDL-6877.
       Once fixed by adodb guys, we'll return to their official distro.

skodak

$Id: readme_moodle.txt,v 1.13 2007/08/10 00:48:00 stronk7 Exp $
