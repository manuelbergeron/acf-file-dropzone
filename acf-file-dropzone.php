<?php
/*
Plugin Name: Advanced Custom Fields: File dropzone field
Plugin URI: http://www.libeo.com/
Description: Add a dropzone to upload file without the media manager
Version: 1.0.0
Author: Manuel Bergeron
Author URI: http://www.libeo.com/
License: GPL
Copyright: Libeo
*/

// actions
add_action('acf/register_fields', 'register_fields_file_dropzone');

/*
 *  register_fields
 *
 *  @description:
 *  @since: 3.6
 *  @created: 03/01/15
 */
function register_fields_file_dropzone() {
    new acf_file_dropzone_plugin();
}

//Call ajax pour l'upload d'un fichier en dehors du constructeur car la classe est crée par un later hook
add_action( 'wp_ajax_acf_input_file_dropzone', "acf_input_file_dropzone");

/**
 * Callback pour la gestion de la sauvegarde du fichier
 */
function acf_input_file_dropzone() {
    $acfFileDropzonePlugin = new acf_file_dropzone_plugin();
    $acfFileDropzonePlugin->saveFile();
    die();
}

class acf_file_dropzone_plugin {

    /*
	*  Constructor
	*
	*  @description:
	*  @since 1.0.0
	*  @created: 03/01/15
	*/
    function __construct() {
        // vars
        $settings = array(
            'version' => '1.0.0',
            'remote' => 'none',
            'basename' => plugin_basename(__FILE__),
        );

        include_once('file-dropzone.php');

        //Call ajax to upload file asynchronously
        // add_action( 'wp_ajax_acf_file_dropzone_send', array($this, "saveFile"));
    }

    /**
     * Sauvegarde le fichier et ajoute ce qu'il faut dans la base de données
     *
     */
    public function saveFile() {
        global $blog_id, $wpdb;
        if (! isset($_FILES['file'])) {
            wp_die(__('Désolé, aucun fichier n\'a été reçu.'));
        }

        if (!current_user_can('upload_files')) {
            wp_die(__('Désolé, vous n\'avez pas les droits nécessaire pour envoyer un fichier.'));
        }

        $fileType = $_POST['mediaType'];
        $file = $_FILES['file'];
        $name = $file['name'];

        if (!function_exists('wp_handle_upload')) require_once(ABSPATH . 'wp-admin/includes/file.php');

        $file = wp_handle_upload($file, array('test_form' => false));

        if (isset($file['type'])) {
            $type = $file['type'];

            // Allow only JPG, GIF, PNG
            if ($fileType == "image" && !preg_match('/(jpe?g|gif|png)$/i', $type)) {
                wp_die(__('Désolé, ce type de fichier n\'est pas authorisé pour des raisons de sécurité.'));
            }

            // Break out file info
            $name_parts = pathinfo($name);
            $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
            $url = $file['url'];
            $title = $name;
            $file = $file['file'];
            if (!function_exists('wp_read_image_metadata')) require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Use image exif/iptc data for title if possible
            if ($fileType == "image" && $image_meta = @wp_read_image_metadata($file)) {
                if (trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))) {
                    $title = $image_meta['title'];
                }
            }

            // Construct the attachment array
            $attachment = array(
                'guid' => $url,
                'post_mime_type' => $type,
                'post_title' => $title,
                'post_content' => ""
            );

            // This should never be set as it would then overwrite an existing attachment
            if (isset($attachment['ID'])) {
                unset($attachment['ID']);
            }
            // Save the attachment metadata
            $attachment_id = wp_insert_attachment($attachment, $file);

            if (is_wp_error($attachment_id)) {
                wp_die(__('Désolé, une erreur s\'est produite lors de la sauvegarde du fichier'));
            }

            $metadata = array();
            if ($fileType == "image") {
                $imagesize = getimagesize( $file );
                $metadata['width'] = $imagesize[0];
                $metadata['height'] = $imagesize[1];
                $image_meta = wp_read_image_metadata( $file );
                if ( $image_meta )
                    $metadata['image_meta'] = $image_meta;
            }

            // Make the file path relative to the upload dir.
            $metadata['file'] = _wp_relative_upload_path($file);
            wp_update_attachment_metadata($attachment_id, $metadata);

            //Callbacks actions
            if ($fileType == "image") {
                do_action( 'acf/file_dropzone/image_upload_complete', $attachment_id, $metadata);
            } else if ($fileType == "video") {
                do_action( 'acf/file_dropzone/video_upload_complete', $attachment_id, $metadata);
            } else if ($fileType == "audio") {
                do_action( 'acf/file_dropzone/audio_upload_complete', $attachment_id, $metadata);
            }

            echo json_encode(array("id" => $attachment_id, "path" => get_the_guid($attachment_id)));
        }
    }
}

?>