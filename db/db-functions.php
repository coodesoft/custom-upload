<?php

  function delete_access($params){
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "DELETE FROM wp_cu_access (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  function delete_access_by_user($id){
    $table = "wp_cu_access";
    return $wpdb->delete($table, ['user_id' => $id], ['%d']);
  }

  function add_access($params){
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "INSERT INTO wp_cu_access (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }


?>
