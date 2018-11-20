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

/*******************************************
  ADMIN SIDE
/*******************************************/

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
        'supports'           => array( 'title', 'thumbnail' ),
		'taxonomies' 		 => array( '' ),
    );
	register_post_type( 'photo_contests', $args );
}

add_action('init', 'photo_contest_submission_post_type');

//Create a custom taxonomy for Contest Post Type
function create_photo_contest_taxonomies () {	
	register_taxonomy(
        'photo_contests_name',
        'photo_contests',
        array(
            'labels' => array(
                'name' => 'Contest Name',
                'add_new_item' => 'Add New Contest Name',
                'new_item_name' => 'New Contest Name'
            ),
            'show_ui' => true,
			'show_admin_column' => true,
            'show_tagcloud' => false,
            'hierarchical' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array( 'slug' => 'contest-name'),
        )
    );
}

add_action( 'init', 'create_photo_contest_taxonomies', 0 );

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

//add custom fields to photo contests post type entry page
add_action( 'admin_init', 'psc_admin_init' );

function psc_admin_init() {
    add_meta_box( 'psc_details_meta_box',
                  'Photo Submission Details',
                  'psc_display_review_details_meta_box',
                  'photo_contests', 'normal', 'high' );
}


//this function is called from the psc_admin_init function
function psc_display_review_details_meta_box( $photo_contest ) {
	
	//Retrieve current entrant and their age based on contest ID
    
    $entrantName = esc_html( get_post_meta( $photo_contest->ID, 'entrantName', true) );
    
    $entrantAge = intval( get_post_meta( $photo_contest->ID, 'entrantAge', true) );

    $photoDescription = esc_html( get_post_meta( $photo_contest->ID, 'photoDescription', true) );
	

?>

	<div style="display: block; margin-bottom:15px;">
  		<label for="entrantName" id="entrantNameLabel">Entrant Name:</label>
  		<input type="text" maxlength="50" id="entrantName" name="entrantName" value="<?php echo $entrantName; ?>" style="width:85%" />
	</div>	

	<div style="display: block; margin-bottom:15px;">
  		<label for="entrantAge" id="entrantAgeLabel">Entrant Age:</label>
  		<input type="text" size="3" maxlength="2" id="entrantAge" name="entrantAge" value="<?php echo $entrantAge; ?>" />
	</div>	

	<div style="display: block;">
  		<label for="photoDescription" id="photoDescriptionlabel">Photo Description:</label>
  		<textarea cols="2" rows="10" id="photoDescription" name="photoDescription" style="width:100%"><?php echo $photoDescription; ?></textarea>
	</div>	

<?php	
}

//register a function that will be called when custom post types fields are saved to the database:
add_action( 'save_post', 'psc_contest_fields', 10, 2 );

//Add an implementation for the psc_contest_fields function, defined in the previous add_action call:
function psc_contest_fields( $photo_contest_id, $photo_contest ) {
    
    //Check post type for photo_contests
    if( $photo_contest->post_type == 'photo_contests' ) {
        
        //Store data in post meta table if present in post data
        if ( isset( $_POST[ 'entrantName' ] ) && $_POST[ 'entrantName' ] != '' ) {
            update_post_meta( $photo_contest_id, 'entrantName', $_POST[ 'entrantName' ] );
        }
        
        if ( isset( $_POST[ 'entrantAge' ] ) && $_POST[ 'entrantAge' ] != '' ) {
            update_post_meta( $photo_contest_id, 'entrantAge', $_POST[ 'entrantAge' ] );
        }

        if ( isset( $_POST[ 'photoDescription' ] ) && $_POST[ 'photoDescription' ] != '' ) {
            update_post_meta( $photo_contest_id, 'photoDescription', $_POST[ 'photoDescription' ] );
        }
    }
}

//Add fields - Entrant Name, and Entrant Age to photo_contests post types page
add_filter( 'manage_edit-photo_contests_columns', 'psc_photo_add_columns' );
function psc_photo_add_columns( $columns ) {
	$columns[ 'photo_submission_entrant_name' ] = 'Entrant Name';
	$columns[ 'photo_submission_entrant_age' ] = 'Entrant Age';
    
    return $columns;
}

//populate the custom fields with data on the photo contests post type page
add_action( 'manage_posts_custom_column', 'psc_photo_populate_columns' );

function psc_photo_populate_columns( $column ) {
    
    if ( 'photo_submission_entrant_name' == $column ) {
        $photo_submission_entrant_name = esc_html( get_post_meta( get_the_ID(), 'entrantName', true ) );
        
        echo $photo_submission_entrant_name;
    
    } elseif ( 'photo_submission_entrant_age' == $column ) {
        $photo_submission_entrant_age = get_post_meta( get_the_ID(), 'entrantAge', true );
        
        echo $photo_submission_entrant_age;
    } 
}

//register the style sheet, styles.css, located in /styles
function psc_photo_submit_form_stylesheet() { 
	wp_enqueue_style('psc_photo_submit_form', plugins_url('/styles/psc_styles.css', __FILE__)); 
}  

add_action('wp_enqueue_scripts', 'psc_photo_submit_form_stylesheet');

include ('add-contest-form.php');