<?php
/*
Plugin Name: Post Expirator
Plugin URI: http://wordpress.org/extend/plugins/post-expirator/
Description: Allows you to add an expiration date (hourly) to posts which you can configure to either delete the post or change it to a draft.
Author: Aaron Axelsen
Version: 1.3.1
Author URI: http://www.frozenpc.net
*/

// Default Values
$expirationdateDefaultDateFormat = 'l F jS, Y';
$expirationdateDefaultTimeFormat = 'g:ia';
$expirationdateDefaultFooterContents = 'Post expires at EXPIRATIONTIME on EXPIRATIONDATE';
$expirationdateDefaultFooterStyle = 'font-style: italic;';

# Save for future use
#function blah_blah_blah($array) {
#       $array['minute'] = array(
#               'interval' => 60,
#               'display' => __('Once a Minute')
#       );
#	return $array;
#}
#add_filter('cron_schedules','blah_blah_blah');
#print_r(wp_get_schedules());

/** 
 * Function that does the actualy deleting - called by wp_cron
 */
function expirationdate_delete_expired_posts() {
	global $wpdb;
	$result = $wpdb->get_results('select post_id, meta_value from ' . $wpdb->postmeta . ' as postmeta, '.$wpdb->posts.' as posts where postmeta.post_id = posts.ID AND posts.post_status = "publish" AND postmeta.meta_key = "expiration-date" AND postmeta.meta_value <= "' . mktime() . '"');
  	if (!empty($result)) foreach ($result as $a) {
		$post_result = $wpdb->get_var('select post_type from ' . $wpdb->posts .' where ID = '. $a->post_id);
		if ($post_result == 'post') {
			$expiredStatus = strtolower(get_option('expirationdateExpiredPostStatus'));
		} else if ($post_result == 'page') {
			$expiredStatus = strtolower(get_option('expirationdateExpiredPageStatus'));
		} else {
			$expiredStatus = 'draft';
		}

		if ($expiredStatus == 'delete')
			wp_delete_post($a->post_id);

		else {
			$postcat[] = 8;
			wp_update_post(array('ID' => $a->post_id, 'post_category' => $postcat));
	                delete_post_meta($a->post_id, 'expiration-date');
        	        update_post_meta($a->post_id, 'expiration-date', $a->meta_value, true);
		}
	}
}
add_action ('expirationdate_delete_'.$current_blog->blog_id, 'expirationdate_delete_expired_posts');

/** 
 * Called at plugin activation
 */
function expirationdate_activate () {
	global $current_blog,$expirationdateDefaultDateFormat,$expirationdateDefaultTimeFormat,$expirationdateDefaultFooterContents,$expirationdateDefaultFooterStyle;
	update_option('expirationdateExpiredPostStatus','Draft');
	update_option('expirationdateExpiredPageStatus','Draft');
	update_option('expirationdateDefaultDateFormat',$expirationdateDefaultDateFormat);
	update_option('expirationdateDefaultTimeFormat',$expirationdateDefaultTimeFormat);
	update_option('expirationdateFooterContents',$expirationdateDefaultFooterContents);
	update_option('expirationdateFooterStyle',$expirationdateDefaultFooterStyle);
        update_option('expirationdateDisplayFooter',0);

	wp_schedule_event(mktime(date('H'),0,0,date('m'),date('d'),date('Y')), 'hourly', 'expirationdate_delete_'.$current_blog->blog_id);
}
register_activation_hook (__FILE__, 'expirationdate_activate');

/**
 * Called at plugin deactivation
 */
function expirationdate_deactivate () {
	global $current_blog;
	delete_option('expirationdateExpiredPostStatus');
	delete_option('expirationdateExpiredPageStatus');
	delete_option('expirationdateDefaultDateFormat');
	delete_option('expirationdateDefaultTimeFormat');
        delete_option('expirationdateDisplayFooter');
        delete_option('expirationdateFooterContents');
        delete_option('expirationdateFooterStyle');
	wp_clear_scheduled_hook('expirationdate_delete_'.$current_blog->blog_id);
}
register_deactivation_hook (__FILE__, 'expirationdate_deactivate');

/**
 * adds an 'Expires' column to the post display table.
 */
function expirationdate_add_column ($columns) {
  	$columns['expirationdate'] = 'Expires <br/><span style="font-size: 0.8em; font-weight: normal;">(YYYY/MM/DD HH)</span>';
  	return $columns;
}
add_filter ('manage_posts_columns', 'expirationdate_add_column');
add_filter ('manage_pages_columns', 'expirationdate_add_column');

/**
 * fills the 'Expires' column of the post display table.
 */
