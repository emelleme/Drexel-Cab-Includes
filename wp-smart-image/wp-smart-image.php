<?php
/*
Plugin Name: WP Smart Image
Plugin URI: http://www.darioferrer.com/wp-smart-image
Description: WP Smart Image is deprecated. Please change to WP Smart Image II.
Author: Darío Ferrer (@metacortex)
Version: 0.3.4
Author URI: http://www.darioferrer.com
*/

/*  Copyright 2009 Darío Ferrer (wp@darioferrer.com)

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

if ( !defined('WP_PLUGIN_DIR') ) $plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
else $plugin_dir = dirname( plugin_basename(__FILE__) );

if ( function_exists('load_plugin_textdomain') ) {
	if ( !defined('WP_PLUGIN_DIR') ) load_plugin_textdomain('wp-smart-image', str_replace( ABSPATH, '', dirname(__FILE__) ) );
	else load_plugin_textdomain('wp-smart-image', false, dirname( plugin_basename(__FILE__) ) );
}

if ( isset($_REQUEST['wpsi_agregar_datos']) ) wpsi_llenar_bd();
if ( isset($_REQUEST['wpsi_remover_datos']) ) wpsi_vaciar_options();
if ( isset($_REQUEST['wpsi_borrar_postmeta']) ) wpsi_vaciar_postmeta();

$wpsi_configuracion = get_option('wpsi_configuracion');

add_action('admin_menu', 'wpsi_options_page');
add_action('admin_head', 'wpsi_cargar_archivos');
add_action('wp_head', 'wpsi_cargar_header');
if($wpsi_configuracion['wpsi_activar_metabox'] == 1) {
	add_action( 'do_meta_boxes' , 'wpsi_agregar_metabox' , 10, 2 );
	add_action( 'save_post', 'wpsi_guardar_metabox' );
}

function wpsi_options_page() {
  add_options_page(__('WP Smart Image', 'wp-smart-image'), __('WP Smart Image', 'wp-smart-image'), 8, 'wp-smart-image', 'wpsi_options');
}

function wpsi_options() {
// Convirtiendo en variable todo lo que hemos metido en la base de datos
// Turning to variables all thing we've set on the database
	global $wpsi_configuracion, $_POST;
	if (!empty($_POST)) {
		if ( isset($_POST['wpsi_ruta_img']) ) $wpsi_configuracion['wpsi_ruta_img'] = $_POST['wpsi_ruta_img'];
		if ( isset($_POST['wpsi_reemp_mini']) ) $wpsi_configuracion['wpsi_reemp_mini'] = $_POST['wpsi_reemp_mini'];
		if ( isset($_POST['wpsi_reemp_medio']) ) $wpsi_configuracion['wpsi_reemp_medio'] = $_POST['wpsi_reemp_medio'];
		if ( isset($_POST['wpsi_reemp_grande']) ) $wpsi_configuracion['wpsi_reemp_grande'] = $_POST['wpsi_reemp_grande'];
		if ( isset($_POST['wpsi_reemp_full']) ) $wpsi_configuracion['wpsi_full'] = $_POST['wpsi_reemp_full'];
		if ( isset($_POST['wpsi_texto_alt']) ) $wpsi_configuracion['wpsi_texto_alt'] = $_POST['wpsi_texto_alt'];
		if ( isset($_POST['wpsi_texto_title']) ) $wpsi_configuracion['wpsi_texto_title'] = $_POST['wpsi_texto_title'];

		$wpsi_configuracion['wpsi_rss'] = $_REQUEST['mostrar-rss'] ? 1 : 0;
		$wpsi_configuracion['custom_compat'] = $_REQUEST['custom-compat'] ? 1 : 0;
		$wpsi_configuracion['wpsi_activar_metabox'] = $_REQUEST['wpsi-activar-metabox'] ? 1 : 0;

		update_option('wpsi_configuracion',$wpsi_configuracion);

		echo '<div id="message" class="updated fade"><p>'.__('Options updated', 'wp-smart-image').'</p></div>';
		echo $texto_error;
	}
	if($wpsi_configuracion['wpsi_rss']) $rss_checked = 'checked="checked"';
	if($wpsi_configuracion['custom_compat']) $custom_compat_checked = 'checked="checked"';	
	if($wpsi_configuracion['wpsi_activar_metabox']) $wpsi_activar_metabox_checked = 'checked="checked"';	
?>

<div id="wpsi-contenedor">
	<h2><?php _e('WP Smart Image Options', 'wp-smart-image') ?></h2>
	<ul id="wpsi-caja" class="wpsi-pestanas">
		<li class="wpsi-selected"><a href="#" rel="tcontent1"><?php _e('Settings', 'wp-smart-image') ?></a></li>
		<li><a href="#" rel="tcontent2"><?php _e('Parameters', 'wp-smart-image') ?></a></li>
		<li><a href="#" rel="tcontent3"><?php _e('About', 'wp-smart-image') ?></a></li>
	</ul>
	<div id="tcontent1" class="wpsi-contenido">
		<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post" id="wpsi-form" class="clase-wpsi-form form1" name="wpsi-form">
			<fieldset>
				<h3><?php _e('General options', 'wp-smart-image') ?></h3>
				<p class="descripcion"><input type="checkbox" id="wpsi-activar-metabox" name="wpsi-activar-metabox" value="1" <?php echo $wpsi_activar_metabox_checked ?> /> <label for="wpsi-activar-metabox"><?php _e('Activate editor box', 'wp-smart-image') ?></label></p>
				<p class="explicacion"><?php _e('If checked, you don\'t need to sort images from menu anymore. You will can choose directly the image you wish from post editor through a sidebox. One little field per post will added in your DB _postmeta table. The activation of this function will not modifies any configuration you have been set before', 'wp-smart-image') ?>.</p>
				<p class="descripcion"><input type="checkbox" id="mostrar-rss" name="mostrar-rss" value="1" <?php echo $rss_checked ?> /> <label for="mostrar-rss"><?php _e('Show thumbnails in RSS feed', 'wp-smart-image') ?></label></p>
				<p class="explicacion"><?php _e('Check this box if you want to show thumbnails in your RSS feeds. Otherwise leave it blank', 'wp-smart-image') ?>.</p>
				<p class="descripcion"><input type="checkbox" id="custom-compat" name="custom-compat" value="1" <?php echo $custom_compat_checked ?> /> <label for="custom-compat"><?php _e('Activate compatibility with', 'wp-smart-image') ?> <?php _e('Max Image Size Control plugin', 'wp-smart-image') ?></label></p>
				<p class="explicacion ultimo"><a href="http://wordpress.org/extend/plugins/max-image-size-control/"><?php _e('Max Image Size Control plugin', 'wp-smart-image') ?></a> <?php _e('adds the functionality to change the max image size each category and post. Even you can create extra sizes. Now you can integrate the power of both plugins', 'wp-smart-image') ?>.</p>
			</fieldset>
			<fieldset>
				<h3><?php _e('Replacement images', 'wp-smart-image') ?></h3>
				<p class="descripcion"><label for="wpsi_ruta_img"><?php _e('Replacement image path', 'wp-smart-image') ?></label></p>
				<p class="formulario"><input type="text" name="wpsi_ruta_img" id="wpsi_ruta_img" value="<?php echo $wpsi_configuracion['wpsi_ruta_img'] ?>" /></p>
				<p class="explicacion"><?php _e('Change this path if you like to custom image folder location.', 'wp-smart-image') ?></p>
				<div class="wpsi-separador wpsi-linea"></div>
				<div class="wpsi-izquierda linea-abajo">
					<p class="descripcion"><label for="wpsi_reemp_mini"><?php _e('Replacement image for thumbnail size', 'wp-smart-image') ?></label></p>
					<p class="formulario"><input type="text" name="wpsi_reemp_mini" id="wpsi_reemp_mini" value="<?php echo $wpsi_configuracion['wpsi_reemp_mini'] ?>" /></p>
					<p class="explicacion"><?php _e('Set thumbnail filename to show in case it not exists one on your post.', 'wp-smart-image') ?></p>
				</div>
				<div class="wpsi-derecha linea-abajo">
					<p class="descripcion"><label for="wpsi_reemp_medio"><?php _e('Replacement image for medium size', 'wp-smart-image') ?></label></p>
					<p class="formulario"><input type="text" name="wpsi_reemp_medio" id="wpsi_reemp_medio" value="<?php echo $wpsi_configuracion['wpsi_reemp_medio'] ?>" /></p>
					<p class="explicacion"><?php _e('Set medium size filename to show in case it not exists one on your post.', 'wp-smart-image') ?></p>
				</div>
				<div class="wpsi-separador"></div>
				<div class="wpsi-izquierda linea-arriba">
					<p class="descripcion"><label for="wpsi_reemp_grande"><?php _e('Replacement image for large size', 'wp-smart-image') ?></label></p>
					<p class="formulario"><input type="text" name="wpsi_reemp_grande" id="wpsi_reemp_grande" value="<?php echo $wpsi_configuracion['wpsi_reemp_grande'] ?>" /></p>
					<p class="explicacion ultimo"><?php _e('Set large size filename to show in case it not exists one on your post.', 'wp-smart-image') ?></p>
				</div>
					<div class="wpsi-derecha linea-arriba">
					<p class="descripcion"><label for="wpsi_reemp_full"><?php _e('Replacement image for full size', 'wp-smart-image') ?></label></p>
					<p class="formulario"><input type="text" name="wpsi_reemp_full" id="wpsi_reemp_full" value="<?php echo $wpsi_configuracion['wpsi_reemp_full'] ?>" /></p>
					<p class="explicacion ultimo"><?php _e('Set full size filename to show in case it not exists one on your post.', 'wp-smart-image') ?></p>
				</div>
				<div class="wpsi-separador"></div>
			</fieldset>
			<fieldset>
				<h3><?php _e('Default alternate text', 'wp-smart-image') ?></h3>
				<p class="descripcion"><label for="wpsi_texto_alt"><?php _e('Default ALT attribute text:', 'wp-smart-image') ?></label></p>
				<p class="formulario"><input type="text" name="wpsi_texto_alt" id="wpsi_texto_alt" value="<?php echo $wpsi_configuracion['wpsi_texto_alt'] ?>" /></p>
				<p class="explicacion"><?php _e('This string will show with <strong>"alt"</strong> attribute in case you have been not assigned one through your image panel.', 'wp-smart-image') ?></p>
				<p class="descripcion"><label for="wpsi_texto_title"><?php _e('Default TITLE attribute text:', 'wp-smart-image') ?></label></p>
				<p class="formulario"><input type="text" name="wpsi_texto_title" id="wpsi_texto_title" value="<?php echo $wpsi_configuracion['wpsi_texto_title'] ?>" /></p>
				<p class="explicacion ultimo"><?php _e('This string will show with <strong>"title"</strong> attribute in case you have been not assigned one through your image panel.', 'wp-smart-image') ?></p>
			</fieldset>
			<p class="enviar"><input type="submit" name="wp_smart_image_enviar" value="<?php _e('Update options &raquo;', 'wp-smart-image') ?>" class="button-primary" /></p>
		</form>
		<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post" id="wpsi-remover-datos" class="clase-wpsi-form form3" name="wpsi-remover-datos">
			<fieldset class="wpsi-advertencia">
				<h3><?php _e('Managing data', 'wp-smart-image') ?></h3>
				<p><?php _e('<span class="aviso">Warning!</span> If you click the wrong button, you will loose all you have been set manually.', 'wp-smart-image') ?></p>
				<p class="submit">
				<input type="submit" title="<?php _e('Remove plugin database info', 'wp-smart-image') ?>" name="wpsi_remover_datos" OnClick="return confirm('<?php _e('Sure you want remove plugin database entry?', 'wp-smart-image') ?>');" value="<?php _e('Remove data', 'wp-smart-image') ?>" />
				<input type="submit" title="<?php _e('Populate/Restore plugin database info', 'wp-smart-image') ?>" name="wpsi_agregar_datos" OnClick="return confirm('<?php _e('Sure you want populate plugin database entry with default data?', 'wp-smart-image') ?>');" value="<?php _e('Populate/Restore data', 'wp-smart-image') ?>" />
				<input type="submit" title="<?php _e('Delete post_meta info', 'wp-smart-image') ?>" name="wpsi_borrar_postmeta" OnClick="return confirm('<?php _e('Sure you want delete post_meta info? This will delete all configurations you have been set through editor! Better think twice buddy!', 'wp-smart-image') ?>');" value="<?php _e('Delete post_meta info', 'wp-smart-image') ?>" />
				</p>
			</fieldset>
		</form>
	</div>
	<div id="tcontent2" class="wpsi-contenido">	
		<form action="" class="clase-wpsi-form form2">
			<fieldset>
				<h3><?php _e('Parameters', 'wp-smart-image') ?></h3>
				<table class="wpsi-tabla">
					<tr>
						<th class="letra" colspan="3"><?php _e('Image parameters', 'wp-smart-image') ?></th>
					</tr>
					<tr>
						<th class="nombre1"><?php _e('Parameter', 'wp-smart-image') ?></th>
						<th class="nombre2"><?php _e('Value', 'wp-smart-image') ?></th>
						<th class="nombre3"><?php _e('Behavior', 'wp-smart-image') ?></th>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$size</td>
						<td>
							<ul>
								<li>&#39;mini&#39;</li>
								<li>&#39;med&#39;</li>
								<li>&#39;big&#39;</li>
								<li>&#39;full&#39;</li>
								<li>&#39;custom[<?php _e('number', 'wp-smart-image') ?>]&#39;</li>
							</ul>
						</td>
						<td>
							<p><?php _e('Affects the size of the image', 'wp-smart-image') ?>:</p>
							<ul>
								<li><span class="codigoAzul">&#39;mini&#39;:</span> <?php _e('Thumbnail size', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;med&#39;:</span> <?php _e('Medium size', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;big&#39;:</span> <?php _e('Large size', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;full&#39;:</span> <?php _e('Full size', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;custom[<?php _e('number', 'wp-smart-image') ?>]&#39;:</span> <?php _e('Shows custom size you have been set previously from ', 'wp-smart-image') ?> <a href="http://wordpress.org/extend/plugins/max-image-size-control/"><?php _e('Max Image Size Control plugin', 'wp-smart-image') ?></a> <?php _e('You must install and configure this plugin before using this parameter. Otherwise will returns Medium Size', 'wp-smart-image') ?>.</li>
							</ul>
						</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla1" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='med') ?&gt;" size="24" onclick="javascript:this.form.copiatabla1.focus();this.form.copiatabla1.select();" /></p>	
						</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?> <?php _e('using', 'wp-smart-image') ?> <?php _e('Max Image Size Control plugin', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla12" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='custom0') ?&gt;" size="24" onclick="javascript:this.form.copiatabla12.focus();this.form.copiatabla12.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="blanco">
						<td class="codigoAzul">$type</td>
						<td>
							<ul>
								<li>&#39;link&#39;</li>
								<li>&#39;single&#39;</li>
								<li>&#39;url&#39;</li>
								<li>&#39;direct&#39;</li>
							</ul>
						</td>
						<td>
							<p><?php _e('Affects the presentation of the image', 'wp-smart-image') ?>:</p>
							<ul>
								<li><span class="codigoAzul">&#39;link&#39;:</span> <?php _e('Image linked to post', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;single&#39;:</span> <?php _e('Image without link', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;url&#39;:</span> <?php _e('Only image URL', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;direct&#39;:</span> <?php _e('Image linked to its full version', 'wp-smart-image') ?>.</li>
							</ul>
						</td>
					</tr>
					<tr class="blanco tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla2" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($type='url') ?&gt;" size="24" onclick="javascript:this.form.copiatabla2.focus();this.form.copiatabla2.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$wh</td>
						<td>
							<ul>
								<li>&#39;css&#39;</li>
								<li>&#39;html&#39;</li>
							</ul>
						</td>
						<td>
							<p><?php _e('Adds width and height attributes to the image. Supposing the image has 100px X 100px', 'wp-smart-image') ?>:</p>
							<ul>
								<li><span class="codigoAzul">&#39;css&#39;:</span> <span class="codigoTipo">style=&#34;width: 100px; height: 100px;&#34;</span></li>
								<li><span class="codigoAzul">&#39;html&#39;:</span> <span class="codigoTipo">width=&#34;100&#34; height=&#34;100&#34;</span></li>
							</ul>
						</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla3" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($wh='css') ?&gt;" size="24" onclick="javascript:this.form.copiatabla3.focus();this.form.copiatabla3.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="blanco">
						<td class="codigoAzul">$class</td>
						<td><?php _e('Any value', 'wp-smart-image') ?></td>
						<td><?php _e('Adds to the image the attribute', 'wp-smart-image') ?> &#34;class&#34;</td>
					</tr>
					<tr class="blanco tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla4" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($class='foo') ?&gt;" size="24" onclick="javascript:this.form.copiatabla4.focus();this.form.copiatabla4.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$cid</td>
						<td><?php _e('Any value', 'wp-smart-image') ?></td>
						<td><?php _e('Adds to the image the attribute', 'wp-smart-image') ?> &#34;ID&#34;</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla5" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($cid='bar') ?&gt;" size="24" onclick="javascript:this.form.copiatabla5.focus();this.form.copiatabla5.select();" /></p>	
						</td>
					</tr>
					<tr>
						<th class="letra ultima" colspan="3"><?php _e('Link parameters', 'wp-smart-image') ?></th>
					</tr>
					<tr>
						<th class="nombre1"><?php _e('Parameter', 'wp-smart-image') ?></th>
						<th class="nombre2"><?php _e('Value', 'wp-smart-image') ?></th>
						<th class="nombre3"><?php _e('Behavior', 'wp-smart-image') ?></th>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$aclass</td>
						<td><?php _e('Any value', 'wp-smart-image') ?></td>
						<td><?php _e('Adds to the link the attribute', 'wp-smart-image') ?> &#34;class&#34;</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla6" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($aclass='foo') ?&gt;" size="24" onclick="javascript:this.form.copiatabla6.focus();this.form.copiatabla6.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="blanco">
						<td class="codigoAzul">$aid</td>
						<td><?php _e('Any value', 'wp-smart-image') ?></td>
						<td><?php _e('Adds to the link the attribute', 'wp-smart-image') ?> &#34;ID&#34;</td>
					</tr>
					<tr class="blanco tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla7" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($aid='bar') ?&gt;" size="24" onclick="javascript:this.form.copiatabla7.focus();this.form.copiatabla7.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$rel</td>
						<td><?php _e('Any value', 'wp-smart-image') ?></td>
						<td><?php _e('Adds to the link the attribute', 'wp-smart-image') ?> &#34;rel&#34; al enlace</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla8" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($rel='foo') ?&gt;" size="24" onclick="javascript:this.form.copiatabla8.focus();this.form.copiatabla8.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="blanco">
						<td class="codigoAzul">$target</td>
						<td>
							<ul>
								<li>&#39;blank&#39;</li>
								<li>&#39;parent&#39;</li>
								<li>&#39;self&#39;</li>
								<li>&#39;top&#39;</li>
								<li>&#39;js&#39;</li>
								<li><del datetime="2009-07-19T10:53:40+00:00"><em><?php _e('Frame name', 'wp-smart-image') ?></em></del>. <?php _e('Replaced by parameter', 'wp-smart-image') ?> <span class="codigoAzul">$targetname</span> (<?php _e('see below', 'wp-smart-image') ?>). </li>
							</ul>
						</td>
						<td>
							<p><?php _e('Adds to the link the attribute', 'wp-smart-image') ?> &#34;target&#34;:</p>
							<ul>
								<li><span class="codigoAzul">&#39;self&#39;:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('in same window', 'wp-smart-image') ?> (<?php _e('Default value', 'wp-smart-image') ?>).</li>
								<li><span class="codigoAzul">&#39;blank&#39;:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('on a new window', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;parent&#39;:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('on parent frame', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;top&#39;:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('using full window size', 'wp-smart-image') ?>.</li>
								<li><span class="codigoAzul">&#39;js&#39;:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('in a new window through javascript. Very useful for DTD Strict websites', 'wp-smart-image') ?>.</li>
							</ul>
						</td>
					</tr>
					<tr class="blanco tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla9" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($target='blank') ?&gt;" size="24" onclick="javascript:this.form.copiatabla9.focus();this.form.copiatabla9.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$targetname</td>
						<td>
							<ul>
								<li>&#39;<?php _e('Frame name', 'wp-smart-image') ?>&#39;</li>
							</ul>
						</td>
						<td>
							<p><?php _e('Adds to the link the attribute', 'wp-smart-image') ?> &#34;target&#34; <?php _e('with custom frame name. It overrides &#34;$target&#34; parameter if both &#34;$target&#34; and &#34;$targetname&#34; are present', 'wp-smart-image') ?>:</p>
							<ul>
								<li><span class="codigoAzul"><em><?php _e('Frame name', 'wp-smart-image') ?></em>:</span> <?php _e('The linked url will be opened', 'wp-smart-image') ?> <?php _e('in the frame named as you have been set here', 'wp-smart-image') ?>.</li>
							</ul>
						</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla10" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($targetname='foo-bar') ?&gt;" size="24" onclick="javascript:this.form.copiatabla10.focus();this.form.copiatabla10.select();" /></p>	
						</td>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr>
						<th class="letra" colspan="3"><?php _e('Function parameters', 'wp-smart-image') ?></th>
					</tr>
					<tr>
						<th class="nombre1"><?php _e('Parameter', 'wp-smart-image') ?></th>
						<th class="nombre2"><?php _e('Value', 'wp-smart-image') ?></th>
						<th class="nombre3"><?php _e('Behavior', 'wp-smart-image') ?></th>
					</tr>
					<tr class="vacio">
						<td colspan="3">&nbsp;</td>
					</tr>
					<tr class="gris">
						<td class="codigoAzul">$mode</td>
						<td>
							<ul>
								<li>&#39;return&#39;</li>
							</ul>
						</td>
						<td>
							<p><?php _e('If set, prepares the function to be passed through PHP parameters', 'wp-smart-image') ?>.</p>
						</td>
					</tr>
					<tr class="gris tr-ejemplo">
						<td class="ejemplo" colspan="3">
							<p class="ejemplo-explicacion"><?php _e('Example', 'wp-smart-image') ?>:</p>
							<p><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copiatabla11" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($mode='return') ?&gt;" size="24" onclick="javascript:this.form.copiatabla11.focus();this.form.copiatabla11.select();" /></p>	
						</td>
					</tr>
				</table>
				<h3><?php _e('More examples', 'wp-smart-image') ?></h3>
				<p class="descripcion"><?php _e('Thumbnail, no link:', 'wp-smart-image') ?></p>
				<p class="formulario"><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia5" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='mini', $type = 'single') ?&gt;" size="24" onclick="javascript:this.form.copia5.focus();this.form.copia5.select();" /></p>
				<p class="descripcion"><?php _e('Only image URL, medium size:', 'wp-smart-image') ?></p>
				<p class="formulario"><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia6" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='med', $type = 'url') ?&gt;" size="24" onclick="javascript:this.form.copia6.focus();this.form.copia6.select();" /></p>
				<p class="descripcion"><?php _e('Thumbnail, linked to original image:', 'wp-smart-image') ?></p>
				<p class="formulario"><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia7" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='mini', $type = 'direct') ?&gt;" size="24" onclick="javascript:this.form.copia7.focus();this.form.copia7.select();" /></p>
				<p class="descripcion"><?php _e('Adding custom CSS styles:', 'wp-smart-image') ?></p>
				<p class="formulario"><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia11" value="&lt;?php if(function_exists('wp_smart_image')) wp_smart_image($class='the-class', $cid = 'the-id') ?&gt;" size="24" onclick="javascript:this.form.copia11.focus();this.form.copia11.select();" /></p>
				<p class="descripcion"><?php _e('Setting a dynamic CSS background:', 'wp-smart-image') ?></p>
				<p class="formulario"><input type="text" readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia8" value="&lt;div style=&quot;background: url( &lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='med', $type = 'url') ?&gt;) no-repeat;&quot;&gt;&lt;/div&gt;" size="24" onclick="javascript:this.form.copia8.focus();this.form.copia8.select();" /></p>
				<p class="descripcion"><?php _e('Setting MEDIUM SIZE image and respective FULL SIZE url code below it &#40;ImageShack style&#41;:', 'wp-smart-image') ?></p>
				<p class="formulario ultimo"><textarea readonly="readonly" title="<?php _e('Click to select code', 'wp-smart-image') ?>" name="copia9" rows="6" cols="60" onclick="javascript:this.form.copia9.focus();this.form.copia9.select();">
&lt;div class=&quot;img&quot;&gt;
 &lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='med') ?&gt;
&lt;/div&gt;
&lt;pre class=&quot;code&quot;&gt;
 &lt;?php if(function_exists('wp_smart_image')) wp_smart_image($size='full' , $type='url') ?&gt;
&lt;/pre&gt;</textarea></p>
			</fieldset>
		</form>
	</div>
	<div id="tcontent3" class="wpsi-contenido">
		<h3 class="wpsi-logo"><?php _e('WP Smart Image', 'wp-smart-image') ?> - <?php _e('Essential resource for web designers', 'wp-smart-image') ?></h3>
		<div class="wpsi-acerca">
			<h3><?php _e('With WP Smart Image you can:', 'wp-smart-image') ?></h3>
			<ul>
				<li><?php _e('Choose the image to show.', 'wp-smart-image') ?></li>
				<li><?php _e('Choose between the four presets WordPress sizes: Thumbnail, Medium, Large and Full.', 'wp-smart-image') ?></li>
				<li><?php _e('Link the image to the article or leave it without a link.', 'wp-smart-image') ?></li>
				<li><?php _e('Showing images in the posts list, even if those images are not setting to appear in the content.', 'wp-smart-image') ?></li>
				<li><?php _e('Get the image url instead of the whole tag.', 'wp-smart-image') ?></li>
				<li><?php _e('Personalize the alt and title attributes if they haven&#39;t been configured already.', 'wp-smart-image') ?></li>
				<li><?php _e('Add custom CSS classes and ID to properly handling the images through CSS, javascript, PHP and others web resources.', 'wp-smart-image') ?></li>
				<li><?php _e('Add a link to the full version of the image from the thumbnail or the medium size one.', 'wp-smart-image') ?></li>
				<li><?php _e('Personalize the generic images and its paths for all the sizes.', 'wp-smart-image') ?></li>
			</ul>
			<h3><?php _e('How to configure your images to be displayed:', 'wp-smart-image') ?></h3>
			<ul>
				<li><?php _e('Upload the images through your edition panel (required for database file association).', 'wp-smart-image') ?></li>
				<li><?php _e('In the Gallery section, drag the image you want to show to the first position, then it will be in the cover, even if you don&#39;t use it in the content.', 'wp-smart-image') ?></li>
				<li><?php _e('That&#39;s it.', 'wp-smart-image') ?></li>
			</ul>
			<h3><?php _e('Get support', 'wp-smart-image') ?></h3>
			<p><a href="http://www.darioferrer.com/wp-smart-image"><span class="negrita"><?php _e('WP Smart Image main site', 'wp-smart-image') ?></span></a></p>
			<p><span class="negrita"><?php _e('Support:', 'wp-smart-image') ?></span> <a href="http://www.darioferrer.com/que/viewforum.php?f=2"><?php _e('Spanish support', 'wp-smart-image') ?></a> | <a href="http://www.darioferrer.com/que/viewforum.php?f=4"><?php _e('English support', 'wp-smart-image') ?></a></p>
		</div>
		<div class="wpsi-creditos">
			<h4><?php _e('They have been collaborated on this project', 'wp-smart-image') ?></h4>
			<p><span class="titulo"><?php _e('Translating revision for version 0.1.1:', 'wp-smart-image') ?></span><br /> PatomaS, Álvaro Linares, Lucas Torres</p>		
			<p><span class="titulo"><?php _e('Javascript code for target _blank:', 'wp-smart-image') ?></span><br /> <a href="http://www.eslomas.com/index.php/archives/2005/04/11/como-abrir-enlaces-en-ventana-nueva-sin-utilizar-target-_blank/">Patxi Echarte</a></p>
			<p><span class="titulo"><?php _e('Testing background image:', 'wp-smart-image') ?></span><br /> Svilen Mushkatov</p>
			<p><span class="titulo"><?php _e('Hard heuristic plugin testing:', 'wp-smart-image') ?></span><br /> <?php _e('My grandma.', 'wp-smart-image') ?></p>		
			<p><span class="titulo"><?php _e('Coffee and cakes:', 'wp-smart-image') ?></span><br /> Lucía.</p>
			<h4><?php _e('Thank you!:', 'wp-smart-image') ?></h4> 
			<p><strong><?php _e('To', 'wp-smart-image') ?> Christian Van der Henst</strong> [Guatemala] <em>(<?php _e('for his valuable achievements for the webmasters community, and for being a true example to be followed in this area', 'wp-smart-image') ?>)</em> - <strong><?php _e('To', 'wp-smart-image') ?> Ernesto Graf</strong> [Uruguay] <em>(<?php _e('for their constant support and friendship, and for being my teacher in CSS', 'wp-smart-image') ?>)</em> - <strong><?php _e('To', 'wp-smart-image') ?> Helena Heidenreich</strong> [<?php _e('Spain', 'wp-smart-image') ?>] <em>(<?php _e('because her represents my first start in the web development area. To me your vote worth quad', 'wp-smart-image') ?>)</em> - <strong><?php _e('To', 'wp-smart-image') ?> Arturo Peraza</strong> [<?php _e('Mexico', 'wp-smart-image') ?>] <em>(<?php _e('compadre, my brother from life and strict Linux teacher =P', 'wp-smart-image') ?>)</em> - <strong><?php _e('To', 'wp-smart-image') ?> Carlos de Sagarra</strong> [<?php _e('Spain', 'wp-smart-image') ?>] <em>(<?php _e('respectable abducted javascripter, and even more respectable friend', 'wp-smart-image') ?>)</em>.</p>
			<p><?php _e('This first plugin release is dedicated to the memory of <strong>Enrique33</strong>.', 'wp-smart-image') ?></p>
		</div>
		<div class="wpsi-separador"></div>
	</div>
</div>
<script type="text/javascript">initializetabcontent("wpsi-caja")</script>

<?php }
function wpsi_cargar_archivos() {
// Css y javascripts para el panel
// Css and javascript for panel
	if ( !defined('WP_PLUGIN_DIR') ) $plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
	else $plugin_dir = dirname( plugin_basename(__FILE__) );
	echo '
<script type="text/javascript" src="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/js/tabcontent.js"></script>
<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/css/estilos.css" />
<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/css/ie.css" />
<![endif]-->
<style type="text/css">
#wpsi-contenedor h3.wpsi-logo {';
if( WPLANG == es_ES)
echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/img/logo-es_ES.gif) no-repeat;
width: 354px;';
if( WPLANG == fr_FR)
echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/img/logo-fr_FR.gif) no-repeat;
width: 345px;';
else echo 'background: url(' . get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir . '/img/logo-en_US.gif) no-repeat;
width: 321px;';
echo '}
</style>
<script type="text/javascript">
<!-- 
document.write(\'<style type="text/css">.wpsi-contenido{display:none;}<\/style>\');
 -->
</script>
';
}

function wpsi_llenar_bd() {
// Desde aquí se lanzan todos los valores por defecto cuando no tienes nada configurado
// From here we launch all default values when you don't have anything set yet
	if ( !defined('WP_PLUGIN_DIR') ) $plugin_dir = str_replace( ABSPATH, '', dirname(__FILE__) );
	else $plugin_dir = dirname( plugin_basename(__FILE__) );
	$wpsi_configuracion = array(
		'wpsi_ruta_img'		=> get_settings('siteurl') . '/' . PLUGINDIR . '/' . $plugin_dir .'/img/',
		'wpsi_reemp_mini'	=> 'noimg-mini.jpg',
		'wpsi_reemp_medio'	=> 'noimg-med.jpg',
		'wpsi_reemp_grande' => 'noimg-big.jpg',
		'wpsi_reemp_full'	=> 'noimg-full.jpg',
		'wpsi_texto_alt'	=> __('Article image', 'wp-smart-image'),
		'wpsi_texto_title'	=> __('Go to article', 'wp-smart-image')
	);
	if ( !get_option('wpsi_configuracion')) add_option('wpsi_configuracion' , $wpsi_configuracion );
	else update_option( 'wpsi_configuracion' , $wpsi_configuracion );
}	

function wpsi_vaciar_options() {
	delete_option( 'wpsi_configuracion' );
}

function wpsi_vaciar_postmeta() {
	global $wpdb;
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_wpsi_foto_lista'" );
}

if($wpsi_configuracion['wpsi_rss']) {
	add_filter('the_content_rss', 'wpsi_feed');
	add_filter('the_excerpt_rss', 'wpsi_feed');
}

function wpsi_feed($content) {
	$wpsi =  wp_smart_image();
    $content = $wpsi.$content;
    return $content;
}

function wpsi_cargar_header() { 
// Pequeño javascript agregado para el target_blank
// A little javascript added for target_blank	
echo 
'<script type="text/javascript"><!--//--><![CDATA[//><!--
function prepareTargetBlank() {
	var className = \'wpsi-blank\';
	var as = document.getElementsByTagName(\'a\');
	for(i=0;i<as.length;i++) {
		var a = as[i];
		r=new RegExp("(^| )"+className+"($| )");
		if(r.test(a.className)){
			a.onclick = function(){
				window.open(this.href);
				return false;
			}
		}
	}
}
window.onload = prepareTargetBlank;
//--><!]]>
</script>';
}

function wpsi_metabox() {
	global $post;
	wp_nonce_field( 'wpsi_metabox_args', '_wpsi_nonce', false, true )."\n";
		$imagenes = get_children( array( 
			'post_parent' => $post->ID, 
			'post_type' => 'attachment', 
			'post_mime_type' => 'image',
			'orderby' => 'menu_order', 
			'order' => 'ASC'
		));
	echo '<div class="wpsi-fl-contenedor">';
	$lista = '<table class="wpsi-fl-tabla">';
	if( !empty( $imagenes ) and !empty($post->ID) ) {
		$keys = array_keys($imagenes);
		$num = $keys[0];
		foreach ( $imagenes as $imagen ) {
			$wpsi_postmeta = get_post_meta( $post->ID, '_wpsi_foto_lista', true );
			if ( $item = wp_get_attachment_image_src( $imagen->ID, 'thumbnail' ) )
				$grande = wp_get_attachment_image_src( $imagen->ID, 'full' );
				$ident = $imagen->ID;
			if ( $wpsi_postmeta ) {
				$foto_lista_checked = $ident == $wpsi_postmeta ? 'checked="ckecked" ' : '';
				$lista = str_replace( 'id="wpsi-col-'.$wpsi_postmeta.'"' , 'id="wpsi-gris"' , $lista );
			} else {
				$foto_lista_checked = $ident == $num ? 'checked="ckecked" ' : '';
			}
			$lista .= '
			<tr>
				<th colspan="3">'.__('Title', 'wp-smart-image').': <span class="wpsi-fl-rojo">'.$imagen->post_title.'</span></th>
			</tr>
			<tr id="wpsi-col-'.$imagen->ID.'" class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_'.$imagen->ID.'\').checked = true;">
				<td class="wpsi-fl-input">
					<input type="radio" name="wpsi_foto_lista" id="boton_'.$imagen->ID.'" value="'.$imagen->ID.'" '.$foto_lista_checked.'/>
				</td>
				<td class="wpsi-fl-img">
					<a href="'.$grande[0].'" target="_blank" title="'.__('View original in new window', 'wp-smart-image').'"><img src="'.$item[0].'" width="48" height="48" /></a>
				</td>
				<td class="wpsi-fl-datos">
					<p><span class="negrita">'.__('Attachment ID', 'wp-smart-image').':</span> '.$imagen->ID.'</p>
					<p><span class="negrita">'.__('Type', 'wp-smart-image').':</span> '.$imagen->post_mime_type.'</p>
					<p><span class="negrita">'.__('W:', 'wp-smart-image').'</span> '.$grande[1].'px | <span class="negrita">'.__('H:', 'wp-smart-image').'</span> '.$grande[2].'px</p>
				</td>
			</tr>
			';
		}
		$lista .= '
		<tr>
			<th colspan="3">'.__('Random images', 'wp-smart-image').'</th>
		</tr>
		<tr id="wpsi-col-aleatorio" class="wpsi-col" onmouseover="this.style.cursor=\'pointer\';" onclick="getElementById(\'boton_aleatorio\').checked = true;">
			<td class="wpsi-fl-input">
				<input type="radio" name="wpsi_foto_lista" id="boton_aleatorio" value="aleatorio" '.$aleatorio_checked.'/>
			</td>
			<td colspan="2">
				<p>'.__('If checked, the images will shown randomly. Very useful in some cases, as dynamic headers, backgrounds or widgets', 'wp-smart-image').'</p>
			</td>
		</tr>';
	} else {
		$lista .= '
		<tr id="wpsi-fotolista-no">
			<td>
				<p>'.__('You have not been uploaded an image yet', 'wp-smart-image').' ¿<a href="media-upload.php?post_id='.$post->ID.'&amp;type=image&amp;TB_iframe=true" id="add_image" class="thickbox" title="Add an Image" onclick="return false;">'.__('Do you want to upload one now', 'wp-smart-image').'</a>?</p>
				<p>'.__('Thumbnail will show here next time you refresh this screen', 'wp-smart-image').'</p>
			</td>
		</tr>
		';
	}
	$lista .='</table>';
	echo $lista;
	echo '</div>';
}

function wpsi_agregar_metabox() {
	add_meta_box('wpsi-metabox', __('Image to show', 'wp-smart-image'), 'wpsi_metabox', 'post', 'side', 'core');
	add_meta_box('wpsi-metabox', __('Image to show', 'wp-smart-image'), 'wpsi_metabox', 'page', 'side', 'core');
}

function wpsi_guardar_metabox( $post_ID ) {
	if ( wp_verify_nonce( $_REQUEST['_wpsi_nonce'], 'wpsi_metabox_args' ) ) {
		if ( isset( $_POST['wpsi_foto_lista'] ) ) update_post_meta( $post_ID, '_wpsi_foto_lista', $_POST['wpsi_foto_lista'] );
		else delete_post_meta( $post_ID, '_wpsi_foto_lista' );
	}
	return $post_ID;
}

function wp_smart_image() { // El corazón del plugin | The plugin's heart
	global $post, $size, $type, $wh, $class, $aclass, $rel, $target, $targetname, $cid, $aid, $mode, $wpsi_configuracion;
	$size	= $size  == true ? $size  : 'mini';
	$type	= $type  == true ? $type  : 'link';	
	$mode	= $mode  == true ? $mode  : 'echo';	
	$clase	= 'class="'.$class.'" ';	
	$ident	= 'id="'.$cid.'" ';	
	$aident	= 'id="'.$aid.'" ';	
	$relatt	= 'rel="'.$rel.'" ';
	$tname	= 'target="'.$targetname.'" ';
	$class	= $class == true ? $clase : '';
	$cid	= $cid == true ? $ident : '';
	$rel	= $rel == true ? $relatt : '';
	$aid	= $aid == true ? $aident  : '';
	$targetname = $targetname == true ? $tname : '';
	$targetjs='';
	if($aclass == true) {
		if($target == 'js') {
			$aclase = 'class="wpsi-blank '.$aclass.'" ';
			$targetjs ='';
		} else {
			$aclase = 'class="'.$aclass.'" ';
		} 
	} else {
		$aclase ='';
		$targetjs = 'class="wpsi-blank" ';
	}
	$aclass	= $aclass == true ? $aclase  : '';	
	$textoalt = strip_tags( $wpsi_configuracion['wpsi_texto_alt'] );
	$textotitle = strip_tags( $wpsi_configuracion['wpsi_texto_title'] );
	$ubicacion = $wpsi_configuracion['wpsi_ruta_img'];
	$images = get_children(array(
		'post_parent'		=> get_the_ID(),
		'post_type'			=> 'attachment',
		'numberposts'		=> 1,
		'post_mime_type'	=> 'image',
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC'
	));
	switch ($size) {
		case 'mini': 
			$tam = 'thumbnail';
			$reemp = $wpsi_configuracion['wpsi_reemp_mini'];
		break;

		case 'med': 
			$tam = 'medium';
			$reemp = $wpsi_configuracion['wpsi_reemp_medio']; 
		break;
		case 'big': 
			$tam = 'large';
			$reemp = $wpsi_configuracion['wpsi_reemp_grande']; 
		break;
		case 'full': 
			$tam = 'full';
			$reemp = $wpsi_configuracion['wpsi_reemp_full']; 
		break;
		case $size: 
			$tam = $wpsi_configuracion['custom_compat'] == true ? $size : 'medium';
			$reemp = $wpsi_configuracion['wpsi_reemp_medio'];
		break;
		default: 
			$tam = 'thumbnail';
			$reemp = $wpsi_configuracion['wpsi_reemp_mini'];
		break;
	}
	switch ($target) {
		case 'blank': 
			$targetatt = 'target="_blank" ';
		break;
		case 'self': 
			$targetatt = 'target="_self" '; 
		break;
		case 'parent': 
			$targetatt = 'target="_parent" '; 
		break;
		case 'top': 
			$targetatt = 'target="_top" '; 
		break;
		case 'js': 
			$targetatt = $targetjs; 
		break;
		default: 
			$targetatt = '';
		break;
	}

	if($targetname == true) $target = ''; else $target = $targetatt;

	$linklist = $rel . $target . $targetname . $aclass . $aid;
	$img_def = '<img src="'.$ubicacion . $reemp.'" '.$class . $cid.' alt="'.$textoalt.'" title="'.$textotitle.'" />';
	$img_def_link = '<a '. $linklist .'href="'.get_permalink().'">'.$img_def.'</a>';
	if($images) {
		foreach( $images as $image ) {
			$wpsi_postmeta = get_post_meta( $post->ID, '_wpsi_foto_lista', true );
			$wpsi_metabox = $wpsi_configuracion['wpsi_activar_metabox'];
			$alt_img = $image->post_excerpt;
			$titulo_img = $image->post_title;
			$ruta = '';
			$weburl_img = '';
			if($wpsi_metabox and $wpsi_postmeta) {
				$ruta = wp_get_attachment_image_src( $wpsi_postmeta, $tam );
				$weburl_img = wp_get_attachment_link( $wpsi_postmeta, $tam );
			} else {
				$ruta = wp_get_attachment_image_src( $image->ID, $tam );
				$weburl_img = wp_get_attachment_link( $image->ID, $tam );
			}
			$weburl = $ruta[0];
			$directurl = str_replace('<a href' , '<a '. $linklist .'href' , $weburl_img);
			$alt_img = $alt_img == '' ? $altern = 'alt="'.$textoalt.'" ' : $altern = 'alt="'.$alt_img.'" ';
			$titulo_img = $titulo_img == $image->post_title ? $titulo = 'title="'.$textotitle.'" ' : $titulo = 'title="'.$titulo_img.'" ';
			switch ($wh) {
				case 'html': 
					$wh = 'width="'.$ruta[1].'" height="'.$ruta[2].'" ';
				break;
				case 'css': 
					$wh = 'style="width: '.$ruta[1].'px; height: '.$ruta[2].'px;" '; 
				break;
			}
			$img_list = $class . $cid . $wh . $titulo . $altern;
			$img_single = '<img src="'.$weburl.'" '.$img_list.'/>';
			$img_link = '<a '. $linklist .'href="'.get_permalink().'">'.$img_single.'</a>';
			switch ($type) {
				case 'link': 
					$imagen = $img_link."\n";
				break;
				case 'single': 
					$imagen = $img_single."\n"; 
				break;
				case 'direct': 
					$imagen = $directurl; 
				break;
				case 'url': 
					$imagen = $weburl; 
				break;
				default: 
					$imagen = $img_link."\n";
				break;
			}
		}	
	} else {
		switch ($type) {
			case 'link': 
				$imagen = $img_def_link."\n";
			break;
			case 'single': 
				$imagen = ''.$img_def.''."\n"; 
			break;
			case 'direct': 
				$imagen = '<a '. $linklist .'href="'.$ubicacion . $wpsi_configuracion['wpsi_reemp_full'] .'">'.$img_def.'</a>'."\n"; 
			break;
			case 'url': 
				$imagen = $ubicacion . $reemp; 
			break;
			default: 
				$imagen = $img_def_link."\n";
			break;
		}
	}
if($mode == 'return') return $imagen; else echo $imagen;
}
?>