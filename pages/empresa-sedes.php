<?php
cabecera($sql, $cusers, $gene, 19);
	/*TODEL*/
	if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("_Jsh_88_".$_GET["dele"]."_ToDelete")){
		$rows = $sql->runDelete("empresario_sedes", "id = '".$sql->fstr($_GET["dele"])."' AND id_empresa = '".$cusers->id_perfil."'");
		if($rows>0){
			$gene->redirect("empresa-sedes");
		}
	}
	$err = Array();
	if(isset($_POST["guardarprofile"])){
		
	
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
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Añadir nuevo registro</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="empresa-sedes">
				<?php
					showInput($datos[0], "Nombre de la sede", "sede");
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
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Desglose de sedes</h4>
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
?>