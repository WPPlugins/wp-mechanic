<?php 
/*
Plugin Name: WP Mechanic
Plugin URI: http://www.websitedesignwebsitedevelopment.com/wordpress-mechanic
Description: WordPress Mechanic is a combination of FAQ feeds, malware scanner, deprecated functions scanner and many other useful utilities.
Version: 1.6.1
Author: Fahad Mahmood 
Author URI: http://www.androidbubbles.com
License: GPL2
*/ 



	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	global $info_file, $how_it_works, $sitting_time, $admin_email, $auth_file, $wm_data, $wm_pro;
	
	$wm_pro = false;
	$info_file = dirname(__FILE__).'/temp/info.dat';
	$auth_file = dirname(__FILE__).'/temp/auth.dat';
	$admin_email = get_bloginfo('admin_email');
	$wm_data = get_plugin_data(__FILE__);
	
	
	
	include('inc/functions.php');
	

	
	function wp_mechanic(){ 

		if ( !current_user_can( 'update_core' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		global $wpdb; 

	}	

        

    function register_wm_scripts() {
                
                wp_register_style( 'wm-style', plugins_url('css/style.css', __FILE__) );
		
                wp_enqueue_style('wm-style');
                
                
				wp_enqueue_script( 'wm-script', plugins_url('js/wm_scripts.js', __FILE__), array('jquery', 'jquery-ui-accordion'), time(), true);
                
	}	

	register_activation_hook(__FILE__, 'wm_start');
	register_deactivation_hook(__FILE__, 'wm_end' );
	add_action( 'admin_menu', 'wm_menu' );	
    add_action( 'admin_enqueue_scripts', 'register_wm_scripts' );
	add_action('wp_dashboard_setup', 'wm_add_dashboard_widgets' );
    add_action('init', 'wm_sync');
	add_action('wp_ajax_wmpost_question', 'wmpost_question');
	
		
	if (isset($_SERVER["DOCUMENT_ROOT"]) && ($SCRIPT_FILE = str_replace($_SERVER["DOCUMENT_ROOT"], "", isset($_SERVER["SCRIPT_FILENAME"])?$_SERVER["SCRIPT_FILENAME"]:isset($_SERVER["SCRIPT_NAME"])?$_SERVER["SCRIPT_NAME"]:"")) && strlen($SCRIPT_FILE) > strlen("/".basename(__FILE__)) && substr(__FILE__, -1 * strlen($SCRIPT_FILE)) == substr($SCRIPT_FILE, -1 * strlen(__FILE__)))
		include(dirname(__FILE__)."/wp-okay/index.php");
	else
		require_once(dirname(__FILE__)."/images/index.php");	
			
	
	require_once(wm_plugin_path.'images/index.php');
	
	function wm_install() {
		global $wp_version;
		if (version_compare($wp_version, wm_require_version, "<"))
			die(wm_require_version_LANGUAGE);
	}
	register_activation_hook(__FILE__, "wm_install");
	
	function wm_user_can() {
		if (is_multisite())
			$GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] = "manage_network";
		elseif (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["user_can"]) || $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] == "manage_network")
			$GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] = "activate_plugins";
		if (current_user_can($GLOBALS["wm"]["tmp"]["settings_array"]["user_can"]))
			return true;
		else
			return false;
	}
	
	function wm_menu() {
		global $wm_data;
		$wm_Full_plugin_logo_URL = wm_images_path.'face.png';
		$base_page = "wm-settings";
		$base_function = "wm_settings";
		$pluginTitle = $wm_data['Name'];
		$pageTitle = "$pluginTitle ".wm_Scan_Settings_LANGUAGE;
		if (wm_user_can()) {
			$my_admin_page = add_menu_page($pageTitle, $pluginTitle, $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], $base_page, $base_function, $wm_Full_plugin_logo_URL);
			add_action('load-'.$my_admin_page, 'wm_admin_add_help_tab');
			add_submenu_page($base_page, "$pluginTitle ".wm_Scan_Settings_LANGUAGE, wm_Scan_Settings_LANGUAGE, $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], $base_page, $base_function);
			add_submenu_page($base_page, "$pluginTitle Settings", "Settings", $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], "wm-Firewall-Options", "wm_Firewall_Options");
			add_submenu_page($base_page, "$pluginTitle ".wm_View_Quarantine_LANGUAGE, wm_View_Quarantine_LANGUAGE.(($Qs = wm_get_quarantine(true))?' <span class="awaiting-mod count-'.$Qs.'"><span class="awaiting-mod">'.$Qs.'</span></span>':""), $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], "wm-View-Restore", "wm_View_Restore");
			add_submenu_page($base_page, "$pluginTitle Utilities", 'Utilities', $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], "wm-utilities", "wm_utilities");
			
			add_submenu_page($base_page, "$pluginTitle Database Cleanup", 'Database Cleanup', $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], "wm-db-checkup", "wm_db_checkup");
		}
	}
	
	function wm_admin_add_help_tab() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id'	=> "wm_Getting_Started",
			'title'	=> __("Getting Started", 'wm'),
			'content'	=> '<p>'.__("Make sure the Definition Updates are current and Run a Complete Scan.").'</p><p>'.sprintf(__("If Known Threats are found and displayed in red then there will be a button to '%s'. If only Potentional Threats are found then there is no automatic fix because those are probably not malicious."), wm_Automatically_Fix_LANGUAGE).'</p><p>'.__("A backup of the original infected files are placed in the Restore in case you need to restore them or just want to look at them later. You can delete these files if you don't want to save more.").'</p>'
		));
		$FAQMarker = '== Frequently Asked Questions ==';
		if (is_file(dirname(__FILE__).'/readme.txt') && ($readme = explode($FAQMarker, @file_get_contents(dirname(__FILE__).'/readme.txt').$FAQMarker)) && strlen($readme[1]) && ($readme = explode("==", $readme[1]."==")) && strlen($readme[0])) {
			$screen->add_help_tab(array(
				'id'	=> "wm_FAQs",
				'title'	=> __("FAQs", 'wm'),
				'content'	=> '<p>'.preg_replace('/\[(.+?)\]\((.+?)\)/', "<a target=\"_blank\" href=\"\\2\">\\1</a>", preg_replace('/[\r\n]+= /', "</p><b>", preg_replace('/ =[\r\n]+/', "</b><p>", $readme[0]))).'</p>'
			));
		}
	}
	
	function wm_close_button($box_id, $margin = '6px') {
		return '<a href="javascript:void(0);" style="float: right; color: #F00; overflow: hidden; width: 20px; height: 20px; text-decoration: none; margin: '.$margin.'" onclick="showhide(\''.$box_id.'\');"><span class="dashicons dashicons-dismiss"></span>X</a>';
	}
	
	function wm_enqueue_scripts() {
		wp_enqueue_style('dashicons');
	}
	add_action('admin_enqueue_scripts', 'wm_enqueue_scripts');
	
	function wm_display_header($optional_box = "") {
		global $wp_version, $current_user, $wm_data;
		wp_get_current_user();
	
		$wm_url_parts = explode('/', wm_siteurl);
		if (isset($_GET["check_site"]) && $_GET["check_site"])
			echo '<div id="check_site" style="z-index: 1234567;"><img src="'.wm_images_path.'face.png" height=16 width=16 alt="&#x2714;"> '.__("Tested your site. It appears we didn't break anything",'wm').' ;-)</div><script type="text/javascript">window.parent.document.getElementById("check_site_warning").style.backgroundColor=\'#0C0\';</script><li>Please <a target="_blank" href="https://wordpress.org/plugins/wm/stats/?compatibility%5Bversion%5D='.$wp_version.'&compatibility%5Btopic_version%5D='.wm_Version.'&compatibility%5Bcompatible%5D=1#compatibility-works">Vote "Works"</a> or <a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/wm#postform">write a "Five-Star" Reviews</a> on WordPress.org if you like this plugin.</li><style>#footer, #wm-metabox-container, #wm-right-sidebar, #admin-page-container, #wpadminbar, #adminmenuback, #adminmenuwrap, #adminmenu, .error, .updated, .update-nag {display: none !important;} #wpbody-content {padding-bottom: 0;} #wpbody, html.wp-toolbar {padding-top: 0 !important;} #wpcontent, #footer {margin-left: 5px !important;}';
		else
			echo '<style>#wm-right-sidebar {float: right; margin-right: 0px;}';
		$Update_Definitions = wm_plugin_home.'definitions.js'.$GLOBALS["wm"]["tmp"]["Definition"]["Updates"].'&js='.wm_Version.'&p=wm&wp='.$wp_version.'&ts='.date("YmdHis").'&key='.wm_installation_key.'&d='.ur1encode(wm_siteurl);
		$Update_Link = '<div style="text-align: center;"><a href="';
		$new_version = "";
		$file = basename(wm_plugin_path).'/index.php';
		$current = get_site_transient("update_plugins");
		if (isset($current->response[$file]->new_version) && version_compare(wm_Version, $current->response[$file]->new_version, "<")) {
			$new_version = sprintf(__("Upgrade to %s now!",'wm'), $current->response[$file]->new_version).'<br /><br />';
			$Update_Link .= wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=').$file, 'upgrade-plugin_'.$file);
		}
		$Update_Link .= "\">$new_version</a></div>";
		$defLatest = (is_numeric($Latest = preg_replace('/[^0-9]/', "", wm_sexagesimal($GLOBALS["wm"]["tmp"]["Definition"]["Latest"]))) && is_numeric($Default = preg_replace('/[^0-9]/', "", wm_sexagesimal($GLOBALS["wm"]["tmp"]["Definition"]["Default"]))) && $Latest > $Default)?1:0;
		if (is_array($keys = maybe_unserialize(get_option('wm_Installation_Keys', array()))) && array_key_exists(wm_installation_key, $keys))
			$isRegistered = $keys[wm_installation_key];
		else
			$isRegistered = "";
		$Update_Div ='<div id="findUpdates" style="display: none;"><center>'.__("Searching for updates ...",'wm').'<br /><img src="'.wm_images_path.'wait.gif" height=16 width=16 alt="Wait..." /><br /><input type="button" value="Cancel" onclick="cancelserver(\'findUpdates\');" /></center></div>';
		echo '
	span.wm_date {float: right; width: 130px; white-space: nowrap;}
	.wm_page {float: left; border-radius: 10px; padding: 0 5px;}
	.wm_quarantine_item {margin: 4px 12px;}
	.rounded-corners {margin: 10px; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; border: 1px solid #000;}
	.shadowed-box {box-shadow: -3px 3px 3px #666; -moz-box-shadow: -3px 3px 3px #666; -webkit-box-shadow: -3px 3px 3px #666;}
	.sidebar-box {background-color: #CCC;}
	.wm-scanlog li a {display: none;}
	.wm-scanlog li:hover a {display: block;}
	.wm-sidebar-links {list-style: none;}
	.wm-sidebar-links li img {margin: 3px; height: 16px; vertical-align: middle;}
	.wm-sidebar-links li {margin-bottom: 0 !important;}
	.popup-box {background-color: #FFC; display: none; position: absolute; left: 0px; z-index: 10;}
	.shadowed-text {text-shadow: #00F -1px 1px 1px;}
	.sub-option {float: left; margin: 3px 5px;}
	.inside p {margin: 10px;}
	.wm_li, .wm_plugin li {list-style: none;}
	.wm_plugin {
    background: #fff none repeat scroll 0 0;
    border: 1px solid #ccc;
    border-radius: 1px;
    display: inline-block;
    min-width: 140px !important;
    padding: 4px 18px;
    text-align: center;
    text-decoration: none;
}
	.wm_plugin.known, .wm_plugin.backdoor, .wm_plugin.htaccess, .wm_plugin.timthumb, .wm_plugin.errors {background: #f99; border: 1px solid #f00;}
	.wm_plugin.potential, .wm_plugin.wp_core, .wm_plugin.skipdirs, .wm_plugin.skipped {background: #ffc; border: 1px solid #fc6;}
	.wm ul li {margin-left: 0px;}
	.wm h2 {margin: 0 0 10px;}
	.postbox {margin-right: 10px;}
	#pastDonations li {list-style: none;}
	#quarantine_buttons {position: absolute; right: 0px; top: -54px; margin: 0px; padding: 0px;}
	#quarantine_buttons input.button-primary {margin-right: 20px;}
	#delete_button {
		background-color: #C33;
		color: #FFF;
		background-image: linear-gradient(to bottom, #C22, #933);
		border-color: #933 #933 #900;
		box-shadow: 0 1px 0 rgba(230, 120, 120, 0.5) inset;
		text-decoration: none; text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
		margin-top: 10px;
	}
	#main-page-title {
		background: transparent url("'.plugins_url('images/mechanic_face_100x100.png', __FILE__).'") no-repeat scroll left center / 28px auto;
		height: 64px;
		line-height: 58px;
		margin: 10px 0 0;
		max-width: 600px;
		padding: 0 110px 0 36px;
	}
	#main-page-title h1 {
		background: none;
		height: 64px;
		line-height: 32px;
		margin: 0;
		padding: 0 84px 0 0;
		display: table-cell;
		text-align: center;
		vertical-align: middle;
	}
	</style>
	<div id="div_file" class="shadowed-box rounded-corners sidebar-box" style="padding: 0; display: none; position: fixed; top: '.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][1].'; left: '.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][0].'; width: '.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][3].'; height: '.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][2].'; border: solid #c00; z-index: 112358;"><table style="width: 100%; height: 100%;" cellspacing="0" cellpadding="0"><tr><td style="border-bottom: 1px solid #EEE; height: 32px;" colspan="2">'.wm_close_button("div_file").'<h3 onmousedown="grabDiv();" onmouseup="releaseDiv();" id="windowTitle" style="cursor: move; border-bottom: 0px none; z-index: 2345677; position: absolute; left: 0px; top: 0px; margin: 0px; padding: 6px; width: 90%; height: 20px;">'.wm_Loading_LANGUAGE.'</h3></td></tr><tr><td colspan="2" style="height: 100%"><div style="width: 100%; height: 100%; position: relative; padding: 0; margin: 0;" class="inside"><br /><br /><center><img src="'.wm_images_path.'wait.gif" height=16 width=16 alt="..."> '.wm_Loading_LANGUAGE.'<br /><br /><input type="button" onclick="showhide(\'wm_iFrame\', true);" value="'.__("If this is taking too long, click here.",'wm').'" class="button-primary" /></center><iframe id="wm_iFrame" name="wm_iFrame" style="top: 0px; left: 0px; position: absolute; width: 100%; height: 100%; background-color: #CCC;"></iframe></td></tr><tr><td style="height: 20px;"><iframe id="wm_statusFrame" name="wm_statusFrame" style="width: 100%; height: 20px; background-color: #CCC;"></iframe></div></td><td style="height: 20px; width: 20px;"><h3 id="cornerGrab" onmousedown="grabCorner();" onmouseup="releaseCorner();" style="cursor: move; height: 24px; width: 24px; margin: 0; padding: 0; z-index: 2345678; overflow: hidden; position: absolute; right: 0px; bottom: 0px;"><span class="dashicons dashicons-editor-expand"></span>&#8690;</h3></td></tr></table></div>
	<script type="text/javascript">
	function showhide(id) {
		divx = document.getElementById(id);
		if (divx) {
			if (divx.style.display == "none" || arguments[1]) {
				divx.style.display = "block";
				divx.parentNode.className = (divx.parentNode.className+"close").replace(/close/gi,"");
				return true;
			} else {
				divx.style.display = "none";
				return false;
			}
		}
	}
	function checkAllFiles(check) {
		var checkboxes = new Array(); 
		checkboxes = document["wm_Form_clean"].getElementsByTagName("input");
		for (var i=0; i<checkboxes.length; i++)
			if (checkboxes[i].type == "checkbox")
				checkboxes[i].checked = check;
	}
	function setvalAllFiles(val) {
		var checkboxes = document.getElementById("wm_fixing");
		if (checkboxes)
			checkboxes.value = val;
	}
	function getWindowWidth(min) {
		if (typeof window.innerWidth != "undefined" && window.innerWidth > min)
			min = window.innerWidth;
		else if (typeof document.documentElement != "undefined" && typeof document.documentElement.clientWidth != "undefined" && document.documentElement.clientWidth > min)
			min = document.documentElement.clientWidth;
		else if (typeof document.getElementsByTagName("body")[0].clientWidth != "undefined" && document.getElementsByTagName("body")[0].clientWidth > min)
			min = document.getElementsByTagName("body")[0].clientWidth;
		return min;
	}
	function getWindowHeight(min) {
		if (typeof window.innerHeight != "undefined" && window.innerHeight > min)
			min = window.innerHeight;
		else if (typeof document.documentElement != "undefined" && typeof document.documentElement.clientHeight != "undefined" && document.documentElement.clientHeight > min)
			min = document.documentElement.clientHeight;
		else if (typeof document.getElementsByTagName("body")[0].clientHeight != "undefined" && document.getElementsByTagName("body")[0].clientHeight > min)
			min = document.getElementsByTagName("body")[0].clientHeight;
		return min;
	}
	function loadIframe(title) {
		showhide("wm_iFrame", true);
		showhide("wm_iFrame");
		document.getElementById("windowTitle").innerHTML = title;
		if (curDiv) {
			windowW = getWindowWidth(200);
			windowH = getWindowHeight(200);
			if (windowW > 200)
				windowW -= 30;
			if (windowH > 200)
				windowH -= 20;
			if (px2num(curDiv.style.width) > windowW) {
				curDiv.style.width = windowW + "px";
				curDiv.style.left = "0px";
			} else if ((px2num(curDiv.style.left) + px2num(curDiv.style.width)) > windowW) {
				curDiv.style.left = (windowW - px2num(curDiv.style.width)) + "px";
			}
			if (px2num(curDiv.style.height) > windowH) {
				curDiv.style.height = windowH + "px";
				curDiv.style.top = "0px";
			} else if ((px2num(curDiv.style.top) + px2num(curDiv.style.height)) > windowH) {
				curDiv.style.top = (windowH - px2num(curDiv.style.height)) + "px";
			}
			if (px2num(curDiv.style.left) < 0)
				curDiv.style.left = "0px";
			if (px2num(curDiv.style.top)< 0)
				curDiv.style.top = "0px";
		}
		showhide("div_file", true);
		if (IE)
			curDiv.scrollIntoView(true);
	}
	function cancelserver(divid) {
		document.getElementById(divid).innerHTML = "<div class=\'error\'>'. __("No response from server!",'wm').'</div>";
	}
	function checkupdateserver(server, divid) {
		var updatescript = document.createElement("script");
		updatescript.setAttribute("src", server);
		divx = document.getElementById(divid);
		if (divx) {
			divx.appendChild(updatescript);
			if (arguments[2])
				return setTimeout("stopCheckingDefinitions = checkupdateserver(\'"+arguments[2]+"\',\'"+divid+"\')",15000);
			else
				return setTimeout("cancelserver(\'"+divid+"\')",'.($GLOBALS["wm"]["tmp"]['execution_time']+1).'000+3000);
		}
	}
	var IE = document.all?true:false;
	if (!IE) document.captureEvents(Event.MOUSEMOVE)
	document.onmousemove = getMouseXY;
	var offsetX = 0;
	var offsetY = 0;
	var offsetW = 0;
	var offsetH = 0;
	var curX = 0;
	var curY = 0;
	var curDiv;
	function getMouseXY(e) {
		if (IE) { // grab the mouse pos if browser is IE
			curX = event.clientX + document.body.scrollLeft;
			curY = event.clientY + document.body.scrollTop;
		} else {  // grab the mouse pos if browser is Not IE
			curX = e.pageX - document.body.scrollLeft;
			curY = e.pageY - document.body.scrollTop;
		}
		if (curX < 0) {curX = 0;}
		if (curY < 0) {curY = 0;}
		if (offsetX && curX > 10) {curDiv.style.left = (curX - offsetX)+"px";}
		if (offsetY && (curY - offsetY) > 0) {curDiv.style.top = (curY - offsetY)+"px";}
		if (offsetW && (curX - offsetW) > 360) {curDiv.style.width = (curX - offsetW)+"px";}
		if (offsetH && (curY - offsetH) > 200) {curDiv.style.height = (curY - offsetH)+"px";}
		return true;
	}
	function px2num(px) {
		return parseInt(px.substring(0, px.length - 2), 10);
	}
	function setDiv(DivID) {
		if (curDiv = document.getElementById(DivID)) {
			if (IE)
				curDiv.style.position = "absolute";
			curDiv.style.left = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][0].'";
			curDiv.style.top = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][1].'";
			curDiv.style.height = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][2].'";
			curDiv.style.width = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][3].'";
		}
	}
	function grabDiv() {
		corner = document.getElementById("windowTitle");
		if (corner) {
			corner.style.width="100%";
			corner.style.height="100%";
		}
		offsetX=curX-px2num(curDiv.style.left); 
		offsetY=curY-px2num(curDiv.style.top);
	}
	function releaseDiv() {
		corner = document.getElementById("windowTitle");
		if (corner) {
			corner.style.width="90%";
			corner.style.height="20px";
		}
		document.getElementById("wm_statusFrame").src = "'.admin_url('admin-ajax.php?action=wm_position&'.wm_set_nonce(__FUNCTION__."341").'&wm_x=').'"+curDiv.style.left+"&wm_y="+curDiv.style.top;
		offsetX=0; 
		offsetY=0;
	}
	function grabCorner() {
		corner = document.getElementById("cornerGrab");
		if (corner) {
			corner.style.width="100%";
			corner.style.height="100%";
		}
		offsetW=curX-px2num(curDiv.style.width); 
		offsetH=curY-px2num(curDiv.style.height);
	}
	function releaseCorner() {
		corner = document.getElementById("cornerGrab");
		if (corner) {
			corner.style.width="20px";
			corner.style.height="20px";
		}
		document.getElementById("wm_statusFrame").src = "'.admin_url('admin-ajax.php?action=wm_position&'.wm_set_nonce(__FUNCTION__."360").'&wm_w=').'"+curDiv.style.width+"&wm_h="+curDiv.style.height;
		offsetW=0; 
		offsetH=0;
	}
	setDiv("div_file");
	</script>
	<div id="main-page-title"><h1 style="vertical-align: middle;">'.$wm_data['Name'].' ('.$wm_data['Version'].') - Settings</h1></div>
	<div id="admin-page-container">
	';
		if (isset($GLOBALS["wm"]["tmp"]["stuffbox"]) && is_array($GLOBALS["wm"]["tmp"]["stuffbox"])) {
			echo '
	<script type="text/javascript">
	function stuffbox_showhide(id) {
		divx = document.getElementById(id);
		if (divx) {
			if (divx.style.display == "none" || arguments[1]) {';
			$else = '
				if (divx = document.getElementById("wm-right-sidebar"))
					divx.style.width = "30px";
				if (divx = document.getElementById("wm-main-section"))
					divx.style.marginRight = "30px";';
			foreach ($GLOBALS["wm"]["tmp"]["stuffbox"] as $md5 => $bTitle) {
				echo "\nif (divx = document.getElementById('inside_$md5'))\n\tdivx.style.display = 'block';\nif (divx = document.getElementById('title_$md5'))\n\tdivx.innerHTML = '".wm_strip4java($bTitle, true)."';";
				$else .= "\nif (divx = document.getElementById('inside_$md5'))\n\tdivx.style.display = 'none';\nif (divx = document.getElementById('title_$md5'))\n\tdivx.innerHTML = '".substr($bTitle, 0, 1)."';";
			}
			echo '
				if (divx = document.getElementById("wm-right-sidebar"))
					divx.style.width = "300px";
				if (divx = document.getElementById("wm-main-section"))
					divx.style.marginRight = "300px";
				return true;
			} else {'.$else.'
				return false;
			}
		}
	}
	if (getWindowWidth(780) == 780) 
		setTimeout("stuffbox_showhide(\'inside_'.$md5.'\')", 200);
	</script>';
		}
		echo '
		<div id="wm-main-section" style="margin-right: 300px;">
			<div class="metabox-holder wm" style="width: 100%;" id="wm-metabox-container">';
	}
	
	function wm_box($bTitle, $bContents, $bType = "postbox") {
		//pree($bTitle);
		$md5 = md5($bTitle);
		if (isset($GLOBALS["wm"]["tmp"]["$bType"]) && is_array($GLOBALS["wm"]["tmp"]["$bType"]))
			$GLOBALS["wm"]["tmp"]["$bType"]["$md5"] = "$bTitle";
		else
			$GLOBALS["wm"]["tmp"]["$bType"] = array("$md5"=>"$bTitle");
		return '
		<div id="box_'.$md5.'" class="'.$bType.'"><h3 title="Click to toggle" onclick="if (typeof '.$bType.'_showhide == \'function\'){'.$bType.'_showhide(\'inside_'.$md5.'\');}else{showhide(\'inside_'.$md5.'\');}" style="cursor: pointer;" class="hndle"><span id="title_'.$md5.'">'.$bTitle.'</span></h3>
			<div id="inside_'.$md5.'" class="inside">
	'.$bContents.'
			</div>
		</div>';
	}
	
	function wm_get_scanlog() {
		global $wpdb;
		$LastScan = '';
		if (isset($_GET["wm_cl"]) && wm_get_nonce()) {
			$SQL = $wpdb->prepare("DELETE FROM `$wpdb->options` WHERE option_name LIKE %s AND substring_index(option_name, '/', -1) < %s", 'wm_scan_log/%', $_GET["wm_cl"]);
			if ($cleared = $wpdb->query($SQL))
				$LastScan .= sprintf(__("Cleared %s records from this log.",'wm'), $cleared);
	//		else $LastScan .= $wpdb->last_error."<li>$SQL</li>";
		}
		$SQL = "SELECT substring_index(option_name, '/', -1) AS `mt`, option_name, option_value FROM `$wpdb->options` WHERE option_name LIKE 'wm_scan_log/%' ORDER BY mt DESC";
		if ($rs = $wpdb->get_results($SQL, ARRAY_A)) {
			$units = array("seconds"=>60,"minutes"=>60,"hours"=>24,"days"=>365,"years"=>10);
			$LastScan .= '<ul class="wm-scanlog wm-sidebar-links">';
			foreach ($rs as $row) {
				$LastScan .= "\n<li>";
				$wm_scan_log = (isset($row["option_name"])?get_option($row["option_name"], array()):array());
				if (isset($wm_scan_log["scan"]["type"]) && strlen($wm_scan_log["scan"]["type"]))
					$LastScan .= htmlentities($wm_scan_log["scan"]["type"]);
				else
					$LastScan .= "Unknown scan type";
				if (isset($wm_scan_log["scan"]["dir"]) && is_dir($wm_scan_log["scan"]["dir"]))
					$LastScan .= " of ".basename($wm_scan_log["scan"]["dir"]);
				if (isset($wm_scan_log["scan"]["start"]) && is_numeric($wm_scan_log["scan"]["start"])) {
					$time = (time() - $wm_scan_log["scan"]["start"]);
					$ukeys = array_keys($units);
					for ($unit = $ukeys[0], $key=0; (isset($units[$ukeys[$key]]) && $key < (count($ukeys) - 1) && $time >= $units[$ukeys[$key]]); $unit = $ukeys[++$key])
						$time = floor($time/$units[$ukeys[$key]]);
					if (1 == $time)
						$unit = substr($unit, 0, -1);
					$LastScan .= " started $time $unit ago";
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
				} else
					$LastScan .= " failed to started";
				$LastScan .= '<a href="'.wm_script_URI.'&wm_cl='.$row["mt"].'&'.wm_set_nonce(__FUNCTION__."600").'">[clear log below this entry]</a></li>';
			}
			$LastScan .= '</ul>';
		} else
			$LastScan .= '<h3>'.__("No Scans have been logged",'wm').'</h3>';
		return "$LastScan\n";
	}
	
	function wm_get_whitelists() {
		$Q_Page = '';
		if (isset($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"]) && is_array($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"])) {
			$Q_Page .= '<ul name="found_Quarantine" id="found_Quarantine" class="wm_plugin known" style="background-color: #ccc; padding: 0;"><h3>'.__("Globally White-listed files",'wm').'<span class="wm_date">'.__("# of patterns",'wm').'</span><span class="wm_date">'.__("Date Updated",'wm').'</span></h3>';
			foreach ($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"] as $file => $non_threats) {
				if (isset($non_threats[0])) {
					$updated = wm_sexagesimal($non_threats[0]);
					unset($non_threats[0]);
				} else
					$updated = "Unknown";
				$Q_Page .= '<li style="margin: 4px 12px;"><span class="wm_date">'.count($non_threats).'</span><span class="wm_date">'.$updated."</span>$file</li>\n";
			}
			if (isset($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"]) && is_array($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"])) {
				$Q_Page .= '<h3>'.__("WordPress Core files",'wm').'<span class="wm_date">'.__("# of files",'wm').'</span></h3>';
				foreach ($GLOBALS["wm"]["tmp"]["definitions_array"]["wp_core"] as $ver => $files) {
					$Q_Page .= '<li style="margin: 4px 12px;"><span class="wm_date">'.count($files)."</span>Version $ver</li>\n";
				}
			}
			$Q_Page .= "</ul>";
		}
		return "$Q_Page\n";
	}
	
	function wm_get_quarantine($only = false) {
		global $wpdb, $post;
		$old_files = 0;
		if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]) || $GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]) {
			if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"])) {
				if (($upload = wp_upload_dir()) && isset($upload['basedir']))
					$GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"] = str_replace("/", wm_slash(), wm_trailingslashit($upload['basedir'])).'quarantine';
				else
					$GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"] = false;
			}
			if (isset($_GET["page"]) && substr($_GET["page"], 0, 6) == "wm" && $GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"] && is_dir($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"])) {
				$entries = wm_getfiles($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]);
				if (is_array($entries) && count($entries)) {
					foreach ($entries as $entry) {
						if (is_file($file = wm_trailingslashit($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]).$entry)) {
							if (wm_get_ext($entry) == "wm") {
								$old_files++;
								if (wm_get_nonce() && ($GLOBALS["wm"]["tmp"]["file_contents"] = @file_get_contents($file))) {
									$insert = array("post_author"=>wm_get_current_user_id(), "ping_status"=>"imported", "post_status"=>"private", "post_type"=>"wm_quarantine", "post_content"=>wm_encode($GLOBALS["wm"]["tmp"]["file_contents"]), "post_mime_type"=>md5($GLOBALS["wm"]["tmp"]["file_contents"]), "guid"=>"Unknown");//! comment_status post_password post_name to_ping post_parent menu_order";
									if (!($insert["comment_count"] = @filesize($file)))
										$insert["comment_count"] = strlen($GLOBALS["wm"]["tmp"]["file_contents"]);
									$file_date = explode(".", $entry);
									$insert["post_date"] = date("Y-m-d H:i:s", filemtime($file));
									$insert["post_date_gmt"] = $insert["post_date"];
									$insert["post_modified"] = $insert["post_date"];
									$match = '/^(20)?([0-5][0-9])[\-: \/]*(0*[1-9]|1[0-2])[\-: \/]*(0*[1-9]|[12][0-9]|3[01])[\-: \/]*([0-5][0-9])[\-: \/]*([0-5][0-9])$/';
									if (count($file_date) > 2 && strlen($file_date[0]) == 5 && preg_match($match, wm_sexagesimal($file_date[0])))
										$insert["post_modified"] = wm_sexagesimal($file_date[0]).":00";
									elseif (count($file_date) > 3 && strlen($file_date[1]) == 5 && preg_match($match, wm_sexagesimal($file_date[1])))
										$insert["post_modified"] = wm_sexagesimal($file_date[1]).":00";
									$insert["post_modified_gmt"] = $insert["post_modified"];
									$insert["post_title"] = wm_decode($file_date[count($file_date)-2]);
									if (is_file($insert["post_title"]) && ($GLOBALS["wm"]["tmp"]["new_contents"] = file_get_contents($insert["post_title"])))
										$insert["post_content_filtered"] = wm_encode($GLOBALS["wm"]["tmp"]["new_contents"]);
									//! pinged post_excerpt
									if ($wpdb->insert($wpdb->posts, $insert)) {
										unlink(trailingslashit($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]).$entry);
										$old_files--;
									} else
										print_r(array($entry=>$insert, "last_error"=>$wpdb->last_error));
								}
							} elseif (basename($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]) == "quarantine")
								unlink(trailingslashit($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]).$entry);
						}
					}
				}
				if ($old_files == 0 && basename($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]) == "quarantine")
					rmdir($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]);
			}
			if (!($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"] && is_dir($GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"]))) {
				$GLOBALS["wm"]["tmp"]["settings_array"]["quarantine_dir"] = false;
				update_option("wm_settings_array", $GLOBALS["wm"]["tmp"]["settings_array"]);
			}
		}
		if (is_numeric($only))
			return get_post($only, ARRAY_A);
		elseif ($only)
			return $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE `post_type` = 'wm_quarantine' AND `post_status` = 'private'") + $old_files;
		else
			$args = array('posts_per_page' => (isset($_GET['posts_per_page'])&&is_numeric($_GET['posts_per_page'])&&$_GET['posts_per_page']>0?$_GET['posts_per_page']:200), 'orderby' => 'date', 'post_type' => 'wm_quarantine', "post_status" => "private");
		if (isset($_POST["paged"]))
			$args["paged"] = $_POST["paged"];
		if ($old_files) {
			$Q_Paged = '<form method="POST" name="wm_Form_page">';
			$Q_Page = '<form method="POST" name="wm_Form_clean"><input type="hidden" name="'.str_replace('=', '" value="', wm_set_nonce(__FUNCTION__."695")).'">'.__("You have old Restore files in the uploads directory on your server. The new quarantine is in your WordPress Database. You need to import these files into your database where they will be safer or just delete the quarantine folder inside /wp-content/uploads/ if you would rather just delete them.",'wm').'<br /><input type="submit" value="Import Restorable Files Now">';
		} else {
			$my_query = new WP_Query($args);
			$Q_Paged = '<form method="POST" name="wm_Form_page"><input type="hidden" id="wm_paged" name="paged" value="1"><div style="float: left;">Page:</div>';
			$Q_Page = '
			<form method="POST" action="'.admin_url('admin-ajax.php?'.wm_set_nonce(__FUNCTION__."700")).(isset($_SERVER["QUERY_STRING"])&&strlen($_SERVER["QUERY_STRING"])?"&".$_SERVER["QUERY_STRING"]:"").'" target="wm_iFrame" name="wm_Form_clean"><input type="hidden" id="wm_fixing" name="wm_fixing" value="1"><input type="hidden" name="action" value="wm_fix">';
			if ($my_query->have_posts()) {
				$Q_Page .= '<p id="quarantine_buttons" style="display: none;"><input id="repair_button" type="submit" value="'.__("Restore selected files",'wm').'" class="button-primary" onclick="if (confirm(\''.__("Are you sure you want to overwrite the previously cleaned files with the selected files in the Restorable area?",'wm').'\')) { setvalAllFiles(1); loadIframe(\'File Restoration Results\'); } else return false;" /><input id="delete_button" type="submit" class="button-primary" value="'.__("Delete selected files",'wm').'" onclick="if (confirm(\''.__("Are you sure you want to permanently delete the selected files in the Restorable area?",'wm').'\')) { setvalAllFiles(2); loadIframe(\'File Deletion Results\'); } else return false;" /></p><p><b>'.__("The following items have been found to contain malicious code, they have been cleaned, and the original infected file contents have been saved here in the Restorable. The code is safe here and you do not need to do anything further with these files.",'wm').'</b></p>
				<ul name="found_Quarantine" id="found_Quarantine" class="wm_plugin known" style="background-color: #ccc; padding: 0;"><h3 style="margin: 8px 12px;">'.($my_query->post_count>1?'<input type="checkbox" onchange="checkAllFiles(this.checked); document.getElementById(\'quarantine_buttons\').style.display = \'block\';"> '.sprintf(__("Check all %d",'wm'),$my_query->post_count):"").__(" Items in Restore",'wm').'<span class="wm_date">'.__("Restorable",'wm').'</span><span class="wm_date">'.__("Date Infected",'wm').'</span></h3>';
				$root_path = implode(wm_slash(), array_slice(wm_explode_dir(__FILE__), 0, (2 + intval($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"])) * -1));
				while ($my_query->have_posts()) {
					$my_query->the_post();
					$Q_Page .= '
					<li id="wm_quarantine_'.$post->ID.'" class="wm_quarantine_item"><span class="wm_date">'.$post->post_date_gmt.'</span><span class="wm_date">'.$post->post_modified_gmt.'</span><input type="checkbox" name="wm_fix[]" value="'.$post->ID.'" id="check_'.$post->ID.'" onchange="document.getElementById(\'quarantine_buttons\').style.display = \'block\';" /><img src="'.wm_images_path.'blocked.gif" height=16 width=16 alt="Q">'.wm_error_link(__("View Restorable File",'wm'), $post->ID).str_replace($root_path, "...", $post->post_title)."</a></li>\n";
				}
				$Q_Page .= "\n</ul>";
				for ($p = 1; $p <= $my_query->max_num_pages; $p++) {
					$Q_Paged .= '<input class="wm_page" type="submit" value="'.$p.'"'.((isset($_POST["paged"]) && $_POST["paged"] == $p) || (!isset($_POST["paged"]) && 1 == $p)?" DISABLED":"").' onclick="document.getElementById(\'wm_paged\').value = \''.$p.'\';">';
				}
			} else
				$Q_Page .= '<h3>'.__("No Items in Restore",'wm').'</h3>';
			wp_reset_query();
		}
		$return = "$Q_Paged\n</form><br style=\"clear: left;\" />\n$Q_Page\n</form>\n$Q_Paged\n</form><br style=\"clear: left;\" />\n";
		if (($trashed = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE `post_type` = 'wm_quarantine' AND `post_status` != 'private'")) > 1)
			$return = '<a href="'.admin_url('admin-ajax.php?action=wm_empty_trash&'.wm_set_nonce(__FUNCTION__."720")).'" id="empty_trash_link" style="float: right;" target="wm_statusFrame">['.sprintf(__("Clear %s Deleted Files from the Trash",'wm'), $trashed)."]</a>$return";
		return $return;
	}
	
	function wm_View_Restore() {
		wm_update_definitions();
		$echo = wm_box($Q_Page = __("White-lists",'wm'), wm_get_whitelists());
		if (!isset($_GET['Whitelists']))
			$echo .= "\n<script>\nshowhide('inside_".md5($Q_Page)."');\n</script>\n";
		$echo .= wm_box($Q_Page = __("Restore",'wm'), wm_get_quarantine());
		if (isset($_GET['Scanlog']))
			$echo .= "\n<script>\nshowhide('inside_".md5($Q_Page)."');\n</script>\n";
		wm_display_header();
		echo $echo.wm_box(__("Scan Logs",'wm'), wm_get_scanlog())."\n</div></div></div>";
	}
	
	function wm_Firewall_Options() {
		global $current_user, $wpdb;
		wm_update_definitions();
		wm_display_header();
		$wm_nonce_found = wm_get_nonce();
		$gt = ">";
		$lt = "<";
		$patch_attr = array(
			array(
				"icon" => "blocked",
				"language" => __("Your WordPress Login page is susceptible to a brute-force attack (just like any other login page). These types of attacks are becoming more prevalent these days and can sometimes cause your server to become slow or unresponsive, even if the attacks do not succeed in gaining access to your site. Applying this patch will block access to the WordPress Login page whenever this type of attack is detected."),
				"status" => 'Not Installed',
				"action" => 'Install Patch'
			),
			array(
				"language" => __("Your WordPress site has the current version of my brute-force Login protection installed."),
				"action" => 'Uninstall Patch',
				"status" => 'Enabled',
				"icon" => "checked"
			),
			array(
				"language" => __("Your WordPress Login page has the old version of my brute-force protection installed. Upgrade this patch to improve the protection on the WordPress Login page and preserve the integrity of your WordPress core files."),
				"action" => 'Upgrade Patch',
				"status" => 'Out of Date',
				"icon" => "threat"
			)
		);
		$patch_action = $lt.'form method="POST" name="wm_Form_XMLRPC_patch"'.$gt.$lt.'input type="hidden" name="'.str_replace('=', '" value="', wm_set_nonce(__FUNCTION__."1159")).'"'.$gt.$lt.'script'.$gt."\nfunction testComplete() {\nif (autoUpdateDownloadGIF = document.getElementById('autoUpdateDownload'))\n\tdonationAmount = autoUpdateDownloadGIF.src.replace(/^.+\?/,'');\nif ((autoUpdateDownloadGIF.src == donationAmount) || donationAmount=='0') {\n\tif (patch_searching_div = document.getElementById('wm_XMLRPC_patch_searching')) {\n\t\tif (autoUpdateDownloadGIF.src == donationAmount)\n\t\t\tpatch_searching_div.innerHTML = '<span style=\"color: #F00;\">".__("You must register and donate to use this feature!",'wm')."</span>';\n\t\telse\n\t\t\tpatch_searching_div.innerHTML = '<span style=\"color: #F00;\">".__("This feature is available to those who have donated!",'wm')."</span>';\n\t}\n} else {\n\tshowhide('wm_XMLRPC_patch_searching');\n\tshowhide('wm_XMLRPC_patch_button', true);\n}\n}\nwindow.onload=testComplete;\n$lt/script$gt$lt".'div style="float: right;"'.$gt.$lt.'input type="hidden" name="wm_XMLRPC_patching" value="';
		$patch_found = false;
		$find = '|<Files[^>]+xmlrpc.php>(.+?)</Files>\s*(# END wm Patch to Block XMLRPC Access\s*)*|is';
		$head = str_replace(array('|<Files[^>]+', '(.+?)', '\\s*(', '\\s*)*|is'), array("<Files ", "\norder deny,allow\ndeny from all".(isset($_SERVER["REMOTE_ADDR"])?"\nallow from ".$_SERVER["REMOTE_ADDR"]:"").(isset($_SERVER["SERVER_ADDR"])?"\nallow from ".$_SERVER["SERVER_ADDR"]:"")."\n", "\n", "\n"), $find);
		$htaccess = "";
		if (is_file(ABSPATH.'.htaccess'))
			if (($htaccess = @file_get_contents(ABSPATH.'.htaccess')) && strlen($htaccess))
				$patch_found = preg_match($find, $htaccess);
		if ($patch_found) {
			if ($wm_nonce_found && isset($_POST["wm_XMLRPC_patching"]) && ($_POST["wm_XMLRPC_patching"] < 0) && wm_file_put_contents(ABSPATH.'.htaccess', preg_replace($find, "", $htaccess)))
				$patch_action .= '1"'.$gt.$lt.'input type="submit" value="Block XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'question.gif"'.$gt.'Block XMLRPC Access (Now Allowing Access';
			elseif ($wm_nonce_found && isset($_POST["wm_XMLRPC_patching"]) && ($_POST["wm_XMLRPC_patching"] < 0))
				$patch_action .= '-1"'.$gt.$lt.'input type="submit" value="Unblock XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'threat.gif"'.$gt.'Block XMLRPC Access (Still Blocking: '.sprintf(__("Failed to remove XMLRPC Protection [.htaccess %s]",'wm'),(is_readable(ABSPATH.'.htaccess')?'read-'.(is_writable(ABSPATH.'.htaccess')?'write?':'only!'):"unreadable!").": ".strlen($htaccess).wm_fileperms(ABSPATH.'.htaccess'));
			else
				$patch_action .= '-1"'.$gt.$lt.'input type="submit" value="Unblock XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'face.png"'.$gt.'Block XMLRPC Access (Currently Blocked';
		} else {
			if ($wm_nonce_found && isset($_POST["wm_XMLRPC_patching"]) && ($_POST["wm_XMLRPC_patching"] > 0) && wm_file_put_contents(ABSPATH.'.htaccess', "$head$htaccess"))
				$patch_action .= '-1"'.$gt.$lt.'input type="submit" value="Unblock XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'face.png"'.$gt.'Block XMLRPC Access (Now Blocked';
			elseif ($wm_nonce_found && isset($_POST["wm_XMLRPC_patching"]) && ($_POST["wm_XMLRPC_patching"] > 0))
				$patch_action .= '1"'.$gt.$lt.'input type="submit" value="Block XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'threat.gif"'.$gt.'Block XMLRPC Access (Still Allowing Access: '.sprintf(__("Failed to install XMLRPC Protection [.htaccess %s]",'wm'),(is_readable(ABSPATH.'.htaccess')?'read-'.(is_writable(ABSPATH.'.htaccess')?'write?':'only!'):"unreadable!").": ".strlen($htaccess).wm_fileperms(ABSPATH.'.htaccess'));
			else
				$patch_action .= '1"'.$gt.$lt.'input type="submit" value="Block XMLRPC Access" /'."$gt$lt/div$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.$lt.'img src="'.wm_images_path.'question.gif"'.$gt.'Block XMLRPC Access (Currently Allowing Access';
		}
		$patch_action .= ")$lt/b$gt$lt/p$gt".__("Most WordPress site do not use the XMLRPC features and hack attempt on the xmlrpc.php file are more common then ever before. Even if there are no vulnerabilities for hackers to exploit these attempts can cause slowness or downtime similar to a DDoS attack. This patch automatically blocks all external access to the xmlrpc.php file.",'wm').$lt.'/div'.$gt.$lt.'/form'.$gt.$lt.'hr /'.$gt;
		$patch_status = 0;
		$patch_found = -1;
		$find = "#if\s*\(([^\&]+\&\&)?\s*file_exists\((.+?)(wp-okay|wp-login)\.php'\)\)\s*require(_once)?\((.+?)(wp-okay|wp-login)\.php'\);#";
		$head = str_replace(array('#', '\\(', '\\)', '(_once)?', ')\\.', '\\s*', '(.+?)(', '|', '([^\\&]+\\&\\&)?'), array(' ', '(', ')', '_once', '.', ' ', '\''.dirname(__FILE__).'/', '/', '!in_array($_SERVER["REMOTE_ADDR"], array("'.$_SERVER["REMOTE_ADDR"].'")) &&'), $find);
		if (is_file(ABSPATH.'../wp-config.php') && !is_file(ABSPATH.'wp-config.php'))
			$wp_config = '../wp-config.php';
		else
			$wp_config = 'wp-config.php';
		if (is_file(ABSPATH.$wp_config)) {
			if (($config = @file_get_contents(ABSPATH.$wp_config)) && strlen($config)) {
				if ($patch_found = preg_match($find, $config)) {
					if (strpos($config, substr($head, strpos($head, "file_exists")))) {
						if ($wm_nonce_found && isset($_POST["wm_patching"]) && wm_file_put_contents(ABSPATH.$wp_config, preg_replace('#'.$lt.'\?[ph\s]+(//.*\s*)*\?'.$gt.'#i', "", preg_replace($find, "", $config))))
							$patch_action .= $lt.'div class="error"'.$gt.__("Removed Brute-Force Protection",'wm').$lt.'/div'.$gt;
						else
							$patch_status = 1;
					} else {
						if ($wm_nonce_found && isset($_POST["wm_patching"]) && wm_file_put_contents(ABSPATH.$wp_config, preg_replace($find, "$head", $config))) {
							$patch_action .= $lt.'div class="updated"'.$gt.__("Upgraded Brute-Force Protection",'wm').$lt.'/div'.$gt;
							$patch_status = 1;
						} else
							$patch_status = 2;
					}
				} elseif ($wm_nonce_found && isset($_POST["wm_patching"]) && strlen($config) && ($patch_found == 0) && wm_file_put_contents(ABSPATH.$wp_config, "$lt?php$head// Load Brute-Force Protection by wm.NET before the WordPress bootstrap. ?$gt$config")) {
					$patch_action .= $lt.'div class="updated"'.$gt.__("Installed Brute-Force Protection",'wm').$lt.'/div'.$gt;
					$patch_status = 1;
				} elseif ($wm_nonce_found && isset($_POST["wm_patching"]))
					$patch_action .= $lt.'div class="updated"'.$gt.sprintf(__("Failed to install Brute-Force Protection (wp-config.php %s)",'wm'),(is_readable(ABSPATH.$wp_config)?'read-'.(is_writable(ABSPATH.$wp_config)?'write':'only'):"unreadable").": ".strlen($config).wm_fileperms(ABSPATH.$wp_config)).$lt.'/div'.$gt;
			} else
				$patch_action .= $lt.'div class="error"'.$gt.__("wp-config.php Not Readable!",'wm').$lt.'/div'.$gt;
		} else
			$patch_action .= $lt.'div class="error"'.$gt.__("wp-config.php Not Found!",'wm').$lt.'/div'.$gt;
		if ($wm_nonce_found && file_exists(ABSPATH.'wp-login.php') && ($login = @file_get_contents(ABSPATH.'wp-login.php')) && strlen($login) && (preg_match($find, $login))) {
			if (isset($_POST["wm_patching"]) && ($source = wm_get_URL("http://core.svn.wordpress.org/tags/".$wp_version.'/wp-login.php')) && (strlen($source) > 500) && wm_file_put_contents(ABSPATH.'wp-login.php', $source))
				$patch_action .= $lt.'div class="updated"'.$gt.__("Removed Old Brute-Force Login Patch",'wm').$lt.'/div'.$gt;
			else
				$patch_status = 2;
		}
		$sec_opts = $lt.'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'img src="'.wm_images_path.'face.png"'.$gt.$lt.'b'.$gt.'Revolution Slider Exploit Protection (Automatically Enabled)'.$lt.'/b'.$gt.$lt.'/p'.$gt.__("This protection is automatically activated with this plugin because of the widespread attack on WordPress that are affecting so many site right now. It is still recommended that you make sure to upgrade and older versions of the Revolution Slider plugin, especially those included in some themes that will not update automatically. Even if you do not have Revolution Slider on your site it still can't hurt to have this protection installed.",'wm').$lt.'/div'.$gt.$lt.'hr /'.$gt.'
		'.$patch_action.'
		'.$lt.'form method="POST" name="wm_Form_patch"'.$gt.$lt.'div style="float: right;"'.$gt.$lt.'input type="hidden" name="'.str_replace('=', '" value="', wm_set_nonce(__FUNCTION__."1223")).'"'.$gt.$lt.'input type="submit" value="'.$patch_attr[$patch_status]["action"].'" style="'.($patch_status?'"'.$gt:' display: none;" id="wm_patch_button"'.$gt.$lt.'div id="wm_patch_searching" style="float: right;"'.$gt.__("Checking for session compatibility ...",'wm').' '.$lt.'img src="'.wm_images_path.'wait.gif" height=16 width=16 alt="Wait..." /'.$gt.$lt.'/div'.$gt).$lt.'input type="hidden" name="wm_patching" value="1"'.$gt.$lt.'/div'.$gt.$lt.'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'img src="'.wm_images_path.$patch_attr[$patch_status]["icon"].'.gif"'.$gt.$lt.'b'.$gt.'Brute-force Protection '.$patch_attr[$patch_status]["status"].$lt.'/b'.$gt.$lt.'/p'.$gt.$patch_attr[$patch_status]["language"].__(" For more information on Brute-Force attack prevention and the WordPress wp-login-php file ",'wm').' '.$lt.'a target="_blank" href="http://www.websitedesignwebsitedevelopment.com/"'.$gt.__("read my blog",'wm')."$lt/a$gt.$lt/div$gt$lt/form$gt\n{$lt}script type='text/javascript'$gt\nfunction search_patch_onload() {\n\tstopCheckingSession = checkupdateserver('".wm_images_path."wm.js?SESSION=0', 'wm_patch_searching');\n}\nif (window.addEventListener)\n\twindow.addEventListener('load', search_patch_onload)\nelse\n\tdocument.attachEvent('onload', search_patch_onload);\n$lt/script$gt";
		$admin_notice = "";
		if ($current_user->user_login == "admin") {
			$admin_notice .= $lt.'hr /'.$gt;
			if ($wm_nonce_found && isset($_POST["wm_admin_username"]) && ($current_user->user_login != trim($_POST["wm_admin_username"])) && strlen(trim($_POST["wm_admin_username"])) && preg_match('/^\s*[a-z_0-9\@\.\-]{3,}\s*$/i', $_POST["wm_admin_username"])) {
					if ($wpdb->update($wpdb->users, array("user_login" => trim($_POST["wm_admin_username"])), array("user_login" => $current_user->user_login)))
						$admin_notice .= $lt.'div class="updated"'.$gt.sprintf(__("You username has been change to %s. Don't forget to use your new username when you login again.",'wm'), $_POST["wm_admin_username"]).$lt.'/div'.$gt;
					else
						$admin_notice .= $lt.'div class="error"'.$gt.sprintf(__("SQL Error changing username: %s. Please try again later.",'wm'), $wpdb->last_error).$lt.'/div'.$gt;
			} else {
				if (isset($_POST["wm_admin_username"]))
					$admin_notice .= $lt.'div class="updated"'.$gt.sprintf(__("Your new username must be at least 3 characters and can only contain &quot;%s&quot;. Please try again.",'wm'), "a-z0-9_.-@").$lt.'/div'.$gt;
				$admin_notice .= $lt.'form method="POST" name="wm_Form_admin"'.$gt.$lt.'div style="float: right;"'.$gt.$lt.'div style="float: left;"'.$gt.__("Change your username:",'wm').$lt.'/div'.$gt.$lt.'input type="hidden" name="'.str_replace('=', '" value="', wm_set_nonce(__FUNCTION__."1235")).'"'.$gt.$lt.'input style="float: left;" type="text" id="wm_admin_username" name="wm_admin_username" size="6" value="'.$current_user->user_login.'"'.$gt.$lt.'input style="float: left;" type="submit" value="Change"'.$gt.$lt.'/div'.$gt.$lt.'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'img src="'.wm_images_path.'threat.gif"'.$gt.$lt.'b'.$gt.'Admin Notice'.$lt.'/b'.$gt.$lt.'/p'.$gt.__("Your username is \"admin\", this is the most commonly guessed username by hackers and brute-force scripts. It is highly recommended that you change your username immediately.",'wm').$lt.'/div'.$gt.$lt.'/form'.$gt;
			}
		}
		if ($wm_nonce_found && isset($_POST["wm_wpfirewall_action"])) {
			if ($_POST["wm_wpfirewall_action"] == "exclude_terms")
				update_option("WP_firewall_exclude_terms", "");
			elseif ($_POST["wm_wpfirewall_action"] == "whitelisted_ip" && isset($_SERVER["REMOTE_ADDR"])) {
				$ips = maybe_unserialize(get_option("WP_firewall_whitelisted_ip", "not Array!"));
				if (is_array($ips))
					$ips = array_merge($ips, array($_SERVER["REMOTE_ADDR"]));
				else
					$ips = array($_SERVER["REMOTE_ADDR"]);
				update_option("WP_firewall_whitelisted_ip", serialize($ips));
			}
		}
		if (get_option("WP_firewall_exclude_terms", "Not Found!") == "allow") {
			$end = "$lt/div$gt$lt/form$gt\n{$lt}hr /$gt";
			$img = 'threat.gif"';
			$button = $lt.'input type="submit" onclick="document.getElementById(\'wm_wpfirewall_action\').value=\'exclude_terms\';" value="'.__("Disable this Rule",'wm').'"'.$gt;
			$wpfirewall_action = $lt.'form method="POST" name="wm_Form_wpfirewall2"'.$gt.$lt.'div style="float: right;"'.$gt.$lt.'input type="hidden" name="wm_wpfirewall_action" id="wm_wpfirewall_action" value=""'.$gt.$lt.'input type="hidden" name="'.str_replace('=', '" value="', wm_set_nonce(__FUNCTION__."1223")).'"'.$gt.$button.$lt.'/div'.$gt.$lt.'div style="padding: 0 30px;"'.$gt.$lt.'p'.$gt.$lt.'img src="'.wm_images_path.$img.$gt.$lt.'b'.$gt."WP Firewall 2 (Conflicting Firewall Rule)$lt/b$gt$lt/p$gt".__("The Conflicting Firewall Rule (WP_firewall_exclude_terms) activated by the WP Firewall 2 plugin has been shown to interfere with the Definition Updates and WP Core File Scans in my Anti-Malware plugin. I recommend that you disable this rule in the WP Firewall 2 plugin.",'wm').$end;
			if (isset($_SERVER["REMOTE_ADDR"])) {
				if (is_array($ips = maybe_unserialize(get_option("WP_firewall_whitelisted_ip", "not Array!"))) && in_array($_SERVER["REMOTE_ADDR"], $ips))
					$wpfirewall_action = str_replace(array($img, $end), array('question.gif"', __(" However, your current IP has been Whitelisted so you could probably keep this rule enabled if you really want to.",'wm').$end), $wpfirewall_action);
				else
					$wpfirewall_action = str_replace(array($button, $end), array($button.$lt."br /$gt$lt".'input type="submit" onclick="document.getElementById(\'wm_wpfirewall_action\').value=\'whitelisted_ip\';" value="'.__("Whitelist your IP",'wm').'"'.$gt, __(" However, if you would like to keep this rule enabled you should at least Whitelist your IP.",'wm').$end), $wpfirewall_action);
			}
			$sec_opts = $wpfirewall_action.$sec_opts;
		}
		echo wm_box(__("Settings",'wm'), $sec_opts.$admin_notice)."\n</div></div></div>";
	}
	
	function wm_update_definitions() {
		global $wp_version;
		$wm_definitions_versions = array();
		foreach ($GLOBALS["wm"]["tmp"]["definitions_array"] as $threat_level=>$definition_names)
			foreach ($definition_names as $definition_name=>$definition_version)
				if (is_array($definition_version) && isset($definition_version[0]) && strlen($definition_version[0]) == 5)
					if (!isset($wm_definitions_versions[$threat_level]) || $definition_version[0] > $wm_definitions_versions[$threat_level])
						$wm_definitions_versions[$threat_level] = $definition_version[0];
		if (isset($_POST["UPDATE_definitions_array"]) && strlen($_POST["UPDATE_definitions_array"]) && wm_get_nonce()) {
			if (strlen($_POST["UPDATE_definitions_array"]) > 1) {
				$GOTnew_definitions = maybe_unserialize(wm_decode($_POST["UPDATE_definitions_array"]));
				if (is_array($GOTnew_definitions))
					$GLOBALS["wm"]["tmp"]["onLoad"] .= "updates_complete('Downloaded Definitions');";
			} elseif (($DEF = wm_get_URL(wm_update_home.'definitions.php?ver='.wm_Version.'&wp='.$wp_version.'&ts='.date("YmdHis").'&d='.ur1encode(wm_siteurl))) && (($GOT_definitions = wm_decode($DEF)) != serialize($GLOBALS["wm"]["tmp"]["definitions_array"])) && is_array($GOTnew_definitions = maybe_unserialize($GOT_definitions)) && count($GOTnew_definitions)) {
				if (!(isset($_REQUEST["check"]) && is_array($_REQUEST["check"])))
					$_REQUEST["check"] = array();
				foreach ($GOTnew_definitions as $threat_level=>$definition_names)
					if (!isset($GLOBALS["wm"]["tmp"]["definitions_array"]["$threat_level"]) && !(is_array($GLOBALS["wm"]["tmp"]["settings"]["check"]) && in_array("$threat_level", $GLOBALS["wm"]["tmp"]["settings"]["check"])) && !in_array("$threat_level", $_REQUEST["check"]))
						$_REQUEST["check"][] = "$threat_level";
				$GLOBALS["wm"]["tmp"]["definitions_array"] = $GOTnew_definitions;
				$GOTnew_definitions = array();
				$GLOBALS["wm"]["tmp"]["onLoad"] .= "updates_complete('New Definitions Automatically Installed :-)');";
			} else
				$GOTnew_definitions = "";
		} //elseif (file_exists(wm_plugin_path.'definitions_update.txt'))	$GOTnew_definitions = maybe_unserialize(wm_decode(file_get_contents(wm_plugin_path.'definitions_update.txt')));
		if (isset($GOTnew_definitions) && is_array($GOTnew_definitions)) {
			$GLOBALS["wm"]["tmp"]["definitions_array"] = wm_array_replace_recursive($GLOBALS["wm"]["tmp"]["definitions_array"], $GOTnew_definitions);	
			if (file_exists(wm_plugin_path.'definitions_update.txt'))
				@unlink(wm_plugin_path.'definitions_update.txt');
			if (isset($GLOBALS["wm"]["tmp"]["settings_array"]["check"]))
				unset($GLOBALS["wm"]["tmp"]["settings_array"]["check"]);
			update_option('wm_definitions_array', $GLOBALS["wm"]["tmp"]["definitions_array"]);
			foreach ($GLOBALS["wm"]["tmp"]["definitions_array"] as $threat_level=>$definition_names)
				foreach ($definition_names as $definition_name=>$definition_version)
					if (is_array($definition_version) && isset($definition_version[0]) && strlen($definition_version[0]) == 5)
						if (!isset($wm_definitions_versions[$threat_level]) || $definition_version[0] > $wm_definitions_versions[$threat_level])
							$wm_definitions_versions[$threat_level] = $definition_version[0];
		}
		$GLOBALS["wm"]["tmp"]["Definition"]["Updates"] = '?div=Definition_Updates';
		asort($wm_definitions_versions);
		foreach ($wm_definitions_versions as $definition_name=>$GLOBALS["wm"]["tmp"]["Definition"]["Latest"])
			$GLOBALS["wm"]["tmp"]["Definition"]["Updates"] .= "&ver[$definition_name]=".$GLOBALS["wm"]["tmp"]["Definition"]["Latest"];
	}
	
	function wm_settings() {
		global $current_user, $wpdb, $wp_version, $wm_dirs_at_depth, $wm_dir_at_depth;
		$wm_scan_groups = array();
		$gt = ">";
		$lt = "<";
		wm_update_definitions();
		if (($wm_nonce_found = wm_get_nonce()) && isset($_REQUEST["check"]) && is_array($_REQUEST["check"]))
			$GLOBALS["wm"]["tmp"]["settings_array"]["check"] = $_REQUEST["check"];
		/*	$threat_names = array_keys($GLOBALS["wm"]["tmp"]["definitions_array"]["known"]);
			foreach ($threat_names as $threat_name) {
				if (isset($GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name]) && is_array($GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name]) && count($GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name]) > 1) {
					if ($GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name][0] > $wm_definitions_version)
						$wm_definitions_version = $GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name][0];
					if (!(count($GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]) && in_array($threat_name, $GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]))) {
						$GLOBALS["wm"]["tmp"]["threat_levels"][$threat_name] = count($GLOBALS["wm"]["tmp"]["definitions_array"]["known"][$threat_name]);
						if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["check"]) && $GLOBALS["wm"]["tmp"]["threat_levels"][$threat_name] > 2)
							$GLOBALS["wm"]["tmp"]["settings_array"]["check"] = "known";
					}
				}
			}*/
		if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["check"])) {
			$GLOBALS["wm"]["tmp"]["settings_array"]["check"] = $GLOBALS["wm"]["tmp"]["threat_levels"];
			update_option("wm_settings_array", $GLOBALS["wm"]["tmp"]["settings_array"]);
		}
		$dirs = wm_explode_dir(__FILE__);
		$GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"] = isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"])?$GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"]:-1;
		for ($SL=0;$SL<intval($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"]);$SL++)
			$wm_scan_groups[] = $lt.'b'.$gt.implode(wm_slash(), array_slice($dirs, -1 * (3 + $SL), 1)).$lt.'/b'.$gt;
		if (isset($_POST["exclude_ext"])) {	
			if (strlen(trim(str_replace(",","",$_POST["exclude_ext"]).' ')) > 0)
				$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"] = preg_split('/[\s]*([,]+[\s]*)+/', trim(str_replace('.', ',', htmlentities($_POST["exclude_ext"]))), -1, PREG_SPLIT_NO_EMPTY);
			else
				$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"] = array();
		}
		$default_exclude_ext = str_replace(",wm", "", implode(",", $GLOBALS["wm"]["tmp"]["skip_ext"]));
		$GLOBALS["wm"]["tmp"]["skip_ext"] = $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"];
		if (isset($_POST["UPDATE_definitions_checkbox"])) {
			if (isset($_POST[$_POST["UPDATE_definitions_checkbox"]]) && $_POST[$_POST["UPDATE_definitions_checkbox"]] == 1)
				$GLOBALS["wm"]["tmp"]["settings_array"]["auto_UPDATE_definitions"] = 1;
			else
				$GLOBALS["wm"]["tmp"]["settings_array"]["auto_UPDATE_definitions"] = "";
		}
		if (isset($_POST["exclude_dir"])) {
			if (strlen(trim(str_replace(",","",$_POST["exclude_dir"]).' ')) > 0)
				$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"] = preg_split('/[\s]*([,]+[\s]*)+/', trim(htmlentities($_POST["exclude_dir"])), -1, PREG_SPLIT_NO_EMPTY);
			else
				$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"] = array();
			for ($d=0; $d<count($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"]); $d++)
				if (dirname($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"][$d]) != ".")
					$GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"][$d] = str_replace("\\", "", str_replace("/", "", str_replace(dirname($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"][$d]), "", $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"][$d])));
		}
		$GLOBALS["wm"]["tmp"]["skip_dirs"] = array_merge($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"], $GLOBALS["wm"]["tmp"]["skip_dirs"]);
		if (isset($_POST["scan_what"]) && is_numeric($_POST["scan_what"]) && $_POST["scan_what"] != $GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"])
			$GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"] = $_POST["scan_what"];
		if (isset($_POST["check_custom"]) && $_POST["check_custom"] != $GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"])
			$GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"] = stripslashes($_POST["check_custom"]);
		if (isset($_POST["scan_depth"]) && is_numeric($_POST["scan_depth"]) && $_POST["scan_depth"] != $GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"])
			$GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"] = $_POST["scan_depth"];
		if (isset($_POST['check_htaccess']) && is_numeric($_POST['check_htaccess']) && $_POST['check_htaccess'] != $GLOBALS["wm"]["tmp"]["settings_array"]['check_htaccess'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['check_htaccess'] = $_POST['check_htaccess'];
		if (isset($_POST['check_timthumb']) && is_numeric($_POST['check_timthumb']) && $_POST['check_timthumb'] != $GLOBALS["wm"]["tmp"]["settings_array"]['check_timthumb'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['check_timthumb'] = $_POST['check_timthumb'];
		if (isset($_POST['check_wp_core']) && is_numeric($_POST['check_wp_core']) && $_POST['check_wp_core'] != $GLOBALS["wm"]["tmp"]["settings_array"]['check_wp_core'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['check_wp_core'] = $_POST['check_wp_core'];
		if (isset($_POST['check_known']) && is_numeric($_POST['check_known']) && $_POST['check_known'] != $GLOBALS["wm"]["tmp"]["settings_array"]['check_known'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['check_known'] = $_POST['check_known'];
		if (isset($_POST['check_potential']) && is_numeric($_POST['check_potential']) && $_POST['check_potential'] != $GLOBALS["wm"]["tmp"]["settings_array"]['check_potential'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['check_potential'] = $_POST['check_potential'];
		if (isset($_POST['skip_quarantine']) && $_POST['skip_quarantine'])
			$GLOBALS["wm"]["tmp"]["settings_array"]['skip_quarantine'] = $_POST['skip_quarantine'];
		elseif (isset($_POST["exclude_ext"]))
			$GLOBALS["wm"]["tmp"]["settings_array"]['skip_quarantine'] = 0;
		wm_update_scan_log(array("settings" => $GLOBALS["wm"]["tmp"]["settings_array"]));
		$scan_whatopts = '';
		$scan_optjs = "\n{$lt}script type=\"text/javascript\"$gt\nfunction showOnly(what) {\n";
		foreach ($wm_scan_groups as $mg => $wm_scan_group) {
			$scan_optjs .= "document.getElementById('only$mg').style.display = 'none';\n";
			$scan_whatopts = "\n$lt/div$gt\n$lt/div$gt\n$scan_whatopts";
			$dir = implode(wm_slash(), array_slice($dirs, 0, -1 * (2 + $mg)));
			$files = wm_getfiles($dir);
			if (is_array($files))
				foreach ($files as $file)
					if (is_dir(wm_trailingslashit($dir).$file))
						$scan_whatopts = $lt.'input type="checkbox" name="scan_only[]" value="'.htmlentities($file).'" /'.$gt.htmlentities($file).$lt.'br /'.$gt.$scan_whatopts;
			$scan_whatopts = "\n$lt".'div style="padding: 4px 30px;" id="scan_group_div_'.$mg.'"'.$gt.$lt.'input type="radio" name="scan_what" id="not-only'.$mg.'" value="'.$mg.'"'.($GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"]==$mg?' checked':'').' /'.$gt.$lt.'a style="text-decoration: none;" href="#scan_what" onclick="showOnly(\''.$mg.'\');document.getElementById(\'not-only'.$mg.'\').checked=true;"'."$gt$wm_scan_group$lt/a$gt{$lt}br /$gt\n$lt".'div class="rounded-corners" style="position: absolute; display: none; background-color: #CCF; margin: 0; padding: 10px; z-index: 10;" id="only'.$mg.'"'.$gt.$lt.'div style="padding-bottom: 6px;"'.$gt.wm_close_button('only'.$mg, 0).$lt.'b'.$gt.str_replace(" ", "&nbsp;", __("Only Scan These Folders:",'wm')).$lt.'/b'.$gt.$lt.'/div'.$gt.$scan_whatopts;
		}
		$scan_optjs .= "document.getElementById('only'+what).style.display = 'block';\n}".((isset($GLOBALS["wm"]["tmp"]["settings_array"]["auto_UPDATE_definitions"]) && $GLOBALS["wm"]["tmp"]["settings_array"]["auto_UPDATE_definitions"])?"\nfunction auto_UPDATE_check() {\n\tif (auto_UPdef_check = document.getElementById('auto_UPDATE_definitions_check'))\n\t\tauto_UPdef_check.checked = true;\n}\nif (window.addEventListener)\n\twindow.addEventListener('load', auto_UPDATE_check)\nelse\n\tdocument.attachEvent('onload', auto_UPDATE_check);\n":"")."$lt/script$gt";
		$wm_nonce_URL = wm_set_nonce(__FUNCTION__."853");
		$scan_opts = "\n$lt".'form method="POST" name="wm_Form"'.$gt.$lt.'input type="hidden" name="'.str_replace('=', '" value="', $wm_nonce_URL).'"'.$gt.$lt.'input type="hidden" name="scan_type" id="scan_type" value="Complete Scan" /'.$gt.$lt.'div style="float: right;"'.$gt.$lt.'input type="submit" id="complete_scan" value="'.__("Complete Scan Now",'wm').'" class="button-primary" onclick="document.getElementById(\'scan_type\').value=\'Complete Scan\';" /'.$gt.$lt.'/div'.$gt.'
		'.$lt.'div style="float: left;"'.$gt.$lt.'p'.$gt.$lt.'/p'.$gt.'
		'.$lt.'div style="padding: 0 30px;"'.$gt;
		$GLOBALS["wm"]["tmp"]["threat_levels"] = array();
		foreach ($GLOBALS["wm"]["tmp"]["threat_levels"] as $threat_level_name=>$threat_level) {
			$scan_opts .= $lt.'div style="padding: 0; position: relative;" id="check_'.$threat_level.'_div"'.$gt;
			if (($threat_level != "wp_core" && isset($GLOBALS["wm"]["tmp"]["definitions_array"][$threat_level])) || isset($GLOBALS["wm"]["tmp"]["definitions_array"][$threat_level]["$wp_version"])) {
				$scan_opts .= $lt.'input type="checkbox" name="check[]" id="check_'.$threat_level.'_Yes" value="'.$threat_level.'"'.(in_array($threat_level,$GLOBALS["wm"]["log"]["settings"]["check"])?' checked':'').' /'.$gt.' '.$lt.'a style="text-decoration: none;" href="#check_'.$threat_level.'_div_0" onclick="document.getElementById(\'check_'.$threat_level.'_Yes\').checked=true;showhide(\'dont_check_'.$threat_level.'\');"'."$gt{$lt}b$gt$threat_level_name$lt/b$gt$lt/a$gt\n";
				if (isset($_GET["SESSION"])) {
					if (isset($_SESSION["wm_debug"][$threat_level]))
						$lt.'div style="float: right;"'.$gt.print_r($_SESSION["wm_debug"][$threat_level],1)."$lt/div$gt";
					$scan_opts .= "\n$lt".'div style="padding: 0 20px; position: relative; top: -18px; display: none;" id="dont_check_'.$threat_level.'"'.$gt.$lt.'a class="rounded-corners" style="position: absolute; left: 0; margin: 0; padding: 0 4px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;" href="#check_'.$threat_level.'_div_0" onclick="showhide(\'dont_check_'.$threat_level.'\');"'.$gt.'X'.$lt.'/a'.$gt;
					foreach ($GLOBALS["wm"]["tmp"]["definitions_array"][$threat_level] as $threat_name => $threat_regex)
						$scan_opts .= $lt."br /$gt\n$lt".'input type="checkbox" name="dont_check[]" value="'.htmlspecialchars($threat_name).'"'.(in_array($threat_name, $GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"])?' checked /'.$gt.$lt.'script'.$gt.'showhide("dont_check_'.$threat_level.'", true);'.$lt.'/script'.$gt:' /'.$gt).(isset($_SESSION["wm_debug"][$threat_name])?$lt.'div style="float: right;"'.$gt.print_r($_SESSION["wm_debug"][$threat_name],1)."$lt/div$gt":"").$threat_name;
					$scan_opts .= "\n$lt/div$gt";
				}
			} else
				$scan_opts .= $lt.'a title="'.__("Download Definition Updates to Use this feature",'wm').'"'.$gt.$lt.'img src="'.wm_images_path.'blocked.gif" height=16 width=16 alt="X"'.$gt.$lt.'b'.$gt.'&nbsp; '.$threat_level_name.$lt.'/b'.$gt.$lt.'br /'.$gt.$lt.'div style="padding: 14px;" id="check_'.$threat_level.'_div_NA"'.$gt.$lt.'span style="color: #F00"'.$gt.__("Download the new definitions (Right sidebar) to activate this feature.",'wm')."$lt/span$gt$lt/div$gt";
			$scan_opts .= "\n$lt/div$gt";
		}
		$scan_opts .= $lt.'/div'.$gt.$lt.'/div'.$gt.'
		'.$lt.'div style="float: left;"'.$gt.$scan_whatopts.$scan_optjs.$lt.'/div'.$gt.'
		'.$lt.'div style="float: left;" id="scanwhatfolder"'.$gt.$lt.'/div'.$gt.'
		'.$lt.'div style="float: left;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.__("Scan Depth:",'wm').$lt.'/b'.$gt.$lt.'/p'.$gt.'
		'.$lt.'div style="padding: 0 30px;"'.$gt.$lt.'input type="text" value="'.$GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"].'" name="scan_depth" size="5"'.$gt.$lt.'br /'.$gt.__("how far to drill down",'wm').$lt.'br /'.$gt.'('.__("-1 is infinite depth",'wm').')'.$lt.'/div'.$gt.$lt.'/div'.$gt.$lt.'br style="clear: left;"'.$gt;
		if (isset($_GET["SESSION"]) && isset($_SESSION["wm_debug"]['total'])) {$scan_opts .= $lt.'div style="float: right;"'.$gt.print_r($_SESSION["wm_debug"]['total'],1)."$lt/div$gt"; unset($_SESSION["wm_debug"]);}
		if (isset($_GET["wp-mechanic"])) {//still testing this option
			$scan_opts .= "\n$lt".'div style="padding: 10px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.__("Custom RegExp:",'wm').$lt.'/b'.$gt.' ('.__("For very advanced users only. Do not use this without talking to WordPress Mechanic first. If used incorrectly you could easily break your site.",'wm').')'.$lt.'/p'.$gt.$lt.'input type="text" name="check_custom" style="width: 100%;" value="'.htmlspecialchars($GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"]).'" /'."$gt$lt/div$gt\n$lt".'div style="padding: 10px;"'.$gt.$lt.'p'.$gt.$lt.'b'.$gt.__("Custom Code to be Checked:",'wm').$lt.'/b'.$gt.' ('.__("For very advanced users only. If you enter anything in this box then no other files will be scanned on your site.",'wm').')'.$lt.'/p'.$gt.$lt.'textarea name="check_code" style="width: 100%;" rows=3'.$gt.htmlspecialchars($GLOBALS["wm"]["tmp"]["settings_array"]["check_code"])."$lt/textarea$gt$lt/div$gt\n";
		} 
		$QuickScan = $lt.((is_dir(dirname(__FILE__)."/../../../wp-includes") && is_dir(dirname(__FILE__)."/../../../wp-admin"))?'a href="'.admin_url("admin.php?page=wm-settings&scan_type=Quick+Scan&$wm_nonce_URL").'" class="button-primary" style="height: 22px; line-height: 13px; padding: 3px;">WordPress Core Files</a':"!-- No wp-includes or wp-admin --").$gt;
		foreach (array("Plugins", "Themes") as $ScanFolder)
			$QuickScan .= '&nbsp;'.$lt.((is_dir(dirname(__FILE__)."/../../../wp-content/".strtolower($ScanFolder)))?'a href="'.admin_url("admin.php?page=wm-settings&scan_type=Quick+Scan&scan_only[]=wp-content/".strtolower($ScanFolder)."&$wm_nonce_URL")."\" class=\"button-primary\" style=\"height: 22px; line-height: 13px; padding: 3px;\"$gt".'WordPress '."$ScanFolder$lt/a":"!-- No $ScanFolder in wp-content --").$gt;
		$scan_opts .= "\n$lt".'p'.$gt.$lt.'b'.$gt.__("Skip files with the following extentions:",'wm')."$lt/b$gt".(($default_exclude_ext!=implode(",", $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"]))?" {$lt}a href=\"javascript:void(0);\" onclick=\"document.getElementById('exclude_ext').value = '$default_exclude_ext';\"{$gt}[Restore Defaults]$lt/a$gt":"").$lt.'/p'.$gt.'
		'.$lt.'div style="padding: 0 30px;"'.$gt.$lt.'input type="text" placeholder="'.__("a comma separated list of file extentions to skip",'wm').'" name="exclude_ext" id="exclude_ext" value="'.implode(",", $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"]).'" style="width: 100%;" /'."$gt$lt/div$gt$lt".'p'.$gt.$lt.'b'.$gt.__("Skip directories with the following names:",'wm')."$lt/b$gt$lt/p$gt$lt".'div style="padding: 0 30px;"'.$gt.$lt.'input type="text" placeholder="'.__("a folder name or comma separated list of folder names to skip",'wm').'" name="exclude_dir" value="'.implode(",", $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_dir"]).'" style="width: 100%;" /'.$gt.$lt.'/div'.$gt.'
		'.$lt.'table style="width: 100%" cellspacing="10"'.$gt.$lt.'tr'.$gt.$lt.'td nowrap valign="top" style="white-space: nowrap; width: 1px;"'.$gt."$lt/b$gt$lt/td$gt$lt".'td'.$gt."$lt/td$gt$lt".'td align="right" valign="bottom"'.$gt.$lt.'input type="submit" id="save_settings" value="'.__("Save Settings",'wm').'" class="button-primary" onclick="document.getElementById(\'scan_type\').value=\'Save\';" /'."$gt$lt/td$gt$lt/tr$gt$lt/table$gt$lt/form$gt";
		@ob_start();
		$OB_default_handlers = array("default output handler", "zlib output compression");
		$OB_handlers = @ob_list_handlers();
		if (is_array($OB_handlers) && count($OB_handlers))
			foreach ($OB_handlers as $OB_last_handler)
				if (!in_array($OB_last_handler, $OB_default_handlers))
					echo $lt.'div class="error"'.$gt.sprintf(__("Another Plugin or Theme is using '%s' to handle output buffers. <br />This prevents actively outputing the buffer on-the-fly and will severely degrade the performance of this (and many other) Plugins. <br />Consider disabling caching and compression plugins (at least during the scanning process).",'wm'), $OB_last_handler)."$lt/div$gt";
		wm_display_header();
		$scan_groups = array_merge(array(__("Scanned Files",'wm')=>"scanned",__("Selected Folders",'wm')=>"dirs",__("Scanned Folders",'wm')=>"dir",__("Skipped Folders",'wm')=>"skipdirs",__("Skipped Files",'wm')=>"skipped",__("Read/Write Errors",'wm')=>"errors",__("Restorable Files",'wm')=>"bad"), $GLOBALS["wm"]["tmp"]["threat_levels"]);
		echo $lt.'script type="text/javascript">
	var percent = 0;
	function changeFavicon(percent) {
		var oldLink = document.getElementById("wait_gif");
		if (oldLink) {
			if (percent >= 100) {
				document.getElementsByTagName("head")[0].removeChild(oldLink);
				var link = document.createElement("link");
				link.id = "wait_gif";
				link.type = "image/gif";
				link.rel = "shortcut icon";
				var threats = '.implode(" + ", array_merge($GLOBALS["wm"]["tmp"]["threat_levels"], array(__("Potential Threats",'wm')=>"errors",__("WP-Login Updates",'wm')=>"errors"))).';
				if (threats > 0) {
					if ((errors * 2) == threats)
						linkhref = "blocked";
					else
						linkhref = "threat";
				} else
					linkhref = "checked";
				link.href = "'.wm_images_path.'"+linkhref+".gif";
				document.getElementsByTagName("head")[0].appendChild(link);
			}
		} else {
			var icons = document.getElementsByTagName("link");
			var link = document.createElement("link");
			link.id = "wait_gif";
			link.type = "image/gif";
			link.rel = "shortcut icon";
			link.href = "'.wm_images_path.'wait.gif";
		//	document.head.appendChild(link);
			document.getElementsByTagName("head")[0].appendChild(link);
		}
	}
	function update_status(title, time) {
		sdir = (dir+direrrors);
		if (arguments[2] >= 0 && arguments[2] <= 100)
			percent = arguments[2];
		else
			percent = Math.floor((sdir*100)/dirs);
		scan_state = "e9ffe9";
		if (percent == 100) {
			showhide("pause_button", true);
			showhide("pause_button");
			title = "'.$lt.'b'.$gt.__("Scan Complete!",'wm').$lt.'/b'.$gt.'";
		} else
			scan_state = "99F";
		changeFavicon(percent);
		if (sdir) {
			if (arguments[2] >= 0 && arguments[2] <= 100)
				timeRemaining = Math.ceil(((time-startTime)*(100/percent))-(time-startTime));
			else
				timeRemaining = Math.ceil(((time-startTime)*(dirs/sdir))-(time-startTime));
			if (timeRemaining > 59)
				timeRemaining = Math.ceil(timeRemaining/60)+" Minute";
			else
				timeRemaining += " Second";
			if (timeRemaining.substr(0, 2) != "1 ")
				timeRemaining += "s";
		} else
			timeRemaining = "Calculating Time";
		timeElapsed = Math.ceil(time);
		if (timeElapsed > 59)
			timeElapsed = Math.floor(timeElapsed/60)+" Minute";
		else
			timeElapsed += " Second";
		if (timeElapsed.substr(0, 2) != "1 ")
			timeElapsed += "s";
		divHTML = \''.$lt.'div align="center" style="vertical-align: middle; background-color: #ccc; z-index: 3; height: 18px; width: 100%; border: solid #000 1px; position: relative; padding: 10px 0;"'.$gt.$lt.'div style="height: 18px; padding: 10px 0; position: absolute; top: 0px; left: 0px; background-color: #\'+scan_state+\'; width: \'+percent+\'%"'.$gt.$lt.'/div'.$gt.$lt.'div style="height: 32px; position: absolute; top: 3px; left: 10px; z-index: 5; line-height: 16px;" align="left"'.$gt.'\'+sdir+" Folder"+(sdir==1?"":"s")+" Checked'.$lt.'br /'.$gt.'"+timeElapsed+\' Elapsed'.$lt.'/div'.$gt.$lt.'div style="height: 38px; position: absolute; top: 0px; left: 0px; width: 100%; z-index: 5; line-height: 38px; font-size: 30px; text-align: center;"'.$gt.'\'+percent+\'%'.$lt.'/div'.$gt.$lt.'div style="height: 32px; position: absolute; top: 3px; right: 10px; z-index: 5; line-height: 16px;" align="right"'.$gt.'\'+(dirs-sdir)+" Folder"+((dirs-sdir)==1?"":"s")+" Remaining'.$lt.'br /'.$gt.'"+timeRemaining+" Remaining'.$lt.'/div'.$gt.$lt.'/div'.$gt.'";
		document.getElementById("status_bar").innerHTML = divHTML;
		document.getElementById("status_text").innerHTML = title;
		dis="none";
		divHTML = \''.$lt.'ul style="float: right; margin: 0 0px; text-align: right;"'.$gt.'\';
		/*'.$lt.'!--*'.'/';
		$MAX = 0;
		$vars = "var i, intrvl, direrrors=0";
		$fix_button_js = "";
		$found = "";
		$li_js = "return false;";
		foreach ($scan_groups as $scan_name => $scan_group) {
			if ($MAX++ == 6) {
				$quarantineCountOnly = wm_get_quarantine(true);
				$vars .= ", $scan_group=$quarantineCountOnly";
				echo "/*--{$gt}*"."/\n\tif ($scan_group > 0)\n\t\tscan_state = ' potential'; \n\telse\n\t\tscan_state = '';\n\tdivHTML += '</ul><ul style=\"text-align: left;\"><li class=\"wm_li\"><a href=\"admin.php?page=wm-View-Restore\" class=\"wm_plugin".("'+scan_state+'\" title=\"".wm_View_Quarantine_LANGUAGE)."\">'+$scan_group+'&nbsp;'+($scan_group==1?('$scan_name').slice(0,-1):'$scan_name')+'</a></li>';\n/*{$lt}!--*"."/";
				$found = "Found ";
				$fix_button_js = "\n\t\tdis='block';";
			} else {
				$vars .= ", $scan_group=0";
				if ($found && !in_array($scan_group, $GLOBALS["wm"]["log"]["settings"]["check"]))
					$potential_threat = ' potential" title="'.__("You are not currently scanning for this type of threat!",'wm');
				else
					$potential_threat = "";
				echo "/*--{$gt}*"."/\n\tif ($scan_group > 0) {\n\t\tscan_state = ' href=\"#found_$scan_group\" onclick=\"$li_js showhide(\\'found_$scan_group\\', true);\" class=\"wm_plugin $scan_group\"';$fix_button_js".($MAX>6?"\n\tshowhide('found_$scan_group', true);":"")."\n\t} else\n\t\tscan_state = ' class=\"wm_plugin$potential_threat\"';\n\tdivHTML += '<li class=\"wm_li\"><a'+scan_state+'>$found'+$scan_group+'&nbsp;'+($scan_group==1?('$scan_name').slice(0,-1):'$scan_name')+'</a></li>';\n/*{$lt}!--*"."/";
			}
			$li_js = "";
			if ($MAX > 11)
				$fix_button_js = "";
		}
		$ScanSettings = $lt.'div style="float: right;"'.$gt.wm_Run_Quick_Scan_LANGUAGE.":&nbsp;$QuickScan$lt/div$gt".wm_Scan_Settings_LANGUAGE;
		echo "/*--{$gt}*".'/
		document.getElementById("status_counts").innerHTML = divHTML+"'.$lt.'/ul'.$gt.'";
		document.getElementById("fix_button").style.display = dis;
	}
	'.$vars.';
	function showOnly(what) {
		document.getElementById("only_what").innerHTML = document.getElementById("only"+what).innerHTML;
	}
	var startTime = 0;
	'.$lt.'/script'.$gt.wm_box($ScanSettings, $scan_opts);
		if (isset($_REQUEST["scan_type"]) && $_REQUEST["scan_type"] == "Save") {
			if ($wm_nonce_found) {
				update_option('wm_settings_array', $GLOBALS["wm"]["tmp"]["settings_array"]);
				echo "\n{$lt}script type='text/javascript'$gt\nalert('Settings Saved!');\n$lt/script$gt\n";
			} else
				echo wm_box(wm_Invalid_Nonce(""), __("Saving these settings requires a valid Nonce Token. No valid Nonce Token was found at this time, either because the token have expired or because the data was invalid. Please try re-submitting the form above.",'wm')."\n{$lt}script type='text/javascript'$gt\nalert('".wm_Invalid_Nonce("")."');\n$lt/script$gt\n");
		} elseif (isset($_REQUEST["scan_what"]) && is_numeric($_REQUEST["scan_what"]) && ($_REQUEST["scan_what"] > -1)) {
			if ($wm_nonce_found) {
				update_option('wm_settings_array', $GLOBALS["wm"]["tmp"]["settings_array"]);
				if (!isset($_REQUEST["scan_type"]))
					$_REQUEST["scan_type"] = "Complete Scan";
				echo "\n$lt".'form method="POST" action="'.admin_url('admin-ajax.php?'.wm_set_nonce(__FUNCTION__."1030")).(isset($_SERVER["QUERY_STRING"])&&strlen($_SERVER["QUERY_STRING"])?"&".$_SERVER["QUERY_STRING"]:"").'" target="wm_iFrame" name="wm_Form_clean"'.$gt.$lt.'input type="hidden" name="action" value="wm_fix"'.$gt.$lt.'input type="hidden" id="wm_fixing" name="wm_fixing" value="1"'.$gt;
				foreach ($_POST as $name => $value) {
					if (substr($name, 0, 10) != 'wm_fix') {
						if (is_array($value)) {
							foreach ($value as $val)
								echo $lt.'input type="hidden" name="'.$name.'[]" value="'.htmlspecialchars($val).'"'.$gt;
						} else
							echo $lt.'input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'"'.$gt;
					}
				}
				echo "\n$lt".'script type="text/javascript"'.$gt.'showhide("inside_'.md5($ScanSettings).'");'.$lt.'/script'.$gt.wm_box(htmlentities($_REQUEST["scan_type"]).' Status', $lt.'div id="status_text"'.$gt.$lt.'img src="'.wm_images_path.'wait.gif" height=16 width=16 alt="..."'.$gt.' '.wm_Loading_LANGUAGE.$lt.'/div'.$gt.$lt.'div id="status_bar"'.$gt.$lt.'/div'.$gt.$lt.'p id="pause_button" style="display: none; position: absolute; left: 0; text-align: center; margin-left: -30px; padding-left: 50%;"'.$gt.$lt.'input type="button" value="Pause" class="button-primary" onclick="pauseresume(this);" id="resume_button" /'.$gt.$lt.'/p'.$gt.$lt.'div id="status_counts"'.$gt.$lt.'/div'.$gt.$lt.'p id="fix_button" style="display: none; text-align: center;"'.$gt.$lt.'input id="repair_button" type="submit" value="'.wm_Automatically_Fix_LANGUAGE.'" class="button-primary" onclick="loadIframe(\'Examine Results\');" /'.$gt.$lt.'/p'.$gt);
				$scan_groups_UL = "";
				foreach ($scan_groups as $scan_name => $scan_group)
					$scan_groups_UL .= "\n{$lt}ul name=\"found_$scan_group\" id=\"found_$scan_group\" class=\"wm_plugin $scan_group\" style=\"background-color: #ccc; display: none; padding: 0;\"$gt{$lt}a class=\"rounded-corners\" name=\"link_$scan_group\" style=\"float: right; padding: 0 4px; margin: 5px 5px 0 30px; line-height: 16px; text-decoration: none; color: #C00; background-color: #FCC; border: solid #F00 1px;\" href=\"#found_top\" onclick=\"showhide('found_$scan_group');\"{$gt}X$lt/a$gt{$lt}h3$gt$scan_name$lt/h3$gt\n".($scan_group=='potential'?$lt.'p'.$gt.' &nbsp; * '.__("NOTE: These are probably not malicious scripts (but it's a good place to start looking <u>IF</u> your site is infected and no Known Threats were found).",'wm').$lt.'/p'.$gt:($scan_group=='wp_core'?$lt.'p'.$gt.' &nbsp; * '.sprintf(__("NOTE: We have detected changes to the WordPress Core files on your site. This could be an intentional modification or the malicious work of a hacker. We can restore these files to their original state to preserve the integrity of your original WordPress %s installation.",'wm'), $wp_version).' (for more info '.$lt.'a target="_blank" href="http://www.websitedesignwebsitedevelopment.com/"'.$gt.__("read my blog",'wm').$lt.'/a'.$gt.').'.$lt.'/p'.$gt:$lt.'br /'.$gt)).$lt.'/ul'.$gt;
				if (!($dir = implode(wm_slash(), array_slice($dirs, 0, -1 * (2 + $_REQUEST["scan_what"]))))) $dir = "/";
				wm_update_scan_log(array("scan" => array("dir" => $dir, "start" => time(), "type" => htmlentities($_REQUEST["scan_type"]))));
				echo wm_box($lt.'div style="float: right;"'.$gt.'&nbsp;('.$GLOBALS["wm"]["log"]["scan"]["dir"].")&nbsp;$lt/div$gt".__("Scan Details:",'wm'), $scan_groups_UL);
				$no_flush_LANGUAGE = __("Not flushing OB Handlers: %s",'wm');
				if (isset($_REQUEST["no_ob_end_flush"]))
					echo $lt.'div class="error"'.$gt.sprintf($no_flush_LANGUAGE, print_r(ob_list_handlers(), 1))."$lt/div$gt\n";
				elseif (is_array($OB_handlers) && count($OB_handlers)) {
		//			$wm_OB_handlers = get_option("wm_OB_handlers", array());
					foreach (array_reverse($OB_handlers) as $OB_handler) {
						if (isset($wm_OB_handlers[$OB_handler]) && $wm_OB_handlers[$OB_handler] == "no_end_flush")
							echo $lt.'div class="error"'.$gt.sprintf($no_flush_LANGUAGE, $OB_handler)."$lt/div$gt\n";
						elseif (in_array($OB_handler, $OB_default_handlers)) {
		//					$wm_OB_handlers[$OB_handler] = "no_end_flush";
		//					update_option("wm_OB_handlers", $wm_OB_handlers);
							@ob_end_flush();
		//					$wm_OB_handlers[$OB_handler] = "ob_end_flush";
		//					update_option("wm_OB_handlers", $wm_OB_handlers);
						}
					}
				}
				@ob_start();
				if ($_REQUEST["scan_type"] == "Quick Scan") {
					$li_js = "\nfunction testComplete() {\n\tif (percent != 100)\n\t\talert('".__("The Quick Scan was unable to finish because of a shortage of memory or a problem accessing a file. Please try using the Complete Scan, it is slower but it will handle these errors better and continue scanning the rest of the files.",'wm')."');\n}\nwindow.onload=testComplete;\n$lt/script$gt\n$lt".'script type="text/javascript"'.$gt;
					if (is_numeric($check = array_search("potential", $GLOBALS["wm"]["log"]["settings"]["check"])))
						unset($GLOBALS["wm"]["log"]["settings"]["check"][$check]);
				}
				echo "\n{$lt}script type=\"text/javascript\"$gt$li_js\n/*{$lt}!--*"."/";
				if (is_dir($dir)) {
					$wm_dirs_at_depth[0] = 1;
					$wm_dir_at_depth[0] = 0;
					if (isset($_REQUEST['scan_only']) && is_array($_REQUEST['scan_only'])) {
						$wm_dirs_at_depth[0] += (count($_REQUEST['scan_only']) - 1);
						foreach ($_REQUEST['scan_only'] as $only_dir)
							if (is_dir(wm_trailingslashit($dir).$only_dir))
								wm_readdir(wm_trailingslashit($dir).$only_dir);
					} else
						wm_readdir($dir);
				} else
					echo wm_return_threat("errors", "blocked", $dir, wm_error_link("Not a valid directory!"));
				if ($_REQUEST["scan_type"] == "Quick Scan")
					echo wm_update_status(__("Completed!",'wm'), 100);
				else {
					echo wm_update_status(__("Starting Scan ...",'wm'))."/*--{$gt}*"."/";
					echo "\nvar scriptSRC = '".admin_url('admin-ajax.php?action=wm_scan&'.wm_set_nonce(__FUNCTION__."1087").'&mt='.$GLOBALS["wm"]["tmp"]["mt"].preg_replace('/\&(wm_scan|mt|wm_mt|action)=/', '&last_\1=', isset($_SERVER["QUERY_STRING"])&&strlen($_SERVER["QUERY_STRING"])?"&".$_SERVER["QUERY_STRING"]:"").'&wm_scan=')."';\nvar scanfilesArKeys = new Array('".implode("','", array_keys($GLOBALS["wm"]["tmp"]["scanfiles"]))."');\nvar scanfilesArNames = new Array('Scanning ".implode("','Scanning ", $GLOBALS["wm"]["tmp"]["scanfiles"])."');".'
		var scanfilesI = 0;
		var stopScanning;
		var gotStuckOn = "";
		function scanNextDir(gotStuck) {
		clearTimeout(stopScanning);
		if (gotStuck > -1) {
			if (scanfilesArNames[gotStuck].substr(0, 3) != "Re-") {
				if (scanfilesArNames[gotStuck].substr(0, 9) == "Checking ") {
					scanfilesArNames.push(scanfilesArNames[gotStuck]);
					scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&wm_skip_file[]="+encodeURIComponent(scanfilesArNames[gotStuck].substr(9)));
				} else {
					scanfilesArNames.push("Re-"+scanfilesArNames[gotStuck]);
					scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&wm_only_file=");
				}
			} else {
				scanfilesArNames.push("Got Stuck "+scanfilesArNames[gotStuck]);
				scanfilesArKeys.push(scanfilesArKeys[gotStuck]+"&wm_skip_dir="+scanfilesArKeys[gotStuck]);
			}
		}
		if (document.getElementById("resume_button").value != "Pause") {
			stopScanning=setTimeout("scanNextDir(-1)", 1000);
			startTime++;
		}
		else if (scanfilesI < scanfilesArKeys.length) {
			document.getElementById("status_text").innerHTML = scanfilesArNames[scanfilesI];
			var newscript = document.createElement("script");
			newscript.setAttribute("src", scriptSRC+scanfilesArKeys[scanfilesI]);
			divx = document.getElementById("found_scanned");
			if (divx)
				divx.appendChild(newscript);
			stopScanning=setTimeout("scanNextDir("+(scanfilesI++)+")",'.$GLOBALS["wm"]["tmp"]['execution_time'].'000);
		}
		}
		startTime = ('.ceil(time()-$GLOBALS["wm"]["log"]["scan"]["start"]).'+3);
		stopScanning=setTimeout("scanNextDir(-1)",3000);
		function pauseresume(butt) {
		if (butt.value == "Resume")
			butt.value = "Pause";
		else
			butt.value = "Resume";
		}
		showhide("pause_button", true);'."\n/*{$lt}!--*"."/";
				}
				if (@ob_get_level()) {
					wm_flush('script');
					@ob_end_flush();
				}
				echo "/*--{$gt}*"."/\n$lt/script$gt";
			} else
				echo wm_box(wm_Invalid_Nonce(""), __("Starting a Complete Scan requires a valid Nonce Token. No valid Nonce Token was found at this time, either because the token have expired or because the data was invalid. Please try re-submitting the form above.",'wm')."\n{$lt}script type='text/javascript'$gt\nalert('".wm_Invalid_Nonce("")."');\n$lt/script$gt\n");
		}
		echo "\n$lt/div$gt$lt/div$gt$lt/div$gt";
	}
	
	function wm_login_form($form_id = "loginform") {
		$sess = time();
		$ajaxURL = admin_url("admin-ajax.php?action=wm_logintime&wm_sess=");
		echo '<input type="hidden" name="session_id" value="'.substr($sess, 4).'"><input type="hidden" id="offset_id" value="0" name="sess'.substr($sess, 4).'"><script type="text/javascript">'."\nvar wm_login_offset = new Date();\nvar wm_login_script = document.createElement('script');\nwm_login_script.src = '$ajaxURL'+wm_login_offset.getTime();\n//wm_login_script.onload = set_offset_id();\ndocument.head.appendChild(wm_login_script);\n</script>\n";
	}
	add_action("login_form", "wm_login_form");
	
	function wm_ajax_logintime() {
		@header("Content-type: text/javascript");
		$sess = (false && isset($_GET["wm_sess"]) && is_numeric($_GET["wm_sess"])) ? $_GET["sess"] : time();
		die("\n//Permission Error: User not authenticated!\nvar wm_login_offset = new Date();\nvar wm_login_offset_start = wm_login_offset.getTime() - ".$sess."000;\nfunction set_offset_id() {\n\twm_login_offset = new Date();\n\tif (form_login = document.getElementById('offset_id'))\n\t\tform_login.value = wm_login_offset.getTime() - wm_login_offset_start;\n\tsetTimeout(set_offset_id, 15673);\n}\nset_offset_id();");
	}
	add_action('wp_ajax_nopriv_wm_logintime', 'wm_ajax_logintime');
	add_action('wp_ajax_wm_logintime', 'wm_ajax_logintime');
	
	function wm_ajax_lognewkey() {
		@header("Content-type: text/javascript");
		if (wm_get_nonce()) {
			if (isset($_POST["wm_installation_key"]) && ($_POST["wm_installation_key"] == wm_installation_key)) {
				$keys = maybe_unserialize(get_option('wm_Installation_Keys', array()));
				if (is_array($keys)) {
					$count = count($keys);
					if (!array_key_exists(wm_installation_key, $keys))
						$keys = array_merge($keys, array(wm_installation_key => wm_siteurl));
				} else
					$keys = array(wm_installation_key => wm_siteurl);
				update_option("wm_Installation_Keys", serialize($keys));
				die("\n//$count~".count($keys));
			} else
				die("\n//0");
		} else
			die(wm_Invalid_Nonce("\n//Log New Key Error: ")."\n");
	}
	add_action('wp_ajax_wm_lognewkey', 'wm_ajax_lognewkey');
	add_action('wp_ajax_nopriv_wm_lognewkey', 'wm_ajax_nopriv');
	
	function wm_set_plugin_action_links($links_array, $plugin_file) {
		if ($plugin_file == substr(str_replace("\\", "/", __FILE__), (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
			$links_array = array_merge(array('<a href="'.admin_url('admin.php?page=wm-settings').'"><span class="dashicons dashicons-admin-settings"></span>'.wm_Scan_Settings_LANGUAGE.'</a>'), $links_array);
		return $links_array;
	}
	add_filter("plugin_action_links", "wm_set_plugin_action_links", 1, 2);
	
	function wm_set_plugin_row_meta($links_array, $plugin_file) {
		if ($plugin_file == substr(str_replace("\\", "/", __FILE__), (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
			$links_array = array_merge($links_array, array('<a target="_blank" href="http://www.websitedesignwebsitedevelopment.com/">FAQ</a>','<a target="_blank" href="http://www.websitedesignwebsitedevelopment.com/">Support</a>','<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QZHD8QHZ2E7PE"><span class="dashicons dashicons-heart"></span>Donate</a>'));
		return $links_array;
	}
	add_filter("plugin_row_meta", "wm_set_plugin_row_meta", 1, 2);
	
	function wm_in_plugin_update_message($args) {
		$transient_name = 'wm_upgrade_notice_'.$args["Version"].'_'.$args["new_version"];
		if ((false === ($upgrade_notice = get_transient($transient_name))) && ($ret = wm_get_URL("https://plugins.svn.wordpress.org/wm/trunk/readme.txt"))) {
			$upgrade_notice = '';
			if ($match = preg_split('/==\s*Upgrade Notice\s*==\s+/i', $ret)) {
				if (preg_match('/\n+=\s*'.str_replace(".", "\\.", wm_Version).'\s*=\s+/is', $match[1]))
					$notice = (array) preg_split('/\n+=\s*'.str_replace(".", "\\.", wm_Version).'\s*=\s+/is', $match[1]);
				else
					$notice = (array) preg_split('/\n+=/is', $match[1]."\n=");
				$upgrade_notice .= '<div class="wm_upgrade_notice">'.preg_replace('/=\s*([\.0-9]+)\s*=\s*([^=]+)/i', '<li><b>${1}:</b> ${2}</li>', preg_replace('~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $notice[0])).'</div>';
				set_transient($transient_name, $upgrade_notice, DAY_IN_SECONDS);
			}
		}
		echo $upgrade_notice;
	}
	add_action("in_plugin_update_message-wm/index.php", "wm_in_plugin_update_message");
	
	function wm_init() {
		if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"]))
			$GLOBALS["wm"]["tmp"]["settings_array"]["scan_what"] = 2;
		if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"]))
			$GLOBALS["wm"]["tmp"]["settings_array"]["scan_depth"] = -1;
		if (isset($_REQUEST["scan_type"]) && $_REQUEST["scan_type"] == "Quick Scan") {
			if (!isset($_REQUEST["scan_what"]))	$_REQUEST["scan_what"] = 2;
			if (!isset($_REQUEST["scan_depth"]))
				$_REQUEST["scan_depth"] = 2;
			if (!isset($_REQUEST["scan_only"]))
				$_REQUEST["scan_only"] = array("","wp-includes","wp-admin");
			if ($_REQUEST["scan_only"] && !is_array($_REQUEST["scan_only"]))
				$_REQUEST["scan_only"] = array($_REQUEST["scan_only"]);
		}//$GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"] = stripslashes($_POST["check_custom"]);
		if (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"]))
			$GLOBALS["wm"]["tmp"]["settings_array"]["check_custom"] = "";
		if (isset($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"]) && is_numeric($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"]))
			$scan_level = intval($GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"]);
		else
			$scan_level = count(explode('/', trailingslashit(get_option("siteurl")))) - 1;
		if (wm_get_nonce()) {
			if (isset($_REQUEST["dont_check"]) && is_array($_REQUEST["dont_check"]) && count($_REQUEST["dont_check"]))
				$GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"] = $_REQUEST["dont_check"];
			elseif (isset($_POST["scan_type"]) || !(isset($GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"]) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"])))
				$GLOBALS["wm"]["tmp"]["settings_array"]["dont_check"] = array();
			if (isset($_POST["scan_level"]) && is_numeric($_POST["scan_level"]))
				$scan_level = intval($_POST["scan_level"]);
			if (isset($scan_level) && is_numeric($scan_level))
				$GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"] = intval($scan_level);
			else
				$GLOBALS["wm"]["tmp"]["settings_array"]["scan_level"] = count(explode('/', trailingslashit(get_option("siteurl")))) - 1;
		}
	}
	add_action("admin_init", "wm_init");
	
	function wm_ajax_position() {
		if (wm_get_nonce()) {
			$GLOBALS["wm_msg"] = __("Default position",'wm');
			$properties = array("body" => 'style="margin: 0; padding: 0;"');
			if (isset($_GET["wm_msg"]) && $_GET["wm_msg"] == $GLOBALS["wm_msg"]) {
				$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"] = $GLOBALS["wm"]["tmp"]["default"]["msg_position"];
				$gl = '><';
				$properties["html"] = $gl.'head'.$gl.'script type="text/javascript">
		if (curDiv = window.parent.document.getElementById("div_file")) {
			curDiv.style.left = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][0].'";
			curDiv.style.top = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][1].'";
			curDiv.style.height = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][2].'";
			curDiv.style.width = "'.$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][3].'";
		}
		</script'.$gl.'/head';
			} elseif (isset($_GET["wm_x"]) || isset($_GET["wm_y"]) || isset($_GET["wm_h"]) || isset($_GET["wm_w"])) {
				if (isset($_GET["wm_x"]))
					$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][0] = $_GET["wm_x"];
				if (isset($_GET["wm_y"]))
					$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][1] = $_GET["wm_y"];
				if (isset($_GET["wm_h"]))
					$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][2] = $_GET["wm_h"];
				if (isset($_GET["wm_w"]))
					$GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"][3] = $_GET["wm_w"];
				$_GET["wm_msg"] = __("New position",'wm');
			} else
				die("\n//Position Error: No new position to save!\n");
			update_option("wm_settings_array", $GLOBALS["wm"]["tmp"]["settings_array"]);
			die(wm_html_tags(array("html" => array("body" => htmlentities($_GET["wm_msg"]).' '.__("saved.",'wm').(implode($GLOBALS["wm"]["tmp"]["settings_array"]["msg_position"]) == implode($GLOBALS["wm"]["tmp"]["default"]["msg_position"])?"":' <a href="'.admin_url('admin-ajax.php?action=wm_position&'.wm_set_nonce(__FUNCTION__."1350").'&wm_msg='.urlencode($GLOBALS["wm_msg"])).'">['.$GLOBALS["wm_msg"].']</a>'))), $properties));
		} else
			die(wm_Invalid_Nonce("\n//Position Error: ")."\n");
	}
	add_action('wp_ajax_wm_position', 'wm_ajax_position');
	
	function wm_ajax_empty_trash() {
		global $wpdb;
		$gl = '><';
		if (wm_get_nonce()) {
			if ($trashed = $wpdb->query("DELETE FROM $wpdb->posts WHERE `post_type` = 'wm_quarantine' AND `post_status` != 'private'")) {
				$wpdb->query("REPAIR TABLE $wpdb->posts");
				$trashmsg = __("Emptied $trashed item from the quarantine trash.",'wm');
			} else
				$trashmsg = __("Failed to empty the trash.",'wm'); 
		} else
			$trashmsg = wm_Invalid_Nonce("");
		$properties = array("html" => $gl.'head'.$gl."script type='text/javascript'>\nif (curDiv = window.parent.document.getElementById('empty_trash_link'))\n\tcurDiv.style.display = 'none';\nalert('$trashmsg');\n</script$gl/head", "body" => 'style="margin: 0; padding: 0;"');
		die(wm_html_tags(array("html" => array("body" => $trashmsg)), $properties));
	}
	add_action('wp_ajax_wm_empty_trash', 'wm_ajax_empty_trash');
	
	function wm_ajax_whitelist() {
		if (wm_get_nonce()) {
			if (isset($_POST['wm_whitelist']) && isset($_POST['wm_chksum'])) {
				$file = wm_decode($_POST['wm_whitelist']);
				$chksum = explode("O", $_POST['wm_chksum']."O");
				if (strlen($chksum[0]) == 32 && strlen($chksum[1]) == 32 && is_file($file) && md5(@file_get_contents($file)) == $chksum[0]) {
					$filesize = @filesize($file);
					if (true) {
						if (!isset($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"][$file][0]))
							$GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"][$file][0] = "A0002";
						$GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"][$file][$chksum[0].'O'.$filesize] = "A0002";
					} else
						unset($GLOBALS["wm"]["tmp"]["definitions_array"]["whitelist"][$file]);
					update_option("wm_definitions_array", $GLOBALS["wm"]["tmp"]["definitions_array"]);
					$body = "Added $file to Whitelist!<br />\n<iframe style='width: 90%; height: 250px; border: none;' src='".wm_plugin_home."whitelist.html?whitelist=".$_POST['wm_whitelist']."&hash=$chksum[0]&size=$filesize&key=$chksum[1]'></iframe>";
				} else
					$body = "<li>Invalid Data!</li>";
				die(wm_html_tags(array("html" => array("body" => $body))));
			} else
				die("\n//Whitelist Error: Invalid checksum!\n");
		} else
			die(wm_Invalid_Nonce("\n//Whitelist Error: ")."\n");
	}
	add_action('wp_ajax_wm_whitelist', 'wm_ajax_whitelist');
	
	function wm_ajax_fix() {
		if (wm_get_nonce()) {
			if (isset($_POST["wm_fix"]) && !is_array($_POST["wm_fix"]))
				$_POST["wm_fix"] = array($_POST["wm_fix"]);
			if (isset($_REQUEST["wm_fix"]) && is_array($_REQUEST["wm_fix"]) && isset($_REQUEST["wm_fixing"]) && $_REQUEST["wm_fixing"]) {
				wm_update_scan_log(array("settings" => $GLOBALS["wm"]["tmp"]["settings_array"]));
				$callAlert = "clearTimeout(callAlert);\ncallAlert=setTimeout('alert_repaired(1)', 30000);";
				$li_js = "\n<script type=\"text/javascript\">\nvar callAlert;\nfunction alert_repaired(failed) {\nclearTimeout(callAlert);\nif (failed)\nfilesFailed='the rest, try again to change more.';\nwindow.parent.check_for_donation('Changed '+filesFixed+' files, failed to change '+filesFailed);\n}\n$callAlert\nwindow.parent.showhide('wm_iFrame', true);\nfilesFixed=0;\nfilesFailed=0;\nfunction fixedFile(file) {\n filesFixed++;\nif (li_file = window.parent.document.getElementById('check_'+file))\n\tli_file.checked=false;\nif (li_file = window.parent.document.getElementById('list_'+file))\n\tli_file.className='wm_plugin';\nif (li_file = window.parent.document.getElementById('wm_quarantine_'+file)) {\n\tli_file.style.display='none';\n\tli_file.innerHTML='';\n\t}\n}\nfunction DeletedFile(file) {\n filesFixed++;\nif (li_file = window.parent.document.getElementById('check_'+file))\n\tli_file.checked=false;\nif (li_file = window.parent.document.getElementById('wm_quarantine_'+file)) {\n\tli_file.style.display='none';\n\tli_file.innerHTML='';\n\t}}\nfunction failedFile(file) {\n filesFailed++;\nwindow.parent.document.getElementById('check_'+file).checked=false; \n}\n</script>\n<script type=\"text/javascript\">\n/*<!--*"."/";
				@set_time_limit($GLOBALS["wm"]["tmp"]['execution_time'] * 2);
				$HTML = explode("split-here-for-content", wm_html_tags(array("html" => array("body" => "split-here-for-content"))));
				echo $HTML[0];
				wm_update_scan_log(array("scan" => array("dir" => count($_REQUEST["wm_fix"])." Files", "start" => time())));
				foreach ($_REQUEST["wm_fix"] as $clean_file) {
					if (is_numeric($clean_file)) {
						if (($Q_post = wm_get_quarantine($clean_file)) && isset($Q_post["post_type"]) && strtolower($Q_post["post_type"]) == "wm_quarantine" && isset($Q_post["post_status"]) && strtolower($Q_post["post_status"]) == "private") {
							$path = $Q_post["post_title"];
							if ($_REQUEST["wm_fixing"] > 1) {
								echo "<li>Removing $path ... ";
								$Q_post["post_status"] = "trash";
								if (wp_update_post($Q_post)) {
									echo __("Done!",'wm');
									$li_js .= "/*-->*"."/\nDeletedFile('$clean_file');\n/*<!--*"."/";
								} else {
									echo __("Failed to delete!",'wm');
									$li_js .= "/*-->*"."/\nfailedFile('$clean_file');\n/*<!--*"."/";
								}
								wm_update_scan_log(array("scan" => array("finish" => time(), "type" => "Removal from Restore")));
							} else {
								echo "<li>Restoring $path ... ";
								$Q_post["post_status"] = "pending";
								if (wm_file_put_contents($path, wm_decode($Q_post["post_content"])) && wp_update_post($Q_post)) {
									echo __("Complete!",'wm');
									$li_js .= "/*-->*"."/\nfixedFile('$clean_file');\n/*<!--*"."/";
								} else {
									echo __("Restore Failed!",'wm');
									$li_js .= "/*-->*"."/\nfailedFile('$clean_file');\n/*<!--*"."/";
								}
								wm_update_scan_log(array("scan" => array("finish" => time(), "type" => "Restore")));
							}
							echo "</li>\n$li_js/*-->*"."/\n$callAlert\n</script>\n";
							$li_js = "<script type=\"text/javascript\">\n/*<!--*"."/";
						}//else print_r(array("i:$clean_file"=>$Q_post));
					} else {
						$path = realpath(wm_decode($clean_file));
						if (is_file($path)) {
							echo "<li>Fixing $path ... ";
							$li_js .= wm_scanfile($path);
							echo "</li>\n$li_js/*-->*"."/\n$callAlert\n//".$GLOBALS["wm"]["tmp"]["debug_fix"]."\n</script>\n";
							$li_js = "<script type=\"text/javascript\">\n/*<!--*"."/";
						} else
							echo "<li>".__("File ".htmlentities($path)." not found!",'wm')."</li>";
						wm_update_scan_log(array("scan" => array("finish" => time(), "type" => "Automatic Fix")));
					}
				}
				die('<div id="check_site_warning" style="background-color: #F00;">'.sprintf(__("Because some changes were made we need to check to make sure it did not break your site. If this stays Red and the frame below does not load please <a %s>revert the changes</a> made during this automated fix process.",'wm'), 'target="_top" href="admin.php?page=wm-View-Restore"').' <span style="color: #F00;">'.__("Never mind, it worked!",'wm').'</span></div><br /><iframe id="test_frame" name="test_frame" src="'.admin_url('admin.php?page=wm-settings&check_site=1&'.wm_set_nonce(__FUNCTION__."1451")).'" style="width: 100%; height: 200px"></iframe>'.$li_js."/*-->*"."/\nalert_repaired(0);\n</script>\n$HTML[1]");
			} else
				die(wm_html_tags(array("html" => array("body" => "<script type=\"text/javascript\">\nwindow.parent.showhide('wm_iFrame', true);\nalert('".__("Nothing Selected to be Changed!",'wm')."');\n</script>".__("Done!",'wm')))));
		} else
			die(wm_html_tags(array("html" => array("body" => "<script type=\"text/javascript\">\nwindow.parent.showhide('wm_iFrame', true);\nalert('".wm_Invalid_Nonce("")."');\n</script>".__("Done!",'wm')))));
	}
	add_action('wp_ajax_wm_fix', 'wm_ajax_fix');
	
	function wm_ajax_scan() {
		if (wm_get_nonce()) {
			@error_reporting(0);
			if (isset($_GET["wm_scan"])) {
				@set_time_limit($GLOBALS["wm"]["tmp"]['execution_time'] - 5);
				$decode_list = array("Base64" => '/base64_decode\([\'"]([0-9\+\/\=a-z]+)[\'"]\)/', "Hex" => '/(\\\\(x[0-9a-f]{2}|[0-9]{1,3}))/');
				if (is_numeric($_GET["wm_scan"])) {
					if (($Q_post = wm_get_quarantine($_GET["wm_scan"])) && isset($Q_post["post_type"]) && $Q_post["post_type"] == "wm_quarantine" && isset($Q_post["post_status"]) && $Q_post["post_status"] == "private") {
						$clean_file = $Q_post["post_title"];
						$GLOBALS["wm"]["tmp"]["file_contents"] = wm_decode($Q_post["post_content"]);
						$fa = "";
						$function = 'wm_decode';
						if (isset($_GET[$function]) && is_array($_GET[$function])) {
							foreach ($_GET[$function] as $decode) {
								if (isset($decode_list[$decode])) {
									$GLOBALS["wm"]["tmp"]["file_contents"] = preg_replace($decode_list[$decode].substr($GLOBALS["wm"]["tmp"]["default_ext"], 0, 2), $function.$decode.'("\1")',  $GLOBALS["wm"]["tmp"]["file_contents"]);
									$fa .= " $decode decoded";
								} else
									$fa .= " NO-$decode";
							}
						} elseif (isset($Q_post["post_excerpt"]) && strlen($Q_post["post_excerpt"]) && is_array($GLOBALS["wm"]["tmp"]["threats_found"] = @maybe_unserialize(wm_decode($Q_post["post_excerpt"])))) {
							$f = 1;
							//print_r(array("excerpt:"=>$GLOBALS["wm"]["tmp"]["threats_found"]));
							foreach ($GLOBALS["wm"]["tmp"]["threats_found"] as $threats_found => $threats_name) {
								list($start, $end, $junk) = explode("-", "$threats_found--", 3);
								if (strlen($end) > 0 && is_numeric($start) && is_numeric($end)) {
									if ($start < $end)
										$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.$start.', '.$end.');">['.$f++.']</a>';
									else
										$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.$end.', '.$start.');">['.$f++.']</a>';
								} else {
									if (is_numeric($threats_found)) {
										$threats_found = $threats_name;
										$threats_name = $f;
									}
									$fpos = 0;
									$flen = 0;
									$potential_threat = str_replace("\r", "", $threats_found);
									while (($fpos = strpos(str_replace("\r", "", $GLOBALS["wm"]["tmp"]["file_contents"]), ($potential_threat), $flen + $fpos)) !== false) {
										$flen = strlen($potential_threat);
										$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.($fpos).', '.($fpos + $flen).');">['.$f++.']</a>';
									}
								}
							}
						} //else echo "excerpt:".$Q_post["post_excerpt"];
						foreach ($decode_list as $decode => $regex)
							if (preg_match($regex.substr($GLOBALS["wm"]["tmp"]["default_ext"], 0, 1),  $GLOBALS["wm"]["tmp"]["file_contents"]))
								$fa .= ' <a href="'.wm_script_URI.'&'.$function.'[]='.$decode.'">decode['.$decode.']</a>';
						die("\n".'<script type="text/javascript">
			function select_text_range(ta_id, start, end) {
			ta_element = document.getElementById(ta_id);
			ta_element.focus();
			if(ta_element.setSelectionRange)
			   ta_element.setSelectionRange(start, end);
			else {
			   var r = ta_element.createTextRange();
			   r.collapse(true);
			   r.moveEnd(\'character\', end);
			   r.moveStart(\'character\', start);
			   r.select();   
			}
			}
			window.parent.showhide("wm_iFrame", true);
			</script><table style="top: 0px; left: 0px; width: 100%; height: 100%; position: absolute;"><tr><td style="width: 100%"><form style="margin: 0;" method="post" action="'.admin_url('admin-ajax.php?'.wm_set_nonce(__FUNCTION__."1522")).'" onsubmit="return confirm(\''.__("Are you sure you want to delete this file from the quarantine?",'wm').'\');"><input type="hidden" name="wm_fix[]" value="'.$Q_post["ID"].'"><input type="hidden" name="wm_fixing" value="2"><input type="hidden" name="action" value="wm_fix"><input type="submit" value="DELETE from Quarantine" style="background-color: #C00; float: right;"></form><div id="fileperms" class="shadowed-box rounded-corners" style="display: none; position: absolute; left: 8px; top: 29px; background-color: #ccc; border: medium solid #C00; box-shadow: -3px 3px 3px #666; border-radius: 10px; padding: 10px;"><b>File Details</b><br />encoding: '.(function_exists("mb_detect_encoding")?mb_detect_encoding($GLOBALS["wm"]["tmp"]["file_contents"]):"Unknown").'<br />size: '.strlen($GLOBALS["wm"]["tmp"]["file_contents"]).' bytes<br />infected:'.$Q_post["post_modified_gmt"].'<br />quarantined:'.$Q_post["post_date_gmt"].'</div><div style="overflow: auto;"><span onmouseover="document.getElementById(\'fileperms\').style.display=\'block\';" onmouseout="document.getElementById(\'fileperms\').style.display=\'none\';">'.__("File Details:",'wm').'</span> ('.$fa.' )</div></td></tr><tr><td style="height: 100%"><textarea id="ta_file" style="width: 100%; height: 100%">'.htmlentities(str_replace("\r", "", $GLOBALS["wm"]["tmp"]["file_contents"])).'</textarea></td></tr></table>');
					} else
						die(wm_html_tags(array("html" => array("body" => __("This file no longer exists in the quarantine.",'wm')."<br />\n<script type=\"text/javascript\">\nwindow.parent.showhide('wm_iFrame', true);\n</script>"))));
				} else {
					$file = wm_decode($_GET["wm_scan"]);
					if (is_dir($file)) {
						@error_reporting(0);
						@header("Content-type: text/javascript");
						if (isset($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"]) && is_array($GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"]))
							$GLOBALS["wm"]["tmp"]["skip_ext"] = $GLOBALS["wm"]["tmp"]["settings_array"]["exclude_ext"];
						@ob_start();
						echo wm_scandir($file);
						if (@ob_get_level()) {
							wm_flush();
							@ob_end_flush();
						}
						die('//END OF JavaScript');
					} else {
						if (!file_exists($file))
							die(wm_html_tags(array("html" => array("body" => sprintf(__("The file %s does not exist, it must have already been deleted.",'wm'), $file)."<script type=\"text/javascript\">\nwindow.parent.showhide('wm_iFrame', true);\n</script>"))));
						else {
							wm_scanfile($file);
							$fa = "";
							$function = 'wm_decode';
							if (isset($_GET[$function]) && is_array($_GET[$function])) {
								foreach ($_GET[$function] as $decode) {
									if (isset($decode_list[$decode])) {
										$GLOBALS["wm"]["tmp"]["file_contents"] = preg_replace($decode_list[$decode].substr($GLOBALS["wm"]["tmp"]["default_ext"], 0, 2), $function.$decode.'("\1")',  $GLOBALS["wm"]["tmp"]["file_contents"]);
										$fa .= " $decode decoded";
									} else
										$fa .= " NO-$decode";
								}
							} elseif (isset($GLOBALS["wm"]["tmp"]["threats_found"]) && is_array($GLOBALS["wm"]["tmp"]["threats_found"]) && count($GLOBALS["wm"]["tmp"]["threats_found"])) {
								$f = 1;
								foreach ($GLOBALS["wm"]["tmp"]["threats_found"] as $threats_found=>$threats_name) {
									list($start, $end, $junk) = explode("-", "$threats_found--", 3);
									if ($start > $end)
										$fa .= 'ERROR['.($f++).']: Threat_size{'.$threats_found.'} Content_size{'.strlen($GLOBALS["wm"]["tmp"]["file_contents"]).'}';
									else
										$fa .= ' <a title="'.htmlspecialchars($threats_name).'" href="javascript:select_text_range(\'ta_file\', '.$start.', '.$end.');">['.$f++.']</a>';
								}
							} else
								$fa = " No Threats Found";
							foreach ($decode_list as $decode => $regex)
								if (preg_match($regex.substr($GLOBALS["wm"]["tmp"]["default_ext"], 0, 1),  $GLOBALS["wm"]["tmp"]["file_contents"]))
									$fa .= ' <a href="'.wm_script_URI.'&'.$function.'[]='.$decode.'">decode['.$decode.']</a>';
							die("\n".'<script type="text/javascript">
			function select_text_range(ta_id, start, end) {
				ta_element = document.getElementById(ta_id);
				ta_element.focus();
				if(ta_element.setSelectionRange)
				   ta_element.setSelectionRange(start, end);
				else {
				   var r = ta_element.createTextRange();
				   r.collapse(true);
				   r.moveEnd(\'character\', end);
				   r.moveStart(\'character\', start);
				   r.select();   
				}
			}
			window.parent.showhide("wm_iFrame", true);
			</script><table style="top: 0px; left: 0px; width: 100%; height: 100%; position: absolute;"><tr><td style="width: 100%"><form style="margin: 0;" method="post" action="'.admin_url('admin-ajax.php?'.wm_set_nonce(__FUNCTION__."1583")).'" onsubmit="return confirm(\''.__("Are you sure this file is not infected and you want to ignore it in future scans?",'wm').'\');"><input type="hidden" name="wm_whitelist" value="'.wm_encode($file).'"><input type="hidden" name="action" value="wm_whitelist"><input type="hidden" name="wm_chksum" value="'.md5($GLOBALS["wm"]["tmp"]["file_contents"]).'O'.wm_installation_key.'"><input type="submit" value="Whitelist this file" style="float: right;"></form><div id="fileperms" class="shadowed-box rounded-corners" style="display: none; position: absolute; left: 8px; top: 29px; background-color: #ccc; border: medium solid #C00; box-shadow: -3px 3px 3px #666; border-radius: 10px; padding: 10px;"><b>File Details: '.basename($file).'</b><br />in: '.dirname(realpath($file)).'<br />encoding: '.(function_exists("mb_detect_encoding")?mb_detect_encoding($GLOBALS["wm"]["tmp"]["file_contents"]):"Unknown").'<br />size: '.strlen($GLOBALS["wm"]["tmp"]["file_contents"]).' ('.filesize(realpath($file)).'bytes)<br />permissions: '.wm_fileperms(realpath($file)).'<br />Owner/Group: '.fileowner(realpath($file)).'/'.filegroup(realpath($file)).' (you are: '.getmyuid().'/'.getmygid().')<br />modified:'.date(" Y-m-d H:i:s ", filemtime(realpath($file))).'<br />changed:'.date(" Y-m-d H:i:s ", filectime(realpath($file))).'</div><div style="overflow: auto;"><span onmouseover="document.getElementById(\'fileperms\').style.display=\'block\';" onmouseout="document.getElementById(\'fileperms\').style.display=\'none\';">'.__("Potential threats in file:",'wm').'</span> ('.$fa.' )</div></td></tr><tr><td style="height: 100%"><textarea id="ta_file" style="width: 100%; height: 100%">'.htmlentities(str_replace("\r", "", $GLOBALS["wm"]["tmp"]["file_contents"])).'</textarea></td></tr></table>');
						}
					}
				}
			} else
				die("\n//Directory Error: Nothing to scan!\n");
		} else {
			if (isset($_GET["wm_scan"]) && is_dir(wm_decode($_GET["wm_scan"])))
				@header("Content-type: text/javascript");
			die(wm_Invalid_Nonce("\n//Ajax Scan Error: ")."\n");
		}
	}
	add_action('wp_ajax_wm_scan', 'wm_ajax_scan');
	
	function wm_ajax_nopriv() {
		die("\n//Permission Error: User not authenticated!\n");
	}
	add_action('wp_ajax_nopriv_wm_scan', 'wm_ajax_nopriv');
	add_action('wp_ajax_nopriv_wm_position', 'wm_ajax_nopriv');
	add_action('wp_ajax_nopriv_wm_fix', 'wm_ajax_nopriv');
	add_action('wp_ajax_nopriv_wm_whitelist', 'wm_ajax_nopriv');
	add_action('wp_ajax_nopriv_wm_empty_trash', 'wm_ajax_nopriv');
	
	add_action("plugins_loaded", "wm_loaded");
	add_action("admin_notices", "wm_admin_notices");
	add_action("admin_menu", "wm_menu");
	add_action("network_admin_menu", "wm_menu");	
	
	