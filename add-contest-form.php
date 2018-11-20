<?php

//Build the form to display on screen
function psc_photo_submit_uploader_callback( $atts ) { 

	//extract the contest name
	extract( shortcode_atts( array('photo_contest_name' => ''), $atts) );

	$theform ='<form method="post" id="psc_photo_submit_form" action="" enctype="multipart/form-data" >';
	
	   //Nonce fields to verify visitor provenance
       $theform .= wp_nonce_field( 'add_photo_form', 'psc_photo_form' ); 
		
		//assign form fields to there value or nothing
        $entrantName = ( empty( $_GET[ 'entrantName' ] ) ? "" : $_GET[ 'entrantName' ] );
        $age = ( empty( $_GET[ 'age' ] ) ? "" : $_GET[ 'age' ] );
		$title = ( empty( $_GET[ 'title' ] ) ? "" : $_GET[ 'title' ] );
		$desc = (  empty( $_GET[ 'desc' ] ) ? "" : $_GET[ 'desc' ] );

		if ( isset( $_GET[ 'addreviewmessage' ]) && $_GET[ 'addreviewmessage'] == 1 ) { 
         $theform .='<div style="margin:8px; border: 1px solid #ddd; background-color: #ff0;">
                Thank for your submission!
            </div>';
        } else if ( isset( $_GET[ 'errormessage' ]) && $_GET[ 'errormessage'] == 1 ) { 
         $theform .='<div style="margin:8px; border: 1px solid #ddd; background-color: #ff0000;">
                All fields are required.
            </div>';
		} else if ( isset( $_GET[ 'errormessage' ]) && $_GET[ 'errormessage'] == 2 ) { 
         $theform .='<div style="margin:8px; border: 1px solid #ddd; background-color: #ff0000;">
                <p>Captcha is wrong</p>
            </div>';
		} 
		
		//put back in the carriage returns
		$desc = str_replace( 'br/', "\n", $desc);

		$term = get_term_by('name', '2018 Winter Contest', 'photo_contests_name');
		
		$theform .= '

		<input type="hidden" name="photo_contest_name" value="' . $photo_contest_name . '">

		Entrant Name:<br/>
		<input type="text" name="entrantName" value="' . $entrantName . '" /><br/>

		Entrant Age:<br/>
		<input type="text" name="age" value="' . $age . '" size="2" /><br/>
	
		Photo Title:<br/>
		<input type="text" name="title" value="' . $title . '" /><br/>
		Photo Description:<br/>
		<textarea col="8" rows="5" name="desc" placeholder="A Description of the photo.">' . $desc . '</textarea><br/>
		Your Photo: <input type="file" name="image" value="<?php echo $image; ?> size="25" /><br/>
		<!-- Post variable to indicate user-submission items -->
		<input type="hidden" name="psc_photo_contest_submission" value="1" />
		<!--Re-type the following text<br/>
                        
                        <img src="' . plugins_url(
                                'EasyCaptcha/easycaptcha.php', __FILE__ ) . '" /> <br/>
		<input type="text" name="contest_submission_captcha" />-->
		
		<input type="submit" name="submit" value="Submit" class="button-primary" />

	</form>';

	return $theform;
}

add_shortcode( 'psc_photo_submit_form', 'psc_photo_submit_uploader_callback' );

// register a function that will intercept user-submitted contest entries: 
add_action( 'template_redirect', 'psc_photo_contests_new_submissions' );

function psc_photo_contests_new_submissions( $template ) {
    
	$photo_contest_submission = trim( $_POST[ 'psc_photo_contest_submission' ] ); 
    if ( !empty( $photo_contest_submission  ) ) {
    	//call the process function to check if submission is correct and add submit the entry.
        psc_process_photo_contest_submission();
    } else {
        return $template;
    }
}

