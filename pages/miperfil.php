<?php
require_once('class/cUpload_file.php');
$upload = new cUpload_file($cusers, $gene, $sql);
cabecera($sql, $cusers, $gene, 15);
$sedes = true;

?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<script type="text/javascript" src="js/chosen.jquery.min.js"></script>
<script>
	jQuery(document).ready(function(){
		jQuery(".chzn-select").chosen();
	});
</script>
<?php

if($cusers->rol != 3){
	$err = Array();
	if(isset($_POST["guardarprofile"])){
		if($cusers->rol == 1){
			if(isset($_POST["nombre"]) && $gene->checkLenght($_POST["nombre"], 3)) $data["nombre"] = $sql->fstr($_POST["nombre"]); else $err[0] = "El nombre es obligatorio y ha de tener un mínimo de 3 carácteres";
			if(isset($_POST["apellidos"]) && $gene->checkLenght($_POST["apellidos"], 3, 0)) $data["apellidos"] = $sql->fstr($_POST["apellidos"]); else $err[1] = "Los apellidos han de tener un mínimo de 3 carácteres";
			if(isset($_POST["email"]) && $gene->checkEmailInDB($_POST["email"], $cusers->id)) $data2["email"] = $sql->fstr($_POST["email"]); else $err[2] = "El e-mail es obligatorio y ha de ser válido <i>(Es posible que el e-mail ya esté en uso)</i>";
			if(isset($_POST["telefono"]) && $gene->checkTelefono($_POST["telefono"], 0)) $data["telefono"] = $sql->fstr($_POST["telefono"]); else $err[3] = "El teléfono no es válido";
			if(isset($_POST["dni"]) && $gene->checkDNI($_POST["dni"], 0)) $data["dni"] = $sql->fstr($_POST["dni"]); else $err[4] = "El DNI no es válido (00000000X)";
			if(isset($_POST["direccion"]) && $gene->checkLenght($_POST["direccion"], 5, 0)) $data["direccion"] = $sql->fstr($_POST["direccion"]); else $err[5] = "La dirección a de tener un mínimo de 5 carácteres";
			if(isset($_POST["descripcion"])) $data["descripcion"] = $sql->fstr($_POST["descripcion"]); else $err[7] = "La descripción no es válida";
			if(isset($_POST["cp"]) && (is_numeric($_POST["cp"]) || strlen($_POST["cp"])==0)) $data["cp"] = $sql->fstr($_POST["cp"]); else $err[6] = "El código postal ha de ser un número";
			if(isset($_POST["localidad"])) $data["localidad"] = $sql->fstr($_POST["localidad"]);
			if(isset($_POST["provincia"])) $data["provincia"] = $sql->fstr($_POST["provincia"]);
			
			if(isset($_POST["estado"]) && is_numeric($_POST["estado"]) && ($_POST["estado"] == 1 || $_POST["estado"] == 0)) $data["estado"] = $sql->fstr($_POST["estado"]);
			if(isset($_POST["ofertas"]) && is_numeric($_POST["ofertas"]) && ($_POST["ofertas"] == 1 || $_POST["ofertas"] == 0)) $data["ofertas"] = $sql->fstr($_POST["ofertas"]);
			
			
			if(count($err)>0){
				$msg = "";
				for($x=0; $x<=7; $x++){
					if($msg != "") $space = "<br />"; else $space = "";
					if(isset($err[$x])){
						$msg .= $space.$err[$x];
					}
				}
				$gene->showMessage($msg);
			}else{
				/*GUARDAR*/
				$rows = $sql->runUpdate("usuarios", $data2,"id = '".$cusers->id."'");
				/*get id del perfil*/
				$check = $sql->runSelect("usuarios", "id = '".$cusers->id."'", "id_perfil");
				if(isset($check[0]["id_perfil"]) && is_numeric($check[0]["id_perfil"])){
					/*Existe?*/
					$existe = $sql->runSelect("consumidor", "id = '".$check[0]["id_perfil"]."'");
					if(count($existe) == 1){
						$rows2 = $sql->runUpdate("consumidor", $data, "id = '".$check[0]["id_perfil"]."'");
					}else{
						$data["id"] = $check[0]["id_perfil"];
						$data["token"] = sha1(rand(1000, 9999).date("YmdHiS")."Tokentosave");
						$rows2 = $sql->runInsert("consumidor", $data);
						/*y Updateamos el id_perfil de la ficha*/
						$sacar = $sql->runSelect("consumidor", "token = '".$data["token"]."'");
						if(isset($sacar[0]["id"]) && is_numeric($sacar[0]["id"])){
							$saveto["id_perfil"] = $sacar[0]["id"];
							$sql->runUpdate("usuarios", $saveto, "id = '".$cusers->id."'");
						}else{
							$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
							$gene->logaction($cusers->id, "ERROR AL GUARDAR EL ID del PERFIL DEL CONSUMIDOR");
						}
						
						
					}
					if(($rows+$rows2)>0){
						$gene->showMessage("Datos guardados correctamente", "success");
						unset($_POST);
					}
				}else{
					$gene->showMessage("Ha ocurrido un error al guardar el perfil");
					$gene->logaction($cusers->id, "ERROR AL GUARDAR EL PERFIL DEL CONSUMIDOR, NO ID PERFIL");
				}
			}
		}elseif($cusers->rol == 2){
			if(isset($_POST["nombre"]) && $gene->checkLenght($_POST["nombre"], 3)) $data["nombre"] = $sql->fstr($_POST["nombre"]); else $err[0] = "El nombre es obligatorio y ha de tener un mínimo de 3 carácteres";
			if(isset($_POST["email"]) && $gene->checkEmailInDB($_POST["email"], $cusers->id)) $data2["email"] = $sql->fstr($_POST["email"]); else $err[1] = "El e-mail es obligatorio y ha de ser válido <i>(Es posible que el e-mail ya esté en uso)</i>";
			if(isset($_POST["domicilio_social"]) && $gene->checkLenght($_POST["domicilio_social"], 5, 0)) $data["domicilio_social"] = $sql->fstr($_POST["domicilio_social"]); else $err[2] = "El domicilio social a de tener un mínimo de 5 carácteres";
			if(isset($_POST["cif"]) && $gene->checkDNI($_POST["cif"], 0)) $data["cif"] = $sql->fstr($_POST["cif"]); else $err[3] = "El CIF no es válido (X0000000X)";
			if(isset($_POST["cnae"]) && $gene->checkcnaeInDB($_POST["cnae"])) $data["cnae"] = $sql->fstr($_POST["cnae"]); else $err[4] = "El C.N.A.E seleccionado no es válido";
			
			if(count($err)>0){
				$msg = "";
				for($x=0; $x<=4; $x++){
					if($msg != "") $space = "<br />"; else $space = "";
					if(isset($err[$x])){
						$msg .= $space.$err[$x];
					}
				}
				$gene->showMessage($msg);
			}else{
				/*GUARDAR*/
				$rows = $sql->runUpdate("usuarios", $data2,"id = '".$cusers->id."'");
				/*get id del perfil*/
				$check = $sql->runSelect("usuarios", "id = '".$cusers->id."'", "id_perfil");
				if(isset($check[0]["id_perfil"]) && is_numeric($check[0]["id_perfil"])){
					/*Existe?*/
					$existe = $sql->runSelect("empresario", "id = '".$check[0]["id_perfil"]."'");
					if(count($existe) == 1){
						$rows2 = $sql->runUpdate("empresario", $data, "id = '".$check[0]["id_perfil"]."'");
					}else{
						$data["id"] = $check[0]["id_perfil"];
						$data["token"] = sha1(rand(1000, 9999).date("YmdHiS")."Tokentosave");
						$rows2 = $sql->runInsert("empresario", $data);
						/*y Updateamos el id_perfil de la ficha*/
						$sacar = $sql->runSelect("empresario", "token = '".$data["token"]."'");
						if(isset($sacar[0]["id"]) && is_numeric($sacar[0]["id"])){
							$saveto["id_perfil"] = $sacar[0]["id"];
							$sql->runUpdate("usuarios", $saveto, "id = '".$cusers->id."'");
						}else{
							$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
							$gene->logaction($cusers->id, "ERROR AL GUARDAR EL ID del PERFIL DEL EMPRESARIO");
						}
					}
					if(($rows+$rows2)>0){
						$gene->showMessage("Datos guardados correctamente", "success");
						unset($_POST);
					}
				}else{
					$gene->showMessage("Ha ocurrido un error al guardar el perfil");
					$gene->logaction($cusers->id, "ERROR AL GUARDAR EL PERFIL DEL EMPRESARIO, NO ID PERFIL");
				}
			}
		}
		if(isset($_FILES['img_user']['name']) && $_FILES['img_user']['name']!="" && $cusers->id != 0){
			$errori = $upload->UploadImage("img_user", 100, 100, "images/photos/", "usuarios", "avatar", "id = '".$cusers->id."'", md5($cusers->id.date("d/m/Y_H:i:s:u")));
			if($errori > 0){
				$error_arrayi=Array(0=>"Imagen subida correctamente",
									1=>"Formato del archivo incompatible",
									2=>"El tamaño del archivo no puede ser superior a 3mb",
									3=>"Resolución de la imagen demasiado grande.(Máximo 3300x3300 px)",
									4=>"Al subir el archivo, porfavor vuelve a intentarlo.",
									5=>"No se ha seleccionado ningúna imagen");
				$gene->showMessage($error_arrayi[$errori]);
			}else{
				$gene->showMessage("Imagen subida correctamente", "success");
				$gene->redirect("miperfil");
			}
		}
	}
?>
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Modificar Datos</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="miperfil" enctype="multipart/form-data">
				<?php
				$provincias = $sql->runSelect("provincias", "1=1", "id, provincia as valor, autonomia as valor2", "autonomia, provincia");
				if($cusers->rol == 1){
					/*es consumidor*/
					$datos = $sql->runSelect("usuarios u LEFT JOIN consumidor c ON c.id = u.id_perfil", "u.id = '".$cusers->id."'", "u.email, c.*");
					if(!isset($datos[0])) $datos[0] = Array();
					showInput($datos[0], "Nombre", "nombre");
					showInput($datos[0], "Apellidos", "apellidos");
					showInput($datos[0], "Dirección", "direccion");
					showInput($datos[0], "Código Postal", "cp");
					showInput($datos[0], "Localidad", "localidad");
					//showInput($datos[0], "Provincia", "provincia");
					showSelect($datos[0], "Provincia", "provincia", $provincias);
					showInput($datos[0], "DNI", "dni");
					showInput($datos[0], "Email", "email");
					showInput($datos[0], "Teléfono", "telefono");
					showInputT($datos[0], "Descripción", "descripcion");
					$estados = Array(0 => Array("id" => "0", "valor" => "Parado"), 1 => Array("id" => "1", "valor" => "Ocupado"));
					showRadio($datos[0], "Estado", "estado", $estados);
					$ofertas = Array(0 => Array("id" => "0", "valor" => "No deseo recibirlas"), 1 => Array("id" => "1", "valor" => "Deseo recibirlas"));
					showRadio($datos[0], "Ofertas de empleo", "ofertas", $ofertas);
					showUpload("Subir Avatar", "img_user");
				}elseif($cusers->rol == 2){
					/*Es empresa*/
					$datos = $sql->runSelect("usuarios u LEFT JOIN empresario e ON e.id = u.id_perfil", "u.id = '".$cusers->id."'", "u.email, e.*");
					if(!isset($datos[0])) $datos[0] = Array();
					showInput($datos[0], "Nombre", "nombre");
					showInput($datos[0], "Email", "email");
					showInput($datos[0], "Domicilio social", "domicilio_social");
					showInput($datos[0], "CIF", "cif");
					showSelect($datos[0], "Provincia", "provincia", $provincias);
					//showInput($datos[0], "CNAE", "cnae");
					$cnaes = $sql->runSelect("cnae", "1=1", "id, CONCAT(id, ' - ', cnae) as valor");
					showSelect($datos[0], "CNAE", "cnae", $cnaes);
					showUpload("Subir Avatar", "img_user");
				}
				?>				
				<p class="stdformbutton">
					<input type="submit" name="guardarprofile" class="btn btn-primary" value="Guardar Cambios" />
				</p>
		</form>
	</div><!--widgetcontent-->
</div><!--widget--> 
<br />
<br />
<?php
}
if($cusers->rol == 2 && $sedes){
	?>
	<div class="widgetbox" id="asedes">
		<h4 class="widgettitle">SEDES</h4>
		<div class="widgetcontent wc1">
			<?php
			/*MOSTRAR EL GUARDAR SEDES*/
			if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("_Jsh_88_".$_GET["dele"]."_ToDelete")){
				$rows = $sql->runDelete("empresario_sedes", "id = '".$sql->fstr($_GET["dele"])."' AND id_empresa = '".$cusers->id_perfil."'");
				if($rows>0){
					$gene->redirect("miperfil");
				}
			}
			$err = Array();
			if(isset($_POST["guardasede"])){
				
			
				/*Variables*/
				/*
				sede
				*/
				if(isset($_POST["sede"]) && strlen($_POST["sede"])>=3) $data["sede"] = $sql->fstr($_POST["sede"]); else $err[0] = "La sede ha de tener al menos 3 carácteres";
				
				if(count($err)>0){
					$msg = "";
					for($x=0; $x<=1; $x++){
						if($msg != "") $space = "<br />"; else $space = "";
						if(isset($err[$x])){
							$msg .= $space.$err[$x];
						}
					}
					$gene->showMessage($msg);
				}else{
					$rows = 0;
					if($cusers->id_perfil == 0){
						/*Hay que crear un perfil*/
						$datain["token"] = sha1(rand(1000, 9999).date("YmdHiS")."Tokentosave");
						$rows2 = $sql->runInsert("empresario", $datain);
						/*y Updateamos el id_perfil de la ficha*/
						$sacar = $sql->runSelect("empresario", "token = '".$datain["token"]."'");
						if(isset($sacar[0]["id"]) && is_numeric($sacar[0]["id"])){
							$saveto["id_perfil"] = $sacar[0]["id"];
							$sql->runUpdate("usuarios", $saveto, "id = '".$cusers->id."'");
							/*y Guardamos el historico*/
							$data["id_empresa"] = $saveto["id_perfil"];
							$rows = $sql->runInsert("empresario_sedes", $data);
						}else{
							$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
							$gene->logaction($cusers->id, "Datos: ERROR AL GUARDAR EL ID del PERFIL DEL EMPRESARIO");
						}
					}else{
						$data["id_empresa"] = $cusers->id_perfil;
						$rows = $sql->runInsert("empresario_sedes", $data);
					}
					if($rows != 0){
						$gene->showMessage("Datos grabados correctamente", "success");
						unset($_POST);
					}else{
						$gene->showMessage("Ha ocurrido un error al grabar los datos");
					}
				}
			}
			?>
			<div class="widgetbox box-info">
				<h4 class="widgettitle">Añadir nueva sede</h4>
				<div class="widgetcontent wc1">
					<form id="form1" class="stdform" method="post" action="miperfil#asedes">
							<?php
							
								showInput($datos[0], "Nombre de la sede", "sede");
							?>
							<p class="stdformbutton">
								<input type="submit" name="guardasede" class="btn btn-primary" value="Añade la sede" />
							</p>
					</form>
				</div><!--widgetcontent-->
			</div><!--widget--> 
			<br />
			<br />

			<?php
			$rows = $sql->runSelect("empresario_sedes", "id_empresa = '".$cusers->id_perfil."'", "*", false, false, false, false, false, false);

			?>
			<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="js/responsive-tables.js"></script>
			<style>
			.ui-datepicker-calendar {
				display: none;
			}
			.ui-datepicker-prev {
				position: initial;
			}
			.ui-datepicker-month, .ui-datepicker-year {
				width: 125px;
				margin: 3px;
			}
			.ui-datepicker-header {
				text-align: start;
			}
			.ui-state-default.ui-corner-all {
				display: inline-block;
				-moz-box-shadow: none;
				-webkit-box-shadow: none;
				box-shadow: none;
				color: #ffffff;
				background-color: #0044cc;
				border-color: #0a6bce;
				display: inline-block;
				-moz-box-shadow: none;
				-webkit-box-shadow: none;
				box-shadow: none;
				padding: 5px;
				border: 1px solid #bbb;
				margin: 5px;
				min-width: 125px;
			}
			</style>
			<div class="widgetbox box-info">
				<h4 class="widgettitle">Desglose de sedes</h4>
				<div class="widgetcontent wc1">
					<script type="text/javascript" src="js/jquery.validate.min.js"></script>
					<script>
					jQuery(document).ready(function(){
						jQuery(".modales").click(function(){
							var id = jQuery(this).attr("id");
							var token = jQuery(this).attr("token");
							jQuery('#paradel').attr("href", "?dele="+id+"&token="+token+"#asedes");
							jQuery("#titulo_mod").text(jQuery(this).attr("titulo"));
						});
					});
					</script>
					<style>
						.row_cus{
							text-align: center !important;
							font-size: 11px !important;
						}
						#dyntable_wrapper {
							overflow-x: auto;
						}
					</style>
					<table id="dyntable" class="table table-bordered responsive">
						<thead>
							<tr>
								<th class="row_cus">Sede</th>
								<th class="row_cus">Añadida el:</th>
								<th class="row_cus">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php
							for($x=0; $x<count($rows); $x++){
								?>
								<tr>
									<td class="row_cus"><?php echo $rows[$x]["sede"]; ?></td>
									<td class="row_cus"><?php echo date("d/m/Y", strtotime($rows[$x]["fecha"])); ?></td>
									<td class="row_cus">
										<a href="#modal_box" data-toggle="modal" class="modales" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Borrar el registro: ".$rows[$x]["sede"]; ?>" token="<?php echo md5("_Jsh_88_".$rows[$x]["id"]."_ToDelete"); ?>">
										<i class="iconfa-trash icon-white"></i>
										</a>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<br />
	<br />
	<!-- MODAL -->
	<div id="modal_box" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="titulo_mod">TITULO</h3>
		</div>
		<div class="modal-body">
			<div class="alert alert-block  alert-error">
				  <h4>BORRAR: </h4>
				  <p style="margin: 8px 0">¿Estás seguro de borrar el registro?, no habrá marcha atrás</p>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
			<a href="#" id="paradel" class="btn btn-danger btn-rounded"><i class="iconfa-trash icon-white"></i> Borrar</a>
		</div>
	</div>
	<?php
}


