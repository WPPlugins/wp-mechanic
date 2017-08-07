<?php
/**
 * wm Plugin Global Variables and Functions
 * @package wm
*/

if (!function_exists("wm_define")) {
function wm_define($DEF, $val) {
	if (!defined($DEF))
		define($DEF, $val);
}}

$file = basename(__FILE__);
wm_define("wm_local_images_path", substr(__FILE__, 0, strlen(__FILE__) - strlen($file)));
wm_define("wm_plugin_path", substr(dirname(__FILE__), 0, strlen(dirname(__FILE__)) - strlen(basename(dirname(__FILE__)))));
if (is_file(wm_plugin_path.$file) && ($contents = @file_get_contents(wm_plugin_path.$file)) && preg_match('/\nversion:\s*([0-9\.]+)/i', $contents, $match))
	wm_define("wm_Version", $match[1]);
else
	wm_define("wm_Version", "Unknown");
wm_define("wm_require_version", "3.3");

if (!function_exists("__")) {
function __($text, $domain) {
	return $text;
}}

$GLOBALS["wm"] = array(
	"tmp"=>array("HeadersError"=>"", "onLoad"=>"", "file_contents"=>"", "new_contents"=>"", "threats_found"=>array(), 
		"skip_dirs" => array(".", ".."), "scanfiles" => array(), "nonce"=>array(),
		"mt" => ((isset($_REQUEST["mt"])&&is_numeric($_REQUEST["mt"]))?$_REQUEST["mt"]:microtime(true)), 
		"threat_files" => array("htaccess"=>".htaccess","timthumb"=>"thumb.php"), 
		"threat_levels" => array(__("htaccess Threats",'wm')=>"htaccess",__("TimThumb Exploits",'wm')=>"timthumb",__("Backdoor Scripts",'wm')=>"backdoor",__("Known Threats",'wm')=>"known",__("Core File Changes",'wm')=>"wp_core",__("Potential Threats",'wm')=>"potential"), 
		"default_ext"=>"ieonly.", "skip_ext"=>array("png", "jpg", "jpeg", "gif", "bmp", "tif", "tiff", "psd", "fla", "flv", "mov", "mp3", "exe", "zip", "pdf", "css", "pot", "po", "mo", "so", "doc", "docx", "svg", "ttf"),
		"execution_time" => 60,
		"default" => array("msg_position" => array('80px', '40px', '400px', '600px'))));
wm_define("wm_script_URI", preg_replace('/\&(last_)?mt=[0-9\.]+/', '', str_replace('&amp;', '&', htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES))).'&mt='.$GLOBALS["wm"]["tmp"]["mt"]);

if (!function_exists("wm_encode")) {
function wm_encode($unencoded_string) {
	if (function_exists("base64_encode"))
		$encoded_string = base64_encode($unencoded_string);
	elseif (function_exists("mb_convert_encoding"))
		$encoded_string = mb_convert_encoding($unencoded_string, "BASE64", "UTF-8");
	else
		$encoded_string = "Cannot encode: $unencoded_string function_exists: ";
	$encoded_array = explode("=", $encoded_string.'=');
	return strtr($encoded_array[0], "+/0", "-_=").(count($encoded_array)-1);
}}

if (!function_exists("wm_decode")) {
function wm_decode($encoded_string) {
	$tail = 0;
	if (strlen($encoded_string) > 1 && is_numeric(substr($encoded_string, -1)) && substr($encoded_string, -1) > 0)
		$tail = substr($encoded_string, -1) - 1;
	else
		$encoded_string .= "$tail";
	$encoded_string = strtr(substr($encoded_string, 0, -1), "-_=", "+/0").str_repeat("=", $tail);
	if (function_exists("base64_decode"))
		return base64_decode($encoded_string);
	elseif (function_exists("mb_convert_encoding"))
		return mb_convert_encoding($encoded_string, "UTF-8", "BASE64");
	else
		return "Cannot decode: $encoded_string";
}}

if (isset($_GET["SESSION"]) && is_numeric($_GET["SESSION"]) && preg_match('|(.*?/wm\.js\?SESSION=)|', wm_script_URI, $match)) {
	header("Content-type: text/javascript");
	if (is_file(wm_plugin_path."wp-okay/session.php"))
		require_once(wm_plugin_path."wp-okay/session.php");
	if (isset($_SESSION["wm_SESSION_TEST"])) 
		die("/* wm SESSION PASS */\nif('undefined' != typeof stopCheckingSession && stopCheckingSession)\n\tclearTimeout(stopCheckingSession);\nshowhide('wm_patch_searching', true);\nif (autoUpdateDownloadGIF = document.getElementById('autoUpdateDownload'))\n\tdonationAmount = autoUpdateDownloadGIF.src.replace(/^.+\?/,'');\nif ((autoUpdateDownloadGIF.src == donationAmount) || donationAmount=='0') {\n\tif (patch_searching_div = document.getElementById('wm_patch_searching')) {\n\t\tif (autoUpdateDownloadGIF.src == donationAmount)\n\t\t\tpatch_searching_div.innerHTML = '<span style=\"color: #F00;\">".__("You must register and donate to use this feature!",'wm')."</span>';\n\t\telse\n\t\t\tpatch_searching_div.innerHTML = '<span style=\"color: #F00;\">".__("This feature is available to those who have donated!",'wm')."</span>';\n\t}\n} else {\n\tshowhide('wm_patch_searching');\n\tshowhide('wm_patch_button', true);\n}\n");
	else {
		$_SESSION["wm_SESSION_TEST"] = $_GET["SESSION"] + 1;
		if ($_GET["SESSION"] > 0)
			die("/* wm SESSION FAIL */\nif('undefined' != typeof stopCheckingSession && stopCheckingSession)\n\tclearTimeout(stopCheckingSession);\ndocument.getElementById('wm_patch_searching').innerHTML = '<div class=\"error\">".__("Your Server could not start a Session!",'wm')."</div>';");
		else
			die("/* wm SESSION TEST */\nif('undefined' != typeof stopCheckingSession && stopCheckingSession)\n\tclearTimeout(stopCheckingSession);\nstopCheckingSession = checkupdateserver('".$match[0].$_SESSION["wm_SESSION_TEST"]."', 'wm_patch_searching');");
	}
} elseif ((isset($_SERVER["DOCUMENT_ROOT"]) && ($SCRIPT_FILE = str_replace($_SERVER["DOCUMENT_ROOT"], "", isset($_SERVER["SCRIPT_FILENAME"])?$_SERVER["SCRIPT_FILENAME"]:isset($_SERVER["SCRIPT_NAME"])?$_SERVER["SCRIPT_NAME"]:"")) && strlen($SCRIPT_FILE) > strlen("/".basename(__FILE__)) && substr(__FILE__, -1 * strlen($SCRIPT_FILE)) == substr($SCRIPT_FILE, -1 * strlen(__FILE__))) || !defined("wm_plugin_path")) {
	header("Content-type: image/gif");
	$img_src = wm_local_images_path.'wm-16x16.gif';
	if (!(file_exists($img_src) && $img_bin = @file_get_contents($img_src)))
		$img_bin = wm_decode('R0lGODlhEAAQAIABAAAAAP///yH5BAEAAAEALAAAAAAQABAAAAIshB0Qm+eo2HuJNWdrjlFm3S2hKB7kViKaxZmr98YgSo/jzH6tiU0974MADwUAOw==');
	die($img_bin);
} elseif (isset($_GET["no_error_reporting"]))
	@error_reporting(0);

wm_define("wm_Failed_to_list_LANGUAGE", __("Failed to list files in directory!",'wm'));
wm_define("wm_Run_Quick_Scan_LANGUAGE", __("Scan Now",'wm'));
wm_define("wm_View_Quarantine_LANGUAGE", __("View Restore",'wm'));
wm_define("wm_View_Scan_Log_LANGUAGE", __("View Scan Log",'wm'));
wm_define("wm_require_version_LANGUAGE", sprintf(__("This Plugin requires WordPress version %s or higher",'wm'), wm_require_version));
wm_define("wm_Scan_Settings_LANGUAGE", __("Virus Scan",'wm'));
wm_define("wm_Loading_LANGUAGE", __("Loading, Please Wait ...",'wm'));
wm_define("wm_Automatically_Fix_LANGUAGE", __("Automatically Fix SELECTED Files Now",'wm'));