function expirationdate_show_value ($column_name) {
	global $wpdb, $post;
	$id = $post->ID;
	if ($column_name === 'expirationdate') {
    		$query = "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = \"expiration-date\" AND post_id=$id";
    		$ed = $wpdb->get_var($query);
    		echo ($ed ? date('Y/m/d H',$ed) : "Never");
  	}
}
add_action ('manage_posts_custom_column', 'expirationdate_show_value');
add_action ('manage_pages_custom_column', 'expirationdate_show_value');

/**
 * Add's hooks to get the meta box added to post
 */
function expirationdate_meta_post() {
	add_meta_box('expirationdatediv', __('Post Expirator'), 'expirationdate_meta_box', 'post', 'advanced', 'high');
}
add_action ('dbx_post_advanced','expirationdate_meta_post');

/**
 * Add's hooks to get the meta box added to page
 */
function expirationdate_meta_page() {
	add_meta_box('expirationdatediv', __('Post Expirator'), 'expirationdate_meta_box', 'page', 'advanced', 'high');
}
add_action ('edit_page_form','expirationdate_meta_page');

/**
 * Actually adds the meta box
 */
function expirationdate_meta_box($post) { 
	// Get default month
	$expirationdatets = get_post_meta($post->ID,'expiration-date',true);
	if (empty($expirationdatets)) {
		$defaultmonth = date('F');
		$defaultday = date('d');
		$defaulthour = date('H');
		$defaultyear = date('Y');
		$disabled = 'disabled="disabled"';
	} else {
		$defaultmonth = date('F',$expirationdatets);
		$defaultday = date('d',$expirationdatets);
		$defaultyear = date('Y',$expirationdatets);
		$defaulthour = date('H',$expirationdatets);

		$enabled = ' checked="checked"';
		$disabled = '';
	}

	$rv = array();
	$rv[] = '<p><input type="checkbox" name="enable-expirationdate" id="enable-expirationdate" value="checked"'.$enabled.' onclick="expirationdate_ajax_add_meta(\'enable-expirationdate\')" />';
	$rv[] = '<label for="enable-expirationdate">Enable Post Expiration</label></p>';
	$rv[] = '<table><tr>';
	   $rv[] = '<th style="text-align: left;">Month</th>';
	   $rv[] = '<th style="text-align: left;">Day</th>';
	   $rv[] = '<th style="text-align: left;">Year</th>';
	   $rv[] = '<th style="text-align: left;"></th>';
	   $rv[] = '<th style="text-align: left;">Hour (24 Hour Format)</th>';
	$rv[] = '</tr><tr>';
	$rv[] = '<td>';
	$rv[] = '<select name="expirationdate_month" id="expirationdate_month"'.$disabled.'">';
	for($i = 1; $i <= 12; $i++) {
		if ($defaultmonth == date('F',mktime(0, 0, 0, $i, 1, date("Y"))))
			$selected = ' selected="selected"';
		else
			$selected = '';
		$rv[] = '<option value="'.date('m',mktime(0, 0, 0, $i, 1, date("Y"))).'"'.$selected.'>'.date('F',mktime(0, 0, 0, $i, 1, date("Y"))).'</option>';
	}
	$rv[] = '</select>';
	$rv[] = '</td><td>';
	$rv[] = '<input type="text" id="expirationdate_day" name="expirationdate_day" value="'.$defaultday.'" size="2"'.$disabled.'" />,'; 
	$rv[] = '</td><td>';
	$rv[] = '<select name="expirationdate_year" id="expirationdate_year"'.$disabled.'">';
	$currentyear = date('Y');
	if ($defaultyear < $currentyear)
		$currentyear = $defaultyear;
	for($i = $currentyear; $i < $currentyear + 8; $i++) {
		if ($i == $defaultyear)
			$selected = ' selected="selected"';
		else
			$selected = '';
		$rv[] = '<option'.$selected.'>'.($i).'</option>';
	}
	$rv[] = '</select>';
	$rv[] = '</td><td>@</td><td>';
	$rv[] = '<input type="text" id="expirationdate_hour" name="expirationdate_hour" value="'.$defaulthour.'" size="2"'.$disabled.'" />';
	$rv[] = '<input type="hidden" name="expirationdate_formcheck" value="true" />';
	$rv[] = '</td></tr></table>';

	$rv[] = '<div id="expirationdate_ajax_result"></div>';

	echo implode("\n",$rv);
}

/**
 * PHP Code to be executed by ajax function call - currently nothing happens
 */
function expirationdate_ajax_process() {
	// Gather Values
	$enable = $_POST['enable'];
	
	die(0);
}
add_action ('wp_ajax_expirationdate_ajax','expirationdate_ajax_process');