if(isset($_POST["guardarpass"])){
	if(isset($_POST["passact"]) && isset($_POST["newpass1"]) && isset($_POST["newpass2"]) &&
	   strlen($_POST["passact"]) >= 8 && strlen($_POST["newpass1"]) >= 8 && strlen($_POST["newpass2"]) >= 8){
		$checkold = $sql->runSelect("usuarios", "id = '".$cusers->id."' AND password = '".sha1($_POST["passact"])."'");
		if(count($checkold)==1){
			if($gene->checkPassword($_POST["newpass1"], $_POST["newpass2"])){
				/*ACTUALIZAMOS PASSWORD*/
				$datap["password"] = sha1($_POST["newpass1"]);
				$rows = $sql->runUpdate("usuarios", $datap, "id = '".$cusers->id."'");
				if($rows == 1){
					$gene->showMessage("Contraseña modificada correctamente", "success");
				}else{
					$gene->showMessage("No se han detectado cambios en la contraseña", "info");
				}
			}else{
				$gene->showMessage("Las contraseñas nuevas no coinciden");
			}
		}else{
			$gene->showMessage("La contraseña anterior no es válida");
		}
	}else{
		$gene->showMessage("La contraseña ha de tener un mínimo de 8 carácteres");
	}
	unset($_POST);
}
?>
<div class="widgetbox box-inverse" id="pass">
	<h4 class="widgettitle">Configuración del acceso</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="miperfil#pass" enctype="multipart/form-data">
				<?php
				$datos[0] = Array();
				showInput($datos[0], "Contraseña Actual", "passact", "password");
				showInput($datos[0], "Nueva Contraseña", "newpass1", "password");
				showInput($datos[0], "Repite Nueva Contraseña", "newpass2", "password");
				?>				
				<p class="stdformbutton">
					<input type="submit" name="guardarpass" class="btn btn-primary" value="Modificar contraseña" />
				</p>
		</form>
	</div><!--widgetcontent-->
