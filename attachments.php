<?php
/**
 * Name: Attachments Metabox Uploader
 * Author: Jared Williams - http://new2wp.com
 * Description: This is for adding a custom metabox to post types which enables you to add/remove 
 * post attachments that you can upload and enter the meta information for right on the edit page.
 * Version: 0.1.0
 *
 * Notes: In order to add this to a post type you need to find the word 'product' and replace it with * whatever the post type is you want to use it on. 
 */

/**
 * TODO:
    1. Make the Save function so the Save button uploads and saves attachments with ajax
    2. Fix the saving of the alt text field and captions
    3. Fix the jQuery ADD markup so the added fields are matching what shows when saved
    4. Make the returned ajax request display in a div not an alert box
    6. There is something I can't remember since writing #2 and it's bugging me now
    7. Now I'm just writing things that aren't really TODO's 
    8. lol this is another one that is not a TODO item
 */
add_action( 'admin_init', 'add_attachment' );
add_action( 'save_post', 'update_attachment' );
add_action( 'post_edit_form_tag', 'form_multipart_encoding' );

add_action( 'wp_ajax_delete_attachment', 'delete_attachment' );
//add_action( 'wp_ajax_delete_attachment', 'update_attachment' );

//add_filter( 'manage_posts_custom_column', 'post_custom_columns' );
//add_filter( 'manage_edit-post_columns', 'post_edit_columns' );


/**
 * Add the metabox to the post type edit page
 */
function add_attachment(){
    add_action( 'admin_head', 'metabox_styles' );
    add_action( 'admin_head', 'metabox_scripts' );
    add_meta_box( 'img-uploads', __( 'Add Image to Homepage Featured Post' ), 'add_attachments', 'post', 'normal', 'high' );
}

/**
 * Add the form multipart attribute for file uploads
 */
function form_multipart_encoding() {
    echo ' enctype="multipart/form-data"';
}

/**
 * Create the metabox and display any attachments as previews
 */
function add_attachments(){
    global $post, $title, $image, $content, $filename;
    
	$attachments = get_posts( array( 'post_type' => 'attachment', 'post_parent' => $post->ID, 'order' => 'ASC' )); ?>
    
    	<details><p>The first image you add below will be the image used for the featured item shown on the homepage even if you add more than one image, the first is still featured on the homepage. The rest will be included in your post as attachment images.</p></details>
    	
    	<a id="addImg" href="#" class="addImage button-primary fr"><?php _e( 'Add Image' ); ?></a>
    	<div id="img_uploads">
    <?php
    if( $attachments ) {
        foreach( $attachments as $attachment ) {
        	$default_attr = array(
				'src'	=> $src,
				'class'	=> 'attachment-thumbnail',
				'alt'   => trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) )),
				'title' => trim(strip_tags( $attachment->post_title )),
			); ?>
        
            <div id="att-<?php echo $attachment->ID; ?>" class="attchmt">
                <?php wp_get_attachment_metadata( $attachment->ID); ?>
                    <a class="remImage submitdelete deletion fr" href="#"><?php _e('Delete'); ?></a>
                    <?php _e('Image: '); ?>
                        <?php if( !isset( $attachment->guid )) { ?>
                            <input type="file" name="a_image" />
                        <?php } else { ?>
                            <input type="text" name="a_url" size="60" value="<?php echo $attachment->guid; ?>" />
                    	<?php } ?>
	                
                    <div class="fr">
                    <?php
                    	echo wp_get_attachment_image( $attachment->ID, 'thumbnail', false, $default_attr);
						echo apply_filters( 'the_title', $attachment->post_title );
						echo apply_filters( 'the_title', $attachment->post_title );
					?>
					</div>
                    <div class="prevImage">
	                    <?php the_attachment_link( $attachment->ID, false); ?>
	                </div>
	                <div class="right"><details><pre><?php print_r($attachment); ?></pre></details></div>
	                
                <!-- <a class="saveImage button" href="#"><?php _e('Save'); ?></a>-->
                <a href="#" class="addImage button fr"><?php _e('Add Image'); ?></a>
            	<input type="hidden" id="att_ID" name="att_ID" value="<?php echo $attachment->ID;?>" />
            <input type="hidden" name="nonce_delete" id="nonce_delete" value="<?php echo wp_create_nonce('delete_attachment');?>" />
            </div>
        <?php
            }
        } ?>
    </div>
    <?php
}

/**
 * jQuery scripts for the fancy stuff
 */