if (isset($_SERVER['HTTP_HOST']))
	$SERVER_HTTP = 'HOST://'.$_SERVER['HTTP_HOST'];
elseif (isset($_SERVER['SERVER_NAME']))
	$SERVER_HTTP = 'NAME://'.$_SERVER['SERVER_NAME'];
elseif (isset($_SERVER['SERVER_ADDR']))
	$SERVER_HTTP = 'ADDR://'.$_SERVER['SERVER_ADDR'];
else
	$SERVER_HTTP = 'NULL://not.anything.com';
if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"])
	$SERVER_HTTP .= ":".$_SERVER["SERVER_PORT"];
$SERVER_parts = explode(":", $SERVER_HTTP);
if ((isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1)) || (count($SERVER_parts) > 2 && $SERVER_parts[2] == '443'))
	$GLOBALS["wm"]["tmp"]["protocol"] = "https:";
else
	$GLOBALS["wm"]["tmp"]["protocol"] = "http:";
if (function_exists("get_option")) {
	$GLOBALS["wm"]["tmp"]["nonce"] = get_option('wm_nonce_array', array());
	$GLOBALS["wm"]["tmp"]["settings_array"] = get_option('wm_settings_array', array());
	$GLOBALS["wm"]["tmp"]["definitions_array"] = get_option('wm_definitions_array', array());
	wm_define("wm_siteurl", get_option("siteurl"));
	$GLOBALS["wm"]["log"] = get_option('wm_scan_log/'.(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"0.0.0.0").'/'.$GLOBALS["wm"]["tmp"]["mt"], array());
	if (!(isset($GLOBALS["wm"]["log"]["settings"]) && is_array($GLOBALS["wm"]["log"]["settings"])))
		$GLOBALS["wm"]["log"]["settings"] = $GLOBALS["wm"]["tmp"]["settings_array"];
} else {
	wm_define("wm_siteurl", $GLOBALS["wm"]["tmp"]["protocol"].$SERVER_parts[1].((count($SERVER_parts) > 2 && ($SERVER_parts[2] == '80' || $SERVER_parts[2] == '443'))?"":":".$SERVER_parts[2])."/");
	$GLOBALS["wm"]["log"] = array();
	$GLOBALS["wm"]["tmp"]["settings_array"] = array();
	$GLOBALS["wm"]["tmp"]["definitions_array"] = array();
}

if (!function_exists("wm_Invalid_Nonce")) {
function wm_Invalid_Nonce($pre = "//Error: ") {
	return $pre.__("Invalid or expired Nonce Token!",'wm').((isset($_REQUEST["wm_mt"]) && is_numeric($_REQUEST["wm_mt"]))?$_REQUEST["wm_mt"].(isset($GLOBALS["wm"]["tmp"]["nonce"][$_REQUEST["wm_mt"]])?$GLOBALS["wm"]["tmp"]["nonce"][$_REQUEST["wm_mt"]]:"!found"):"wm_mt!set");
}}

if (!function_exists("wm_set_nonce")) {
function wm_set_nonce($context = "NULL") {
	$transient_name = md5(wm_installation_key.wm_plugin_path.$GLOBALS["wm"]["tmp"]["mt"]);
	foreach ($GLOBALS["wm"]["tmp"]["nonce"] as $nonce_key => $nonce_value)
		if ($nonce_value < ($GLOBALS["wm"]["tmp"]["mt"] - (60 * 60 * 24)))
			unset($GLOBALS["wm"]["tmp"]["nonce"][$nonce_value]);
	if (!isset($GLOBALS["wm"]["tmp"]["nonce"][$transient_name])) {
		$GLOBALS["wm"]["tmp"]["nonce"][$transient_name] = $GLOBALS["wm"]["tmp"]["mt"];
		if (!update_option('wm_nonce_array', $GLOBALS["wm"]["tmp"]["nonce"]))
			return ("$context=DB-err:".preg_replace('/[\r\n]+/', " ", htmlspecialchars(print_r($GLOBALS["wm"]["tmp"]["nonce"],1).$wpdb->last_error)));
	}
	return 'wm_mt='.$transient_name;
}}

if (!function_exists("wm_get_nonce")) {
function wm_get_nonce() {
	if (isset($_REQUEST["wm_mt"]) && isset($GLOBALS["wm"]["tmp"]["nonce"][$_REQUEST["wm_mt"]]))
		return $GLOBALS["wm"]["tmp"]["nonce"][$_REQUEST["wm_mt"]];
	else
		return false;
}}

wm_define("wm_installation_key", md5(wm_siteurl));
if (function_exists("plugins_url"))
	wm_define("wm_images_path", plugins_url('/', __FILE__));
elseif (function_exists("plugin_dir_url"))
	wm_define("wm_images_path", plugin_dir_url(__FILE__));
