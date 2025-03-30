<?php
cabecera($sql, $cusers, $gene, 12);
?>
<script type="text/javascript" src="js/chosen.jquery.min.js"></script>
<script>
	jQuery(document).ready(function(){
		jQuery(".chzn-select").chosen();
		jQuery("#porc_sector").spinner({min: 0, max: 100, step: 0.01 });
	});
</script>
<form method="POST">
	<?php
	$cnaes = $sql->runSelect("cnae", "1=1", "id, CONCAT(id, ' - ', cnae) as valor");
	showSelect("CNAE", "cnae", $cnaes, false);
	$provincias = $sql->runSelect("provincias", "1=1", "id, provincia as valor, autonomia as valor2", "autonomia, provincia");
	showSelect("Ámbito", "provincia", $provincias, true, "Toda españa");
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
	if(isset($_POST["saveData"])){
		foreach($_POST as $key => $value){
			if($key != "saveData")
				$save[$key] = $sql->fstr($value);
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
		$p_auto = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario", 
		                          "e.cnae = '".$d_cnae["id"]."'".$whereprov, 
								  "IF(SUM(valor_empresarial) IS NULL, 0, SUM(valor_empresarial)) AS valor_empresarial, 
								   IF(SUM(num_trabajadores) IS NULL, 0, SUM(num_trabajadores)) AS num_trabajadores, 
								   IF(AVG(coste_sal_medio) IS NULL, 1, 
								   AVG(coste_sal_medio)) AS coste_sal_medio");
		$valor_empresarial = $p_auto[0]["valor_empresarial"];
		$coste_lab_med = $p_auto[0]["coste_sal_medio"];
		$num_trabajadores = $p_auto[0]["num_trabajadores"];
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
			}
			.row_cus input{
				text-align: center !important;
				height: 22px;
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
					jQuery('#valor_social').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#porc_sector").val())/100)).toFixed(2));
					jQuery('#resultado').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#porc_sector").val())/100)/Number(jQuery("#coste_laboral_medio").val())).toFixed(0));
					checkTotal();
				});
				
				/*cambia el coste laboral medio*/
				jQuery("#coste_laboral_medio").change(function() {
					jQuery('#resultado').val((Number(jQuery("#valor_empresarial").val())*(Number(jQuery("#porc_sector").val())/100)/Number(jQuery("#coste_laboral_medio").val())).toFixed(0));
					checkTotal();
				});
				
				function checkTotal(){
					/*Veamos que el num de personas sea valido o negativo*/
					if(Number(jQuery('#coste_laboral_medio').val()) >= Number(jQuery('#valor_social').val())){
						jQuery('#resultado').val((((Number(jQuery('#coste_laboral_medio').val())-Number(jQuery('#valor_social').val()))/Number(jQuery('#valor_social').val()))*-1).toFixed(0));
					}
					jQuery('#num_trabajadores').val(Number(jQuery("#trabajadores").val())+Number(jQuery("#resultado").val()));
					jQuery('#porc_trabajadores').val((Number(jQuery("#resultado").val())*100/Number(jQuery("#num_trabajadores").val())).toFixed(2));
					pintar();
				}
				function pintar(){
					if(Number(jQuery('#resultado').val())>0){
						jQuery('#resultado').css("color", "green");
					}else if(Number(jQuery('#resultado').val())>0){
						jQuery('#resultado').css("color", "black");
					}else{
						jQuery('#resultado').css("color", "red");
					}
					if(Number(jQuery('#porc_trabajadores').val())>0){
						jQuery('#porc_trabajadores').css("color", "green");
					}else if(Number(jQuery('#porc_trabajadores').val())>0){
						jQuery('#porc_trabajadores').css("color", "black");
					}else{
						jQuery('#porc_trabajadores').css("color", "red");
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
								<th class="row_cus">Valor Empresarial Total</th>
								<th class="row_cus">% Valor Social del Sector</th>
								<th class="row_cus">Valor Social</th>
								<th class="row_cus">Coste Laboral Medio</th>
								<th class="row_cus">Incremento / Decremento Trabajadores</th>
								<th class="row_cus">Total Trabajadores</th>
								<th class="row_cus">% Aumento</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<?php
								$res = round((($valor_empresarial*$porc)/100)/($coste_lab_med));
								$porc_tr = round((round($res)*100/($num_trabajadores+round($res))), 2);
								if($res == 0) $color = "black"; elseif($res > 0) $color = "green"; else $color = "red";
								if($porc_tr == 0) $color2 = "black"; elseif($porc_tr > 0) $color2 = "green"; else $color2 = "red";
								?>
								<td class="row_cus"><div class="input-append"><input type="text" id="valor_empresarial" name="valor_empresarial" value="<?php echo ($valor_empresarial); ?>" readonly /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="porc_sector" name="porc_sector" class="input-small input-spinner" value="<?php echo $porc; ?>" /><span class="add-on">%</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="valor_social" name="valor_social" value="<?php echo round(($valor_empresarial*$porc)/100, 2); ?>" readonly /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="coste_laboral_medio" name="coste_laboral_medio" value="<?php echo round($coste_lab_med, 2); ?>" /><span class="add-on">€</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="resultado" style="font-weight: bold; color: <?php echo $color; ?>;" name="resultado" value="<?php echo $res; ?>" readonly /><span class="add-on">Tr</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="num_trabajadores" style="font-weight: bold;" name="num_trabajadores" value="<?php echo ($num_trabajadores+$res); ?>" readonly /><span class="add-on">Tr</span></div></td>
								<td class="row_cus"><div class="input-append"><input type="text" id="porc_trabajadores" style="font-weight: bold; color: <?php echo $color2; ?>;" name="porc_trabajadores" value="<?php echo $porc_tr; ?>" readonly /><span class="add-on">%</span></div></td>
							</tr>
						</tbody>
					</table>
					<input type="hidden" id="trabajadores" value="<?php echo ($num_trabajadores); ?>" disabled />
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
			$sql->runDelete("simulador", "cnae = '".$sql->fstr($_POST["cnae"])."'".$wheregueno);
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
					"sPaginationType": "full_numbers"
				});
			});
		</script>
			<div class="widgetbox box-inverse">
				<h4 class="widgettitle">Datos comparativos Simulador</h4>
				<div class="widgetcontent wc1">
					
					<table id="dyntable" class="table table-bordered responsive">
						<thead>
							<tr>
								<th class="row_cus">#</th>
								<th class="row_cus">Valor Empresarial</th>
								<th class="row_cus">% del V.S. Sector</th>
								<th class="row_cus">Valor Social</th>
								<th class="row_cus">Coste Laboral Medio</th>
								<th class="row_cus">Incremento / Decremento</th>
								<th class="row_cus">Trabajadores Totales</th>
								<th class="row_cus">% Trabajadores</th>
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
									<td class="row_cus"><span style="color: <?php echo $color; ?>;"><b><?php echo round($simulador[$x]["resultado"]); ?> puestos</b></span></td>
									<td class="row_cus"><?php echo round($simulador[$x]["num_trabajadores"]); ?> puestos</td>
									<td class="row_cus"><span style="color: <?php echo $color2; ?>;"><b><?php echo round($simulador[$x]["porc_trabajadores"], 2); ?> %</b></span></td>
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
					<input type="submit" name="delData" value="Borrar TODOS los registros del simulador" class="btn btn-danger" />
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

function seeNum($num){
	return number_format($num, 2, ",", ".");
}
?>