function metabox_scripts() { ?>
    
    <script type="text/javascript">
    
    jQuery(function() {
        var imgDiv = jQuery('#img_uploads'),
            size = jQuery('#img_uploads .attchmt').size() + 1;			
        jQuery('.addImage').click(function(e) {
            jQuery('<div id="' + size + '" class="attchmt"><a class="remImage submitdelete deletion fr" href="#"><?php _e('Remove'); ?></a><?php _e('Image: '); ?><input type="file" name="a_image" /><div class="prevImage"></div></div>')
	                
	            //<div id="' + size + '" class="attchmt"><a class="remImage submitdelete deletion" href="#"><?php _e('Remove'); ?></a><span class="req">*</span> <?php _e('Image: '); ?><input type="file" name="a_image" /><div class="prevImage"><div align="center"></div></div><a href="#" class="addImage button fr"><?php _e('Add Image'); ?></a></div>')
                .fadeIn('slow').prependTo(imgDiv);
            size++;
            e.preventDefault();
        });
        jQuery('.remImage').click(function(e) {
            if( size > 1 ) {
                var jqXhr = jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: {
                        action: 'delete_attachment',
                        att_ID: jQuery(this).parents('.attchmt').find('#att_ID').val(),
                        _ajax_nonce: jQuery('#nonce').val(),
                        post_type: 'attachment'
                    }
                })
                .done(function( html ) {
                    alert( html );
                });
                jQuery(this).parents('.attchmt').fadeOut('slow').detach();
                jQuery(this).parents('.attchmt').siblings('#addImage').detach();
                size--;
            }
            e.preventDefault();
        });
    });
    </script>	
    <?php
}

/**
 * Make it look stylish
 */
function metabox_styles() { ?>
    <style type="text/css">
    .hidetable { display: none; }
    #uploadfield { width: 500px; }
    #uploadfield input { display: inline; }
    .attchmt { border-bottom: 35px solid #ccc; padding: 0; margin: 10px 0; }
    .prevImage { display: inline-block; width: auto; position: relative; left: 25px; }
    .prevImage a { display: inline-block; }
    #addImg{ position: absolute; top: -3px; right: 10px; }
    .addImage { margin: 6px;  }
    .right { position: relative; left: 20px; }
    .remImage {top: 5px; position: relative; color: red; float: right; }
    .fr { float: right; }
    </style>
    <?php	
}

/**
 * Delete the attachment permanently. Remove 'true' from wp_delete_attachment() 
 * if you only want to remove it from the post not the site completely
 */
function delete_attachment( $post ) {
    if( $_POST['att_ID'] ) {
        $msg = _e( 'Attachment ID [' . $_POST['att_ID'] . '] has been deleted!' );
        if( wp_delete_attachment( $_POST['att_ID'], true )) {
            echo $msg;
        }
    }
    die();
}

/**
 * Save and/or Update the file attachment.
 * This function does the uploading and sets the metadata for the file.
 */
function update_attachment() {
    global $post;
    //wp_update_attachment_metadata( $post->ID, $_POST['a_image'] );

    if( !empty( $_FILES['a_image']['name'] ) || !empty( $_POST['a_url'] )) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $override['action'] = 'editpost';
        $file = wp_handle_upload( $_FILES['a_image'], $override );

        if ( isset( $file['error'] )) {
            return new WP_Error( 'upload_error', $file['error'] );
        }
		$types = array(
            'jpg|jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png'
		);
        $file_type = wp_check_filetype( $_FILES['a_image']['name'], null );
        
        if( $file_type['type'] ) {
         
            $url = $file['url'];
            $name = $file['filename'];
            $type = $file['type'];
            $title = $_POST['a_title'] ? $_POST['a_title'] : $name;
            $content = $_POST['a_content'];

            $post_id = $post->ID;
            $attachment = array(
                'post_title' => $title,
                'post_type' => 'attachment',
                'post_content' => $content,
                'post_parent' => $post_id,
                'post_mime_type' => $type,
                'guid' => $url,
            );

            foreach( get_intermediate_image_sizes() as $s ) {
                $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => true );
                $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
                $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
                $sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
            }

            $sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes );

            foreach( $sizes as $size => $size_data ) {
                $resized = image_make_intermediate_size( $file['file'], $size_data['width'], $size_data['height'], $size_data['crop'] );
                if ( $resized )
                    $metadata['sizes'][$size] = $resized;
            }

            $attach_id = wp_insert_attachment( $attachment, $file['file'] /*, $post_id - for post_thumbnails*/);

            if ( !is_wp_error( $id )) {
                $attach_meta = wp_generate_attachment_metadata( $attach_id, $file['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_meta );
            }
            update_post_meta( $post->ID, 'a_image', $images );
        }
    }
}

function post_edit_columns( $columns ) {
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'a_image' => __( 'Image' ),
    );
    return $columns;
}
function post_custom_columns( $column ) {
    global $attachment;
    switch ( $column ) {
        case 'a_image' :
        the_attachment_link( $attachment->ID, false);
        break;
    }
}

/* END POST ATTACHMENTS */
?>