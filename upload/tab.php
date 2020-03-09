<?php

require_once(__DIR__ . '/../db/Files.php');
require_once(__DIR__ .'/../permissions/permission.php');
require_once('util.php');
require_once('deleteFiles.php');
require_once('assign-default.php');


function cu_show_files_tree(){
    $filesDir = get_cu_upload_folder();

    $path = $_GET['path'];
    if ($path)
      $filesDir = $filesDir . "/" . $path;

    $dirTree = navigate($filesDir);
?>
  <ul>
    <?php $dir = $dirTree['dir']; ?>
    <?php foreach ($dir as $key => $dElement) { ?>
      <li class="uc-dir"><?php echo $dElement?></li>
    <?php } ?>
    <?php $files = $dirTree['file'] ?>
    <?php foreach ($files as $key => $fElement) { ?>
        <li class="uc-files">
          <div class="uc-name"><?php echo $fElement; ?></div>
          
          <div class="uc-assign">
            <form enctype="multipart/form-data" action="<?= admin_url('admin-post.php') ?>" method="POST">
              <input type="hidden" name="url" value="<?php echo $filesDir. '/' . $fElement ?>">
              <input type="hidden" name="action" value="assign_default">
              <button id="cuAssignDefault" type="submit">Asignación masiva</button>
            </form>
          </div>

          <div class="uc-delete">
            <form enctype="multipart/form-data" action="<?= admin_url('admin-post.php') ?>" method="POST">
              <input type="hidden" name="url" value="<?php echo $filesDir. '/' . $fElement ?>">
              <input type="hidden" name="action" value="delete_files">
              <button id="ucEraseFiles" style="background-image:url('../img/basura.svg')" type="submit"></button>
            </form>
          </div>
        </li>
    <?php } ?>

  </ul>

<?php }

function createUploadForn(){
    $products = getProducts();
  ?>

  <div id="ucInstructions">
    <p>Los archivos que suba seran almacenados en la carpeta del cliente seleccionado.</p>
  </div>

  <?php if (isset($_GET['assign_status'])){ ?>
    <div id="actionResult">
      <p><?php echo $result = $_GET['assign_status'] ? 'La carga de archivos se completó exitosamente':'Se produjo un error inesperado durante la carga de archivos'?></p>
    </div>
  <?php }
    if (isset($_GET['delete_status'])){ ?>
    <div id="actionResult">
      <p><?php echo $result = $_GET['delete_status'] ? 'El archivo se borró exitosamente':'Se produjo un error inesperado durante el borrado del archivos'?></p>
    </div>
  <?php }
  if (isset($_GET['assign_default_status'])){ ?>
    <div id="actionResult">
      <p><?php echo $result = $_GET['assign_default_status'] ? 'El archivo se asignó exitosamente':'Se produjo un error inesperado durante la asignación masiva de archivos'?></p>
    </div>
  <?php }

  $nombres_productos = array(
    'belen' => 'Belen',
    'bakhou' => 'Bakhou',
    'lara_teens' => 'Lara Teens',
    'sigry' => 'Sigry',
  );
  ?>


    <div id="uploadWrapper">
      <div class="left-panel">
      <form enctype="multipart/form-data" action="<?= admin_url('admin-post.php') ?>" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="104857600">
        <input type="hidden" name="action" value="upload_files">

        <div id="clientSelection" class="uc-upload-block">
          <div>Seleccione el producto:</div>
            <div id="clientsList">
              <select name="product" required>
                  <option value="" disabled selected>Producto</option>
                <?php foreach ($products as $product => $id) { ?>
                  <option value="<?php echo $id?>"><?php echo $nombres_productos[$product] ?></option>
                <?php } ?>
              </select>
            </div>
        </div>

        <div id="fileTypeSelection" class="uc-upload-block">
          <div>Seleccione el tipo de archivo:</div>
          <div>
            <select name="FileType" required>
              <option value="" disabled selected>Tipo de archivo</option>
              <?php $types = Files::getTypes(); ?>
              <?php foreach ($types as $key => $type) { ?>
              <option value="<?php echo $type['id'] ?>"><?php echo $type['label'] ?></option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="uc-fileinput-wrapper uc-upload-block">
          <div class="uc-fileinput">
            <div class="btn">Seleccione los archivos:</div>
            <input id="uploadInput" type="file" name="Files[]" multiple/>
          </div>
          <div class="uc-file-name"></div>
        </div>

        <?php submit_button('Subir Archivos') ?>
      </form>
      </div>

      <div class="right-panel">
        <h3>Archivos Subidos</h3>
        <form id="assign-permission" action="<?php admin_url('admin-post.php') ?>" method="POST">
          <div id="fileTree" class="uc-upload-block">
            <button id="ucGoBack">Volver</button>
            <div class="uc-list">
              <?php cu_show_files_tree() ?>
            </div>
          </div>
        </form>
      </div>
    </div>

<?php }

function cu_upload_files(){
  $files = $_FILES['Files'];
  $id = intval($_POST['product']);
  $type = intval($_POST['FileType']);

  $product_name = getProductById($id);
  $basePath = get_cu_upload_folder()."/".$product_name;

  Files::transaction();
  $opResult = false;
  for ($i=0; $i < count($files['name']) ; $i++) {

      $mkDirStatus = wp_mkdir_p($basePath);
      if ($mkDirStatus){
        $uploadFilePath = $basePath ."/".basename($files['name'][$i]);
        
        try{
          
          $storeInDBStatus = Files::add([['file_dir' => $uploadFilePath, 'file_type' => $type]]) ? 1 : 0;
          if ($storeInDBStatus){
            $fileCopyStatus = move_uploaded_file($files['tmp_name'][$i], $uploadFilePath);

            if ( $fileCopyStatus ) {
              $opResult = Flags::UPLOAD_SUCCESS;
              Files::commit();
            } else{
              $opResult = Flags::COPY_FILE_ERROR;
              Files::rollBack();
              break;
            }

          } else{
              $opResult = Flags::DB_SAVE_ERROR;
              Files::rollBack();
              break;
          }

        } catch(Exception $e){
            $opResult = Flags::DB_SAVE_EXCEPTION;
            Files::rollBack();
            break;
        }

      } else{
        $opResult = Flags::CREATE_DIR_ERROR;
        break;
      }
  }


  $url ='admin.php?page=global_custom_upload&tab=uploadFiles&assign_status='.$opResult;
  wp_redirect($url);
  exit;

}



add_action( 'admin_post_upload_files', 'cu_upload_files' );
add_action( 'wp_ajax_cu_navigate', 'cu_show_files_tree' );