</div><!--widget-->   
<?php
pie();

function showInput($datos, $label, $name, $type = "text", $class="input-large"){
	$default = "Valor no encontrado";
	if(isset($datos[$name])){
		$default = $datos[$name];
	}
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && $_POST[$name] != $datos[$name]){
		$default = $_POST[$name];
		$color = "error";
		$msg = '<span class="help-inline">Porfavor corrige el error</span>';
	}

	?>
	<div class="par control-group <?php echo $color; ?>">
		<label class="control-label" for="<?php echo $name; ?>"><?php echo $label; ?></label>
		<div class="controls"><input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $class; ?>" placeholder="<?php echo $label; ?>" value="<?php echo $default; ?>" /><?php echo $msg; ?></div>
	</div>
	<?php
}

function showInputT($datos, $label, $name, $type = "text", $class="input-large"){
	$default = "Valor no encontrado";
	if(isset($datos[$name])){
		$default = $datos[$name];
	}
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && $_POST[$name] != $datos[$name]){
		$default = $_POST[$name];
		$color = "error";
		$msg = '<span class="help-inline">Porfavor corrige el error</span>';
	}

	?>
	<div class="par control-group <?php echo $color; ?>">
		<label class="control-label" for="<?php echo $name; ?>"><?php echo $label; ?></label>
		<div class="controls">
			<textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $class; ?>" placeholder="<?php echo $label; ?>"><?php echo $default; ?></textarea><?php echo $msg; ?>
		</div>
	</div>
	<?php
}