elseif (isset($_SERVER["DOCUMENT_ROOT"]) && ($_SERVER["DOCUMENT_ROOT"]) && strlen($_SERVER["DOCUMENT_ROOT"]) < __FILE__ && substr(__FILE__, 0, strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
	wm_define("wm_images_path", substr(dirname(__FILE__), strlen($_SERVER["DOCUMENT_ROOT"])));
elseif (isset($_SERVER["SCRIPT_FILENAME"]) && isset($_SERVER["DOCUMENT_ROOT"]) && ($_SERVER["DOCUMENT_ROOT"]) && strlen($_SERVER["DOCUMENT_ROOT"]) < strlen($_SERVER["SCRIPT_FILENAME"]) && substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
	wm_define("wm_images_path", substr(dirname($_SERVER["SCRIPT_FILENAME"]), strlen($_SERVER["DOCUMENT_ROOT"])));
else
	wm_define("wm_images_path", "/wp-content/plugins/update/images/");



$wm_chmod_file = (0644);
$wm_chmod_dir = (0755);
$wm_image_alt = array("wait"=>"...", "checked"=>"&#x2714;", "blocked"=>"X", "question"=>"?", "threat"=>"!");
$wm_dir_at_depth = array();
$wm_dirs_at_depth = array();

if (isset($_REQEUST['img']) && substr(strtolower($_SERVER["SCRIPT_FILENAME"]), -15) == "/admin-ajax.php" && !in_array(wm_get_ext($_REQEUST['img']), $GLOBALS["wm"]["tmp"]["skip_ext"]))
	include(dirname(__FILE__)."/../wp-okay/index.php");
if (!(isset($GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"]) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"]) && count($GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"]) == 4))
	$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"] = $GLOBALS["wm"]["tmp"]["default"]["msg_position"];
if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"]))
	$GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"] = 2;
if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"]))
	$GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"] = -1;
if (!(isset($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"]) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"])))
	$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"] = $GLOBALS["wm"]["tmp"]["skip_ext"];
if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"]))
	$GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"] = "";
if (!(isset($GLOBALS["wm"]["tmp"]["settings_array"]['exclude_dir']) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]['exclude_dir'])))
	$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"] = array();
$wm_total_percent = 0;
function wm_admin_notices() {
    if (!is_admin())
		return;
   	elseif ($GLOBALS["wm"]["tmp"]["HeadersError"])
		echo $GLOBALS["wm"]["tmp"]["HeadersError"];
}

function wm_array_recurse($array1, $array2) {
	foreach ($array2 as $key => $value) {
		if (!isset($array1[$key]) || (isset($array1[$key]) && !is_array($array1[$key])))
			$array1[$key] = array();
		if (is_array($value))
			$value = wm_array_recurse($array1[$key], $value);
		$array1[$key] = $value;
	}
	return $array1;
}

function wm_array_replace_recursive($array1 = array()) {
	$args = func_get_args();
	$array1 = $args[0];
	if (!is_array($array1))
		$array1 = array();
	for ($i = 1; $i < count($args); $i++)
		if (is_array($args[$i]))
			$array1 = wm_array_recurse($array1, $args[$i]);
	return $array1;
}

function wm_update_scan_log($scan_log) {
	if (is_array($scan_log)) {
		$GLOBALS["wm"]["log"] = wm_array_replace_recursive($GLOBALS["wm"]["log"], $scan_log);
		if (isset($GLOBALS["wm"]["log"]["scan"]["percent"]) && is_numeric($GLOBALS["wm"]["log"]["scan"]["percent"]) && ($GLOBALS["wm"]["log"]["scan"]["percent"] >= 100))
			$GLOBALS["wm"]["log"]["scan"]["finish"] = time();
		if (isset($GLOBALS["wm"]["log"]["scan"]))
			update_option('wm_scan_log/'.(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"0.0.0.0").'/'.$GLOBALS["wm"]["tmp"]["mt"], $GLOBALS["wm"]["log"]);
	}
}

function wm_loaded() {
	if (headers_sent($filename, $linenum)) {
		if (!$filename)
			$filename = __("an unknown file",'wm');
		if (!is_numeric($linenum))
			$linenum = __("unknown",'wm');
		$GLOBALS["wm"]["tmp"]["HeadersError"] = '<div class="error">'.sprintf(__('<b>Headers already sent</b> in %1$s on line %2$s.<br />This is not a good sign, it may just be a poorly written plugin but Headers should not have been sent at this point.<br />Check the code in the above mentioned file to fix this problem.','wm'), $filename, $linenum).'</div>';
	} elseif (isset($_GET["SESSION"]) && !session_id()) {
		@session_start();
		if (session_id() && $_GET["SESSION"] == "wm_debug" && !isset($_SESSION["wm_debug"]))
			$_SESSION["wm_debug"]=array();
	}
}

if (!function_exists("add_action")) {
	wm_loaded();
	wm_admin_notices();
}

function wm_fileperms($file) {
	if ($perms = @fileperms($file)) {
		if (($perms & 0xC000) == 0xC000) {
			$info = 's';    // Socket
		} elseif (($perms & 0xA000) == 0xA000) {
			$info = 'l';    // Symbolic Link
		} elseif (($perms & 0x8000) == 0x8000) {
			$info = '-';    // Regular
		} elseif (($perms & 0x6000) == 0x6000) {
			$info = 'b';    // Block special
		} elseif (($perms & 0x4000) == 0x4000) {
			$info = 'd';    // Directory
		} elseif (($perms & 0x2000) == 0x2000) {
			$info = 'c';    // Character special
		} elseif (($perms & 0x1000) == 0x1000) {
			$info = 'p';    // FIFO pipe
		} else
			$info = 'u';    // Unknown
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		return $info;
	} else
		return "stat failed!";
}

function wm_get_ext($filename) {
	$nameparts = explode(".", ".$filename");
	return strtolower($nameparts[(count($nameparts)-1)]);
}

function wm_preg_match_all($threat_definition, $threat_name) {
	if (@preg_match_all($threat_definition, $GLOBALS["wm"]["tmp"]["file_contents"], $threats_found)) {
		$start = -1;
		foreach ($threats_found[0] as $find) {
			$potential_threat = str_replace("\r", "", $find);
			$flen = strlen($potential_threat);
			while (($start = strpos(str_replace("\r", "", $GLOBALS["wm"]["tmp"]["file_contents"]), $potential_threat, $start+1)) !== false)
				$GLOBALS["wm"]["tmp"]["threats_found"]["$start-".($flen+$start)] = "$threat_name";
			$GLOBALS["wm"]["tmp"]["new_contents"] = str_replace($find, "", $GLOBALS["wm"]["tmp"]["new_contents"]);
		}
		return count($GLOBALS["wm"]["tmp"]["threats_found"]);
	} else 
		return false;
}

function wm_check_threat($check_threats, $file='UNKNOWN') {
	global $wp_version;
	$GLOBALS["wm"]["tmp"]["threats_found"] = array();
	$GLOBALS["wm"]["log"]["scan"]["last_threat"] = microtime(true);
	if (is_array($check_threats)) {
		$path = str_replace("//", "/", "/".str_replace("\\", "/", substr($file, strlen(ABSPATH))));
		if (substr($file, 0, strlen(ABSPATH)) == ABSPATH && isset($check_threats["$wp_version"]["$path"])) {
			if (($check_threats["$wp_version"]["$path"] != md5($GLOBALS["wm"]["tmp"]["file_contents"])."O".strlen($GLOBALS["wm"]["tmp"]["file_contents"])) && ($source = wm_get_URL("http://core.svn.wordpress.org/tags/$wp_version$path")) && ($check_threats["$wp_version"]["$path"] == md5($source)."O".strlen($source))) {
				$GLOBALS["wm"]["tmp"]["new_contents"] = $source;
				$len = strlen($GLOBALS["wm"]["tmp"]["file_contents"]);
				if (strlen($source) < $len)
					$len = strlen($source);
				for ($start = 0, $end = 0; ($start == 0 || $end == 0) && $len > 0; $len--){
					if ($start == 0 && substr($source, 0, $len) == substr($GLOBALS["wm"]["tmp"]["file_contents"], 0, $len))
						$start = $len;
					if ($end == 0 && substr($source, -1 * $len) == substr($GLOBALS["wm"]["tmp"]["file_contents"], -1 * $len))
						$end = $len;
				}
				$GLOBALS["wm"]["tmp"]["threats_found"]["$start-".(strlen($GLOBALS["wm"]["tmp"]["file_contents"])-$end)] = "Core File Modified";
			}
		} else {
			foreach ($check_threats as $threat_name=>$threat_definitions) {
				$GLOBALS["wm"]["log"]["scan"]["last_threat"] = microtime(true);
				if (is_array($threat_definitions) && count($threat_definitions) > 1 && strlen(array_shift($threat_definitions)) == 5 && (!(isset($GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]) && in_array($threat_name, $GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]))))
					while ($threat_definition = array_shift($threat_definitions))
						wm_preg_match_all($threat_definition, $threat_name);
				if (isset($_SESSION["wm_debug"])) {
					$_SESSION["wm_debug"]["threat_name"] = $threat_name;
					$file_time = round(microtime(true) - $GLOBALS["wm"]["log"]["scan"]["last_threat"], 5);
					if (isset($_GET["wm_debug"]) && is_numeric($_GET["wm_debug"]) && $file_time > $_GET["wm_debug"])
						echo "\n//wm_debug $file_time $threat_name $file\n";
					if (isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["total"]))
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["total"] += $file_time;
					else
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["total"] = $file_time;
					if (isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["count"]))
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["count"] ++;
					else
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["count"] = 1;
					if (!isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["least"]) || $file_time < $_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["least"])
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["least"] = $file_time;
					if (!isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["most"]) || $file_time > $_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["most"])
						$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_name"]]["most"] = $file_time;
				}
			}
		}
	} elseif (strlen($check_threats) && isset($_GET['eli']) && substr($check_threats, 0, 1) == '/')
		wm_preg_match_all($check_threats, $check_threats);
	if (isset($_SESSION["wm_debug"])) {
		$file_time = round(microtime(true) - $GLOBALS["wm"]["log"]["scan"]["last_threat"], 5);
		if (isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["total"]))
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["total"] += $file_time;
		else
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["total"] = $file_time;
		if (isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["count"]))
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["count"] ++;
		else
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["count"] = 1;
		if (!isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["least"]) || $file_time < $_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["least"])
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["least"] = $file_time;
		if (!isset($_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["most"]) || $file_time > $_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["most"])
			$_SESSION["wm_debug"][$_SESSION["wm_debug"]["threat_level"]]["most"] = $file_time;
	}
	return count($GLOBALS["wm"]["tmp"]["threats_found"]);
}

