<?php
/*
Plugin Name: Polls For Contact Form 7
Description: This Plugin allows you to create polls for contact form 7 with many form fields.
Author: Geek Code Lab
Version: 1.1
Author URI: https://geekcodelab.com/
Text Domain : polls-for-contact-form-7
*/
if (!defined('ABSPATH')) exit;

define( 'CF7P_BUILD', 1.0 );

if (!defined( 'CF7P_PLUGIN_DIR_PATH' ))
	define( 'CF7P_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__) );

if (!defined( 'CF7P_PLUGIN_URL' ))
	define( 'CF7P_PLUGIN_URL', plugins_url() . '/' . basename(dirname(__FILE__)) );

register_activation_hook( __FILE__, 'cf7p_plugin_activate' );
function cf7p_plugin_activate() {
	if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
		die( 'Polls for Contact Form 7 can not activate as it requires <b>Contact Form 7</b>.' );
	}
        
    global $wpdb; 
    $db_table_name = $wpdb->prefix . 'cf7p_options';  // table name
    $charset_collate = $wpdb->get_charset_collate();

    if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ){
        $sql = "CREATE TABLE " . $db_table_name . " (
            id bigint(20) NOT NULL AUTO_INCREMENT, 
            form_id bigint(20) NOT NULL, 
            inputs varchar(900) NOT NULL, 
            PRIMARY KEY  (id)
        ) ". $charset_collate .";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'cf7p_add_plugin_settings_link');
function cf7p_add_plugin_settings_link( $links ) {
	$support_link = '<a href="https://geekcodelab.com/contact/" target="_blank" >' . __( 'Support', 'polls-for-contact-form-7' ) . '</a>'; 
	array_unshift( $links, $support_link );
	
	$setting_link = '<a href="'. admin_url('admin.php?page=wpcf7') .'">' . __( 'Settings', 'polls-for-contact-form-7' ) . '</a>'; 
	array_unshift( $links, $setting_link );

	return $links;
}  

