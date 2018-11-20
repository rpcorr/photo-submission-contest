<?php
/*
Plugin Name:  Photo Submissions Contest
Plugin URI: 
Description: A photo submission form that creates new posts to place in the information provided.  Uploads photo to the Media Library and attach it to the post
Version: 1.1
Author:  Ronan Corr
Author URI: http://www.ronancorr.com
License: GPLv2
*/


//Set up custom post type - Photo Contests
function photo_contest_submission_post_type() {
	$labels = array(
        'name'               => 'Photo Contests',
        'singular_name'      => 'Photo Contest',
        'menu_name'          => 'Photo Contests',
        'name_admin_bar'     => 'Photo Contest',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Photo Contest',
        'new_item'           => 'New Photo Contest',
        'edit_item'          => 'Edit Photo Contest',
        'view_item'          => 'View Photo Contest',
        'all_items'          => 'All Photo Contests',
        'search_items'       => 'Search Photo Contests',
        'parent_item_colon'  => 'Parent Photo Contests:',
        'not_found'          => 'No photo contests found.',
        'not_found_in_trash' => 'No photo contests found in Trash.',
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 20,
		'menu_icon'			 => 'dashicons-awards',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'photo-contests' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies' 		 => array( '' ),
    );
	register_post_type( 'photo_contests', $args );
}

add_action('init', 'photo_contest_submission_post_type');

function my_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    photo_contest_submission_post_type();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'my_rewrite_flush' );