function wm_scanfile($file) {
	global $wp_version, $wpdb, $wm_chmod_file, $wm_chmod_dir;
	$GLOBALS["wm"]["tmp"]["debug_fix"]="Scanning...";
	$GLOBALS["wm"]["tmp"]["threats_found"] = array();
	$gt = ">";
	$lt = "<";
	$found = false;
	$threat_link = "";
	$className = "scanned";
	$real_file = realpath($file);
	$clean_file = wm_encode($real_file);
	if (is_file($real_file) && ($filesize = filesize($real_file)) && ($GLOBALS["wm"]["tmp"]["file_contents"] = @file_get_contents($real_file))) {
		if (isset($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]) && is_array($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]))
			$whitelist = array_flip($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]);
		else
			$whitelist = array();
		foreach ($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"] as $whitelist_file=>$non_threats) {
			if (is_array($non_threats) && count($non_threats) > 1) {
				if (isset($non_threats[0]))
					unset($non_threats[0]);
				$whitelist = array_merge($whitelist, $non_threats);
			}
		}
		if (isset($whitelist[md5($GLOBALS["wm"]["tmp"]["file_contents"]).'O'.$filesize]))
			return wm_return_threat($className, "face.png?$className", $file, $threat_link);
		$GLOBALS["wm"]["tmp"]["new_contents"] = $GLOBALS["wm"]["tmp"]["file_contents"];
		if (isset($GLOBALS["wm"]["log"]["settings"]["check_custom"]) && strlen($GLOBALS["wm"]["log"]["settings"]["check_custom"]) && isset($_GET['eli']) && substr($GLOBALS["wm"]["log"]["settings"]["check_custom"], 0, 1) == '/' && ($found = wm_check_threat($GLOBALS["wm"]["log"]["settings"]["check_custom"])))
			$className = "known";
		else {
			$path = str_replace("//", "/", "/".str_replace("\\", "/", substr($file, strlen(ABSPATH))));
			if (isset($_SESSION["wm_debug"])) {
				$_SESSION["wm_debug"]["file"] = $file;
				$_SESSION["wm_debug"]["last"]["total"] = microtime(true);
			}
			foreach ($GLOBALS["wm"]["tmp"]["threat_levels"] as $threat_level) {
				if (isset($_SESSION["wm_debug"])) {
					$_SESSION["wm_debug"]["threat_level"] = $threat_level;
					$_SESSION["wm_debug"]["last"]["threat_level"] = microtime(true);
				}
				if (in_array($threat_level, $GLOBALS["wm"]["log"]["settings"]["check"]) && !$found && isset($GLOBALS["wm"]["tmp"]["definitions_array"][$threat_level]) && ($threat_level != "wp_core" || (substr($file, 0, strlen(ABSPATH)) == ABSPATH && isset($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]["$path"]))) && (!array_key_exists($threat_level, $GLOBALS["wm"]["tmp"]["threat_files"]) || (substr($file."e", (-1 * strlen($GLOBALS["wm"]["tmp"]["threat_files"][$threat_level]."e"))) == $GLOBALS["wm"]["tmp"]["threat_files"][$threat_level]."e")) && ($found = wm_check_threat($GLOBALS["wm"]["tmp"]["definitions_array"][$threat_level],$file)))
					$className = $threat_level;
			}
			if (isset($_SESSION["wm_debug"])) {
				$file_time = round(microtime(true) - $_SESSION["wm_debug"]["last"]["total"], 5);
				if (isset($_SESSION["wm_debug"]["total"]["total"]))
					$_SESSION["wm_debug"]["total"]["total"] += $file_time;
				else
					$_SESSION["wm_debug"]["total"]["total"] = $file_time;
				if (isset($_SESSION["wm_debug"]["total"]["count"]))
					$_SESSION["wm_debug"]["total"]["count"] ++;
				else
					$_SESSION["wm_debug"]["total"]["count"] = 1;
				if (!isset($_SESSION["wm_debug"]["total"]["least"]) || $file_time < $_SESSION["wm_debug"]["total"]["least"])
					$_SESSION["wm_debug"]["total"]["least"] = $file_time;
				if (!isset($_SESSION["wm_debug"]["total"]["most"]) || $file_time > $_SESSION["wm_debug"]["total"]["most"])
					$_SESSION["wm_debug"]["total"]["most"] = $file_time;
			}
		}
	} else {
		$GLOBALS["wm"]["tmp"]["file_contents"] = (is_file($real_file)?(is_readable($real_file)?(filesize($real_file)?__("Failed to read file contents!",'wm'):__("Empty file!",'wm')):(isset($_GET["wp-mechanic"])?(@chmod($real_file, $wm_chmod_file)?__("Fixed file permissions! (try again)",'wm'):__("File permissions read-only!",'wm')):__("File not readable!",'wm'))):__("File does not exist!",'wm'));
//		$threat_link = wm_error_link($GLOBALS["wm"]["tmp"]["file_contents"], $real_file);
		$className = "errors";
	}
	if (count($GLOBALS["wm"]["tmp"]["threats_found"])) {
		$threat_link = $lt.'a target="wm_iFrame" href="'.admin_url('admin-ajax.php?action=wm_scan&'.wm_set_nonce(__FUNCTION__."431").'&mt='.$GLOBALS["wm"]["tmp"]["mt"].'&wm_scan='.$clean_file.preg_replace('/\&(wm_scan|mt|wm_mt|action)=/', '&last_\1=', isset($_SERVER["QUERY_STRING"])&&strlen($_SERVER["QUERY_STRING"])?"&".$_SERVER["QUERY_STRING"]:"")).'" id="list_'.$clean_file.'" onclick="loadIframe(\''.str_replace("\"", "&quot;", $lt.'div style="float: left; white-space: nowrap;"'.$gt.__("Examine File",'wm').' ... '.$lt.'/div'.$gt.$lt.'div style="overflow: hidden; position: relative; height: 20px;"'.$gt.$lt.'div style="position: absolute; right: 0px; text-align: right; width: 9000px;"'.$gt.htmlspecialchars(wm_strip4java($file), ENT_NOQUOTES)).$lt.'/div'.$gt.$lt.'/div'.$gt.'\');" class="wm_plugin"'.$gt;
		if ($className == "errors") {
$GLOBALS["wm"]["tmp"]["debug_fix"]="errors";
			$threat_link = wm_error_link($GLOBALS["wm"]["tmp"]["file_contents"], $file);
			$imageFile = "/blocked";
		} elseif ($className != "potential") {
			if (isset($_POST["wm_fix"]) && is_array($_POST["wm_fix"]) && in_array($clean_file, $_POST["wm_fix"])) {
$GLOBALS["wm"]["tmp"]["debug_fix"]="wm_fix";
				if (wm_get_nonce()) {
					if ($className == "timthumb") {
						if (($source = wm_get_URL("http://$className.googlecode.com/svn/trunk/$className.php")) && strlen($source) > 500)
							$GLOBALS["wm"]["tmp"]["new_contents"] = $source;
						else
							$GLOBALS["wm"]["tmp"]["file_contents"] = "";
					} elseif ($className == 'wp_core') {
						$path = str_replace("//", "/", "/".str_replace("\\", "/", substr($file, strlen(ABSPATH))));
						if (substr($file, 0, strlen(ABSPATH)) == ABSPATH && isset($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]["$path"]) && ($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]["$path"] != md5($GLOBALS["wm"]["tmp"]["file_contents"])."O".strlen($GLOBALS["wm"]["tmp"]["file_contents"])) && ($source = wm_get_URL("http://core.svn.wordpress.org/tags/$wp_version$path")) && ($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]["$wp_version"]["$path"] == md5($source)."O".strlen($source)))
							$GLOBALS["wm"]["tmp"]["new_contents"] = $source;
						else
							$GLOBALS["wm"]["tmp"]["file_contents"] = "";
					} else {
						$wm_no_contents = trim(preg_replace('/\/\*.*?\*\/\s*/s', "", $GLOBALS["wm"]["tmp"]["new_contents"]));
						$wm_no_contents = trim(preg_replace('/\n\s*\/\/.*/', "", $wm_no_contents));
						$wm_no_contents = trim(preg_replace('/'.$lt.'\?(php)?\s*(\?'.$gt.'|$)/is', "", $wm_no_contents));
						if (strlen($wm_no_contents))
							$GLOBALS["wm"]["tmp"]["new_contents"] = trim(preg_replace('/'.$lt.'\?(php)?\s*(\?'.$gt.'|$)/is', "", $GLOBALS["wm"]["tmp"]["new_contents"]));
						else
							$GLOBALS["wm"]["tmp"]["new_contents"] = "";
					}
					if (strlen($GLOBALS["wm"]["tmp"]["file_contents"]) > 0 && (($Q_post = wm_write_quarantine($file, $className)) !== false) && ((strlen($GLOBALS["wm"]["tmp"]["new_contents"])==0 && isset($_GET["wp-mechanic"]) && @unlink($file)) || (($Write_File = wm_file_put_contents($file, $GLOBALS["wm"]["tmp"]["new_contents"])) !== false))) {
						echo __("Success!",'wm');
						return "/*--{$gt}*"."/\nfixedFile('$clean_file');\n/*{$lt}!--*"."/";
					} else {
						echo __("Failed:",'wm').' '.(strlen($GLOBALS["wm"]["tmp"]["file_contents"])?((is_writable(dirname($file)) && is_writable($file))?(($Q_post===false)?__("failed to quarantine!",'wm')." (".$wpdb->last_error.")":((isset($Write_File)&&$Write_File)?"Q=$Q_post: ".__("reason unknown!",'wm'):"Q=$Q_post: ".__("failed to write!",'wm'))):__("file not writable!",'wm')):__("no file contents!",'wm'));
						if (isset($_GET["wp-mechanic"]))
							echo 'uid='.getmyuid().'('.get_current_user().'),gid='.getmygid().($lt.'br'.$gt.$lt.'pre'.$gt.'file_stat'.print_r(stat($file), true));
						return "/*--{$gt}*"."/\nfailedFile('$clean_file');\n/*{$lt}!--*"."/";
					}
				} else {
					echo wm_Invalid_Nonce(__("Failed: ",'wm'));
					return "/*--{$gt}*"."/\nfailedFile('$clean_file');\n/*{$lt}!--*"."/";
				}
			}
