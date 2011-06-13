<?php
/**
 Plugin Name: Simple Section Navigation Widget
 Plugin URI: http://www.cmurrayconsulting.com/software/wordpress-simple-section-navigation/
 Description: Adds a <strong>widget</strong> to your sidebar for <strong>section based navigation</strong>... essential for <strong>CMS</strong> implementations! The <strong>title of the widget is the top level page</strong> within the current section. Shows all page siblings (except on the top level page), all parents and grandparents (and higher), the siblings of all parents and grandparents (up to top level page), and any immediate children of the current page. Can also be called by a function inside template files. May <strong>exclude any pages or sections</strong>. Uses standard WordPress navigation classes for easy styling. 
 Version: 1.3.1
 Author: Jacob M Goldman (C. Murray Consulting)
 Author URI: http://www.cmurrayconsulting.com

    Plugin: Copyright 2009 C. Murray Consulting  (email : jake@cmurrayconsulting.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//*********//
//PLUG INIT//
//*********//
function ssn_admin_init() {
	register_setting('ssn-options', 'ssn_show_all');
	register_setting('ssn-options', 'ssn_exclude');
	register_setting('ssn-options', 'ssn_hide_on_excluded');
	//register_setting('ssn-options', 'ssn_cats_as_pages');
	register_setting('ssn-options', 'ssn_show_on_home');
	register_setting('ssn-options', 'ssn_show_empty');
	register_setting('ssn-options', 'ssn_a_heading');
	register_setting('ssn-options', 'ssn_sortby');
}
add_action( 'admin_init', 'ssn_admin_init' );

function ssn_plugin_actlinks( $links ) { 
 // Add a link to this plugin's settings page
 $plugin = plugin_basename(__FILE__);
 $settings_link = sprintf( '<a href="options-general.php?page=%s">%s</a>', $plugin, __('Settings') ); 
 array_unshift( $links, $settings_link ); 
 return $links; 
}
if(is_admin()) add_filter("plugin_action_links_".$plugin, 'ssn_plugin_actlinks' );

//*******************//
//***CORE FUNCTION***//
//*******************//

function simple_section_nav($before_title="",$after_title="") {
	$ssn_args = array('before_title'=>$before_title,'after_title' => $after_title);
	widget_ssn($ssn_args);
}

//****************************//
//*** WIDGET INITIALIZATION **//
//****************************//

function widget_ssn($args) {
	if($args) extract($args);  
  	
  	$ssn_show_on_home = get_option('ssn_show_on_home');
  	if (is_front_page() && !$ssn_show_on_home) return false;	//if we're on the home page and we haven't chosen to show this anyways, leave
  	if (is_search() || is_404()) return false; //doesn't work on search or 404 page
  	
  	if (is_page()) global $post;	//make the post global so we can talk to it in a widget or sidebar
	else {
		$post_page = get_option("page_for_posts");
		if ($post_page) $post = get_page($post_page); //get the posts page
		elseif ($ssn_show_on_home) $sub_front_page = true;
		else return false;
	}
  	
 	$sortby = get_option('ssn_sortby');
	if(!$sortby) $sortby = 'menu_order';
	
	if (is_front_page() || isset($sub_front_page)) {
		echo $before_widget;  
		echo $before_title;  
		bloginfo('name');
		echo $after_title;
		echo "<ul>";  
		wp_list_pages('title_li=&depth=1&sort_column='.$sortby);
		echo "</ul>";  
		echo $after_widget;
		
		return true;  
  	}
	
	if(!$post) return false;	//if we cant get current post or page info, lets leave the function
  	
  	//get the list of excluded pages, and add a comma to the end so we can precisely search for matching page id later
	$excluded = explode(',', get_option('ssn_exclude'));
	
	//do not display widget if this page is in the excluded list, and user choose to not show section navigation for excluded pages 
	if (in_array($post->ID,$excluded) && get_option('ssn_hide_on_excluded')) return false; 
  	
  	//get the current page's ancestors
	$post_ancestors = get_post_ancestors($post);
	//get the top page id
	$top_page = $post_ancestors ? end($post_ancestors) : $post->ID;
	//if the top level page is in the excluded list, cancel function
	if (in_array($top_page,$excluded)) return false;
	//initialize default variables
	$pagelist = "";
	$thedepth = 0;
	
	if(!get_option('ssn_show_all')) {	
		//exclude pages not in direct hierarchy
		foreach ($post_ancestors as $theid) {
			$pageset = get_pages('child_of='.$theid.'&parent='.$theid);
			foreach ($pageset as $apage) {
			 	if(!in_array($apage->ID,$post_ancestors) && $apage->ID != $post->ID) {
					$excludeset = get_pages('child_of='.$apage->ID.'&parent='.$apage->ID);
					foreach ($excludeset as $expage) $pagelist = $pagelist.$expage->ID.",";
				}
			}
		}
		
		$thedepth = count($post_ancestors)+1; //prevents improper grandchildren from showing
	}		
	
	$children = wp_list_pages('title_li=&echo=0&depth='.$thedepth.'&child_of='.$top_page.'&sort_column='.$sortby.'&exclude='.$pagelist.get_option('ssn_exclude'));	//get the list of pages, including only those in our page list
	if(!$children && !get_option('ssn_show_empty')) return false; 	//if there are no pages in this section, and use hasnt chosen to display widget anyways, leave the function
	
	$sect_title = get_the_title($top_page);
	if (get_option('ssn_a_heading')) {
		$headclass = ($post->ID == $top_page) ? "current_page_item" : "current_page_ancestor";
		$sect_title = '<a href="'.get_permalink($top_page).'" id="toppage-'.$top_page.'" class="'.$headclass.'">'.$sect_title.'</a>';	
	}
  	
	echo $before_widget;  
	echo $before_title;  
	echo $sect_title;
	echo $after_title;
	echo "<ul>";  
	echo $children;
	echo "</ul>";  
	echo $after_widget;
	
	return true;  
}

