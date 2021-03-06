<?php
/*
Plugin Name: My WordPress Plugin
Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
Description: Basic WordPress Plugin Header Comment
Version:     1.0
Author:      WordPress.org
Author URI:  https://developer.wordpress.org/
Text Domain: my-plugin-textdomain
Domain Path: /languages
License:     GPL2
 
{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

register_activation_hook( __FILE__, 'myplugin_activate' );

//Activation and add table to db
function myplugin_activate() {
	global $wpdb;
	$sql = "CREATE TABLE `".$wpdb->prefix."my_favorite_posts` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
	  `post_id` int(11) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`user_id`),
	  KEY `post_id` (`post_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	$wpdb->query( $sql );
}


register_deactivation_hook( __FILE__, 'myplugin_deactivate' );

//Nothing
function myplugin_deactivate() {

}

//Uninstall hook
register_uninstall_hook(__FILE__, 'myplugin_uninstall');
function myplugin_uninstall() {
	//uninstall actions, for example delete_option("option_name"); - your options should not litter the database
}


//Add button to post
add_filter( 'the_content', 'mfp_add_button' );

//Add button to post
function mfp_add_button($content){
	global $post;
	
	$user_id = get_current_user_id();

	if (!$user_id) {
		$message = '<p class="my-plugin-message">' . __( 'Please, Sign In to Add to Favorite', 'my-plugin-textdomain' ) . '</p>';
		$content .= $message;
		return $content;
	}

	if ($post->post_type == 'post') {
		
		$mfp_button = '<form action="" method="post" enctype="multipart/form-data">'.
		$mfp_button .= '<input type="hidden" name="mfp_post_id" value="'.$post->ID.'">';
		$mfp_button .= '<input type="hidden" name="mfp_action" value="add">';
		$mfp_button .='<input type="submit" id="mfp_button" value="'.__( 'Add to favorite', 'my-plugin-textdomain' ).'">';
		$mfp_button .= '</form>';
		
		$content .= $mfp_button;
		
	}
	return $content;
}

//Shortcode to display faforite posts everywhere ([my-favorite-posts])
add_shortcode( 'my-favorite-posts', 'mfp_shortcode');

//Shortcode to display faforite posts everywhere ([my-favorite-posts])
function mfp_shortcode() {
	global $post;

	$user_id = get_current_user_id();
	if (!$user_id) {
		return;
	}
	$posts = mfp_get_posts($user_id);
	if ($posts) { ?>
		<ul>
		<?php 
		foreach ($posts as $mf_post) {
			$s_post = get_post($mf_post->post_id);
			?>
			<li>
					<p class="mfp_title"><a href="<?php echo get_permalink($mf_post->post_id) ?>" rel="bookmark"><?php echo $s_post->post_title; ?></a></p>

					<div class="mfp_thumbnail">
						<a href="<?php echo get_permalink($mf_post->post_id); ?>">
							<?php echo  get_the_post_thumbnail($mf_post->post_id, 'medium'); ?>
						</a>
					</div>
				
				<div class="mfp_content">
					<p><?php echo $s_post->post_content; ?></p>
					<form action="" method="post" enctype="multipart/form-data">
						<input type="hidden" name="mfp_post_id" value="<?php echo $mf_post->post_id; ?>">
						<input type="hidden" name="mfp_action" value="remove">
						<input type="submit" id="mfp_button" value="<?php echo __( 'Remove from favorite', 'my-favorite-posts' ); ?>">
					</form>
				</div>
			</li>
			<?php 
		} ?>
		</ul>
		<?php 
	} else {
		echo __( 'No entries found', 'my-favorite-posts' );
	}

	return $html;
}

//Get favorite posts array 
function mfp_get_posts($user_id){
	global $wpdb;
	$sql = "SELECT post_id FROM `".$wpdb->prefix."my_favorite_posts` WHERE user_id = %d";
	$result = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
	return $result;
}