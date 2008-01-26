<?php
/// Please, do not edit this file manually! It's auto generated from
/// contents stored in your standard lang pack files:
/// (langconfig.php, install.php, moodle.php, admin.php and error.php)
///
/// If you find some missing string in Moodle installation, please,
/// keep us informed using http://moodle.org/bugs Thanks!
///
/// File generated by cvs://contrib/lang2installer/installer_builder
/// using strings defined in stringnames.txt (same dir)

$string['admindirerror'] = 'El directori d\'administració especificat és incorrecte';
$string['admindirname'] = 'Directori d\'administració';
$string['admindirsettinghead'] = 'S\'està configurant el directori d\'administració...';
$string['admindirsettingsub'] = 'Alguns proveïdors d\'espai web utilitzen /admin com una adreça URL especial per accedir a un tauler de control o quelcom semblant. Malauradament això interfereix amb la ubicació estàndard de les pàgines d\'administració de Moodle. Podeu solucionar aquest problema canviant el nom del directori admin en la vostra instal·lació i introduint aquí el nou nom. Per exemple: <br /> <br /><b>moodleadmin</b><br /> <br />
Això repararà tots els enllaços d\'administració de Moodle.';
$string['bypassed'] = 'S\'ha deixat de banda';
$string['cannotcreatelangdir'] = 'No s\'ha pogut crear el directori d\'idiomes.';
$string['cannotcreatetempdir'] = 'No s\'ha pogut crear el directori temporal';
$string['cannotdownloadcomponents'] = 'No s\'han pogut baixar components';
$string['cannotdownloadzipfile'] = 'No s\'ha pogut baixar el fitxer zip';
$string['cannotfindcomponent'] = 'No s\'ha pogut trobar un component';
$string['cannotsavemd5file'] = 'No s\'ha pogut desar el fitxer md5';
$string['cannotsavezipfile'] = 'No s\'ha pogut desar el fitxer zip';
$string['cannotunzipfile'] = 'No s\'ha pogut descomprimir el fitxer';
$string['caution'] = 'Alerta';
$string['check'] = 'Reviseu';
$string['chooselanguagehead'] = 'Trieu un idioma';
$string['chooselanguagesub'] = 'Trieu un idioma NOMÉS per a la instal·lació. En una pantalla posterior podreu triar idiomes per al lloc i per als usuaris.';
$string['closewindow'] = 'Tanca aquesta finestra';
$string['compatibilitysettingshead'] = 'S\'estan comprovant els paràmetres del PHP...';
$string['compatibilitysettingssub'] = 'El vostre servidor hauria de passar totes aquestes proves per tal que Moodle funcioni correctament.';
$string['componentisuptodate'] = 'El component està al dia';
$string['configfilenotwritten'] = 'La seqüència d\'instal·lació no ha estat capaç de crear automàticament un fitxer config.php que contingui els paràmetres que heu triat, probablement perquè no pugui escriure al directori de Moodle. Podeu copiar a mà el codi següent en un fitxer anomenat config.php dins del directori arrel de Moodle.';
$string['configfilewritten'] = 'S\'ha creat amb èxit el fitxer config.php';
$string['configurationcompletehead'] = 'S\'ha completat la configuració';
$string['configurationcompletesub'] = 'Moodle ha intentat desar la configuració en un fitxer en l\'arrel de la vostra instal·lació de Moodle.';
$string['continue'] = 'Continua';
$string['curlrecommended'] = 'Es recomana instal·lar la biblioteca opcional Curl a fi d\'habilitar la funcionalitat de Moodle en Xarxa.';
$string['customcheck'] = 'Altres comprovacions';
$string['database'] = 'Base de dades';
$string['databasecreationsettingshead'] = 'Ara cal configurar els paràmetres de la base de dades on s\'emmagatzemaran la majoria de dades de Moodle. L\'instal·lador crearà automàticament aquesta base de dades amb els paràmetres que especifiqueu aquí.';
$string['databasecreationsettingssub'] = '<b>Tipus:</b> fixat a \"mysql\" per l\'instal·lador<br />
<b>Servidor:</b> fixat a \"localhost\" per l\'instal·lador<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> fixat a \"root\" per l\'instal·lador<br />
<b>Contrasenya:</b> la contrasenya de l\'usuari \"root\" de la base de dades<br />
<b>Prefix de les taules:</b> prefix opcional per utilitzar en els noms de totes les taules';
$string['databasesettingshead'] = 'Ara cal configurar la base de dades on s\'emmagatzemaran la majoria de dades de Moodle. Aquesta base de dades s\'ha d\'haver crear abans i cal tenir un nom d\'usuari i una contrasenya per accedir-hi.';
$string['databasesettingssub'] = '<b>Tipus:</b> mysql o postgres7<br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> nom de l\'usuari de la base de dades<br />
<b>Contrasenya:</b> contrasenya de l\'usuari de la base de dades<br />
<b>Prefix de les taules:</b> prefix opcional per utilitzar en els noms de totes les taules';
$string['databasesettingssub_mssql'] = '<b>Tipus:</b> SQL*Server (no UTF-8) <b><font color=\"red\">Experimental! (no per a utilitzar en producció)</font></b><br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (obligatori)';
$string['databasesettingssub_mssql_n'] = '<b>Tipus:</b> SQL*Server (UTF-8 habilitat)<br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (obligatori)';
$string['databasesettingssub_mysql'] = '<b>Tipus:</b> MySQL<br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (opcional)';
$string['databasesettingssub_mysqli'] = '<b>Tipus:</b> MySQL Improved<br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (opcional)';
$string['databasesettingssub_oci8po'] = '<b>Tipus:</b> Oracle<br />
<b>Servidor:</b> no utilitzat, s\'ha de deixar en blanc<br />
<b>Nom:</b> nom de la connexió tnsnames.ora<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (obligatori, màxim 2 caràcters)';
$string['databasesettingssub_odbc_mssql'] = '<b>Tipus:</b> SQL*Server (sobre ODBC) <b><font color=\"red\">Experimental! (no per a utilitzar en producció)</font></b><br />
<b>Servidor:</b> nom del DSN al tauler de control de l\'ODBC<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (obligatori)';
$string['databasesettingssub_postgres7'] = '<b>Tipus:</b> PostgreSQL<br />
<b>Servidor:</b> p. ex. localhost o db.isp.com<br />
<b>Nom:</b> nom de la base de dades, p. ex. moodle<br />
<b>Usuari:</b> el vostre nom d\'usuari de la base de dades<br />
<b>Contrasenya:</b> la vostra contrasenya de la base de dades<br />
<b>Prefix de les taules:</b> prefix que cal anteposar als noms de totes les taules (obligatori)';
$string['dataroot'] = 'Directori de dades';
$string['datarooterror'] = 'No s\'ha pogut trobar o crear el directori de dades que heu especificat. Corregiu el camí o creeu el directori a mà.';
$string['dbconnectionerror'] = 'No es pot obrir la connexió amb la base de dades que heu especificat. Comproveu els paràmetres de la base de dades.';
$string['dbcreationerror'] = 'Error en la creació de la base de dades. No s\'ha pogut crear la base de dades amb els paràmetres proporcionats.';
$string['dbhost'] = 'Ordinador servidor';
$string['dbprefix'] = 'Prefix de taules';
$string['dbtype'] = 'Tipus';
$string['dbwrongencoding'] = 'La base de dades que heu seleccionat està funcionant amb una codificació ($a) no recomanada. Seria millor utilitzar una base de dades amb codificació Unicode (UTF-8). De totes maneres, podeu deixar de banda aquesta prova si seleccioneu més avall \"Omet la prova de codificació de la base de dades\", però podríeu experimentar problemes en el futur.';
$string['dbwronghostserver'] = 'Heu de seguir les regles referents al servidor, exposades més amunt.';
$string['dbwrongnlslang'] = 'La variable d\'entorn NLS_LANG del vostre servidor web ha d\'utilitzar el joc de caràcters AL32UTF8. Consulteu la documentació de PHP sobre la configuració correcta d\'OCI8.';
$string['dbwrongprefix'] = 'Heu de seguir les regles referents al prefix de les taules, exposades més amunt.';
$string['directorysettingshead'] = 'Confirmeu les ubicacions d\'aquesta instal·lació de Moodle.';
$string['directorysettingssub'] = '<b>Adreça web:</b>
Especifiqueu l\'adreça web completa per a accedir a Moodle. Si el vostre lloc és accessible per mitjà de diversos URL, trieu el més natural per als estudiants. No inclogueu la barra final.</p>
<br /><br />
<b>Directori de Moodle:</b>
Especifiqueu el camí complet del directori d\'aquesta instal·lació. Assegureu-vos que les majúscules/minúscules són correctes.</p>
<br /><br />
<b>Directori de dades:</b>
Necessiteu un lloc on Moodle pugui desar els fitxers que es pengin. L\'usuari del servidor web (generalment \'nobody\' o \'apache\') ha de tenir permisos de lectura i d\'ESCRIPTURA en aquest directori, però no hauria de ser accessible directament via web.</p>';
$string['dirroot'] = 'Directori de Moodle';
$string['dirrooterror'] = 'El paràmetre \'Directori de Moodle\' sembla incorrecte: no s\'hi ha pogut trobat cap instal·lació de Moodle. S\'ha reiniciat el valor del paràmetre.';
$string['download'] = 'Baixa';
$string['downloadedfilecheckfailed'] = 'Ha fallat la comprovació del fitxer baixat';
$string['downloadlanguagebutton'] = 'Baixa el paquet d\'idioma \"$a\"';
$string['downloadlanguagehead'] = 'Baixa paquet d\'idioma';
$string['downloadlanguagenotneeded'] = 'Podeu continuar el procés d\'instal·lació amb el paquet d\'idioma per defecte \"$a\".';
$string['downloadlanguagesub'] = 'Ara teniu l\'opció de baixar un paquet d\'idioma i continuar el procés d\'instal·lació en aquest idioma.<br /><br />Si no podeu baixar el paquet, el procés d\'instal·lació prosseguirà en anglès. (Després que s\'hagi completat la instal·lació, tindreu l\'oportunitat de baixar i instal·lar paquets d\'idioma addicionals.)';
$string['environmenterrortodo'] = 'Abans d\'instal·lar aquesta versió de Moodle heu de resoldre tots els problemes d\'entorn (errors) que s\'han trobat.';
$string['environmenthead'] = 'S\'està comprovant el vostre entorn';
$string['environmentrecommendcustomcheck'] = 'si aquesta prova falla, això indica un problema en potència';
$string['environmentrecommendinstall'] = 'es recomana instal·lar/habilitar';
$string['environmentrecommendversion'] = 'esteu executant la versió $a->current i es recomana la $a->needed';
$string['environmentrequirecustomcheck'] = 'cal passar aquesta prova';
$string['environmentrequireinstall'] = 'es requereix instal·lar/habilitar';
$string['environmentrequireversion'] = 'esteu executant la versió $a->current i es requereix la $a->needed';
$string['environmentsub'] = 'S\'està comprovant si els diferents components del vostre sistema satisfan els requeriments';
$string['environmentxmlerror'] = 'S\'ha produït un error en llegir les dades de l\'entorn ($a->error_code)';
$string['error'] = 'Error';
$string['fail'] = 'Error';
$string['fileuploads'] = 'Càrrega de fitxers';
$string['fileuploadserror'] = 'Hauria d\'estar habilitada';
$string['gdversion'] = 'Versió GD';
$string['gdversionerror'] = 'La biblioteca GD hauria d\'estar present per processar i crear imatges';
$string['gdversionhelp'] = '<p>Sembla que el vostre servidor no té instal·lat el GD.</p>

