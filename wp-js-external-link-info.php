<?php
/*
Plugin Name: WP Js External Link Info
Plugin URI: http://www.joergschueler.de/dev/wp/external-link-info/
Description: Rewrites and redirect all external links in posts, comments and author links with info page.
Author: Joerg Schueler
Version: 1.21
Author URI: http://www.joergschueler.de
*/

if ( !defined('WP_CONTENT_URL') ) define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_PLUGIN_URL') )  define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
define('WP_JS_BASENAME', plugin_basename(__FILE__) );
define('WP_JS_BASEDIR', dirname( plugin_basename(__FILE__) ) );
define('WP_JS_BLOGNAME', get_settings('blogname'));

if ( is_admin() ) { //Adds admin verification
	add_action('admin_menu', 'js_external_link_info_add_pages');
	add_action('admin_init', 'register_js_external_link_info_settings');
} else {

}

function register_js_external_link_info_settings() { // whitelist options
  register_setting( 'js_external_link_info', 'redirect_file' );
  register_setting( 'js_external_link_info', 'redirect_nobloginfo' );
  register_setting( 'js_external_link_info', 'redirect_notblank' );
  register_setting( 'js_external_link_info', 'redirect_questsonly' );
  register_setting( 'js_external_link_info', 'redirect_exclude' );
}

function js_external_link_info_options_page() {
?>
<div class="wrap">
<h2>WP Js External Link Info <? echo __("Options", 'js-external-link-info'); ?></h2>
<em>&nbsp;</em>
<form method="post" action="options.php">
<?php wp_nonce_field('js_external_link_info'); ?>

<table class="form-table">

<tr>
<td align="right" width="30%"><? echo __("Redirect file", 'js-external-link-info'); ?>:</td>
<td width="70%"><?php echo get_bloginfo('home') . '/'; ?><input type="text" name="redirect_file" value="<?php echo get_option('redirect_file'); ?>" /> <small>(<? echo __("empty for default", 'js-external-link-info'); ?>)</small></td>
</tr>

<tr>
<td align="right" width="30%"><? echo __("Redirect without Bloginfo", 'js-external-link-info'); ?>:</td>
<td><input type="checkbox" name="redirect_nobloginfo" value="1" <?php if (get_option('redirect_nobloginfo') == '1') { echo 'checked' ; } ?>> </td>
</tr>

<tr>
<td align="right"><? echo __("Redirect active for", 'js-external-link-info'); ?>:</td>
<td>
  <select name="redirect_questsonly" id="redirect_questsonly">
    <option value='false' <?php if (get_option('redirect_questsonly') != 'true') { echo 'selected' ; } ?>><? echo __("Everybody", 'js-external-link-info'); ?></option>
    <option value='true' <?php if (get_option('redirect_questsonly') == 'true') { echo 'selected' ; } ?>><? echo __("Quests only", 'js-external-link-info'); ?></option>
  </select>
</td>
</tr>

<tr>
<td align="right"><? echo __("new Browserwindow", 'js-external-link-info'); ?>:</td>
<td>
  <select name="redirect_notblank" id="redirect_notblank">
    <option value='2' <?php if (get_option('redirect_notblank') == 2) { echo 'selected' ; } ?>><? echo __("No", 'js-external-link-info'); ?></option>
    <option value='0' <?php if (get_option('redirect_notblank') < 1) { echo 'selected' ; } ?>><? echo __("Everybody", 'js-external-link-info'); ?></option>
    <option value='1' <?php if (get_option('redirect_notblank') == 1) { echo 'selected' ; } ?>><? echo __("Quests only", 'js-external-link-info'); ?></option>
  </select>
</td>
</tr>
<tr>
<td align="right" valign="top">
    <? echo __("Excluded domains", 'js-external-link-info'); ?>:<br />
    <small><? echo __("Simply enter the domain to exclude separated by line breaks here. (Example: www.joergschueler.de)", 'js-external-link-info'); ?></small>
  </td>
<td>
  <textarea name="redirect_exclude" rows="5" cols="64"><? echo trim(get_option('redirect_exclude')); ?></textarea>
</td>
</tr>

</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="redirect_questsonly,redirect_file,redirect_nobloginfo,redirect_notblank,redirect_exclude" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
<?php settings_fields( 'js_external_link_info' ); ?>
</form>

<hr>

<em>&nbsp;</em>

<table border="0">
<tr>
<td align="right">
	<form action="http://www.joergschueler.de/dev/wp/external-link-info/donate/" method="post">
	<input type="image" src="https://www.paypal.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen � mit PayPal.">
	</form>
</td>
<td><? echo __("If you like this extension please feel free to donate for support and future development.", 'js-external-link-info'); ?></td>
</tr>
</table>

<em>&nbsp;</em>

<hr>

</div>

<?php
}

function js_external_link_info_add_pages() {
	add_options_page('WP Js External Link Info', 'WP Js External Link Info', 8, 'js_external_link_info', 'js_external_link_info_options_page');
 }

function js_external_link_info_content($str, $arg = 1) {
	if (!isset($str)) return $str;
	if (!$arg) return $str;
	return preg_replace_callback('#<a\s([^>]*\s*href\s*=[^>]*)>#i', 'js_external_link_info_replace', $str);
}

