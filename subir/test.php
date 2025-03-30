<?php
require_once('class/DBclass.php');

$sql = new Database();
$sql->connect();


$seeCNAE = false;
$seeHistorico = false;
$divide_historico = false;
$pepo = false;

$porcentajeGeneral = false;
$porcentajeProvincia = false;

if($porcentajeGeneral){
	$cnae = $sql->runSelect("cnae");
	for($x=0; $x<count($cnae); $x++){
		$data_emp = $sql->getSql("SELECT IF(SUM(num_trabajadores)IS NULL, '0', SUM(num_trabajadores)) AS nt, 
									     IF(SUM(valor_empresarial) IS NULL, '0', SUM(valor_empresarial)) AS ve, 
									     IF(AVG(salario_medio) IS NULL, '0', AVG(salario_medio)) AS sm,
										 iva
								  FROM (
										SELECT * FROM (
														SELECT * FROM empresario_historico_ine WHERE id_empresa IN (SELECT id FROM empresario e WHERE e.cnae = '".$cnae[$x]["id"]."')
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
		
		if($valor_empresarial == 0) $valor_empresarial = 1;
		$p_valor_social = round($importe_salarial_total*100/$valor_empresarial, 2);
		
		?>
		UPDATE cnae_provincia SET porcentaje_auto = '<?php echo $p_valor_social; ?>' WHERE cnae = '<?php echo $cnae[$x]["id"]; ?>';<br />
		<?php
	}
}

if($porcentajeProvincia){
	$cnae = $sql->runSelect("cnae");
	$prov = $sql->runSelect("provincias");
	for($x=0; $x<count($cnae); $x++){
		for($y=0; $y<count($prov); $y++){
			$data_emp = $sql->getSql("SELECT IF(SUM(num_trabajadores)IS NULL, '0', SUM(num_trabajadores)) AS nt, 
											 IF(SUM(valor_empresarial) IS NULL, '0', SUM(valor_empresarial)) AS ve, 
											 IF(AVG(salario_medio) IS NULL, '0', AVG(salario_medio)) AS sm,
											 iva
									  FROM (
											SELECT * FROM (
															SELECT * FROM empresario_historico_ine WHERE id_empresa IN (SELECT id FROM empresario e WHERE e.cnae = '".$cnae[$x]["id"]."' AND e.provincia = '".$prov[$y]["id"]."')
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
			
			if($valor_empresarial != 0){
				$p_valor_social = round($importe_salarial_total*100/$valor_empresarial, 2);
			?>
			UPDATE cnae_provincia SET porcentaje_auto = '<?php echo $p_valor_social; ?>' WHERE cnae = '<?php echo $cnae[$x]["id"]; ?>' AND provincia = '<?php echo $prov[$y]["id"]; ?>';<br />
			<?php
			}
		}
	}
}


if($seeCNAE){
	$prov = $sql->runSelect("provincias");
	$cnae = $sql->runSelect("cnae");
	for($x=0; $x<count($cnae); $x++){
		for($y=0; $y<count($prov); $y++){
			$p_auto = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario",
									  "e.cnae = '".$cnae[$x]["id"]."' AND e.provincia = '".$prov[$y]["id"]."'",
									  "IF(SUM(coste_sal_total) IS NULL, 0, SUM(coste_sal_total)) AS coste_sal_total, 
									   IF(SUM(importe_nominas) IS NULL, 0, SUM(importe_nominas)) AS importe_nominas,
									   IF(SUM(ventas_totales) IS NULL, 1, SUM(ventas_totales)) AS ventas_totales"
			,false,false,false,false,false,false);
			$p_auto = $p_auto[0];
			$saveauto["porcentaje_auto"] = $p_auto["importe_nominas"]*100/$p_auto["ventas_totales"];
			if($saveauto["porcentaje_auto"] != 0)
			echo "UPDATE cnae_provincia SET porcentaje_auto = ".$saveauto["porcentaje_auto"]." WHERE cnae = '".$cnae[$x]["id"]."' AND provincia = '".$prov[$y]["id"]."';<br />";
			
			//$sql->runUpdate("cnae", $saveauto, "id = '".$cnae_e."'");
		}
	}
}
if($seeHistorico){
	$pocern = $sql->runSelect("empresario e INNER JOIN empresario_historico eh ON e.id = eh.id_empresario INNER JOIN cnae_provincia cnae ON cnae.cnae = e.cnae AND cnae.provincia = e.provincia",
							"1=1",
							"DISTINCT cnae.porcentaje_auto, eh.id_empresario", false, false, false, false, false, false);
	for($x=0; $x<count($pocern); $x++){
		if($pocern[$x]["porcentaje_auto"] != "0.00")
		echo "UPDATE empresario_historico SET porc_sector = ".$pocern[$x]["porcentaje_auto"]." WHERE id_empresario = '".$pocern[$x]["id_empresario"]."';<br />";
		
		//$sql->runUpdate("cnae", $saveauto, "id = '".$cnae_e."'");
		
	}
}
if($divide_historico){
	$hist = $sql->runSelect("empresario_historico");
	$ins = "";
	$cont = 0;
	for($x=0; $x<count($hist); $x++){
		/*Vemos los datos a dividir*/
		$fecha = substr($hist[$x]["fecha"], 0, 4);
		unset($hist[$x]["id"]);
		for($m=2; $m<=12; $m++){
			$hist[$x]["fecha"] = $fecha."-".str_pad($m, 2, "0", STR_PAD_LEFT)."-01";
			$ins .= $sql->runInsert("empresario_historico", $hist[$x], NULL, 0, false, $cont+$m-2);
		}
		$cont = $cont+1;
		if($cont == 300){
			$cont = 0;
			$ins = substr($ins, 0, strlen($ins)-7).";<br /><br /><br />";
			echo $ins;
			$ins = "";
		}
		

		//$sql->runUpdate("cnae", $saveauto, "id = '".$cnae_e."'");
		
	}
	$ins = substr($ins, 0, strlen($ins)-7).";<br /><br /><br />";
	echo $ins;
	//echo "UPDATE empresario_historico SET porc_sector = ".$pocern[$x]["porcentaje_auto"]." WHERE id = '".$pocern[$x]["id"]."';<br />";
}

if($pepo){

?>
<style>
	table td{
		border: 1px solid black;
		text-align: center;
		font-size: 10px;
	}
	.negro{
		background-color: black;
		color: white;
	}
	.blanco{
		background-color: white;
		color: black;
	}
</style>
<?php
	//for($esteban=1; $esteban<=10; $esteban++){
		pintarTabla(19, 19);
	//}
	
}
function pintarTabla($filas, $columnas){
	?>
	<table style="width: 1000px; height: 1000px;">
		<?php
		for($y=0; $y<$filas; $y++){
			?>
			<tr>
				<?php
					for($x=0; $x<$columnas; $x++){
						$Nfil = ($y+1);
						$Ncol = ($x+1);
						//   ! = NO
						
						if($Nfil%2 != 0){
							/*ES IMPAR*/
							if($Ncol%2 == 0){
								/*ES PAR*/
								$clase = "negro";
							}else{
								$clase = "blanco";
							}
							
						}else{
							/*ES PAR*/
							if($Ncol%2 != 0){
								/*ES IMPAR*/
								$clase = "negro";
							}else{
								$clase = "blanco";
							}
						}
						
						
						
						if(($Nfil%2 == 0 && $Ncol%2 == 0) || ($Nfil%2 != 0 && $Ncol%2 != 0)){
							$clase = "negro";
						}else{
							$clase = "blanco";
						}
						
						
						
						
						$clase = "blanco";
						if(($Nfil+$Ncol)%2 == 0) $clase = "negro";
						

						?>
						<td class="<?php echo $clase; ?>" style="width: <?php echo (100/$columnas); ?>%;">
							<?php echo $Nfil."-".$Ncol; ?>
						</td>
						<?php
					}
				?>
			</tr>
			<?php
		}
		?>
	</table>
	<br /><br />
	<?php
}

?>