function ssn_init() {
  register_sidebar_widget('Simple Section Nav', 'widget_ssn');
}

add_action("plugins_loaded", "ssn_init");


//************************//
//** ADMIN CONTROL PANEL *//
//************************//

function ssn_options() {
?>
	<div class="wrap">
		<h2>Simple Section Navigation Configuration</h2>
	
		<div id="poststuff" style="margin-top: 20px;">
	
			<div class="postbox" style="width: 215px; min-width: 215px; float: right;">
				<h3 class="hndle">Support us</h3>
				<div class="inside">
					<p>Help support continued development of Simple Section Navigation and our other plugins.</p>
					<p>The best thing you can do is <strong>refer someone looking for web development or strategy work <a href="http://www.cmurrayconsulting.com" target="_blank">to our company</a></strong>. Learn more about our <a href="http://www.cmurrayconsulting.com/services/partners/wordpress-developer/" target="_blank">Wordpress experience and services</a>.</p>
					<p>Short of that, please consider a donation. If you cannot afford even a small donation, please consider providing a link to our website, maybe in a blog post acknowledging this plugin.</p>
					<form method="post" action="https://www.paypal.com/cgi-bin/webscr" style="text-align: left;">
					<input type="hidden" value="_s-xclick" name="cmd"/>
					<input type="hidden" value="3377715" name="hosted_button_id"/>
					<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/> <img height="1" border="0" width="1" alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif"/><br/>
					</form>
					<p><strong><a href="http://www.cmurrayconsulting.com/software/wordpress-simple-section-navigation/">Support page</a></strong></p>
				</div>
			</div>
			
			<form method="post" action="options.php">
			<?php settings_fields('ssn-options'); ?>
			
				<div class="postbox" style="width: 350px;">
					<h3 class="hndle">Display options</h3>
					<div class="inside">
						<table class="form-table" style="clear: none;">
							<tr valign="top">
								<th scope="row" valign="top">Show on home page [<a href="#" onclick="alert('Normally, we do not want section navigation on the home page. If you check this box, the section navigation will also appear on the home page, with the name of the name of the site as the heading and all the top level pages listed.'); return false;" style="cursor: help;" title="Normally, we do not want section navigation on the home page. If you check this box, the section navigation will also appear on the home page, with the name of the name of the site as the heading and all the top level pages listed.">?</a>]</th>
								<td style="padding: 10px;">
									<input type="checkbox" name="ssn_show_on_home" id="ssn_show_on_home"<?php if (get_option('ssn_show_on_home')) { echo ' checked="true"'; } ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" valign="top">Link heading [<a href="#" onclick="alert('If you would like the heading to be linked to the top level page, with a unique ID based on the page ID, check this box. This link will also include a current_page_item or current_page_ancestor class for consistent styling.'); return false;" style="cursor: help;" title="If you would like the heading to be linked to the top level page, with a unique ID based on the page ID, check this box. This link will also include a current_page_item or current_page_ancestor class for consistent styling.">?</a>]</th>
								<td style="padding: 10px;">
									<input type="checkbox" name="ssn_a_heading" id="ssn_a_heading"<?php if (get_option('ssn_a_heading')) { echo ' checked="true"'; } ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" valign="top">Show all pages in section [<a href="#" onclick="alert('Normally, the plugin will only show siblings, parents and their siblings, and grandparents and their siblings (and higher), and any children of the current page. Check this box to show all pages within the section no matter where the user is in the section.'); return false;" style="cursor: help;" title="Normally, the plugin will only show siblings, parents and their siblings, and grandparents and their siblings (and higher), and any children of the current page. Check this box to show all pages within the section no matter where the user is in the section.">?</a>]</th>
								<td style="padding: 10px;">
									<input type="checkbox" name="ssn_show_all" id="ssn_show_all"<?php if (get_option('ssn_show_all')) { echo ' checked="true"'; } ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" valign="top">Show even if empty [<a href="#" onclick="alert('Normally, the widget will not display if there are no pages in the current section. If you want the widget to output anyways, with the section title, check this box.'); return false;" style="cursor: help;" title="Normally, the widget will not display if there are no pages in the current section. If you want the widget to output anyways, with the section title, check this box.">?</a>]</th>
								<td style="padding: 10px;">
									<input type="checkbox" name="ssn_show_empty" id="ssn_show_empty"<?php if (get_option('ssn_show_empty')) { echo ' checked="true"'; } ?> />
								</td>
							</tr>
							<?php /*
							<tr valign="top">
							<th scope="row" valign="top">Categories as blog children [<a href="#" onclick="alert('Will handle blog categories as children of the main blog page. Note that this uses category CSS styles instead of page CSS styles in the current version, which may break your styling.'); return false;" style="cursor: help;" title="Will handle blog categories as children of the main blog page. Note that this uses category CSS styles instead of page CSS styles in the current version, which may break your styling.">?</a>]</th>
							<td style="padding: 10px;">
								<input type="checkbox" name="ssn_cats_as_pages" id="ssn_cats_as_pages"<?php if (get_option('ssn_cats_as_pages')) { echo ' checked="true"'; } ?> />
							</td>
							</tr>
							*/ ?>
							<tr valign="top">
								<th scope="row" valign="top" style="padding-bottom: 4px;">Exclude Pages [<a href="#" onclick="alert('A comma seperated list of page IDs for pages that should not appear in the navigation. Note that all children of excluded pages will also be excluded. If you include the page id for a top level page in this list, the widget will not be displayed in that section.'); return false;" style="cursor: help;" title="A comma seperated list of page IDs for pages that should not appear in the navigation. Note that all children of excluded pages will also be excluded. If you include the page id for a top level page in this list, the widget will not be displayed in that section.">?</a>]</th>
								<td style="padding-bottom: 4px;">
									<input type="text" name="ssn_exclude" id="ssn_exclude" value="<?php echo get_option('ssn_exclude'); ?>" style="width: 80px;" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" valign="top">Hide on excluded pages [<a href="#" onclick="alert('Although excluded pages do not show up in the widget, normally, when on an excluded page, the navigation for the section the page is in still shows up. Check this box if you do not want to display section navigation when on a page in the exclude list above.'); return false;" style="cursor: help;" title="Although excluded pages do not show up in the widget, normally, when on an excluded page, the navigation for the section the page is in still shows up. Check this box if you do not want to display section navigation when on a page in the exclude list above.">?</a>]</th>
								<td style="padding: 10px;">
									<input type="checkbox" name="ssn_hide_on_excluded" id="ssn_hide_on_excluded"<?php if (get_option('ssn_hide_on_excluded')) { echo ' checked="true"'; } ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" valign="top">Sort pages by</th>
								<td style="padding: 10px;">
									<select name="ssn_sortby" id="ssn_sortby">
										<option value="menu_order"<?php if (get_option('ssn_sortby') == "menu_order") echo ' selected="selected"'; ?>>Menu Order</option>
										<option value="post_title"<?php if (get_option('ssn_sortby') == "post_title") echo ' selected="selected"'; ?>>Title</option>
										<option value="post_date"<?php if (get_option('ssn_sortby') == "post_date") echo ' selected="selected"'; ?>>Pub Date</option>
										<option value="post_modified"<?php if (get_option('ssn_sortby') == "post_modified") echo ' selected="selected"'; ?>>Mod Date</option>
										<option value="ID"<?php if (get_option('ssn_sortby') == "ID") echo ' selected="selected"'; ?>>Page ID</option>
										<option value="post_author"<?php if (get_option('ssn_sortby') == "post_author") echo ' selected="selected"'; ?>>Author</option>
										<option value="post_name"<?php if (get_option('ssn_sortby') == "post_name") echo ' selected="selected"'; ?>>Permalink</option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2" style="border-top: 1px dashed #DFDFDF;">
									<p>If your template does not use widgets, you can call the section navigation by using the function <strong>simple_section_nav()</strong>. The function accepts two parameters: "before_title" and "after_title", allowing control over HTML around the  section title. For example, if you wanted to wrap the section title in heading 2 tags, you would call the function like so: <strong>simple_section_nav("&lt;h2&gt;","&lt;/h2&gt;")</strong></p>
								</td>
							</tr>
						</table>
					</div>
				</div>
				
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="ssn_show_on_home,ssn_a_heading,ssn_show_all,ssn_show_empty,ssn_exclude,ssn_hide_on_excluded,ssn_sortby" />
				
				<p>
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			
			</form>	
		</div>
	</div>	
<?php 
	}

function ssn_admin_menu() {
	add_options_page('Simple Section Navigation Configuration', 'Section Nav', 8, __FILE__, 'ssn_options');
}
add_action('admin_menu', 'ssn_admin_menu');
?>