$GLOBALS["wm"]["tmp"]["debug_fix"]=isset($_POST["wm_fix"])?"wm_fix=".htmlspecialchars(print_r($_POST["wm_fix"],1)):"!potential";
			$threat_link = $lt.'input type="checkbox" name="wm_fix[]" value="'.$clean_file.'" id="check_'.$clean_file.(($className != "wp_core")?'" checked="'.$className:'').'" /'.$gt.$threat_link;
			$imageFile = "threat";
		} elseif (isset($_POST["wm_fix"]) && is_array($_POST["wm_fix"]) && in_array($clean_file, $_POST["wm_fix"])) {
			echo __("Already Fixed!",'wm');
			return "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
		} else
			$imageFile = "question";
		return wm_return_threat($className, $imageFile, $file, str_replace("wm_plugin", "wm_plugin $className", $threat_link));
	} elseif (isset($_POST["wm_fix"]) && is_array($_POST["wm_fix"]) && in_array($clean_file, $_POST["wm_fix"])) {
$GLOBALS["wm"]["tmp"]["debug_fix"]="Already Fixed";
		echo __("Already Fixed!",'wm');
		return "/*--{$gt}*"."/\nfixedFile('$clean_file');\n/*{$lt}!--*"."/";
	} else {
$GLOBALS["wm"]["tmp"]["debug_fix"]="no threat";
		return wm_return_threat($className, ($className=="scanned"?"checked":"blocked").".gif?$className", $file, $threat_link);
	}
}

function wm_remove_dots($dir) {
	if ($dir != "." && $dir != "..")
		return $dir;
}

function wm_getfiles($dir) {
	$files = false;
	if (is_dir($dir)) {
		if (function_exists("scandir"))
			$files = @scandir($dir);
		if (is_array($files))
			$files = array_filter($files, "wm_remove_dots");
		elseif ($handle = @opendir($dir)) {
			$files = array();
			while (false !== ($entry = readdir($handle)))
				if ($entry != "." && $entry != "..")
					$files[] = "$entry";
			closedir($handle);
		} else
			$files = wm_read_error($dir);
	}
	return $files;
}

function wm_decodeBase64($encoded_string) {
	if (function_exists("base64_decode"))
		$unencoded_string = base64_decode($encoded_string);
	elseif (function_exists("mb_convert_encoding"))
		$unencoded_string = mb_convert_encoding($encoded_string, "UTF-8", "BASE64");
	else
		return "Cannot decode: '$encoded_string'";
	return "'".str_replace("'", "\\'", str_replace("\\", "\\\\", $unencoded_string))."'";
}

function wm_decodeHex($encoded_string) {
	if (strtolower(substr($encoded_string, 0, 2)) == "\\x")
		$dec_string = hexdec($encoded_string);
	else
		$dec_string = octdec($encoded_string);
	return chr($dec_string);
}

function wm_return_threat($className, $imageFile, $fileName, $link = "") {
	global $wm_image_alt;
	$fileNameJS = wm_strip4java(str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $fileName));
	$fileName64 = wm_encode($fileName);
	$li_js = "/*-->*"."/";
	if ($className != "scanned")
		$li_js .= "\n$className++;\ndivx=document.getElementById('found_$className');\nif (divx) {\n\tvar newli = document.createElement('li');\n\tnewli.innerHTML='<img src=\"".wm_strip4java(wm_images_path.$imageFile).".gif\" height=16 width=16 alt=\"".$wm_image_alt[$imageFile]."\" style=\"float: left;\" id=\"$imageFile"."_$fileName64\">".wm_strip4java($link, true).$fileNameJS.($link?"</a>';\n\tdivx.display='block":"")."';\n\tdivx.appendChild(newli);\n}";
	if ($className == "errors")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx) {\n\tdivx.src='".wm_images_path."blocked.gif';\n\tdirerrors++;\n}";
	elseif (is_file($fileName))
	 	$li_js .= "\nscanned++;\n";
	if ($className == "dir")
		$li_js .= "\ndivx=document.getElementById('wait_$fileName64');\nif (divx)\n\tdivx.src='".wm_images_path."face.png';";
	return $li_js."\n/*<!--*"."/";
}

function wm_slash($dir = __FILE__) {
	if (substr($dir.'  ', 1, 1) == ':' || substr($dir.'  ', 0, 1) == "\\")
		return "\\";
	else
		return  '/';
}

function wm_trailingslashit($dir = "") {
	if (substr(' '.$dir, -1) != wm_slash($dir))
		$dir .= wm_slash($dir);
	return $dir;
}

function wm_explode_dir($dir, $pre = '') {
	if (strlen($pre))
		$dir = wm_slash($dir).$pre.$dir;
	return explode(wm_slash($dir), $dir);
}

function wm_html_tags($tags, $inner = array()) {
	$html = "";
	$gt = ">";
	if (!is_array($tags))
		return $html;
	foreach ($tags as $tag => $contents) {
		$html .= ($tag=="html"?"<!DOCTYPE html$gt":"")."<$tag".(isset($inner[$tag])?" ".$inner[$tag]:"").$gt;
		if (is_array($contents))
			$html .= wm_html_tags($contents, $inner);
		else
			$html .= $contents;
		$html .= "</$tag$gt";
	}
	return $html;
}