<p>GD és una biblioteca requerida pel PHP per tal que Moodle pugui processar imatges (p. ex. les icones dels perfils d\'usuari) i crear imatges noves (p. ex. els gràfics dels registres d\'activitat). Moodle pot funcionar sense GD, però aquestes característiques no estaran disponibles.</p>

<p>Per afegir GD al PHP en Unix, compileu el PHP amb el paràmetre --with-gd.</p>

<p>En Windows generalment es pot editar el fitxer php.ini i treure el comentari de la línia que porti la referència a php_gd2.dll.</p>';
$string['globalsquotes'] = 'Gestió insegura dels globals';
$string['globalsquoteserror'] = 'Arregleu els paràmetres del vostre PHP: inhabiliteu register_globals i/o habiliteu magic_quotes_gpc';
$string['help'] = 'Ajuda';
$string['iconvrecommended'] = 'És sumament recomanable instal·lar la biblioteca opcional ICONV a fi de millorar el rendiment del lloc, especialment si el vostre lloc utilitza llengües no romàniques.';
$string['info'] = 'Informació';
$string['installation'] = 'Instal·lació';
$string['invalidmd5'] = 'El md5 no és vàlid';
$string['langdownloaderror'] = 'Dissortadament l\'idioma \"$a\" no està instal·lat. La instal·lació prosseguirà en anglès.';
$string['langdownloadok'] = 'L\'dioma \"$a\" s\'ha instal·lat amb èxit. La instal·lació prosseguirà en aquest idioma.';
$string['language'] = 'Idioma';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'Hauria d\'estar desactivat';
$string['mbstringrecommended'] = 'És sumament recomanable instal·lar la biblioteca opcional MBSTRING a fi de millorar el rendiment del lloc, especialment si el vostre lloc utilitza llengües no romàniques.';
$string['memorylimit'] = 'Límit de memòria';
$string['memorylimiterror'] = 'El límit de memòria del PHP està definit una mica baix. Podeu tenir problemes més endavant.';
$string['memorylimithelp'] = '<p>El límit de memòria del PHP del vostre servidor actualment està definit en $a.</p>

<p>Això pot causar que Moodle tingui problemes de memòria més endavant, especialment si teniu molts mòduls habilitats i/o molts usuaris.</p>

<p>És recomanable que configureu el PHP amb un límit superior, com ara 40 MB, sempre que sigui possible. Hi ha diverses maneres de fer això:</p>
<ol>
<li>Si podeu, recompileu el PHP amb <i>--enable-memory-limit</i>. Això permetrà que Moodle defineixi el límit de memòria per si mateix.</li>
<li>Si teniu accés al fitxer php.ini, podeu canviar el paràmetre <b>memory_limit</b> a 40 MB. Si no hi teniu accés podeu demanar al vostre administrador que ho faci ell.</li>
<li>En alguns servidors PHP podeu crear un fitxer .htaccess dins del directori de Moodle amb aquesta línia:
<p><blockquote>php_value memory_limit 40M</blockquote></p>
<p>Tanmateix, en alguns servidors això farà que no funcioni <b>cap</b> pàgina PHP (es visualitzaran errors) en el qual cas hauríeu de suprimir el fitxer .htaccess.</p></li>
</ol>';
$string['missingrequiredfield'] = 'Falta algun camp necessari';
$string['moodledocslink'] = 'Documentació de Moodle per a aquesta pàgina';
$string['mssql'] = 'SQL*Server (mssql)';
$string['mssql_n'] = 'SQL*Server amb UTF-8 (mssql_n)';
$string['mssqlextensionisnotpresentinphp'] = 'El PHP no s\'ha configurat correctament amb l\'extensió MSSQL de manera que pugui comunicar-se amb SQL*Server. Reviseu el fitxer php.ini o recompileu PHP.';
$string['mysql'] = 'MySQL (mysql)';
$string['mysql416bypassed'] = 'Tanmateix, si el vostre lloc fa servir NOMÉS llengües romàniques (iso-8859-1), podeu seguir utilitzant el MySQL 4.1.12 (o superior) instal·lat.';
$string['mysql416required'] = 'MySQL 4.1.16 és la versió mínima requerida per Moodle 1.6 a fi de garantir la conversió de totes les dades a UTF-8 en el futur.';
$string['mysqlextensionisnotpresentinphp'] = 'El PHP no s\'ha configurat correctament amb l\'extensió MySQL de manera que pugui comunicar-se amb MySQL. Reviseu el fitxer php.ini o recompileu el PHP.';
$string['mysqli'] = 'MySQL Improved (mysqli)';
$string['mysqliextensionisnotpresentinphp'] = 'El PHP no ha estat configurat adequadament amb l\'extensió MySQLi de manera que pugui comunicar-se amb MySQL. Reviseu el fitxer php.ini o recompileu el PHP. L\'extensió MySQLi no està disponible per a PHP 4.';
$string['name'] = 'Nom';
$string['next'] = 'Següent';
$string['oci8po'] = 'Oracle (oci8po)';
$string['ociextensionisnotpresentinphp'] = 'El PHP no s\'ha configurat correctament amb l\'extensió OCI8 de manera que pugui comunicar-se amb Oracle. Reviseu el fitxer php.ini o recompileu el PHP.';
$string['odbc_mssql'] = 'SQL*Server over ODBC (odbc_mssql)';
$string['odbcextensionisnotpresentinphp'] = 'El PHP no s\'ha configurat correctament amb l\'extensió ODBC de manera que pugui comunicar-se amb Oracle. Reviseu el fitxer php.ini o recompileu el PHP.';
$string['ok'] = 'OK';
$string['opensslrecommended'] = 'Es recomana instal·lar la biblioteca OpenSSL per habilitar la funcionalitat de Moodle en Xarxa.';
$string['pass'] = 'Correcte';
$string['password'] = 'Contrasenya';
$string['pgsqlextensionisnotpresentinphp'] = 'El PHP no s\'ha configurat correctament amb l\'extensió PGSQL de manera que pugui comunicar-se amb PostgreSQL. Reviseu el fitxer php.ini o recompileu el PHP.';
$string['php50restricted'] = 'PHP 5.0 té alguns problemes coneguts. Actualitzeu-vos si us plau a 5.1.x o torneu a 4.3.x o 4.4.x';
$string['phpversion'] = 'Versió PHP';
$string['phpversionerror'] = 'La versió del PHP ha de ser com a mínim la 4.1.0';
$string['phpversionhelp'] = '<p>Moodle necessita la versió de PHP 4.1.0 o posterior.</p>
<p>A hores d\'ara esteu utilitzant la versió $a.</p>
<p>Us caldrà actualitzar el PHP o traslladar Moodle a un ordinador amb una versió de PHP més recent.</p>';
$string['postgres7'] = 'PostgreSQL (postgres7)';
$string['previous'] = 'Anterior';
$string['qtyperqpwillberemoved'] = 'Durant l\'actualització, el tipus de pregunta RPQ se suprimirà. No estàveu utilitzant aquest tipus de pregunta, de manera que no hauríeu d\'experimentar cap problema.';
$string['qtyperqpwillberemovedanyway'] = 'Durant l\'actualització, el tipus de pregunta RPQ se suprimirà. Teniu algunes preguntes RQP en la base de dades que deixaran de funcionar si no reinstal·leu el codi de http://moodle.org/mod/data/view.php?d=13&amp;rid=797 abans de prosseguir l\'actualització.';
$string['remotedownloadnotallowed'] = 'El vostre servidor no permet baixar components ((allow_url_fopen inhabilitat).<br /><br />Baixeu manualment el fitxer <a href=\"$a->url\">$a->url</a>, copieu en la ubicació \"$a->dest\" del vostre servidor i descomprimiu-lo allí.';
$string['report'] = 'Informe';
$string['restricted'] = 'Restringit';
$string['safemode'] = 'Mode segur';
$string['safemodeerror'] = 'Moodle pot tenir problemes amb el mode segur activat';
$string['serverchecks'] = 'Proves del servidor';
$string['sessionautostart'] = 'Autoinici de sessió';
$string['sessionautostarterror'] = 'Hauria d\'estar desactivat';
$string['skipdbencodingtest'] = 'Omet la prova de codificació de la base de dades';
$string['status'] = 'Estat';
$string['thischarset'] = 'UTF-8';
$string['thisdirection'] = 'ltr';
$string['thislanguage'] = 'Català';
$string['unicoderecommended'] = 'Es recomana emmagatzemar totes les dades en Unicode (UTF-8). Les noves instal·lacions s\'haurien de realitzar en bases de dades que tinguin definit com a Unicode el conjunt de caràcters per defecte. Si esteu fent una actualització, hauríeu de passar el procés de migració a UTF-8 (vg. la pàgina d\'Administració).';
$string['unicoderequired'] = 'Es requereix emmagatzemar totes les dades en format Unicode (UTF-8). Les noves instal·lacions s\'han de realitzar en bases de dades que tinguin definit com a Unicode el conjunt de caràcters per defecte. Si esteu fent una actualització, hauríeu de passar el procés de migració a UTF-8 (vg. la pàgina d\'Administració).';
$string['user'] = 'Usuari';
$string['welcomep10'] = '$a->installername ($a->installerversion)';
$string['welcomep20'] = 'Esteu veient aquesta pàgina perquè heu instal·lat amb èxit i heu executat el paquet <strong>$a->packname $a->packversion</strong>. Felicitacions!';
$string['welcomep30'] = 'Aquesta versió de <strong>$a->installername</strong> inclou les aplicacions necessàries per crear un entorn en el qual funcioni <strong>Moodle</strong>:';
$string['welcomep40'] = 'El paquet inclou també <strong>Moodle $a->moodlerelease ($a->moodleversion)</strong>.';
$string['welcomep50'] = 'L\'ús de totes les aplicacions d\'aquest paquet és governat per les seves llicències respectives. El paquet <strong>$a->installername</strong> complet és 
<a href=\"http://www.opensource.org/docs/definition_plain.html\">codi font obert</a> i es distribueix 
sota llicència <a href=\"http://www.gnu.org/copyleft/gpl.html\">GPL</a>.';
$string['welcomep60'] = 'Les pàgines següents us guiaran per una sèrie de passos fàcils de seguir per configurar <strong>Moodle</strong> en el vostre ordinador. Podeu acceptar els paràmetres per defecte o, opcionalment, modificar-los perquè s\'ajustin a les vostres necessitats.';
$string['welcomep70'] = 'Feu clic en el botó \"Següent\" per continuar la configuració de <strong>Moodle</strong>.';
$string['wrongdestpath'] = 'El camí de destinació és erroni';
$string['wrongsourcebase'] = 'L\'adreça (URL) base de la font és errònia';
$string['wrongzipfilename'] = 'El nom del fitxer ZIP és erroni';
$string['wwwroot'] = 'Adreça web';
$string['wwwrooterror'] = 'L\'adreça web no sembla vàlida. La instal·lació de Moodle no sembla que sigui en aquesta ubicació,';
?>
