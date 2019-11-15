<?php


function cu_delete_files(){
  if (isset($_POST['url'])){
    $url = $_POST['url'];
    $uploadStatus = Files::delete($url);
    //$uploadStatus = true;
  } else $uploadStatus = false;

  $url ='admin.php?page=global_custom_upload&tab=uploadFiles&delete_status='. $uploadStatus ;
  wp_redirect($url);
  exit;
}

add_action( 'admin_post_delete_files', 'cu_delete_files' );
