<?php
function cabecera($sql, $cusers, $gene, $seccion){
$seccion = $sql->runSelect("secciones", "id = '".$sql->fstr($seccion)."' AND (rol = '".$cusers->rol."' OR rol = '0')");
if(count($seccion) == 1){
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Valorismo - <?php echo $seccion[0]["seccion"]; ?></title>
<link rel="stylesheet" href="css/style.default.css" type="text/css" />

<link rel="stylesheet" href="css/responsive-tables.css">
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="js/jquery-migrate-1.1.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.9.2.min.js"></script>
<script type="text/javascript" src="js/modernizr.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/jquery.uniform.min.js"></script>
<!--
<script type="text/javascript" src="js/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="js/flot/jquery.flot.resize.min.js"></script>
-->
<script type="text/javascript" src="js/responsive-tables.js"></script>
<script type="text/javascript" src="js/custom.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
<?php
if($cusers->rol == 1){
	?><link rel="stylesheet" href="css/style.navyblue.css" type="text/css" /><?php
}elseif($cusers->rol == 2){
	?><link rel="stylesheet" href="css/style.palegreen.css" type="text/css" /><?php
}elseif($cusers->rol == 3){
	?><link rel="stylesheet" href="css/style.red.css" type="text/css" /><?php
}
?>
</head>

<body>

<div class="mainwrapper">
    
    <div class="header">
        <div class="logo">
            <a href="\inicio"><img src="images/logo.png" alt="" /></a>
        </div>
        <div class="headerinner">
            <ul class="headmenu">
				<?php
				/*Sacar los menus de arriba*/
				$menusup = $sql->runSelect("secciones", "(rol = '".$cusers->rol."' OR rol = '0') AND (situacion = '11' OR situacion = '01')", "*", "orden");
				$tipo = "";
				for($x=0; $x<count($menusup); $x++){
					if($tipo == ""){
						$tipo = "odd";
					}else{
						$tipo = "";
					}
					if($menusup[$x]["enlace"] == "/mensajes"){
						/*Cuantos Mensajes*/
						$cuantos = $sql->runSelect("mensajes", "receptor = '".$cusers->id."' AND estado LIKE '%0'", "COUNT(*) as cuantos"); $cuantos = $cuantos[0]["cuantos"];
					}
					?>
					<li class="<?php echo $tipo; ?>" style="text-align: center;">
						<a href="<?php echo $menusup[$x]["enlace"]; ?>">
							<span class="count"><?php if(isset($cuantos) && $cuantos > 0) echo $cuantos; ?></span>
							<!--<span class="head-icon head-<?php echo $menusup[$x]["icono"]; ?>"></span>-->
							<span class="iconfa-<?php echo $menusup[$x]["icono"]; ?>" style="font-size: 50px;"></span>
							<span class="headmenu-label"><?php echo $menusup[$x]["seccion"]; ?></span>
						</a>
					</li>
					<?php
				}
				?>
                <li class="right">
                    <div class="userloggedinfo">
						<?php
						/*Info de usuario*/
						if($cusers->rol == 1){
							$datos = $sql->runSelect("consumidor", "id = '".$sql->fstr($cusers->id_perfil)."'", "CONCAT(nombre, ' ', apellidos) as nombre", false, false, false, false, false, false, 0);
						}elseif($cusers->rol == 2){
							$datos = $sql->runSelect("empresario", "id = '".$sql->fstr($cusers->id_perfil)."'", "nombre", false, false, false, false, false, false, 0);
						}else{
							/*es el admin*/
							$datos[0]["nombre"] = "Administrador";
						}
						if(!isset($datos[0]["nombre"])) $datos[0]["nombre"] = "Desconocido";
						?>
                        <img src="images/photos/<?php echo $cusers->avatar; ?>" alt="<?php echo $datos[0]["nombre"]; ?>" />
                        <div class="userinfo">
							<?php
							if(strlen($datos[0]["nombre"])>20){
								$datos[0]["nombre"] = substr($datos[0]["nombre"], 0, 20)."...";
							}
							?>
                            <h5><?php echo $datos[0]["nombre"]; ?><!-- <small>- <?php echo $cusers->email; ?></small>--></h5>
                            <ul>
                                <li><a href="/miperfil">Editar Perfil</a></li>
                                <li><a href="#" style="background: none;">&nbsp;</a></li>
                                <li><a href="?closeses=0">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul><!--headmenu-->
        </div>
    </div>
    <?php
	$menulat = $sql->runSelect("secciones", "(rol = '".$cusers->rol."' OR rol = '0') AND (situacion = '11' OR situacion = '10') AND depende_de = 0", "*", "orden");
	if(count($menulat)>0){
	?>
    <div class="leftpanel">
        
        <div class="leftmenu">        
            <ul class="nav nav-tabs nav-stacked">
            	<li class="nav-header">Menú principal</li>
				<?php
				/*Sacamos el menu lateral*/
				
				for($x=0; $x<count($menulat); $x++){
					if($menulat[$x]["id"] == $seccion[0]["id"]) $cls = "active"; else $cls = "";
					
						/*check if subsections!*/
						$submen = $sql->runSelect("secciones", "(rol = '".$cusers->rol."' OR rol = '0') AND (situacion = '11' OR situacion = '10') AND depende_de = '".$sql->fstr($menulat[$x]["id"])."'", "*", "orden");
						if(count($submen)>0){
							?>
							<li class="dropdown <?php echo $cls; ?>"><a href=""><span class="iconfa-<?php echo $menulat[$x]["icono"]; ?>"></span> <?php echo $menulat[$x]["seccion"]; ?></a>
								<ul>
								<?php
								for($y=0; $y<count($submen); $y++){
									?><li><a href="<?php echo $submen[$y]["enlace"]; ?>"><?php echo $submen[$y]["seccion"]; ?></a></li><?php
								}
								?>
								</ul>
							</li>
							<?php
						}else{
							?>
							<li class="<?php echo $cls; ?>"><a href="<?php echo $menulat[$x]["enlace"]; ?>"><span class="iconfa-<?php echo $menulat[$x]["icono"]; ?>"></span> <?php echo $menulat[$x]["seccion"]; ?></a></li>
							<?php
						}
				}
				?>
            </ul>
        </div><!--leftmenu-->
        
    </div><!-- leftpanel -->
    <?php
	}
	?>
    <div class="rightpanel2" style="background: url('images/bg1.png');">
	
        
        <ul class="breadcrumbs">
            <li><a href="\inicio"><i class="iconfa-home"></i></a> <span class="separator"></span></li>
            <li><?php echo $seccion[0]["seccion"]; ?></li>
        </ul>
        
        <div class="pageheader">
            <div class="pageicon"><span class="iconfa-<?php echo $seccion[0]["icono2"]; ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $seccion[0]["descripcion"]; ?></h5>
                <h1><?php echo $seccion[0]["seccion"]; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
<?php
	}else{
		$gene->redirect("\inicio?noperm");
	}	
}

function pie(){
?>
                <!-- Site footer -->
				<div class="footer">
					<a href="https://www.linkedin.com/in/fcocarlosbeltran/">
						<p>&copy;2025 Desarrollador</p>
					</a>
				</div>
				
            </div><!--maincontentinner-->
        </div><!--maincontent-->
    </div><!--rightpanel-->
    
</div><!--mainwrapper-->
</body>
</html>

<?php
}
?>