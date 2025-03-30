<?php
error_reporting(E_ALL);
session_start();
require_once('class/DBclass.php');
require_once('class/cGeneral.php');
require_once('class/cUsers.php');
$sql = new Database();
$sql->connect();
$cusers = new cUsers($sql);
$gene = new cGeneral($sql, "", $cusers, "es");

if(isset($_POST["id"]) && is_numeric($_POST["id"])){
	$rows = $sql->runSelect("empresario_puestos p LEFT JOIN empresario_sedes s ON p.lugar_trabajo = s.id", "p.id_empresario = '".$sql->fstr($_POST["id"])."'", "p.*, s.sede", false, false, false, false, false, false);
	if(count($rows)>0){
		?>
		<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/responsive-tables.js"></script>
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
					<th class="row_cus">Departamento</th>
					<th class="row_cus">Categoría</th>
					<th class="row_cus">Titulación</th>
					<th class="row_cus">Otras capacidades</th>
					<th class="row_cus">Plazas / Salidas</th>
					<th class="row_cus">Lugar</th>
					<th class="row_cus">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				for($x=0; $x<count($rows); $x++){
					?>
					<tr>
						<td class="row_cus"><?php echo $rows[$x]["departamento"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["categoria"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["titulacion_academica"]; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["otras_preparaciones"]; ?></td>
						<td class="row_cus"><?php echo "<span style='color: green;'>".$rows[$x]["personas_admitir"]."</span> / <span style='color: red;'>".$rows[$x]["personas_salida"]."</span>"; ?></td>
						<td class="row_cus"><?php echo $rows[$x]["sede"]; ?></td>
						<td>
							<?php
							if($cusers->id == 0){
								$emp = $sql->runSelect("usuarios", "id_perfil = '".$sql->fstr($_POST["id"])."'", "email", false, false, false, false, false, false);
								if(isset($emp[0]["email"]) && $emp[0]["email"] != ""){
									?>
									<a href="mailto:<?php echo $emp[0]["email"] ?>">Solicitar!</a><?php
								}
							}else{
								/*Se manda mensaje Privado*/
								$rowsid = $sql->runSelect("usuarios", "id_perfil = '".$sql->fstr($_POST["id"])."' AND rol = 2", "id", false, false, false, false, false, false);
								?>
								<form action="busqueda-empresa" method="POST">
									<input type="hidden" value="<?php echo $rowsid[0]["id"]; ?>" name="empresa" />
									<input type="hidden" value="<?php echo $rows[$x]["id"]; ?>" name="oferta" />
									<input type="hidden" value="<?php echo sha1($rowsid[0]["id"]."-keyTo".$rows[$x]["id"]."Save".$cusers->id."secury"); ?>" name="token" />
									<input type="submit" value="Solicitar" class="btn btn-block btn-success" />
								</form>
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
		//echo $rowsid[0]["id"]."-keyTo".$rows[0]["id"]."Save".$cusers->id."secury";
		?>
		<br />
		<br />
		<?php

	}else{
		$gene->showMessage("No se han encontrado registro de desglose de puestos de trabajo y trabajadores", "info");
	}
}else{
	$gene->showMessage("No se han encontrado registro de desglose de puestos de trabajo y trabajadores", "info");
}
?>