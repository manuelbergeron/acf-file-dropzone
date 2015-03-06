=== Advanced Custom Fields: File Dropzone Field ===
Contributors: manuelbergeron
Author: Manuel Bergeron
Author URI: http://www.libeo.com
Plugin URI: http://www.libeo.com
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: trunk
Homepage: http://www.libeo.com
Version: 1.0.0

Allow the admin or the user to drop a file into a dropzone field to upload
a video, an audio file or an image without using the Wordpress Media Manager.

Javascript Event :

//When a file is deleted, this event occur
.on('acf/delete_file_dropzone_attachement', function(event, data))
    data : {
        "postId" : postId, //the post id where the event occured
        "mediaType" : mediaType,  //the type of media. Could be : video, audio, image
        "attachmentId" : attachmentId  // the id of the attachement
    }

//When the value of the hidden field storing the value of the id of the attachement for the dropzone change
.on('change', function())

PHP event
//Event trigger when the image file upload is completed
do_action( 'acf/file_dropzone/image_upload_complete', $attachment_id, $metadata);

//Event trigger when the video file upload is completed
do_action( 'acf/file_dropzone/video_upload_complete', $attachment_id, $metadata);

//Event trigger when the audio file upload is completed
do_action( 'acf/file_dropzone/audio_upload_complete', $attachment_id, $metadata);

= 1.0.0 =
* Initial Release.
