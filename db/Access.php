<?php
require_once(__DIR__."/DbAbstract.php");

class Access extends DbAbstract{

  public static function getTableName(){
    return [
      "access" => "cu_access"
    ];
  }

  static function deleteByIDs($IDs){
    if ($IDs){
      global $wpdb;
      $access_table = static::getTable("access");
      $values = array();

      $query = "DELETE FROM " .$access_table. " WHERE access_id IN ($IDs)";

      return $wpdb->query($query);
    } else
      throw new Exception('Access::deleteByIDs - parámetro inválido', 1);
  }

  static function deleteByFile($id){
    
    if ($id){
      global $wpdb;
      $access_table = static::getTable('access');
      $result = $wpdb->delete( $access_table, ['file_id' => $id], ['%d'] );
            
      if ($result === false)
        return ['status' => Flags::DB_DELETE_ERROR, 'id' => null];
      elseif ($result > 0)
        return ['status' => Flags::DB_DELETE_SUCCESS, 'id' => $id];
      else
        return ['status' => Flags::DB_DELETE_NO_ROWS, 'id' => $id];

    } else
      throw new Exception('Access::delteByFile - parámetro inválido', 1);
  }


  //TODO - validar el parámetro
  static function deleteByUser($id){
    global $wpdb;
    $access_table = self::getTable("access");
    return $wpdb->delete($access_table, ['user_id' => $id], ['%d']);
  }

  //TODO - validar el parámetro
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

  static function getAll(){
    global $wpdb;
    $access_table = self::getTable("access");
    return $wpdb->get_results('SELECT * FROM '. $access_table, ARRAY_A);
  }
}
