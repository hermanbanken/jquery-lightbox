<?php
/*
Plugin Name: jQuery Lightbox 2 with Skins & jQuery
Plugin URI: https://github.com/hermanbanken/jquery-lightbox
Description: Used to overlay images on the current page. Converted to jQuery by Herman Banken (WP-plugin) and Warren Krewenki (lightbox -> jQuery).
Version: 2.9.2
Author: Herman Banken
Author URI: http://hermanbanken.nl/
*/

/* Where our theme reside: */
$lightbox_jq_theme_path = (dirname(__FILE__)."/Themes");
update_option('lightbox_jq_theme_path', $lightbox_jq_theme_path);
/* Set the default theme to Black */
add_option('lightbox_jq_theme', 'Black');
add_option('lightbox_jq_automate', 1);
add_option('lightbox_jq_resize_on_demand', 0);

/* use WP_PLUGIN_URL if version of WP >= 2.6.0. If earlier, use wp_url */
if($wp_version >= '2.6.0') {
	$jq_lightbox_plugin_prefix = WP_PLUGIN_URL."/jquery-lightbox/"; /* plugins dir can be anywhere after WP2.6 */
} else {
	$jq_lightbox_plugin_prefix = get_bloginfo('wpurl')."/wp-content/plugins/jquery-lightbox/";
}

/* options page (required for saving prefs)*/
$options_page = get_option('siteurl') . '/wp-admin/admin.php?page=lightbox-jq/options.php';
/* Adds our admin options under "Options" */
function lightbox_jq_options_page() {
	add_options_page('Lightbox Options', 'Lightbox 2', 10, 'lightbox-jq/options.php');
}

function jq_lightbox_styles() {
	/* What version of WP is running? */
	global $wp_version;
	global $jq_lightbox_plugin_prefix;
    /* The next line figures out where the javascripts and images and CSS are installed,
    relative to your wordpress server's root: */
    $lightbox_jq_theme = urldecode(get_option('lightbox_jq_theme'));
    $lightbox_style = ($jq_lightbox_plugin_prefix."Themes/".$lightbox_jq_theme."/");

    /* The xhtml header code needed for lightbox to work: */
	$lightboxscript = "
	<!-- begin lightbox scripts -->
	<script type=\"text/javascript\">
    //<![CDATA[
    document.write('<link rel=\"stylesheet\" href=\"".$lightbox_style."lightbox.css\" type=\"text/css\" media=\"screen\" />');
    //]]>
    </script>
	<!-- end lightbox scripts -->\n";
	/* Output $lightboxscript as text for our web pages: */
	echo($lightboxscript);
}

/* Added a code to automatically insert rel="lightbox[nameofpost]" to every image with no manual work. 
If there are already rel="lightbox[something]" attributes, they are not clobbered. 
Michael Tyson, you are a regular expressions god! ;) 
http://atastypixel.com
*/
function autoexpand_rel_wlightbox ($content) {
	global $post;
	$pattern        = "/(<a(?![^>]*?rel=['\"]lightbox.*)[^>]*?href=['\"][^'\"]+?\.(?:bmp|gif|jpg|jpeg|png)['\"][^\>]*)>/i";
	$replacement    = '$1 rel="lightbox['.$post->ID.']">';
	$content = preg_replace($pattern, $replacement, $content);
	return $content;
}

if (get_option('lightbox_jq_automate') == 1){
	add_filter('the_content', 'autoexpand_rel_wlightbox', 99);
	add_filter('the_excerpt', 'autoexpand_rel_wlightbox', 99);
}

wp_register_script( 'lightbox-jq', $jq_lightbox_plugin_prefix.'lightbox.js', array('jquery'), '2.9' );

if (!is_admin()) { // if we are *not* viewing an admin page, like writing a post or making a page:
	wp_enqueue_script('lightbox-jq');

/* we want to add the above xhtml to the header of our pages: */
add_action('wp_head', 'jq_lightbox_styles');
add_action('admin_menu', 'lightbox_jq_options_page');
?>
