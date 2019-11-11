<?php

  function delete_access($params){
    $values = array();
    global $wpdb;
    $prefix = $wpdb->prefix;

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "DELETE FROM ".$prefix."cu_access (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  function delete_access_by_user($id){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $table = $prefix."cu_access";
    return $wpdb->delete($table, ['user_id' => $id], ['%d']);
  }

  function add_access($params){
    global $wpdb;
    $prefix = $wpdb->prefix;
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "INSERT INTO ".$prefix."cu_access (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }


?>