function js_external_link_info_comment($str, $arg = 1) {
	if (!isset($str)) return $str;
	if (!$arg) return $str;
	return preg_replace_callback('#<a\s([^>]*\s*href\s*=[^>]*)>#i', 'js_external_link_info_replace', $str);
}

function js_external_link_check_local($href, $blogurl) {
	$schemes = array('http','ftp');
	$isext = false;
	foreach($schemes as $scheme) {
		if (stripos($href, $scheme) !== false) { $isext = true; }
/* var $href mit href prefix, zB.: href="http://wordpress.org/extend/plugins/wp-js-external-link-info/" */
	}
	if ($isext) {
		$local = (
	   	          (strpos($href, $blogurl))
                        || (stripos($href, get_option('redirect_exclude')))
                           );
	} else {
		$local = true;
	}
	return $local;
}

function js_external_link_info_replace($matches) {
	$blogurl = get_bloginfo('home') . '/';
         if (get_option('redirect_file') != "") { $redirect_file = $blogurl . get_option('redirect_file'); }
                                           else { $redirect_file = WP_PLUGIN_URL . '/' . WP_JS_BASEDIR . '/redirect.php'; }
	$str = $matches[1];
	preg_match_all('/[^=[:space:]]*\s*=\s*"[^"]*"|[^=[:space:]]*\s*=\s*\'[^\']*\'|[^=[:space:]]*\s*=[^[:space:]]*/', $str, $attr);
	$href_arr = preg_grep('/^href\s*=/i', $attr[0]);
	if (count($href_arr) > 0) {
         	$href = array_pop($href_arr);
		if ($href) {
			$local = js_external_link_check_local($href, $blogurl);
                         if ( ( (get_option('redirect_notblank') < 2)
                             && ( (get_option('redirect_notblank') < 1) || (!is_user_logged_in()) ) )
			    && ($local === false) && ($href{6} != "#") ) {
                 		$blank = 'target="_blank"';
			}
		} else {
                 	$local = TRUE;
                 }

		if ( ($local === false) && ($href{6} != "#")
		  && ( (get_option('redirect_questsonly') != 'true') || (!is_user_logged_in()) ) ) {
  			$href = str_replace('?','%3F',$href);
  			$href = str_replace('&','%26',$href);
                         if (get_option('redirect_nobloginfo') == '1') {
                           $href = preg_replace('/^(href\s*=\s*[\'"]?)/i', '\1' . $redirect_file . '?url=', $href);
                         } else {
                           $href = preg_replace('/^(href\s*=\s*[\'"]?)/i', '\1' . $redirect_file . '?blog=' . WP_JS_BLOGNAME . '&url=', $href);
                         }
		}
		$attr = preg_grep('/^href\s*=/i', $attr[0], PREG_GREP_INVERT);
	}
    	return '<a ' . $blank . join(' ', $attr) . ' ' . $href . '>';
}

function js_external_link_info_links($link) { // author link changer
	$blogurl = get_bloginfo('home').'/';
         if (get_option('redirect_file') != "") { $redirect_file = $blogurl . get_option('redirect_file'); }
                                           else { $redirect_file = WP_PLUGIN_URL . '/' . WP_JS_BASEDIR . '/redirect.php'; }
	$local = js_external_link_check_local($link, $blogurl);
	if ($local === false) {
  		if ( ( (get_option('redirect_notblank') < 2)
		  && ( (get_option('redirect_notblank') < 1) || (!is_user_logged_in()) ) )
		  && ($href{6} != "#") ) {
			$blank = 'target="_blank"';
                 }
	}
	if ( ($local === false) && ($href{6} != "#")
	  && ( (get_option('redirect_questsonly') != 'true') || (!is_user_logged_in()) ) ) {
                 if (get_option('redirect_nobloginfo') == '1') {
		  $link = preg_replace("#(.*href\s*=\s*)[\"\']*(.*)[\"\'] (.*)#i", "<a " . $blank . " href='" . $redirect_file . "?url=$2' $3", $link);
                 } else {
		  $link = preg_replace("#(.*href\s*=\s*)[\"\']*(.*)[\"\'] (.*)#i", "<a " . $blank . " href='" . $redirect_file . "?blog=" . WP_JS_BLOGNAME . "&url=$2' $3", $link);
                 }
	} else {
		$link = preg_replace("#(.*href\s*=\s*)[\"\']*(.*)[\"\'] (.*)#i", "<a " . $blank . " href=$2 $3", $link);
         }
	return $link;
}

function js_external_link_info_shortcuts($links, $file) {
	if ( $file == plugin_basename(__FILE__) )
	{
		$links[] = '<a href="options-general.php?page=js_external_link_info">' . __('Settings') . '</a>';
                 $links[] = '<a href="http://www.joergschueler.de/dev/wp/external-link-info/donate/">' . __('Donate','js-external-link-info') . '</a>';
	}
	return $links;
}

function js_external_link_info_init() {
	load_plugin_textdomain('js-external-link-info', str_replace(ABSPATH, '', dirname(__FILE__)), dirname(plugin_basename(__FILE__)));
	global $user_ID, $user_identity, $user_level;
}

add_action('init', 'js_external_link_info_init');
// the filters
add_filter('the_content', 'js_external_link_info_content');
add_filter('comment_text', 'js_external_link_info_comment');
add_filter('get_comment_author_link', 'js_external_link_info_links');

add_filter('plugin_row_meta','js_external_link_info_shortcuts',10,2);

?>