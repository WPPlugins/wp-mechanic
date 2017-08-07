<?php
/**
 * wm SESSION Start
 * @package wm
*/

if (!defined("wm_SESSION_TIME"))
	define("wm_SESSION_TIME", microtime(true));
if (!@session_id())
	@session_start();
if (isset($_SESSION["wm_SESSION_TIME"]))
	$_SESSION["wm_SESSION_LAST"] = $_SESSION["wm_SESSION_TIME"];
else
	$_SESSION["wm_SESSION_LAST"] = 0;
$_SESSION["wm_SESSION_TIME"] = wm_SESSION_TIME;