/**
 * Add's ajax javascript
 */
function expirationdate_js_admin_header() {
	// use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'sack' ));

	// Define custom JavaScript function
	?>
<script type="text/javascript">
//<![CDATA[
function expirationdate_ajax_add_meta(expireenable) {
	var mysack = new sack("<?php expirationdate_get_blog_url(); ?>wp-admin/admin-ajax.php");

	var expire = document.getElementById(expireenable);

	if (expire.checked == true) {
		var enable = 'true';
		document.getElementById('expirationdate_month').disabled = false;
		document.getElementById('expirationdate_day').disabled = false;
		document.getElementById('expirationdate_year').disabled = false;
		document.getElementById('expirationdate_hour').disabled = false;
	} else {
		document.getElementById('expirationdate_month').disabled = true;
		document.getElementById('expirationdate_day').disabled = true;
		document.getElementById('expirationdate_year').disabled = true;
		document.getElementById('expirationdate_hour').disabled = true;
		var enable = 'false';
	}
	
	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( "action", "expirationdate_ajax" );
	mysack.setVar( "enable", enable );
	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error in looking up elevation' )};
	mysack.runAJAX();

	return true;
}
//]]>
</script>
<?php
}
add_action('admin_print_scripts', 'expirationdate_js_admin_header' );

/**
 * Get correct URL (HTTP or HTTPS)
 */
function expirationdate_get_blog_url() {
	global $current_blog;
	$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
        echo $schema.$current_blog->domain.$current_blog->path;
}

/**
 * Called when post is saved - stores expiration-date meta value
 */
function expirationdate_update_post_meta($id) {
	if (!isset($_POST['expirationdate_formcheck']))
		return false;

        $month = $_POST['expirationdate_month'];
        $day = $_POST['expirationdate_day'];
        $year = $_POST['expirationdate_year'];
        $hour = $_POST['expirationdate_hour'];

	if (isset($_POST['enable-expirationdate'])) {
        	// Format Date
        	$ts = mktime($hour,0,0,$month,$day,$year);
        	// Update Post Meta
		delete_post_meta($id, 'expiration-date');
	        update_post_meta($id, 'expiration-date', $ts, true);
	} else {
		delete_post_meta($id, 'expiration-date');
	}
}
add_action('save_post','expirationdate_update_post_meta');

/**
 * Hook's to add plugin page menu
 */
function expirationdate_plugin_menu() {
	add_submenu_page('options-general.php','Post Expirator Options','Post Expirator',9,basename(__FILE__),'expirationdate_show_options');
}
add_action('admin_menu', 'expirationdate_plugin_menu');

/**
 * Show the Expiration Date options page
 */
