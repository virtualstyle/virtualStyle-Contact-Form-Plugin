<?php
/*
Plugin Name: virtualStyle Contact Form Plugin
Plugin URI: http://virtualstyle.us/vs-contact-form.zip
Description: Simple non-bloated WordPress contact form with multiple instance Recaptcha handling
Version: 1.0
Author: Rob Wood
Author URI: http://virtualstyle.us
*/

$instance_count = 0;

add_shortcode( 'vs-contact-form', 'cf_shortcode' );

add_filter('clean_url', 'clean_g_recaptcha_url', 99, 3);

add_action( 'wp_enqueue_scripts', 'vs_contact_form_files' );

add_action( 'wp_ajax_vs_contact_form_captcha', 'prefix_ajax_vs_contact_form_captcha' );
add_action( 'wp_ajax_nopriv_vs_contact_form_captcha', 'prefix_ajax_vs_contact_form_captcha' );

add_action( 'wp_ajax_vs_contact_form_send', 'prefix_ajax_vs_contact_form_send' );
add_action( 'wp_ajax_nopriv_vs_contact_form_send', 'prefix_ajax_vs_contact_form_send' );
    
function html_form_code() {

  global $instance_count;
  
  ?>

  <script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
  </script>

  <div id="vs-contact-form">
    <form class="pure-form pure-form-stacked" action="<?=esc_url( $_SERVER['REQUEST_URI'] );?>" method="post">
      <input type="hidden" id="sitekey" value="<?=esc_attr( get_option('vs_recaptcha_sitekey') );?>">
      <input type="hidden" id="captcha-response" value="">
      <fieldset>
        <legend>Get Help Now - Contact Us</legend>

        <div class="pure-g">
          <div class="pure-u-1 pure-u-md-1-3">
            <label for="cf-name">Full Name</label>
            <input id="cf-name" name="cf-name" class="pure-u-23-24" type="text" pattern="[a-zA-Z0-9 ]+" value="<?=( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' );?>" required>
          </div>

          <div class="pure-u-1 pure-u-md-1-3">
            <label for="cf-email">Email</label>
            <input id="cf-email" name="cf-email" class="pure-u-23-24" type="email" value="<?=( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' );?>" required>
          </div>

          <div class="pure-u-1 pure-u-md-1-3">
            <label for="cf-subject">Subject</label>
            <input id="cf-subject" name="cf-subject" class="pure-u-23-24" type="text" pattern="[a-zA-Z0-9 ]+" value="<?=( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' );?>" required>
          </div>

          <div class="pure-u-1 pure-u-md-1-3">
            <label for="cf-message">Message</label>
            <textarea style="height:120px;" id="cf-message" name="cf-message" class="pure-u-23-24" required><?=( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' );?></textarea>
          </div>
          
          <div class="vs-recaptcha pure-u-1 pure-u-md-1-3">
          
            <div id="g-recaptcha<?=$instance_count;?>" class="g-recaptcha" data-sitekey="<?=esc_attr( get_option('vs_recaptcha_sitekey') );?>"></div>
          
          </div>

        </div>
        <div class="pure-controls">
          <button type="submit" class="contact-form-submit pure-button pure-button-primary">Get Help Now</button>
          <img class="contact-form-load" src="http://tampa-legal.com/wp-content/uploads/2016/09/helix_loader.gif">
        </div>
      </fieldset>
    </form>
  </div>

  <?php
    
  $instance_count++;
}    

function deliver_mail() {

  // if the submit button is clicked, send the email
  if ( isset( $_POST['form-data'] ) ) {

    // sanitize form values
    $name    = sanitize_text_field( $_POST['form-data']['cf-name'] );
    $email   = sanitize_email( $_POST['form-data']['cf-email'] );
    $subject = sanitize_text_field( $_POST['form-data']['cf-subject'] ) . ' (TAMPA-LEGAL.COM CONTACT FORM)';
    $message = esc_textarea( $_POST['form-data']['cf-message'] );

    // get the blog administrator's email address
    $to = get_option( 'admin_email' );

    $headers = "From: $name <$email>" . "\r\n";

    // If email has been process for sending, display a success message
    if ( wp_mail( $to, $subject, $message, $headers ) ) {
      echo 'successful send';
    } else {
      echo 'failed send';
    }
    
  }
  
}

function cf_shortcode() {
  ob_start();
  html_form_code();
  return ob_get_clean();
}

if ( is_admin() ){ // admin actions
  add_action( 'admin_menu', 'vs_contact_form_menu' );
  add_action( 'admin_init', 'register_vs_contact_form_settings' );
} else {
  // non-admin enqueues, actions, and filters
}

function register_vs_contact_form_settings() { // whitelist options
  register_setting( 'vs-contact-form-group', 'vs_recaptcha_sitekey' );
  register_setting( 'vs-contact-form-group', 'vs_recaptcha_secret' );
  register_setting( 'vs-contact-form-group', 'vs_recaptcha_hostname' );
  register_setting( 'vs-contact-form-group', 'vs_recaptcha_time_variance' );
}

function vs_contact_form() {

?>

<div class="wrap">
  <h1>virtualStyle Contact Form Options</h1>
  <form method="post" action="options.php"> 
  
    <?php
    
    settings_fields( 'vs-contact-form-group' );
    do_settings_sections( 'vs-contact-form-group' );
    
    ?>
    
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Recaptcha Sitekey</th>
        <td><input type="text" style="width:350px !important;" name="vs_recaptcha_sitekey" value="<?php echo esc_attr( get_option('vs_recaptcha_sitekey') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">Recaptcha Secret</th>
        <td><input type="text" style="width:350px !important;" name="vs_recaptcha_secret" value="<?php echo esc_attr( get_option('vs_recaptcha_secret') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">Recaptcha Host Domain (leave blank to accept any domain)</th>
        <td><input type="text" style="width:350px !important;" name="vs_recaptcha_hostname" value="<?php echo esc_attr( get_option('vs_recaptcha_hostname') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">Recaptcha Time Variance (leave blank to accept any time difference)</th>
        <td><input type="text" style="width:350px !important;" name="vs_recaptcha_time_variance" value="<?php echo esc_attr( get_option('vs_recaptcha_time_variance') ); ?>" /></td>
      </tr>
    </table>
    
    <?php
    
    submit_button();
    
    ?>
  </form>
</div>

<?php

}

function vs_contact_form_menu() {
	add_options_page( 
		'virtualStyle Contact Form Plugin',
		'virtualStyle Contact Form Options',
		'manage_options',
		'vs-contact-form-options',
		'vs_contact_form'
	);
}

function clean_g_recaptcha_url($url, $original_url, $_context) {
  if ( strstr( $url, "https://www.google.com/recaptcha/api.js" ) !== false ) {
      $url = str_replace( "&#038;", "&", $url ); // or $url = $original_url
  }

  return $url;
}

/*
function add_async_attribute($tag, $handle, $src) {
  if ( 'g-recaptcha' !== $handle ) {
    return $tag;
  }
  return str_replace( ' src', ' async defer src', $tag );
}
*/

function vs_contact_form_files()
{
  // Register the script like this for a plugin:
  wp_register_script( 'vs-contact-form-script', plugins_url( '/js/vs-contact-form.js', __FILE__ ), array( 'jquery' ), null, true );
  wp_register_script( 'alertify', 'https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.8.0/alertify.min.js', array( 'jquery' ), null, true );
  wp_register_script( 'g-recaptcha', apply_filters( 'clean_url', 'https://www.google.com/recaptcha/api.js?onload=CaptchaLoad&render=explicit' ), array( 'alertify', 'vs-contact-form-script' ), null, true );

  // For either a plugin or a theme, you can then enqueue the script:
  wp_enqueue_script( 'vs-contact-form-script' );
  wp_enqueue_script( 'alertify' );
  wp_enqueue_script( 'g-recaptcha' );
  
  wp_register_style( 'pure', 'http://yui.yahooapis.com/pure/0.6.0/pure-min.css', array(), '20160928', 'all' );
  wp_register_style( 'vs-contact-form-styles', plugins_url( '/css/vs-contact-form.css', __FILE__ ), array(), '20160928', 'all' );
  wp_register_style( 'alertify-core-styles', 'https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.8.0/css/alertify.min.css', array(), '20160928', 'all' );
  wp_register_style( 'alertify-bootstrap-styles', 'https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.8.0/css/themes/bootstrap.min.css', array(), '20160928', 'all' );
  wp_enqueue_style( 'pure' );
  wp_enqueue_style( 'vs-contact-form-styles' );
  wp_enqueue_style( 'alertify-core-styles' );
  wp_enqueue_style( 'alertify-bootstrap-styles' );
}

function prefix_ajax_vs_contact_form_send() {

  deliver_mail();
  wp_die();

}

function prefix_ajax_vs_contact_form_captcha() {
  
	$response = $_POST['g-recaptcha-response'];
  
  if( $response == '' ) {
    echo 'failed captcha';
    wp_die();
  }
  
  
  $data = array(
    'secret' => get_option( 'vs_recaptcha_secret' ),
    'response' => $response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
  );

  $verify = curl_init();
  curl_setopt( $verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify" );
  curl_setopt( $verify, CURLOPT_POST, true );
  curl_setopt( $verify, CURLOPT_POSTFIELDS, http_build_query( $data ) );
  curl_setopt( $verify, CURLOPT_SSL_VERIFYPEER, false );
  curl_setopt( $verify, CURLOPT_RETURNTRANSFER, true );
  $curl_response = json_decode( curl_exec( $verify ), true );
    
  $now = strtotime('now');
  $timestamp = strtotime( $curl_response['challenge_ts'] );

  $timediff = $now - $timestamp;
    
  if( $curl_response['success'] == 1 && 
    ( get_option( 'vs_recaptcha_hostname' ) == '' || $curl_response['hostname'] == get_option( 'vs_recaptcha_hostname' ) ) && 
    ( get_option( 'vs_recaptcha_time_variance' ) == '' || abs($timediff) <= intval( get_option( 'vs_recaptcha_time_variance' ) ) ) ) {
  
    echo 'successful captcha';
  
  } else {
  
    echo 'failed captcha';
  
  }
  
  wp_die();
  
}
?>