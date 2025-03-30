<?php
require_once('class/cUpload_file.php');
$upload = new cUpload_file($cusers, $gene, $sql);
cabecera($sql, $cusers, $gene, 16);
?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<?php
	/*TODEL*/
	$sector = $sql->runSelect("empresario e LEFT JOIN cnae ON e.cnae = cnae.id", "e.id = '".$cusers->id_perfil."'", "IF(cnae.porcentaje_admin IS NULL, cnae.porcentaje_auto, cnae.porcentaje_admin) as porcentaje, cnae.id");
	if(isset($sector[0]["id"])) $cnae_e = $sector[0]["id"]; else $cnae_e = "";
	$sector = getValue("porcentaje", $sector);
	
	if(isset($_GET["dele"]) && isset($_GET["token"]) && is_numeric($_GET["dele"]) && $_GET["token"] == md5("5125".$_GET["dele"]."_ToDelete")){
		$rows = $sql->runDelete("empresario_historico", "id = '".$sql->fstr($_GET["dele"])."'");
		if($rows>0){
			$gene->redirect("empresa-datos");
		}
	}
	$err = Array();
	if(isset($_POST["guardarprofile"])){
		
		/*autocalculados*/
		if(isset($_POST["ventas_totales"])) $data["ventas_totales"] = $sql->fstr($_POST["ventas_totales"]);
		if(isset($_POST["porc_sector"])) $data["porc_sector"] = $sql->fstr($_POST["porc_sector"]);
		if(isset($_POST["valor_social"])) $data["valor_social"] = $sql->fstr($_POST["valor_social"]);
		if(isset($_POST["base_imponible"])) $data["base_imponible"] = $sql->fstr($_POST["base_imponible"]);
		if(isset($_POST["iva"])) $data["iva"] = $sql->fstr($_POST["iva"]);
	
		/*Usuario*/
		if(isset($_POST["valor_empresarial"]) && is_numeric($_POST["valor_empresarial"])) $data["valor_empresarial"] = $sql->fstr($_POST["valor_empresarial"]); else $err[0] = "El valor empresarial ha de ser un número";
		if(isset($_POST["valor_social_p"]) && is_numeric($_POST["valor_social_p"]) && $_POST["valor_social_p"]>=$sector) $data["valor_social_p"] = $sql->fstr($_POST["valor_social_p"]); else $err[1] = "El porcentaje del valor social ha de ser IGUAL o SUPERIOR al % del Sector (".$sector."%)";
		if(isset($_POST["iva_p"]) && is_numeric($_POST["iva_p"])) $data["iva_p"] = $sql->fstr($_POST["iva_p"]); else $err[2] = "el % del IVA ha de ser un número";
		if(isset($_POST["resto_valor_social"]) && is_numeric($_POST["resto_valor_social"])) $data["resto_valor_social"] = $sql->fstr($_POST["resto_valor_social"]); else $err[4] = "El resto del valor social ha de ser un número";
		if(isset($_POST["remanente_valor_social"]) && is_numeric($_POST["remanente_valor_social"])) $data["remanente_valor_social"] = $sql->fstr($_POST["remanente_valor_social"]); else $err[5] = "El remanente del valor social ha de ser un número";
		if(isset($_POST["num_trabajadores"]) && is_numeric($_POST["num_trabajadores"])) $data["num_trabajadores"] = $sql->fstr($_POST["num_trabajadores"]); else $err[6] = "El numero de trabajadores ha de ser un número";
		if(isset($_POST["coste_sal_medio"]) && is_numeric($_POST["coste_sal_medio"])) $data["coste_sal_medio"] = $sql->fstr($_POST["coste_sal_medio"]); else $err[7] = "El coste salarial medio ha de ser un número";
		
		
		if(isset($_POST["date"]) && $_POST["date"] != "") $data["fecha"] = $sql->fstr($_POST["date"]); else $err[3] = "Ha de introducir una fecha";
		if(isset($data["fecha"])){
			$sep = explode(" ", $data["fecha"]);
			if(count($sep) == 2){
				$meses = Array("Enero" => "01",
							   "Febrero" => "02",
							   "Marzo" => "03",
							   "Abril" => "04",
							   "Mayo" => "05",
							   "Junio" => "06",
							   "Julio" => "07",
							   "Agosto" => "08",
							   "Septiembre" => "09",
							   "Octubre" => "10",
							   "Noviembre" => "11",
							   "Diciembre" => "12");
				if(isset($meses[$sep[0]])){
					if($sep[1]>= 1970 && $sep[1] < 2100){
						$data["fecha"] = $sep[1]."-".$meses[$sep[0]]."-01 00:00:00";
						/*Existe esa fecha grabada??*/
						$ckh = $sql->runSelect("empresario_historico", "id_empresario = '".$cusers->id_perfil."' AND fecha = '".$data["fecha"]."'");
						if(count($ckh)>0){
							$err[3] = "La fecha Seleccionada ya ha sido usada, por favor cambiela<br />Sí desea modificar los datos, primero debe eliminar el registro referente a esa fecha.";
						}
					}else{
						$err[3] = "Año no válido";
					}
				}else{
					$err[3] = "Mes no válido";
				}
			}else{
				$err[3] = "La fecha no es válida";
			}
		}
		$dateerr = "";
		if(isset($err[3])){
			$dateerr = "error";
		}
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
			$rows = 0;
			/*SET LAS QUE FALTAN*/
			$data["coste_sal_total"] = $data["num_trabajadores"] * $data["coste_sal_medio"];
			$data["aplicado_empresa"] = round(($data["valor_empresarial"] * 100 / $data["coste_sal_total"]), 2)."%";
			//% del Valor empresarial que representa el coste salarial total
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
					$rows = $sql->runInsert("empresario_historico", $data);
				}else{
					$gene->showMessage("Ha ocurrido un error al actualizar el id del perfil");
					$gene->logaction($cusers->id, "Datos: ERROR AL GUARDAR EL ID del PERFIL DEL EMPRESARIO");
				}
			}else{
				$data["id_empresario"] = $cusers->id_perfil;
				//print_r($_POST);
				$rows = $sql->runInsert("empresario_historico", $data);
			}
			if($rows != 0){
				$gene->showMessage("Datos grabados correctamente", "success");
				/*AHORA ACTUALIZAMOS EL % del SECTOR...*/
				if($cnae_e != ""){
					$p_auto = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", "e.cnae = '".$cnae_e."'", "IF(SUM(coste_sal_total) IS NULL, 0, SUM(coste_sal_total)) AS coste_sal_total, IF(SUM(ventas_totales) IS NULL, 0, SUM(ventas_totales)) AS ventas_totales");
					$p_auto = $p_auto[0];
					$saveauto["porcentaje_auto"] = $p_auto["coste_sal_total"]*100/$p_auto["ventas_totales"];
					$sql->runUpdate("cnae", $saveauto, "id = '".$cnae_e."'");
					
					/*Sacamos de nuevo la variable*/
					$sector = $sql->runSelect("empresario e LEFT JOIN cnae ON e.cnae = cnae.id", "e.id = '".$cusers->id_perfil."'", "IF(cnae.porcentaje_admin IS NULL, cnae.porcentaje_auto, cnae.porcentaje_admin) as porcentaje, cnae.id");
					$sector = getValue("porcentaje", $sector);
				}
				unset($_POST);
			}else{
				$gene->showMessage("Ha ocurrido un error al grabar los datos");
			}
		}
	}
	
