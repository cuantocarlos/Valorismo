<?php

cabecera($sql, $cusers, $gene, 1);
$gene->redirect("miperfil");
if(isset($_GET["noperm"])){
	$gene->showMessage("No tienes permisos para ver esta sección.");
}
?>
<link rel="stylesheet" href="custom.css" type="text/css">
<div class="maincontent">
	<div class="maincontentinner">
		<div class="row-fluid">
			<div id="dashboard-left" class="span12">
				<!--
				<h5 class="subtitle">Secciones</h5>
				<ul class="shortcuts">
					<?php
					$menulat = $sql->runSelect("secciones", "(rol = '".$cusers->rol."' OR rol = '0') AND situacion <> '00'", "*", "orden");
					for($x=0; $x<count($menulat); $x++){
						?>
						<li class="events">
							<a style="min-width: 130px; width: auto;" href="<?php echo $menulat[$x]["enlace"]; ?>">
								<span class="shortcuts-icon iconfa-<?php echo $menulat[$x]["icono2"]; ?>" style="font-size: 40px;"></span>
								<span class="shortcuts-label" style="text-align: center;"><?php echo $menulat[$x]["seccion"]; ?></span>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
				<br />
				-->
				<div style="clear: both;"></div>
				<?php
				showStat($sql, "empresario", "Empresas", "blue", "building");
				showStat($sql, "consumidor", "Trabajadores", "red", "group");
				showStat($sql, "mensajes", "Mensajes", "purple", "envelope");
				showStat($sql, "empresario_puestos", "Ofertas", "yellow", "inbox", "SUM(personas_admitir)");
				showStat($sql, "consumidor_archivos", "Curriculums", "green", "paper-clip");
				if($cusers->rol != 3) $addWhere = " AND user_id = '".$cusers->id."'";
				showStat($sql, "logs", "Accesos", "orange", "dashboard", "COUNT(*)", "`action` LIKE 'Acceso Aceptado%'".$addWhere);
				?>
			</div>
		</div>
	</div>
</div>
<?php
pie();


function showStat($sql, $table, $label, $color, $icon, $stati = "COUNT(*)", $where = "1=1"){
	?>
	<div data-desktop="span2" data-tablet="span6" class="span2 responsive" style="margin-left: 0; margin-right: 10px;">
		<div class="dashboard-stat <?php echo $color; ?>">
			<div class="visual">
				<span class="shortcuts-icon iconfa-<?php echo $icon; ?>" style="font-size: 72px; color: #fff;"></span>
			</div>
			<div class="details">
				<?php
				$stat = $sql->runSelect($table, $where, $stati." as cuantos"); $stat = $stat[0]["cuantos"];
				?>
				<div class="number"><?php echo $stat; ?></div>
				<div class="desc"><?php echo $label; ?></div>
			</div>
		</div>
	</div>
	<?php
}
?>