<?php
error_reporting(0);
session_start();
require_once('class/DBclass.php');
require_once('class/cGeneral.php');
require_once('class/cUsers.php');
$sql = new Database();
$sql->connect();
$cusers = new cUsers($sql);
$gene = new cGeneral($sql, "", $cusers, "es");

$word1 = "88Jhs_lo";
$word2 = "_LSKu322h";

if(isset($_POST["id"]) && is_numeric($_POST["id"])){
	/*SCRIPTS*/
	?>
	<script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".edit").unbind("click").click(function () {
				jQuery("#cargando").css("display", "inline");
				
				var id = "<?php echo $_POST["id"]; ?>";
				var action = "edit";
				var ide = jQuery(this).attr("ide");
				var token = jQuery(this).attr("token");
				
				jQuery.post("m-historial-trabajadores.php", { id: id, action: action, ide: ide, token: token }, function(data){
					jQuery(".modal-body").html(data);
					jQuery("#cargando").css("display", "none");
					return false;
				});
				
			});
			
			jQuery(".delete").unbind("click").click(function () {
				jQuery("#cargando").css("display", "inline");
				
				var id = "<?php echo $_POST["id"]; ?>";
				var action = "delete";
				var ide = jQuery(this).attr("ide");
				var token = jQuery(this).attr("token");
				
				jQuery.post("m-historial-trabajadores.php", { id: id, action: action, ide: ide, token: token }, function(data){
					jQuery(".modal-body").html(data);
					jQuery("#cargando").css("display", "none");
					return false;
				});
				
			});
			
			jQuery(".nuevo").unbind("click").click(function () {
				jQuery("#cargando").css("display", "inline");
				var id = "<?php echo $_POST["id"]; ?>";
				jQuery.post("m-historial-trabajadores.php", { id: id }, function(data){
					jQuery(".modal-body").html(data);
					jQuery("#cargando").css("display", "none");
					return false;
				});
				
			});
			jQuery("#btnguardar").unbind("click").click(function () {
				jQuery("#cargando").css("display", "inline");
				var id = "<?php echo $_POST["id"]; ?>";
				var iden = jQuery(this).attr("iden");
				var periodo = jQuery("#periodo").val();
				var categoria = jQuery("#categoria").val();
				
				
				jQuery.post("m-historial-trabajadores.php", { id: id, iden: iden, periodo: periodo, categoria: categoria }, function(data){
					jQuery(".modal-body").html(data);
					jQuery("#cargando").css("display", "none");
					return false;
				});
				
			});
			
		});
	</script>
	<?php
	
	/*Hay cosas que guardar?*/
	if(isset($_POST["iden"]) && isset($_POST["periodo"]) && isset($_POST["categoria"])){
		$toedit["anyo"] = $sql->fstr($_POST["periodo"]);
		$toedit["historico"] = $sql->fstr($_POST["categoria"]);
		$toedit["tipo_historico"] = "puesto";
		if(is_numeric($_POST["iden"])){
			/*edit*/
			$sql->runUpdate("consumidor_historico", $toedit, "id = '".$sql->fstr($_POST["iden"])."' AND id_consumidor = '".$sql->fstr($_POST["id"])."' AND id_empresa = '".$sql->fstr($cusers->id_perfil)."'");
		}elseif($_POST["iden"] == "nuevo"){
			/*new*/
			$toedit["id_consumidor"] = $sql->fstr($_POST["id"]);
			$toedit["id_empresa"] = $sql->fstr($cusers->id_perfil);
			$sql->runInsert("consumidor_historico", $toedit);
		}
		unset($toedit);
	}
	
	/*Hay cosas que borrar?*/
	if(isset($_POST["action"]) && $_POST["action"] == "delete" && isset($_POST["ide"]) && is_numeric($_POST["ide"]) && isset($_POST["token"]) && $_POST["token"] == md5($word2.$_POST["ide"].$word1)){
		$sql->runDelete("consumidor_historico", "id = '".$sql->fstr($_POST["ide"])."' AND id_consumidor = '".$sql->fstr($_POST["id"])."' AND id_empresa = '".$sql->fstr($cusers->id_perfil)."'");
	}
	
	/*Sacamos el formulario en principio de añadir*/
	if(isset($_POST["action"]) && $_POST["action"] == "edit" && isset($_POST["ide"]) && is_numeric($_POST["ide"]) && isset($_POST["token"]) && $_POST["token"] == md5($word1.$_POST["ide"].$word2)){
		/*Editamos*/	
		$rows = $sql->runSelect("consumidor_historico", "id_consumidor = '".$sql->fstr($_POST["id"])."' AND id_empresa = '".$sql->fstr($cusers->id_perfil)."' AND id = '".$sql->fstr($_POST["ide"])."'");
		if(count($rows)==1){
			$html = "<h3>Editando <span class='nuevo btn btn-success btn-rounded'>Nuevo</span></h3>";
			$action = "Guardar";
			$accion = "editar";
			$rows = $rows[0];
			$iden = $rows["id"];
		}else{
			$html = "<h3>Añadir nuevo registro</h3>";
			$action = "Añadir";
			$accion = "nuevo";
			$iden = "nuevo";
			$rows["historico"] = "";
			$rows["anyo"] = "";
		}
	}else{
		$html = "<h3>Añadir nuevo registro</h3>";
		$action = "Añadir";
		$accion = "nuevo";
		$iden = "nuevo";
		$rows["historico"] = "";
		$rows["anyo"] = "";
	}
	?>
	<div class="par control-group">
		<div class="controls">
			<?php echo $html; ?>
			<table>
				<tr>
					<td>Categoría</td>
					<td>Periodo</td>
					<td></td>
				</tr>
				<tr>
					<td><input type="text" name="categoria" id="categoria" placeholder="Categoría" value="<?php echo $rows["historico"]; ?>" /></td>
					<td><input type="text" name="periodo" id="periodo" placeholder="Periodo" value="<?php echo $rows["anyo"]; ?>" /></td>
					<td><span style="height: 18px; margin-top: -5px;" id="btnguardar" accion="<?php echo $accion; ?>" iden="<?php echo $iden; ?>" class="btn btn-default btn-rounded"><?php echo $action; ?></span></td>
				</tr>
			</table>
		</div>
	</div>
	<?php


	$rows = $sql->runSelect("consumidor_historico", "id_consumidor = '".$sql->fstr($_POST["id"])."' AND id_empresa = '".$cusers->id_perfil."' AND tipo_historico = 'puesto'", "*", "anyo DESC, fecha DESC", false, false, false, false, false);
	if(count($rows)>0){
		?>
		<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/responsive-tables.js"></script>
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
					<!-- <th class="row_cus">Fecha Registro</th> -->
					<th class="row_cus">Categoría</th>
					<th class="row_cus">Periodo</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<!-- <td class="row_cus"><?php //echo date("d/m/Y", strtotime($rows[$x]["fecha"])); ?></td> -->
						<td class="row_cus"><?php echo $rows[$x]["historico"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["anyo"]; ?></td>
						<td class="row_cus">
							<span style="color: white; height: 15px; padding-top: 4px;" class="edit btn btn-success btn-rounded btn-small" ide="<?php echo $rows[$x]["id"]; ?>" token="<?php echo md5($word1.$rows[$x]["id"].$word2); ?>">
							<i class="iconfa-edit"></i> Editar</a>
							</span>
							<span style="color: white; height: 15px; padding-top: 4px;" class="delete btn btn-danger btn-rounded btn-small" ide="<?php echo $rows[$x]["id"]; ?>" token="<?php echo md5($word2.$rows[$x]["id"].$word1); ?>">
							<i class="iconfa-trash"></i> Borrar</a>
							</span>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<br />
		<br />
		<?php
		/*Veamos si tiene nomina y eso... formulario que recarga la pagina y cierra el modal*/
		$datos = $sql->runSelect("empresario_consumidor", "id_empresario = '".$cusers->id_perfil."' AND id_consumidor = '".$sql->fstr($_POST["id"])."'");
		if(count($datos)==1 && $datos[0]["nomina"] != ""){
			echo "La última nomina que se le asigno a este empleado es del dia: ".date("d/m/Y", strtotime($datos[0]["nomina_fecha"]))."&nbsp;&nbsp;&nbsp;<a href='/archivos/nominas/".$datos[0]["nomina"]."' target='_blank' class='btn btn-warning'>Ver</a>";
		}else{
			echo "Todavía no se ha subido ninguna nómina para el empleado";
		}
		?>
		<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
		<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
		<form method="POST" action="empresa-trabajadores" enctype="multipart/form-data">
			<div class="par control-group">
				<label>Subir nueva nómina</label>
				<div class="fileupload fileupload-new" data-provides="fileupload">
					<div class="input-append">
						<div class="uneditable-input span3">
							<i class="iconfa-file fileupload-exists"></i>
							<span class="fileupload-preview"></span>
						</div>
						<span class="btn btn-file"><span class="fileupload-new">Selecciona archivo</span>
						<span class="fileupload-exists">Cambiar</span>
						<input type="file" name="nomina_upl" /></span>
						<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Quitar</a>
					</div>
					<input type="hidden" name="id_nominado" id="id_nominado" value="<?php echo $_POST["id"] ?>" />
					<input style="color: white; height: 30px; margin-top: -10px;" type="submit" name="subenomina" class="btn btn-primary" value="Subir nomina" />
				</div>
			</div>
		</form>
		<?php
	}else{
		$gene->showMessage("El trabajador todavía no tiene historial con la empresa", "info");
	}
}else{
	$gene->showMessage("No se ha encontrado el historial para el trabajador seleccionado", "info");
}
?>