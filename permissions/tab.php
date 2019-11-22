<?php

require_once('permission.php');


function assignCapabilities(){
  $users = getClients();

?>
  <div id="ucInstructions">
    <p>Seleccione un cliente y luego haga click en cada checkbox para indicar que se otorga el permiso de descarga. </p>
  </div>

  <?php if (isset($_GET['assign_status'])){ ?>
  <div id="actionResult">
    <p><?php echo $result = $_GET['assign_status'] ? 'Se modificaron los permisos de descarga exitosamente':'Se produjo un error inesperado al modificar los permisos de descarga'?></p>
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
<<<<<<< HEAD
          <button type="submit" name="button">Seleccionar</button>
          <label for="select-all">Seleccionar todos los permisos </label>
            <input type="checkbox" name="select-all" value=""/>
=======
          <button type="submit" name="button" onclick="enableAssingDefaultBtn()">Seleccionar</button>
>>>>>>> 97943e17bde238eeaf32715f069048940bd0e75e
        </div>
      </form>
  </div>

  <div class="uc-horizontal-separator"></div>

  <div id="filesPermissionTable"></div>

<?php }
