<?php
abstract class DbAbstract {

  public static function getPrefix() {
    global $wpdb;
    return $wpdb->prefix;
  }

  public static function getTableName(){}
    
  public static function getTable($name){
    $tables = static::getTableName();
    if ( isset($tables[$name]) && strlen($tables[$name])>0 ){
      return self::getPrefix() . $tables[$name];
    } else
      throw new Exception("No se puede obtener el nombre de la tabla asociada a: ". $name, 1);
      

  }
 
  static function transaction(){
    global $wpdb;
    $wpdb->query("START TRANSACTION");
  }

  static function commit(){
    global $wpdb;
    $wpdb->query("COMMIT");
  }

  static function rollBack(){
    global $wpdb;
    $wpdb->query("ROLLBACK");
  }  

}
?>