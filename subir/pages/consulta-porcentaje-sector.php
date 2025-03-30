<?php
cabecera($sql, $cusers, $gene, 11);
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
	?>
</form>
<div style="clear: both;"></div>
<br /><br />
<?php
if(isset($_POST["cnae"]) && $_POST["cnae"] != ""){
	/*esta en BBDD?*/
	/*ESTA SETEADO PROVINCIA?*/
	$whereprov ="";
	if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
		$prov = $sql->runSelect("provincias", "id = '".$sql->fstr($_POST["provincia"])."'");
		if(isset($prov[0]["provincia"])){
			$whereprov = " AND e.provincia = '".$sql->fstr($_POST["provincia"])."'";
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
		$empresas = $sql->runSelect("empresario e", "e.cnae = '".$d_cnae["id"]."'".$whereprov, "COUNT(*) as cuantas", false, false, false, false, false, false, 0); $empresas = $empresas[0]["cuantas"];
		//$registros = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", "e.cnae = '".$d_cnae["id"]."'".$whereprov, "COUNT(*) as cuantas"); $registros = $registros[0]["cuantas"];
		/*
		PRIMERA VERSION DESCARTADA
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
		/*
		SEGUNDA VERSION DESCARTADA
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
								   IF(SUM(coste_sal_total) IS NULL, 0, SUM(coste_sal_total)) AS coste_sal_total", false, false, false, false, false, false, 0);
		$p_auto = $p_auto[0];
		*/
		/*Para cada empresa, sacamos el ultimo Valor!*/
		/*
		
		*/
		$data_emp = $sql->getSql("SELECT IF(SUM(num_trabajadores)IS NULL, '0', SUM(num_trabajadores)) AS nt, 
									     IF(SUM(valor_empresarial) IS NULL, '0', SUM(valor_empresarial)) AS ve, 
									     IF(AVG(salario_medio) IS NULL, '0', AVG(salario_medio)) AS sm,
										 iva
								  FROM (
										SELECT * FROM (
														SELECT * FROM empresario_historico_ine WHERE id_empresa IN (SELECT id FROM empresario e WHERE e.cnae = '".$d_cnae["id"]."'".$whereprov.")
									ORDER BY id_empresa, anyo DESC) tb GROUP BY id_empresa) tb2");
		if($data_emp[0]["iva"] == "") $data_emp[0]["iva"] = "21";
		
		$iva = $data_emp[0]["iva"];
		$ventas_totales = $data_emp[0]["ve"];
		$numero_trabajadores = $data_emp[0]["nt"];
		$salario_medio = $data_emp[0]["sm"];
		
		$importe_salarial_total = $salario_medio*$numero_trabajadores;
		$importe_iva = $ventas_totales*$iva/100;
		$base_imponible = $ventas_totales - $importe_iva;
		
		$valor_empresarial = $base_imponible - $importe_salarial_total;
		
		$p_valor_social = round($importe_salarial_total*100/$valor_empresarial, 2);
		
		/*
		if($p_auto["ventas_totales"] == 0){
			$porc_auto = $p_auto["importe_nominas"]*100/1;
		}else{
			$porc_auto = $p_auto["importe_nominas"]*100/$p_auto["ventas_totales"];
		}
		*/
		
		/*Porcentaje a Mostrar*/
		if($whereprov == ""){
			$porc = $p_valor_social;
		}else{
			/*Ademas lo actualizamos en la BBDD, por si no coincide!*/
			
			if($d_cnae["porcentaje_admin"] == ""){
				$porc = round($d_cnae["porcentaje_auto"], 2);
			}else{
				$porc = $d_cnae["porcentaje_admin"];
			}
		}
		
		$meses = 14;
		?>
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
							<td><?php //echo ($registros); ?></td>
						</tr>
						-->
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr>
							<td>Ventas Totales Anuales [VT]</td>
							<td><?php echo seeNum($ventas_totales); ?> €</td>
						</tr>
						<tr>
							<td>Importe IVA Anual [IVA]</td>
							<td><?php echo seeNum($importe_iva); ?> €</td>
						</tr>
						<tr>
							<td><b>Base imponible Anual (VT-IVA)</b></td>
							<td><b><?php echo seeNum($base_imponible); ?> €</b></td>
						</tr>
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr>
							<td>Coste Laboral Medio Anual [CLM]</td>
							<td><?php echo seeNum($salario_medio); ?> €</td>
						</tr>
						<tr>
							<td>Numero de trabajadores [NT]</td>
							<td><?php echo ($numero_trabajadores); ?> trabajadores</td>
						</tr>
						<tr>
							<td><b>Importe Salarial Total Anual (CLM * NT) [IST]</b></td>
							<td><b><?php echo seeNum($importe_salarial_total); ?> €</b></td>
						</tr>
						<tr>
							<td>Valor Empresarial [VE]</td>
							<td>
								<?php //echo seeNum($p_auto["valor_empresarial"]*$meses); ?>
								<?php //echo seeNum((($p_auto["ventas_totales"]*12)-($p_auto["iva"]*12))-($p_auto["coste_sal_medio"]*$p_auto["num_trabajadores"]*$meses)); ?>
								<?php echo seeNum($valor_empresarial); ?> €
						    </td>
						</tr>
						<tr>
							<td><b>% Valor Social (IST/VE %)</b></td>
							<?php
								/*
								$valem = (($p_auto["ventas_totales"]*12)-($p_auto["iva"]*12))-($p_auto["coste_sal_medio"]*$p_auto["num_trabajadores"]*$meses);
								if($valem == 0) $valdiv = 1; else $valdiv = $valem;
								*/
							?>
							<td><b>
								<?php //echo round((($p_auto["coste_sal_medio"]*$p_auto["num_trabajadores"])*100/$valdiv), 2); ?>
								<?php echo $p_valor_social; ?> %</b>
							</td>
						</tr>
						<tr><td colspan="2">&nbsp;</td></tr>
						<!--
						<tr>
							<td>Porcentaje Sector segun los datos de las empresas</td>
							<td><?php //echo round($porc_auto, 2); ?> %</td>
						</tr>
						-->
						<tr>
							<td>Porcentaje Sector a aplicar (Admin)</td>
							<td><?php echo round($porc, 2); ?> %</td>
						</tr>
				</tbody>
			</table>
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