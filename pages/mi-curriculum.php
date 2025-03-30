<?php
cabecera($sql, $cusers, $gene, 2);
require_once('class/cUpload_file.php');
$upload = new cUpload_file($cusers, $gene, $sql);
?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<?php
	/*TODEL*/
	if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("9658".$_GET["dele"]."_ToDelete")){
		$data = $sql->runSelect("consumidor_archivos", "id = '".$sql->fstr($_GET["dele"])."' AND id_consumidor = '".$cusers->id_perfil."'", "enlace");
		if(isset($data[0]["enlace"])){
			unlink("archivos/".$data[0]["enlace"]);
			$rows = $sql->runDelete("consumidor_archivos", "id = '".$sql->fstr($_GET["dele"])."' AND id_consumidor = '".$cusers->id_perfil."'");
			if($rows>0){
				$gene->redirect("mi-curriculum");
			}
		}
	}
	$err = Array();
	if(isset($_POST["guardarprofile"])){
		if(isset($_POST["titulo"]) && strlen($_POST["titulo"])>0) $data["titulo"] = $sql->fstr($_POST["titulo"]); else $err[0] = "Ha de introducir un título";
		if(isset($_POST["tipo_documento"]) && strlen($_POST["tipo_documento"])>0) $data["tipo_documento"] = $sql->fstr($_POST["tipo_documento"]); else $err[1] = "Ha de indicar el tipo de documento";
		if(count($err)>0){
			$msg = "";
			for($x=0; $x<=2; $x++){
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
					$rows = saveAndUpload($upload, $gene, $sql, $data);	
				}else{
					$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
					$gene->logaction($cusers->id, "Datos: ERROR AL GUARDAR EL ID del PERFIL DEL CONSUMIDOR");
				}
			}else{
				$data["id_consumidor"] = $cusers->id_perfil;
				$rows = saveAndUpload($upload, $gene, $sql, $data);
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
	<h4 class="widgettitle">Añadir nuevo documento</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="mi-curriculum" enctype="multipart/form-data">
				<?php
					showInput("", "Título identificador", "titulo");
				?>
				<div class="par control-group">
					<label>Tipo de documento</label>
					<span class="field">
					<select name="tipo_documento" class="uniformselect">
						<option value="curriculum">Curriculum</option>
						<option value="carta presentacion">Carta de presentación</option>
					</select>
					</span>
				</div>
				<div class="par control-group">
					<label>Subir archivo</label>
					<div class="fileupload fileupload-new" data-provides="fileupload">
						<div class="input-append">
							<div class="uneditable-input span3">
								<i class="iconfa-file fileupload-exists"></i>
								<span class="fileupload-preview"></span>
							</div>
							<span class="btn btn-file"><span class="fileupload-new">Selecciona archivo</span>
							<span class="fileupload-exists">Cambiar</span>
							<input type="file" name="archivo_upl" /></span>
							<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Quitar</a>
						</div>
					</div>
				</div>
				<p class="stdformbutton">
					<input type="submit" name="guardarprofile" class="btn btn-primary" value="Subir archivo" />
				</p>
		</form>
	</div><!--widgetcontent-->
</div><!--widget--> 
<br />
<br />

<?php
$rows = $sql->runSelect("consumidor_archivos", "id_consumidor = '".$cusers->id_perfil."'", "*", "fecha DESC", false, false, false, false, false);
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
<div class="widgetbox box-inverse">
	<h4 class="widgettitle">Mis archivos subidos</h4>
	<div class="widgetcontent wc1">
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="js/jquery.gdocsviewer.min.js"></script> 
		<script>
		jQuery(document).ready(function(){
			jQuery(".modales").click(function(){
				var id = jQuery(this).attr("id");
				var token = jQuery(this).attr("token");
				var tipo = jQuery(this).attr("tipo");
				if(tipo == "see"){
					jQuery('#paradel').css("display", "none");
					jQuery('#err').css("display", "none");
					jQuery('#embed').css("display", "inherit");
					jQuery('#embed').attr("href", token);
					jQuery('.gdocsviewer').css("display", "");
					jQuery('#embed').gdocsViewer({ width: "100%", height: 350 });
				}else{
					jQuery('#paradel').css("display", "");
					jQuery('#err').css("display", "inherit");
					jQuery('#embed').css("display", "none");
					jQuery('.gdocsviewer').css("display", "none");
					jQuery('#paradel').attr("href", "?dele="+id+"&token="+token);
				}
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
					<th class="row_cus">Fecha</th>
					<th class="row_cus">Identificador</th>
					<th class="row_cus">Tipo de documento</th>
					<th class="row_cus">Ver</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus"><?php echo date("d/m/Y", strtotime($rows[$x]["fecha"])); ?></td>
						<td class="row_cus"><?php echo $rows[$x]["titulo"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["tipo_documento"]; ?></td>
						<td class="row_cus">
							<a href="#modal_box" data-toggle="modal" class="modales" tipo="see" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Archivo: ".$rows[$x]["titulo"]." ".date("d/m/Y", strtotime($rows[$x]["fecha"])); ?>" token="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/archivos/<?php echo $rows[$x]["enlace"]; ?>">
							Ver
							</a>
						</td>
						<td class="row_cus">
							<a href="#modal_box" data-toggle="modal" class="modales" tipo="" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Borrar el registro: ".$rows[$x]["titulo"]." ".date("d/m/Y", strtotime($rows[$x]["fecha"])); ?>" token="<?php echo md5("9658".$rows[$x]["id"]."_ToDelete"); ?>">
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
		<div class="alert alert-block  alert-error" id="err" style="display: none;">
			  <h4>BORRAR: </h4>
			  <p style="margin: 8px 0">¿Estás seguro de borrar el registro?, no habrá marcha atrás</p>
		</div>
		<a id="embed" href=""></a>
	</div>
	<div class="modal-footer">
		<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
		<a href="#" style="display: none;" id="paradel" class="btn btn-danger btn-rounded"><i class="iconfa-trash icon-white"></i> Borrar</a>
	</div>
</div>
<?php
}else{
	$gene->showMessage("Todavía no se ha subido ningún archivo", "info");
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

function saveAndUpload($upload, $gene, $sql, $data){
	/*UPLOAD FILE*/
	if(isset($_FILES['archivo_upl']['name']) && $_FILES['archivo_upl']['name']!="" && $data["id_consumidor"] != 0){
		$arr = $upload->UploadArchivo("archivo_upl", "archivos/", str_pad($data["id_consumidor"], 7, "0", STR_PAD_LEFT)."_".date("YmdHis"));
		if($arr["errori"] > 0){
			$error_arrayi=Array(0=>"Archivo subida correctamente",
								1=>"Formato del archivo incompatible (Doc, Docx, Pdf, Odt, txt)",
								2=>"El tamaño del archivo no puede ser superior a 3MB",
								3=>"Resolución de la imagen demasiado grande.(Máximo 3300x3300 px)",
								4=>"Al subir el archivo, porfavor vuelve a intentarlo.",
								5=>"No se ha seleccionado ningún archivo");
			$gene->showMessage($error_arrayi[$arr["errori"]]);
			$rows = 0;
		}else{
			$data["enlace"] = $arr["filename"];
			$rows = $sql->runInsert("consumidor_archivos", $data);
		}
	}
	return $rows;
}
?>