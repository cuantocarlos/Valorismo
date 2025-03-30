<?php
cabecera($sql, $cusers, $gene, 10);
if(isset($_GET["errtosave"]) && !isset($_POST["cnae"])) $_POST["cnae"] = $_GET["errtosave"];
if(isset($_GET["errtosave2"]) && !isset($_POST["provincia"])) $_POST["provincia"] = $_GET["errtosave2"];
?>
<script type="text/javascript" src="js/chosen.jquery.min.js"></script>
<script>
	jQuery(document).ready(function(){
		jQuery(".chzn-select").chosen();
	});
</script>
<form method="POST">
	<?php
	$cnaes = $sql->runSelect("cnae", "1=1", "id, CONCAT(id, ' - ', cnae) as valor");
	showSelect("Clasificación Nacional de Actividades Económicas (CNAE)", "cnae", $cnaes, false);
	$provincias = $sql->runSelect("provincias", "1=1", "id, provincia as valor, autonomia as valor2", "autonomia, provincia");
	showSelect("Área geográfica", "provincia", $provincias, true, "Toda españa");
	/*
	$emp = $sql->runSelect("empresario", "1=1", "id, CONCAT(cif, ' - ', nombre) as valor", "nombre, cif");
	showSelect("Empresas", "Empresas", $emp, true, "Todas");
	*/
	?>