function showUpload($label, $name){
	?>
	<div class="par">
		<label><?php echo $label; ?></label>
		<div class="fileupload fileupload-new" data-provides="fileupload">
		<div class="input-append">
		<div class="uneditable-input span3">
			<i class="iconfa-file fileupload-exists"></i>
			<span class="fileupload-preview"></span>
		</div>
		<span class="btn btn-file"><span class="fileupload-new">Seleccionar archivo</span>
		<span class="fileupload-exists">Cambiar</span>
		<input type="file" name="<?php echo $name; ?>" /></span>
		<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Quitar</a>
		</div>
		</div>
	</div>
	<?php
}

function showSelect($datos, $label, $name, $array, $default = ""){
	if(isset($datos[$name])){
		$default = $datos[$name];
	}
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && $_POST[$name] != $datos[$name]){
		$default = $_POST[$name];
		$color = "error";
		$msg = '<span class="help-inline">Porfavor corrige el error</span>';
	}
	?>
	<div class="par control-group <?php echo $color; ?>">
		<label><?php echo $label; ?></label>
		<span class="formwrapper">
			<select data-placeholder="<?php echo $label; ?>" name="<?php echo $name; ?>"  style="width:350px" class="chzn-select" tabindex="2">
			  <option value=""></option>
			  <?php
			  $open = false;
			  for($x=0; $x<count($array); $x++){
				if($array[$x]["id"] == $default) $sel = "selected"; else $sel = "";
				if(isset($array[$x]["valor2"])){
					if($x == 0 || $array[$x]["valor2"] != $array[$x-1]["valor2"]){
						if($x!=0){ ?></optgroup><?php $open = false; }
						?><optgroup label="<?php echo $array[$x]["valor2"]; ?>"><?php
						$open = true;
					}
				}
				?><option value="<?php echo $array[$x]["id"]; ?>" <?php echo $sel; ?>><?php echo $array[$x]["valor"]; ?></option> <?php
			  }
			  if($open){
				?></optgroup><?php
			  }
			  ?>
			</select>
			<?php echo $msg; ?>
		</span>
	</div>
	<?php
}
function showRadio($datos, $label, $name, $array, $default = ""){
	if(isset($datos[$name])){
		$default = $datos[$name];
	}
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && $_POST[$name] != $datos[$name]){
		$default = $_POST[$name];
	}
	?>
	<label><?php echo $label; ?></label>
	<span class="formwrapper">
		<?php
		 for($x=0; $x<count($array); $x++){
			if($array[$x]["id"] == $default) $sel = "checked='checked'"; else $sel = "";
			?>
			<input type="radio" name="<?php echo $name; ?>" <?php echo $sel; ?> value="<?php echo $array[$x]["id"]; ?>" /> <?php echo $array[$x]["valor"]; ?> &nbsp; &nbsp;
			<?php
		}
		?>
	</span>
	<?php
}
?>