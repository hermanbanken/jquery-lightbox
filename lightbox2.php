<?php
/*
Plugin Name: jQuery Lightbox 2 with Skins & jQuery
Plugin URI: https://github.com/hermanbanken/jquery-lightbox
Description: Used to overlay images on the current page. Converted to jQuery by Herman Banken (WP-plugin) and Warren Krewenki (lightbox -> jQuery).
Version: 2.9.2
Author: Herman Banken
Author URI: http://hermanbanken.nl/
*/

class LightboxJQ {
	public $plugin_path = "";
	public $theme = "Black";
	public $automate = true;
	public $resize = false;
	public $optionspage = 'lightboxjq';
	
	public function __construct(){
		add_action('init', array($this, 'init'));
		add_action('wp_head', array($this, 'add_style'));
		add_action('wp_head', array($this, 'lighter'));
		add_action('admin_menu', array($this, 'menu'));	
	}
	
	public function init(){
		$this->get_vars();
		
		// Register filters and scripts
		$jsfile = plugins_url( "jquery.lightbox.js", __FILE__);
		wp_register_script( 'lightbox-jq', $jsfile, array('jquery'), '2.9' );

		if (!is_admin()) { // if we are viewing an admin page, don't do lightboxing:
			wp_enqueue_script('lightbox-jq');
		}
		
		if($this->automate){
			add_filter('the_content', array($this, 'auto_lightbox_filter'), 99);
			add_filter('the_excerpt', array($this, 'auto_lightbox_filter'), 99);
		}
	}
	
	private function get_vars(){
		// Fetch variables
		$this->plugin_path = dirname(__FILE__);
		$this->theme = get_option('lightbox_jq_theme');
		$this->automate = get_option('lightbox_jq_automate');
		$this->resize = get_option('lightbox_jq_resize');
	}

	public function menu(){
		add_options_page( 
			'Lightbox JQ Options', 'Lightbox JQ', 
			'manage_options', $this->optionspage, array($this, 'options_page')
		);
	}
	
	/*
	* Added a code to automatically insert rel="lightbox[nameofpost]" 
	* to every image with no manual work. If there are already 
	* rel="lightbox[something]" attributes, they are not clobbered.
	*  
    * Michael Tyson, you are a regular expressions god! ;) 
    * http://atastypixel.com
	*/
	public function auto_lightbox_filter($content) {
		global $post;
		$pattern        = "/(<a(?![^>]*?rel=['\"]lightbox.*)[^>]*".
						  "?href=['\"][^'\"]+?\.(?:bmp|gif|jpg|jpeg|png)['\"][^\>]*)>/i";
		$replacement    = '$1 rel="lightbox['.$post->ID.']">';
		$content = preg_replace($pattern, $replacement, $content);
		return $content;
	}
	
	public function add_style(){
		$sheet = plugins_url( "Themes/".$this->theme."/lightbox.css", __FILE__);
		echo "<link rel='stylesheet' href='".$sheet."' type='text/css' media='screen' />";
	}
	
	private function available_themes(){
		/* Check if there are themes: */
		$path =  $this->plugin_path . "/Themes";
		$themes = array();
		//print_r($lightbox_2_theme_path);
		if ($handle = opendir($path)) {
		    while (false !== ($file = readdir($handle))) {
		        if (substr($file, 0, 1) != "." && file_exists($path."/".$file."/lightbox.css")) {
		            $themes[$file] = $path."/".$file."/";
		        }   
		    }
		    closedir($handle);
		}
		return $themes;
	}
	
	public function lighter(){ ?><script type="text/javascript">
		jQuery(document).ready(function(){
			var rels = {};
			jQuery("a[rel^=lightbox]").lightbox({
					fitToScreen: true,
			    	scaleImages: true,
			    	xScale: 1.2,
			    	yScale: 1.2,
			    	displayDownloadLink: true,
			    	imageClickClose: false		
			});
			/*for(rel in rels){
				console.log(jQuery("a").filter(function(){
					return (jQuery(this).attr("rel") == rel);
				}).lightbox({
					fitToScreen: true,
			    	scaleImages: true,
			    	xScale: 1.2,
			    	yScale: 1.2,
			    	displayDownloadLink: true,
			    	imageClickClose: false		
				}));
			}*/
		});
	</script><?php }
		
	public function options_page(){
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['lightbox_jq_theme'])){
			update_option('lightbox_jq_theme', $_POST['lightbox_jq_theme']);
			update_option('lightbox_jq_automate', $_POST['lightbox_jq_automate']);
			update_option('lightbox_jq_resize', $_POST['lightbox_jq_resize']);
			$this->get_vars();
			echo "<h1>Updating vars</h1>";
		}
		
		$action = admin_url("options-general.php?page=".$this->optionspage);
		?>
		<div class='wrap'>
			<h2><?php _e('Lightbox 2 Options', 'lightbox_jq') ?></h2>
			<form name='lightbox-jq-options' method='post' action='<?php echo $action; ?>'>
				<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
					<tr valign="baseline">
					<th scope="row"><?php _e('Lightbox Appearance', 'lightbox_jq') ?></th> 
					<td><select name='lightbox_jq_theme'>
					<?php
						$current_theme = get_option('lightbox_jq_theme');
						foreach($this->available_themes() as $theme => $dir){
							$selected = $theme == $this->theme ? 'selected' : '';
							echo "<option value='".urlencode($theme)."' $selected>$theme</option>";
						}
					?>
					</select><p><small><?php 
						_e('If in doubt, try the Black theme', 'lightbox_jq') 
					?></small></p></td></tr>
					<tr valign="baseline">
					<th scope="row"><?php 
						_e('Auto-lightbox image links', 'lightbox_jq') ?></th> 
			    	<td><?php
						$ca = $this->automate ? 'checked' : '';
			    		echo "<input type='checkbox' name='lightbox_jq_automate' value='1' $ca />";
			    	?><p><small><?php 
						_e('Let the plugin add necessary html to image links', 'lightbox_jq') 
					?></small></p>
				 	</td>
					<tr valign="baseline">
					<th scope="row"><?php 
						_e('Shrink large images to fit smaller screens', 'lightbox_jq') ?></th> 
					<td><?php
						$cr = $this->resize ? 'checked' : '';
				   		echo "<input type='checkbox' name='lightbox_jq_resize' value='1' $cr />";
				   	?><p><small><?php 
						_e('Note: <u>Excessively large images</u> waste bandwidth '.
						   'and slow browsing!', 'lightbox_jq');
					?></small></p>
					</td>
			    	</tr>
				</table>

				<p class="submit">
			   		<input type="submit" value="<?php _e('Save Changes', 'lightbox_jq') ?>" />
			    </p>
			</form>
		</div>
	<?php }
}

/*
var rels = {};
jQuery("a[rel^=lightbox]").each(function(){ rels[jQuery(this).attr('rel')] = true; });
for(rel in rels){
	jQuery("a[rel="+rel+"]").lightbox({
		fitToScreen: true,
	    scaleImages: true,
	    xScale: 1.2,
	    yScale: 1.2,
	    displayDownloadLink: true,
	    imageClickClose: false		
	});
}
*/

new LightboxJQ();
?>