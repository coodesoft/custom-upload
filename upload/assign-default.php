<?php

function cu_assign_default_DEFFERED(){
  if (isset($_POST['url'])){
    $url = $_POST['url'];
    $uploadStatus = Files::assignDefault($url);
    //$uploadStatus = new Files();
  } else
    $uploadStatus = false;

  $url ='admin.php?page=global_custom_upload&tab=uploadFiles&assign_default_status='. $uploadStatus ;
  wp_redirect($url);
  exit;
}

add_action( 'admin_post_assign_default', 'cu_assign_default' );
