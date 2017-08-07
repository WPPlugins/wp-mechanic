<div class="wm-deprecated-check hide">
<div class="wm-ds">
<form method="post">
	<h2>As fast as one click</h2>
    <ul>
    <li><input type="radio" name="wm-only" value="plugins" id="po" /><label for="po">Plugins</label></li>
    <li><input type="radio" name="wm-only" value="themes" id="mo" checked="checked" /><label for="mo">Themes</label></li>
    </ul>
    <button type="submit" class="button-secondary" name="deprecated-check">Click here to start!</button>
</form>
</div>
<div class="wrap">
        <div class="icon32" id="icon-tools"><br></div>
        <div class="tool-box">
        	
            <h3 class="title">Paths to Search</h3>
            <p>All themes and plugin files will be checked. You may add extra paths to search by hooking the "deprecated_check_paths" filter. You can easily turn off search for the themes or plugin directories by defining the DEP_CHECK_NO_PLUGINS and DEP_CHECK_NO_THEMES as TRUE.
                <br><br><strong>Usage:</strong>
                <pre>

function add_deprecated_paths_to_check($paths){
    $paths["descriptive_slug"] = ABSPATH."wp_content/custom_folder";
    return $paths;
}
add_filter("deprecated_check_paths", "add_deprecated_paths_to_check", 0, 1);
                </pre>
            </p>
            <h3 class="title">Functions List</h3>
            <p>WordPress deprecated functions are collated automatically. You may add more functions to the deprecations array by hooking the "deprecated_check_functions" hook.
                <br><br><strong>Usage:</strong>
                <pre>

function add_deprecated_function($functions){
    $functions["deprecated_function_name"] = array(
        "new_function" =>"new_function()",
        "since" => "version_number"
    );
    return $functions;
}
add_filter("deprecated_check_functions", "add_deprecated_function", 0, 1);
                </pre>
            </p>
            
            
    </div>
</div>	