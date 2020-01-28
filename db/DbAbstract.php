<?php
abstract class DbAbstract {

  public static function getPrefix() {
    global $wpdb;
    return $wpdb->prefix;
  }

  public static function getTableName(){}
    
  public static function getTable($name){
    $tables = static::getTableName();
    $result = static::getPrefix();
    foreach ($tables as $key => $value) {
      if ($key == $name)
        $result.= $tables[$value];
    }
    return $result;
  }
}
?>