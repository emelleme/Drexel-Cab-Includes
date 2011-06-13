<?php
/*
Plugin Name: Max Image Size Control
Plugin URI: http://wpgogo.com/development/max-image-size-control.html
Description: This plugin adds the functionality to change the max image size each category and post.
Author: Hiroaki Miyashita
Version: 0.1.6
Author URI: http://wpgogo.com/
*/

class max_image_size_control {
	var $dvalue, $dflag;

	function max_image_size_control() {
		add_action( 'init', array(&$this, 'max_image_size_control_init') );
		add_action( 'admin_head', array(&$this, 'max_image_size_control_admin_head') );
		add_action( 'admin_menu', array(&$this, 'max_image_size_control_admin_menu') );
		add_filter( 'intermediate_image_sizes', array(&$this, 'max_image_size_control_intermediate_image_sizes') );
		add_filter( 'wp_generate_attachment_metadata', array(&$this, 'max_image_size_control_wp_generate_attachment_metadata') );
		add_filter( 'editor_max_image_size', array(&$this, 'max_image_size_control_editor_max_image_size'), 10, 2 );
		add_filter( 'image_downsize', array(&$this, 'max_image_size_control_image_downsize'), 10, 3 );
		add_filter( 'attachment_fields_to_edit', array(&$this, 'max_image_size_control_attachment_fields_to_edit'), 100, 2 );
	}
	
	function max_image_size_control_init() {
		if ( function_exists('load_plugin_textdomain') ) {
			if ( !defined('WP_PLUGIN_DIR') ) {
				load_plugin_textdomain('max-image-size-control', str_replace( ABSPATH, '', dirname(__FILE__) ) );
			} else {
				load_plugin_textdomain('max-image-size-control', false, dirname( plugin_basename(__FILE__) ) );
			}
		}
	}
	
