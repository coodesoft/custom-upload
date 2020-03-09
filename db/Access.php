<?php
require_once(__DIR__."/DbAbstract.php");

class Access extends DbAbstract{

  public static function getTableName(){
    return [
      "access" => "cu_access"
    ];
  }

  static function deleteByIDs($IDs){
    global $wpdb;
    $access_table = static::getTable("access");
    $values = array();

    $query = "DELETE FROM " .$access_table. " WHERE access_id IN ($IDs)";
    return $wpdb->query($query);
  }

  static function deleteByUser($id){
    global $wpdb;
    $access_table = self::getTable("access");
    return $wpdb->delete($access_table, ['user_id' => $id], ['%d']);
  }

  static function add($params){
    global $wpdb;
    $access_table = self::getTable("access");
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%d,%d)", $value['file_id'], $value['user_id'] );

    $query = "INSERT INTO " .$access_table. " (file_id, user_id) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  static function permissionsFilesList($user){
    global $wpdb;
    $access_table = self::getTable("access");
    $files_table = Files::getTable("files");

    $queryStr = "SELECT " . $files_table . ".*, " . $access_table . ".access_id, " . $access_table . ".user_id FROM " . $files_table . " ";
    $queryStr.= "LEFT JOIN " . $access_table . " ON " . $files_table . ".file_id=" . $access_table . ".file_id AND " . $access_table . ".user_id=".$user;

    return $wpdb->get_results($queryStr, OBJECT);
  }

  static function getPermissions($req){
    global $wpdb;
    $access_table = self::getTable("access");
    $query = $wpdb->prepare("SELECT * FROM " . $access_table . " WHERE user_id=%d", $req['user']);
    return $wpdb->get_results($query, ARRAY_A);
  }
}
