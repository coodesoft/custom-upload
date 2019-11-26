<?php

class Files{

  const TABLE = 'wd_cu_files';
  const TABLEACCESS = 'wd_cu_access';
  const TABLEDEFAULT = 'wd_cu_default_files';

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
    $resultAccess = $wpdb->delete( self::TABLEACCESS, ['file_id' => $file_id], ['%d'] );
    $resultDefault = $wpdb->delete( self::TABLEDEFAULT, ['file_id' => $file_id], ['%d'] );

    if ($result !== false && $resultUnlink !== false && $resultAccess !== false && $resultDefault !== false){
      $wpdb->query('COMMIT');
      return true;
    } else {
      $wpdb->query('ROLLBACK');
      return false;
    }
  }

  static function assignDefault($path){
    global $wpdb;
    $default_table = "wd_cu_default_files";
    $files_table = "wd_cu_files";
    $access_table = "wd_cu_access";

    $queryFiles = "SELECT * FROM " . $files_table . " WHERE file_dir = '". $path ."'";
    $files = $wpdb->get_row($queryFiles, ARRAY_A);

    $file_id = $defaultFile['file_id'];
    $queryDefault = "SELECT * FROM " . $default_table . " WHERE file_id = '". $file_id ."'";
    $existDefault = $wpdb->get_row($queryDefault, ARRAY_A);

    var_dump($files);
    var_dump($default);
    throw new Exception (json_encode($files), 1);
    /*
    foreach ($files as $value) {
        $defaultFiles['default_id'] = 0;
        $defaultFiles['file_id'] = $value['file_id'];
        $defaultFiles['file_dir'] = $value['file_dir'];
        $defaultFiles['file_type'] = $value['file_type'];
    }*/

    // acá chequeo si ya existe el archivo en la tabla cu_default_files. (consultar si existe file_id)
    if (!empty($existDefault)) {
      $result = $wpdb->insert($default_table, $defaultFiles);  
    }
    

    //var_dump($result);
    //throw new Exception (json_encode($result), 1);

    if(!empty($result)){
      $queryClients = "SELECT * FROM wd_gs_clientes";
      $clients = $wpdb->get_results($queryClients, ARRAY_A);

      $values = array();
      foreach ( $clients as $client ){
        $values[] = $wpdb->prepare( "(%d,%d)", $file_id, $client['client_id'] );
      }

      $query = "INSERT INTO " .$access_table. " (file_id, user_id) VALUES ";
      $query.= implode( ",\n", $values );

      $res= $wpdb->query($query);
      //throw new Exception (json_encode($values), 1);
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
