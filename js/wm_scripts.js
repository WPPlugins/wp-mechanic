jQuery(document).ready(function($) {

	$('.wm-deprecated').click(function(){
		$(this).hide();
		$('.wm-deprecated-check').fadeIn();
	});

	jQuery('#wm-content-wrap #wm-ask-question').click(function(){

    	jQuery('#wm_dashboard_widget #title-wrap').show();

		jQuery('#wm_dashboard_widget .submit').show();

        jQuery(this).parent().hide();

		jQuery('#wm_question').focus();

        

    });

	$('.db-tables tr').on('click', function(){
		var checkBoxes = $(this).find('input[name="del[]"]');
		checkBoxes.prop("checked", !checkBoxes.prop("checked"));
		$(this).toggleClass('selected');
	});
	
	
	$("#select_all").change(function(){  //"select all" change
		$(this).toggleClass('checked');
		$(".checkbox").prop('checked', $(this).prop("checked")); 
		$.each($('.db-tables tr'), function(){
			var class_name = ($(this).find('.checkbox').prop('checked')?'selected':'');
			$(this).attr('class', class_name);
		});
	});
	
	if($(".db-tables #select_all").length>0)
	$("#select_all").change();
	
	$('.checkbox').change(function(){
		//uncheck "select all", if one of the listed checkbox item is unchecked
		if(false == $(this).prop("checked")){ //if this item is unchecked
			$("#select_all").prop('checked', false); //change "select all" checked status to false
		}
		//check "select all" if all checkbox items are checked
		if ($('.checkbox:checked').length == $('.checkbox').length ){
			$("#select_all").prop('checked', true);
		}
	});	

	jQuery('#wm-cancel').click(function(){



		jQuery('#wm_dashboard_widget .submit').hide();

		jQuery('#wm_dashboard_widget #title-wrap').hide();

		jQuery('#wm-content-wrap').show();

		jQuery('#wm_question').val('');

        

    });

	

	jQuery('#wm_dashboard_widget #wm-hire-mechanic').click(		function(){

				

			jQuery('#wm-content-wrap').hide();	

			jQuery('#wm-mechanic-wrap').show();	

																									      });

	

	

	jQuery('#wm_dashboard_widget #wm-submit').click(function(){

															 

	jQuery('#wm_dashboard_widget .spinner').show();

	

	jQuery.post(

	   ajaxurl, 

	   {

		  'action':'wmpost_question',

		  'wm_question':jQuery('#wm_question').val()

	   }, 

	   function(response){

		  

		  var resp = jQuery.parseJSON(response);

		  var msg = jQuery('#wm_dashboard_widget #wm-msg');

		  

		  if(resp.status){

			  

			  

	

			  msg.attr('class', 'updated');

			  msg.find('p').html(resp.msg);

			  

		  

		  }else{

			  

			  msg.attr('class', 'error');

			  msg.find('p').html('An error occurred: '+resp.msg);

			  

		  }

		  

		  msg.show();



		  

		  jQuery('#wm-cancel').click();

		  jQuery('#wm_dashboard_widget .spinner').hide();

			  

		  setTimeout(function(){

			msg.slideUp();

							  }, 10000);

		  

	   }

	);															 

										

	



	

	});

	

	jQuery('#wm_dashboard_widget #wm-mechanic-close').click(function(){

	

			jQuery('#wm-content-wrap').show();	

			jQuery('#wm-mechanic-wrap').hide();			

																			 	});

	

	 jQuery(function() {
		if(jQuery( "#wm_dashboard_widget .wm-feeds" ).length>0){
			jQuery( "#wm_dashboard_widget .wm-feeds" ).accordion({
				  collapsible: true,
				  active: false,
				  heightStyle: "content"
			});
		}

		});
	 
	
	 
	 jQuery.expr[":"].contains = jQuery.expr.createPseudo(function(arg) {
			return function( elem ) {
				return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
			};
		});
	 
	 jQuery("#wm_dashboard_widget #wm-lookup").bind('keypress', function(){
		
		jQuery("div.wm-feeds h3").css('font-weight', 'normal');
		if(jQuery(this).val()!=''){
		var found = jQuery("div.wm-feeds p:contains('"+jQuery(this).val()+"')");
		
		found.parent().prev('h3').css('font-weight', 'bold');
		
		//document.title = jQuery(this).val();
		}
																		 		});
	 
	 jQuery("#wm_dashboard_widget #wm-mechanic-help, .wm-face ").click(function(){
                                                                                                                                                jQuery("#wm_dashboard_widget #wm-help").slideToggle();								jQuery("#wm_dashboard_widget .wm-rss-widget").slideToggle();
																				        jQuery('#wm_dashboard_widget #wm-mechanic-help').toggleClass("highlight");
                                                                                                                                                jQuery('#wm-content-wrap').hide();
																			 	});

                                                                            jQuery("#wm_dashboard_widget #wm_get_started a").click(function(){  
                                                                                jQuery("#wm_dashboard_widget #wm-help").slideUp();
                                                                                jQuery("#wm_dashboard_widget .wm-rss-widget").slideDown();
                                                                                jQuery('#wm-content-wrap').show();
                                                                            });                                                                                      
jQuery('.add-new-h2').parent().append('<a class="add-new-h2 open-all" title="Double Click here to open all items at once">Open All</a>');
	
	jQuery('.open-all').dblclick(function(){
			
		jQuery.each(jQuery('.view a'), function(){ 
			if(jQuery(this).html()=='View'){
				var o = window.open(jQuery(this).attr('href'), '_blank');
				o.focus();
			}
		});
		
	});                                                                                   

});



