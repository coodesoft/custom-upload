<?php
require_once(__DIR__."/DbAbstract.php");

class Files extends DbAbstract{

  public static function getTableName(){
    return [
      "files" => "cu_files"
    ];
  }

  public static function getTypes(){
    return [
      ['label' => 'JPG',  'id' => 0 ],
      ['label' => '1X1',  'id' => 1 ],
      ['label' => 'PDF',  'id' => 2 ],
      ['label' => 'Precios', 'id' => 3 ],
      ['label' => 'Pedidos', 'id' => 4 ],
      ['label' => 'Video',  'id' => 5 ]
    ];
  }

  static function add($params){
    global $wpdb;
    $files_table = self::getTable("files");
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%s,%s, %d)", 'DEFAULT', $value['file_dir'], $value['file_type'] );

    $query = "INSERT INTO " . $files_table . " (file_id, file_dir, file_type) VALUES ";
    $query.= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  static function delete($path){
    global $wpdb;
    $files_table = self::getTable("files");
    $access_table = Access::getTable("access");
    $default_table = self::getTable("default");
    $query = "SELECT file_id FROM " . $files_table . " WHERE file_dir = '". $path ."'";
    $file_id = $wpdb->get_var($query);

    $wpdb->query('START TRANSACTION');
    $resultUnlink = unlink($path);
    $result = $wpdb->delete( $files_table, ['file_id' => $file_id], ['%d'] );
    $resultAccess = $wpdb->delete( $access_table, ['file_id' => $file_id], ['%d'] );
    $resultDefault = $wpdb->delete( $default_table, ['file_id' => $file_id], ['%d'] );

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
    $files_table = self::getTable("files");
    $access_table = Access::getTable("access");

    /*
    var_dump($default_table);
    throw new Exception(json_encode($default_table), 1);
    */
    $queryFile = "SELECT * FROM " . $files_table . " WHERE file_dir = '". $path ."'";
    $file = $wpdb->get_row($queryFile, ARRAY_A);

    $file_id = $file['file_id'];
    $queryDefault = "SELECT * FROM " . $default_table . " WHERE file_id = '". $file_id ."'";
    $existDefault = $wpdb->get_row($queryDefault, ARRAY_A);

    // acá chequeo si ya existe el archivo en la tabla cu_default_files. si null, empty() devuelve true
    if (empty($existDefault)) {
      $wpdb->query("START TRANSACTION");
      $result = $wpdb->insert($default_table, $file);
      // acá chequeo si inserta el archivo en la tabla cu_default_files.
      if (!empty($result)) {
        $clientes_table = Clients::getTable("clientes_gs"); // esta es una tabla del plugin GlobalSaxCore
        $queryClients = "SELECT * FROM " . $clientes_table;
        $clients = $wpdb->get_results($queryClients, ARRAY_A);

        $values = array();
        foreach ($clients as $client){
          $values[] = $wpdb->prepare( "(%d,%d,%d)", 0, $file_id, $client['id'] );
        }

        $query = "INSERT INTO " .$access_table. " (access_id, file_id, user_id) VALUES ";
        $query.= implode( ",\n", $values );
        $res = $wpdb->query($query);
      }
    }

    if (empty($existDefault) && !empty($result)){
      $wpdb->query("COMMIT");
      return true;
    } else {
      $wpdb->query("ROLLBACK");
      return false;
    }
  }
}
