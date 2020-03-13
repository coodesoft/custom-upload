<?php
require_once(__DIR__ . '/../db/Access.php');
require_once(__DIR__ . '/../db/Clients.php');
require_once(__DIR__ . '/../db/Access.php');

add_action( 'wp_ajax_load_permission', 'cu_load_files_permision_by_user' );
function cu_load_files_permision_by_user(){
    $params = array();
    parse_str($_POST['user'], $params);
    $user = $params['user'];

    if (!$user){ ?>
      <div class="cu-message"><p>No se ha seleccionado ningun cliente</p></div>
    <?php
    wp_die();
    }
   ?>
    <form action="<?= admin_url('admin-post.php') ?>" method="POST">
      <input type="hidden" name="action" value="assign_permission">
      <input type="hidden" name="Permissions[user]" value="<?php echo $user ?>">
      <table>
        <tr>
          <th>Archivos</th>
          <th>Permitir Descarga</th>
        </tr>
        <?php
        $access = Access::permissionsFilesList($user);
        foreach($access as $index => $row){
          $lastSlash = strrpos($row->file_dir, '/');
          $filename = substr($row->file_dir, $lastSlash+1);
        ?>
          <tr>
            <td><?php echo $filename ?></td>
            <td><input type="checkbox" name="Permissions[files][]" value="<?php echo $row->file_id?>" <?php echo ($row->user_id != null) ? 'checked': '' ?>></td>
          </tr>
        <?php } ?>
      </table>
      <div class="submitCUButton">
        <button type="submit"> Actualizar permisos</button>
      </div>
    </form>
  <?php
    wp_die();
  }

function prepare_data($req){
  $userID = $req['user'];
  $filesID = $req['files'];

  $permissionArray = [];
  foreach($filesID as $k => $fileID)
    $permissionArray[] = ['file_id' => $fileID, 'user_id' => $userID];

  return $permissionArray;
}

function compare_data($stored, $permissions){

  foreach ($stored as $i => $record) {
    foreach ($permissions as $k => $permission) {
      if ($permission['file_id'] == $record['file_id'] && $permission['user_id'] == $record['user_id']){
        unset($stored[$i]);
        unset($permissions[$k]);
        break;
      }
    }
  }
  return ['toAdd' => $permissions, 'toDelete' => $stored];
}


function getIDsToDelete($toDelete){
  if (!empty($toDelete)){
    $ids = [];
    foreach ($toDelete as $key => $value) {
      $ids['access_id'][] = $value['access_id'];
      $ids['file_id'][] = $value['file_id'];
    }

    $ids['access_id'] = implode( ',', $ids['access_id'] );
    $ids['files_id'] = implode( ',', $ids['file_id'] );
    return $ids;
  }
  return [];
}

function add_permissions($permissions){
  if (!empty($permissions))
    return Access::add($permissions);
  return 0;
}

add_action( 'admin_post_assign_permission', 'cu_assign_permission' );
function cu_assign_permission(){
  $req = $_POST['Permissions'];
  $stored = Access::getPermissions($req);

  $permissionsArr = prepare_data($req);
  $comparison = compare_data($stored, $permissionsArr);
  $idsToDelete = getIDsToDelete($comparison['toDelete']);

  Access::transaction();
  try {
      $deletedCount = !empty($idsToDelete) ? Access::deleteByIDs($idsToDelete['access_id']) : true;
      $defaultRemovedCount = !empty($idsToDelete) ? Files::removeDefaultFlag($idsToDelete['files_id']) : true;
      $addedCount = add_permissions($comparison['toAdd']);

      if ($deletedCount !== false && $defaultRemovedCount !==false && $addedCount !==false){
        $assign_status = Flags::ASSIGN_PERMISSON_SUCCESS;
        Access::commit();
      }

      if($deletedCount === false ){
          $assign_status = Flags::ASSIGN_DELETE_ERROR;
          Access::rollBack();
      }

      if ($addedCount === false){
          $assign_status = Flags::ASSIGN_ADD_ERROR;
          Access::rollBack();
      }

      if ($defaultRemovedCount === false){
          $assign_status = Flags::ASSIGN_DEFUALT_ERROR;
          Access::rollBack();
      }

  } catch (Exception $e) {
      $assign_status = Flags::DB_PARAM_ERROR;
      Access::rollBack();

  }

  $url ='admin.php?page=global_custom_upload&tab=assignCapabilities&assign_status='.$assign_status;
  wp_redirect($url);
  exit;
}

add_action( 'wp_ajax_assign_default', 'cu_assign_default' );
function cu_assign_default(){
  parse_str($_POST['data']);

  Files::transaction();
  try {
      // seteo el archivo actual como default
      $fileResult = Files::setAsDefault($url);

      // chequeo que se pudo setear correctamente
      if ($fileResult['status'] == Flags::DB_SAVE_SUCCESS){

        //obtengo todos los permisos almacenados
        $permissionsStored = Access::getAll();

        $toOmit = [];
        //armo un array mínimo con los permisos almacenados para poder comparar con los nuevos
        foreach ($permissionsStored as $key => $permission)
          $toOmit[] = ['file_id' => $permission['file_id'], 'user_id' => $permission['user_id']];

        $users = getClients();
        //creo un nuevo array de permisos a almacenar con la combinación de todos los id de usuarios y del id del archivo actual
        foreach ($users as $key => $user)
          $newAccess[] = [ 'file_id' => $fileResult['file_id'], 'user_id'=> $user->ID ];

        $toAdd = [];
        /*
         * Se filtran los permisos nuevos que no estan actualmente almacenados. Puede darse el caso de que un archivo
         * que esté asignado a algunos usuarios sea seleccionado para ser asignado masivamente. En estos casos hay que
         * filtrar los almacenados para no duplicar registros y solamente agregar para los usuarios que no tienen el acceso
         */
        foreach ($newAccess as $key => $access) {
          $exist = false;
          foreach ($toOmit as $key => $omit) {
              if ( $access['file_id'] == $omit['file_id'] && $access['user_id'] == $omit['user_id'] ){
                $exist = true;
                break;
              }
          }
          if (!$exist)
            $toAdd[] = $access;
        }

        // se obtiene el número de registros guardados
        $addCount = Access::add($toAdd);
        if ($addCount > 0){
          $return = [ 'status' => $fileResult['status'], 'msg' => 'El archivo se asignó masivamente exitosamente!'];
          Files::commit();
        } else{
          [ 'status' => Flags::DB_SAVE_ERROR, 'mgs' => 'Access - No se pudieron asignar los permisos'];
          Files::rollBack();
        }

      } elseif ( $fileResult['status'] == Flags::DB_UPDATE_NO_ROWS ){
          $return = ['status' => Flags::DB_UPDATE_NO_ROWS, 'msg' => 'Files - No se actualizó el estado "is_default" del archivo ' . $url];
          Files::rollBack();
      } else{
          $return = ['status' => Flags::DB_UPDATE_ERROR, 'msg' => 'Files - Se produjo un error al actualizar el estado del archivo'];
          Files::rollBack();
      }

  } catch (Exception $e) {
      Files::rollBack();
      $return = ['status' => Flags::DB_PARAM_ERROR, 'msg' => $e->getMessage()];
  }

  echo json_encode($return);
  wp_die();
}
