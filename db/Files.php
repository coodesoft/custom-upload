<?php
require_once(__DIR__."/DbAbstract.php");

class Files extends DbAbstract{

  public static function getTableName(){
    return [
      "files" => "cu_files"
    ];
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

  /* TODO refactorizar. Si por alguna razon falla la consulta pero no el borrado
   * físico se produce una inconsistencia entre la db y la información en disco.
   */
  static function delete($id, $path){
    
    if ($id){
      global $wpdb;
      $files_table = self::getTable("files");
      
      
      $result = $wpdb->delete( $files_table, ['file_id' => $id], ['%d'] );
      
      if ($result === false)
        return ['status' => Flags::DB_DELETE_ERROR, 'id' => null];
      elseif ($result > 0){
        
        if (unlink($path))
          return ['status' => Flags::DB_DELETE_SUCCESS, 'id' => $id];
        else
           return ['status' => Flags::DB_DELETE_ERROR, 'id' => $id];
      }
      else
        return ['status' => Flags::DB_DELETE_NO_ROWS, 'id' => $id];

    } else
      throw new Exception('Files::delete - parámetro inválido', 1);
  }

  static function getByPath($path){
    global $wpdb;
    $table_name = self::getTable("files");

    $query = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE file_dir=%s", $path);
    return $wpdb->get_row($query, ARRAY_A);
  }

  static function setAsDefault($path){
    if ( isset($path) && is_string($path) ){
        global $wpdb;
        $table_name = self::getTable("files");

        $result = $wpdb->update( $table_name, ['is_default' => 1], ['file_dir' => $path], ['%d'], ['%s'] );

        if ($result === false)
          return ['status' => Flags::DB_UPDATE_ERROR, 'id' => null];
        else{
          $updated = self::getByPath($path);
          return ($result > 0) ?  ['status' => Flags::DB_SAVE_SUCCESS, 'file_id' => $updated['file_id']] :
                                  ['status' => Flags::DB_UPDATE_NO_ROWS, 'file_id' => $updated['file_id']];
        }
    } else
      throw new Exception('Files::setAsDefault - parámetro inválido', 1);
  }

  static function removeDefaultFlag($IDs){
    if (!empty($IDs)){
      global $wpdb;
      $table_name = self::getTable("files");

      $query = "UPDATE " .$table_name. " SET is_default=0 WHERE file_id IN ($IDs)";
      return $wpdb->query($query);

    } else
      throw new Exception('Files::removeDefaultFlag - parámetro inválido', 1);
  }

}
