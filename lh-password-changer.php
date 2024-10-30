<?php
/**
 * Plugin Name: LH Password Changer
 * Plugin URI: https://lhero.org/portfolio/lh-password-changer/
 * Description: Front end change password form
 * Version: 1.55
 * Requires PHP: 5.3
 * Author: Peter Shaw
 * Author URI: https://shawfactor.com/
 * Text Domain: lh_password_changer
 * Domain Path: /languages
*/

if (!class_exists('LH_password_changer_plugin')) {


class LH_password_changer_plugin {

var $opt_name = 'lh_password_changer-options';
var $page_id_field = 'lh_password_changer-page_id';
var $hidden_field_name = 'lh_password_changer-submit_hidden';
var $namespace = 'lh_password_changer';
var $plugin_version = '1.54';

static function curpageurl() {
	$pageURL = 'http';

	if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on")){
		$pageURL .= "s";
}

	$pageURL .= "://";

	if (($_SERVER["SERVER_PORT"] != "80") and ($_SERVER["SERVER_PORT"] != "443")){
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

}

	return $pageURL;
}


 /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name    The    ID to register with WordPress
     * @file_path        The path to the actual file
     * @is_script        Optional argument for if the incoming file_path is a JavaScript source file.
     */
    private function load_file( $name, $file_path, $is_script = false, $deps = array(), $in_footer = true, $atts = array() ) {
        $url  = plugins_url( $file_path, __FILE__ );
        $file = plugin_dir_path( __FILE__ ) . $file_path;
        if ( file_exists( $file ) ) {
            if ( $is_script ) {
                wp_register_script( $name, $url, $deps, $this->plugin_version, $in_footer ); 
                wp_enqueue_script( $name );
            }
            else {
                wp_register_style( $name, $url, $deps, $this->plugin_version );
                wp_enqueue_style( $name );
            } // end if
        } // end if
	  
	  if (isset($atts) and is_array($atts) and isset($is_script)){
		
		
  $atts = array_filter($atts);

if (!empty($atts)) {

  $this->script_atts[$name] = $atts; 
  
}

		  
	 add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	   

	   
if (isset($this->script_atts[$handle][0]) and !empty($this->script_atts[$handle][0])){
  
$atts = $this->script_atts[$handle];

$implode = implode(" ", $atts);
  
unset($this->script_atts[$handle]);

return str_replace( ' src', ' '.$implode.' src', $tag );

unset($atts);
usent($implode);

		 

	 } else {
	   
 return $tag;	   
	   
	   
	 }
	

}, 10, 2 );
 

	
	  
	}
		
    } // end load_file

private function handle_result($result){

$output = '';

if (!is_numeric($result)){

$output .= "<p>".__('There was an error: ', $this->namespace)."</p>";

$output .= $result;

} else {

$output .= "<p>".__('Your password has been changed', $this->namespace)."</p>";

}

return $output;


}





private function create_page() {

$options = get_option($this->opt_name);

if (!$page = get_page($options[$this->page_id_field])){


$page['post_type']    = 'page';
$page['post_content'] = '[lh_password_changer_form]';
$page['post_status']  = 'publish';
$page['post_title']   = 'Change Password';

if ($pageid = wp_insert_post($page)){

$options = $this->options;

$options[$this->page_id_field] = $pageid;

if (update_option($this->opt_name, $options )){


}

}
}
}

private function can_user_edit(){

$userobject = $this->get_referenced_user();


if (isset($userobject->ID) and current_user_can('edit_user', $userobject->ID) ){

return $userobject;

} else {

return false;


}

}


private function get_referenced_user(){

if (isset($_GET['user_id'])){

$userobject = get_userdata( $_GET['user_id'] );


} else {

$userobject = wp_get_current_user();

}

if (isset($userobject->ID)){

return $userobject;

} else {

return false;

}

}



function process_password_change(){



if ( (isset( $_POST['lh_password_changer-password1'] )) and ($user = wp_get_current_user())){

if ($userobject = $this->can_user_edit()){


if ( wp_verify_nonce( $_POST['lh_password_changer-form-nonce'], 'lh_password_changer-change_password'.$userobject->ID) ) {


$password1 = trim($_POST['lh_password_changer-password1']);

$password2 = trim($_POST['lh_password_changer-password2']);

if (empty($password1)){

$form_error = __( "The string is empty", $this->namespace );

} elseif ($password1 == $password2){

wp_set_password( $password1, $userobject->ID );

$current_user = wp_get_current_user();

if ($current_user->ID == $userobject->ID){

wp_set_auth_cookie( $userobject->ID, true);

}



} else {

$form_error = __( "The passwords do not match", $this->namespace );


}


if (isset($form_error ) ) {


setcookie($this->namespace.'-message', json_encode($form_error), time()+3600);  /* expire in 1 hour */

wp_redirect(self::curpageurl()); 

} else {
    
$timestamp = current_time('mysql');
    
update_user_meta( $user->ID, $this->namespace.'-password_updated', $timestamp );

setcookie($this->namespace.'-message', $userobject->ID, time()+3600);  /* expire in 1 hour */


if (isset($_POST['redirect']) and !empty($_POST['redirect'])){
    
wp_redirect($_POST['redirect']); 


    
    
} else {
    
wp_redirect(self::curpageurl()); 



}

}


exit;

}

}

} elseif (is_singular() and isset($_COOKIE[$this->namespace.'-message'])){

if (is_numeric($_COOKIE[$this->namespace.'-message'])){

$GLOBALS['lh_password_changer-form-result'] = $_COOKIE[$this->namespace.'-message'];

} else {


$GLOBALS['lh_password_changer-form-result'] = json_decode(trim(stripslashes($_COOKIE[$this->namespace.'-message'])));


}

setcookie($this->namespace.'-message', '', time() - 3600);


}


}




