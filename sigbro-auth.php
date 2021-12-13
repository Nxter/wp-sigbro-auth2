<?php

/*
Plugin Name: Sigbro Auth 2.0
Plugin URI: https://www.nxter.org/sigbro
Version: 0.2.0
Author: scor2k
Description: Use Sigbro Mobile app to log in to the site
License: MIT
License URI: https://opensource.org/licenses/MIT
 */

session_start([
  'cookie_lifetime' => 600,
]);

require_once 'utils.php';
require_once 'phpqrcode.php';

defined('ABSPATH') or die('No script kiddies please!');

function sigbro_auth_shortcode($attr)
{

  $this_is_new_qrcode = false;

  if  ( !isset($_SESSION["sigbro_auth_uuid"]) ) {
    $_SESSION["sigbro_auth_uuid"] = sigbro_generate_uuid();
    $this_is_new_qrcode = true;
  }

  if ( $this_is_new_qrcode ) {
      $sigbro_url = "https://random.api.nxter.org/api/auth/new";
      $params = array('uuid' => $_SESSION["sigbro_auth_uuid"]);

      // wait max 6 second (1 retry on the server side)
      $result = sigbro_send_post_json($sigbro_url, $params, 6);

      if ( $result['result'] == 'fail' ) {
        $msg = sprintf("<p style='color: red; text-align:center;'>%s</p>", $result["msg"]);
        return $msg;
      }
  }

  $args = shortcode_atts( array(
    'redirect' => '/sigbro'
  ), $attr );

  // generate URL for the Sigbro App
  $url = sprintf("https://dl.sigbro.com/auth/%s/", $_SESSION["sigbro_auth_uuid"]);

  // generate QR code
  ob_start();
  QRcode::png($url, null, QR_ECLEVEL_H, 10, 1);
  $qrcode = ob_get_contents();
  ob_end_clean();

  // prepare base64 image
  $png = sprintf("<p style='text-align: center;'><img src='data:image/png;base64,%s'/></p>", base64_encode($qrcode));

  $redirect_url = $args['redirect'];

  $js = '<script type="text/javascript">
    var retry_counter = 300;
    var uuid = "' . $_SESSION["sigbro_auth_uuid"] . '"; 
    var redirect_url = "' . $redirect_url . '";
    console.log("Your ID: ", uuid); 
    console.log("Redirect URL: ", redirect_url);
      
    retry = setInterval( function() {
      ask(uuid);
      retry_counter--;
      if (retry_counter <= 0) { clearInterval(retry); }
    }, 2000 + Math.floor(Math.random() * 2000) );

    function setCookie(name,value,days) {
      var expires = "";
      if (days) {
          var date = new Date();
          date.setTime(date.getTime() + (days*24*60*60*1000));
          expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function ask(uuid) {
      var url = "https://random.api.nxter.org/api/auth/status";
      var xhr = new XMLHttpRequest();
      xhr.open("POST", url, true);
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var result = JSON.parse(xhr.responseText);

            if ( result.result == "ok" ) {
              clearInterval(retry); 
              console.log(result);
              // save cookie for a month
              setCookie("sigbro_auth_account", result.accountRS, 30);
              window.location.replace(redirect_url);
            }
        }
      };
      var data = JSON.stringify({"uuid": uuid});
      xhr.send(data);
    }
  
  </script>';

  $msg = sprintf("%s\n%s", $png, $js);

  return $msg;
}

function sigbro_set_property($attr) {
  $account = $_COOKIE["sigbro_auth_account"];

  if ( $account == "" ) {
    header('Location: /');
    wp_die();
  }

  $args = shortcode_atts( array(
    'setter' => '',
    'setter_publickey' => '',
    'property' => 'sigbro',
    'value' => 'silver',
    'token' => ''
  ), $attr );

  $_setter = $args['setter'];
  $_setter_publickey = $args['setter_publickey'];
  $_property = $args['property'];
  $_value = $args['value'];
  $_token = $args['token'];

  $_validate = sigbro_validate_account_property($account, $_property, $_setter, $_value);

  if ( $_validate == true ) {
    $msg = sprintf("<p style='text-align: center;'>Welcome, %s<br>Your account is valid!</p>", $account);
  } else {
    $prop_res = sigbro_set_account_property($account, $_property, $_setter_publickey, $_value, $_token);
    if ( $prop_res == true ) {
      $msg = sprintf("<p style='text-align: center;'>Welcome, %s<br>Your account is going to be valid soon!</p>", $account);
    } else {
      $msg = sprintf("<p style='text-align: center;'>Welcome, %s<br>Your need a Sigbro Blessing!</p>", $account);
    }
  }
 
  return $msg;
}

add_shortcode('sigbro-auth', 'sigbro_auth_shortcode');


// [sigbro-property setter="ARDOR-H2W5-VZAB-9XFZ-38885" property="sigbro" value="silver" token="qwerty123"]
add_shortcode('sigbro-property', 'sigbro_set_property');

?>