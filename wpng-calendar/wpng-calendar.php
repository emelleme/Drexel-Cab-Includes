<?php
/*
Plugin Name: Wordpress Google Calendar
Plugin URI: http://code.google.com/p/wpng-calendar/
Description: This plugin allows for the integration of a Google calendar into a Wordpress blog.
Version: 0.8.5
Author: L1 Jockeys
Author URI: http://code.google.com/p/wpng-calendar/

----------------------------------------------------------------------------
LICENSE
----------------------------------------------------------------------------
Copyright 2008  - L1 Jockeys  (email : l1jockeys@gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
----------------------------------------------------------------------------
*/

// Define class
if (!class_exists("WPNGCalendar")) {
	class WPNGCalendar {
		
		/*--------------------------------------------------------------------
		    General Functions
		  --------------------------------------------------------------------*/
		  
		// Class members
		var $adminOptionsName           = "wpng_cal_admin_options";
		var $adminKeyOptName            = "wpng_cal_api_key";
		var $adminCalFeedsOptName       = "wpng_cal_feeds";
		var $adminPageMaxEntriesOptName = "wpng_cal_page_max_entries";
		var $adminWikiOptName           = "wpng_cal_show_wiki";
		var $adminShowNavName		= "wpng_cal_show_nav";
		
		var $widgetOptionsName		= "wpng_cal_widget_options";
		var $widgetListSizeOptName	= "wpng_cal_widget_list_size";
		
		var $showCalendar               = "show-wpng-calendar";
			
		// Constructor
		function WPNGCalendar() {
			
		}
		
		// Initialization function
		function init() {
			$this->getAdminOptions();
		}
		
		/*--------------------------------------------------------------------
		    Administrative Functions
		  --------------------------------------------------------------------*/
	  
		// Option loader function
		function getAdminOptions() {
			// Set default values for the options
			$adminKeyOpt            = "";
			$adminCalFeedsOpt       = "http://www.google.com/calendar/feeds/pdighgf028nmbjbrno8oed8vsg%40group.calendar.google.com/public/full";
			$adminPageMaxEntriesOpt = 15;
			$adminWikiOpt           = false;
			$adminShowNav		= true;
			$adminOptions = array($this->adminKeyOptName => $adminKeyOpt,
				              $this->adminCalFeedsOptName => $adminCalFeedsOpt,
					      $this->adminPageMaxEntriesOptName => $adminPageMaxEntriesOpt,
					      $this->adminWikiOptName => $adminWikiOpt,
					      $this->adminShowNavName => $adminShowNav);
			
			// Get values from the WP options table in the database, re-assign if found
			$dbOptions = get_option($this->adminOptionsName);
			if (!empty($dbOptions)) {
				foreach ($dbOptions as $key => $option)
					$adminOptions[$key] = $option;
			}
			
			// Update the options for the panel
			update_option($this->adminOptionsName, $adminOptions);
			return $adminOptions;
		}
		
		// Print the admin page for the plugin
		function printAdminPage() {
			// Get the admin options
			$adminOptions = $this->getAdminOptions();
										
			if (isset($_POST['update_wpngCalendarSettings'])) { 
				if (isset($_POST['wpngAPIKey'])) {
					$adminOptions[$this->adminKeyOptName] = $_POST['wpngAPIKey'];
				}	
				if (isset($_POST['wpngCalFeeds'])) {
					$adminOptions[$this->adminCalFeedsOptName] = $_POST['wpngCalFeeds'];
				}
				if (isset($_POST['wpngPageMax'])) {
					$adminOptions[$this->adminPageMaxEntriesOptName] = $_POST['wpngPageMax'];
				}
				if (isset($_POST['wpngShowWiki'])) {
					$adminOptions[$this->adminWikiOptName] = $_POST['wpngShowWiki'];
				}
				if (isset($_POST['wpngShowNav'])) {
					$adminOptions[$this->adminShowNavName] = $_POST['wpngShowNav'];
				}
				update_option($this->adminOptionsName, $adminOptions);
				// update settings notification below
				?>
				<div class="updated"><p><strong><?php _e("Settings Updated.", "WPNGCalendar");?></strong></p></div>
			<?php
			}
			// Display HTML form for the options below
			?>
			<div class=wrap>
				<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
					<h2>Wordpress Google Calendar Plugin</h2>
					<h3>Google GDATA API Key</h3>
					<p><input name="wpngAPIKey" style="width: 95%;" value="<?php _e(apply_filters('format_to_edit',$adminOptions[$this->adminKeyOptName]), 'WPNGCalendar') ?>" /></p>
					<h3>Google Calendar Feed</h3>
					<p>The feed must be a public feed viewable by anyone; be sure to use the address for the full feed, <em>not basic</em></p>
					<p><input name="wpngCalFeeds" style="width: 95%;" value="<?php _e(apply_filters('format_to_edit',$adminOptions[$this->adminCalFeedsOptName]), 'WPNGCalendar') ?>" /></p>
					<h3>Maximum # of Entries To Show On A Page</h3>
					<p><input name="wpngPageMax" style="width: 5%;" value="<?php _e(apply_filters('format_to_edit',$adminOptions[$this->adminPageMaxEntriesOptName]), 'WPNGCalendar') ?>" /></p>
					<h3>Process Calendar Description As Wiki Markup</h3>
					<p><label for="wpngShowWiki_yes"><input type="radio" id="wpngShowWiki_yes" name="wpngShowWiki" value="true" <?php if ($adminOptions[$this->adminWikiOptName] == "true") { _e('checked="checked"', 'WPNGCalendar'); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpngShowWiki_no"><input type="radio" id="wpngShowWiki_no" name="wpngShowWiki" value="false" <?php if ($adminOptions[$this->adminWikiOptName] == "false") { _e('checked="checked"', "WPNGCalendar"); }?>/> No</label></p>
					<h3>Show Navigation Links On The Page</h3>
					<p><label for="wpngShowNav_yes"><input type="radio" id="wpngShowNav_yes" name="wpngShowNav" value="true" <?php if ($adminOptions[$this->adminShowNavName] == "true") { _e('checked="checked"', 'WPNGCalendar'); }?> /> Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;<label for="wpngShowNav_no"><input type="radio" id="wpngShowNav_no" name="wpngShowNav" value="false" <?php if ($adminOptions[$this->adminShowNavName] == "false") { _e('checked="checked"', "WPNGCalendar"); }?>/> No</label></p>
					
					<div class="submit">
						<input type="submit" name="update_wpngCalendarSettings" value="<?php _e('Update Settings', 'WPNGCalendar') ?>" />
					</div>
				</form>
			</div>
			<?php
		}
		
		/*--------------------------------------------------------------------
		    Content Header Functions
		  --------------------------------------------------------------------*/
		
		// Add Google Calendar Javascript API Key registration
		function addJSAPIHeader() {
			$adminOptions = $this->getAdminOptions();
			?>
			<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo($adminOptions[$this->adminKeyOptName]) ?>"></script>
			<?php
		}
		
		// Add the plugin's necessary objects to the header
		function addWPNGHeader() {
			// Add the DateJS file			
			wp_enqueue_script('date-js', get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/js/date.js', null, 'alpha-1');
			// Add the ThickBox files
			wp_enqueue_script('jquery-js', get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/js/jquery.js', null, '6124');
			wp_enqueue_script('thickbox-js', get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/js/thickbox.js', null, '3.1');
			echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/css/thickbox.css" />';
			// Add the Wiky converter file
			wp_enqueue_script('wiky-js', get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/js/wiky.js', null, '1.0');
			
			// Add the plugin's JS file
			wp_enqueue_script('wpng-calendar', get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/js/functions.js', array('date-js'), '0.85');
			
			// Add the plugin's CSS file
			echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/wpng-calendar/css/style.css" />';
		}
		
		// Add the display settings to the header
		function addWPNGSettings() {
			$adminOptions = $this->getAdminOptions();
			?>
			<script type="text/javascript">
				var calendarURL = '<?php echo($adminOptions[$this->adminCalFeedsOptName]) ?>';
				var pageMaxResults = <?php echo($adminOptions[$this->adminPageMaxEntriesOptName]) ?>;
				var parseWiki = <?php echo($adminOptions[$this->adminWikiOptName]) ?>;
				var showNav = <?php echo($adminOptions[$this->adminShowNavName]) ?>;
				var weeks = null;
				var widgetListSize = null;
		        </script>
			<?php
		}
		
		// Add onLoad function to work around IE 6 & 7 bugs
		function addOnLoadScript() {
			?>
			<script type="text/javascript">
			function addLoadEvent(func) {
				var oldonload = window.onload;
				if (typeof window.onload != 'function') {
					window.onload = func;
				} else {
					window.onload = function() {
					if (oldonload) {
						oldonload();
					}
						func();
					}
				}
			}
			
			//addLoadEvent(loadCalTest);
			</script>
			<?php
		}
		
		/*--------------------------------------------------------------------
		    Content Page Functions
		  --------------------------------------------------------------------*/
		
		// Place the calendar in a Page
		function placePageCalendar($content) {
			// Only do this if this is a page and it has the appropriate custom field
			if (is_page()) {
				$custFieldValues = get_post_custom_values($this->showCalendar);
				if ($custFieldValues != NULL) {
					$weeks = $custFieldValues[0];
					$content = $this->displayPageCalendar($weeks);
				}
			}
			return $content;
		}
		
		// Display the calendar in a Page
		function displayPageCalendar($weeks) {
			// Get the feed URL from the options
			$adminOptions = $this->getAdminOptions();
			// Set the default for number of weeks to query if none (or invalid)
			if (($weeks == null) or (!is_numeric($weeks))) {
				$weeks = 4;
			}
			?>
			<div id="wpng-cal-events" style="display:none;"></div>
			<div id="wpng-cal-load-page" class="wpng-cal-loading">
				<img class="wpng-cal-image" src='/wp-content/plugins/wpng-calendar/images/loading_large.gif'/>
			</div>
			<div>
			<script type="text/javascript">
			  	weeks = <?php echo($weeks) ?>;
				addLoadEvent(loadCalendarByWeeks);
			</script>
			</div>
			<?php
		}
		
		/*--------------------------------------------------------------------
		    Widget Functions
		  --------------------------------------------------------------------*/
		  
		function widgetWPNGCalendarInit() {
		
			if(!function_exists('register_sidebar_widget')) { return; }
			function widgetWPNGCalendar($args) {
				extract($args);
				echo $before_widget . $before_title . $after_title;
				
				if(!$options = get_option('wpng_cal_widget_options')) { 
					$options = array('wpng_cal_widget_list_size' => 5, 'wpng_cal_widget_title' => 'Upcoming Events');
				}
				?>
				<h2 class="widgettitle"><?php echo($options['wpng_cal_widget_title']) ?></h2>
				<div id="wpng-cal-widget-events" style="display:none;"></div>
				<div id="wpng-cal-load-widget" class="wpng-cal-loading">
					<img class="wpng-cal-image" src='/wp-content/plugins/wpng-calendar/images/loading_large.gif'/>
				</div>
				<div>
				<script type="text/javascript" defer>
					widgetListSize = <?php echo($options['wpng_cal_widget_list_size']) ?>;
					addLoadEvent(loadCalendarWidget);
				</script>
				</div>
				<?php
				echo $after_widget;
			}
			
			function widgetWPNGCalendarOptions()
			{
				if(!$options = get_option('wpng_cal_widget_options')) { 
					$options = array('wpng_cal_widget_list_size' => 5, 'wpng_cal_widget_title' => 'Upcoming Events');
				}
				
				if($_POST['updateWPNGWidgetSettings']) {
					$options = array('wpng_cal_widget_list_size' => $_POST['evnt_cnt'], 'wpng_cal_widget_title' => $_POST['evnt_list_title']);
					update_option('wpng_cal_widget_options', $options);
				}
				echo '<p>Sidebar title:<input type="text" name="evnt_list_title" value="'.$options['wpng_cal_widget_title'].'" id="evnt_list_title" /></p>';
				
				echo '<p>Number of events to show:<input type="text" name="evnt_cnt" value="'.$options['wpng_cal_widget_list_size'].'" id="evnt_cnt" /></p>';
				
				echo '<input type="hidden" id="updateWPNGWidgetSettings" name="updateWPNGWidgetSettings" value="1" />';
			}
			
			register_sidebar_widget('WPNG Calendar','widgetWPNGCalendar');
			register_widget_control('WPNG Calendar','widgetWPNGCalendarOptions', 200, 200);
		}
	}
}

// Instantiate the class
if (class_exists("WPNGCalendar")) {
	$dl_pluginWPNGCal = new WPNGCalendar();
}

// Initialize the admin panel if the plugin has been activated
if (!function_exists("WPNGCalendar_ap")) {
	function WPNGCalendar_ap() {
		global $dl_pluginWPNGCal;
		if (!isset($dl_pluginWPNGCal)) {
			return;
		}
		if (function_exists('add_options_page')) {
			add_options_page('WPNG Calendar Plugin', 'WPNG Calendar Plugin', 9, basename(__FILE__), array(&$dl_pluginWPNGCal, 'printAdminPage'));
		}
	}	
}

// Actions and filters	
if (isset($dl_pluginWPNGCal)) {
	/*--------------------------------------------------------------------
	    Actions
	  --------------------------------------------------------------------*/
	  
	// Add the admin menu
	add_action('admin_menu', 'WPNGCalendar_ap');
	// Initialize options on plugin activation
	add_action("activate_wpng-calendar/wpng-calendar.php",  array(&$dl_pluginWPNGCal, 'init'));
	// Add plugin needs to the header of each page
	add_action('wp_head', array(&$dl_pluginWPNGCal, 'addJSAPIHeader'), 1);
	add_action('wp_head', array(&$dl_pluginWPNGCal, 'addWPNGSettings'), 1);
	add_action('wp_head', array(&$dl_pluginWPNGCal, 'addWPNGHeader'), 1);
	add_action('wp_head', array(&$dl_pluginWPNGCal, 'addOnLoadScript'), 1);
	// Add the widget
	add_action('plugins_loaded', array(&$dl_pluginWPNGCal, 'widgetWPNGCalendarInit'), 1);
	
	/*--------------------------------------------------------------------
	    Filters
	  --------------------------------------------------------------------*/
	
	// Filter to display the calendar in a page
	add_filter('the_content', array(&$dl_pluginWPNGCal, 'placePageCalendar'), '7');
}

?>
