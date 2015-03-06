<?php
/**
 * Created by IntelliJ IDEA.
 * User: manuelbergeron
 * Date: 15-02-03
 * Time: 15:47
 */
class acf_field_file_dropzone extends acf_field {
    var $settings;
    var $pluginPath;
    var $pluginUrl;

    /*
    *  __construct
    *
    *  Set name / label needed for actions / filters
    *
    *  @since	3.6
    *  @date	03/01/2015
    */

    function __construct()
    {
        // vars
        $this->name = 'filedropzone';
        $this->label = __("File dropzone",'acf');
        $this->category = __("Content",'acf');
        $this->pluginPath = plugin_dir_path( __FILE__ );
        $this->pluginUrl = plugins_url() . "/acf-file-dropzone";

        // do not delete!
        parent::__construct();

        // settings
        $this->settings = array(
            'path' => apply_filters('acf/helpers/get_path', __FILE__),
            'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
            'version' => '1.0.0'
        );
    }

    /*
     *  create_options()
     *
     *  Create extra options for your field. This is rendered when editing a field.
     *  The value of $field['name'] can be used (like below) to save extra data to the $field
     *
     *  @type	action
     *  @since	3.6
     *  @date	23/01/13
     *
     *  @param	$field	- an array holding all the field's data
     */

    function create_options( $field )
    {
        // defaults?
        /*
        $field = array_merge($this->defaults, $field);
        */

        // key is needed in the field names to correctly save the data
        $key = $field['name'];

        // Create Field Options HTML
        ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e("File type",'acf'); ?></label>
            </td>
            <td>
                <?php
                do_action('acf/create_field', array(
                    'type'		=>	'radio',
                    'name'		=>	'fields['.$key.'][file_type]',
                    'value'		=>	$field['file_type'],
                    'layout'	=>	'horizontal',
                    'choices' 	=>	array(
                        'image'	    =>	__("Image",'acf'),
                        'audio'		=>	__("Audio",'acf'),
                        'video'		=>	__("Video",'acf')
                    )
                ));

                ?>
            </td>
        </tr>
    <?php

    }


    /*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

    function create_field( $field ) {
        // defaults?
        $o = array();

        // perhaps use $field['preview_size'] to alter the markup?
        // create Field HTML
        if($field['file_type'] == "image" && $field['value'] && is_numeric($field['value']) )  {
            $url = wp_get_attachment_image_src($field['value'], $field['preview_size']);
            $o['class'] = 'active';
            $o['url'] = $url[0];
        } else if (is_numeric($field['value'])) {
            $o['class'] = 'active';
            $o['url'] = wp_get_attachment_url($field['value']);
        }
        ?>
        <div class="acf-file-dropzone-uploader clearfix <?php echo $o['class']; ?>" data-library="<?php echo $field['library']; ?>" >
            <div class="has-file">
                <div class="hover">
                    <ul class="bl">
                        <li><a class="acf-button-delete ir" href="#"><?php _e("Remove",'acf'); ?></a></li>
                    </ul>
                </div>
                <?php if ($field['file_type'] == "image") { ?>
                    <img class="acf-file-dropzone-image" width="150" height="auto" src="<?php echo $o['url']; ?>" alt=""/>
                <?php } else if ($field['file_type'] == "audio") {  ?>
                    <audio id="mediaelement_<?php echo $field['id']; ?>" src="<?php echo $o['url'] ?>" width="320">
                <?php } else if ($field['file_type'] == "video") { ?>
                    <video id="mediaelement_<?php echo $field['id']; ?>" src="<?php echo $o['url'] ?>" width="320" height="240"></video>
                <?php } ?>
            </div>
            <div class="no-file">
                <div class="dropzone" id="dropzone_<?php echo $field['id']; ?>">
                    <div class="fallback">
                        <input name="file" class="dropzone-file" id="file-<?php echo $field['id']; ?>" type="file" />
                    </div>
                    <input type="hidden" id="file_type-<?php echo $field['id']; ?>" name="file_type-<?php echo $field['id']; ?>" value="<?php echo $field['file_type']; ?>" />
                    <input type="hidden" class="acf-file-dropzone-value" id="hidden-<?php echo $field['id']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
                </div>
            </div>
        </div>
    <?php
    }

    /*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your create_field() action.
	*
	*  $info	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
    function input_admin_enqueue_scripts() {
        // register ACF scripts
        wp_register_script( 'mediaelement', $this->pluginUrl . '/vendor/mediaelement/build/mediaelement-and-player.min.js', array('acf-input'), $this->settings['version'] );
        wp_register_script( 'dropzone', $this->pluginUrl . '/vendor/dropzone/dist/min/dropzone.min.js', array('acf-input'), $this->settings['version'] );
        wp_register_script( 'acf-input-file-dropzone', $this->pluginUrl . '/js/acf-file-dropzone.js', array('acf-input'), $this->settings['version'] );
        wp_register_style( 'dropzone-basic', $this->pluginUrl . '/vendor/dropzone/dist/min/basic.min.css', array('acf-input'), $this->settings['version'] );
        wp_register_style( 'dropzone', $this->pluginUrl . '/vendor/dropzone/dist/min/dropzone.min.css', array('acf-input'), $this->settings['version'] );
        wp_register_style( 'acf-input-file-dropzone', $this->pluginUrl . '/css/style.css', array('acf-input'), $this->settings['version'] );
        wp_register_style( 'mediaelement', $this->pluginUrl . '/vendor/mediaelement/build/mediaelementplayer.min.css', array('acf-input'), $this->settings['version'] );

        wp_localize_script( 'acf-input-file-dropzone', 'acf_input_file_dropzone', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));

        // scripts
        wp_enqueue_script(array(
            'jquery', 'dropzone', 'mediaelement', 'acf-input-file-dropzone'
        ));
        // styles
        wp_enqueue_style(array(
            'dropzone', 'dropzone-basic', 'acf-input-file-dropzone', 'mediaelement'
        ));
    }

    /*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/

    function format_value_for_api( $value, $post_id, $field ) {
        $value = wp_get_attachment_url( $value );
        return $value;
    }
}

new acf_field_file_dropzone();