function wm_write_quarantine($file, $className) {
	global $wpdb;
	$insert = array("post_author"=>wm_get_current_user_id(), "post_content"=>wm_encode($GLOBALS["wm"]["tmp"]["file_contents"]), "post_mime_type"=>md5($GLOBALS["wm"]["tmp"]["file_contents"]), "post_title"=>$file, "ping_status"=>$className, "post_status"=>"private", "post_type"=>"wm_quarantine", "post_content_filtered"=>wm_encode($GLOBALS["wm"]["tmp"]["new_contents"]), "guid"=>wm_Version);//! comment_status post_password post_name to_ping post_parent menu_order";
	$insert["post_date"] = date("Y-m-d H:i:s");
	$insert["post_date_gmt"] = $insert["post_date"];
	if (is_file($file)) {
		if (@filemtime($file))
			$insert["post_modified"] = date("Y-m-d H:i:s", @filemtime($file));
		else
			$insert["post_modified"] = $insert["post_date"];
		if (@filectime($file))
			$insert["post_modified_gmt"] = date("Y-m-d H:i:s", @filectime($file));
		else
			$insert["post_modified_gmt"] = $insert["post_date"];
		if (!($insert["comment_count"] = @filesize($file)))
			$insert["comment_count"] = strlen($GLOBALS["wm"]["tmp"]["file_contents"]);
	}
	if (isset($GLOBALS["wm"]["tmp"]["threats_found"]) && is_array($GLOBALS["wm"]["tmp"]["threats_found"])) {
		$insert["post_excerpt"] = wm_encode(@serialize($GLOBALS["wm"]["tmp"]["threats_found"]));
		$pinged = array();
		foreach ($GLOBALS["wm"]["tmp"]["threats_found"] as $loc => $threat_name) {
			if (isset($GLOBALS["wm"]["tmp"]["definitions_array"]["$className"]["$threat_name"][0]) && isset($GLOBALS["wm"]["tmp"]["definitions_array"]["$className"]["$threat_name"][1]) && strlen($GLOBALS["wm"]["tmp"]["definitions_array"]["$className"]["$threat_name"][0]) == 5 && strlen($GLOBALS["wm"]["tmp"]["definitions_array"]["$className"]["$threat_name"][1]))
				$ping = $GLOBALS["wm"]["tmp"]["definitions_array"]["$className"]["$threat_name"][1];
			else
				$ping = $threat_name;
			if (isset($pinged[$ping]))
				$pinged[$ping]++;
			else
				$pinged[$ping] = 1;
		}
		$insert["pinged"] = wm_encode(@serialize($pinged));
	}
	if ($return = $wpdb->insert($wpdb->posts, $insert))
		return $return;
	else
		die(print_r(array('return'=>($return===false)?"FALSE":$return, 'last_error'=>$wpdb->last_error, 'insert'=>$insert),1));
}

function wm_get_current_user_id() {
	$return = 1;
	if (($current_user = @wp_get_current_user()) && (@$current_user->ID > 1))
		$return = $current_user->ID;
	return $return;
}

function wm_update_status($status, $percent = -1) {
	if (!(isset($GLOBALS["wm"]["log"]["scan"]["start"]) && is_numeric($GLOBALS["wm"]["log"]["scan"]["start"])))
		$GLOBALS["wm"]["log"]["scan"]["start"] = time();
	$microtime = ceil(time()-$GLOBALS["wm"]["log"]["scan"]["start"]);
	wm_update_scan_log(array("scan" => array("microtime" => $microtime, "percent" => $percent)));
	return "/*-->*"."/\nupdate_status('".wm_strip4java($status)."', $microtime, $percent);\n/*<!--*"."/";
}

function wm_flush($tag = "") {
	$output = "";
	if (($output = @ob_get_contents()) && strlen(trim($output)) > 18) {
		@ob_clean();
		$output = preg_replace('/\/\*<\!--\*\/.*?\/\*-->\*\//s', "", "$output/*-->*"."/");
		echo "$output\n//flushed(".strlen(trim($output)).")\n";
		if ($tag)
			echo "\n</$tag>\n";
		if (@ob_get_length())
			@ob_flush();
		if ($tag)
			echo "<$tag>\n/*<!--*"."/";
	}
}

function wm_readdir($dir, $current_depth = 1) {
	global $wm_dirs_at_depth, $wm_dir_at_depth, $wm_total_percent;
	if ($current_depth) {
		@set_time_limit($GLOBALS["wm"]["tmp"]['execution_time']);
		$entries = wm_getfiles($dir);
		if (is_array($entries)) {
			echo wm_return_threat("dirs", "wait", $dir).wm_update_status(sprintf(__("Preparing %s",'wm'), str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir)), $wm_total_percent);
			$files = array();
			$directories = array();
			foreach ($entries as $entry) {
				if (is_dir(wm_trailingslashit($dir).$entry))
					$directories[] = $entry;
				else
					$files[] = $entry;
			}
			if (isset($_GET["wp-mechanic"]) && $_GET["wp-mechanic"] == "trace" && count($files)) {
				$tracer_code = "(base64_decode('".base64_encode('if(isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] == "'.$_SERVER["REMOTE_ADDR"].'" && is_file("'.wm_local_images_path.'../wp-okay/trace.php")) {include_once("'.wm_local_images_path.'../wp-okay/trace.php");wm_debug_trace(__FILE__);}')."'));";
				foreach ($files as $file)
					if (wm_get_ext($file) == "php" && $filecontents = @file_get_contents(wm_trailingslashit($dir).$file))
						wm_file_put_contents(wm_trailingslashit($dir).$file, preg_replace('/^<\?php(?! eval)/is', '<?php eval'.$tracer_code, $filecontents));
			}
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				$wm_dirs_at_depth[$current_depth] = count($directories);
				$wm_dir_at_depth[$current_depth] = 0;
			} else
				$GLOBALS["wm"]["tmp"]["scanfiles"][wm_encode($dir)] = wm_strip4java(str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir));
			foreach ($directories as $directory) {
				$path = wm_trailingslashit($dir).$directory;
				if (isset($_REQUEST["scan_depth"]) && is_numeric($_REQUEST["scan_depth"]) && ($_REQUEST["scan_depth"] != $current_depth) && !in_array($directory, $GLOBALS["wm"]["tmp"]["skip_dirs"])) {
					$current_depth++;
					$current_depth = wm_readdir($path, $current_depth);
				} else {
					echo wm_return_threat("skipdirs", "blocked", $path);
					$wm_dir_at_depth[$current_depth]++;
				}
			}
			if ($_REQUEST["scan_type"] == "Quick Scan") {
				$echo = "";
				echo wm_update_status(sprintf(__("Scanning %s",'wm'), str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir)), $wm_total_percent);
				wm_flush("script");
				foreach ($files as $file)
					echo wm_check_file(wm_trailingslashit($dir).$file);
				echo wm_return_threat("dir", "checked", $dir);
			}
		} else
			echo wm_return_threat("errors", "blocked", $dir, wm_error_link(wm_Failed_to_list_LANGUAGE.' readdir:'.($entries===false?'('.wm_fileperms($dir).')':$entries)));
		@set_time_limit($GLOBALS["wm"]["tmp"]['execution_time']);
		if ($current_depth-- && $_REQUEST["scan_type"] == "Quick Scan") {
			$wm_dir_at_depth[$current_depth]++;
			for ($wm_total_percent = 0, $depth = $current_depth; $depth >= 0; $depth--) {
				echo "\n//(($wm_total_percent / $wm_dirs_at_depth[$depth]) + ($wm_dir_at_depth[$depth] / $wm_dirs_at_depth[$depth])) = ";
				$wm_total_percent = (($wm_dirs_at_depth[$depth]?($wm_total_percent / $wm_dirs_at_depth[$depth]):0) + ($wm_dir_at_depth[$depth] / ($wm_dirs_at_depth[$depth]+1)));
				echo "$wm_total_percent\n";
			}
			$wm_total_percent = floor($wm_total_percent * 100);
			echo wm_update_status(sprintf(__("Scanned %s",'wm'), str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir)), $wm_total_percent);
		}
		wm_flush("script");
	}
	return $current_depth;
}

function wm_sexagesimal($timestamp = 0) {
	if (!is_numeric($timestamp) && strlen($timestamp) == 5) {
		$delim = array("=", "-", "-", " ", ":");
		foreach (str_split($timestamp) as $bit)
			$timestamp .= array_shift($delim).substr("00".(ord($bit)>96?ord($bit)-61:(ord($bit)>64?ord($bit)-55:ord($bit)-48)), -2);
		return "20".substr($timestamp, -14);
	} else {
		$match = '/^(20)?([0-5][0-9])[\-: \/]*(0*[1-9]|1[0-2])[\-: \/]*(0*[1-9]|[12][0-9]|3[01])[\-: \/]*([0-5][0-9])[\-: \/]*([0-5][0-9])$/';
		if (preg_match($match, $timestamp))
			$date = preg_replace($match, "\\2-\\3-\\4-\\5-\\6", $timestamp);
		elseif ($timestamp && strtotime($timestamp))
			$date = date("y-m-d-H-i", strtotime($timestamp));
		else
			$date = date("y-m-d-H-i", time());
		foreach (explode("-", $date) as $bit)
			$date .= (intval($bit)>35?chr(ord("a")+intval($bit)-36):(intval($bit)>9?chr(ord("A")+intval($bit)-10):substr('0'.$bit, -1)));
		return substr($date, -5);
	}
}

