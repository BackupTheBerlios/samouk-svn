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

$string['admindirerror'] = 'O le ala i le fa\'afoega (admin directory) o lo\'o tu\'uina mai e sese';
$string['admindirname'] = 'Fa\'afoega';
$string['caution'] = 'Fa\'aeteetega';
$string['closewindow'] = 'Tapuni lenei itulau';
$string['configfilenotwritten'] = 'The installer script was not able to automatically create a config.php file containing your chosen settings, probably because the Moodle directory is not writeable. You can manually copy the following code into a file named config.php within the root directory of Moodle.';
$string['configfilewritten'] = 'config.php ua fa\'amanuiaina lona amataga';
$string['continue'] = 'Fa\'aauau';
$string['database'] = 'Taula\'iga o fa\'amatalaga (Database)';
$string['dataroot'] = 'Ala atu i fa\'amaumauga';
$string['datarooterror'] = 'O le \'Ala atu i fa\'amaumauga\' ua e tu\'uina mai e le o maua pe ua faia. E mafai ona e fa\'asa\'oina mai le ala pe amataina loa le tusiga o le ala.';
$string['dbconnectionerror'] = 'E le o mafai ona fa\'afeso\'ota\'i atu i le Database ua tu\'uina mai. Fa\'amolemole siaki ia fa\'aupuga ma seti ole Database. ';
$string['dbcreationerror'] = 'E sese le faiga o le Database. E le\'o mafai ona amatalia le igoa o le Database ua tu\'uina mai ma seti ua aumaia. ';
$string['dbhost'] = 'Seva o lo\'o u\'uina fa\'amatalaga';
$string['dbprefix'] = 'Tables prefix';
$string['dbtype'] = 'Ituaiga';
$string['dirroot'] = 'Auala atu i le Moodle';
$string['dirrooterror'] = 'O le \'auala atu i le Moodle\' ua uma ona seti e foliga mai e sese - e le o mafai ona maua se fa\'atuga o le Moodle i le mea ua aumaia. O le laia o lo\'o i lalo ua uma lea ona toe fa\'afouina.';
$string['download'] = 'Aumai mai le Initaneti';
$string['error'] = 'Mea sese';
$string['fail'] = 'Le fa\'amanuiaina';
$string['fileuploads'] = 'Faila ua tu\'uina atu';
$string['fileuploadserror'] = 'O le mea lea sa tatau ona i luga o le';
$string['gdversion'] = 'Liliuga GD ';
$string['gdversionerror'] = 'O le faletusi (library) GD e tatau ona i ai aua le faiga ma le fa\'agasologa o ata.';
$string['gdversionhelp'] = '<p>Your server does not seem to have GD installed.</p>

<p>GD is a library that is required by PHP to allow Moodle to process images 
   (such as the user profile icons) and to create new images (such as 
   the log graphs).  Moodle will still work without GD - these features 
   will just not be available to you.</p>

<p>To add GD to PHP under Unix, compile PHP using the --with-gd parameter.</p>

<p>Under Windows you can usually edit php.ini and uncomment the line referencing php_gd2.dll.</p>';
$string['help'] = 'Fesoasoani';
$string['installation'] = 'Fa\'atu/Install';
$string['language'] = 'Gagana';
$string['magicquotesruntime'] = 'Magic Quotes Run Time';
$string['magicquotesruntimeerror'] = 'O le mea lea e tatau ona le i ai';
$string['memorylimit'] = 'Limiti o le memory';
$string['memorylimiterror'] = 'The PHP memory limit is set quite low ... you may run into problems later.';
$string['memorylimithelp'] = '<p>The PHP memory limit for your server is currently set to $a.</p>

<p>This may cause Moodle to have memory problems later on, especially 
   if you have a lot of modules enabled and/or a lot of users.

<p>We recommend that you configure PHP with a higher limit if possible, like 40M.  
   There are several ways of doing this that you can try:
<ol>
<li>If you are able to, recompile PHP with <i>--enable-memory-limit</i>.  
    This will allow Moodle to set the memory limit itself.
<li>If you have access to your php.ini file, you can change the <b>memory_limit</b> 
    setting in there to something like 40M.  If you don\'t have access you might 
    be able to ask your administrator to do this for you.
<li>On some PHP servers you can create a .htaccess file in the Moodle directory 
    containing this line:
    <p><blockquote>php_value memory_limit 40M</blockquote></p>
    <p>However, on some servers this will prevent <b>all</b> PHP pages from working 
    (you will see errors when you look at pages) so you\'ll have to remove the .htaccess file.
</ol>';
$string['name'] = 'Igoa';
$string['next'] = 'Le isi';
$string['ok'] = 'OK';
$string['pass'] = 'Pasi';
$string['password'] = 'Upu tatala';
$string['phpversion'] = 'Liliuga PHP';
$string['phpversionerror'] = 'O le liliuga o le PHP e tatau lava ona fa\'aaoga le 4.1.0';
$string['phpversionhelp'] = '<p>Moodle requires a PHP version of at least 4.1.0.</p>
<p>You are currently running version $a</p>
<p>You must upgrade PHP or move to a host with a newer version of PHP!</p>';
$string['previous'] = 'Talu ai, tuana\'i';
$string['safemode'] = 'Safe Mode';
$string['safemodeerror'] = 'Moodle may have trouble with safe mode on';
$string['sessionautostart'] = 'Session Auto Start';
$string['sessionautostarterror'] = 'O le mea lea e tatu ona tape';
$string['status'] = 'Tulaga';
$string['thischarset'] = 'utf-8';
$string['thisdirection'] = 'ltr';
$string['thislanguage'] = 'Samoan';
$string['user'] = 'O le tagata fa\'aaoga';
$string['wwwroot'] = 'Tuatusi o le initaneti';
$string['wwwrooterror'] = 'O le tuatusi o le initaneti e foliga mai e le\'o i ai - o le fa\'atuuga o le Moodle e foliga mai e le\'o i ai se mea. ';
?>
