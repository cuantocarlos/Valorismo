<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//error_reporting(0);
ob_start();
define('_EmpleoWeb_', 'DefVal');
$error=0;
$errora=0;
$errorr=0;
$errorreg=Array(1=>"La dirección email ya existe.",2=>"La contraseña debe contener entre 4 y 20 caácteres",3=>"El formato del email es incorrecto.");
session_start();
require_once('class/DBclass.php');
require_once('class/cGeneral.php');
require_once('class/cUsers.php');

$sql = new Database();
$sql->connect();
$cusers = new cUsers($sql);
$gene = new cGeneral($sql, $cusers);


//Cierro Sesion
if(isset($_GET['closeses'])){
	setcookie( 'identificadorempleo', "close", time() ,'/', $cusers->domain, false, true);
	setcookie("identificadorempleo", "", time()-3600);
	unset($_SESSION["id_colegio"]);
	$gene->redirect("/");

}

//REGISTRO USUARIO
if(isset($_POST['email']) && isset($_POST['password'])){
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) || strlen($_POST['email'])>50 || strlen($_POST['email'])<5 ){
		$errorr=3;
	}elseif(strlen($_POST['password'])<4 || strlen($_POST['password'])>20){
		$errorr=2;
	}elseif($sql->runSelect("usuarios", "email='".$gene->fstr($_POST['email'])."'", "count(*)",false, 1, false,false,true,true)>0){
		$errorr=1;
	}else{
		if(isset($_POST['rol']) && $_POST['rol']==1){
			$rol=1;
		}else{
			$rol=2;
		}
		$toins['password']=sha1($sql->fstr($_POST['password']));
		$toins['email']=$sql->fstr($_POST['email']);
		$toins['rol']=$rol;
		$toins['estado']="1";
		$toins['creado']='NOW()';
		if($sql->runInsert("usuarios",$toins)){
			$cusers->checkUser($_POST['email'],$_POST['password'],"on");
			$gene->redirect("/inicio?login");
		}
	}
}

//LOGIN USUARIO
if(isset($_POST['regular']) && isset($_POST['pass'])){
	$mantener='off';
	if(isset($_POST['mantener'])){
		$mantener='on';
	}
	if($cusers->checkUser($_POST['regular'],$_POST['pass'],$mantener)){
		$gene->redirect("/inicio");
	}else{
		$error=1;
	}
}
if(isset($_POST['regulara']) && isset($_POST['pass'])){
	$mantener='off';
	if(isset($_POST['mantener'])){
		$mantener='on';
	}
	if($cusers->checkUser($_POST['regulara'],$_POST['pass'],$mantener)){
		$gene->redirect("/inicio");
	}else{
		$errora=1;
	}
}

$uri="/";
$get="";
$parseo=parse_url($_SERVER['REQUEST_URI']);
if(isset($parseo["path"])){
	$uri=$parseo["path"];
}
if(isset($parseo["query"])){
	parse_str($parseo["query"],$get);
}

$pages="pages/";
if($cusers->id>0){
	include_once("includes/design.php");
	$urlmod = $sql->runSelect("secciones");
	$var = "
		switch (\$uri){
		default: include('pages/notfound.php'); break;
		case '/': include('pages/miperfil.php'); break;
		case '/inicio': include('pages/miperfil.php'); break;";
	for($x=0; $x<count($urlmod); $x++){
		$var .= "case '".$urlmod[$x]["enlace"]."':";
	}
	$var .= "if (file_exists('pages/' . \$uri . '.php')) require_once 'pages/' . \$uri . '.php'; else include('pages/notfound.php');
			break; }";
	eval($var);
}else{
	if($uri == "/busqueda"){
		include($pages.'busqueda.php');
	}else{
		include($pages.'landing.php');
	}
}
ob_end_flush();

?>