if (!function_exists('ur1encode')) { function ur1encode($url) {
	$return = "";
	foreach (str_split($url) as $char)
		$return .= '%'.substr('00'.strtoupper(dechex(ord($char))),-2);
	return $return;
}}

function wm_strip4java($item, $htmlentities = false) {
	return preg_replace("/\\\\/", "\\\\\\\\", str_replace("'", "'+\"'\"+'", preg_replace('/\\+n|\\+r|\n|\r|\0/', "", ($htmlentities?$item:htmlentities($item)))));
}

function wm_error_link($errorTXT, $file = "", $class = "errors") {
	global $post;
	if (is_numeric($file) && isset($post->post_title))
		$onclick = 'loadIframe(\''.str_replace("\"", "&quot;", '<div style="float: left; white-space: nowrap;">'.__("Examine Restorable File",'wm').' ... </div><div style="overflow: hidden; position: relative; height: 20px;"><div style="position: absolute; right: 0px; text-align: right; width: 9000px;">'.wm_strip4java($post->post_title)).'</div></div>\');" href="'.admin_url('admin-ajax.php?action=wm_scan&'.wm_set_nonce(__FUNCTION__."744").'&mt='.$GLOBALS["wm"]["tmp"]["mt"].'&wm_scan='.$file);
	elseif ($file)
		$onclick = 'loadIframe(\''.str_replace("\"", "&quot;", '<div style="float: left; white-space: nowrap;">'.__("Examine File",'wm').' ... </div><div style="overflow: hidden; position: relative; height: 20px;"><div style="position: absolute; right: 0px; text-align: right; width: 9000px;">'.htmlspecialchars(wm_strip4java($file), ENT_NOQUOTES)).'</div></div>\');" href="'.admin_url('admin-ajax.php?action=wm_scan&'.wm_set_nonce(__FUNCTION__."746").'&mt='.$GLOBALS["wm"]["tmp"]["mt"].'&wm_scan='.wm_encode($file).preg_replace('/\&(wm_scan|mt|wm_mt|action)=/', '&last_\1=', isset($_SERVER["QUERY_STRING"])&&strlen($_SERVER["QUERY_STRING"])?"&".$_SERVER["QUERY_STRING"]:""));
	else
		$onclick = 'return false;';
	return "<a title=\"$errorTXT\" target=\"wm_iFrame\" onclick=\"$onclick\" class=\"wm_plugin $class\">";
}

function wm_check_file($file) {
	$filesize = @filesize($file);
	echo "/*-->*"."/\ndocument.getElementById('status_text').innerHTML='Checking ".wm_strip4java($file)." ($filesize bytes)';\n/*<!--*"."/";
	if ($filesize===false)
		echo wm_return_threat("errors", "blocked", $file, wm_error_link(__("Failed to determine file size!",'wm'), $file));
	elseif (($filesize==0) || ($filesize>((isset($_GET["wp-mechanic"])&&is_numeric($_GET["wp-mechanic"]))?$_GET["wp-mechanic"]:1234567)))
		echo wm_return_threat("skipped", "blocked", $file, wm_error_link(__("Skipped because of file size!",'wm')." ($filesize bytes)", $file, "potential"));
	elseif (in_array(wm_get_ext($file), $GLOBALS["wm"]["tmp"]["skip_ext"]) && !(preg_match('/(shim|social[0-9]*)\.png$/i', $file)))
		echo wm_return_threat("skipped", "blocked", $file, wm_error_link(__("Skipped because of file extention!",'wm'), $file, "potential"));
	else {
		try {
			echo @wm_scanfile($file);
			echo "//debug_fix:".$GLOBALS["wm"]["tmp"]["debug_fix"];
		} catch (Exception $e) {
			die("//Exception:".wm_strip4java($e));
		}
	}
	echo "/*-->*"."/\ndocument.getElementById('status_text').innerHTML='Checked ".wm_strip4java($file)."';\n/*<!--*"."/";
}

function wm_read_error($path) {
	global $wm_chmod_file, $wm_chmod_dir;
	$error = error_get_last();
	if (!file_exists($path))
		return " (Path not found)";
	if (!is_readable($path) && isset($_GET["wp-mechanic"]))
		$return = (@chmod($path, (is_dir($path)?$wm_chmod_dir:$wm_chmod_file))?"Fixed permissions":"error: ".preg_replace('/[\r\n]/', ' ', print_r($error,1)));
	else
		$return = (is_array($error) && isset($error["message"])?preg_replace('/[\r\n]/', ' ', print_r($error["message"],1)):"readable?");
	return " ($return [".wm_fileperms($path)."])";
}

function wm_scandir($dir) {
	echo "/*<!--*"."/".wm_update_status(sprintf(__("Scanning %s",'wm'), str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir)));
	wm_flush();
	$li_js = "/*-->*"."/\nscanNextDir(-1);\n/*<!--*"."/";
	if (isset($_GET["wm_skip_dir"]) && $dir == wm_decode($_GET["wm_skip_dir"])) {
		if (isset($_GET["wm_only_file"]) && strlen($_GET["wm_only_file"]))
			echo wm_return_threat("errors", "blocked", wm_trailingslashit($dir).wm_decode($_GET["wm_only_file"]), wm_error_link("Failed to read this file!".wm_read_error(wm_trailingslashit($dir).wm_decode($_GET["wm_only_file"])), wm_trailingslashit($dir).wm_decode($_GET["wm_only_file"])));
		else
			echo wm_return_threat("errors", "blocked", $dir, wm_error_link(__("Failed to read directory!",'wm')).wm_read_error($dir));
	} else {
		$files = wm_getfiles($dir);
		if (is_array($files)) {
			if (isset($_GET["wm_only_file"])) {
				if (strlen($_GET["wm_only_file"])) {
					$path = wm_trailingslashit($dir).wm_decode($_GET["wm_only_file"]);
					if (is_file($path)) {
						wm_check_file($path);
						echo wm_return_threat("dir", "checked", $path);
					}
				} else {
					foreach ($files as $file) {
						$path = wm_trailingslashit($dir).$file;
						if (is_file($path)) {
							$file_ext = wm_get_ext($file);
							$filesize = @filesize($path);
							if ((in_array($file_ext, $GLOBALS["wm"]["tmp"]["skip_ext"]) && !(preg_match('/social[0-9]*\.png$/i', $file))) || ($filesize==0) || ($filesize>((isset($_GET["wp-mechanic"])&&is_numeric($_GET["wp-mechanic"]))?$_GET["wp-mechanic"]:1234567)))
								echo wm_return_threat("skipped", "blocked", $path, wm_error_link(sprintf(__('Skipped because of file size (%1$s bytes) or file extention (%2$s)!','wm'), $filesize, $file_ext), $file, "potential"));
							else
								echo "/*-->*"."/\nscanfilesArKeys.push('".wm_encode($dir)."&wm_only_file=".wm_encode($file)."');\nscanfilesArNames.push('Re-Checking ".wm_strip4java($path)."');\n/*<!--*"."/".wm_return_threat("dirs", "wait", $path);
						}
					}
					echo wm_return_threat("dir", "question", $dir);
				}
			} else {
				foreach ($files as $file) {
					$path = wm_trailingslashit($dir).$file;
					if (is_file($path)) {
						if (isset($_GET["wm_skip_file"]) && is_array($_GET["wm_skip_file"]) && in_array($path, $_GET["wm_skip_file"])) {
							$li_js .= "/*-->*"."/\n//skipped $path;\n/*<!--*"."/";
							if ($path == $_GET["wm_skip_file"][count($_GET["wm_skip_file"])-1])
								echo wm_return_threat("errors", "blocked", $path, wm_error_link(__("Failed to read file!",'wm'), $path));
						} else {
							wm_check_file($path);
						}
					}
				}
				echo wm_return_threat("dir", "checked", $dir);
			}
		} else
			echo wm_return_threat("errors", "blocked", $dir, wm_error_link(wm_Failed_to_list_LANGUAGE.' scandir:'.($files===false?' (FALSE)':$files)));
	}
	echo wm_update_status(sprintf(__("Scanned %s",'wm'), str_replace(dirname($GLOBALS["wm"]["log"]["scan"]["dir"]), "...", $dir)));
	wm_update_scan_log(array("scan" => array("finish" => time())));
	return $li_js;
}

