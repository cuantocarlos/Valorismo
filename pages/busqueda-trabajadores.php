<?php
require_once('class/cSearch.php');
$search = new CSearch($sql);
cabecera($sql, $cusers, $gene, 8);
?>
<div class="body-content">
	<div style="width: 100%; margin: auto;" id="tosee">
		<?php
		if(isset($_POST["trabajador"]) && is_numeric($_POST["trabajador"]) && isset($_POST["token2"]) && $_POST["token2"] == md5($_POST["trabajador"]."_Jhsy26_") && isset($_POST["mensaje"])){
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
		}
		$search->showTable($gene);
		$search->showModal2();
		$search->showModalContact();
		?>
	</div>
</div>
<br />
<script type="text/javascript" src="js/jquery.tagsinput.min.js"></script>
<div class="widget">
	<h4 class="widgettitle">Buscar Trabajadores</h4>
	<div class="widgetcontent">
		<form class="stdform" action="#tosee" method="post">
			<h3>Ámbito</h3>
			<?php
			$search->showTags("Código Postal", "cp", "", "span6");
			$search->showTags("Localidad", "localidad", "",  "span6");
			$search->showTags("Provincia", "provincia", "",  "span6");
			$search->showTags("Autonomía", "autonomia", "",  "span6");
			?>
			<div style="clear: both;"></div>
			<h3>C.N.A.E.</h3>
			<?php
			$search->showTags("Sector", "cnae", "", "span10");
			?>
			<div style="clear: both;"></div>
			<h3>Puesto ocupado</h3>
			<?php
			$search->showTags("Puesto", "puesto", "(Encargado, Secretario, Director, etc...)", "span10");
			?>
			<div style="clear: both;"></div>
			<h3>Preparación</h3>
			<?php
			$search->showTags("Preparación", "preparacion", "(Graduado, Licenciado, Tecnico superior, etc...)", "span5");
			?>
			<div style="clear: both;"></div>
			<br />
			<button type="submit" class="btn btn-large btn-block btn-info">Buscar por filtros</button>
			<br />
			<div style="clear: both;"></div>
		</form>
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
	</div><!--widgetcontent-->
</div>
<?php
pie();
?>