function expirationdate_show_options() {

	if ($_POST['expirationdateSave']) {
		update_option('expirationdateExpiredPostStatus',$_POST['expired-post-status']);
		update_option('expirationdateExpiredPageStatus',$_POST['expired-page-status']);
		update_option('expirationdateDefaultDateFormat',$_POST['expired-default-date-format']);
		update_option('expirationdateDefaultTimeFormat',$_POST['expired-default-time-format']);
		update_option('expirationdateDisplayFooter',$_POST['expired-display-footer']);
		update_option('expirationdateFooterContents',$_POST['expired-footer-contents']);
		update_option('expirationdateFooterStyle',$_POST['expired-footer-style']);
                echo "<div id='message' class='updated fade'><p>Saved Options!</p></div>";
	}

	// Get Option
	$expirationdateExpiredPostStatus = get_option('expirationdateExpiredPostStatus');
	if (empty($expirationdateExpiredPostStatus))
		$expirationdateExpiredPostStatus = 'Draft';

	$expirationdateExpiredPageStatus = get_option('expirationdateExpiredPageStatus');
	if (empty($expirationdateExpiredPageStatus))
		$expirationdateExpiredPageStatus = 'Draft';

	$expirationdateDefaultDateFormat = get_option('expirationdateDefaultDateFormat');
	if (empty($expirationdateDefaultDateFormat)) {
		global $expirationdateDefaultDateFormat;
		$expirationdateDefaultDateFormat = $expirationdateDefaultDateFormat;
	}

	$expirationdateDefaultTimeFormat = get_option('expirationdateDefaultTimeFormat');
	if (empty($expirationdateDefaultTimeFormat)) {
		global $expirationdateDefaultTimeFormat;
		$expirationdateDefaultTimeFormat = $expirationdateDefaultTimeFormat;
	}

	$expireddisplayfooter = get_option('expirationdateDisplayFooter');
	if (empty($expireddisplayfooter))
		$expireddisplayfooter = 0;

	$expireddisplayfooterenabled = '';
	$expireddisplayfooterdisabled = '';
	if ($expireddisplayfooter == 0)
		$expireddisplayfooterdisabled = 'checked="checked"';
	else if ($expireddisplayfooter == 1)
		$expireddisplayfooterenabled = 'checked="checked"';
	
	$expirationdateFooterContents = get_option('expirationdateFooterContents');
	if (empty($expirationdateFooterContents)) {
		global $expirationdateDefaultFooterContents;
		$expirationdateFooterContents = $expirationdateDefaultFooterContents;
	}

	$expirationdateFooterStyle = get_option('expirationdateFooterStyle');
	if (empty($expirationdateFooterStyle)) {
		global $expirationdateDefaultFooterStyle;
		$expirationdateFooterStyle = $expirationdateDefaultFooterStyle;
	}

	?>
<div class="wrap">
	<h2><?php _e('Post Expirator Options'); ?></h2>
	<p>
	The post expirator plugin sets a custom meta value, and then optionally allows you to select if you want the post
	changed to a draft status or deleted when it expires.
	</p>
	<p>Valid [postexpiration] attributes:
	<ul>
		<li>type - defaults to full - valid options are full,date,time</li>
		<li>dateformat - format set here will override the value set on the settings page</li>
		<li>timeformat - format set here will override the value set on the settings page</li>
	</ul>
	</p>
	<form method="post" id="expirationdate_save_options">
		<h3>Defaults</h3>
		<table class="form-table">
			<tr valign-"top">
				<th scope="row"><label for="expired-post-status">Set Post To:</label></th>
				<td>
					<select name="expired-post-status" id="expired-post-status">
					<option<?php if ($expirationdateExpiredPostStatus == 'Draft'){ echo ' selected="selected"';}?>>Draft</option>
					<option<?php if ($expirationdateExpiredPostStatus == 'Delete'){ echo ' selected="selected"';}?>>Delete</option>
					</select>	
					<br/>
					Select whether the post should be deleted or changed to a draft at expiration time.
				</td>
			</tr>
			<tr valign-"top">
				<th scope="row"><label for="expired-page-status">Set Page To:</label></th>
				<td>
					<select name="expired-page-status" id="expired-page-status">
					<option<?php if ($expirationdateExpiredPageStatus == 'Draft'){ echo ' selected="selected"';}?>>Draft</option>
					<option<?php if ($expirationdateExpiredPageStatus == 'Delete'){ echo ' selected="selected"';}?>>Delete</option>
					</select>	
					<br/>
					Select whether the page should be deleted or changed to a draft at expiration time.
				</td>
			</tr>
			<tr valign-"top">
				<th scope="row"><label for="expired-default-date-format">Date Format:</label></th>
				<td>
					<input type="text" name="expired-default-date-format" id="expired-default-date-format" value="<?php echo $expirationdateDefaultDateFormat ?>" size="25" /> (<?php echo date("$expirationdateDefaultDateFormat") ?>)
					<br/>
					The default format to use when displaying the expiration date within a post using the [postexpirator] 
					shortcode or within the footer.  For information on valid formatting options, see: <a href="http://us2.php.net/manual/en/function.date.php" target="_blank">PHP Date Function</a>.
				</td>
			</tr>
			<tr valign-"top">
				<th scope="row"><label for="expired-default-time-format">Time Format:</label></th>
				<td>
					<input type="text" name="expired-default-time-format" id="expired-default-time-format" value="<?php echo $expirationdateDefaultTimeFormat ?>" size="25" /> (<?php echo date("$expirationdateDefaultTimeFormat") ?>)
					<br/>
					The default format to use when displaying the expiration time within a post using the [postexpirator] 
					shortcode or within the footer.  For information on valid formatting options, see: <a href="http://us2.php.net/manual/en/function.date.php" target="_blank">PHP Date Function</a>.
				</td>
			</tr>
		</table>
		<h3>Post Footer Display</h3>
		<p>Enabling this below will display the expiration date automatically at the end of any post which is set to expire.</p>
		<table class="form-table">
			<tr valign-"top">
				<th scope="row">Show in post footer?</th>
				<td>
					<input type="radio" name="expired-display-footer" id="expired-display-footer-true" value="1" <?php echo $expireddisplayfooterenabled ?>/> <label for="expired-display-footer-true">Enabled</label> 
					<input type="radio" name="expired-display-footer" id="expired-display-footer-false" value="0" <?php echo $expireddisplayfooterdisabled ?>/> <label for="expired-display-footer-false">Disabled</label>
					<br/>
					This will enable or disable displaying the post expiration date in the post footer.
				</td>
			</tr>
			<tr valign-"top">
				<th scope="row"><label for="expired-footer-contents">Footer Contents:</label></th>
				<td>
					<textarea id="expired-footer-contents" name="expired-footer-contents" rows="3" cols="50"><?php echo $expirationdateFooterContents; ?></textarea>
					<br/>
					Enter the text you would like to appear at the bottom of every post that will expire.  The following placeholders will be replaced
					with the post expiration date in the following format:
					<ul>
						<li>EXPIRATIONFULL -> <?php echo date("$expirationdateDefaultDateFormat $expirationdateDefaultTimeFormat") ?></li>
						<li>EXPIRATIONDATE -> <?php echo date("$expirationdateDefaultDateFormat") ?></li>
						<li>EXPIRATIONTIME -> <?php echo date("$expirationdateDefaultTimeFormat") ?></li>
					</ul>
				</td>
			</tr>
			<tr valign-"top">
				<th scope="row"><label for="expired-footer-style">Footer Style:</label></th>
				<td>
					<input type="text" name="expired-footer-style" id="expired-footer-style" value="<?php echo $expirationdateFooterStyle ?>" size="25" />
					(<span style="<?php echo $expirationdateFooterStyle ?>">This post will expire on <?php echo date("$expirationdateDefaultDateFormat $expirationdateDefaultTimeFormat"); ?></span>)
					<br/>
					The inline css which will be used to style the footer text.
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="expirationdateSave" value="Save" />
		</p>
	</form>
</div>
	<?php
}

