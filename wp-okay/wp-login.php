<?php
/**
 * wm wp-login protection
 * @package wm
*/

if (!defined("wm_REQUEST_METHOD"))
	define("wm_REQUEST_METHOD", (isset($_SERVER["REQUEST_METHOD"])?strtoupper($_SERVER["REQUEST_METHOD"]):"none"));
if ((wm_REQUEST_METHOD == "POST") && isset($_POST["log"]) && isset($_POST["pwd"]) && isset($_POST["session_id"]) && isset($_POST["sess".$_POST["session_id"]]) && is_numeric($_POST["sess".$_POST["session_id"]])) {
	$sess = round($_POST["sess".$_POST["session_id"]] / 60000);
	$time = round(time() / 60);
	if ((($time - $sess) > 2) || (($sess - $time) > 2)) {
		$GLOBALS["wm"]["detected_attacks"] = '&attack[]=NO_JS';
		include(dirname(__FILE__)."/index.php");
	}
} else {
	include(dirname(__FILE__)."/session.php");
	if (!function_exists("wm_update_log_file")) {
		function wm_update_log_file($dont_force_write = true) {
			if (!defined("wm_SESSION_FILE"))
				define("wm_SESSION_FILE", dirname(__FILE__)."/_SESSION/index.php");
			if (is_file(wm_SESSION_FILE))
				include(wm_SESSION_FILE);
			else {
				if (!is_dir(dirname(wm_SESSION_FILE)))
					@mkdir(dirname(wm_SESSION_FILE));
				if (is_dir(dirname(wm_SESSION_FILE)))
					if (!is_file(wm_SESSION_FILE))
						if (file_put_contents(wm_SESSION_FILE, "<?php if (!defined('wm_INSTALL_TIME')) define('wm_INSTALL_TIME', '".wm_SESSION_TIME."');"))
							include(wm_SESSION_FILE);
			}
			if (!defined("wm_INSTALL_TIME"))
				return false;
			else {
				$wm_LOGIN_ARRAY = array("ADDR"=>(isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:"REMOTE_ADDR"), "AGENT"=>(isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:"HTTP_USER_AGENT"), "TIME"=>wm_INSTALL_TIME);
				$wm_LOGIN_KEY = md5(serialize($wm_LOGIN_ARRAY));
				if (!defined("wm_LOG_FILE"))
					define("wm_LOG_FILE", dirname(wm_SESSION_FILE)."/.wm.$wm_LOGIN_KEY.php");
				if (is_file(wm_LOG_FILE))
					include(wm_LOG_FILE);
				if (wm_REQUEST_METHOD == "POST")
					$GLOBALS["wm"]["logins"][$wm_LOGIN_KEY][wm_REQUEST_METHOD][wm_INSTALL_TIME] = $wm_LOGIN_ARRAY;
				else
					$GLOBALS["wm"]["logins"][$wm_LOGIN_KEY][wm_REQUEST_METHOD] = wm_INSTALL_TIME;
				@file_put_contents(wm_LOG_FILE, '<?php $GLOBALS["wm"]["logins"]["'.$wm_LOGIN_KEY.'"]=unserialize(base64_decode("'.base64_encode(serialize($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY])).'"));');
				if (isset($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]) && is_array($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]))
					return $wm_LOGIN_KEY;
				else
					return 0;
			}
		}
	}
	if ((wm_REQUEST_METHOD == "POST") && isset($_POST["log"]) && isset($_POST["pwd"]) && !(isset($wm_LOGIN_KEY) && isset($wm_logins[$wm_LOGIN_KEY]["whitelist"]))) {
		if (!(isset($_SESSION["wm_detected_attacks"]) && $_SESSION["wm_SESSION_LAST"]))
			$GLOBALS["wm"]["detected_attacks"] = '&attack[]=NO_SESSION';
		if (!isset($_SERVER["REMOTE_ADDR"]))
			$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_REMOTE_ADDR';
		if (!isset($_SERVER["HTTP_USER_AGENT"]))
			$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_HTTP_USER_AGENT';
		if (!isset($_SERVER["HTTP_REFERER"]))
			$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_HTTP_REFERER';
		if (!$GLOBALS["wm"]["detected_attacks"]) {
			if (isset($_SESSION["wm_login_attempts"]) && is_numeric($_SESSION["wm_login_attempts"]) && strlen($_SESSION["wm_login_attempts"]."") > 0)
				$_SESSION["wm_login_attempts"]++;
			else {
				if ($wm_LOGIN_KEY = wm_update_log_file()) {
					if (!(isset($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["POST"]) && is_array($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["POST"])))
						$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_LOGIN_ATTEMPTS';
					elseif (!isset($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["GET"]))
						$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_LOGIN_GETS';
					else {
						$_SESSION["wm_login_attempts"] = 0;
						foreach ($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["POST"] as $LOGIN_TIME=>$LOGIN_ARRAY) {
							if ($LOGIN_TIME > $GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["GET"])
								$_SESSION["wm_login_attempts"]++;
							else
								unset($GLOBALS["wm"]["logins"][$wm_LOGIN_KEY]["POST"][$LOGIN_TIME]);
						}
					}
				} else
					$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=NO_LOG_FILE';
			}
			if (!(isset($_SESSION["wm_login_attempts"]) && is_numeric($_SESSION["wm_login_attempts"]) && ($_SESSION["wm_login_attempts"] < 6) && $_SESSION["wm_login_attempts"]))
				$GLOBALS["wm"]["detected_attacks"] .= '&attack[]=TOO_MANY_login_attempts';
		}
		if ($GLOBALS["wm"]["detected_attacks"])
			include(dirname(__FILE__)."/index.php");
	} else {
		if (isset($_SERVER["SCRIPT_FILENAME"]) && basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
			wm_update_log_file();
		$_SESSION["wm_detected_attacks"] = '';
		$_SESSION["wm_login_attempts"] = 0;
	}
}