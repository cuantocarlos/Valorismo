<?php
cabecera($sql, $cusers, $gene, 12);
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
	showSelect("Área geográfica", "provincia", $provincias, true, "Toda españa");
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
	
	/*Guardamos los datos del simulador*/
	$est = Array(".", ",");
	$xes = Array("", ".");
	if(isset($_POST["saveData"])){
		foreach($_POST as $key => $value){
			if($key != "saveData"){
				if($key != "porc_sector")
					$save[$key] = $sql->fstr(str_replace($est, $xes, $value));
				else
					$save[$key] = $sql->fstr($value);
			}
		}
		$sql->runInsert("simulador", $save);
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
		/*
		$p_auto = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", 
		                          "e.cnae = '".$d_cnae["id"]."'".$whereprov, 
								  "IF(SUM(valor_empresarial) IS NULL, 0, SUM(valor_empresarial)) AS valor_empresarial, 
								   IF(SUM(num_trabajadores) IS NULL, 0, SUM(num_trabajadores)) AS num_trabajadores, 
								   IF(AVG(coste_sal_medio) IS NULL, 1, 
								   AVG(coste_sal_medio)) AS coste_sal_medio");
		*/
		/*
		VERSION ANTERIOR
		$p_auto = $sql->runSelect("(SELECT *
									FROM (
										SELECT eh.* 
										FROM empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario 
										WHERE e.cnae = '".$d_cnae["id"]."'".$whereprov." 
										ORDER BY eh.fecha DESC
									   ) eh
									GROUP BY id_empresario) tb", 
		                          "1 = 1", 
								  "IF(SUM(valor_empresarial) IS NULL, 0, SUM(valor_empresarial)) AS valor_empresarial, 
								   IF(SUM(num_trabajadores) IS NULL, 0, SUM(num_trabajadores)) AS num_trabajadores, 
								   IF(AVG(coste_sal_medio) IS NULL, 1, 
								   AVG(coste_sal_medio)) AS coste_sal_medio");
								   
		$valor_empresarial = $p_auto[0]["valor_empresarial"];
		$coste_lab_med = $p_auto[0]["coste_sal_medio"];
		$num_trabajadores = $p_auto[0]["num_trabajadores"];
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
		
		
		if($d_cnae["porcentaje_admin"] == ""){
			$porc = round($d_cnae["porcentaje_auto"], 2);
		}else{
			$porc = $d_cnae["porcentaje_admin"];
		}
		?>
		<style>
			.row_cus{
				text-align: center !important;
				font-size: 11px !important;
				vertical-align: middle !important;
			}
			.row_cus input{
				text-align: center !important;
				height: 22px;
				width: 80px !important;
			}
			#dyntable_wrapper {
				overflow-x: auto;
			}
		</style>
		<script type="text/javascript" src="js/ui.spinner.min.js"></script>
		<script>
			jQuery(document).ready(function(){
				/*VEAMOS LAS MODIFICACIONES*/
				/*Cambia el porcentaje del sector*/				
					
				jQuery("#porc_sector").change(function() {
					calcFormat(jQuery("#valor_empresarial").val());
					jQuery('#valor_social').val(SeeFormat((calcFormat(jQuery("#valor_empresarial").val())*(calcFormat(jQuery("#porc_sector").val())/10000)).toFixed(2)));
					ve = calcFormat(jQuery("#valor_empresarial").val());
					ps = calcFormat(jQuery("#porc_sector").val())/10000;
					clm = calcFormat(jQuery("#coste_laboral_medio").val());
					tra = calcFormat(jQuery("#trabajadores").val());
					jQuery('#resultado').val(SeeFormat(((ve*ps/clm)-(tra)).toFixed(0)));
					
					checkTotal();
				});
				
				/*cambia el coste laboral medio*/
				jQuery("#coste_laboral_medio").change(function() {
					ve = calcFormat(jQuery("#valor_empresarial").val());
					ps = calcFormat(jQuery("#porc_sector").val())/10000;
					clm = calcFormat(jQuery("#coste_laboral_medio").val());
					tra = calcFormat(jQuery("#trabajadores").val());
					jQuery('#resultado').val(SeeFormat(((ve*ps/clm)-(tra)).toFixed(0)));
					//jQuery('#resultado').val(SeeFormat((calcFormat(jQuery("#valor_empresarial").val())*(calcFormat(jQuery("#porc_sector").val())/10000)/calcFormat(jQuery("#coste_laboral_medio").val()))-calcFormat(jQuery("#trabajadores").val())).toFixed(0));
					checkTotal();
				});
				
				function checkTotal(){
					/*Veamos que el num de personas sea valido o negativo*/
					if(calcFormat(jQuery('#coste_laboral_medio').val()) >= calcFormat(jQuery('#valor_social').val())){
						jQuery('#resultado').val(SeeFormat((((calcFormat(jQuery('#coste_laboral_medio').val())-calcFormat(jQuery('#valor_social').val()))/calcFormat(jQuery('#valor_social').val()))*-1).toFixed(0)));
					}
					jQuery('#num_trabajadores').val(SeeFormat(calcFormat(jQuery("#trabajadores").val())+calcFormat(jQuery("#resultado").val())));
					jQuery('#porc_trabajadores').val(SeeFormat((calcFormat(jQuery("#resultado").val())*100/calcFormat(jQuery("#num_trabajadores").val())).toFixed(2)));
					pintar();
				}
				function pintar(){
					if(calcFormat(jQuery('#resultado').val())>0){
						jQuery('#resultado').css("color", "green");
					}else if(calcFormat(jQuery('#resultado').val())>0){
						jQuery('#resultado').css("color", "black");
					}else{
						jQuery('#resultado').css("color", "red");
					}
					if(calcFormat(jQuery('#porc_trabajadores').val())>0){
						jQuery('#porc_trabajadores').css("color", "green");
					}else if(calcFormat(jQuery('#porc_trabajadores').val())>0){
						jQuery('#porc_trabajadores').css("color", "black");
					}else{
						jQuery('#porc_trabajadores').css("color", "red");
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
					//alert(num);
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
		<h3><?php echo $d_cnae["id"]." - ".$d_cnae["cnae"]; if(isset($prov[0]["provincia"])){ echo " <span style='color: darkblue;'>para la provincia de</span> ".$prov[0]["provincia"]." (".$prov[0]["autonomia"].")"; }?></h3>
		<div class="row-fluid">              
			<div class="span12">
				<form method="POST">
					<table class="table table-bordered responsive">
						<thead>
							<tr>
								<th class="row_cus">Valor Empresarial<br />Total</th>
								<th class="row_cus">% Valor Social<br />del Sector</th>
								<th class="row_cus">Valor<br />Social</th>
								<th class="row_cus">Coste Laboral<br />Medio</th>
								<th class="row_cus">Trabajadores<br />Actuales</th>
								<th class="row_cus">Aumento / Dismunición<br />de trabajadores</th>
								<th class="row_cus">Total<br />Trabajadores</th>
								<th class="row_cus">% Aumento<br />de trabajadores</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<?php
								$nuevoNumeroTrabajadores = round(((($valor_empresarial*$porc)/100)/($salario_medio)))-$numero_trabajadores;
								$porc_tr = round((round($nuevoNumeroTrabajadores)*100/($numero_trabajadores+round($nuevoNumeroTrabajadores))), 2);
								if($nuevoNumeroTrabajadores == 0) $color = "black"; elseif($nuevoNumeroTrabajadores > 0) $color = "green"; else $color = "red";
								if($porc_tr == 0) $color2 = "black"; elseif($porc_tr > 0) $color2 = "green"; else $color2 = "red";
								?>
								<td class="row_cus"><div class="input-append"><input type="text" id="valor_empresarial" name="valor_empresarial" value="<?php echo seeNum($valor_empresarial); ?>" readonly /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="porc_sector" name="porc_sector" class="input-small input-spinner" value="<?php echo ($porc); ?>" /><span class="add-on">%</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="valor_social" name="valor_social" value="<?php echo seeNum(($valor_empresarial*$porc)/100); ?>" readonly /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="coste_laboral_medio" name="coste_laboral_medio" value="<?php echo seeNum($salario_medio); ?>" /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="num_trabajadoresA" name="num_trabajadoresA" style="font-weight: bold;" value="<?php echo seeNum($numero_trabajadores, 0); ?>" readonly /><span class="add-on">Tr</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="resultado" style="font-weight: bold; color: <?php echo $color; ?>;" name="resultado" value="<?php echo seeNum($nuevoNumeroTrabajadores, 0); ?>" readonly /><span class="add-on">Tr</span></div></td>
								
								<td class="row_cus"><div class="input-append"><input type="text" id="num_trabajadores" style="font-weight: bold;" name="num_trabajadores" value="<?php echo seeNum($numero_trabajadores+$nuevoNumeroTrabajadores, 0); ?>" readonly /><span class="add-on">Tr</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="porc_trabajadores" style="font-weight: bold; color: <?php echo $color2; ?>;" name="porc_trabajadores" value="<?php echo seeNum($porc_tr); ?>" readonly /><span class="add-on">%</span></div></td>
							</tr>
						</tbody>
					</table>
					<input type="hidden" id="trabajadores" value="<?php echo seeNum($numero_trabajadores, 0); ?>" disabled />
					<input type="hidden" name="cnae" value="<?php echo $_POST["cnae"]; ?>" />
					<?php
					if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
						?><input type="hidden" name="provincia" value="<?php echo $_POST["provincia"]; ?>" /><?php
					}
					?>
					
					<br />
					<div style="text-align: center">
						<input type="submit" name="saveData" value="Guardar datos" class="btn btn-info btn-large" />
					</div>
				</form>
				<br /><br />
			</div><!--span6-->
		</div>
		<?php
		/*SACAMOS LOS DATOS DEL SIMULADOR anteriores*/
		if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
			$wheregueno = " AND provincia = '".$sql->fstr($_POST["provincia"])."'";
		}else{
			$wheregueno = " AND provincia = '0'";
		}
		/*Check for DEL DATA*/
		if(isset($_POST["delData"])){
			if(is_numeric($_POST["delData"])){
				$sql->runDelete("simulador", "cnae = '".$sql->fstr($_POST["cnae"])."' AND id = '".$sql->fstr($_POST["delData"])."'".$wheregueno);
			}else{
				$sql->runDelete("simulador", "cnae = '".$sql->fstr($_POST["cnae"])."'".$wheregueno);
			}
		}
		
		$simulador = $sql->runSelect("simulador", "cnae = '".$sql->fstr($_POST["cnae"])."'".$wheregueno, "*", "fecha DESC");
		if(count($simulador)>0){
			?>
			<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="js/responsive-tables.js"></script>
			<script type="text/javascript">
			jQuery(document).ready(function(){
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
					"aaSorting": [[ 1, "desc" ]],
					"sPaginationType": "full_numbers"
				});
			});
		</script>
			<div class="widgetbox">
				<h4 class="widgettitle">Datos comparativos Simulador</h4>
				<div class="widgetcontent wc1">
					
					<table id="dyntable" class="table table-bordered responsive">
						<thead>
							<tr>
								<th class="row_cus">#</th>
								<th class="row_cus">Valor<br />Empresarial</th>
								<th class="row_cus">% del Valor<br />Social del Sector</th>
								<th class="row_cus">Valor<br />Social</th>
								<th class="row_cus">Coste Laboral<br />Medio</th>
								<th class="row_cus">Trabajadores<br />Actual</th>
								<th class="row_cus">Nuevo Número<br />Trabajadores</th>
								<th class="row_cus">Aumento<br />Trabajadores</th>
								<th class="row_cus">% Aumento<br />de trabajadores</th>
								<th class="row_cus">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<?php
							for($x=0; $x<count($simulador); $x++){
								if($simulador[$x]["resultado"]==0){
									$color = "black";
								}elseif($simulador[$x]["resultado"]>0){
									$color = "green";
								}else{
									$color = "red";
								}	
								if($simulador[$x]["porc_trabajadores"]==0){
									$color2 = "black";
								}elseif($simulador[$x]["porc_trabajadores"]>0){
									$color2 = "green";
								}else{
									$color2 = "red";
								}	
								?>
								<tr>
									<td class="row_cus"><span style="display: none;"><?php echo date("YmdHis", strtotime($simulador[$x]["fecha"])); ?></span><?php echo $x; ?></td>
									<td class="row_cus"><?php echo seeNum($simulador[$x]["valor_empresarial"]); ?> €</td>
									<td class="row_cus"><?php echo round($simulador[$x]["porc_sector"], 2); ?> %</td>
									<td class="row_cus"><?php echo seeNum($simulador[$x]["valor_social"]); ?> €</td>
									<td class="row_cus"><?php echo seeNum($simulador[$x]["coste_laboral_medio"]); ?> €</td>
									<td class="row_cus"><?php echo seeNum($simulador[$x]["num_trabajadoresA"],0); ?> puestos</td>
									<td class="row_cus"><?php echo seeNum($simulador[$x]["num_trabajadores"],0); ?> puestos</td>
									<td class="row_cus"><span style="color: <?php echo $color; ?>;"><b><?php echo seeNum($simulador[$x]["resultado"],0); ?> puestos</b></span></td>
									<td class="row_cus"><span style="color: <?php echo $color2; ?>;"><b><?php echo round($simulador[$x]["porc_trabajadores"], 2); ?> %</b></span></td>
									<td class="row_cus">
										<form method="POST">
											<input type="hidden" name="cnae" value="<?php echo $_POST["cnae"]; ?>" />
											<input type="hidden" name="delData" value="<?php echo $simulador[$x]["id"]; ?>" />
											<input type="submit" value="Borrar" style="font-size: 10px; padding-top: 1px;" class="btn btn-danger btn-circle" />
										</form>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<form method="POST">
				<input type="hidden" name="cnae" value="<?php echo $_POST["cnae"]; ?>" />
				<?php
				if(isset($_POST["provincia"]) && is_numeric($_POST["provincia"])){
					?><input type="hidden" name="provincia" value="<?php echo $_POST["provincia"]; ?>" /><?php
				}
				?>
				<div style="text-align: center;">
					<input type="submit" name="delData" value="Borrar TODOS" class="btn btn-danger btn-small" />
				</div>
			</form>
			<?php
		}else{
			$gene->showMessage("Aún no hay datos para comparar en el simulador", "info");
		}
		
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

function seeNum($num, $dec = 2){
	return number_format($num, $dec, ",", ".");
}
?>