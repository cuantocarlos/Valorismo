<?php
cabecera($sql, $cusers, $gene, 20);
?>
<script type="text/javascript" src="js/chosen.jquery.min.js"></script>
<script>
	jQuery(document).ready(function(){
		jQuery(".chzn-select").chosen();
		jQuery("#porc_sector").spinner({
			min: 0, 
			max: 100, 
			step: 0.01
		});
	});
</script>
<form method="POST">
	<?php
	$cnaes = $sql->runSelect("cnae", "1=1", "id, CONCAT(id, ' - ', cnae) as valor");
	showSelect("Clasificación Nacional de Actividades Económicas (CNAE)", "cnae", $cnaes, false);
	$provincias = $sql->runSelect("provincias", "1=1", "id, provincia as valor, autonomia as valor2", "autonomia, provincia");
	showSelect("Área geográfica", "provincia", $provincias, true, "Toda España");
	?>
</form>
<div style="clear: both;"></div>
<form method="POST">
	<?php
	$empresas = $sql->runSelect("empresario", "1=1", "id, CONCAT(cif, ' - ', nombre) as valor");
	showSelect("Empresas", "cif", $empresas, true, "o seleccione una empresa");
	?>
</form>
<div style="clear: both;"></div>
<br /><br />
<?php
/*Vemos para Grabar!*/
if(isset($_POST["guardar_simulacion"])){
	if(isset($_POST["m_valor_empresarial"]) &&
	   isset($_POST["mp_valor_social"]) &&
	   isset($_POST["m_iva"]) &&
	   isset($_POST["imp_sal_tot"]) &&
	   isset($_POST["sal_med"]) &&
	   isset($_POST["emp_act"]) &&
	   isset($_POST["num_tra"]) &&
	   isset($_POST["iva_aplicado"]) &&
	   isset($_POST["a_num_tra"]) &&
	   isset($_POST["pa_num_tra"]) &&
	   isset($_POST["ventas_totales_ini"]) && 
	   isset($_POST["ventas_totales"]) && 
	   isset($_POST["ma_ventas_totales"]) && 
	   isset($_POST["valor_socialActual"]) && 
	   isset($_POST["pa_ventas_totales"])
	){
		$est = Array(".", ",");
		$xes = Array("", ".");
		$tosave["valor_empresarial"] = $sql->fstr(str_replace($est, $xes, $_POST["m_valor_empresarial"]));
		$tosave["valor_social"] = $sql->fstr(str_replace($est, $xes, $_POST["imp_sal_tot"]));
		$tosave["porc_sector"] = $sql->fstr(str_replace($est, $xes, $_POST["mp_valor_social"]));
		$tosave["coste_laboral_medio"] = $sql->fstr(str_replace($est, $xes, $_POST["sal_med"]));
		$tosave["resultado"] = $sql->fstr(str_replace($est, $xes, $_POST["a_num_tra"]));
		$tosave["num_trabajadores"] = $sql->fstr(str_replace($est, $xes, $_POST["num_tra"]));
		$tosave["num_trabajadoresA"] = $sql->fstr(str_replace($est, $xes, $_POST["emp_act"]));
		$tosave["porc_trabajadores"] = $sql->fstr(str_replace($est, $xes, $_POST["pa_num_tra"]));
		$tosave["iva"] = $sql->fstr(str_replace($est, $xes, $_POST["m_iva"]));
		$tosave["iva_aplicado"] = $sql->fstr(str_replace($est, $xes, $_POST["iva_aplicado"]));
		$tosave["ventas_totales_ini"] = $sql->fstr(str_replace($est, $xes, $_POST["ventas_totales_ini"]));
		$tosave["ventas_totales"] = $sql->fstr(str_replace($est, $xes, $_POST["ventas_totales"]));
		$tosave["ma_ventas_totales"] = $sql->fstr(str_replace($est, $xes, $_POST["ma_ventas_totales"]));
		$tosave["pa_ventas_totales"] = $sql->fstr(str_replace($est, $xes, $_POST["pa_ventas_totales"]));
		$tosave["valor_socialA"] = $sql->fstr(str_replace($est, $xes, $_POST["valor_socialActual"]));
		
		if(isset($_POST["cif"])){
			$tosave["empresa"] = $sql->fstr($_POST["cif"]);
		}else{
			if(isset($_POST["cnae"])){
				$tosave["cnae"] = $sql->fstr($_POST["cnae"]);
			}
			if(isset($_POST["provincia"])){
				$tosave["provincia"] = $sql->fstr($_POST["provincia"]);
			}
		}
		
		$rows = $sql->runInsert("simulador", $tosave);
		
		unset($tosave);
   }else{
		$gene->showMessageP("No se ha grabado, Variables incorrectas!");
   }
}
/*Ver si borramos*/
/*Check for DEL DATA*/
if(isset($_POST["delData"])){
	if(is_numeric($_POST["delData"])){
		$sql->runDelete("simulador", "id = '".$sql->fstr($_POST["delData"])."'");
	}
}
?>
<br />
<?php
if(isset($_POST["cnae"]) && $_POST["cnae"] != ""){
	$datasec = $sql->runSelect("cnae", "id = '".$sql->fstr($_POST["cnae"])."'");
	if(count($datasec)==1){
		$datos["empresa"] = $datasec[0]["id"]." - ".$datasec[0]["cnae"];
		/*ESTA SETEADO PROVINCIA?*/
		$whereprov =""; $datos["cif"] = "De toda España";
		if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
			$prov = $sql->runSelect("provincias", "id = '".$sql->fstr($_POST["provincia"])."'");
			if(isset($prov[0]["provincia"])){
				$datos["cif"] = "En: ".$prov[0]["provincia"]." (".$prov[0]["autonomia"].")";
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
			/*Vemos si los pillamos de la simulacion*/
			$where = "cnae = '".$sql->fstr($_POST["cnae"])."'";
			if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
				$where .= " AND provincia = '".$sql->fstr($_POST["provincia"])."' AND empresa = '0'";
			}else{
				$where .= " AND provincia = '0' AND empresa = '0'";
			}
			
			$simulador = $sql->runSelect("simulador", $where, "*", "fecha DESC", false, false, false, false, false);
			
			/*CONSULTAS INICIALES*/
			$data_emp = $sql->getSql("SELECT IF(SUM(num_trabajadores)IS NULL, '0', SUM(num_trabajadores)) AS nt, 
									     IF(SUM(valor_empresarial) IS NULL, '0', SUM(valor_empresarial)) AS ve, 
									     IF(AVG(salario_medio) IS NULL, '0', AVG(salario_medio)) AS sm,
										 iva
								  FROM (
										SELECT * FROM (
														SELECT * FROM empresario_historico_ine WHERE id_empresa IN (SELECT id FROM empresario e WHERE e.cnae = '".$d_cnae["id"]."'".$whereprov.")
									ORDER BY id_empresa, anyo DESC) tb GROUP BY id_empresa) tb2");
			if($data_emp[0]["iva"] == "") $data_emp[0]["iva"] = "21";
			$datos["iva_aplicado"] = $data_emp[0]["iva"];
			$recalcula = true;
			if(count($simulador)>0){
				$datos["iva2"] = $simulador[0]["iva"];
				$datos["iva_aplicado"] = $simulador[0]["iva_aplicado"];
				$datos["ventas_totales2"] = $simulador[0]["ventas_totales"];
				$datos["numero_trabajadores2"] = $simulador[0]["num_trabajadores"];
				$datos["salario_medio2"] = $simulador[0]["coste_laboral_medio"];
				
				$datos["valor_empresarial2"] = $simulador[0]["valor_empresarial"];
				$datos["p_valor_social2"] = $simulador[0]["porc_sector"];
				
				$datos["imp_sal_tot_nuevo"] = $simulador[0]["valor_social"];
				$datos["numero_trabajadores_nuevo"] = $simulador[0]["num_trabajadores"];
				$datos["ventas_totales_nuevo"] = $simulador[0]["ventas_totales"];
				$recalcula = false;
			}else{
				$datos["iva2"] = 0;
				$datos["ventas_totales2"] = 0;
				$datos["numero_trabajadores2"] = 0;
				$datos["salario_medio2"] = 0;
				
				$datos["imp_sal_tot_nuevo"] = 0;
				$datos["numero_trabajadores_nuevo"] = 0;
				$datos["ventas_totales_nuevo"] = 0;
			}
			
			/*PARTE 2, para el simulador*/
			$datos["importe_salarial_total2"] = $datos["salario_medio2"]*$datos["numero_trabajadores2"];
			$datos["base_imponible2"] = $datos["ventas_totales2"]*100/(100+$datos["iva2"]);
			$datos["importe_iva2"] = $datos["iva2"]/100*$datos["base_imponible2"];
			
			if($recalcula){
				$datos["valor_empresarial2"] = $datos["base_imponible2"] - $datos["importe_salarial_total2"];
				$datos["p_valor_social2"] = round($datos["importe_salarial_total2"]*100/$datos["valor_empresarial2"], 2);
			}
			/*Porcentaje a Mostrar*/
			if($whereprov == ""){
				$porc = $datos["p_valor_social2"];
			}else{
				/*Ademas lo actualizamos en la BBDD, por si no coincide!*/
				if($d_cnae["porcentaje_admin"] == ""){
					$datos["a_porc2"] = round($d_cnae["porcentaje_auto"], 2);
				}else{
					$datos["a_porc2"] = $d_cnae["porcentaje_admin"];
				}
			}
			
			$datos["iva"] = $data_emp[0]["iva"]; /*Esto es para que salga el ultimo aplicado*/
			$datos["iva"] = $datos["iva_aplicado"];
			$datos["ventas_totales"] = $data_emp[0]["ve"];
			$datos["numero_trabajadores"] = $data_emp[0]["nt"];
			$datos["salario_medio"] = $data_emp[0]["sm"];
			
			/*PARTE 1, para el resto*/
			$datos["iva"] = $data_emp[0]["iva"];
			$datos["ventas_totales"] = $data_emp[0]["ve"];
			$datos["numero_trabajadores"] = $data_emp[0]["nt"];
			$datos["salario_medio"] = $data_emp[0]["sm"];
			
			$datos["importe_salarial_total"] = $datos["salario_medio"]*$datos["numero_trabajadores"];
			$datos["base_imponible"] = $datos["ventas_totales"]*100/(100+$datos["iva"]);
			$datos["importe_iva"] = $datos["iva"]/100*$datos["base_imponible"];
			
			$datos["valor_empresarial"] = $datos["base_imponible"] - $datos["importe_salarial_total"];
			
			$datos["p_valor_social"] = $datos["importe_salarial_total"]*100/$datos["valor_empresarial"];
			/*Porcentaje a Mostrar*/
			if($whereprov == ""){
				$porc = $datos["p_valor_social"];
			}else{
				/*Ademas lo actualizamos en la BBDD, por si no coincide!*/
				if($d_cnae["porcentaje_admin"] == ""){
					$datos["a_porc"] = round($d_cnae["porcentaje_auto"], 2);
				}else{
					$datos["a_porc"] = $d_cnae["porcentaje_admin"];
				}
			}
			
			
			
			
			showTable($datos);
			if(isset($rows)){
				if($rows == 1){
					$gene->showMessageP("Datos añadidos al simulador correctamente.", "success");
				}else{
					$gene->showMessageP("ha ocurrido un error en la grabación de los datos");
				}
			}
			showSimulador($sql, $gene, "s");
		}else{
			$gene->showMessage("No se ha encontrado datos del sector especificado");
		}
	}else{
		$gene->showMessage("No se ha encontrado el sector especificado");
	}
}elseif(isset($_POST["cif"]) && $_POST["cif"] != ""){
	$dataemp = $sql->runSelect("empresario", "id = '".$sql->fstr($_POST["cif"])."'");
	if(count($dataemp)==1){
		$datos["empresa"] = $dataemp[0]["nombre"];
		$datos["cif"] = "CIF: ".$dataemp[0]["cif"];
		
		/*Datos del simulador*/
		$where = "empresa = '".$sql->fstr($_POST["cif"])."' AND provincia = '0' AND cnae = '0'";
		$simulador = $sql->runSelect("simulador", $where, "*", "fecha DESC", false, false, false, false, false);
		
		/*Datos de la empresa*/
		$data_emp = $sql->getSql("SELECT IF(SUM(num_trabajadores)IS NULL, '0', SUM(num_trabajadores)) AS nt, 
									     IF(SUM(valor_empresarial) IS NULL, '0', SUM(valor_empresarial)) AS ve, 
									     IF(AVG(salario_medio) IS NULL, '0', AVG(salario_medio)) AS sm,
										 iva
								  FROM (
										SELECT * FROM (
														SELECT * FROM empresario_historico_ine WHERE id_empresa = '".$sql->fstr($_POST["cif"])."'
									ORDER BY id_empresa, anyo DESC) tb GROUP BY id_empresa) tb2");
		if($data_emp[0]["iva"] == "") $data_emp[0]["iva"] = "21";
		
		
		$datos["iva_aplicado"] = $data_emp[0]["iva"];
		$recalcula = true;
		if(count($simulador)>0){
			$datos["iva2"] = $simulador[0]["iva"];
			$datos["iva_aplicado"] = $simulador[0]["iva_aplicado"];
			$datos["ventas_totales2"] = $simulador[0]["ventas_totales"];
			$datos["numero_trabajadores2"] = $simulador[0]["num_trabajadores"];
			$datos["salario_medio2"] = $simulador[0]["coste_laboral_medio"];
			
			$datos["valor_empresarial2"] = $simulador[0]["valor_empresarial"];
			$datos["p_valor_social2"] = $simulador[0]["porc_sector"];
			
			$datos["imp_sal_tot_nuevo"] = $simulador[0]["valor_social"];
			$datos["numero_trabajadores_nuevo"] = $simulador[0]["num_trabajadores"];
			$datos["ventas_totales_nuevo"] = $simulador[0]["ventas_totales"];
			$recalcula = false;
		}else{
			$datos["iva2"] = 0;
			$datos["ventas_totales2"] = 0;
			$datos["numero_trabajadores2"] = 0;
			$datos["salario_medio2"] = 0;
			
			$datos["imp_sal_tot_nuevo"] = 0;
			$datos["numero_trabajadores_nuevo"] = 0;
			$datos["ventas_totales_nuevo"] = 0;
		}
		
		/*PARTE 2, para el simulador*/
		$datos["importe_salarial_total2"] = $datos["salario_medio2"]*$datos["numero_trabajadores2"];
		$datos["base_imponible2"] = $datos["ventas_totales2"]*100/(100+$datos["iva2"]);
		$datos["importe_iva2"] = $datos["iva2"]/100*$datos["base_imponible2"];
		
		if($recalcula){
			$datos["valor_empresarial2"] = $datos["base_imponible2"] - $datos["importe_salarial_total2"];
			$datos["p_valor_social2"] = round($datos["importe_salarial_total2"]*100/$datos["valor_empresarial2"], 2);
		}
		
		/*Porcentaje a Mostrar*/
		if($whereprov == ""){
			$porc = $datos["p_valor_social2"];
		}else{
			/*Ademas lo actualizamos en la BBDD, por si no coincide!*/
			if($d_cnae["porcentaje_admin"] == ""){
				$datos["a_porc2"] = round($d_cnae["porcentaje_auto"], 2);
			}else{
				$datos["a_porc2"] = $d_cnae["porcentaje_admin"];
			}
		}
		
		$datos["iva"] = $data_emp[0]["iva"]; /*Esto es para que salga el ultimo aplicado*/
		$datos["iva"] = $datos["iva_aplicado"];
		$datos["ventas_totales"] = $data_emp[0]["ve"];
		$datos["numero_trabajadores"] = $data_emp[0]["nt"];
		$datos["salario_medio"] = $data_emp[0]["sm"];
		
		$datos["importe_salarial_total"] = $datos["salario_medio"]*$datos["numero_trabajadores"];
		$datos["base_imponible"] = $datos["ventas_totales"]*100/(100+$datos["iva"]);
		$datos["importe_iva"] = $datos["iva"]/100*$datos["base_imponible"];
		
		$datos["valor_empresarial"] = $datos["base_imponible"] - $datos["importe_salarial_total"];
		
		$datos["p_valor_social"] = $datos["importe_salarial_total"]*100/$datos["valor_empresarial"];
		/*Porcentaje a Mostrar*/
		if($whereprov == ""){
			$porc = $datos["p_valor_social"];
		}else{
			/*Ademas lo actualizamos en la BBDD, por si no coincide!*/
			if($d_cnae["porcentaje_admin"] == ""){
				$datos["a_porc"] = round($d_cnae["porcentaje_auto"], 2);
			}else{
				$datos["a_porc"] = $d_cnae["porcentaje_admin"];
			}
		}

		
		showTable($datos);
		if(isset($rows)){
			if($rows == 1){
				$gene->showMessageP("Datos añadidos al simulador correctamente.", "success");
			}else{
				$gene->showMessageP("ha ocurrido un error en la grabación de los datos");
			}
		}
		showSimulador($sql, $gene, "e");
	}else{
		$gene->showMessage("No se ha encontrado la empresa en la base de datos");
	}

}else{
	$gene->showMessage("Seleccione un Sector o una empresa para continuar", "info");
}



pie();




function showTable($datos){
	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			calcMvalues();
			/*VARIABLES*/
			jQuery("#iva_aplicado").change(function() {
			
				iva = calcFormat(jQuery("#iva_aplicado").val());
				jQuery("#a_iva").text(SeeFormat(iva.toFixed(2)));
				jQuery("#n_iva").text(SeeFormat(iva.toFixed(2)));
				
				jQuery("#a_base_imponible").text(SeeFormat((calcFormat(jQuery("#a_total").text())*100/(iva+100)).toFixed(0)));
				jQuery("#n_base_imponible").text(SeeFormat((calcFormat(jQuery("#n_total").text())*100/(iva+100)).toFixed(0)));
				
				jQuery("#a_importe_iva").text(SeeFormat((calcFormat(jQuery("#a_base_imponible").text())*(iva/100)).toFixed(0)));
				jQuery("#n_importe_iva").text(SeeFormat((calcFormat(jQuery("#a_base_imponible").text())*(iva/100)).toFixed(0)));
				
				jQuery("#a_ventas_totales").text(jQuery("#a_base_imponible").text());
				
				jQuery("#n_valor_empresarial").text(SeeFormat((calcFormat(jQuery("#n_base_imponible").text())-calcFormat(jQuery("#n_valor_social").text())).toFixed(0)));
				
				jQuery("#np_valor_social").text(SeeFormat(((calcFormat(jQuery("#n_valor_social").text())/calcFormat(jQuery("#n_valor_empresarial").text()))*100).toFixed(2)));
				
				/*Tambien cambia en la nueva modificada*/
				/*
				jQuery("#m_iva").val(SeeFormat(jQuery("#iva_aplicado").val()));
				jQuery("#m_valor_empresarial").val(SeeFormat((calcFormat(jQuery("#n_base_imponible").text())-calcFormat(jQuery("#n_valor_social").text())).toFixed(0)));
				jQuery("#mp_valor_social").val(SeeFormat(((calcFormat(jQuery("#n_valor_social").text())/calcFormat(jQuery("#n_valor_empresarial").text()))*100)));
				*/
				
				calcMvalues();
			});
			jQuery("#m_valor_empresarial").change(function() {
				calcMvalues();
			});
			jQuery("#mp_valor_social").change(function() {
				calcMvalues();
			});
			jQuery("#m_iva").change(function() {
				calcMvalues();
			});
			
			function calcMvalues(){
				m_valor_empresarial = calcFormat(jQuery("#m_valor_empresarial").val());
				mp_valor_social = calcFormat(jQuery("#mp_valor_social").val());
				//mp_valor_social = calcFormat(SeeFormat(((calcFormat(jQuery("#m_valor_social").text())/calcFormat(jQuery("#m_valor_empresarial").val()))*100).toFixed(6)));
				m_iva = calcFormat(jQuery("#m_iva").val());
				
				jQuery("#m_valor_social").text(SeeFormat(((m_valor_empresarial)*(mp_valor_social/100)).toFixed(0)));
				m_valor_social = calcFormat(jQuery("#m_valor_social").text());
				
				jQuery("#m_base_imponible").text(SeeFormat((m_valor_empresarial+m_valor_social).toFixed(0)));
				m_base_imponible = calcFormat(jQuery("#m_base_imponible").text());
				
				jQuery("#m_importe_iva").text(SeeFormat((m_base_imponible*(m_iva/100)).toFixed(0)));
				m_importe_iva = calcFormat(jQuery("#m_importe_iva").text());
				
				jQuery("#m_total").text(SeeFormat((m_base_imponible+m_importe_iva).toFixed(0)));
				m_total = calcFormat(jQuery("#m_total").text());
				
				/*Datos de la tabla (Para el Simulador)*/
				
				sal_med = calcFormat(jQuery("#sal_med").val());
				emp_act = calcFormat(jQuery("#emp_act").val());
				ventas_totales_ini = calcFormat(jQuery("#ventas_totales_ini").val());
				
				jQuery("#imp_sal_tot").val(SeeFormat(m_valor_social.toFixed(0)));
				imp_sal_tot = calcFormat(jQuery("#imp_sal_tot").val());
				
				jQuery("#num_tra").val(SeeFormat((imp_sal_tot/sal_med).toFixed(0)));
				num_tra = calcFormat(jQuery("#num_tra").val());
				
				
				jQuery("#a_num_tra").val(SeeFormat((num_tra-emp_act).toFixed(0)));
				a_num_tra = calcFormat(jQuery("#a_num_tra").val());
				pintar("a_num_tra");
				
				jQuery("#pa_num_tra").val(SeeFormat((a_num_tra/emp_act*100).toFixed(2)));
				pa_num_tra = calcFormat(jQuery("#pa_num_tra").val());
				pintar("pa_num_tra");
				
				jQuery("#ventas_totales").val(SeeFormat(m_total));
				ventas_totales = calcFormat(jQuery("#ventas_totales").val());
				
				jQuery("#ma_ventas_totales").val(SeeFormat((ventas_totales-ventas_totales_ini).toFixed(0)));
				ma_ventas_totales = calcFormat(jQuery("#ma_ventas_totales").val());
				pintar("ma_ventas_totales");
				
				jQuery("#pa_ventas_totales").val(SeeFormat((ma_ventas_totales/ventas_totales_ini*100).toFixed(2)));
				pa_ventas_totales = calcFormat(jQuery("#pa_ventas_totales").val());
				pintar("pa_ventas_totales");
				
			}
			
			
			function pintar(id){
					if(calcFormat(jQuery('#'+id).val())>0){
						jQuery('#'+id).css("color", "green");
					}else if(calcFormat(jQuery('#'+id).val())==0){
						jQuery('#'+id).css("color", "black");
					}else{
						jQuery('#'+id).css("color", "red");
					}
				}
			
			function calcFormat(num){
				num = num.replace(".", "");
				num = num.replace(".", "");
				num = num.replace(".", "");
				num = num.replace(".", "");
				num = num.replace(".", "");
				num = num.replace(".", "");
				num = num.replace(",", ".");
				return Number(num);
			}
			function SeeFormat(num){
				num = num.toString().replace(".", "p");
				num = addCommas(num);
				return (num);
			}
			
			function addCommas(str) {
				/*Separamos la parte decimal*/
				str = str.split("p");
				var amount = new String(str[0]);
				
				amount = amount.split("").reverse();

				var output = "";
				for ( var i = 0; i <= amount.length-1; i++ ){
					output = amount[i] + output;
					if ((i+1) % 3 == 0 && (amount.length-1) !== i)output = '.' + output;
				}
				if(typeof str[1] === 'undefined'){
					return output;
				}else{
					return output+","+str[1];
				}
			}
		});
	</script>
	<div class="val_div">
		<form method="POST" action="#simulador">
			<?php
			if(isset($_POST["cnae"])){
				?><input type="hidden" name="cnae" value="<?php echo $_POST["cnae"]; ?>" /><?php
			}
			if(isset($_POST["provincia"])){
				?><input type="hidden" name="provincia" value="<?php echo $_POST["provincia"]; ?>" /><?php
			}
			if(isset($_POST["cif"])){
				?><input type="hidden" name="cif" value="<?php echo $_POST["cif"]; ?>" /><?php
			}
			?>
			
			<table class="valorismo">
				<tbody>
					<tr>
						<td class="concepto">&nbsp;</td>
						<td class="importe">&nbsp;</td>
						<td class="separador">&nbsp;</td>
						<td class="concepto">&nbsp;</td>
						<td class="importe">&nbsp;</td>
						<td class="separador">&nbsp;</td>
						<td class="concepto">&nbsp;</td>
						<td class="importe">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" class="titulo">ACTUAL</td>
						<td class="separador"></td>
						<td colspan="2" class="titulo">NUEVA</td>
						<td class="separador"></td>
						<td colspan="2" class="titulo">NUEVA MODIFICADA (Simulación)</td>
					</tr>
					<tr>
						<td colspan="2" class="emp"><?php echo $datos["empresa"]; ?></td>
						<td class="separador"></td>
						<td colspan="2" class="emp"><?php echo $datos["empresa"]; ?></td>
						<td class="separador"></td>
						<td colspan="2" class="emp"><?php echo $datos["empresa"]; ?></td>
					</tr>
					<tr>
						<td colspan="2" class="emp"><?php echo $datos["cif"]; ?></td>
						<td class="separador"></td>
						<td colspan="2" class="emp"><?php echo $datos["cif"]; ?></td>
						<td class="separador"></td>
						<td colspan="2" class="emp"><?php echo $datos["cif"]; ?></td>
					</tr>
					<tr>
						<td class="subtitulo">Concepto</td>
						<td class="subtitulo">Importe</td>
						<td class="separador"></td>
						<td class="subtitulo">Concepto</td>
						<td class="subtitulo">Importe</td>
						<td class="separador"></td>
						<td class="subtitulo">Concepto</td>
						<td class="subtitulo">Importe</td>
					</tr>
					<tr>
						<td class="filaName">Ventas Totales</td>
						<td class="filaImporte"><div id="a_ventas_totales"><?php echo seeNum($datos["base_imponible"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="filaName">Ventas Totales</td>
						<td class="filaImporte"></td>
						<td class="separador"></td>
						<td class="filaName">Ventas Totales</td>
						<td class="filaImporte"></td>
					</tr>
					<tr>
						<td class="filaName"></td>
						<td class="filaImporte"></td>
						<td class="separador"></td>
						<td class="filaName">Valor Empresarial</td>
						<td class="filaImporte"><div id="n_valor_empresarial"><?php echo seeNum($datos["valor_empresarial"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="filaName">Valor Empresarial</td>
						<td class="filaImporte">B <input type="text" id="m_valor_empresarial" name="m_valor_empresarial" value="<?php echo seeNum($datos["valor_empresarial2"], 0); ?>" /> €</td>
					</tr>
					<tr class="filvacia">
						<td class="filaName"></td>
						<td class="filaImporte"></td>
						<td class="separador"></td>
						<td class="filaName"></td>
						<td class="filaImporte"></td>
						<td class="separador"></td>
						<td class="filaName"></td>
						<td class="filaImporte"></td>
					</tr>
					<tr>
						<td class="filaName"></td>
						<td class="filaImporte"></td>
						<td class="separador"></td>
						<td class="FilVS"><div>Valor Social</div><div style="text-align: right;"><div id="np_valor_social"><?php echo seeNum($datos["p_valor_social"], 2); ?></div> %</div></td>
						<td class="FilVS filaImporte"><div id="n_valor_social"><?php echo seeNum($datos["importe_salarial_total"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="FilVS"><div>Valor Social</div><div style="text-align: right;">C <input type="text" id="mp_valor_social" name="mp_valor_social" value="<?php echo seeNum($datos["p_valor_social2"], 2); ?>" /> %</div></td>
						<td class="FilVS filaImporte"><div id="m_valor_social"><?php echo seeNum($datos["importe_salarial_total2"], 0); ?></div> €</td>
					</tr>
					<tr>
						<td class="FilBII filaName" style="border-top: 2px solid black;">Base Imponible</td>
						<td class="FilBII filaImporte" style="border-top: 2px solid black;"><div id="a_base_imponible"><?php echo seeNum($datos["base_imponible"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="FilBII filaName">Base Imponible</td>
						<td class="FilBII filaImporte"><div id="n_base_imponible"><?php echo seeNum($datos["base_imponible"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="FilBII filaName">Base Imponible</td>
						<td class="FilBII filaImporte"><div id="m_base_imponible"><?php echo seeNum($datos["base_imponible2"], 0); ?></div> €</td>
					</tr>
					<tr>
						<td class="FilBII filaName"><div>IVA</div><div style="text-align: right;"><div id="a_iva"><?php echo seeNum($datos["iva"], 2); ?></div>%</div></td>
						<td class="FilBII filaImporte"><div id="a_importe_iva"><?php echo seeNum($datos["importe_iva"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="FilBII filaName"><div>IVA</div><div style="text-align: right;"><div id="n_iva"><?php echo seeNum($datos["iva"], 2); ?></div>%</div></td>
						<td class="FilBII filaImporte"><div id="n_importe_iva"><?php echo seeNum($datos["importe_iva"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="FilBII filaName"><div>IVA</div><div style="text-align: right;">D <input type="text" id="m_iva" name="m_iva" value="<?php echo seeNum($datos["iva2"], 2); ?>" /> %</div></td>
						<td class="FilBII filaImporte"><div id="m_importe_iva"><?php echo seeNum($datos["importe_iva2"], 0); ?></div> €</td>
					</tr>
					<tr>
						<td class="total filaName">TOTAL</td>
						<td class="total filaImporte"><div id="a_total"><?php echo seeNum($datos["ventas_totales"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="total filaName">TOTAL</td>
						<td class="total filaImporte"><div id="n_total"><?php echo seeNum($datos["ventas_totales"], 0); ?></div> €</td>
						<td class="separador"></td>
						<td class="total filaName">TOTAL</td>
						<td class="total filaImporte"><div id="m_total"><?php echo seeNum($datos["ventas_totales2"], 0); ?></div> €</td>
					</tr>
				</tbody>
			</table>
		</div>
		<br />
		<?php
		if(isset($_POST["cnae"])){
			?>
			<button class="btn btn-disabled" style="float: right;" disabled>Aplicar Porcentaje</button>
			<div style="clear: both;"></div>
			<br />
			<?php
		}
		?>
		<br />
		<div class="val_div">
			<table class="valorismo">
				<tbody>
					<tr>
						<td class="valFijo Vnombre">Valor social destinado a los trabajadores ACTUAL</td>
						<td class="valFijo Vvalor Vgreen"><input type="text" class="inv" id="valor_socialActual" name="valor_socialActual" value="<?php echo seeNum($datos["importe_salarial_total"], 0); ?>" readonly /> €</td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Valor social destinado a los trabajadores NUEVO</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="imp_sal_tot" name="imp_sal_tot" value="<?php echo seeNum($datos["imp_sal_tot_nuevo"], 0); ?>" readonly /> €</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre">Coste por empleado según el sector (Fijo)</td>
						<td class="valFijo Vvalor Vgreen"><div><?php echo seeNum($datos["salario_medio"], 0); ?></div> €</td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Coste por empleado según el sector (Fijo)</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="sal_med" name="sal_med" value="<?php echo seeNum($datos["salario_medio"], 0); ?>" readonly /> €</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre">Número de empleados ACTUAL</td>
						<td class="valFijo Vvalor Vgreen"><input type="text" class="inv" id="emp_act" name="emp_act" value="<?php echo seeNum($datos["numero_trabajadores"], 0); ?>" readonly /> Tr</td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Número de empleados NUEVO</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="num_tra" name="num_tra" value="<?php echo seeNum($datos["numero_trabajadores_nuevo"], 0); ?>" readonly /> Tr</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre">Introduce el IVA aplicado en cada sector <span style="float: right;">A</span></td>
						<td class="valFijo Vvalor Vgreen"><input type="text" id="iva_aplicado" name="iva_aplicado" value="<?php echo seeNum($datos["iva_aplicado"], 2); ?>" /> %</td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Aumento del número de trabajadores</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="a_num_tra" name="a_num_tra" value="<?php echo seeNum("0", 0); ?>" readonly /> Tr</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre"></td>
						<td class="valFijo Vvalor"></td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Porcentaje de aumento del número de trabajadores</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="pa_num_tra" name="pa_num_tra" value="<?php echo seeNum("0", 2); ?>" readonly /> %</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre">Valor total de las ventas ACTUAL</td>
						<td class="valFijo Vvalor Vgreen"><input type="text" class="inv" id="ventas_totales_ini" name="ventas_totales_ini" value="<?php echo seeNum($datos["ventas_totales"], 0); ?>" readonly /> €</td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Valor total de las ventas NUEVAS</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="ventas_totales" name="ventas_totales" value="<?php echo seeNum($datos["ventas_totales_nuevo"], 0); ?>" readonly /> €</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre"></td>
						<td class="valFijo Vvalor"></td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Aumento o disminución de las ventas</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="ma_ventas_totales" name="ma_ventas_totales" value="<?php echo seeNum("0", 0); ?>" readonly /> €</td>
					</tr>
					<tr>
						<td class="valFijo Vnombre"></td>
						<td class="valFijo Vvalor"></td>
						<td class="valSeparador"></td>
						<td class="valVari Vnombre">Porcentaje en el aumento o disminución de las ventas</td>
						<td class="valVarit Vvalor"><input type="text" class="inv" id="pa_ventas_totales" name="pa_ventas_totales" value="<?php echo seeNum("0", 2); ?>" readonly /> %</td>
					</tr>
				</tbody>
			</table>
		</div>
		<br />
		<input type="submit" name="guardar_simulacion" value="Guardar a Simulación" style="float: right" class="btn btn-success" />
		<div style="clear: both;"></div>
		<br />
	</form>
	<br />
	<?php
}



function showSimulador($sql, $gene, $string){
	if($string == "s"){
		$where = "cnae = '".$sql->fstr($_POST["cnae"])."'";
		$string = "este sector";
		if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
			$where .= " AND provincia = '".$sql->fstr($_POST["provincia"])."' AND empresa = '0'";
			$string .= " y provincia";
		}else{
			$where .= " AND provincia = '0' AND empresa = '0'";
		}
	}else{
		$where = "empresa = '".$sql->fstr($_POST["cif"])."' AND provincia = '0' AND cnae = '0'";
		$string = "esta empresa";
	}
	$simulador = $sql->runSelect("simulador", $where, "*", "fecha ASC", false, false, false, false, false);
	if(count($simulador) > 0){
		?>
		<table class="sim" id="simulador">
			<tr>
				<td style="width: 400px; background-color: #FFFFFF;"><strong>Historial de simulaciones</strong></td>
				<?php
				for($x=0; $x<count($simulador); $x++){
					?><td class="subtitulo"><?php echo ($x+1)."ª Simulación"; ?></td><?php
				}
				?>
			</tr>
			<?php
				showTr($simulador, "%", "porc_sector", 2, "Porcentaje de Valor Social a aplicar");
				showTrB($simulador);
				showTr($simulador, "€", "valor_socialA", 0, "Valor Social destinado a los trabajadores ACTUAL");
				showTr($simulador, "€", "valor_social", 0, "Valor Social destinado a los trabajadores NUEVO");
				showTr($simulador, "€", "coste_laboral_medio", 0, "Coste por empleado  (Fijo)");
				showTr($simulador, "Tr", "num_trabajadoresA", 0, "Número de empleados ACTUAL");
				showTr($simulador, "Tr", "num_trabajadores", 0, "Número de empleados NUEVO");
				showTr($simulador, "Tr", "resultado", 0, "Aumento del número de trabajadores", true);
				showTr($simulador, "%", "porc_trabajadores", 2, "Porcentaje de aumento del número de trabajadores", true);
				showTrB($simulador);
				showTr($simulador, "%", "iva", 2, "IVA");
				showTrB($simulador);
				showTr($simulador, "€", "ventas_totales_ini", 0, "Valor total de las ventas ACTUAL");
				showTr($simulador, "€", "ventas_totales", 0, "Valor total de las ventas NUEVO");
				showTr($simulador, "€", "ma_ventas_totales", 0, "Aumento en el volumen de las ventas", true);
				showTr($simulador, "%", "pa_ventas_totales", 2, "Porcentaje en el aumento de las ventas", true);
				showTrB($simulador);
			?>
			<tr>
				<td style="width: 400px; background-color: #FFFFFF;"></td>
				<?php
				for($x=0; $x<count($simulador); $x++){
					?>
					<td class="subtitulo">
						<form method="POST" action="#simulador">
							<?php
							if(isset($_POST["cnae"])){
								?><input type="hidden" name="cnae" value="<?php echo $_POST["cnae"]; ?>" /><?php
							}
							if(isset($_POST["provincia"])){
								?><input type="hidden" name="provincia" value="<?php echo $_POST["provincia"]; ?>" /><?php
							}
							if(isset($_POST["cif"])){
								?><input type="hidden" name="cif" value="<?php echo $_POST["cif"]; ?>" /><?php
							}
							?>
							<input type="hidden" name="delData" value="<?php echo $simulador[$x]["id"]; ?>" />
							<input type="submit" value="Borrar" style="font-size: 15px;" class="btn btn-danger btn-circle" />
						</form>
					</td>
					<?php
				}
				?>
			</tr>
		</table>
		<?php
	}else{
		$gene->showMessage("Todavía no hay datos en la simulacion para ".$string, "info");
	}
	?>
	<br />
	<div style="width: 1000px; margin: auto;">
		<h4>Guía de Utilización</h4>
		<ol>	
			<li>Seleccione un sector de actividad económica y su área geográfica o una empresa</li>
			<li>Se completarán los campos en verde con sus datos</li>
			<li>Complete esos datos rellenando el campo A con el IVA correspondiente</li>
			<li>Se completarán todos los campos de la facturas ACTUAL y NUEVA</li>
			<li>En la factura NUEVA MODIFICADA (SIMULACIÓN) introduzca en los campos B, C y D los datos que desee para efectuar la simulación</li>
			<li>Obtendrá en los campos en amarillo los resultados de la simulación</li>
			<li>Observe los efectos que tienen las variaciones del IVA y el Valor Social en el número de trabajadores, así como en el volumen de las ventas de la empresa</li>
			<li>Pulse en "GUARDAR" para que la simulación quede guardada en el historial de simulaciones</li>
		</ol>
	</div>
	<?php
}

function showTrB($simulador){
	?>
	<tr>
		<td class="blanco" colspan="<?php echo (count($simulador)+1); ?>"></td>
	<tr>
	<?php
}
function showTr($simulador, $simbolo, $campo, $dec, $titulo, $pintar = false){
	?>
	<tr>
		<td class="titulo"><?php echo $titulo; ?></td>
		<?php showTd($simulador, $simbolo, $campo, $dec, $pintar); ?>
	<tr>
	<?php
}

function showTd($simulador, $simbolo, $campo, $dec, $pintar){
	//$ancho = 75/count($simulador)."%";
	//$ancho = "50px";
	for($x=0; $x<count($simulador); $x++){
		$sty = "";
		if($pintar){
			if($simulador[$x][$campo]>0){
				$sty = "style='color: green;'";
			}elseif($simulador[$x][$campo]<0){
				$sty = "style='color: red;'";
			}else{
				$sty = "style='color: black;'";
			}
		}
		?><td class="dato" <?php echo $sty; ?>><?php echo seeNum($simulador[$x][$campo], $dec); ?> <?php echo $simbolo; ?></td><?php
	}
}




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

function seeNum($num, $dec = 2){
	return number_format($num, $dec, ",", ".");
}
?>