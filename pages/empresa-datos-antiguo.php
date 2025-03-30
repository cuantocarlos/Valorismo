<?php
require_once('class/cUpload_file.php');
$upload = new cUpload_file($cusers, $gene, $sql);
cabecera($sql, $cusers, $gene, 16);
?>
<link rel="stylesheet" href="css/bootstrap-fileupload.min.css" type="text/css" />
<script type="text/javascript" src="js/bootstrap-fileupload.min.js"></script>
<?php

	$err = Array();
	if(isset($_POST["guardarprofile"])){
		if(isset($_POST["ventas_totales"]) && is_numeric($_POST["ventas_totales"])) $data["ventas_totales"] = $sql->fstr($_POST["ventas_totales"]); else $err[0] = "Las ventas totales ha de ser un número";
		if(isset($_POST["num_trabajadores"]) && is_numeric($_POST["num_trabajadores"])) $data["num_trabajadores"] = $sql->fstr($_POST["num_trabajadores"]); else $err[1] = "El numero de trabajadores ha de ser un número";
		if(isset($_POST["coste_sal_medio"]) && is_numeric($_POST["coste_sal_medio"])) $data["coste_sal_medio"] = $sql->fstr($_POST["coste_sal_medio"]); else $err[2] = "El coste salarial medio ha de ser un número";
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
			for($x=0; $x<=3; $x++){
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
			$data["valor_empresarial"] = $data["ventas_totales"] - $data["coste_sal_total"];
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
				$rows = $sql->runInsert("empresario_historico", $data);
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
	<h4 class="widgettitle">Modificar Datos</h4>
	<div class="widgetcontent wc1">
		<form id="form1" class="stdform" method="post" action="empresa-datos">
			<div class="par control-group <?php echo $dateerr; ?>">
				<label class="control-label" for="datepickercool">Mes - Año</label>
				<span class="field"><input id="datepickercool" type="text" name="date" class="date-picker" /></span>
			</div> 
				<?php
					$datos = $sql->runSelect("empresario_historico", "id_empresario = '".$cusers->id_perfil."'", "*", "fecha DESC");
					if(!isset($datos[0])) $datos[0] = Array();
					showInput($datos[0], "Ventas totales", "ventas_totales", "text", "input-large", "€");
					showInput($datos[0], "Nº Trabajadores", "num_trabajadores");
					showInput($datos[0], "Coste Salarial Medio", "coste_sal_medio", "text", "input-large", "€");
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
		<script>
		jQuery(document).ready(function(){
			//jQuery("#datepicker").datepicker();
			
			jQuery('#dyntable').dataTable({
				"oLanguage": {
					"sLengthMenu": "_MENU_ registros por página",
					"oPaginate": {
						"sPrevious": "Ant",
						"sNext": "Sig"
					}
				}
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
			
		});
		</script>
		<table id="dyntable" class="table table-bordered responsive">
			<thead>
				<tr>
					<th>Fecha</th>
					<th>Ventas Totales</th>
					<th>Nº Trabajadores</th>
					<th>Coste Salarial Medio</th>
					<th>Valor Empresarial</th>
					<th>Aplicado Empresa</th>
					<th>Coste Salarial Total</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$esto = Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
				$poresto = Array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td style="text-align: center;"><span style="display: none;"><?php echo str_pad($x, 5, "0", STR_PAD_LEFT); ?></span><?php echo str_replace($esto, $poresto, date("M Y", strtotime($rows[$x]["fecha"]))); ?></td>
						<td style="text-align: center;"><?php echo number_format($rows[$x]["ventas_totales"], 2, ",", "."); ?> €</td>
						<td style="text-align: center;"><?php echo $rows[$x]["num_trabajadores"]; ?></td>
						<td style="text-align: center;"><?php echo number_format($rows[$x]["coste_sal_medio"], 2, ",", "."); ?> €</td>
						<td style="text-align: center;"><?php echo number_format($rows[$x]["valor_empresarial"], 2, ",", "."); ?> €</td>
						<td style="text-align: center;"><?php echo $rows[$x]["aplicado_empresa"]; ?></td>
						<td style="text-align: center; font-weight: bold;"><?php echo number_format($rows[$x]["coste_sal_total"], 2, ",", "."); ?> €</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>
<?php
pie();

function showInput($datos, $label, $name, $type = "text", $class="input-large", $simbol = ""){
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