</form>
<div style="clear: both;"></div>
<br /><br />
<?php
if(isset($_POST["cnae"]) && $_POST["cnae"] != ""){
	
	
	/*esta en BBDD?*/
	
	
	/*ESTA SETEADO PROVINCIA?*/
	$whereprov =""; $condi = "";
	if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
		$prov = $sql->runSelect("provincias", "id = '".$sql->fstr($_POST["provincia"])."'");
		if(isset($prov[0]["provincia"])){
			$whereprov = " AND e.provincia = '".$sql->fstr($_POST["provincia"])."'";
			$condi = "&errtosave2=".$_POST["provincia"];
		}
	}
	/*Ver si hay que grabar algo!*/
	if(isset($_POST["porcentaje_admin"])){
		$saveadmin["porcentaje_admin"] = $sql->fstr($_POST["porcentaje_admin"]);
		$sql->runUpdate("cnae_provincia e", $saveadmin, "e.cnae = '".$sql->fstr($_POST["cnae"])."'".$whereprov);
		
		unset($saveadmin);
		$d_cnae = $sql->runSelect("cnae_provincia e", "cnae = '".$sql->fstr($_POST["cnae"])."'".$whereprov);
		if(count($d_cnae) > 0){
			$d_cnae = $d_cnae[0];
			$gene->showMessage("El porcentaje del sector se ha actualizado correctamente", "success");
		}else{
			$gene->redirect("porcentaje-sector?errtosave=".$_POST["cnae"].$condi);
		}
	}
	
	$d_cnae = $sql->runSelect("cnae_provincia e INNER JOIN cnae c ON c.id = e.cnae", "e.cnae = '".$sql->fstr($_POST["cnae"])."'".$whereprov, "e.*, c.cnae, e.cnae as id");
	if(count($d_cnae) > 1){
		/*hay varios, reformulamos la select*/
		$d_cnae = $sql->runSelect("(SELECT '".$sql->fstr($_POST["cnae"])."' AS id, c.cnae, AVG(e.porcentaje_auto) AS porcentaje_auto FROM cnae_provincia e INNER JOIN cnae c ON c.id = e.cnae WHERE e.cnae = '".$sql->fstr($_POST["cnae"])."'".$whereprov." AND e.porcentaje_auto <> '0.00' AND e.porcentaje_auto IS NOT NULL) tb1 INNER JOIN
								   (SELECT '".$sql->fstr($_POST["cnae"])."' AS id, c.cnae, AVG(e.porcentaje_admin) AS porcentaje_admin FROM cnae_provincia e INNER JOIN cnae c ON c.id = e.cnae WHERE e.cnae = '".$sql->fstr($_POST["cnae"])."'".$whereprov." AND e.porcentaje_admin <> '0.00' AND e.porcentaje_admin IS NOT NULL) tb2 ON tb1.id = tb2.id", 
								  "1 = 1",
								  "tb1.id, IF(tb1.cnae IS NULL, tb2.cnae, tb1.cnae) as cnae,porcentaje_auto, porcentaje_admin", false, false, false, false, false, false);
	}
	if(count($d_cnae) == 1){
		$d_cnae = $d_cnae[0];
		
		
		
		/*Saca datos y actualiza...*/
		$empresas = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", "e.cnae = '".$d_cnae["id"]."'".$whereprov, "DISTINCT eh.id_empresario"); $empresas = count($empresas);
		$registros = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", "e.cnae = '".$d_cnae["id"]."'".$whereprov, "COUNT(*) as cuantas"); $registros = $registros[0]["cuantas"];
		/*
		$p_auto = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", 
		                          "e.cnae = '".$d_cnae["id"]."'".$whereprov, 
								  "IF(AVG(coste_sal_medio) IS NULL, 0, AVG(coste_sal_medio)) AS coste_sal_medio, 
								   IF(SUM(num_trabajadores) IS NULL, 0, SUM(num_trabajadores)) AS num_trabajadores,  
								   IF(SUM(ventas_totales) IS NULL, 0, SUM(ventas_totales)) AS ventas_totales,
								   IF(SUM(importe_nominas) IS NULL, 0, SUM(importe_nominas)) AS importe_nominas,
								   IF(SUM(valor_empresarial) IS NULL, 0, SUM(valor_empresarial)) AS valor_empresarial,
								   IF(SUM(base_imponible) IS NULL, 0, SUM(base_imponible)) AS base_imponible, 
								   IF(SUM(iva) IS NULL, 0, SUM(iva)) AS iva, 
								   IF(SUM(coste_sal_total) IS NULL, 0, SUM(coste_sal_total)) AS coste_sal_total");
		*/
		$p_auto = $sql->runSelect("(SELECT *
									FROM (
										SELECT eh.* 
										FROM empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario 
										WHERE e.cnae = '".$d_cnae["id"]."'".$whereprov." 
										ORDER BY eh.fecha DESC
									   ) eh
									GROUP BY id_empresario) tb", 
		                          "1 = 1", 
								  "IF(AVG(coste_sal_medio) IS NULL, 0, AVG(coste_sal_medio)) AS coste_sal_medio, 
								   IF(SUM(num_trabajadores) IS NULL, 0, SUM(num_trabajadores)) AS num_trabajadores,  
								   IF(SUM(ventas_totales) IS NULL, 0, SUM(ventas_totales)) AS ventas_totales,
								   IF(SUM(importe_nominas) IS NULL, 0, SUM(importe_nominas)) AS importe_nominas,
								   IF(SUM(valor_empresarial) IS NULL, 0, SUM(valor_empresarial)) AS valor_empresarial,
								   IF(SUM(base_imponible) IS NULL, 0, SUM(base_imponible)) AS base_imponible, 
								   IF(SUM(iva) IS NULL, 0, SUM(iva)) AS iva, 
								   IF(SUM(coste_sal_total) IS NULL, 0, SUM(coste_sal_total)) AS coste_sal_total");
		$p_auto = $p_auto[0];
		if($p_auto["ventas_totales"] == 0){
			$saveauto["porcentaje_auto"] = $p_auto["importe_nominas"]*100/1;
		}else{
			$saveauto["porcentaje_auto"] = $p_auto["importe_nominas"]*100/$p_auto["ventas_totales"];
		}
		$sql->runUpdate("cnae e", $saveauto, "e.cnae = '".$d_cnae["id"]."'".$whereprov);
		
		if($d_cnae["porcentaje_admin"] == ""){
			$porc = round($saveauto["porcentaje_auto"], 2);
		}else{
			$porc = $d_cnae["porcentaje_admin"];
		}

		$meses = 1; /*Cambiar x 14 si anual!*/
		?>
		<!--<script type="text/javascript" src="prettify/prettify.js"></script>-->
		<script type="text/javascript" src="js/ui.spinner.min.js"></script>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				// slider with fixed minimum
				/*
				jQuery("#slider4").slider({
						range: "min",
						value: <?php echo $porc; ?>,
						step: 0.01,
						min: 0.00,
						max: 100.00,
						slide: function( event, ui ) {
							jQuery("#amount4").text(ui.value + " %");
							jQuery("#porcentaje_admin").val(ui.value);
						}
				});
				jQuery("#amount4").text(jQuery("#slider4").slider("value") + " %");
				jQuery("#porcentaje_admin").val(jQuery("#slider4").slider("value"));
				*/
				jQuery("#porcentaje_admin").spinner({min: 0, max: 100, step: 0.01 });
			});
		</script>
		<h3><?php echo $d_cnae["id"]." - ".$d_cnae["cnae"]; if(isset($prov[0]["provincia"])){ echo " <span style='color: darkblue;'>para la provincia de</span> ".$prov[0]["provincia"]." (".$prov[0]["autonomia"].")"; }?></h3>
		<div class="row-fluid">              
			<div class="span12">
				<table class="table table-bordered table-invoice">
					<tbody>
						<!--
						<tr>
							<td class="width30">Sector</td>
							<td class="width70"><strong><?php echo $d_cnae["id"]." - ".$d_cnae["cnae"]; ?></strong></td>
						</tr>
						-->
						<tr>
							<td class="width30">Nº empresas en el sistema</td>
							<td class="width70"><strong><?php echo ($empresas); ?></strong></td>
						</tr>
						<!--
						<tr>
							<td>Nº Historicos de esas empresas</td>
							<td><?php echo ($registros); ?></td>
						</tr>
						-->
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr>
							<td>Importe Salarial Total (CLM * NT) [IST]</td>
							<td><?php echo seeNum($p_auto["coste_sal_medio"]*$p_auto["num_trabajadores"]*$meses); ?> €</td>
						</tr>
						<tr>
							<td>Valor Empresarial [VE]</td>
							<td><?php echo seeNum($p_auto["valor_empresarial"]); ?> €</td>
						</tr>
						<tr>
							<td><b>% Valor Social (IST/VE %)</b></td>
							<?php
							if($p_auto["valor_empresarial"] == 0) $valdiv = 1; else $valdiv = $p_auto["valor_empresarial"];
							?>
							<td><b><?php echo round((($p_auto["coste_sal_medio"]*$p_auto["num_trabajadores"])*100*$meses/$valdiv), 2); ?> %</b></td>
						</tr>
						<!--
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr>
							<td>Porcentaje Sector segun los datos de las empresas</td>
							<td><?php echo round($saveauto["porcentaje_auto"], 2); ?> %</td>
						</tr>
						-->
				</tbody>
			</table>
			<br />
			<style>
			
				.ui-spinner-buttons {
					left: 154px !important;
				}
				.inputDentro{
					height: 48px !important;
					width: 110px !important;
					margin-right: 16px !important;
					font-weight: bold;
					text-align: right !important;
					font-size: 32px !important;
					border: none !important;
					background-color: transparent !important;
					-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0) !important;
					-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.0) !important;
					box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.0) !important;
				}
				.ui-spinner-button {
					border: 1px solid #bbb !important;
				}
				.ui-spinner-up {
					background: url(../images/spinner28.png) no-repeat -2px 3px;
					border-bottom: 0;
					width: 28px !important;
				}
				.ui-spinner-down {
					background: url(../images/spinner28.png) no-repeat -2px -16px;
					width: 28px !important;
				}
			</style>
			<div class="amountdue" style="text-align: center;">
				<form method="POST">
					<h1>
						<span>Porcentaje a aplicar:</span>
						<input type="text" name="porcentaje_admin" id="porcentaje_admin" class="input inputDentro input-spinner" value="<?php echo $porc; ?>" />
					</h1>
					<br />
					<input type="hidden" name="cnae" value="<?php echo $_POST["cnae"] ?>" />
					<?php
					if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
						?><input type="hidden" name="provincia" value="<?php echo $_POST["provincia"]; ?>" /><?php
					}
					?>
					<input type="submit" value="guardar" class="btn btn-info" />
				</form>
				<br />
			</div>
			<!--
			<div class="amountdue" style="text-align: center;">
				<h1>
					<span>Porcentaje a aplicar:</span>
					<div id="amount4" style="line-height: normal;"><?php echo $porc; ?> %</div>
				</h1>
				<form method="POST">
					<input type="hidden" name="cnae" value="<?php echo $_POST["cnae"] ?>" />
					<input type="hidden" name="porcentaje_admin" id="porcentaje_admin" value="nulo" />
					<input type="submit" value="guardar" class="btn btn-info" />
				</form>
				<br />
			</div>
			<div class="row-fluid">
				<div class="span12">
					<h4 class="subtitle2">Ajusta el porcentaje:</h4>
					<br />
					<div class="pargroup">
						<div class="par">
							<h6>Nuevo porcentaje del sector</h6>
							<div id="slider4"></div>
						</div>
					</div>
				</div>
			</div>
			-->
			</div><!--span6-->
		</div>
		<?php
	}else{
		$gene->showMessage("No se ha encontrado información del Sector seleccionado", "warning");
	}
}else{
	$gene->showMessage("Seleccione un Sector para continuar", "info");
}



pie();



function showSelect($label, $name, $array, $button = false, $amb = ""){
	/*Hay POSTS*/
	if(isset($_POST[$name])){
		$default = $_POST[$name];
	}else{
		$default = "";
	}
	?>
	<div class="par control-group" style="float: left; margin-left: 5px;">
		<label><?php echo $label; ?></label>
		<span class="formwrapper">
			<select data-placeholder="<?php echo $label; ?>" name="<?php echo $name; ?>"  style="width:350px" class="chzn-select" tabindex="2">
			  <option value=""><?php echo $amb; ?></option>
			  <?php
			  $open = false;
			  for($x=0; $x<count($array); $x++){
				if($array[$x]["id"] === $default) $sel = "selected"; else $sel = "";
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
			<?php
			if($button){
				?><input type="submit" value="Seleccionar" class="btn btn-info btn-rounded" style="margin-top: -22px;" /><?php
			}
			?>
		</span>
	</div>
	<?php
}

function seeNum($num){
	return number_format($num, 2, ",", ".");
}
?>