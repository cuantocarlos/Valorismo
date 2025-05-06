<?php
cabecera($sql, $cusers, $gene, 17);
?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<?php
	/*TODEL*/
	if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("5458".$_GET["dele"]."_ToDelete")){
		$rows = $sql->runDelete("empresario_puestos", "id = '".$sql->fstr($_GET["dele"])."'");
		if($rows>0){
			$gene->redirect("empresa-puestos");
		}
	}
	$err = Array();
	if(isset($_POST["guardarprofile"])){
		
	
		/*Variables*/
		/*
		departamento
		categoria
		titulacion_academica
		otras_preparaciones
		personas_admitir
		personas_salida
		lugar_trabajo
		*/
		if(isset($_POST["departamento"]) && strlen($_POST["departamento"])>0) $data["departamento"] = $sql->fstr($_POST["departamento"]); else $err[0] = "El departamento ha de tener información";
		if(isset($_POST["categoria"]) && strlen($_POST["categoria"])>0) $data["categoria"] = $sql->fstr($_POST["categoria"]); else $err[1] = "La categoría ha de tener información";
		if(isset($_POST["titulacion_academica"]) && strlen($_POST["titulacion_academica"])>0) $data["titulacion_academica"] = $sql->fstr($_POST["titulacion_academica"]); else $err[2] = "la titulación académica ha de tener información";
		if(isset($_POST["otras_preparaciones"])) $data["otras_preparaciones"] = $sql->fstr($_POST["otras_preparaciones"]);
		if(isset($_POST["personas_admitir"]) && is_numeric($_POST["personas_admitir"])) $data["personas_admitir"] = $sql->fstr($_POST["personas_admitir"]); else $err[3] = "El Nº de personas a admitir ha de ser un número";
		if(isset($_POST["personas_salida"]) && is_numeric($_POST["personas_salida"])) $data["personas_salida"] = $sql->fstr($_POST["personas_salida"]); else $err[4] = "El Nº de personas a dar salida ha de ser un número";
		if(isset($_POST["lugar_trabajo"]) && is_numeric($_POST["lugar_trabajo"])) $data["lugar_trabajo"] = $sql->fstr($_POST["lugar_trabajo"]);
		/*check si el lugar de trabajo esta en BBDD*/
		$toCheck = $sql->runSelect("empresario_sedes", "id = '".$data["lugar_trabajo"]."' AND id_empresa = '".$cusers->id_perfil."'");
		if(count($toCheck) != 1) unset($data["lugar_trabajo"]);
		
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
					$data["id_empresario"] = $saveto["id_perfil"];
					$rows = $sql->runInsert("empresario_puestos", $data);
				}else{
					$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
					$gene->logaction($cusers->id, "Datos: ERROR AL GUARDAR EL ID del PERFIL DEL EMPRESARIO");
				}
			}else{
				$data["id_empresario"] = $cusers->id_perfil;
				$rows = $sql->runInsert("empresario_puestos", $data);
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
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Añadir nuevo registro</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="empresa-puestos">
				<?php
					showInput($datos[0], "Departamento", "departamento");
					showInput($datos[0], "Categoría", "categoria");
					showInput($datos[0], "Titulación académica", "titulacion_academica");
					showInput($datos[0], "Otras preparaciones y capacidades", "otras_preparaciones");
					showInput($datos[0], "Personas a admitir", "personas_admitir");
					showInput($datos[0], "Personas a darles salida", "personas_salida");
					$sedes = $sql->runSelect("empresario_sedes", "id_empresa = '".$cusers->id_perfil."'", "id, sede as valor");
					showSelect($datos[0], "Lugar de trabajo", "lugar_trabajo", $sedes);
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
$rows = $sql->runSelect("empresario_puestos t LEFT JOIN empresario_sedes s ON t.lugar_trabajo = s.id", "id_empresario = '".$cusers->id_perfil."'", "t.*, s.sede", false, false, false, false, false, false);

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
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Desglose de puestos de trabajo</h4>
	<div class="widgetcontent wc1">
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script>
		jQuery(document).ready(function(){
			jQuery(".modales").click(function(){
				var id = jQuery(this).attr("id");
				var token = jQuery(this).attr("token");
				jQuery('#paradel').attr("href", "?dele="+id+"&token="+token);
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
					<th class="row_cus">Departamento</th>
					<th class="row_cus">Categoría</th>
					<th class="row_cus">Titulación academica</th>
					<th class="row_cus">Otras capacidades</th>
					<th class="row_cus">Nº Personas admitir</th>
					<th class="row_cus">Nº Personas salir</th>
					<th class="row_cus">Lugar de trabajo</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus"><?php echo $rows[$x]["departamento"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["categoria"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["titulacion_academica"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["otras_preparaciones"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["personas_admitir"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["personas_salida"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["sede"]; ?></td>
						<td class="row_cus">
							<a href="#modal_box" data-toggle="modal" class="modales" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Borrar el registro: ".$rows[$x]["departamento"]." ".$rows[$x]["categoria"]; ?>" token="<?php echo md5("5458".$rows[$x]["id"]."_ToDelete"); ?>">
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
pie();


function showInput($datos, $label, $name, $type = "text", $class="input-large", $simbol = "", $err = Array()){
	$default = "No hay datos";
	if(isset($datos[$name])){
		$default = $datos[$name];
	}
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && ($_POST[$name] != $datos[$name] || isset($err[1]))){
		$default = $_POST[$name];
		$color = "error";
		$msg = '<span class="help-inline">Porfavor corrige el error</span>';
	}
	$sym = "";
	$simclass = "";
	if($simbol != ""){
		$sym = '<span class="add-on">'.$simbol.'</span>';
		$simclass = " input-append";
	}

	?>
	<div class="par control-group <?php echo $color; ?>">
		<label class="control-label" for="<?php echo $name; ?>"><?php echo $label; ?></label>
		<div class="controls<?php echo $simclass; ?>">
			<input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="<?php echo $class; ?>" placeholder="<?php echo $label; ?>" value="<?php echo $default; ?>" />
			<?php echo $sym; ?>
			<?php echo $msg; ?>
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
			  <option value="Ninguna">Sin sede</option>
			  <?php
			  for($x=0; $x<count($array); $x++){
				if($array[$x]["id"] == $default) $sel = "selected"; else $sel = "";
				?><option value="<?php echo $array[$x]["id"]; ?>" <?php echo $sel; ?>><?php echo $array[$x]["valor"]; ?></option> <?php
			  }
			  ?>
			</select>
			<?php echo $msg; ?>
		</span>
	</div>
	<?php
}
?>