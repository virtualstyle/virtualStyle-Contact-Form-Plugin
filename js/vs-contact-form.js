var recaptchas = [];

var CaptchaLoad = function() {

  jQuery( '.g-recaptcha' ).each(function( n ){
    
    recaptchas.push( grecaptcha.render( jQuery(this).prop('id'), {'sitekey' : jQuery('#sitekey').val(), 'callback' : CaptchaResponse} ) );
    
  } );  

};

var CaptchaResponse = function( response ) {

  jQuery( '#captcha-response' ).val( response );
  
};

jQuery( document ).ready(function() {

  jQuery( '#vs-contact-form form' ).on( 'submit', function( e ) {
      
    e.preventDefault();
  
    jQuery( '.contact-form-submit' ).fadeOut( 100 );
    jQuery( '.contact-form-load' ).fadeIn( 100 );
  
    var formel = jQuery( this ).serializeArray();
    
    var formdata = {};
    jQuery( formel ).each( function( index, obj ) {
      formdata[ obj.name ] = obj.value;
    });
    
    var data = {
      action: 'vs_contact_form_captcha',
      'g-recaptcha-response': jQuery( '#captcha-response' ).val()
    };
    
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post( ajaxurl, data, function( response ) {
      if( response == 'successful captcha' ) {
      
        var data = {
          action: 'vs_contact_form_send',
          'form-data': formdata
        };          

        jQuery.post( ajaxurl, data, function( response ) {
          if( response == 'successful send' ) {               
            alertify.success( 'Your message was sent to Tampa-Legal. We will be in touch as soon as possible.' );                
          } else {
            alertify.error( 'Failed to send message. Please try resubmitting the form or contact customer service through virtualstyle.us' ); 
          }       
        
        });    
        
      } else {
          alertify.error( 'Captcha failed. Please try resubmitting the form or contact customer service through virtualstyle.us' );
      }

      jQuery.each( recaptchas, function( k, v ) {
        
        grecaptcha.reset( v );
        
      });  
      
      jQuery( '.contact-form-load' ).fadeOut( 100 );
      jQuery( '.contact-form-submit' ).fadeIn(100 );
      
    } );
    
  } );

} );