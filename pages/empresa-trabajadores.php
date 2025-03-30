<?php
cabecera($sql, $cusers, $gene, 18);

$word1 = "Jsheyr6_";
$word2 = "_kkJhsy";
?>

<div class="widgetbox box-warning">
	<h4 class="widgettitle">Búsqueda de trabajadores</h4>
	<div class="widgetcontent">
		<?php
		if(isset($_GET["dni"]) && strlen($_GET["dni"])>5){
			/*Buscamos*/
			$dni = $sql->fstr($_GET["dni"]);
			$trab = $sql->runSelect("consumidor", "dni LIKE '".$dni."%' AND id NOT IN (SELECT id_consumidor FROM empresario_consumidor WHERE id_empresario = '".$cusers->id_perfil."')");
			if(count($trab)>0){
				/*Saca la tabla con los datos y boton Añadir!*/
				?>
				<table id="dyntable" class="table table-bordered responsive">
					<thead>
						<tr>
							<th class="row_cus">Nombre</th>
							<th class="row_cus">Apellidos</th>
							<th class="row_cus">DNI/NIF</th>
							<th class="row_cus">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php
						for($x=0; $x<count($trab); $x++){
							?>
							<tr>
								<td class="row_cus" style="font-weight: bold;"><?php echo $trab[$x]["nombre"]; ?></td>
								<td class="row_cus" style="font-weight: bold;"><?php echo $trab[$x]["apellidos"]; ?></td>
								<td class="row_cus" style="font-weight: bold;"><?php echo $trab[$x]["dni"]; ?></td>
								<td class="row_cus">
									<a style="color: white;" href="?add=<?php echo $trab[$x]["id"]; ?>&sectoken=<?php echo sha1($word2.$trab[$x]["id"].$word1); ?>" class="btn btn-success btn-rounded">
									<i class="iconfa-plus"></i> Añadir a la empresa</a>
									</a>
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
			}else{
				$gene->showMessage("La búsqueda no ha producido resultados", "info");
			}
		}elseif(isset($_GET["add"]) && is_numeric($_GET["add"]) && isset($_GET["sectoken"]) && $_GET["sectoken"] == sha1($word2.$_GET["add"].$word1)){
			/*Add people to company*/
			$datatoadd["id_empresario"] = $cusers->id_perfil;
			$datatoadd["id_consumidor"] = $sql->fstr($_GET["add"]);
			$sql->runInsert("empresario_consumidor", $datatoadd);
			unset($datatoadd);
			$gene->redirect("empresa-trabajadores");
		}elseif(isset($_GET["remove"]) && is_numeric($_GET["remove"]) && isset($_GET["sectoken"]) && $_GET["sectoken"] == sha1($word1.$_GET["remove"].$word2)){
			/*Delete people from company*/
			$sql->runDelete("empresario_consumidor", "id_consumidor = '".$sql->fstr($_GET["remove"])."' AND id_empresario = '".$cusers->id_perfil."'");
			$gene->redirect("empresa-trabajadores");
		}
		?>
		<form method="GET" action="empresa-trabajadores">
			<div class="par control-group">
				<label class="control-label" for="dni">DNI / NIF del trabajador</label>
				<div class="controls">
					<input type="text" name="dni" id="dni" placeholder="DNI / NIF del trabajador" value="<?php if(isset($_GET["dni"])) echo $_GET["dni"]; ?>" />
					<input style="height: 30px; margin-top: -5px;" type="submit" class="btn btn-default btn-rounded" value="Buscar" />
				</div>
			</div>
		</form>
	</div>
</div>
<?php
/*VEMOS A VER DE SUBIR LA NOMINA!*/
if(isset($_FILES['nomina_upl']['name']) && $_FILES['nomina_upl']['name']!="" && isset($_POST["id_nominado"]) && is_numeric($_POST["id_nominado"])){
	require_once('class/cUpload_file.php');
	$upload = new cUpload_file($cusers, $gene, $sql);
	$arr = $upload->UploadArchivo("nomina_upl", "archivos/nominas/", str_pad($_POST["id_nominado"], 7, "0", STR_PAD_LEFT)."_".date("YmdHis"));
	if($arr["errori"] > 0){
		$error_arrayi=Array(0=>"Archivo subido correctamente",
							1=>"Formato del archivo incompatible (Doc, Docx, Pdf, Odt, txt)",
							2=>"El tamaño del archivo no puede ser superior a 3MB",
							3=>"Resolución de la imagen demasiado grande.(Máximo 3300x3300 px)",
							4=>"Al subir el archivo, porfavor vuelve a intentarlo.",
							5=>"No se ha seleccionado ningún archivo");
		$gene->showMessage($error_arrayi[$arr["errori"]]);
		$rows = 0;
	}else{
		$data["nomina"] = $arr["filename"];
		$data["nomina_fecha"] = date("Y-m-d H:i:s");
		$rows = $sql->runUpdate("empresario_consumidor", $data, "id_empresario = '".$cusers->id_perfil."' AND id_consumidor = '".$sql->fstr($_POST["id_nominado"])."'");
		unset($data);
		if($rows == 1){
			$gene->showMessage("Nómina subida correctamente", "success");
		}else{
			$gene->showMessage("Ha ocurrido un error al guardar la nomina");
		}
	}
}


$rows = $sql->runSelect("empresario_consumidor ec INNER JOIN consumidor c ON c.id = ec.id_consumidor", "ec.id_empresario = '".$sql->fstr($cusers->id_perfil)."'", "c.*, ec.nomina, ec.nomina_fecha");
if(count($rows)>0){
?>
<div class="widgetbox box-info">
	<h4 class="widgettitle">Trabajadores de la empresa</h4>
	<div class="widgetcontent wc1">
		<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/responsive-tables.js"></script>
		<script type="text/javascript" src="js/jquery.validate.min.js"></script>
		<script>
			jQuery(document).ready(function(){
				jQuery(".modales").click(function(){
					var id = jQuery(this).attr("id");
					jQuery("#titulo_mod").text(jQuery(this).attr("titulo"));
					jQuery("#cargando").css("display", "inline");
					jQuery.post("m-historial-trabajadores.php", { id: id }, function(data){
						jQuery(".modal-body").html(data);
						jQuery("#cargando").css("display", "none");
						return false;
					});
				});
				
				jQuery('#dyntable').dataTable({
					"oLanguage": {
						"sProcessing":     "Procesando...",
						"sLengthMenu":     "Mostrar _MENU_ registros",
						"sZeroRecords":    "No se encontraron resultados",
						"sEmptyTable":     "Ningún dato disponible en esta tabla",
						"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
						"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
						"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
						"sInfoPostFix":    "",
						"sSearch":         "Buscar:",
						"sUrl":            "",
						"sInfoThousands":  ",",
						"sLoadingRecords": "Cargando...",
						"oPaginate": {
							"sFirst":    "Primero",
							"sLast":     "Último",
							"sNext":     "Siguiente",
							"sPrevious": "Anterior"
						},
						"oAria": {
							"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
							"sSortDescending": ": Activar para ordenar la columna de manera descendente"
						}
					},
					"bStateSave": true,
					"sPaginationType": "full_numbers"
				});	
			});
		</script>
		<style>
			.row_cus{
				text-align: center !important;
				padding: 2px !important;
				line-height: 15px !important;
				vertical-align: middle !important;
			}
			#dyntable_wrapper {
				overflow-x: auto;
			}
		</style>
		<table id="dyntable" class="table table-bordered responsive">
			<thead>
				<tr>
					<th class="row_cus">Nombre</th>
					<th class="row_cus">Apellidos</th>
					<th class="row_cus">DNI/NIF</th>
					<th class="row_cus">Nomina</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus" style="font-weight: bold;"><?php echo $rows[$x]["nombre"]; ?></td>
						<td class="row_cus" style="font-weight: bold;"><?php echo $rows[$x]["apellidos"]; ?></td>
						<td class="row_cus" style="font-weight: bold;"><?php echo $rows[$x]["dni"]; ?></td>
						<?php
						if($rows[$x]["nomina"] != ""){
							$nomin = "<a href='/archivos/nominas/".$rows[$x]["nomina"]."' target='_blank'>Subida: ".date("d/m/Y", strtotime($rows[$x]["nomina_fecha"]))."</a>";
						}else{
							$nomin = "Sín nómina";
						}
						
						?>
						<td class="row_cus" style="font-weight: bold;"><?php echo $nomin; ?></td>
						<td class="row_cus">
							<a href="#modal_box" style="color: white; height: 15px; padding-top: 4px;" data-toggle="modal" class="modales btn btn-info btn-rounded btn-small" titulo="<?php echo "Historial del trabajador: ".$rows[$x]["nombre"]." ".$rows[$x]["apellidos"]; ?>" id="<?php echo $rows[$x]["id"]; ?>">
							<i class="iconfa-book"></i> Historial</a>
							</a>
							&nbsp;&nbsp;&nbsp;&nbsp;
							<a style="color: white; height: 15px; padding-top: 4px;" href="?remove=<?php echo $rows[$x]["id"]; ?>&sectoken=<?php echo sha1($word1.$rows[$x]["id"].$word2); ?>" class="btn btn-danger btn-rounded btn-small">
							<i class="iconfa-trash"></i> Quitar de la empresa</a>
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
	<div id="cargando" style="width: 50px; margin: auto; display: none;"><img src="images/loading.gif" alt="cargando..." title="cargando..." /></div>
	<div class="modal-body"></div>
	<div class="modal-footer">
		<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
	</div>
</div>
<?php
}else{
	$gene->showMessage("Todavía no has asignado trabajadores a tu empresa", "info");
}
pie();
?>