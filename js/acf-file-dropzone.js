Dropzone.autoDiscover = false;
var dropZones = new Array();
var mediaElementOptions = {
    defaultVideoWidth: 320,
    defaultVideoHeight: 240,
    audioWidth: 320,
    pluginWidth: -1,
    pluginHeight: -1
};
var acfFileDropzoneOptions = {
    id : "",
    maxFiles: 1,
    acceptedFiles: "image/*",
    dictDefaultMessage: "Veuillez déposer votre fichier ici ou cliquez ici.",
    dictMaxFilesExceeded: "Désolé mais vous excédez la quantité de fichier permis.",
    dictInvalidFileType: "Désolé mais ce type de fichier n'est pas permis.",
    url : acf_input_file_dropzone.ajaxurl,
    maxFilesize: 4096,
    hiddenField : null,
    player : null,
    params: {
        "mediaType" : "image"
    },

    success: function(file, response) {
        var media = JSON.parse(response);
        this.options.hiddenField.val(media.id);
        this.options.parent.addClass("active");
        if (this.options.params.mediaType == "video") {
            var id = "mediaelement_" + this.options.id;
            var dropzone = "dropzone_" + this.options.id;
            var newPlayer = jQuery('<video id="' + id + '" src="' + media.path + '" width="320" height="240"></video>');
            jQuery("#" + id).parents(".mejs-container").replaceWith(newPlayer);
            var player = new MediaElementPlayer("#" + id, mediaElementOptions);
        } else if (this.options.params.mediaType == "audio") {
            var id = "mediaelement_" + this.options.id;
            var newPlayer = jQuery('<audio id="' + id + '" src="' + media.path + '" width="320">');
            jQuery("#" + id).parents(".mejs-container").replaceWith(newPlayer);
            var player = new MediaElementPlayer("#" + id, mediaElementOptions);
        } else if (this.options.params.mediaType == "image") {
            jQuery("img", this.options.player).attr("src", media.path);
        }

        this.removeAllFiles();
    },
    error: function(file, errorMessage, xhr) {
        alert("ERREUR : " + errorMessage);
    }
}

jQuery(document).on('acf/setup_fields', function(e, postbox){
    if (jQuery("div.dropzone", postbox).length) {
        jQuery("div.dropzone", postbox).each(function () {
            acf.fields.dropzone.set({ $el : jQuery(this) }).init();

            var id = jQuery(this).attr("id");
            var idParts = id.split("_");
            if (idParts.length && idParts.length > 1) {
                var fieldId = idParts[1];
                var config = {
                    id : fieldId,
                    parent : jQuery("div.dropzone#" + id, postbox).parent().parent(),
                    hiddenField : jQuery("#hidden-" + fieldId, this),
                    acceptedFiles : "",
                    params: {
                        mediaType : "",
                        "action" : "acf_input_file_dropzone"
                    }
                }

                var type = jQuery("#file_type-" + fieldId, this).val();
                switch(type) {
                    case "audio" :
                        config.acceptedFiles = "audio/*";
                        config.params.mediaType = "audio";
                        break;
                    case "video" :
                        config.acceptedFiles = "video/*";
                        config.params.mediaType = "video";
                        break;
                    case "image" :
                        config.acceptedFiles = "image/*";
                        config.params.mediaType = "image";
                        break;
                }

                config = jQuery.extend(acfFileDropzoneOptions, config);
                var newDropzone = new Dropzone("#" + id,  jQuery.extend({}, config));
                dropZones.push(newDropzone);
            }
        });
    }
});


jQuery(document).ready(function($) {
    jQuery('video,audio').each(function() {
        var id = jQuery(this).attr("id");
        var player = new MediaElementPlayer("#" + id, mediaElementOptions);
    });

});


/* **********************************************
 Begin image.js
 ********************************************** */

(function($){

    /*
     *  File Dropzone
     *
     *  static model for this field
     *
     *  @type	event
     *  @date	1/03/15
     *
     */

    // reference
    var _media = acf.media;

    acf.fields.dropzone = {
        $el : null,
        $input : null,
        o : {},

        set : function( o ){
            // merge in new option
            $.extend( this, o );

            // find input
            this.$input = this.$el.find('input[type="hidden"]');

            // get options
            this.o = acf.helpers.get_atts( this.$el );

            // multiple?
            this.o.multiple = this.$el.closest('.repeater').exists() ? true : false;

            // wp library query
            this.o.query = {
                type : 'file-dropzone'
            };

            // library
            if( this.o.library == 'uploadedTo' )
            {
                this.o.query.uploadedTo = acf.o.post_id;
            }

            // return this for chaining
            return this;

        },
        init : function(){

            // is clone field?
            if( acf.helpers.is_clone_field(this.$input) )
            {
                return;
            }

        },
        add : function( image ){

            // this function must reference a global div variable due to the pre WP 3.5 uploader
            // vars
            var div = _media.div;

            // set div class
            div.addClass('active');


            // validation
            div.closest('.field').removeClass('error');

        },
        edit : function(){

            // vars
            var id = this.$input.val();

            // set global var
            _media.div = this.$el;

            // clear the frame
            _media.clear_frame();

            // create the media frame
            _media.frame = wp.media({
                title		:	acf.l10n.image.edit,
                multiple	:	false,
                button		:	{ text : acf.l10n.image.update }
            });
            // open
            _media.frame.on('open',function() {
                // set to browse
                if( _media.frame.content._mode != 'browse' )
                {
                    _media.frame.content.mode('browse');
                }

                // add class
                _media.frame.$el.closest('.media-modal').addClass('acf-media-modal acf-expanded');

                // set selection
                var selection	=	_media.frame.state().get('selection'),
                    attachment	=	wp.media.attachment( id );


                // to fetch or not to fetch
                if( $.isEmptyObject(attachment.changed) )
                {
                    attachment.fetch();
                }

                selection.add( attachment );
            });

            // close
            _media.frame.on('close',function(){
                // remove class
                _media.frame.$el.closest('.media-modal').removeClass('acf-media-modal');
            });

            // Finally, open the modal
            acf.media.frame.open();

        },
        remove : function()
        {
            var attachmentId = this.$el.find('.acf-file-dropzone-value').val();

             setTimeout( function(){
                $(document).trigger('acf/delete_file_dropzone_attachement', attachmentId);
             }, 200);

            // set atts
            this.$el.find('.acf-file-dropzone-image').attr( 'src', '' );
            this.$el.find('.acf-file-dropzone-value').val( '' ).trigger('change');

            // remove class
            this.$el.removeClass('active');
        },

        // temporary gallery fix
        text : {
            title_add : "Select Image",
            title_edit : "Edit Image"
        }

    };


    /*
     *  Events
     *
     *  jQuery events for this field
     *
     *  @type	function
     *  @date	1/03/2011
     *
     *  @param	N/A
     *  @return	N/A
     */

    $(document).on('click', '.acf-file-dropzone-uploader .acf-button-edit', function( e ){

        e.preventDefault();

        acf.fields.dropzone.set({ $el : $(this).closest('.acf-file-dropzone-uploader') }).edit();

    });

    $(document).on('click', '.acf-file-dropzone-uploader .acf-button-delete', function( e ){

        e.preventDefault();

        acf.fields.dropzone.set({ $el : $(this).closest('.acf-file-dropzone-uploader') }).remove();

    });

})(jQuery);
