<?php
	
	class WmDeprecatedCheck{
		protected $directories_to_search;
		protected $deprecated_functions;
		protected $deprecated_file_paths;
		protected $check_themes;
		protected $check_plugins;
		
		function __construct(){
			$this->setup();
			$this->search_directories();
		}
		
		protected function setup(){
			$this->check_plugins = defined('DEP_CHECK_NO_PLUGINS') ? 0 : 1; 
			$this->check_themes = defined('DEP_CHECK_NO_THEMES') ? 0 : 1;
			$this->set_directories_to_search();
			$this->set_deprecated_file_paths();
			$this->set_deprecated_functions();
		}
		
		protected function set_directories_to_search(){
			if($this->check_themes)
				$this->directories_to_search['themes'] = WP_CONTENT_DIR.'/themes';
			if($this->check_plugins)
				$this->directories_to_search['plugins'] = WP_PLUGIN_DIR;
			$this->directories_to_search = apply_filters('deprecated_check_paths', $this->directories_to_search);
		}
		
		protected function set_deprecated_file_paths(){
			$paths = array(
				'wp-includes/deprecated.php',
				'wp-admin/includes/deprecated.php',
				'wp-includes/pluggable-deprecated.php',
				'wp-includes/ms-deprecated.php',
				'wp-admin/includes/ms-deprecated.php'
			);
			foreach($paths as $path)
				$this->deprecated_file_paths[] = ABSPATH."$path";
		}
		
		protected function set_deprecated_functions(){
			global $wp_version;
			if($cache = get_option("deprecated_functions_$wp_version")){
				$this->deprecated_functions = apply_filters('deprecated_check_functions', $cache);
				return;
			}
			foreach($this->deprecated_file_paths as $path){
				if(!file_exists($path)) continue;
				$contents = file_get_contents($path);
				if(preg_match_all("/function (.+)\(.+\n\t_deprecated_function\( __FUNCTION__, '(.+)', '(.+)'/", $contents, $functions)){
					$i = -1;
					foreach($functions[1] as $function){
						$i++;
						if(strpos($function, ' '))
							continue;
						if(strpos($function, '(')){
							$function = explode('(', $function);
							$function = $function[0];
						}
						$deprecated_functions[$function] = array(
							'new_function' => stripslashes($functions[3][$i]),
							'since' => $functions[2][$i]
						);
					}
				}
				
			}
			update_option("deprecated_functions_$wp_version", $deprecated_functions);
			$this->deprecated_functions = apply_filters('deprecated_check_functions', $deprecated_functions);
			
		}
		
		function search_directories(){

			$only = $_POST['wm-only'];
			//pree($only);
			show_message('<small>This might will take a few minutes, your patience is appreciated...</small><br />
<small style="color:red;">If you see empty results under the headings Plugins & Themes, it means that all good.</small>');
			set_time_limit(60*60*2);
			//pree($this->directories_to_search);exit;
			foreach($this->directories_to_search as $slug => $directory){
				if($only!='' && $slug!=$only){
					continue;
				}else{
					//pree($slug);exit;
				}
				show_message('<h2>'.ucwords(str_replace('_', '', $slug)).'</h2>');
				//continue;
				$folders = new RecursiveDirectoryIterator($directory);
				foreach(new RecursiveIteratorIterator($folders) as $file_path){
					//pree($file_path);
					$i = 1;
					if(strpos($file_path, '.php')){
						if(!file_exists($file_path)) continue;
						$file = fopen($file_path, 'rb');
						while ($line = fgets($file)) {
							//pree($line);
							//pree($this->deprecated_functions);exit;
							foreach($this->deprecated_functions as $function => $deprecated_info){
								//pree($function);
								if(preg_match("/->\b$function\((.+)\);/", $line))
									continue;
								if(preg_match("/::$function/", $line))
									continue;
								if(preg_match("/\b$function\((.+)\);/", $line)){
									$new_function = $deprecated_info['new_function'];
									$since = $deprecated_info['since'];
									if($since < 2.8)
										show_message("<strong style='color:orange;'>Warning: $function has been deprecated since $since and could possibly be removed from core soon.</strong>");
									show_message("Line $i - $file_path - <strong style='color:red;'>$function</strong> - deprecated since $since - use <strong style='color:green'>$new_function</strong>");
									unset($naughty);
								}
							}
							$i++;
						}
						fclose($file);
					}
				}
			}
		}
	}
		

