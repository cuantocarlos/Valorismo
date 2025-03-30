<?php
//No direct access
defined('_EmpleoWeb_') or die('Restricted access');
require_once('class/cSearch.php');
$search = new CSearch($sql);
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>Web de Empleo | Búsqueda</title>

		<!-- Bootstrap core CSS -->
		<link href="bootstrap3/css/bootstrap.css" rel="stylesheet">
		<!-- JavaScript plugins (requires jQuery) -->
		<script src="bootstrap3/js/jquery-1.10.2.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="bootstrap3/js/bootstrap.js"></script>
		
		<script type="text/javascript" src="js/jquery.tagsinput.min.js"></script>

		<!-- Custom CSS -->
		<link href="custom.css" rel="stylesheet">
		<link href="css/datatables.css" rel="stylesheet">
		<link href="css/jquery.tagsinput.css" rel="stylesheet">
	</head>

	<body>
		<div class="container">

			<a href="/inicio" class="btn btn-large btn-warning" style="min-width: 150px;">Volver</a>
			<br />
			
			<?php
			if(isset($_GET["trabajadores"])){
				?>
				<div class="body-content">
					<div style="width: 100%; margin: auto;" id="tosee">
						<?php		
						$search->showTable($gene);
						?>
					</div>
				</div>
				<br />
				<div class="masthead">
					<h3 class="text-muted">Búsqueda de Trabajadores</h3>
				</div>
				<!-- Jumbotron -->
				<div class="graybg">
					<div class="row">
						<div class="col-lg-10 col-offset-1">
							<form class="form-horizontal" action='#tosee' method='POST'>
								<h3>Ámbito</h3>
								<?php
								$search->showTags("Código Postal", "cp");
								$search->showTags("Localidad", "localidad");
								$search->showTags("Provincia", "provincia");
								$search->showTags("Autonomía", "autonomia");
								?>
								<div style="clear: both;"></div>
								<div class="col-lg-5 col-offset-1" style="margin-left: -8px;">
									<h3>C.N.A.E.</h3>
									<?php
									$search->showTags("Sector", "cnae", "", "col-lg-12");
									?>
								</div>
								<div class="col-lg-5  col-offset-1">
									<h3>Puesto ocupado</h3>
									<?php
									$search->showTags("Puesto", "puesto", "(Encargado, Secretario, Director, etc...)", "col-lg-12");
									?>
								</div>
								<div style="clear: both;"></div>
								<h3>Preparación</h3>
								<?php
								$search->showTags("Preparación", "preparacion", "(Graduado, Licenciado, Tecnico superior, etc...)", "col-lg-5");
								?>
								<div style="clear: both;"></div>
								<br />
								<button type="submit" class="btn btn-large btn-block btn-cons">Buscar</button>
								<div style="clear: both;"></div>
							</form>
							<hr />
							<form class="form-horizontal" action='#tosee' method='POST'>
								<h3>Por DNI</h3>
								<?php
								$search->showField("DNI", "dni", "", "col-lg-5");
								?>
								<div style="clear: both;"></div>
								<br />
								<button type="submit" class="btn btn-large btn-block btn-cons">Buscar</button>
								<br />
								<div style="clear: both;"></div>
							</form>
						</div>
					</div>
				</div>
				<?php
			}elseif(isset($_GET["empresas"])){
				?>
				<div class="body-content">
					<div style="width: 100%; margin: auto;" id="tosee">
						<?php		
						$search->showTableEmp($gene);
						?>
						<script language="javascript">
							jQuery(document).ready(function(){
								jQuery(".modales").click(function(){
									var ide = jQuery(this).attr("ide");					
									jQuery("#titulo_mod").text(jQuery(this).attr("titulo"));
									jQuery("#cargando").css("display", "inline");
									jQuery.post("m-desglosepuestos.php", { id: ide }, function(data){
										jQuery(".modal-body").html(data);
										jQuery("#cargando").css("display", "none");
										return false;
									});
								});
							});
						</script>
						
						<div class="modal fade" id="modal_boxe" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
										<h3 id="titulo_mod">TITULO</h3>
									</div>
									<div id="cargando" style="width: 50px; margin: auto; display: none;"><img src="images/loading.gif" alt="cargando..." title="cargando..." /></div>
									<div class="modal-body">
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<br />
				<div class="masthead">
					<h3 class="text-muted">Búsqueda de Empresas</h3>
				</div>
				<!-- Jumbotron -->
				<div class="graybg">
					<div class="row">
						<div class="col-lg-10 col-offset-1">
							<form class="form-horizontal" action='#tosee' method='POST'>
								<h3>Ámbito</h3>
								<?php
								$search->showTags("Código Postal", "cp");
								$search->showTags("Localidad", "localidad");
								$search->showTags("Provincia", "provincia");
								$search->showTags("Autonomía", "autonomia");
								?>
								<div style="clear: both;"></div>
								<div class="col-lg-5 col-offset-1" style="margin-left: -8px;">
									<h3>C.N.A.E.</h3>
									<?php
									$search->showTags("Sector", "cnae", "", "col-lg-12");
									?>
								</div>
								<div style="clear: both;"></div>
								<h3>Plazas disponibles</h3>
								<p>
									<span class="formwrapper">
										<?php
										if(isset($_POST["disponibles"])) $chk = 'checked="checked"'; else $chk = "";
										if(count($_POST) == 0) $chk = 'checked="checked"';
										?>
										<input type="checkbox" name="disponibles" <?php echo $chk; ?> /> Disponibles
									</span>
								</p>
								<div style="clear: both;"></div>
								<br />
								<button type="submit" class="btn btn-large btn-block btn-cons">Buscar</button>
								<div style="clear: both;"></div>
							</form>
							<hr />
							<form class="form-horizontal" action='#tosee' method='POST'>
								<h3>Por DNI</h3>
								<?php
								$search->showField("DNI", "dni", "", "col-lg-5");
								?>
								<div style="clear: both;"></div>
								<br />
								<button type="submit" class="btn btn-large btn-block btn-cons">Buscar</button>
								<br />
								<div style="clear: both;"></div>
							</form>
						</div>
					</div>
				</div>
				<?php
			}else{
				$gene->redirect("/inicio");
			}
			?>
		</div>
	</body>
</html>