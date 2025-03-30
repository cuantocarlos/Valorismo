<?php
require_once('class/cSearch.php');
$search = new CSearch($sql);
cabecera($sql, $cusers, $gene, 8);
?>
<div class="body-content">
	<div style="width: 100%; margin: auto;" id="tosee">
		<?php
		if(isset($_POST["trabajador"]) && is_numeric($_POST["trabajador"]) && isset($_POST["token2"]) && $_POST["token2"] == md5($_POST["trabajador"]."_Jhsy26_") && isset($_POST["mensaje"])){
			if($cusers->is_demo == "0"){
				/*SEND MENSAJE AL trabajador*/
				if(strlen($_POST["mensaje"]) > 3){
					$dts["emisor"] = $cusers->id;
					$dts["receptor"] = $sql->fstr($_POST["trabajador"]);
					$dts["estado"] = "10";
					$dts["mensaje"] = $sql->fstr($_POST["mensaje"]);
					$rows = $sql->runInsert("mensajes", $dts);
					if($rows>0)
						$gene->showMessage("Se ha enviado el mensaje al trabajador.<br />Puedes revisar el mensaje en la sección de mensajes", "success");
					else
						$gene->showMessage("Ha ocurrido un error al enviar el mensaje.");
				}else{
					$gene->showMessage("El mensaje es demasiado corto, al menos 3 carácteres!");
				}
			}else{
				$gene->showMessage("El usuario DEMO no puede guardar ningún dato", "warning");
			}
		}
		$search->showTable($gene);
		$search->showModal2();
		$search->showModalContact();
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
			<h3>Lugar de Residencia</h3>
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
					<td><h3>Puesto ocupado</h3></td>
				</tr>
				<tr>
					<td><?php $search->showTagsSearch("Sector", "cnae", "", "span5", $cnaes, "width: 250px;"); ?></td>
					<style>
						#puesto_tagsinput{
							width: 262px !important; 
						}
					</style>
					<td><?php $search->showTags("", "puesto", "(Encargado, Secretario, Director, etc...)", "span5", "width: 220px;"); ?></td>
				</tr>
			</table>
			<div style="clear: both;"></div>
			<table width="100%">
				<tr>
					<td><h3>Preparación</h3></td>
					<td rowspan="2" style="vertical-align: bottom;"><button type="submit" class="btn btn-large btn-block btn-info">Buscar por filtros</button></td>
				</tr>
				<tr>
					<td><?php $search->showTags("", "preparacion", "(Graduado, Licenciado, Tecnico superior, etc...)", "span5"); ?></td>
				</tr>
			</table>
			
			<?php
			
			?>
			<div style="clear: both;"></div>
		</form>
		<!--
		<hr />
		<form class="form-horizontal" action='#tosee' method='POST'>
			<h3>Por DNI</h3>
			<?php
			$search->showField("DNI", "dni", "", "span10");
			?>
			<div style="clear: both;"></div>
			<br />
			<button type="submit" class="btn btn-large btn-block btn-info">Buscar por DNI</button>
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