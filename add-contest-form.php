<?php

//Build the form to display on screen
function psc_photo_submit_uploader_callback( $atts ) { 

	//extract the contest name
	extract( shortcode_atts( array('photo_contest_name' => ''), $atts) );

	$theform ='<form method="post" id="psc_photo_submit_form"  action="" enctype="multipart/form-data" >';
	
	   //Nonce fields to verify visitor provenance
       $theform .= wp_nonce_field( 'add_photo_form', 'psc_photo_form' ); 
		
		//assign form fields to there value or nothing
		$title = ( empty( $_GET[ 'title' ] ) ? "" : $_GET[ 'title' ] );
		$desc = (  empty( $_GET[ 'desc' ] ) ? "" : $_GET[ 'desc' ] );
		$image = ( empty( $_FILES[ 'image' ] ) ? "" : $_FILES[ 'image' ] );

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
		Re-type the following text<br/>
                        
                        <img src="' . plugins_url(
                                'EasyCaptcha/easycaptcha.php', __FILE__ ) . '" /> <br/>
		<input type="text" name="contest_submission_captcha" />
		
		<input type="submit" name="submit" value="Submit" class="button-primary" />

	</form><br/>term id is:' . $term->id;

	return $theform;
}

add_shortcode( 'psc_photo_submit_form', 'psc_photo_submit_uploader_callback' );