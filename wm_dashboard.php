<?php global $how_it_works; ?>
<div class="wm-face" title="Click here to get started"></div>
<div id="wm-help"><?php echo $how_it_works; ?></div>

<div id="wm-msg"><p></p></div>

<div class="wm-rss-widget">


<input title="Quick search" type="text" placeholder="Quick search" id="wm-lookup" name="wm-lookup">

<div class="wm-feeds">

<?php if (empty($rss_items)) : ?>

<?php _e( 'There are no feeds at the moment.'); ?></li>

<?php else : ?>

<?php foreach ( $rss_items as $items ): ?>

<?php if (!empty($items)) : $i = 1; foreach ( $items as $item ): 
$dated = strtotime($item->get_date());
?>


<h3 class="head_<?php echo $i; ?>"><?php echo esc_html( $item->get_title() );?><span><?php echo date('Y')==date('Y', $dated)?date('d M', $dated):date('d M, Y', $dated); ?></span></h3>

<div class="content_<?php echo $i; ?>">

<?php echo ($item->get_content());?>

</div> 

<?php $i++; endforeach; endif; ?>

<?php endforeach; endif; ?>

</div>









</div>



<div class="wp-editor-wrap hide-if-no-js wp-media-buttons" id="wm-content-wrap">

<a title="Ask Question" class="button" id="wm-ask-question"><span></span>Ask Question?</a>



<a title="Talk to Mechanic" class="button-primary" id="wm-hire-mechanic"><span></span>Talk to WP Mechanic</a>	

<a id="wm-mechanic-help">How it works?</a>
</div>



<div class="wp-media-buttons hide" id="wm-mechanic-wrap">

<a href="mailto:<?php echo WM_EMAIL; ?>" class="button">Email to WP Mechanic</a><a class="button-primary" href="skype:profile_name?wp.mechanic">Skype</a> (<?php echo wm_timezone(); ?>)



<a id="wm-mechanic-close">Back</a>

</div>

            

<div id="title-wrap" class="textarea-wrap hide">

<textarea cols="15" rows="3" class="mceEditor" id="wm_question" name="wm_question" placeholder="Type your question here..."></textarea>

</div>



<p class="submit hide">

<input type="button" value="Submit" class="button-primary" id="wm-submit" name="wm_submit" />

<input id="wm-cancel" type="reset" class="button" value="Cancel" />



<span class="spinner hide"></span>

<br class="clear">



</p>

