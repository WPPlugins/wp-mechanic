<?php
        


	

	

	define('WM_EMAIL', 'wp.mechanic@androidbubbles.com');

		//FOR QUICK DEBUGGING
		if(!function_exists('pre')){
		function pre($data){
				if(isset($_GET['debug'])){
					pree($data);
				}
			}	 
		} 	
		
		if(!function_exists('pree')){
		function pree($data){
					echo '<pre>';
					print_r($data);
					echo '</pre>';	
			}	 
		} 
		

        if(!function_exists('wm_sync')){
            function wm_sync(){
                
                global $info_file;
                global $how_it_works;
                global $sitting_time;
                global $auth_file;
                global $admin_email;
                
                $update_required = FALSE;
                
                if(file_exists($info_file) && PHP_VERSION>=5.3){
                    
                    $datetime1 = new DateTime(date('Y-m-d H:i:s', filemtime($info_file)));
                    $datetime2 = new DateTime(date('Y-m-d H:i:s', time()));
                    $interval = $datetime1->diff($datetime2);

                    //ONE WEEK
                    if($interval->format('%h')>168){
                        $update_required = TRUE;
                    }
                }
                
                if(!file_exists($info_file) || $update_required){
                    $info = @file_get_contents('http://www.androidbubbles.com/api/wm.php?q=info');
                    if($info===FALSE){
                    }else{
                    $f = fopen($info_file, 'w+');
                    fwrite($f, $info);
                    fclose($f);      
                    }
                }
                
                if(!file_exists($auth_file)){
                    $autho_info = 'WP'.md5($admin_email).'MECHANIC';
                    $f = fopen($auth_file, 'w+');
                    fwrite($f, $autho_info);
                    fclose($f);                
                }                
                
                
                if(file_exists($info_file)){
                    $get_info = file_get_contents($info_file);
                    $format_info = json_decode($get_info);
                    
                    
                    if(!empty($format_info)){
                        
                   
                    $how_it_works = $format_info->how_it_works->description;
                    $how_it_works .= $format_info->how_it_works->steps_heading;
                    $how_it_works .= '<ol><li>'.implode('</li><li>', $format_info->how_it_works->steps).'</li></ol>';
                    $how_it_works .= $format_info->how_it_works->notes;
                    $how_it_works .= '<div id="wm_get_started"><a class="button button-primary button-hero">Get Started!</a></div>';
                    
                    $sitting_time = $format_info->sitting_time;
                    
                     }
                }

            }
        }
            
	if(!function_exists('wm_start')){

		function wm_start(){	



		}	

	}

	

	

	if(!function_exists('wm_end')){



		function wm_end(){	



		}

	}
	
	if(!function_exists('wm_searchable')){
		function wm_searchable($content){	

			$exp = explode(' ', $content);
			return '<span>'.implode('</span><span>', $exp).'</span>';

		}

	}	


	

	if(!function_exists('wm_feeds')){



		function wm_feeds(){	

			global $auth_file;
                        
                        $auth_file_text = file_get_contents($auth_file);
                        
			$sources = array();
                        
                        if($auth_file_text!=''){
                            
                        
			$sources['asked'] = 'http://www.websitedesignwebsitedevelopment.com/?s='.$auth_file_text.'&feed=rss';
                        }
                         
			$sources['wdwd'] = 'http://www.websitedesignwebsitedevelopment.com/category/website-development/php-frameworks/wordpress/feed/';

			

			$rss_items = array();

			foreach($sources as $uri){

				$rss = @fetch_feed( $uri );

				

				if ( ! is_wp_error( $rss ) ) :

				

				$maxitems = $rss->get_item_quantity( 6 ); 

				$rss_items[] = $rss->get_items( 0, $maxitems );

				
				else:
				
				endif;

			}

			

			return $rss_items;



		}

	}		

	

	

	

	if(!function_exists('wm_dashboard_widget_function')){

		function wm_dashboard_widget_function() {

		

			$rss_items = wm_feeds();



			include('wm_dashboard.php');			

		} 

	}

	



	

	if(!function_exists('wm_add_dashboard_widgets')){

		function wm_add_dashboard_widgets() {

			wp_add_dashboard_widget('wm_dashboard_widget', 'WordPress Mechanic', 'wm_dashboard_widget_function');	

		} 

	}

	

	if(!function_exists('set_html_content_type')){

		function set_html_content_type()

		{

			return 'text/html';

		}	

	}

	

	if(!function_exists('clean_data')){

		function clean_data($input) {

		$input = trim(htmlentities(strip_tags($input,",")));

	 

		if (get_magic_quotes_gpc())

			$input = stripslashes($input);

	 

		$input = mysql_real_escape_string($input);

	 

		return strip_tags($input);

		}

	}



	if(!function_exists('wmpost_question')){

		function wmpost_question() {	

                        global $admin_email;
                        global $auth_file;
			$resp = array('status'=>true, 'msg'=>'Successfully sent.');

			$question = clean_data($_POST['wm_question']);

			

			

			

			if(strlen($question)<60){

				$resp['msg'] = 'Your question is too short, please explain it.';

				$resp['status'] = false;

			

			}elseif(strlen($question)>600){

				$resp['msg'] = 'Your question is too long, please ask precisely.';

				$resp['status'] = false;

			

			}elseif($admin_email=='' || !is_email($admin_email)){

				$resp['msg'] = 'Make sure that admin email is valid.';

				$resp['status'] = false;

			}else{

				

				//add_filter( 'wp_mail_content_type', 'set_html_content_type' );

				$headers = 'From: <'.$admin_email.'>' . "\r\n";

				$subject = wp_trim_words($question, 8);

				$question.='<span class="hide">'.file_get_contents($auth_file).'</span>';
				
				if(wp_mail(WM_EMAIL, $subject, $question, $headers)){

				

				}else{

					$resp['status'] = false;

					$resp['msg'] = 'Please try later.';

				}

				

				//remove_filter( 'wp_mail_content_type', 'set_html_content_type' ); 





			}

			

			echo json_encode($resp);

			

			exit;

		} 

	}

	

	

	if(!function_exists('wm_timezone')){

		function wm_timezone() {	

		global $sitting_time;	

		$time = date('d-m-Y h:i:s a',mktime($sitting_time, 0, 0, date('m'), date('d'), date('y'))); 

		

		try {

		

		$tz = new DateTime($time, new DateTimeZone('Asia/Karachi'));

		

		$tz->setTimeZone(new DateTimeZone(date_default_timezone_get()));

			return 'Availability around '.$tz->format('h:i A P');

		} catch(Exception $e) {



			 return $e->getMessage();

		}			





		}

	}



?>