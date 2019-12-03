<?php

class History{
  
  static function getAllByUser($id){
    global $wpdb;
    $history_table = $wpdb->prefix . "cu_history";
    $files_table = $wpdb->prefix . "cu_files";
    $queryStr = "SELECT " . $history_table . ".*, " . $files_table . ".file_dir FROM " . $history_table;
    $queryStr.= " LEFT JOIN " . $files_table . " ON " . $files_table . ".file_id=" . $history_table . ".file_id WHERE " . $history_table . ".user_id=%d";
    $query = $wpdb->prepare($queryStr, array($id));
    return $wpdb->get_results($query, ARRAY_A);
  }

  static function add($params){
    global $wpdb;
    $history_table = $wpdb->prefix . "cu_history";
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d,%d,%s)", $value['id'], $value['user_id'], $value['file_id'], $value['date'] );

    $query = "INSERT INTO " . $history_table . " (id, user_id, file_id, date) VALUES ";
    $query.= implode( ",\n", $values );

    return $wpdb->query($query);
  }
}
