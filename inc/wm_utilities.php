
<div class="wrap wm_settings_div">

        

<div class="icon32" id="icon-options-general"><br></div><h2><?php echo $wm_data['Name']; ?> <?php echo '('.$wm_data['Version'].($wm_pro?') Pro':')'); ?> - Utilities</h2> 
<?php if(!$wm_pro): ?>
<a title="Click here to download pro version" style="background-color: #25bcf0;    color: #fff !important;    padding: 2px 30px;    cursor: pointer;    text-decoration: none;    font-weight: bold;    right: 0;    position: absolute;    top: 0;    box-shadow: 1px 1px #ddd;" href="http://shop.androidbubbles.com/download/" target="_blank">Already a Pro Member?</a>
<?php endif; ?>





<?php $wpurl = get_bloginfo('wpurl'); ?>



<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

</form>





<?php
	if(isset($_GET['page']) && $_GET['page']=='wm-utilities' && isset($_POST['deprecated-check'])){
		new WmDeprecatedCheck();
	}else{
?>
<div class="wm_notes">This plugin can help you to check <a href="https://codex.wordpress.org/Category:Deprecated_Functions" target="_blank">Deprecated Functions</a> in your theme and plugin directories. <a class="button button-secondary button-small wm-deprecated">Click here to check</a></div>
<?php include('wm_deprecated_check.php'); ?>
<?php		
	}
?>	









</div>