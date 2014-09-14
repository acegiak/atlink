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
	$thecontent = get_post_field('post_content', $postid);
	$check_hash = preg_match_all("/([#][a-zA-Z-0-9]+)/", $thecontent, $hashtweet);
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
					return $matches[1].'<a class="h-card" href="'.$bookmark->link_url.'" alt="'.$bookmark->link_name.'">'.$bookmark->link_name.'</a>';
				}
			}
return $matches[0];
        },$content);
	
}

add_action( 'save_post', 'acegiak_atlink_webmention', 9999 );
add_filter( 'the_content', 'acegiak_atlink_content', 9999 );

?>