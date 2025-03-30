<?php
error_reporting(0);
require_once('class/cSearch.php');
$search = new CSearch($sql, "emp");
cabecera($sql, $cusers, $gene, 9);
?>
<div class="body-content">
	<div style="width: 100%; margin: auto;" id="tosee">
		<?php
		//echo $_POST["empresa"]."-keyTo".$_POST["oferta"]."Save".$cusers->id."secury";
		if(isset($_POST["empresa"]) && is_numeric($_POST["empresa"]) && isset($_POST["oferta"]) && is_numeric($_POST["oferta"]) && isset($_POST["token"]) && $_POST["token"] == sha1($_POST["empresa"]."-keyTo".$_POST["oferta"]."Save".$cusers->id."secury")){
			if($cusers->is_demo == "0"){
				/*SEND MENSAJE A LA EMPRESA*/
				/*¿que oferta?*/
				$oferta = $sql->runSelect("empresario_puestos t INNER JOIN usuarios u ON u.id_perfil = t.id_empresario LEFT JOIN empresario_sedes s ON t.lugar_trabajo = s.id", "t.id = '".$sql->fstr($_POST["oferta"])."' AND u.id = '".$sql->fstr($_POST["empresa"])."'", "t.*, s.sede", false, false, false, false, false, false);
				if(count($oferta)>0){
					$dts["emisor"] = $cusers->id;
					$dts["receptor"] = $sql->fstr($_POST["empresa"]);
					$dts["estado"] = "10";
					$dts["mensaje"] = "Hola, he visto que necesita trabajadores en su empresa y que cumplo los requisitos de ".$oferta[0]["titulacion_academica"]." además de otras preparaciones como ".$oferta[0]["otras_preparaciones"]." para el puesto en el departamento de ".$oferta[0]["departamento"]." en la categoria de ".$oferta[0]["categoria"].", además de que dispongo de disponibilidad para desplazarme hasta ".$oferta[0]["sede"].".<br /><br />Por favor puede revisar mi curriculum haciendo click en \"Ver curriculum\".<br /><br />Gracias de antemano y espero que sea de su interés";
					$rows = $sql->runInsert("mensajes", $dts);
					if($rows>0)
						$gene->showMessage("Se ha enviado su solicitud del puesto a la empresa seleccionada.<br />Puedes revisar el mensaje en la sección de mensajes", "success");
					else
						$gene->showMessage("Ha ocurrido un error al enviar la solicitud del puesto, por favor inténtelo de nuevo.");
				}
			}else{
				$gene->showMessage("El usuario DEMO no puede guardar ningún dato", "warning");
			}
		}
		$search->showTableEmp($gene);
		$search->showModal();
		?>
	</div>
</div>
<br />
<script type="text/javascript" src="js/jquery.tagsinput.min.js"></script>
<script type="text/javascript" src="js/chosen.jquery.min.js"></script>
<style>
.field{
	line-height: 15px;
}
.tagsinput{
	padding: 2px;
}
.tagsinput input{
	margin-bottom: 0px;
	height: 12px;
}
</style>
<div class="widget">
	<h4 class="widgettitle">Filtros de búsqueda</h4>
	<div class="widgetcontent">
		<form class="stdform" action="#tosee" method="post">
			<h3>Ubicación geográfica de la empresa</h3>
			<table width="100%">
				<?php
				
				$provincias = $sql->runSelect("provincias", "1 = 1", "provincia as id, provincia as valor");
				$autonomias = $sql->runSelect("provincias", "1 = 1", "DISTINCT autonomia as valor, autonomia as id");
				$cnaes = $sql->runSelect("cnae", "1 = 1", "cnae as id, CONCAT(id, ' - ', cnae) as valor");
				?>
				<tr>
					<td><?php $search->showTags("Código Postal", "cp", "", "span5"); ?></td>
					<td><?php $search->showTagsSearch("Provincia", "provincia", "",  "span5", $provincias); ?></td>
				</tr>
				<tr>
					<td><?php $search->showTags("Localidad", "localidad", "",  "span5"); ?></td>
					<td><?php $search->showTagsSearch("Autonomía", "autonomia", "",  "span5", $autonomias); ?></td>
				</tr>
			</table>
			<div style="clear: both;"></div>
			<table width="100%">
				<tr>
					<td><h3>Clasificación Nacional de Actividades Económicas (CNAE)</h3></td>
					<td rowspan="2"><p>
						<span class="formwrapper">
							<label>Plazas disponibles</label>
							<?php
							if(isset($_POST["disponibles"])) $chk = 'checked="checked"'; else $chk = "";
							if(count($_POST) == 0) $chk = 'checked="checked"';
							?>
							<input type="checkbox" name="disponibles" <?php echo $chk; ?> /> Disponibles
						</span>
					</p></td>
				</tr>
				<tr>
					<td><?php $search->showTagsSearch("Sector", "cnae", "", "span5", $cnaes, "width: 250px;"); ?></td>
				</tr>
			</table>
			
			<?php
			
			?>
			<div style="clear: both;"></div>
			<br />
			<button type="submit" class="btn btn-large btn-info">Buscar</button>
			<br />
			<div style="clear: both;"></div>
		</form>
		<!--
		<hr />
		<form class="form-horizontal" action='#tosee' method='POST'>
			<h3>Por CIF</h3>
			<?php
			$search->showField("CIF", "cif", "", "span10");
			?>
			<div style="clear: both;"></div>
			<br />
			<button type="submit" class="btn btn-large btn-block btn-info">Buscar por CIF</button>
			<br />
			<div style="clear: both;"></div>
			<br /><br />
		</form>
		-->
	</div><!--widgetcontent-->
</div>
<?php
pie();
?>