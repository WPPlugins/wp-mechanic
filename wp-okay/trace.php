<?php // Debug Tracer function by ELI at wm.NET
if (!function_exists("wm_debug_trace")) {
	function wm_debug_trace($file) {
		$mt = microtime(true);
		if (!session_id())
			@session_start();
		if (!isset($_SESSION["wm_traces"]))
			$_SESSION["wm_traces"] = 0;
		if (!isset($_SESSION["wm_trace_includes"]))
			$_SESSION["wm_trace_includes"] = array();
		if (isset($_SESSION["wm_trace_includes"][$_SESSION["wm_traces"]][$file]))
			$_SESSION["wm_traces"] = $mt;
		if (!$wm_headers_sent && $wm_headers_sent = headers_sent($filename, $linenum)) {
			if (!$filename)
				$filename = __("an unknown file",'wm');
			if (!is_numeric($linenum))
				$linenum = __("unknown",'wm');
			$mt .= sprintf(__(': Headers sent by %1$s on line %2$s.','wm'), $filename, $linenum);
		}
		if (!(isset($_SESSION["wm_OBs"]) && is_array($_SESSION["wm_OBs"])))
			$_SESSION["wm_OBs"] = array();
		if (($OBs = ob_list_handlers()) && is_array($OBs) && (count($_SESSION["wm_OBs"]) != count($OBs))) {
			$mt .= print_r(array("ob"=>ob_list_handlers()),1);
			$_SESSION["wm_OBs"] = $OBs;
		}
		$_SESSION["wm_trace_includes"][$_SESSION["wm_traces"]][$file] = $mt;
		if (isset($_GET["wm_traces"]) && count($_SESSION["wm_trace_includes"][$_SESSION["wm_traces"]]) > $_GET["wm_includes"]) {
			$_SESSION["wm_traces"] = $mt;
			foreach ($_SESSION["wm_trace_includes"] as $trace => $array)
				if ($trace < $_GET["wm_traces"])
					unset($_SESSION["wm_trace_includes"][$trace]);
			die(print_r(array("<a href='?wm_traces=".substr($_SESSION["wm_traces"], 0, 10)."'>".substr($_SESSION["wm_traces"], 0, 10)."</a><pre>",$_SESSION["wm_trace_includes"],"<pre>")));
		}
	}
}