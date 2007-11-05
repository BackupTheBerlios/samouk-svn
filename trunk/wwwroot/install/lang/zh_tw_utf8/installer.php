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

$string['admindirerror'] = '系統管理目錄指定不正確';
$string['admindirname'] = '管理者目錄';
$string['admindirsettinghead'] = '設定管理資料夾';
$string['admindirsettingsub'] = '少部份主機使用 /admin 作為管理介面或是其他用途的特殊網址，這會與 Moodle 管理介面的標準位置；您可以修改管理介面資料夾的名稱，然後將新名稱輸入這兒。例如 <br /> <br /><b>moodleadmin</b><br /> <br />
這會修正 Moodle 管理介面的連結。';
$string['bypassed'] = '通過';
$string['cannotcreatelangdir'] = '無法建立語言資料夾';
$string['cannotcreatetempdir'] = '無法建立暫存資料夾';
$string['cannotdownloadcomponents'] = '無法下載元件';
$string['cannotdownloadzipfile'] = '無法下載 ZIP 檔案';
$string['cannotfindcomponent'] = '找不到元件';
$string['cannotsavemd5file'] = '無法儲存 md5 檔案';
$string['cannotsavezipfile'] = '無法儲存 ZIP 檔案';
$string['cannotunzipfile'] = '無法解壓縮檔案';
$string['caution'] = '注意';
$string['check'] = '勾選';
$string['chooselanguagehead'] = '選擇語系';
$string['chooselanguagesub'] = '這個語系只會影響安裝過程，您可以在接下來的畫面中選擇網站與使用者的語言。';
$string['closewindow'] = '關閉本視窗';
$string['compatibilitysettingshead'] = '檢查PHP設定';
$string['compatibilitysettingssub'] = '您的伺服器必須通過所有測試才能夠正確執行 Moodle。';
$string['componentisuptodate'] = '元件是最新狀態';
$string['configfilenotwritten'] = '本安裝程式無法自動產生conifg.php檔案，此檔案包含您的個人設定，有可能是您的moodle資料夾無寫入權限，您可以手動複製以下的程式碼，存成conifg.php，然後把它放在MOODLE的根目錄中';
$string['configfilewritten'] = 'CONFIG.PHP成功建立';
$string['configurationcompletehead'] = '設定完成';
$string['configurationcompletesub'] = 'Moodle 嘗試儲存設定資料於根目錄的一個檔案中。';
$string['continue'] = '下一頁';
$string['curlrecommended'] = '建議安裝額外的 Curl 函式庫來啟用 Moodle 網路功能。';
$string['database'] = '資料庫';
$string['databasecreationsettingshead'] = '現在您需要設定大部分 Moodle 資料儲存的資料庫，透過下面指定的設定，安裝程式可以自動建立資料庫。';
$string['databasecreationsettingssub'] = '<b>類型：</b> 安裝程式固定使用 \"mysql\" <br />
<b>主機：</b> 安裝程式固定使用 \"localhost\"<br />
<b>名稱：</b> 資料庫名稱，例如 moodle<br />
<b>帳號：</b> 安裝程式固定使用 \"root\"<br />
<b>密碼：</b> 您的資料庫密碼<br />
<b>資料表前置字元：</b> 選擇性為所有資料表名稱加入前置字元';
$string['databasesettingshead'] = '現在您需要設定大部分 Moodle 資料儲存的資料庫，資料庫必須已經建立且準備好能夠存取它的帳號、密碼。';
$string['databasesettingssub'] = '<b>類型：</b>mysql或postgres7<br />
<b>主機：</b>localhost或URL<br />
<b>名稱：</b>資料庫名稱， 例:moodle<br />
<b>帳號：</b>您的資料庫操作帳號<br />
<b>密碼：</b>您資料庫的操作密碼<br />
<b>資料表前置字元:</b>資料表前置名稱的預設選項';
$string['dataroot'] = '資料目錄';
$string['datarooterror'] = '您指定的\"資料目錄\"找不到或無法建立，請修正路徑或手動建立該目錄';
$string['dbconnectionerror'] = '無法連到您指定的資料庫，請查檢您的資料庫設定';
$string['dbcreationerror'] = '建立資料庫錯誤，無法以您給的資料庫名稱建立資料表';
$string['dbhost'] = '主機位址';
$string['dbprefix'] = '資料表前置字元';
$string['dbtype'] = '類型';
$string['dbwrongencoding'] = '指定的資料庫執行在一個不建議的編碼設定 ($a) ，建議您改用萬國編碼 (UTF-8) 替代；您可以選擇 \"忽略資料庫編碼測試\" 來跳過這個項目，不過可能會在未來遇到問題。';
$string['directorysettingshead'] = '請確認 Moodle 的安裝路徑';
$string['directorysettingssub'] = '<b>網站位址：</b>
指定存取 Moodle 的完整網址，如果您的網站透過多個網址存取，請選擇最常被學生使用的那一個；結尾不要有斜線。
<br />
<br />
<b>Moodle 目錄：</b>
指定安裝的完整路徑，請確認英文大小寫是否正確。
<br />
<br />
<b>資料目錄：</b>
您需要設定一個 Moodle 可以儲存上傳資料的位置，這個位置要能夠讓網頁伺服器(通常是 \'nobody\' 或 \'apache\')讀取與寫入，但是建議不要放在能夠直接透過網址存取的位置。';
$string['dirroot'] = 'Moodle目錄';
$string['dirrooterror'] = '\"Moodle目錄\"的設定似乎有誤-無法找到MOODLE的安裝，下列的值已重設';
$string['download'] = '下載';
$string['downloadedfilecheckfailed'] = '下載檔案檢查錯誤';
$string['downloadlanguagebutton'] = '下載 \"$a\" 語言包';
$string['downloadlanguagehead'] = '下載語言包';
$string['downloadlanguagenotneeded'] = '您可以用預設的語言包 \"$a\" 繼續安裝過程。';
$string['downloadlanguagesub'] = '您現在可以選擇下載一個語言包然後用指定的語言繼續安裝過程，如果您無法下載語言包，安裝過程會繼續以英文進行。（只要安裝完成，您還是可以下載、安裝其他的語言包）';
$string['environmenterrortodo'] = '在開始安裝這個 Moodle 版本前，您必須修正上述所有環境問題（錯誤）！';
$string['environmenthead'] = '檢查您的環境';
$string['environmentrecommendinstall'] = '建議安裝/啟用';
$string['environmentrecommendversion'] = '建議版本為 $a->needed ，您目前版本為 $a->current';
$string['environmentrequireinstall'] = '必須安裝/啟用';
$string['environmentrequireversion'] = '必要版本為 $a->needed ，您目前版本為 $a->current';
$string['environmentsub'] = '正在檢查系統的相關元件來確認是否符合安裝需求';
$string['environmentxmlerror'] = '讀取環境資料時發生錯誤 ($a->error_code)';
$string['error'] = '錯誤';
$string['fail'] = '失敗';
$string['fileuploads'] = '檔案上傳';
$string['fileuploadserror'] = '這應該開啟';
$string['gdversion'] = 'GD版本';
$string['gdversionerror'] = 'GD程式庫應該存在，以便處理並建立影像';
$string['gdversionhelp'] = '<p>您的伺服器似乎未安裝GD</P>

<p>GD是PHP的一套繪圖程式庫，可允許MOODLE處理圖形(像是使用者的個人圖像)或自動產生新圖形(像是產生統計圖)。沒有GD，MOODLE仍可正常運作，但上述的功能就會無法使用</P>

<P>要在UNIX中，把GD功能加到PHP中，您必須用--with-gd 參數重新編譯PHP</P>
<P>在WINDOWS中，您可以編輯PHP.INI這個檔案，將libgd.dll前的#號取消</p>';
$string['globalsquotes'] = '不安全的全域變數處理';
$string['globalsquoteserror'] = '修正您的PHP設定： 停用register_globals且/或啟用magic_quotes_gpc';
$string['help'] = '說明';
$string['iconvrecommended'] = '為了提昇網站效率，建議您可以安裝額外的 ICONV 函式庫，特別是在您的網站使用非拉丁語系。';
$string['info'] = '資訊';
$string['installation'] = '系統安裝';
$string['invalidmd5'] = '錯誤的 md5';
$string['langdownloaderror'] = '抱歉，無法安裝語言 \"$a\" ，安裝過程將會以英文繼續。';
$string['langdownloadok'] = '語言 \"$a\" 成功安裝，安裝過程將會以指定語言繼續。';
$string['language'] = '語言/文字';
$string['magicquotesruntime'] = 'Magic Quotes 動態執行程式庫';
$string['magicquotesruntimeerror'] = '這應該關閉';
$string['mbstringrecommended'] = '為了提昇網站效率，建議您可以安裝額外的 MBSTRING 函式庫，特別是在您的網站使用非拉丁語系。';
$string['memorylimit'] = '記憶體限制';
$string['memorylimiterror'] = 'PHP 執行之記憶體設定過低，您可能稍後會遇到一些問題';
$string['memorylimithelp'] = '<p>您伺服器的記憶體限制目前設為$a。</p>

<p>這可能會影響往後moodle運作時記憶體的使用，尤其是您有許多模組和(或)使用者的時候</p>

<p>我們建議您在php中的記憶體儘量設定較高的記憶體使用，如16MB，有幾種方式可以進行:</p>
<ol>
<li>如果您可以重新編譯PHP請使用 <i>--enable-memory-limit</i>。
這可以讓MOODLE 自行設定記憶體上限。</li>
<li>如果您可以存取php.ini，您可以變更 <b>memory_limit</b>
將它設為16M或更高。如果您無法存取，可洽您的系統管理員，幫您調整設定。</li>
<li>在一些PHP 伺服器中您可以在MOODL目錄建立一個 .htaccess 檔案，檔案內容包含:
<p><blockquote>php_value memory_limit 16M</blockquote></p>
<p>但是，在一些伺服器上，這樣的設定會使<b>所有</b> PHP 頁面無法正常動作(瀏覽時會發生錯誤)，此時您必須移除 .htaccess 檔案。</p></li>
</ol>';
$string['missingrequiredfield'] = '缺少部份必填欄位';
$string['moodledocslink'] = '這個頁面的 Moodle 說明文件';
$string['mysql416bypassed'] = '不過您的網站只有使用 iso-8859-1 (拉丁) 語言，因此您可以繼續使用已經安裝的 MySQL 4.1.12 (或更新版本)。';
$string['mysql416required'] = 'Moodle 需要 MySQL 4.1.16 或更新的版本來確保未來所有資料都能夠轉換為 UTF-8 編碼。';
$string['mysqlextensionisnotpresentinphp'] = 'PHP並未正確設定支援MySQL，請確認您的php.ini檔案設定或重新編譯PHP。';
$string['name'] = '名稱';
$string['next'] = '下一個';
$string['ok'] = '確定';
$string['opensslrecommended'] = '強烈建議您安裝 OpenSSL 函式庫，這樣才能夠使用 Moodle 網路功能';
$string['parentlanguage'] = '主顯示語言';
$string['pass'] = '測試通過';
$string['password'] = '密碼';
$string['php50restricted'] = 'PHP 5.0.x 有著許多已知問題，請升級為 5.1.x 或降級為 4.3.x 或 4.4.x';
$string['phpversion'] = 'PHP版本';
$string['phpversionerror'] = 'PHP版本至少必須在4.1.0以上';
$string['phpversionhelp'] = '<p>Moodle 需要至少4.1.0 的PHP版本 </p>
<p>您目前執行的是$a 版</p>
<p>您必須更新您的 PHP 或在有更新版本的主機進行安裝!</p>';
$string['previous'] = '前';
$string['remotedownloadnotallowed'] = '您的伺服器不允許下載元件（停用了 allow_url_fopen ）。<br /><br />您必須手動下載 <a href=\"$a->url\">$a->url</a> ，然後複製到 \"$a->dest\" 並且解壓縮。';
$string['report'] = '報表';
$string['restricted'] = '限制的';
$string['safemode'] = '安全模式';
$string['safemodeerror'] = 'moodle 在安全模式啟動時可能會發生錯誤';
$string['sessionautostart'] = 'Session自動啟動';
$string['sessionautostarterror'] = '這應該關閉';
$string['skipdbencodingtest'] = '略過資料庫編碼測試';
$string['status'] = '狀態';
$string['thischarset'] = "UTF-8";
$string['thisdirection'] = 'ltr';
$string['thislanguage'] = "正體中文";
$string['unicoderecommended'] = '建議將所有資料儲存為萬國編碼(UTF-8)，新安裝的網站在存取資料庫時預設編碼都是萬國編碼，如果您正在進行升級，您應該執行 UTF-8 轉換程式（請參考管理首頁）';
$string['unicoderequired'] = '您必須將資料儲存為萬國編碼格式 (UTF-8)，新安裝的網站在存取資料庫時預設編碼都是萬國編碼，如果您正在進行升級，您應該執行 UTF-8 轉換程式（請參考管理首頁）';
$string['user'] = '使用者';
$string['welcomep10'] = '$a->installername ($a->installerversion';
$string['welcomep20'] = '這個頁面是提醒您已經成功安裝與啟動 <strong>$a->packname $a->packversion</strong> ，恭喜！';
$string['welcomep30'] = '這個版本的 <strong>$a->installername</strong> 包含能夠建立 <strong>Moodle</strong> 執行環境的應用，名稱：';
$string['welcomep40'] = '這個包裝也包含 <strong>Moodle $a->moodlerelease ($a->moodleversion)</strong> 。';
$string['welcomep50'] = '使用這個包裝的所有應用程式必須遵循他們所使用的授權方式，完整的 <strong>$a->installername</strong> 包裝是
<a href=\"http://www.opensource.org/docs/definition_plain.html\">開放原始碼</a> 且基於 <a href=\"http://www.gnu.org/copyleft/gpl.html\">GPL</a> 授權發佈。';
$string['welcomep60'] = '下面頁面將會透過一些簡單的步驟引導您安裝 <strong>Moodle</strong> 在您的電腦中，您可以接受預設值或是針對自己的需求調整。';
$string['welcomep70'] = '點選 \"下一步\" 按鈕來繼續 <strong>Moodle</strong> 安裝。';
$string['wrongdestpath'] = '錯誤的目的路徑。';
$string['wrongsourcebase'] = '錯誤的來源網址基礎';
$string['wrongzipfilename'] = '錯誤的 ZIP 檔名。';
$string['wwwroot'] = '網頁路徑';
$string['wwwrooterror'] = '指定網頁路徑不存在 - 這個 moodle系統並不在您指定的地方';
?>