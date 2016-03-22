<?php
/*
Plugin Name: atlink
Plugin URI: http://acegiak.net
Description: atlink people
Version: 1.0
Author: acegiak
Author URI: http://acegiak.net
License: GPL2
*/

/*  Copyright 2014  smartware.cc  (email : sw@smartware.cc)

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

function acegiak_atlink_webmention( $postid ) {
	if(get_post_status ( $postid ) != 'publish'){
		error_log("ATTAG WARNING: Not publish, no webmention on postid".$postid);
		return;
	}
	$thecontent = get_post_field('post_content', $postid);
	$check_hash = preg_match_all("`(^|\W)@(\w+)`i", $thecontent, $hashtweet);
	//error_log("hashtweetcheck: ".print_r($hashtweet,true));
	if(!is_array($hashtweet[2])){
		error_log("ATTAG ERROR: hashtweet2 isntarray ");
		return;
	}
	foreach ($hashtweet[2] as $ht){
	  foreach (get_bookmarks() as $bookmark){
			if(preg_match("`".$ht."`i",preg_replace("`\W`","",$bookmark->link_name))){
				do_action('send_webmention', get_permalink($postid), urldecode($bookmark->link_url));
			}
		}
	}

}

function acegiak_atlink_content( $content ) {
	return preg_replace_callback('`(^|\W)@(\w+)`i',
		function ($matches) {
			foreach (get_bookmarks() as $bookmark){
				if(preg_match("`".$matches[2]."`i",preg_replace("`\W`","",$bookmark->link_name))){
					return $matches[1].'<a class="h-card u-category" href="'.$bookmark->link_url.'" alt="'.$bookmark->link_name.'">'.$bookmark->link_name.'</a>';
				}
			}
	return $matches[1].'<a class="h-card u-category" href="https://twitter.com/'.$matches[2].'" alt="@'.$matches[2].'">@'.$matches[2].'</a>';;
        },$content);
	
}

add_action( 'save_post', 'acegiak_atlink_webmention', 9999 );
add_filter( 'the_content', 'acegiak_atlink_content', 9999 );


		$post_ID = apply_filters( 'webmention_post_id', $post_ID, $target );

add_filter('webmention_post_id', 'acegiak_atlink_default_webmention_target',10,2);

function acegiak_atlink_default_webmention_target($post_ID, $target ){
	//error_log("filtering failed webmention");
	if(!$post_ID){
		//error_log("yup, it's failed");
$options = get_option( 'acegiak_atlink_options' );
		$page = get_page_by_path($options['page_slug']);
		if($page != null){
			//error_log("assigning my default id");
			return $page->ID;
		}
	}	
	return $post_ID;
}

// Register and define the settings
add_action('admin_init', 'acegiak_atlink_admin_init');

function acegiak_atlink_admin_init(){
	register_setting(
		'discussion',                 // settings page
		'acegiak_atlink_options',          // option name
		'acegiak_atlink_validate_options'  // validation callback
	);
	
	add_settings_field(
		'acegiak_atlink_person_tag_slug',      // id
		'Person Tag Page Slug',              // setting title
		'acegiak_atlink_setting_input',    // display callback
		'discussion',                 // settings page
		'default'                  // settings section
	);

}


function acegiak_atlink_setting_input() {
	// get option 'boss_email' value from the database
	$options = get_option( 'acegiak_atlink_options' );
	$value = $options['page_slug'];
	
	// echo the field
	?>
<input id='page_slug' name='acegiak_atlink_options[page_slug]'
 type='text' value='<?php echo esc_attr( $value ); ?>' /> Slug of page to assign person tag comments to
	<?php
}



// Validate user input and return validated data
function acegiak_atlink_validate_options( $input ) {
	$valid = array();
	$valid['page_slug'] = $input['page_slug'];
	return $valid;
}


add_action('transition_comment_status', 'acegiak_atlink_approve_comment_callback', 10, 3);
function acegiak_atlink_approve_comment_callback($new_status, $old_status, $comment) {
    if($old_status != $new_status) {
        if($new_status == 'approved') {
            $postid = $comment->comment_post_ID;
	    acegiak_atlink_webmention( $postid );
        }
    }
}
?>