<?php 
/*
	Plugin Name: WP-Colophon
	Plugin URI: http://www.digitalredeye.com/wp-plugins/colophon
	Description: Use the WEB_AUTHOR meta tag to create your website credits. Say no to SEO Tramp Stamps!
	Author: Rick R. Duncan
	Author URI: http://digitalredeye.com
	Version: 1.0.2
	License: GPL v2
	Usage: Visit the plugin's settings page to configure your options.

  	Copyright 2013  Rick R. Duncan  (email : rick@digitalredeye.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$b3m_plugin  = __('WP-Colophon');
$b3m_options = get_option('b3m_options');
$b3m_path    = plugin_basename(__FILE__);
$b3m_homeurl = 'http://www.digitalredeye.com/wp-plugins/colophon';
$b3m_version = '1.0.2';

// require minimum version of WordPress
add_action('admin_init', 'b3m_require_wp_version');
function b3m_require_wp_version() {
	global $wp_version, $b3m_path, $b3m_plugin;
	if (version_compare($wp_version, '3.5', '<')) {
		if (is_plugin_active($b3m_path)) {
			deactivate_plugins($b3m_path);
			$msg =  '<strong>' . $b3m_plugin . '</strong> ' . __('requires WordPress 3.5 or higher, and has been deactivated!') . '<br />';
			$msg .= __('Please return to the ') . '<a href="' . admin_url() . '">' . __('WordPress Admin area') . '</a> ' . __('to upgrade WordPress and try again.');
			wp_die($msg);
		}
	}
}

// insert head meta data
add_action('wp_head', 'b3m_head_meta_data');
function b3m_head_meta_data() { 
	echo b3m_display_content();
}

// shortcode to display head meta data
add_shortcode('head_meta_data','b3m_shortcode');
function b3m_shortcode() {
	$get_meta_data = b3m_display_content();
	$the_meta_data = str_replace(array('>', '<'), array('&gt;','&lt;'), $get_meta_data);
	return $the_meta_data;
}

// display head meta data
function b3m_display_content() {
	global $b3m_options;
	$b3m_output = '';
	$b3m_enable = $b3m_options['b3m_enable']; 
	$b3m_format = $b3m_options['b3m_format'];
	if ($b3m_format == false) {
		$close_tag = '">' . "\n";
	} else {
		$close_tag = '" />' . "\n";
	}
	if ($b3m_enable == true) {
		if ($b3m_options['b3m_webauthor']   !== '') $b3m_output .= '<meta name="web_author" content="'       . $b3m_options['b3m_webauthor']   . $close_tag;
	}
	return $b3m_output;
}


// display settings link on plugin page
add_filter ('plugin_action_links', 'b3m_plugin_action_links', 10, 2);
function b3m_plugin_action_links($links, $file) {
	global $b3m_path;
	if ($file == $b3m_path) {
		$b3m_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . $b3m_path . '">' . __('Settings') .'</a>';
		array_unshift($links, $b3m_links);
	}
	return $links;
}

// delete plugin settings
function b3m_delete_plugin_options() {
	if ($b3m_options['default_options'] == 1) {
		register_uninstall_hook (__FILE__, 'b3m_delete_plugin_options');
	}
}

// define default settings
register_activation_hook (__FILE__, 'b3m_add_defaults');
function b3m_add_defaults() {

	// default text for web_author meta tag value
	$current_theme = wp_get_theme( );
	$webauthor = $current_theme->get( 'Author' );

	$tmp = get_option('b3m_options');
	if(($tmp['default_options'] == '1') || (!is_array($tmp))) {
		$arr = array(
			'default_options' => 0,
			'b3m_webauthor'   => '&copy; ' . $site_name . ' - All rights Reserved.',
			'b3m_enable'      => 1,
			'b3m_format'      => 1,
		);
		update_option('b3m_options', $arr);
	}
}

// whitelist settings
add_action ('admin_init', 'b3m_init');
function b3m_init() {
	register_setting('b3m_plugin_options', 'b3m_options', 'b3m_validate_options');
}

// sanitize and validate input
function b3m_validate_options($input) {

	if (!isset($input['default_options'])) $input['default_options'] = null;
	$input['default_options'] = ($input['default_options'] == 1 ? 1 : 0);

	$input['b3m_webauthor']   = wp_filter_nohtml_kses($input['b3m_webauthor']);

	if (!isset($input['b3m_enable'])) $input['b3m_enable'] = null;
	$input['b3m_enable'] = ($input['b3m_enable'] == 1 ? 1 : 0);

	// dealing with kses
	global $allowedposttags;
	$allowed_atts = array(
		'align'=>array(), 'class'=>array(), 'id'=>array(), 'dir'=>array(), 'lang'=>array(), 'style'=>array(), 'label'=>array(), 'url'=>array(), 
		'xml:lang'=>array(), 'src'=>array(), 'alt'=>array(), 'name'=>array(), 'content'=>array(), 'http-equiv'=>array(), 'profile'=>array()
		);
	$allowedposttags['strong'] = $allowed_atts;
	$allowedposttags['script'] = $allowed_atts;
	$allowedposttags['style'] = $allowed_atts;
	$allowedposttags['small'] = $allowed_atts;
	$allowedposttags['span'] = $allowed_atts;
	$allowedposttags['meta'] = $allowed_atts;
	$allowedposttags['item'] = $allowed_atts;
	$allowedposttags['base'] = $allowed_atts;
	$allowedposttags['link'] = $allowed_atts;
	$allowedposttags['abbr'] = $allowed_atts;
	$allowedposttags['code'] = $allowed_atts;
	$allowedposttags['div'] = $allowed_atts;
	$allowedposttags['img'] = $allowed_atts;
	$allowedposttags['h1'] = $allowed_atts;
	$allowedposttags['h2'] = $allowed_atts;
	$allowedposttags['h3'] = $allowed_atts;
	$allowedposttags['h4'] = $allowed_atts;
	$allowedposttags['h5'] = $allowed_atts;
	$allowedposttags['ol'] = $allowed_atts;
	$allowedposttags['ul'] = $allowed_atts;
	$allowedposttags['li'] = $allowed_atts;
	$allowedposttags['em'] = $allowed_atts;
	$allowedposttags['p'] = $allowed_atts;
	$allowedposttags['a'] = $allowed_atts;

	if (!isset($input['b3m_format'])) $input['b3m_format'] = null;
	$input['b3m_format'] = ($input['b3m_format'] == 1 ? 1 : 0);

	return $input;
}

// add the options page
add_action ('admin_menu', 'b3m_add_options_page');
function b3m_add_options_page() {
	global $b3m_plugin;
	add_options_page($b3m_plugin, $b3m_plugin, 'manage_options', __FILE__, 'b3m_render_form');
}

// create the options page
function b3m_render_form() {
	global $b3m_plugin, $b3m_options, $b3m_path, $b3m_homeurl, $b3m_version; ?>

	<style type="text/css">
		.b3m-panel-overview { padding-left: 140px; background: url(<?php echo plugins_url(); ?>/wp-colophon/colophon-logo.png) no-repeat 15px 0; }

		#b3m-plugin-options h2 small { font-size: 60%; }
		#b3m-plugin-options h3 { cursor: pointer; }
		#b3m-plugin-options h4, 
		#b3m-plugin-options p { margin: 15px; line-height: 18px; }
		#b3m-plugin-options ul { margin: 15px 15px 25px 40px; }
		#b3m-plugin-options li { margin: 10px 0; list-style-type: disc; }
		#b3m-plugin-options abbr { cursor: help; border-bottom: 1px dotted #dfdfdf; }

		.b3m-table-wrap { margin: 15px; }
		.b3m-table-wrap td { padding: 5px 10px; vertical-align: middle; }
		.b3m-table-wrap .widefat th { padding: 10px 15px; vertical-align: middle; }
		.b3m-table-wrap .widefat td { padding: 10px; vertical-align: middle; }

		.b3m-item-caption { margin: 3px 0 0 3px; font-size: 11px; color: #777; line-height: 17px; }
		.b3m-code-example { margin: 10px 0 20px 0; }
		.b3m-code-example div { margin-left: 15px; }
		.b3m-code-example pre { margin-left: 30px; }
		.b3m-code { background-color: #fafae0; color: #333; font-size: 14px; }

		#setting-error-settings_updated { margin: 10px 0; }
		#setting-error-settings_updated p { margin: 5px; }
		#b3m-plugin-options .button-primary { margin: 0 0 15px 15px; }

		#b3m-panel-toggle { margin: 5px 0; }
		#b3m-credit-info { margin-top: -5px; }
	</style>

	<div id="b3m-plugin-options" class="wrap">
		<?php screen_icon(); ?>

		<h2><?php echo $b3m_plugin; ?> <small><?php echo 'v' . $b3m_version; ?></small></h2>
		<form method="post" action="options.php">
			<?php $b3m_options = get_option('b3m_options'); settings_fields('b3m_plugin_options'); ?>

			<div class="metabox-holder">
				<div class="meta-box-sortables ui-sortable">
					<div id="b3m-panel-overview" class="postbox">
						<h3><?php _e('Overview'); ?></h3>
						<div class="toggle">
							<div class="b3m-panel-overview">
								<p>
									<strong><?php echo $b3m_plugin; ?></strong> <?php _e('adds the \'WEB_AUTHOR\' <code>&lt;meta&gt;</code> tag into the <code>&lt;head&gt;</code> section of all posts &amp; pages.'); ?>
								</p>
								<ul>
									<li><?php _e('To configure the plugin, click'); ?> <a id="b3m-panel-primary-link" href="#b3m-panel-primary"><?php _e('Options'); ?></a>.</li>
									<li><?php _e('For a live preview of the meta tags, click'); ?> <a id="b3m-panel-secondary-link" href="#b3m-panel-secondary"><?php _e('Preview'); ?></a>.</li>
									<li><?php _e('To restore default settings, click'); ?> <a id="b3m-restore-settings-link" href="#b3m-restore-settings"><?php _e('Restore Default Options'); ?></a>.</li>
									<li><?php _e('For more information open the <code>readme.txt</code> file or visit the'); ?> <a target="_blank" href="<?php echo $b3m_homeurl; ?>"><?php _e('Plugin Page'); ?></a>.</li>
								</ul>
							</div>
						</div>
					</div>
					<div id="b3m-panel-primary" class="postbox">
						<h3><?php _e('Options'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<h4><?php _e('General Options'); ?></h4>
							<p><?php _e('Here you may enable/disable output of the meta tag and choose either <abbr title="Hypertext Markup Language">HTML</abbr> or <abbr title="eXtensible Hypertext Markup Language">XHTML</abbr> formatting.'); ?></p>
							<div class="b3m-table-wrap">
								<table class="widefat b3m-table">
									<tr>
										<th scope="row"><label class="description" for="b3m_options[b3m_enable]"><?php _e('Enable Plugin?'); ?></label></th>
										<td><input type="checkbox" name="b3m_options[b3m_enable]" value="1" <?php if (isset($b3m_options['b3m_enable'])) { checked('1', $b3m_options['b3m_enable']); } ?> /> 
										<span class="b3m-item-caption"><?php _e('Check this box if you want to enable output of the WEB_AUTHOR meta tag.'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="b3m_options[b3m_format]"><?php _e('XHTML Format?'); ?></label></th>
										<td><input type="checkbox" name="b3m_options[b3m_format]" value="1" <?php if (isset($b3m_options['b3m_format'])) { checked('1', $b3m_options['b3m_format']); } ?> /> 
										<span class="b3m-item-caption"><?php _e('Uncheck this box if you want to use <abbr title="Hypertext Markup Language">HTML</abbr> format. Leave checked for the default (<abbr title="eXtensible Hypertext Markup Language">XHTML</abbr>).'); ?></span></td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Meta Tag'); ?></h4>
							<p><?php _e('Here you may specify the value of the WEB_AUTHOR <code>&lt;meta&gt;</code> tag. Leave blank to disable the tag.'); ?></p>
							<div class="b3m-table-wrap">
								<table class="widefat b3m-table">
									<tr>
										<th scope="row"><label class="description" for="b3m_options[b3m_webauthor]"><?php _e('Meta Tag'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="b3m_options[b3m_webauthor]" value="<?php echo $b3m_options['b3m_webauthor']; ?>" />
										<div class="b3m-item-caption"><?php _e('Enter the name of the company that built your website. (e.g., DigitalRedEye - www.digitalredeye.com)'); ?></div></td>
									</tr>
		
								</table>
							</div>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" />
						</div>
					</div>
					<div id="b3m-panel-secondary" class="postbox">
						<h3><?php _e('Preview'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<div class="b3m-code-example">
								<h4><?php _e('Meta Tag'); ?></h4>
								<pre><?php echo do_shortcode('[head_meta_data]'); ?></pre>
							</div>
						</div>
					</div>
					<div id="b3m-restore-settings" class="postbox">
						<h3><?php _e('Restore Default Options'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p>
								<input name="b3m_options[default_options]" type="checkbox" value="1" id="mm_restore_defaults" <?php if (isset($b3m_options['default_options'])) { checked('1', $b3m_options['default_options']); } ?> /> 
								<label class="description" for="b3m_options[default_options]"><?php _e('Restore default options upon plugin deactivation/reactivation.'); ?></label>
							</p>
							<p>
								<small>
									<?php _e('<strong>Tip:</strong> leave this option unchecked to remember your settings. Or, to go ahead and restore all default options, check the box, save your settings, and then deactivate/reactivate the plugin.'); ?>
								</small>
							</p>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" />
						</div>
					</div>
					<div id="b3m-credit-info" class="postbox">
						<h3><?php _e('Plugin Credit'); ?></h3>
						<div class="toggle default-hidden">
							<p>
							<a target="_blank" href="<?php echo $b3m_homeurl; ?>" title="<?php echo $b3m_plugin; ?> Homepage"><?php echo $b3m_plugin; ?></a> by 
							<a target="_blank" href="http://twitter.com/rickrduncan" title="Rick R. Duncan on Twitter">Rick R. Duncan</a> @ 
							<a target="_blank" href="http://digitalredeye.com" title="Custom Web Applications">DigitalRedEye</a>
							</p>
						</div>
					</div>				
				</div>
			</div>

		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			// toggle panels
			jQuery('.default-hidden').hide();
			jQuery('#b3m-panel-toggle a').click(function(){
				jQuery('.toggle').slideToggle(300);
				return false;
			});
			jQuery('h3').click(function(){
				jQuery(this).next().slideToggle(300);
			});
			jQuery('#b3m-panel-primary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#b3m-panel-primary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#b3m-panel-secondary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#b3m-panel-secondary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#b3m-restore-settings-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#b3m-restore-settings .toggle').slideToggle(300);
				return true;
			});
			// prevent accidents
			if(!jQuery("#b3m_restore_defaults").is(":checked")){
				jQuery('#b3m_restore_defaults').click(function(event){
					var r = confirm("<?php _e('Are you sure you want to restore all default options? (this action cannot be undone)'); ?>");
					if (r == true){  
						jQuery("#b3m_restore_defaults").attr('checked', true);
					} else {
						jQuery("#b3m_restore_defaults").attr('checked', false);
					}
				});
			}
		});
	</script>

<?php } ?>