function lh_password_changer_form_output($atts) {

    // define attributes and their defaults
    extract( shortcode_atts( array (
        'submit' => 'submit',
        'redirect' => false
    ), $atts ) );


$content = '';

if ( $this->can_user_edit() ) {

$userobject = $this->get_referenced_user();


if (isset($GLOBALS['lh_password_changer-form-result'])){


$content .= $this->handle_result($GLOBALS['lh_password_changer-form-result']);





} else {

$current_user = wp_get_current_user();

if ($current_user->ID != $userobject->ID){

$content .= '<p>You are changing the password for '.$userobject->user_login.'</p>';

}

global $post;

include ('partials/lh_password_changer_form_output.php');

// include the lh-password-changer-js library
$this->load_file( $this->namespace.'-script', '/scripts/lh-password-changer.js', true, array(), true, array('defer="defer"'));


}


} else {

$content .= __('Sorry you are not logged in or cannot edit this user. ', $this->namespace);

$content .= '<a href="'.wp_login_url( self::curpageurl() ).'">'; 

$content .= __('Please login', $this->namespace);

$content .= '</a>'; 

}


return $content;

}


function register_shortcodes(){

add_shortcode('lh_password_changer_form', array($this,"lh_password_changer_form_output"));

}

public function plugin_menu() {
add_options_page('LH Password Changer Options', 'Password Changer', 'manage_options', $this->filename, array($this,"plugin_options")); 
}


function plugin_options() {

if (!current_user_can('manage_options')){

wp_die( __('You do not have sufficient permissions to access this page.', $this->namespace) );

}


if( isset($_POST[ $this->hidden_field_name ]) && $_POST[ $this->hidden_field_name ] == 'Y' ) {

if ($_POST[ $this->page_id_field ] != ""){
$options[ $this->page_id_field ] = $_POST[ $this->page_id_field ];
}



if (update_option( $this->opt_name, $options )){


$this->options = get_option($this->opt_name);


?>
<div class="updated"><p><strong><?php _e('Values saved', $this->namespace ); ?></strong></p></div>
<?php


} 



} 



include ('partials/option-settings.php');


}



public function return_user_link( $id ){

if (get_permalink($this->options[$this->page_id_field_name])){

return get_permalink($this->options[$this->page_id_field_name])."?user_id=".$id;

} else {

return false;

}

}



function modify_lh_profile_page_form( $html ) {

if ($permalink = get_permalink($this->options[ $this->page_id_field ])){

if ($_GET['user_id']){

$permalink = add_query_arg( 'user_id', $_GET['user_id'], $permalink );

}

return $html ."<br/><a href=\"".$permalink."\">Change Password</a>";

} else {

 return $html;

}
}

public function on_activate( $network){

global $wpdb;

  if ( is_multisite() && $network ) {
        // store the current blog id
        $current_blog = $wpdb->blogid;
        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {

        }

    } else {

$this->create_page();

}

}

// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}

public function the_content_filter( $content ) {

global $post;


if (isset($post->post_content) and !has_shortcode( $post->post_content, 'lh_password_changer_form' )  and is_singular() and isset($GLOBALS['lh_password_changer-form-result'])){

$content = $this->handle_result($GLOBALS['lh_password_changer-form-result']).$content;


}

return $content;


}


public function plugins_loaded(){


load_plugin_textdomain( $this->namespace, false, basename( dirname( __FILE__ ) ) . '/languages' ); 

}




public function __construct() {

$this->options = get_option($this->opt_name);
$this->filename = plugin_basename( __FILE__ );

add_action( 'init', array($this,"register_shortcodes"));
add_action( 'wp', array($this,"process_password_change"));
add_action('admin_menu', array($this,"plugin_menu"));
add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);

//Filters the lh profile page output if enabled
add_filter( 'lh_profile_page_form_html', array($this,"modify_lh_profile_page_form"));


//add message to redirected request
add_filter( 'the_content', array($this,"the_content_filter"),100);


//run whatever on plugins loaded (currently just translations)
add_action( 'plugins_loaded', array($this,"plugins_loaded"));


}


}

$lh_password_changer_instance = new LH_password_changer_plugin();
register_activation_hook(__FILE__, array($lh_password_changer_instance,'on_activate'), 10, 1);

}

?>