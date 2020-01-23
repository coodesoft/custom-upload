correc<?php
require_once(__DIR__."/DbAbstract.php");

class Clients extends DbAbstract{

  public static function getTableName(){
    return [
      "clientes" => "cu_clientes",
      "sucursales" => "cu_sucursales",
      "clientes_gs" => "gs_clientes" // esta es una tabla del plugin GlobalSaxCore
    ];
  }
    
  static function getSpecialKeys(){
      return ['direccion_publica', 'sitio_web', 'telefono'];
  }
    
  static function add($name){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    return $wpdb->insert($clientes_table, array('nombre_cliente' => $name), array('%s') );
  }

  static function update($id, $name){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    return $wpdb->update($clientes_table, ['nombre_cliente' => $name], ['cliente_id' => $id], ['%s'], ['%d']);
  }

  static function delete($id){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $sucursales_table = self::getTable("sucursales");
    $wpdb->query('START TRANSACTION');
    $result = $wpdb->delete( $clientes_table, ['cliente_id' => $id], ['%d'] );
    $related = $wpdb->delete( $sucursales_table, ['cliente_id' => $id], ['%d'] );
    if ($result !== false && $related !== false){
      $wpdb->query('COMMIT');
      return true;
    } else {
      $wpdb->query('ROLLBACK');
      return false;
    }
  }

  static function addSucursal($cliente_id, $sucursal, $provincia, $ciudad){
   global $wpdb;
   $sucursales_table = self::getTable("sucursales");
   $values = array( 'cliente_id' => $cliente_id,
                    'provincia' => $provincia,
                    'ciudad' => $ciudad,
                    'direccion_real' => $sucursal,
                    'direccion_publica' => $sucursal );
   $types = array( '%d', '%s', '%s', '%s', '%s' );
   return $wpdb->insert($sucursales_table, $values, $types);
  }

  static function updateSucursalFeature($params, $cliente_id, $sucursal_id){
    global $wpdb;
    $sucursales_table = self::getTable("sucursales");
    $fields = [];
    $types = [];
    $specialKeys = self::getSpecialKeys();
    foreach ($params as $key => $value) {
      $types[] = ( in_array($key, $specialKeys) ) ? '%s' : '%d';
    }
    return $wpdb->update($sucursales_table, $params, ['id' => $sucursal_id, 'cliente_id' => $cliente_id], $types, ['%d', '%d']);
  }

  static function updateSucursalGeocode($params){
    global $wpdb;
    $sucursales_table = self::getTable("sucursales");
    return $wpdb->update($sucursales_table, ['lat' => $params[1], 'long' => $params[2]], ['id' => $params[0]], ['%s'], ['%s']);
  }

  static function getAll(){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $queryStr = 'SELECT * FROM '. $clientes_table .' ORDER BY cliente_id ASC';
    return $wpdb->get_results($queryStr, ARRAY_A);
  }

  static function getByName($name){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $queryStr = 'SELECT * FROM '. $clientes_table .' WHERE nombre_cliente=%s';
    $query = $wpdb->prepare($queryStr, array($name));
    return $wpdb->get_results($query, ARRAY_A);
  }

  static function getSucursalesByClient($id){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $sucursales_table = self::getTable("sucursales");
    $queryStr = 'SELECT * FROM '. $clientes_table;
    $queryStr.= ' RIGHT JOIN ' . $sucursales_table . ' ON ' . $clientes_table . '.cliente_id=' . $sucursales_table . '.cliente_id';
    $queryStr.= ' WHERE ' . $clientes_table . '.cliente_id=%d';
    $query = $wpdb->prepare($queryStr, array($id));
    return $wpdb->get_results($query, ARRAY_A);
  }

  static function getSucursales(){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $sucursales_table = self::getTable("sucursales");
    $queryStr = 'SELECT * FROM '. $clientes_table;
    $queryStr.= ' RIGHT JOIN '. $sucursales_table .' ON '. $clientes_table .'.cliente_id='. $sucursales_table .'.cliente_id';
    return $wpdb->get_results($queryStr, ARRAY_A);
  }

  static function getGeocodeSucursales($ids){
    global $wpdb;
    $sucursales_table = self::getTable("sucursales");
    $placeholders = array_fill(0, count($ids), '%d');
    $format = implode(', ', $placeholders);
    $queryStr = 'SELECT id, ' .$sucursales_table. '.lat, '.$sucursales_table.'.long, '.$sucursales_table.'.telefono FROM '. $sucursales_table .' WHERE id IN ('.$format.')';
    return $wpdb->get_results($wpdb->prepare($queryStr, $ids), ARRAY_A);
  }

  static function getSucursalesByProvincia($provincia){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $sucursales_table = self::getTable("sucursales");
    $queryStr = 'SELECT * FROM ' .$sucursales_table;
    $queryStr.= ' LEFT JOIN '. $clientes_table .' ON '. $clientes_table .'.cliente_id='. $sucursales_table.'.cliente_id WHERE provincia ="'. $provincia.'"';
    /*$str = $wpdb->esc_like($provincia);
    $str = '%' . $str . '%';*/
    $query = $wpdb->prepare($queryStr, array($provincia));
    return $wpdb->get_results($queryStr, ARRAY_A);
  }

  static function getSucursalesByCategory($category){
    global $wpdb;
    $clientes_table = self::getTable("clientes");
    $sucursales_table = self::getTable("sucursales");
    $queryStr = 'SELECT * FROM ' .$sucursales_table;
    $queryStr.= ' LEFT JOIN '. $clientes_table .' ON '. $clientes_table .'.cliente_id='. $sucursales_table.'.cliente_id WHERE '. $category.' = 1';
    $query = $wpdb->prepare($queryStr, array($category));
    return $wpdb->get_results($queryStr, ARRAY_A);
  }
}