?>
<form id="form1" class="stdform" method="post" action="empresa-datos">
	<div class="widgetbox box-inverse span5">
		<h4 class="widgettitle">Modificar Datos</h4>
		<div class="widgetcontent wc1">
					<label class="control-label" for="datepickercool">Mes - Año</label>
					<div class="controls input-append <?php echo $dateerr; ?>">
						<input id="datepickercool" type="text" name="date" class="input-large date-picker" />
						<span class="add-on">P</span>
					</div> 
					<?php
						//showInput($datos[0], "Mes - Año", "datepickercool", "text", "input-large date-picker");
						showInput($datos[0], "Valor empresarial", "valor_empresarial", "text", "input-large", "€");
						showInput($datos[0], "% Valor Social", "valor_social_p", "text", "input-large", "%", $err);
						showInput($datos[0], "% IVA", "iva_p", "text", "input-large", "%");
						showInput($datos[0], "Resto Valor Social", "resto_valor_social", "text", "input-large", "€");
						showInput($datos[0], "Remanente Valor Social", "remanente_valor_social", "text", "input-large", "€");
						showInput($datos[0], "Nº Trabajadores", "num_trabajadores", "text", "input-large", "Tr");
						showInput($datos[0], "Coste Salarial Medio", "coste_sal_medio", "text", "input-large", "€");
					?>
					
					<p class="stdformbutton">
						<input type="submit" name="guardarprofile" class="btn btn-primary" value="Guardar Cambios" />
					</p>
		</div><!--widgetcontent-->
	</div><!--widget-->
	<div class="widgetbox box-info span5">
		<h4 class="widgettitle">Datos autocalculados</h4>
		<div class="widgetcontent">
			<?php
			$datos = $sql->runSelect("empresario_historico", "id_empresario = '".$cusers->id_perfil."'", "*", "fecha DESC");
			if(!isset($datos[0])) $datos[0] = Array();
			
			
			$ventas_totales = getValue("ventas_totales", $datos, 1);
			$valor_social = getValue("valor_social", $datos, 2);
			$base_imponible = getValue("base_imponible", $datos, 3);
			$iva = getValue("iva", $datos, 4);
			
			showDefault(seeNum2($ventas_totales), "Ventas Totales", "ventas_totales", "Valor Empresarial + Valor Social + IVA", "€");
			showDefault(seeNum2($sector), "%del Sector", "porc_sector", "% Según el INE del Sector", "%");
			showDefault(seeNum2($valor_social), "Valor Social", "valor_social", "Valor Empresarial x % del Valor Social", "€");
			showDefault(seeNum2($base_imponible), "Base Imponible", "base_imponible", "Valor Empresarial + Valor Social", "€");
			showDefault(seeNum2($iva), "IVA", "iva", "Base Imponible x % IVA", "€");
			?>
		</div>
	</div>