// Admin scripts
add_action('admin_enqueue_scripts','cf7p_plugin_admin_scripts');
function cf7p_plugin_admin_scripts(){
    wp_enqueue_style('cf7p-admin-css', plugins_url('assets/css/admin-style.css', __FILE__), array('wp-color-picker'), CF7P_BUILD);
    wp_enqueue_script('cf7p-admin-script', plugins_url() . '/' . basename(dirname(__FILE__)) . '/assets/js/admin-script.js', array( 'jquery','wp-color-picker' ),CF7P_BUILD);
    wp_localize_script( 'cf7p-admin-script', 'custom_call', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
}

// Front scripts
add_action('wp_enqueue_scripts','cf7p_plugin_front_scripts');
function cf7p_plugin_front_scripts(){
    wp_enqueue_style('cf7p-front-css', plugins_url('assets/css/front-style.css', __FILE__), array(), CF7P_BUILD);
    wp_enqueue_script('cf7p-front-script', plugins_url() . '/' . basename(dirname(__FILE__)) . '/assets/js/front-script.js', array( 'jquery' ),CF7P_BUILD);
    wp_localize_script( 'cf7p-front-script', 'custom_call', array('ajaxurl' => admin_url( 'admin-ajax.php' )) );
}

// Admin --Add More Poll
add_action('wp_ajax_cf7p_add_more','cf7p_add_more');
function cf7p_add_more(){
    $form_id = sanitize_text_field($_POST['form_id']);
    $contact_form = WPCF7_ContactForm::get_instance( $form_id );
    $form_fields  = $contact_form->scan_form_tags();    
    $valid_input_type = array('select','checkbox','radio'); 
    $valid_field_found = false;
    if(isset($form_fields) && !empty($form_fields)){
        foreach ($form_fields as $key => $value){
            if(in_array($value->basetype, $valid_input_type)){
                $valid_field_found = true;
                break;
            }
        }
    }

    if ($valid_field_found == true) { ?>
        <tr class="cf7p-field-row">
            <td>
                <input type="text" name="cf7p-title[]" id="cf7p-title" value=""  />
            </td>
            <td>
                <select name="cf7p-names[]" id="cf7p-name">
                    <?php
                    if(isset($form_fields) && !empty($form_fields)){
                        foreach ($form_fields as $key => $value) {
                            if (in_array($value->basetype,$valid_input_type)) {  ?>
                                    <option value="<?php esc_attr_e($value->name);  ?>" ><?php esc_attr_e($value->name);  ?></option>
                            <?php
                            }
                        }
                     } ?>
                </select>
            </td>
            <td>
                <button type="button" class="cf7p_remove_field">
                    <svg width="18" height="18" x="0" y="0" viewBox="0 0 1024 1024">
                        <g>
                            <path xmlns="http://www.w3.org/2000/svg" d="m724.9 952.2h-423c-22.1 0-40.4-17.1-41.9-39.2l-36.3-539.6c-1.6-24.3 17.6-44.8 41.9-44.8h495.6c24.3 0 43.5 20.6 41.9 44.8l-36.3 539.6c-1.5 22.1-19.8 39.2-41.9 39.2zm119.6-702.3h-657c-.6 0-1-.4-1-1v-114.9c0-.6.4-1 1-1h657c.6 0 1 .4 1 1v114.8c0 .6-.4 1.1-1 1.1z" fill="#000000" data-original="#000000"></path>
                            <path xmlns="http://www.w3.org/2000/svg" d="m690.9 189.5h-351.1c-.6 0-1-.4-1-1v-130.6c0-.6.4-1 1-1h351.1c.6 0 1 .4 1 1v130.6c0 .5-.4 1-1 1z" fill="#000000" data-original="#000000"></path>
                        </g>
                    </svg>
                </button>
            </td>
        </tr>
     <?php }
     else{ 
         ?>
        <tr class="cf7p-no-field" data-msg="1">
            <td colspan="2"><h3><?php esc_html_e('There is no relevant field found.','polls-for-contact-form-7'); ?></h3></td>
        </tr>
    <?php 
    }
    die;
}

// Admin --Remove Poll
add_action('wp_ajax_cf7p_remove','cf7p_remove');
function cf7p_remove(){
    $form_id    =   sanitize_text_field($_POST['form_id']);
    $field_name =   sanitize_text_field($_POST['field_name']);
    $option     =   get_option('cf7p_'.$form_id);
    $cf7p_name_option   =   explode(',',$option['cf7p_name']);
    $cf7p_title_option   =   explode(',',$option['cf7p_title']);

    // Remove field name from option
    $arr = $title = $names = [];
    if (isset($cf7p_name_option)) {
        foreach ($cf7p_name_option as $key => $value) {
            $arr[$value]=$cf7p_title_option[$key];
        }
    }
    if (isset($arr)) {
        foreach ($arr as $key => $value) {
            if ($key==$field_name) { unset($arr[$key]); }
             else{
                 $title[]   =   $value;
                 $names[]   =   $key;
             }
        }
    }
    $option['cf7p_name']  = implode(',',$names);
    $option['cf7p_title'] = implode(',',$title);
    if (count($cf7p_name_option) == 1) {
        $option['cf7p_status'] = ''; ?>
        <input type="hidden" name="" class="hide-remove-all" data-msg="1">
            <?php
    }
    update_option('cf7p_'.$form_id,$option);

    // Remove data from table
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'cf7p_options';
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $db_table_name WHERE form_id = %d", $form_id ) );
    if(isset($results) && !empty($results)){
        foreach ($results as $key => $value) {
            $row_id = $value->id;
            $data   = unserialize($value->inputs);
            $single_row_data = []; 
            if(isset($data) && !empty($data)){
                foreach ($data as $data_key => $data_value) {
                    if ($data_key!=$field_name) {
                        $single_row_data[$data_key]=$data_value;
                    }
                }
            }
            $serialize_data = serialize($single_row_data);     
            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET inputs = %s WHERE form_id = %d AND id = %d", $serialize_data, $form_id, $row_id ) );
        }
    }
    die;
}

// Admin --Remove All 
add_action('wp_ajax_cf7p_remove_all','cf7p_remove_all');
function cf7p_remove_all(){
    $option['cf7p_name'] = $option['cf7p_title'] = $option['cf7p_status'] = '';
    $form_id    =   sanitize_text_field($_POST['form_id']);
    $option     =   get_option('cf7p_'.$form_id);
    update_option('cf7p_'.$form_id,$option);
    
    global $wpdb;
    $cf7p_table  = $wpdb->prefix . 'cf7p_options';
    $wpdb->query( $wpdb->prepare( "DELETE FROM $cf7p_table WHERE form_id = %d", $form_id ) );
    die;
}

// Front --View Result
add_action('wp_ajax_cf7p_result_btn','cf7p_result_btn');
function cf7p_result_btn(){
    $form_id    =   sanitize_text_field($_POST['form_id']); ?>
    <div class="cf7p_view_result">
        <?php echo esc_html(do_shortcode('[cf7p id='.$form_id.']')); ?>
        <a href="javascript:void(0);" class="cf7p-btf" value="<?php esc_attr_e($form_id); ?>"><?php esc_html_e('Back To Form','polls-for-contact-form-7'); ?></a>
    </div>
    <?php
    die;
}
require_once(CF7P_PLUGIN_DIR_PATH . 'class-admin.php');
require_once(CF7P_PLUGIN_DIR_PATH . 'functions.php');

<<<<<<< HEAD
// added last line new
=======
// added comment to last line
>>>>>>> b7d2bdfcddf6e3b24f1f6148d1f45f522af457ae
