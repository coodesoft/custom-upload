<?php

class Files{
  
  const TABLE = 'wp_cu_files';
  private $prefix;

  /*public function __construct(){
    global $wpdb;
    $this->prefix = $wpdb->prefix;
  }*/

  static function add($params){
    global $wpdb;
    $values = array();

    foreach ( $params as $key => $value )
      $values[] = $wpdb->prepare( "(%s,%s, %d)", 'DEFAULT', $value['file_dir'], $value['file_type'] );

    $query = "INSERT INTO " .Files::TABLE. " (file_id, file_dir, file_type) VALUES ";
    $query .= implode( ",\n", $values );

    return $wpdb->query($query);
  }

  static function getIdByFile($file){
    global $wpdb;
    $query = "SELECT * FROM"
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
