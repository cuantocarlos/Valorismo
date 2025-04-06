<?php
//No direct access
defined('_EmpleoWeb_') or die('Restricted access'); 
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Valorismo.es</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap3/css/bootstrap.css" rel="stylesheet">
	 <!-- JavaScript plugins (requires jQuery) -->
    <script src="bootstrap3/js/jquery-1.10.2.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bootstrap3/js/bootstrap.js"></script>
	<!-- Custom CSS -->
	<link href="custom.css" rel="stylesheet">
	<style>
	html, body{
		font-size: 12px;
		font-family: 'RobotoRegular', 'Helvetica Neue', 'Helvetica', 'sans-serif' !important;
	}
	</style>
  </head>

  <body>

    <div class="container">

      <div class="masthead">
        <h3 class="text-muted">Valorismo.es</h3>
      </div>

      <!-- Jumbotron -->
      <div class="graybg">
			<div class="row">
			  <div class="col-lg-5 col-offset-1">
				<h2>Trabajador</h2>
				<form class="form-horizontal" action='' method='POST'>
				  <div class="form-group">
					<div class="col-lg-7">
					  <input type="text" class="form-control" id="inputEmail" name="regular" placeholder="Email">
					</div>
				  </div>
				  <div class="form-group">
					<div class="col-lg-7">
					  <input type="password" class="form-control" id="inputPassword" name="pass" placeholder="Password">
					  <div class="checkbox">
						
						  <p style="font-size:16px;"><input name='mantener' type="checkbox"> Recordarme</p>
						
					  </div>
					  <button type="submit" class="btn btn-cons" class="width: 200px;">Acceder</button>  
					</div>
				  </div>
				</form>

			  </div>
			  
			  <div class="col-lg-5 col-offset-1">
				<h2>Empresa</h2>
				<form class="form-horizontal" action='' method='POST'>
				  <div class="form-group">
					<div class="col-lg-7">
					  <input type="text" class="form-control" id="inputEmail" name="regular" placeholder="Email">
					</div>
				  </div>
				  <div class="form-group">
					<div class="col-lg-7">
					  <input type="password" class="form-control" id="inputPassword" name="pass" placeholder="Password">
					  <div class="checkbox">
						
						  <p style="font-size:16px;"><input name='mantener' type="checkbox"> Recordarme</p>
						
					  </div>
					  <button type="submit" class="btn btn-empr" class="width: 200px;">Acceder</button>
					</div>
				  </div>
				</form>
			  </div>
			</div>
      </div>
		<div class="row">
		<?php
		if($error>0){
			?>
			<div class="alert alert-danger">
              <button data-dismiss="alert" class="close" type="button">×</button>
              <strong>Error</strong> al iniciar sesión.
            </div>
			<?php
		}
		?>
		</div>

		<div class="body-content">

			<!-- Example row of columns -->
			<div class="row" style="margin-top: -40px;">
			  <div class="col-lg-5  col-offset-1">
				<h2></h2>
				<p><button href="#myReg" data-toggle="modal" class="btn btn-cons" style="width: 200px;">Alta nuevo trabajador</button></p>
				<form method="POST">
					<input type="hidden" name="regular" value="demo@trabajador.com">
					<input type="hidden" name="pass" value="demo">
					<input type="submit" class="btn btn-cons" style="width: 200px;" value="Ver la demo de trabajador" />
				</form>
				<!-- <p><a href="busqueda?empresas" class="btn btn-cons" style="width: 200px;">Busqueda de empresas</a></p> -->
			  </div>
			  
			  <div class="col-lg-5  col-offset-1">
				<h2></h2>
				<p><button href="#myReg" data-toggle="modal" class="btn btn-empr" style="width: 200px;">Alta nueva empresa</button></p>
				<form method="POST">
					<input type="hidden" name="regular" value="demo@empresario.com">
					<input type="hidden" name="pass" value="demo">
					<input type="submit" class="btn btn-empr" style="width: 200px;" value="Ver la demo de empresario" />
				</form>
				<!-- <p><a href="busqueda?trabajadores" class="btn btn-empr" style="width: 200px;">Busqueda de trabajadores</a></p> -->
			  </div>
			</div>
			<script>
			$( document ).ready(function() {
				// Handler for .ready() called.
				$( ".btn-cons" ).click(function() {
					$("#myReg .modal-title").html("Nuevo Trabajador");
					$("#myReg .rol").val("1");
				});
				$( ".btn-empr" ).click(function() {
					$("#myReg .modal-title").html("Nueva Empresa");
					$("#myReg .rol").val("2");
				});
				
			});
			//#myReg .modal-title .html
			//#myReg .rol	.val
			</script>
			
			<!-- Modal -->
					<div class="modal fade" id="myReg">
						<?php 
						$user="";
						$out="";
						if($errorr>0){
							$user=$_POST['email'];
							$rol=2;
							$msg="Nueva empresa";
							if($_POST['rol']==1){
								$rol=1;
								$msg="Nuevo Trabajador";
							}
							$out='
							<div class="alert alert-danger">
							  <button data-dismiss="alert" class="close" type="button">×</button>
							  <strong>Error</strong> '.$errorreg[$errorr].'
							</div>
							';
							?>
							<script>
							$( document ).ready(function() {
								// Handler for .ready() called.
									$("#myReg .modal-title").html("<?php echo $msg; ?>");
									$("#myReg .rol").val("<?php echo $rol; ?>");
									$('#myReg').modal('show')
							});
							//#myReg .modal-title .html
							//#myReg .rol	.val
							</script>
							<?php
						}
						?>
						<div class="modal-dialog">
						  <div class="modal-content">
							<div class="modal-header">
							  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							  <h4 class="modal-title">Nuevo Trabajador</h4>
							</div>
							<form class="form-horizontal ajax" action='' method='POST'>
								<div class="modal-body">
									
									
									<?php echo $out; ?>
									<div class="form-group">
										<label class="control-label" for="login">Email</label>
										<input type="text" class="form-control" value="<?php echo $user; ?>" name="email" placeholder="Email">
									</div>
									<div class="form-group">
										<label class="control-label" for="password">Password</label>
										<input type="password" class="form-control" name="password" placeholder="Password">
									</div>
									
								</div>
								<div class="modal-footer">
									<input type="hidden" name="rol" class='rol' value="1">
									<button type='submit' class="btn btn-info">Entrar</button>
								</div>
							</form>
						  </div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					  </div><!-- /.modal -->
		</div><!-- /.body-content -->
		
		<div class="row">
			<div class="col-lg-3 col-offset-4">
				<button  href="#admin" data-toggle="modal"  class="btn btn-admin btn-large">Administración</button>
							<!-- Modal -->
					<div class="modal fade" id="admin">
						<?php 
						$outa="";
						$user="";
						if($errora>0){
							$user=$_POST['regulara'];
							$outa='
							<div class="alert alert-danger">
							  <button data-dismiss="alert" class="close" type="button">×</button>
							  <strong>Error</strong> al iniciar sesión.
							</div>
							';
							?>
							<script>
							$( document ).ready(function() {
								// Handler for .ready() called.
									$("#admin .userlogin").val("<?php echo $_POST['regulara']; ?>");
									$('#admin').modal('show')
							});
							//#myReg .modal-title .html
							//#myReg .rol	.val
							</script>
							<?php
						}
						?>
						<div class="modal-dialog">
						  <div class="modal-content">
							<div class="modal-header">
							  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							  <h4 class="modal-title">Administración</h4>
							</div>
							<form class="form-horizontal ajax" action='' method='POST'>
								<div class="modal-body">
									
									
									<?php echo $outa; ?>
									<div class="form-group">
										<label class="control-label" for="login">Email</label>
										<input type="text" class="form-control" class='userlogin' value="<?php echo $user; ?>" name="regulara" placeholder="Email">
									</div>
									<div class="form-group">
										<label class="control-label" for="password">Password</label>
										<input type="password" class="form-control" name="pass" placeholder="Password">
									</div>
									
								</div>
								<div class="modal-footer">
									<button type='submit' class="btn btn-info">Enviar</button>
								</div>
							</form>
						  </div><!-- /.modal-content -->
						</div><!-- /.modal-dialog -->
					  </div><!-- /.modal -->
			</div>
		</div>
	 
		<!-- Site footer -->
		<div class="footer">
			<p>&copy;2025 Valorismo.es</p>
		</div>

    </div> <!-- /container -->

  </body>
</html>