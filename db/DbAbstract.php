<?php
abstract class DbAbstract {

  public static function getPrefix() {
    global $wpdb;
    return $wpdb->prefix;
  }

  public static function getTableName(){}
    
  public static function getTable($name){
    $tables = self::getTableName();
    $result = self::getPrefix();
    foreach ($tables as $key => $value) {
      if ($key == $name)
        $result.= $tables[$name];
    }
    return $result;
  }
}
?>