	function max_image_size_control_admin_head() {
?>
<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function() {
		jQuery('#categorychecklist input').click(function() {setTimeout(function() {autosaveLast='';autosave();}, 1000);});
		jQuery('#categorychecklist-pop input').click(function() {setTimeout(function() {autosaveLast='';autosave();}, 1000);});
	});
//-->
</script>
<?php
	}

	function max_image_size_control_admin_menu() {
		add_options_page(__('Max Image Size Control', 'max-image-size-control'), __('Max Image Size Control', 'max-image-size-control'), 8, basename(__FILE__), array(&$this, 'max_image_size_control_admin') );
	}

	function max_image_size_control_admin() {
		$options = get_option('max_image_size_control_data');
		if($_POST["max_image_size_control_submit"]) :
			unset($options['max_image_size']);
			$j = 0;
			for($i=0;$i<count($_POST["thumbnail_size_w"]);$i++) :
				if( $_POST["everypost"][$i] || $_POST["post_id"][$i] || $_POST['category_id'][$i] || $_POST['ex_post_id'][$i] || $_POST['ex_category_id'][$i] ) :
					$options['max_image_size'][$j]['everypost']        = trim($_POST['everypost'][$i]);
					$options['max_image_size'][$j]['post_id']          = trim($_POST['post_id'][$i]);
					$options['max_image_size'][$j]['category_id']      = trim($_POST['category_id'][$i]);
					$options['max_image_size'][$j]['ex_post_id']       = trim($_POST['ex_post_id'][$i]);
					$options['max_image_size'][$j]['ex_category_id']   = trim($_POST['ex_category_id'][$i]);
					$options['max_image_size'][$j]['thumbnail_size_w'] = trim($_POST['thumbnail_size_w'][$i]);
					$options['max_image_size'][$j]['thumbnail_size_h'] = trim($_POST['thumbnail_size_h'][$i]);
					$options['max_image_size'][$j]['thumbnail_crop']   = trim($_POST['thumbnail_crop'][$i]);
					$options['max_image_size'][$j]['medium_size_w']    = trim($_POST['medium_size_w'][$i]);
					$options['max_image_size'][$j]['medium_size_h']    = trim($_POST['medium_size_h'][$i]);
					$options['max_image_size'][$j]['medium_crop']      = trim($_POST['medium_crop'][$i]);
					$options['max_image_size'][$j]['large_size_w']     = trim($_POST['large_size_w'][$i]);
					$options['max_image_size'][$j]['large_size_h']     = trim($_POST['large_size_h'][$i]);
					$options['max_image_size'][$j]['large_crop']       = trim($_POST['large_crop'][$i]);
					if ( $_POST['custom_size_w'][$i] ) :
						$m = 0;
						for($k=0;$k<count($_POST['custom_size_w'][$i]);$k++) :
							if ( $_POST['custom_size_w'][$i][$k] || $_POST['custom_size_h'][$i][$k] ) :
								$options['max_image_size'][$j]['custom'][$m]['custom_size_w'] = trim($_POST['custom_size_w'][$i][$k]);
								$options['max_image_size'][$j]['custom'][$m]['custom_size_h'] = trim($_POST['custom_size_h'][$i][$k]);
								$options['max_image_size'][$j]['custom'][$m]['custom_crop'] = trim($_POST['custom_crop'][$i][$k]);
								$m++;
							endif;
						endfor;						
					endif;
					$j++;
				endif;
			endfor;
			update_option('max_image_size_control_data', $options);
			$message = __('Options updated.', 'max-image-size-control');
		elseif ($_POST['max_image_size_control_delete_options_submit']) :
			delete_option('max_image_size_control_data');
			$options = get_option('max_image_size_control_data');
			$message = __('Options deleted.', 'max-image-size-control');
		endif;
?>
<?php if ($message) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div class="wrap">
<div id="icon-plugins" class="icon32"><br/></div>
<h2><?php _e('Max Image Size Control', 'max-image-size-control'); ?></h2>

<br class="clear"/>

<div id="poststuff" class="meta-box-sortables" style="position: relative; margin-top:10px;">
<div class="postbox">
<div class="handlediv" title="<?php _e('Click to toggle', 'max-image-size-control'); ?>"><br /></div>
<h3><?php _e('Max Image Size Control Options', 'max-image-size-control'); ?></h3>
<div class="inside">
<form method="post">
<?php
		for ( $i = 0; $i < count($options['max_image_size'])+1; $i++ ) :
?>
<fieldset style="border:1px solid #CCCCCC;">
<legend style="margin:10px; font-weight:bold;"><?php _e('Setting', 'max-image-size-control'); ?> #<?php echo $i; ?></legend>
<p><?php _e('You need to specify at least one of the following five sections. If not, settings will be deleted.', 'max-image-size-control'); ?>
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr>
<th><label for="everypost_<?php echo $i; ?>"><?php _e('Apply to every post', 'max-image-size-control'); ?></label></th>
<td><input name="everypost[<?php echo $i; ?>]" type="checkbox" id="everypost_<?php echo $i; ?>" value="1"<?php if( $options['max_image_size'][$i]['everypost'] ) : echo ' checked="checked"'; endif; ?> /></td>
</tr>
<tr>
<th><label for="post_id_<?php echo $i; ?>"><?php _e('Post ID (comma-deliminated)', 'max-image-size-control'); ?></label></th>
<td><input name="post_id[<?php echo $i; ?>]" type="text" id="post_id_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['post_id']; ?>" /></td>
</tr>
<tr>
<th><label for="category_id_<?php echo $i; ?>"><?php _e('Category ID (comma-deliminated)', 'max-image-size-control'); ?></label></th>
<td><input name="category_id[<?php echo $i; ?>]" type="text" id="category_id_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['category_id']; ?>" /></td>
</tr>
<tr>
<th><label for="ex_post_id_<?php echo $i; ?>"><?php _e('Exclude Post ID (comma-deliminated)', 'max-image-size-control'); ?></label></th>
<td><input name="ex_post_id[<?php echo $i; ?>]" type="text" id="ex_post_id_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['ex_post_id']; ?>" /></td>
</tr>
<tr>
<th><label for="ex_category_id_<?php echo $i; ?>"><?php _e('Exclude Category ID (comma-deliminated)', 'max-image-size-control'); ?></label></th>
<td><input name="ex_category_id[<?php echo $i; ?>]" type="text" id="ex_category_id_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['ex_category_id']; ?>" /></td>
</tr>
<tr>
<th><?php _e('Thumbnail size', 'max-image-size-control'); ?></th>
<td>
<label for="thumbnail_size_w_<?php echo $i; ?>"><?php _e('Width', 'max-image-size-control'); ?></label>
<input name="thumbnail_size_w[<?php echo $i; ?>]" type="text" id="thumbnail_size_w_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['thumbnail_size_w']; ?>" class="small-text" />
<label for="thumbnail_size_h_<?php echo $i; ?>"><?php _e('Height', 'max-image-size-control'); ?></label>
<input name="thumbnail_size_h[<?php echo $i; ?>]" type="text" id="thumbnail_size_h_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['thumbnail_size_h']; ?>" class="small-text" />
<input name="thumbnail_crop[<?php echo $i; ?>]" type="checkbox" id="thumbnail_crop_<?php echo $i; ?>" value="1" <?php checked('1', $options['max_image_size'][$i]['thumbnail_crop']); ?>/>
<label for="thumbnail_crop_<?php echo $i; ?>"><?php _e('Crop', 'max-image-size-control'); ?></label>
</td>
</tr>
<tr>
<th><?php _e('Medium size', 'max-image-size-control'); ?></th>
<td>
<label for="medium_size_w_<?php echo $i; ?>"><?php _e('Width', 'max-image-size-control'); ?></label>
<input name="medium_size_w[<?php echo $i; ?>]" type="text" id="medium_size_w_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['medium_size_w']; ?>" class="small-text" />
<label for="medium_size_h_<?php echo $i; ?>"><?php _e('Height', 'max-image-size-control'); ?></label>
<input name="medium_size_h[<?php echo $i; ?>]" type="text" id="medium_size_h_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['medium_size_h']; ?>" class="small-text" />
<input name="medium_crop[<?php echo $i; ?>]" type="checkbox" id="medium_crop_<?php echo $i; ?>" value="1" <?php checked('1', $options['max_image_size'][$i]['medium_crop']); ?>/>
<label for="medium_crop_<?php echo $i; ?>"><?php _e('Crop', 'max-image-size-control'); ?></label>
</td>
</tr>
<tr>
<th><?php _e('Large size', 'max-image-size-control'); ?></th>
<td>
<label for="large_size_w_<?php echo $i; ?>"><?php _e('Width', 'max-image-size-control'); ?></label>
<input name="large_size_w[<?php echo $i; ?>]" type="text" id="large_size_w_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['large_size_w']; ?>" class="small-text" />
<label for="large_size_h_<?php echo $i; ?>"><?php _e('Height', 'max-image-size-control'); ?></label>
<input name="large_size_h[<?php echo $i; ?>]" type="text" id="large_size_h_<?php echo $i; ?>" value="<?php echo $options['max_image_size'][$i]['large_size_h']; ?>" class="small-text" />
<input name="large_crop[<?php echo $i; ?>]" type="checkbox" id="large_crop_<?php echo $i; ?>" value="1" <?php checked('1', $options['max_image_size'][$i]['large_crop']); ?>/>
<label for="large_crop_<?php echo $i; ?>"><?php _e('Crop', 'max-image-size-control'); ?></label>
</td>
</tr>
<?php
	for ( $j=0; $j<count($options['max_image_size'][$i]['custom'])+1;$j++ ) :
?>
<tr>
<th><?php _e('Custom size', 'max-image-size-control'); ?> [ custom<?php echo $j; ?> ]</th>
<td>
<label for="custom_size_w_<?php echo $i; ?>_<?php echo $j; ?>"><?php _e('Width', 'max-image-size-control'); ?></label>
<input name="custom_size_w[<?php echo $i; ?>][<?php echo $j; ?>]" type="text" id="custom_size_w_<?php echo $i; ?>_<?php echo $j; ?>" value="<?php echo $options['max_image_size'][$i]['custom'][$j]['custom_size_w']; ?>" class="small-text" />
<label for="custom_size_h_<?php echo $i; ?>_<?php echo $j; ?>"><?php _e('Height', 'max-image-size-control'); ?></label>
<input name="custom_size_h[<?php echo $i; ?>][<?php echo $j; ?>]" type="text" id="custom_size_h_<?php echo $i; ?>_<?php echo $j; ?>" value="<?php echo $options['max_image_size'][$i]['custom'][$j]['custom_size_h']; ?>" class="small-text" />
<input name="custom_crop[<?php echo $i; ?>][<?php echo $j; ?>]" type="checkbox" id="custom_crop_<?php echo $i; ?>_<?php echo $j; ?>" value="1" <?php checked('1', $options['max_image_size'][$i]['custom'][$j]['custom_crop']); ?>/>
<label for="custom_crop_<?php echo $i; ?>_<?php echo $j; ?>"><?php _e('Crop', 'max-image-size-control'); ?></label>
</td>
</tr>
<?php
	endfor;
?>
</tbody>
</table>
</fieldset>
<?php
		endfor;
?>
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="max_image_size_control_submit" value="<?php _e('Update Options &raquo;', 'max-image-size-control'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'max-image-size-control'); ?>"><br /></div>
<h3><?php _e('Delete Options', 'max-image-size-control'); ?></h3>
<div class="inside">
<form method="post" onsubmit="return confirm('<?php _e('Are you sure to delete options? Options you set will be deleted.', 'max-image-size-control'); ?>');">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<p><input type="submit" name="max_image_size_control_delete_options_submit" value="<?php _e('Delete Options &raquo;', 'max-image-size-control'); ?>" class="button-primary" /></p>
</td></tr>
</tbody>
</table>
</form>
</div>
</div>

<div class="postbox closed">
<div class="handlediv" title="<?php _e('Click to toggle', 'max-image-size-control'); ?>"><br /></div>
<h3><?php _e('Donation', 'max-image-size-control'); ?></h3>
<div class="inside">
<p><?php _e('If you liked this plugin, please make a donation via paypal! Any amount is welcome. Your support is much appreciated.', 'max-image-size-control'); ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<table class="form-table" style="margin-bottom:5px;">
<tbody>
<tr><td>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="100156" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="" style="border:0;" />
<img alt="" border="0" src="https://www.paypal.com/ja_JP/i/scr/pixel.gif" width="1" height="1" />
</td></tr>
</tbody>
</table>
</form>
</div>
</div>
</div>


<script type="text/javascript">
// <![CDATA[
<?php if ( version_compare( substr($wp_version, 0, 3), '2.7', '<' ) ) { ?>
jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
<?php } ?>
jQuery('.postbox div.handlediv').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
jQuery('.postbox.close-me').each(function(){
jQuery(this).addClass("closed");
});
//-->
</script>

</div>
<?php	
	}
	
	function max_image_size_control_intermediate_image_sizes( $sizes ) {
		$options = get_option('max_image_size_control_data');
		
		$categories = get_the_category($_POST['post_id']);
		$cats = array();
		foreach( $categories as $val ) :
			$cats[] = $val->cat_ID;
		endforeach;
				
		if ( is_array($options['max_image_size']) ) :
			for ( $i=0; $i<count($options['max_image_size']); $i++ ) :
				if ( $options['max_image_size'][$i]['everypost'] ) :
					return $this->_max_image_size_control_intermediate_image_sizes( $sizes, $options['max_image_size'][$i] );
				endif;				
				if ( $options['max_image_size'][$i]['ex_post_id'] || $options['max_image_size'][$i]['ex_category_id'] ) :
					$ex_post_ids = explode(',', $options['max_image_size'][$i]['ex_post_id']);
					$ex_category_ids = explode(',', $options['max_image_size'][$i]['ex_category_id']);
					if ( in_array($_POST['post_id'], $ex_post_ids) ) continue ;
					foreach ( $ex_category_ids as $val ) :
						if ( in_array($val, $cats ) ) continue 2;
					endforeach;
					return $this->_max_image_size_control_intermediate_image_sizes( $sizes, $options['max_image_size'][$i] );
				endif;

				$post_ids = explode(',', $options['max_image_size'][$i]['post_id']);
				if ( in_array($_POST['post_id'], $post_ids) ) :
					return $this->_max_image_size_control_intermediate_image_sizes( $sizes, $options['max_image_size'][$i] );
				endif;
				$category_ids = explode(',', $options['max_image_size'][$i]['category_id']);
				foreach ( $category_ids as $val ) :
					if ( in_array($val, $cats ) ) :
						return $this->_max_image_size_control_intermediate_image_sizes( $sizes, $options['max_image_size'][$i] );
					endif;
				endforeach;
			endfor;
		endif;

		return $sizes;	
	}
	
	function _max_image_size_control_intermediate_image_sizes( $sizes, $data ) {
		foreach( $sizes as $size ) :
			$this->dvalue[$size.'_size_w'] = get_option($size.'_size_w');
			$this->dvalue[$size.'_size_h'] = get_option($size.'_size_h');
			$this->dvalue[$size.'_crop'] = get_option($size.'_crop');
			if ( $data[$size.'_size_w'] || $data[$size.'_size_h'] ) :
				update_option($size.'_size_w', intval($data[$size.'_size_w']));
				update_option($size.'_size_h', intval($data[$size.'_size_h']));
				update_option($size.'_crop', $data[$size.'_crop']);
			endif;
		endforeach;
		if ( is_array($data['custom']) ) :
			for($j=0;$j<count($data['custom']); $j++ ) :
				$size = 'custom'.$j;
				$sizes[] = $size;
				$this->dvalue[$size.'_size_w'] = get_option($size.'_size_w');
				$this->dvalue[$size.'_size_h'] = get_option($size.'_size_h');
				$this->dvalue[$size.'_crop'] = get_option($size.'_crop');
				if ( $data['custom'][$j]['custom_size_w'] && $data['custom'][$j]['custom_size_h'] ) :
					update_option($size.'_size_w', intval( $data['custom'][$j]['custom_size_w']));
					update_option($size.'_size_h', intval($data['custom'][$j]['custom_size_h']));
					update_option($size.'_crop', $data['custom'][$j]['custom_crop']);
				endif;
			endfor;
		endif;
		return $sizes;	
	}
	
	function max_image_size_control_wp_generate_attachment_metadata( $metadata ) {
		if ( $this->dvalue['thumbnail_size_w'] || $this->dvalue['thumbnail_size_h'] || $this->dvalue['medium_size_w'] || $this->dvalue['medium_size_h'] || $this->dvalue['large_size_w'] || $this->dvalue['large_size_h'] ) :
			update_option("thumbnail_size_w", $this->dvalue['thumbnail_size_w']);
			update_option("thumbnail_size_h", $this->dvalue['thumbnail_size_h']);
			update_option("thumbnail_crop", $this->dvalue['thumbnail_crop']);
			update_option("medium_size_w", $this->dvalue['medium_size_w']);
			update_option("medium_size_h", $this->dvalue['medium_size_h']);
			update_option("medium_crop", $this->dvalue['medium_crop']);
			update_option("large_size_w", $this->dvalue['large_size_w']);
			update_option("large_size_h", $this->dvalue['large_size_h']);
			update_option("large_crop", $this->dvalue['large_crop']);
		endif;

		return $metadata;
	}
	
	function max_image_size_control_editor_max_image_size( $max_size, $size ) {
		if ( $this->dflag ) return array(0,0);
		else return $max_size;
	}
	
	function max_image_size_control_image_downsize( $flag, $id, $size ) {
		$object = get_post($id);
		$post_id = $object->post_parent;
		$options = get_option('max_image_size_control_data');
		
		$categories = get_the_category($post_id);
		$cats = array();
		foreach( $categories as $val ) :
			$cats[] = $val->cat_ID;
		endforeach;
				
		if ( is_array($options['max_image_size']) ) :
			for ( $i=0; $i<count($options['max_image_size']); $i++ ) :
				if ( $options['max_image_size'][$i]['everypost'] ) :
					$this->dflag = true;
					return false;
				endif;
				if ( $options['max_image_size'][$i]['ex_post_id'] || $options['max_image_size'][$i]['ex_category_id'] ) :
					$ex_post_ids = explode(',', $options['max_image_size'][$i]['ex_post_id']);
					$ex_category_ids = explode(',', $options['max_image_size'][$i]['ex_category_id']);
					if ( in_array($post_id, $ex_post_ids) ) continue ;
					foreach ( $ex_category_ids as $val ) :
						if ( in_array($val, $cats ) ) continue 2;
					endforeach;
					$this->dflag = true;
					return false;
				endif;
			
				$post_ids = explode(',', $options['max_image_size'][$i]['post_id']);
				if ( in_array($post_id, $post_ids) ) :
					$this->dflag = true;
					return false;
				endif;
				$category_ids = explode(',', $options['max_image_size'][$i]['category_id']);
				foreach ( $category_ids as $val ) :
					if ( in_array($val, $cats ) ) :
						$this->dflag = true;
						return false;
					endif;
				endforeach;
			endfor;
		endif;
	}
	
	function max_image_size_control_attachment_fields_to_edit( $form_fields, $post ) {
		if ( !is_array( $imagedata = wp_get_attachment_metadata( $post->ID ) ) )
			return $form_fields;
			
		if ( is_array($imagedata['sizes']) ) :
			foreach ( $imagedata['sizes'] as $size => $val ) :
				if ( $size != 'thumbnail' && $size != 'medium' && $size != 'large' ) :
					$css_id = "image-size-{$size}-{$post->ID}";
					$html .= '<div class="image-size-item"><input type="radio" name="attachments['.$post->ID.'][image-size]" id="'.$css_id.'" value="'.$size.'" />';
					$html .= '<label for="'.$css_id.'">'.$size.'</label>';
					$html .= ' <label for="'.$css_id.'" class="help">'.sprintf( __("(%d&nbsp;&times;&nbsp;%d)"), $val['width'], $val['height'] ). '</label>';
					$html .= '</div>';
				endif;			
			endforeach;
		endif;

		$form_fields['image-size']['html'] .= $html;
		return $form_fields;
	}
}

$max_image_size_control = new max_image_size_control();
?>