<?php

/*

Plugin Name:  Tensai RSS

Version: 1.0

Plugin URI: http://tensaiweb.info/blog/plugins-wordpress/tensai-rss/

Description: Allows you to add some cool changes to your RSS feed articles.

Author: Rogelio Hoyos

Author URI: http://tensaiweb.info/blog/

*/

if ( ! class_exists( 'TensaiRSS_Admin' ) ) {



	class TensaiRSS_Admin {

		

		function add_config_page() {

			global $wpdb;

			if ( function_exists('add_submenu_page') ) {

				add_options_page('Tensai RSS Configuration', 'Tensai RSS', 1, basename(__FILE__), array('TensaiRSS_Admin','config_page'));

			}

		}

		

		function config_page() {

			if ( isset($_POST['submit']) ) {

				if (!current_user_can('manage_options')) die(__('You cannot edit the Tensai RSS options.'));

				check_admin_referer('tensairss-config');



				if (isset($_POST['signpost']) && $_POST['signpost'] != "") 

					$options['signpost'] 	= $_POST['signpost'];

				if (isset($_POST['reptext']) && $_POST['reptext'] != "") 

					$options['reptext'] 	= $_POST['reptext'];

				/*checks*/
				if (isset($_POST['imagesfeed'])) {

					$options['imagesfeed'] = true;

				} else 
					$options['imagesfeed'] = false;
				if (isset($_POST['applyfb'])) {

					$options['applyfb'] = true;

				} else {

					$options['applyfb'] = false;	

				}
				if (!isset($_POST['applyfb']) && !(strpos($_SERVER['REQUEST_URI'],'feedburner.com')===false )) {
					$options['allchanges'] = false;

				} else 

					$options['allchanges'] = true;

				$options['inicializar'] = true;

				

				$opt = serialize($options);

				update_option('TensaiRSSOptions', $opt);

			}

			

			$opt  = get_option('TensaiRSSOptions');

			$options = unserialize($opt);

			

			?>

			<div class="wrap">

				<h2>Tensai RSS options</h2>

				<form action="" method="post" id="tensairss-conf">

					<?php

					if ( function_exists('wp_nonce_field') )

						wp_nonce_field('tensairss-config');

					?>

					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>

					<table class="niceblue" style="width: 100%;">

						<tr>

							<th scope="row">

							<label for="imagesfeed">Images:</label>	

							</th>

							<td>

								<input type="checkbox" name="imagesfeed" <?php if ($options['imagesfeed']) echo 'checked="checked"'?>/> Replace images in the post.

							</td>
						</tr>
						<tr>
							<th scope="row">

							<label for="reptext">Replacement Text:</label><br/>
							<small>(HTML allowed)</small>

							</th>

							<td>

								<input style="width:400px;" type="text" name="reptext" <?php if ($options['reptext']) echo 'value="'.$options['reptext'].'"'?>/>

							</td>

						</tr>
						
						<tr valign="top">

							<th scope="row">

								<label for="signpost">Signature:</label><br/>

								<small>(HTML allowed)</small>

							</th>

							<td>

								<textarea cols="80" rows="4" id="signpost" name="signpost"><?php echo stripslashes(htmlentities($options['signpost'])); ?></textarea>

							</td>

						</tr>
						<tr>

							<th scope="row">

							<label for="applyfb">Feedburner:	

							</th>

							<td>

								<input type="checkbox" name="applyfb" <?php if ($options['applyfb']) echo 'checked="checked"'?>/> Apply changes to feedburner.

							</td>

						</tr>
					</table>

					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>

				</form>

			</div>

<?php		}	

	}

}


$options  = unserialize(get_option('TensaiRSSOptions'));

if (!isset($options['inicializar'])) {

	// Set default values

	$options['signpost'] = "Original post in: <a href=\"".get_bloginfo('url')."\">".get_bloginfo('name')."</a>";
	$options['reptext']="Click to see full size image";

	$opt = serialize($options);

	update_option('TensaiRSSOptions', $opt);

}



function set_tensairss($content) {

	if(is_feed()) {

		$options  = unserialize(get_option('TensaiRSSOptions'));

		if($options['allchanges'])
		{
			$content = ereg_replace("<\!==rss(([^=]+[=]?[^=]+)+)==>", "\\1", $content);
			$content = ereg_replace("<\!==blog(([^=]+[=]?[^=]+)+)==>", "", $content);
		}
		if($options['imagesfeed'] && $options['allchanges'])
			$content = ereg_replace("<img src=([^>]+)>", " <a  href='".get_the_guid()."'>".$options['reptext']."</a><a  href='".get_the_guid()."'><img style='border:0;' src='".get_bloginfo('wpurl')."/wp-content/plugins/tensai-rss/external.png'/></a> ", $content);

		if($options['allchanges'])
		{
			$content = ereg_replace("<a href=([^>]+)>[^<]+</a>", "\\0<a href=\\1><img style='border:0;' src='".get_bloginfo('wpurl')."/wp-content/plugins/tensai-rss/external.png'/></a>", $content);
		
			if($options['signpost'])
				$content = $content . "<div>" . stripslashes($options['signpost']) . "</div>\n";					
			
		}	
	
	}
	else
	{
		$content = ereg_replace("<\!==rss(([^=]+[=]?[^=]+)+)==>", "", $content);
		$content = ereg_replace("<\!==blog(([^=]+[=]?[^=]+)+)==>", "\\1", $content);
	}
	return $content;
}

add_filter('the_content', 'set_tensairss');
add_action('admin_menu', array('TensaiRSS_Admin','add_config_page'));

?>