// [postexpirator format="l F jS, Y g:ia" tz="foo"]
function postexpirator_shortcode($atts) {
	global $post;

        $expirationdatets = get_post_meta($post->ID,'expiration-date',true);
	if (empty($expirationdatets))
		return false;

	extract(shortcode_atts(array(
		'dateformat' => get_option('expirationdateDefaultDateFormat'),
		'timeformat' => get_option('expirationdateDefaultTimeFormat'),
		'type' => full,
		'tz' => date('T')
	), $atts));

	if (empty($dateformat)) {
		global $expirationdateDefaultDateFormat;
		$dateformat = $expirationdateDefaultDateFormat;		
	}

	if (empty($timeformat)) {
		global $expirationdateDefaultTimeFormat;
		$timeformat = $expirationdateDefaultTimeFormat;		
	}

	if ($type == 'full') 
		$format = $dateformat.' '.$timeformat;
	else if ($type == 'date')
		$format = $dateformat;
	else if ($type == 'time')
		$format = $timeformat;

	return date("$format",$expirationdatets);
}
add_shortcode('postexpirator', 'postexpirator_shortcode');

function postexpirator_add_footer($text) {
	global $post;

	// Check to see if its enabled
	$displayFooter = get_option('expirationdateDisplayFooter');
	if ($displayFooter === false || $displayFooter == 0)
		return $text;

        $expirationdatets = get_post_meta($post->ID,'expiration-date',true);
	if (!is_numeric($expirationdatets))
		return $text;

        $dateformat = get_option('expirationdateDefaultDateFormat');
	if (empty($dateformat)) {
		global $expirationdateDefaultDateFormat;
		$dateformat = $expirationdateDefaultDateFormat;		
	}

        $timeformat = get_option('expirationdateDefaultTimeFormat');
	if (empty($timeformat)) {
		global $expirationdateDefaultTimeFormat;
		$timeformat = $expirationdateDefaultTimeFormat;		
	}

        $expirationdateFooterContents = get_option('expirationdateFooterContents');
        if (empty($expirationdateFooterContents)) {
                global $expirationdateDefaultFooterContents;
                $expirationdateFooterContents = $expirationdateDefaultFooterContents;
        }
	
        $expirationdateFooterStyle = get_option('expirationdateFooterStyle');
        if (empty($expirationdateFooterStyle)) {
                global $expirationdateDefaultFooterStyle;
                $expirationdateFooterStyle = $expirationdateDefaultFooterStyle;
        }
	
	$search = array(
		'EXPIRATIONFULL',
		'EXPIRATIONDATE',
		'EXPIRATIONTIME'
	);
	$replace = array(
		date("$dateformat $timeformat",$expirationdatets),
		date("$dateformat",$expirationdatets),
		date("$timeformat",$expirationdatets)
	);

	$add_to_footer = '<p style="'.$expirationdateFooterStyle.'">'.str_replace($search,$replace,$expirationdateFooterContents).'</p>';
	return $text.$add_to_footer;
}
add_action('the_content','postexpirator_add_footer',0);
