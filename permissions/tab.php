<?php

require_once('permission.php');


function assignCapabilities(){
  $users = getClients();

?>
  <div id="ucInstructions">
    <p>Seleccione un cliente y luego haga click en cada checkbox para indicar que se otorga el permiso de descarga. </p>
  </div>

  <?php if (isset($_GET['assign_status'])){
    $assign_status = $_GET['assign_status'];
  ?>
  <div id="actionResult">
    <?php if ($assign_status == Flags::ASSIGN_PERMISSON_SUCCESS) {?>
      <p>Se modificaron los permisos de descarga exitosamente.</p>
    <?php } ?>

    <?php if ($assign_status == Flags::ASSIGN_DELETE_ERROR) {?>
      <p>Se produjo un error al eliminar permisos. La operación no se pudo completar.</p>
    <?php } ?>

    <?php if ($assign_status == Flags::ASSIGN_DEFUALT_ERROR) {?>
      <p>Se modificaron los permisos exitosamente, pero se produjo un erro al actualizar el estado de default de los archivos asociados. La operación no se pudo completar.</p>
    <?php } ?>

    <?php if ($assign_status == Flags::ASSIGN_ADD_ERROR) {?>
      <p>Se produjo un error al agregar permisos. La operación no se pudo completar.</p>
    <?php } ?>

    <?php if ($assign_status == Flags::DB_PARAM_ERROR) {?>
      <p>Se encontró un error en el envío de parámetros al servicio encargado de actualizar los permisos. La operación no se pudo completar.</p>
    <?php } ?>

  </div>
  <?php } ?>

  <div id="clientSelection">
      <div>Seleccione el cliente:</div>

      <form id="filesByClientForm">
        <div id="clientsList">
          <select name="user" required>
              <option value="" disabled selected>Seleccionar</option>
            <?php foreach ($users as $key => $user) { ?>
              <option value="<?php echo $user->ID?>"><?php echo $user->display_name ?></option>
            <?php } ?>
          </select>
        </div>
        <div>
          <button type="submit" name="button" onclick="enableAssingDefaultBtn()">Seleccionar</button>
        </div>
      </form>
  </div>

  <div class="uc-horizontal-separator"></div>

  <div id="filesPermissionTable"></div>

<?php }
