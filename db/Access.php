<?php
require_once(__DIR__."/DbAbstract.php");

class Access extends DbAbstract{

  public static function getTableName(){
    return [
      "access" => "cu_access"
    ];
  }
  
  static function deleteByIDs($IDs){
    $access_table = static::getTable("access");
    $values = array();
    
    $query = "DELETE FROM " .$access_table. " WHERE access_id IN ($IDs)";
    return $wpdb->query($query);
  }

  static function deleteByUser($id){
    $access_table = static::getTable("access");
    return $wpdb->delete($access_table, ['user_id' => $id], ['%d']);
  }

  static function add($params){
    $access_table = static::getTable("access");
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "INSERT INTO " .$access_table. " (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }
}
