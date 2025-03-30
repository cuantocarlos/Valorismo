<?php
cabecera($sql, $cusers, $gene, 7);
$editar = false;
?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<?php
	/*TODEL*/
	if($editar){
		if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("221452".$_GET["dele"]."_ToDelete")){
			if($cusers->is_demo == "0"){
				$rows = $sql->runDelete("consumidor_historico", "id = '".$sql->fstr($_GET["dele"])."' AND id_consumidor = '".$cusers->id_perfil."'");
				if($rows>0){
					$gene->redirect("historial");
				}
			}else{
				$gene->showMessage("El usuario DEMO no puede guardar ningún dato", "warning");
			}
		}
		$err = Array();
		if(isset($_POST["guardarprofile"])){
			if($cusers->is_demo == "0"){
				if(isset($_POST["historico"]) && strlen($_POST["historico"])>0) $data["historico"] = $sql->fstr($_POST["historico"]); else $err[0] = "Ha de introducir puesto empeñado";
				if(isset($_POST["anyo"]) && is_numeric($_POST["anyo"])) $data["anyo"] = $sql->fstr($_POST["anyo"]); else $err[1] = "El año no es válido";
				$data["tipo_historico"] = "puesto";
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
						$rows2 = $sql->runInsert("consumidor", $datain);
						/*y Updateamos el id_perfil de la ficha*/
						$sacar = $sql->runSelect("consumidor", "token = '".$datain["token"]."'");
						if(isset($sacar[0]["id"]) && is_numeric($sacar[0]["id"])){
							$saveto["id_perfil"] = $sacar[0]["id"];
							$sql->runUpdate("usuarios", $saveto, "id = '".$cusers->id."'");
							/*y Guardamos el historico*/
							$data["id_consumidor"] = $saveto["id_perfil"];
							$rows = saveAndUpload($sql, $data);	
						}else{
							$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
							$gene->logaction($cusers->id, "Datos: ERROR AL GUARDAR EL ID del PERFIL DEL CONSUMIDOR");
						}
					}else{
						$data["id_consumidor"] = $cusers->id_perfil;
						$rows = saveAndUpload($sql, $data);
					}
					if($rows != 0){
						$gene->showMessage("Datos grabados correctamente", "success");
						unset($_POST);
					}else{
						$gene->showMessage("Ha ocurrido un error al grabar los datos");
					}
				}
			}else{
				$gene->showMessage("El usuario DEMO no puede guardar ningún dato", "warning");
			}
		}
	?>
	<div class="widgetbox">
		<h4 class="widgettitle">Añadir nuevo historico de trabajo</h4>
		<div class="widgetcontent wc1">
			<form id="form1" class="stdform" method="post" action="historial">
					<?php
						showInput("", "Puesto ocupado", "historico");
						showInput("", "Año que lo ocupó", "anyo");
					?>
					<p class="stdformbutton">
						<input type="submit" name="guardarprofile" class="btn btn-primary" value="Añadir al histórico" />
					</p>
			</form>
		</div><!--widgetcontent-->
	</div><!--widget--> 
	<br />
	<br />

	<?php
}

$rows = $sql->runSelect("consumidor_historico ch LEFT JOIN empresario e ON e.id = ch.id_empresa LEFT JOIN cnae ON cnae.id = e.cnae", "ch.id_consumidor = '".$cusers->id_perfil."' AND ch.tipo_historico = 'puesto'", "ch.*, e.nombre, e.domicilio_social, e.cif, cnae.cnae", "ch.anyo DESC, ch.fecha DESC", false, false, false, false, false);
if(count($rows)>0){
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
<?php
if($editar){
?>
<div class="widgetbox">
	<h4 class="widgettitle">Mi historial de trabajo</h4>
	<div class="widgetcontent wc1">
<?php
}
?>
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="js/jquery.gdocsviewer.min.js"></script> 
		<?php
		if($editar){
		?>
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
		<?php
		}
		?>
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
					<th class="row_cus">Empresa</th>
					<th class="row_cus">Domicilio Social</th>
					<th class="row_cus">CIF</th>
					<th class="row_cus">Sector</th>
					<th class="row_cus">Periodo</th>
					<th class="row_cus">Categoría</th>
					<?php if($editar){ ?><th class="row_cus">&nbsp;</th> <?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus"><?php echo $rows[$x]["nombre"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["domicilio_social"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["cif"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["cnae"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["anyo"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["historico"]; ?></td>
						<?php
						if($editar){
						?>
						<td class="row_cus">
							<a href="#modal_box" data-toggle="modal" class="modales" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Borrar el registro: ".$rows[$x]["historico"]." (".$rows[$x]["anyo"].") ".date("d/m/Y", strtotime($rows[$x]["fecha"])); ?>" token="<?php echo md5("221452".$rows[$x]["id"]."_ToDelete"); ?>">
							<i class="iconfa-trash icon-white"></i>
							</a>
						</td>
						<?php
						}
						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<br />
		<br />
		<?php
		$nominas = $sql->runSelect("empresario_consumidor ec INNER JOIN empresario e ON e.id = ec.id_empresario", "ec.id_consumidor = '".$cusers->id_perfil."' AND nomina IS NOT NULL", "ec.*, e.nombre");
		if(count($nominas)>0){
			?>
			Mis nóminas:
			<ul class="shortcuts">
				<?php
				for($x=0; $x<count($nominas); $x++){
					?>
					<li class="events">
						<a style="min-width: 130px; width: auto;" target="_blank" href="/archivos/nominas/<?php echo $nominas[$x]["nomina"]; ?>">
							<span class="shortcuts-icon iconfa-list-alt" style="font-size: 40px;"></span>
							<span class="shortcuts-label" style="text-align: center;"><?php echo $nominas[$x]["nombre"]; ?></span>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		?>
<?php if($editar){ ?>
	</div>
</div>
<?php
}
if($editar){
	?>
	<!-- MODAL -->
	<div id="modal_box" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="titulo_mod">TITULO</h3>
		</div>
		<div class="modal-body">
			<div class="alert alert-block  alert-error" id="err">
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
}else{
	$gene->showMessage("Todavía no se ha añadido ningún dato histórico", "info");
}
pie();


function showInput($datos, $label, $name){
    $default = $datos;
	$color = "";

	/*Hay POSTS*/
	$msg = "";
	if(isset($_POST[$name]) && ($_POST[$name] != $datos)){
		$default = $_POST[$name];
		$color = "error";
		$msg = '<span class="help-inline">Porfavor corrige el error</span>';
	}

	?>
	<div class="par control-group <?php echo $color; ?>">
		<label class="control-label" for="<?php echo $name; ?>"><?php echo $label; ?></label>
		<div class="controls">
			<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="input-large" placeholder="<?php echo $label; ?>" value="<?php echo $default; ?>" />
			<?php echo $msg; ?>
		</div>
	</div>
	<?php
}

function saveAndUpload($sql, $data){
	$rows = $sql->runInsert("consumidor_historico", $data);
	return $rows;
}
?>