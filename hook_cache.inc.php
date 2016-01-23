<?php
function hook_ambassador_cache_delete( $post_id ) {
	global $post;
	// verify if this is an auto save routine.  // If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	// Check permissions
	if ( 'page' == $_POST['post_type'] ){ if ( !current_user_can( 'edit_page', $post_id ) ) return;  } 
	else { if ( !current_user_can( 'edit_post', $post_id ) ) return; }

	## IKKE LAGRE HVIS REVISJON, SJEKK OM DET FUNKER!!
	if( $post->post_type == 'revision' ) return; // Don't store custom data twice
	
 	// OK, we're authenticated: we need to find and save the data
	global $blog_id;
	
	if(empty($post->ID)||(int)$post->ID == 0)
		return false;
	if((int) $blog_id == 0)
		return false;
	
	// CURL DELETE
	require_once('UKM/curl.class.php');
	$url = 'http://ambassador.'. UKM_HOSTNAME .'/wpcache/reset';
	$curl = new UKMCURL();
	$curl->request( $url );
	var_dump( $url );
	var_dump( $curl->result );
}
