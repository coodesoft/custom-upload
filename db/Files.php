<?php

class Files{

  const TABLE = 'wd_cu_files';

  static function add($params){
    global $wpdb;
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%s,%s, %d)", 'DEFAULT', $value['file_dir'], $value['file_type'] );

    $query = "INSERT INTO " .Files::TABLE. " (file_id, file_dir, file_type) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  static function delete($path){
    global $wpdb;
    $query = "SELECT file_id FROM " . self::TABLE . " WHERE file_dir = '". $path ."'";
    $file_id = $wpdb->get_var($query);

    $wpdb->query('START TRANSACTION');
    $resultUnlink = unlink($path);
    $result = $wpdb->delete( self::TABLE, ['file_id' => $file_id], ['%d'] );

    if ($result !== false && $resultUnlink !== false){
      $wpdb->query('COMMIT');
      return true;
    } else {
      $wpdb->query('ROLLBACK');
      return false;
    }
  }

  static function assignDefault($path){
    global $wpdb;
    $default_table = "wp_cu_default_files";
    $files = "wp_cu_files";
    $access = "wp_cu_access";

    $query = "SELECT * FROM " . $files . " WHERE file_dir = '". $path ."'";
    $defaultFile = $wpdb->get_row($query, ARRAY_A);

    $result = $wpdb->insert($default_table, $defaultFile);
    
    if($result !== false){
      $queryClients = "SELECT * FROM wp_cu_clientes";
      $clients = $wpdb->get_results($queryClients, ARRAY_A);
      $file_id = $defaultFile['file_id'];

      $values = array();
      foreach ( $clients as $client ){
        $values[] = $wpdb->prepare( "(%d,%d)", $file_id, $client['cliente_id'] );
      }

      $query = "INSERT INTO " .$access. " (file_id, user_id) VALUES ";
      $query.= implode( ",\n", $values );

      $res= $wpdb->query($query);
      //throw new Exception (json_encode($clients), 1);
    }

    return $result;
  }

  static function getTypes(){
    return [
      ['label' => 'JPG',  'id' => 0 ],
      ['label' => '1X1',  'id' => 1 ],
      ['label' => 'PDF',  'id' => 2 ],
      ['label' => 'Precios', 'id' => 3 ],
      ['label' => 'Pedidos', 'id' => 4 ],
      ['label' => 'Video',  'id' => 5 ]
    ];
  }

}