//process the photo contest submission entry
function psc_process_photo_contest_submission() {

	//assign inputted entry to variables and remove unnecessary white spaces.
	$entrantName = trim( $_POST[ 'entrantName' ] );
	$age = trim( $_POST[ 'age' ] );
	$title = trim( $_POST[ 'title' ] );
	$desc = trim( $_POST[ 'desc' ] );
	$file_name = trim( $_FILES["image"]["name"] );

	//check if form was submitted correctly and that all the fields have been filled in	
	if ( wp_verify_nonce( $_POST[ 'psc_photo_form' ], 'add_photo_form') &&
		!empty( $entrantName ) &&
		!empty( $age ) &&
		!empty( $title ) &&
		!empty( $desc ) &&
		!empty( $file_name ) ) {

		//Redirect browser back to photo contest submission page
		$redirectaddress = ( empty( $_POST[ '_wp_http_referer' ] ) ? site_url() :
											   $_POST[' _wp_http_referer'] );

		wp_redirect( add_query_arg( 'addreviewmessage', '1', $redirectaddress ) );
		
		exit;

	} else {

		//assign already entered form values to variables to display again
		$post_entrantName = htmlentities(trim($_POST['entrantName']));
		$post_age = htmlentities(trim($_POST['age']));
		$post_title = htmlentities(trim($_POST['title']));
		$post_desc = htmlentities(trim($_POST['desc']));
		
		//replace spaces with %20 in variables
		$post_entrantName = str_replace(" ", "%20", $post_entrantName );
		$post_title = str_replace(" ", "%20", $post_title );
		$post_desc = str_replace( " ", "%20", $post_desc );
		
		//replaces carriage returns with br/
		$post_desc = nl2br($post_desc);

		//Redirect browser back to photo contest submission page
		$redirectaddress = ( empty( $_POST[ '_wp_http_referer' ] ) ? site_url() :
											   $_POST[' _wp_http_referer'] );

		wp_redirect( add_query_arg( array(
						'errormessage' 	=> '1',
						'entrantName'	=> $post_entrantName,
						'age'			=> $post_age,
						'title' 		=> $post_title,
						'desc' 			=> $post_desc), $redirectaddress ) );
		
		exit;

	}
}

//function create a new post from form submission
function psc_create_new_post( $post_entrantName, $post_age, $post_title, $post_desc, $post_image ) {
				
	// Create post object
	$new_contest_data = array(
	  'post_type'			=> 'photo_contests',
	  'post_status'			=> 'publish',
	  'post_title'    		=> $post_title
	);
	
	// Insert the post into the database
	$new_contest_id = wp_insert_post( $new_contest_data );
	
	
	//assign a custom category to the the post
	//wp_set_object_terms( post id, term value, custom post name );
	wp_set_post_terms( $new_contest_id, $_POST[ 'photo_contest_name' ], 'photo_contests_name');

	//assign entrant name and age as custom fields
	add_post_meta( $new_contest_id, 'entrantName',
                         wp_kses( $post_entrantName, array() ) );
						 
	add_post_meta( $new_contest_id, 'entrantAge',
                          (int) $post_age );

	add_post_meta( $new_contest_id, 'desc', 
						wp_kses( $post_desc, array() ) );
	//upload files
	$upload = wp_upload_bits($_FILES["image"]["name"], null, file_get_contents($_FILES["image"]["tmp_name"]));
	
	//get the most recent postID
	$post_id = pscGetLastPostId();
	
	// call the upload_photo function and returns the $attach_id
	$attach_id = psc_upload_photo($upload);		
	
	//attach photo to post
	set_post_thumbnail( $post_id, $attach_id );
}


//function takes photo from form and uploads it to WP Media Library
function psc_upload_photo($upload) {
	
	$filename = $upload['file'];
		$wp_filetype = wp_check_filetype($filename, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => sanitize_file_name($filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data ); 
		
		return $attach_id;
}

//function return the last post id
function pscGetLastPostId() {
    global $wpdb;

    $query = "SELECT ID FROM $wpdb->posts ORDER BY ID DESC LIMIT 0,1";

    $result = $wpdb->get_results($query);
    $row = $result[0];
    $id = $row->ID;

    return $id;
}