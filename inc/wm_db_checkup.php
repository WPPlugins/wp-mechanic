
<div class="wrap wm_settings_div">

        

<div class="icon32" id="icon-options-general"><br></div><h2><?php echo $wm_data['Name']; ?> <?php echo '('.$wm_data['Version'].($wm_pro?') Pro':')'); ?> - Database Cleanup</h2> 
<?php if(!$wm_pro): ?>
<a title="Click here to download pro version" style="background-color: #25bcf0;    color: #fff !important;    padding: 2px 30px;    cursor: pointer;    text-decoration: none;    font-weight: bold;    right: 0;    position: absolute;    top: 0;    box-shadow: 1px 1px #ddd;" href="http://shop.androidbubbles.com/download/" target="_blank">Already a Pro Member?</a>
<?php endif; ?>





<?php $wpurl = get_bloginfo('wpurl'); ?>



<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

</form>
<?php 

	global $wpdb;

	$results = $wpdb->get_results("SELECT 
    
     table_name AS `table`, 
     round(((data_length + index_length) / 1024 / 1024), 2) `mb`      
FROM information_schema.TABLES 
WHERE table_schema = '$wpdb->dbname'
ORDER BY (data_length + index_length) DESC");

	$total_table = count($results);
	
	$db_size = array();
	
	$table_selected = isset($_GET['table'])?$_GET['table']:'';
	$table_submitted = isset($_POST['nonce_'])?base64_decode($_POST['nonce_']):'';
	
	$tables = array();
	if(!empty($results)){
		foreach($results as $table){
			$tables[$table->table] = $table->mb;			
		}
	}
	
	
	
	if(!empty($_POST) && isset($_POST['del']) && !empty($_POST['del']) && array_key_exists($table_submitted, $tables)){
		$primary_key = $wpdb->get_row("SHOW INDEX FROM $table_submitted WHERE Key_name = 'PRIMARY'");
		//pree($_POST);
		//pree($primary_key);exit;
		if(!empty($primary_key) && isset($primary_key->Column_name)){
			$primary_key = $primary_key->Column_name;
			$ids = $_POST['del'];
			$delete_query = "DELETE FROM $table_submitted WHERE $primary_key IN (".implode(',',$ids).")";
			
			//pree('delete_query:');pree($delete_query);exit;
						
						
			if ( 
				! isset( $_POST['del_table'] ) 
				|| ! wp_verify_nonce( $_POST['del_table'], 'del_table_fields' ) 
			) {
				pree('Injection Failed!');exit;
			
			}else{
				//pree($delete_query);exit;
				$wpdb->query($delete_query);
			}
			
		}
		
	}
	
	if($table_selected!='' && array_key_exists($table_selected, $tables)){
		$org_table_selected = str_replace($wpdb->prefix, '', $table_selected);
		$where = array();
		switch($org_table_selected){
			case "comments":
				$where[] = "comment_author NOT IN ('WooCommerce')";
				$where[] = "comment_approved!=1";
				
			break;
		}
		$where_str = '';
		if(!empty($where)){
			$where_str = 'WHERE '.implode(' AND ', $where);
		}
		
		$limit = ((
					isset($_GET['limit']) 
					&& 
					is_numeric($_GET['limit'])
				)?$_GET['limit']:10);
		
		
		$query_str = "SELECT * FROM $table_selected $where_str LIMIT $limit";
		
		//pree($query_str);
		
		$table_results = $wpdb->get_results($query_str);
		
		
		
		//pree($table_results);
	}
	
?>

<ul class="db-summary">
	<li>Database Size: <?php echo array_sum($tables); ?> MB</li> 
    <li>Total Tables: <?php echo $total_table; ?></li>    
</ul>


<?php if(!empty($tables)): ?>
<ol class="db-tables">
<?php foreach($tables as $name=>$size): ?>
	<li class="<?php echo ($table_selected==$name?'selected':''); ?>"><a href="admin.php?page=wm-db-checkup&table=<?php echo $name; ?>"><?php echo $name; ?> (<?php echo $size; ?> MB)</a>
    
    
<?php if($table_selected==$name && !empty($table_results)): $rows = 0;  ?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<?php wp_nonce_field( 'del_table_fields', 'del_table' ); ?>
<a href="admin.php?page=wm-db-checkup&table=<?php echo $table_selected; ?>&limit=1000">Click here to load more results</a>
<table cellpadding="0" cellspacing="0" border="1">
<?php foreach($table_results as $data): $rows++; ?>
<?php if($rows==1): $data_arr = (array)$data; $cols = array_keys($data_arr); ?>
<thead><tr>
<?php foreach($cols as $col): ?>
<th><?php echo $col; ?></th>
<?php endforeach; ?>
</tr></thead>
<?php endif; ?>
<tr>


<?php $field = 0; foreach($data as $values): $field++; ?>

<td>
<?php if($field==1): ?>
<input type="checkbox" class="checkbox" name="del[]" value="<?php echo $values; ?>" />
<?php endif; ?>
<?php echo $values; ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
<input type="hidden" name="nonce_" value="<?php echo base64_encode($table_selected); ?>" />
<input type="checkbox" id="select_all" /><label for="select_all">All</label>
<input type="submit" class="button button-primary button-large" value="Delete Selected Items" /> 
</form>
<?php endif; ?>    
    
    </li>
<?php endforeach; ?>    
</ol>
<?php endif; ?>









</div>