<?php


add_action( 'wp_ajax_delete_files', 'cu_delete_files' );
function cu_delete_files(){
  parse_str($_POST['data']);

  if ( isset($url) ){
    $path = $url;

    Files::transaction();

    try {
      $file = Files::getByPath($path);
      $accessDeleteResult = Access::deleteByFile($file['file_id']);

      if ($accessDeleteResult['status'] != Flags::DB_DELETE_ERROR){
          
        $fileDeleteResult = Files::delete($file['file_id'], $path);
        
        if ($fileDeleteResult['status'] == Flags::DB_DELETE_SUCCESS){
          $result = [ 'status' => Flags::DB_DELETE_SUCCESS, 'msg' => 'El archivo se eliminó correctamente junto a los permisos asociados'];
          Files::commit();
        } elseif ($fileDeleteResult['status'] == Flags::DB_DELETE_NO_ROWS){
            $result = ['status' => Flags::DB_DELETE_NO_ROWS, 'msg' => 'La operación se completo sin errores pero no se eliminó el archivo. Consulte al administrador del sitio'];
            Files::rollBack();
        } else{
            $result = ['status' => Flags::DB_DELETE_ERROR, 'msg' => 'Se produjo un error al eliminar el archivo y/o sus permisos'];
            Files::rollBack();
        }

      } else{
        $result = ['status' => Flags::DB_DELETE_ERROR, 'msg' => 'Se produjo un error al eliminar los permisos del archivo'];
        Files::rollBack();
      }

    } catch (Exception $e) {
      $result = ['status' => Flags::DB_DELETE_ERROR, 'msg' => 'Se produjo una excepción al eliminar el archivo y/o sus permisos. Excepción: '. $e->getMessage()];
      Files::rollBack();

    }
    echo json_encode($result);
    wp_die();
  }
}