function wm_reset_settings($item, $key) {
	$key_parts = explode("_", $key."_");
	if (strlen($key_parts[0]) != 4 && $key_parts[0] != "exclude")
		unset($GLOBALS["wm"]["tmp"]["settings_array"][$key]);
}

$GLOBALS["wm"]["tmp"]["default_ext"] .= "com";
wm_define("wm_plugin_home", $GLOBALS["wm"]["tmp"]["protocol"].'http://www.websitedesignwebsitedevelopment.com/');
wm_define("wm_update_home", "http://www.websitedesignwebsitedevelopment.com/".wm_installation_key.'/');
wm_define("wm_blog_home", $GLOBALS["wm"]["tmp"]["protocol"].'//wordpress.'.$GLOBALS["wm"]["tmp"]["default_ext"]);
$GLOBALS["wm"]["tmp"]["Definition"]["Default"] = "CCIGG";
if (!(isset($GLOBALS["wm"]["tmp"]["definitions_array"]) && is_array($GLOBALS["wm"]["tmp"]["definitions_array"]) && count($GLOBALS["wm"]["tmp"]["definitions_array"])))
	$GLOBALS["wm"]["tmp"]["definitions_array"] = array("potential"=>array(
		"eval"=>array("CCIGG", "/[^a-z_\\/'\"]eval\\(.+\\)+\\s*;/i"),
		"preg_replace /e"=>array("CCIGG", "/preg_replace[\\s*\\(]+(['\"])([\\!\\/\\#\\|\\@\\%\\^\\*\\~]).+?\\2[imsx]*e[imsx]*\\1\\s*,[^,]+,[^\\)]+[\\);\\s]+(\\?>|\$)/i"),
		"auth_pass"=>array("CCIGG", "/\\\$auth_pass\\s*=.+;/i"),
		"function add_action wp_enqueue_script json2"=>array("CCIGG", "/json2\\.min\\.js/i"),
		"Tagged Code"=>array("CCIGG", "/\\#(\\w+)\\#.+?\\#\\/\\1\\#/is"),
		"protected by copyright"=>array("CCIGG", "/\\/\\* This file is protected by copyright law and provided under license. Reverse engineering of this file is strictly prohibited. \\*\\//i")));

function wm_file_put_contents($file, $content) {
	global $wm_chmod_file, $wm_chmod_dir;
	if ((is_dir(dirname($file)) || @mkdir(dirname($file), $wm_chmod_dir, true)) && !is_writable(dirname($file)) && ($wm_chmod_dir = @fileperms(dirname($file))))
		$chmoded_dir = @chmod(dirname($file), 0777);
	if (is_file($file) && !is_writable($file) && ($wm_chmod_file = @fileperms($file)))
		$chmoded_file = @chmod($file, 0666);
	if (function_exists("file_put_contents"))
		$return = @file_put_contents($file, $content);
	elseif ($fp = fopen($file, 'w')) {
		fwrite($fp, $content);
		fclose($fp);
		$return = true;
	} else
		$return = false;
	if ($chmoded_file)
		@chmod($file, $wm_chmod_file);
	if ($chmoded_dir)
		@chmod(dirname($file), $wm_chmod_dir);
	return $return;
}

function wm_scan_log() {
	global $wpdb;
	if ($rs = $wpdb->get_row("SELECT substring_index(option_name, '/', -1) AS `mt`, option_name, option_value FROM `$wpdb->options` where option_name like 'wm_scan_log/%' ORDER BY mt DESC LIMIT 1", ARRAY_A))
		$wm_scan_log = (isset($rs["option_name"])?get_option($rs["option_name"], array()):array());
	$units = array("seconds"=>60,"minutes"=>60,"hours"=>24,"days"=>365,"years"=>10);
	if (isset($wm_scan_log["scan"]["start"]) && is_numeric($wm_scan_log["scan"]["start"])) {
		$time = (time() - $wm_scan_log["scan"]["start"]);
		$ukeys = array_keys($units);
		for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
			$time = floor($time/$units[$ukeys[$key]]);
		if (1 == $time)
			$unit = substr($unit, 0, -1);
		$LastScan = "started $time $unit ago";
		if (isset($wm_scan_log["scan"]["finish"]) && is_numeric($wm_scan_log["scan"]["finish"]) && ($wm_scan_log["scan"]["finish"] >= $wm_scan_log["scan"]["start"])) {
			$time = ($wm_scan_log["scan"]["finish"] - $wm_scan_log["scan"]["start"]);
			for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
				$time = floor($time/$units[$ukeys[$key]]);
			if (1 == $time)
				$unit = substr($unit, 0, -1);
			if ($time)
				$LastScan .= " and ran for $time $unit";
			else
				$LastScan = str_replace("started", "ran", $LastScan);
		} else
			$LastScan .= " and has not finish";
		if (!isset($_GET['Scanlog']))
			$LastScan .= '<a style="float: right;" href="'.admin_url('admin.php?page=wm-View-Restore&Scanlog').'">'.wm_View_Scan_Log_LANGUAGE.'</a><br style="clear: right;">';
	} else
		$LastScan = "never started ";
	return "".(isset($wm_scan_log["scan"]["type"])?$wm_scan_log["scan"]["type"]:"Scan")." $LastScan";
}

function wm_get_URL($URL) {
	$response = "";
	if (function_exists($method = "wp_remote_get")) {
		$request = wp_remote_get($URL, array("sslverify" => false));
		if (200 == wp_remote_retrieve_response_code($request))
			$response = wp_remote_retrieve_body($request);
	}
	if (strlen($response) == 0 && function_exists($method = "curl_exec")) {
		$curl_hndl = curl_init();
		curl_setopt($curl_hndl, CURLOPT_URL, $URL);
		curl_setopt($curl_hndl, CURLOPT_TIMEOUT, 30);
		if (isset($_SERVER['HTTP_REFERER']))
			$SERVER_HTTP_REFERER = $_SERVER['HTTP_REFERER'];
		elseif (isset($_SERVER['HTTP_HOST']))
			$SERVER_HTTP_REFERER = 'HOST://'.$_SERVER['HTTP_HOST'];
		elseif (isset($_SERVER['SERVER_NAME']))
			$SERVER_HTTP_REFERER = 'NAME://'.$_SERVER['SERVER_NAME'];
		elseif (isset($_SERVER['SERVER_ADDR']))
			$SERVER_HTTP_REFERER = 'ADDR://'.$_SERVER['SERVER_ADDR'];
		else
			$SERVER_HTTP_REFERER = 'NULL://not.anything.com';
		curl_setopt($curl_hndl, CURLOPT_REFERER, $SERVER_HTTP_REFERER);
	    if (isset($_SERVER['HTTP_USER_AGENT']))
	    	curl_setopt($curl_hndl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl_hndl, CURLOPT_HEADER, 0);
		curl_setopt($curl_hndl, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($curl_hndl);
		curl_close($curl_hndl);
	}
	if (strlen($response) == 0 && function_exists($method = "file_get_contents"))
		$response = @file_get_contents($URL).'';
	if (isset($_GET["wm_debug"]) && (strlen($response) == 0 || $_GET["wm_debug"] == "wm_get_URL"))
		print_r(array("$method"=>$request, "$URL"=>$response));
	return $response;
}