</form>
<br />
<div style="clear: both;"></div>
<br />

<?php
$rows = $sql->runSelect("empresario_historico", "id_empresario = '".$cusers->id_perfil."'", "*", "fecha DESC", false, false, false, false, false);

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
	<h4 class="widgettitle">Histórico</h4>
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
			jQuery('#datepickercool').datepicker( {
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: 'MM yy',
				onClose: function(dateText, inst) { 
					var month = jQuery("#ui-datepicker-div .ui-datepicker-month :selected").val();
					var year = jQuery("#ui-datepicker-div .ui-datepicker-year :selected").val();
					jQuery(this).datepicker('setDate', new Date(year, month, 1));
				}
			});
			/*Cambia el valor empresarial*/
			jQuery("#valor_empresarial").change(function() {
				jQuery('#valor_social').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#valor_social_p").val())/100)).toFixed(2));
				jQuery('#ventas_totales').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())+Number(jQuery("#iva").val())).toFixed(2));
				jQuery('#base_imponible').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())).toFixed(2));
			});
			
			/*cambia el valor social porcentaje*/
			jQuery("#valor_social_p").change(function() {
				jQuery('#valor_social').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#valor_social_p").val())/100)).toFixed(2));
				jQuery('#ventas_totales').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())+Number(jQuery("#iva").val())).toFixed(2));
				jQuery('#base_imponible').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())).toFixed(2));
			});
			
			/*cambia el valor social porcentaje*/
			jQuery("#iva_p").change(function() {
				jQuery('#iva').val((Number(jQuery("#base_imponible").val())*(Number(jQuery("#iva_p").val())/100)).toFixed(2));
				jQuery('#ventas_totales').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())+Number(jQuery("#iva").val())).toFixed(2));
			});
			
			/*Al principio Carga las VARS*/
			<?php
			if(count($err)>0){
				?>
				jQuery('#valor_social').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#valor_social_p").val())/100)).toFixed(2));
				jQuery('#ventas_totales').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())+Number(jQuery("#iva").val())).toFixed(2));
				jQuery('#base_imponible').val((Number(jQuery("#valor_empresarial").val())+Number(jQuery("#valor_social").val())).toFixed(2));
				jQuery('#iva').val((Number(jQuery("#base_imponible").val())*(Number(jQuery("#iva_p").val())/100)).toFixed(2));
				<?php
			}
			?>
			
			
			/*
			ventas_totales", "Valor Empresarial + Valor Social + IVA", "€");
			valor_social", "Valor Empresarial x % del Valor Social", "€");
			base_imponible", "Valor Empresarial + Valor Social", "€");
			iva", "Base Imponible x % IVA", "€");
			*/
			
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
					<th class="row_cus">Ventas Tot.</th>
					<th class="row_cus">V. Empresarial</th>
					<th class="row_cus">% Sector</th>
					<!--<th colspan="2">Valor Social</th>-->
					<th class="row_cus">% V.S.</th>
					<th class="row_cus">V.Social</th>
					<th class="row_cus">Base Imponible</th>
					<!--<th colspan="2">IVA</th>-->
					<th class="row_cus">% IVA</th>
					<th class="row_cus">IVA</th>
					<th class="row_cus">Resto V.S.</th>
					<th class="row_cus">Remanente V.S.</th>
					<th class="row_cus">Trab</th>
					<th class="row_cus">Coste Sal. M.</th>
					<th class="row_cus">Coste Sal. T.</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$esto = Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
				$poresto = Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
				$poresto2 = Array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus"><span style="display: none;"><?php echo str_pad($x, 5, "0", STR_PAD_LEFT); ?></span><?php echo str_replace($esto, $poresto2, date("M Y", strtotime($rows[$x]["fecha"]))); ?></td>
						<td class="row_cus"><?php echo seeNum($rows[$x]["ventas_totales"]); ?> €</td>
						
						<td class="row_cus"><?php echo seeNum($rows[$x]["valor_empresarial"]); ?> €</td>
						
						<td class="row_cus"><?php echo $rows[$x]["porc_sector"]; ?> %</td>
						
						<td class="row_cus"><?php echo $rows[$x]["valor_social_p"]; ?> %</td>
						<td class="row_cus"><?php echo seeNum($rows[$x]["valor_social"]); ?> €</td>
						
						<td class="row_cus"><?php echo seeNum($rows[$x]["base_imponible"]); ?> €</td>
						
						<td class="row_cus"><?php echo $rows[$x]["iva_p"]; ?> %</td>
						<td class="row_cus"><?php echo seeNum($rows[$x]["iva"]); ?> €</td>
						
						<td class="row_cus"><?php echo seeNum($rows[$x]["resto_valor_social"]); ?> €</td>
						
						<td class="row_cus"><?php echo seeNum($rows[$x]["remanente_valor_social"]); ?> €</td>
						
						<td class="row_cus"><?php echo $rows[$x]["num_trabajadores"]; ?></td>
						
						<td class="row_cus"><?php echo seeNum($rows[$x]["coste_sal_medio"]); ?> €</td>
						
						<td class="row_cus" style="font-weight: bold;"><?php echo seeNum($rows[$x]["coste_sal_total"]); ?> €</td>
						
						<td class="row_cus">
							<a href="#modal_box" data-toggle="modal" class="modales" id="<?php echo $rows[$x]["id"]; ?>" titulo="<?php echo "Borrar el registro: ".str_replace($esto, $poresto, date("M Y", strtotime($rows[$x]["fecha"]))); ?>" token="<?php echo md5("5125".$rows[$x]["id"]."_ToDelete"); ?>">
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
function showDefault($valor, $label, $name, $info, $sim){
	?>
	<div class="par control-group info">
		<label for="<?php echo $name; ?>" class="control-label"><?php echo $label; ?></label>
		<div class="controls input-append">
			<input readonly type="text" class="span2" id="<?php echo $name; ?>" name="<?php echo $name; ?>" placeholder="<?php echo $label; ?>" value="<?php echo $valor; ?>">
			<span class="add-on"><?php echo $sim; ?></span>
		</div>
			<span class="help-inline"><?php echo $info; ?></span>
	</div>
	<?php
}
function seeNum($num){
	return number_format($num, 2, ",", ".");
}
function seeNum2($num){
	return number_format($num, 2, ".", "");
}
function getValue($name, $array, $formula = ""){
	if(isset($array[0][$name]) && $array[0][$name] != ""){
		return $array[0][$name];
	}else{
		if($formula == ""){
			return "100.00";
		}elseif($formula == 1){
			//Valor Empresarial + Valor Social + IVA
			$ve = getValue("valor_empresarial", $array);
			$vs = getValue("valor_social", $array, 2);
			$iva = getValue("iva", $array, 4);
			return ($ve+$vs+$iva);
		
		}elseif($formula == 2){
			//	Valor Empresarial x % del Valor Social
			$ve = getValue("valor_empresarial", $array);
			$vsp = (getValue("valor_social_p", $array)/100);
			return ($ve*$vsp);
		
		}elseif($formula == 3){
			//	Valor Empresarial + Valor Social
			$ve = getValue("valor_empresarial", $array);
			$vs = getValue("valor_social", $array, 2);
			return ($ve+$vs);
		
		}elseif($formula == 4){
			//	Base Imponible x % IVA
			$bi = getValue("base_imponible", $array, 3);
			$iva_p = (getValue("iva_p", $array)/100);
			return ($bi*$iva_p);
		
		}else{
			return "N/A";
		}
	}
}
?>