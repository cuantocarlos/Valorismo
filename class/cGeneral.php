<?php
class cGeneral {
	var $dbc;
	var $cusers;
	function cGeneral($_sql, $cusers) {
	  $this->dbc = $_sql;
	  $this->cusers = $cusers;
	}
	
	function showMessage($msg, $type="error", $close = true){
		if($type == "error"){
			$alert = " alert-error";
			$name = "Error: ";
		}elseif($type == "info"){
			$alert = " alert-info";
			$name = "Info: ";
		}elseif($type == "success"){
			$alert = " alert-success";
			$name = "Correcto: ";
		}else{
			$alert = "";
			$name = "Aviso: ";
		}
		?>
		<div class="alert alert-block <?php echo $alert; ?>">
			  <?php if($close){ ?><button data-dismiss="alert" class="close" type="button">&times;</button><?php } ?>
			  <h4><?php echo $name; ?></h4>
			  <p style="margin: 8px 0"><?php echo $msg; ?></p>
		</div><!--alert-->
		<?php
	}
	
	function checkEmail($email, $obligatorio = 1){
		if($obligatorio != 1 && $email == ""){
			return true;
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			$var = explode("@", $email);
			$var2 = explode(".", $var[1]);
			if(isset($var2[1]) && strlen($var2[1])>=2){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	function checkEmailInDB($email, $id){
		if($this->checkEmail($email)){
			$rows = $this->dbc->runSelect("usuarios", "email = '".$this->dbc->fstr($email)."' AND id <> '".$id."'");
			if(count($rows)>0){
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}
	function checkcnaeInDB($cnae){
		$rows = $this->dbc->runSelect("cnae", "id = '".$this->dbc->fstr($cnae)."'");
		if(count($rows)>0){
			return true;
		}else{
			return false;
		}

	}
	function checkDomain($domain, $id){
		if(strlen($domain)>=3){
			$rows = $this->dbc->runSelect("clients", "subdomain = '".strtolower($this->cleanSite($this->dbc->fstr($domain)))."' AND id <> '".$id."'");
			if(count($rows)>0){
				return false;
			}else{
				return $this->is_clean_sub_reg($domain);
			}
		}else{
			return false;
		}
	}
	function is_clean_sub($subd){
		$list=Array("blog","beta","marketing","online");
		if(in_array($subd,$list)){
				return false;
		}
		return true;
	}
	function is_clean_sub_reg($subd){
		$list=Array("blog","www","wwww","wwwww","tuintra","beta","marketing","online");
		if(in_array($subd,$list)){
				return false;
		}
		return true;
	}
	function cleanSite($site){
		$site = strtolower($site);
		$esto = Array("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü", "ç", "ñ", " ");
		$poresto = Array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "c", "n", "_");
		$site = str_replace($esto, $poresto, $site);
		$site = preg_replace('/[^a-z0-9_%&-]/s', '', $site);
		return $site;
	}	
	
	function checkLenght($var, $lenght, $obligatorio = 1){
		if($obligatorio == 1){
			if(strlen($var) >= $lenght){
				return true;
			}else{
				return false;
			}
		}else{
			/*no es obligatorio*/
			if($var == "" || strlen($var) >= $lenght){
				return true;
			}else{
				return false;
			}
		}
	}
	
	function checkPassword($pass1, $pass2){
		if(strlen($pass1) >= 8){
			if($pass1 == $pass2){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	function checkTelefono($telefono, $obligatorio=1, $largo = 9){
		if(preg_match("/^[0-9-+]+$/", $telefono) && strlen($telefono) >= $largo) { 
			return true;
		}
		if($obligatorio == 0){
			if($telefono == ""){
				return true;
			}
		}
		return false;
	}
	function checkDNI($dni, $obligatorio = 1){
			$dni = strtoupper($dni);
			if($obligatorio == 1){
				if(strlen($dni)>8 && $this->validateNif($dni)){
					return true;
				}else{
					if(strlen($dni)>8 && $this->validateCif($dni)){
						return true;
					}
				}
				return false;
			}else{
				if($dni == ""){
					return true;
				}else{
					if(strlen($dni)>8 && $this->validateNif($dni)){
						return true;
					}else{
						if(strlen($dni)>8 && $this->validateCif($dni)){
							return true;
						}
					}
					return false;
				}
			}
	}
	
	function getCifSum ($cif) {
		$sum = $cif[2] + $cif[4] + $cif[6];
		for ($i = 1; $i<8; $i += 2) {
			$tmp = (string) (2 * $cif[$i]);
			$tmp = $tmp[0] + ((strlen ($tmp) == 2) ?  $tmp[1] : 0);
			$sum += $tmp;
		}
		return $sum;
	}
 
	function validateCif ($cif) {
		$cif_codes = 'JABCDEFGHI';
		$sum = (string) $this->getCifSum ($cif);
		$n = (10 - substr ($sum, -1)) % 10;
		if (preg_match ('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)) {
			if (in_array ($cif[0], array ('A', 'B', 'E', 'H'))) {
				// Numerico
				return ($cif[8] == $n);
			} elseif (in_array ($cif[0], array ('K', 'P', 'Q', 'S'))) {
				// Letras
				return ($cif[8] == $cif_codes[$n]);
			} else {
				// Alfanumérico
				if (is_numeric ($cif[8])) {
					return ($cif[8] == $n);
				} else {
					return ($cif[8] == $cif_codes[$n]);
				}
			}
		}
		return false;
	}

	function validateNif ($nif) {
		$nif_codes = 'TRWAGMYFPDXBNJZSQVHLCKE';
		$sum = (string) $this->getCifSum ($nif);
		$n = 10 - substr($sum, -1);
 
		if (preg_match ('/^[0-9]{8}[A-Z]{1}$/', $nif)) {
			$num = substr($nif, 0, 8);
 
			return ($nif[8] == $nif_codes[$num % 23]);
		} elseif (preg_match ('/^[XYZ][0-9]{7}[A-Z]{1}$/', $nif)) {
			$tmp = substr ($nif, 1, 7);
			$tmp = strtr(substr ($nif, 0, 1), 'XYZ', '012') . $tmp;
 
			return ($nif[8] == $nif_codes[$tmp % 23]);
		} elseif (preg_match ('/^[KLM]{1}/', $nif)) {
			return ($nif[8] == chr($n + 64));
		} elseif (preg_match ('/^[T]{1}[A-Z0-9]{8}$/', $nif)) {
			return true;
		}
		return false;
	}
	
	function checkNacionalidad($nacionalidad, $obligatorio = 1){
		if($obligatorio != 1){
			if($nacionalidad == ""){
				return true;
			}
		}
		$valid = $this->cusers->sqli->runSelect("nacionalidad", "nacionalidad_es = '".$this->dbc->fstr($nacionalidad)."' OR nacionalidad_en = '".$this->dbc->fstr($nacionalidad)."'");
		if(count($valid)>0 && $nacionalidad != ""){
			return true;
		}
		return false;
	}
	function checkDNIde($dni_de, $obligatorio = 1){
		if($obligatorio != 1){
			if($dni_de == ""){
				return true;
			}
		}
		$arr_valid = Array("de la persona"  => 1,
							"del padre" => 2,
							"de la madre" => 3,
							"del tutor" => 4);
		if(isset($arr_valid[$dni_de])){
			return true;
		}
		return false;
	}
	
	function getDNIdeValue($dni_en){
		$arr_valid = Array("de la persona"  => 1,
							"del padre" => 2,
							"de la madre" => 3,
							"del tutor" => 4);
		if(isset($arr_valid[$dni_en])){
			return $arr_valid[$dni_en];
		}else{
			return "";
		}
	}
	
	function getNacionalidadValue($nacionalidad){
		$valid = $this->cusers->sqli->runSelect("nacionalidad", "nacionalidad_es = '".$this->dbc->fstr($nacionalidad)."' OR nacionalidad_en = '".$this->dbc->fstr($nacionalidad)."'");
		if(count($valid)>0 && $nacionalidad != ""){
			return $valid[0]["id"];
		}
		return "";
	}
	
	function showText($texto){
		$texto = $this->wordchars($texto); //Quita si han copiado de word!
		$texto = str_replace("\n", "<br>", $texto);
		return $texto;
	}
	
	function wordchars($string){
		$quotes = array(
			"\xC2\xAB"     => '"', // « (U+00AB) in UTF-8
			"\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
			"\xE2\x80\x98" => "'", // ‘ (U+2018) in UTF-8
			"\xE2\x80\x99" => "'", // ’ (U+2019) in UTF-8
			"\xE2\x80\x9A" => "'", // ‚ (U+201A) in UTF-8
			"\xE2\x80\x9B" => "'", // ‛ (U+201B) in UTF-8
			"\xE2\x80\x9C" => '"', // “ (U+201C) in UTF-8
			"\xE2\x80\x9D" => '"', // ” (U+201D) in UTF-8
			"\xE2\x80\x9E" => '"', // „ (U+201E) in UTF-8
			"\xE2\x80\x9F" => '"', // ‟ (U+201F) in UTF-8
			"\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
			"\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
			"\xe2\x80\xa6" => '...', // ‟ (U+201F) in UTF-8
			"\xe2\x80\x94" => '--', // ‹ (U+2039) in UTF-8
			"\xe2\x80\x93" => '-', // › (U+203A) in UTF-8
		);
		return strtr($string, $quotes);
	}
	
	function logaction($userid,$action){
		$action=$this->fstr($action);
		$toins=Array("user_id" => $userid ,"ip" => $this->return_true_ip(),"action" => $action,"fecha" => "NOW()");
		$this->dbc->runInsert("logs", $toins, NULL);
		
		return true;
		
	}
	
	function return_true_ip()
	{
	
			return $_SERVER['REMOTE_ADDR'];
		
	}
	
	function fstr($str)
	{
		$str=mysql_real_escape_string(htmlspecialchars(trim($str)));
		return $str;			
	}
	
	function RandomString($length=10,$lc=FALSE,$uc=TRUE,$n=TRUE,$sc=FALSE){
		$source = "";
		if($lc==1) $source .= 'abcdefghijklmnopqrstuvwxyz';
		if($uc==1) $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($n==1) $source .= '1234567890';
		if($sc==1) $source .= '|@#~$%()=^*+[]{}-_';
		if($length>0){
			$rstr = "";
			$source = str_split($source,1);
			for($i=1; $i<=$length; $i++){
				mt_srand((double)microtime() * 1000000);
				$num = mt_rand(1,count($source));
				$rstr .= $source[$num-1];
			}

		}
		return $rstr;
	}
	
	
	function sendmail($to,$subject,$msg){
	require_once('Mandrill.php');

	$Mandrill = new Mandrill("jT0YK9Q9tM0YWBlD-7oXEw");


	$params = array(
			"html" => "$msg",
			"text" => null,
			"from_email" => "info@tuintra.com",
			"from_name" => "Tuintra.com",
			"subject" => "$subject",
			"to" => array(array("email" => "$to")),
			"track_opens" => true,
			"track_clicks" => true,
			"auto_text" => true
	);

	$Mandrill->messages->send($params, true);
		
		
		
	}
	function redirect($to){
	//echo "TO:$to";
		//return true;
		header("Location: $to");
		die("No hay mas");
	}
	function creardb($name){
		$enlace = mysql_connect('localhost', 'tuintra', 'peportics');
		if (!$enlace) {
			return "error";
			//die('No pudo conectarse: ' . mysql_error());
		}
		//** CREAR ZONAS PARA ARCHIVOS/data/bddname crear una carpeta con el nombre de la bdd y zona_publica
		//** Por defecto asignar una plantilla
		$sql = "CREATE DATABASE $name";
		if (mysql_query($sql, $enlace)) {
			return "correcto";
			//Dar PErmisos al user tuintra
			$nsql="GRANT SELECT , INSERT , UPDATE , DELETE , CREATE , DROP , INDEX , ALTER , CREATE VIEW , SHOW VIEW , CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `$name` . * TO 'tuintra'@'%';";
			mysql_query($nsql, $enlace);
		} else {
			return "error";
			//echo 'Error al crear la base de datos: ' . mysql_error() . "\n";
		}
	
	}
	function getGeneralModules(){
		$modules = Array(
						-2 => Array("id" => 0, "header" => 0, "icon" => "log_book", "url" => "m-tasks",
								   "modulename_es" => $this->translate->_('m-tasks'),
								   "modulename_en" => $this->translate->_('m-tasks')),
						-1 => Array("id" => 0, "header" => 0, "icon" => "user", "url" => "my-profile-user",
								   "modulename_es" => $this->translate->_('my-profile-user'),
								   "modulename_en" => $this->translate->_('my-profile-user')),
						0 => Array("id" => 0, "header" => 0, "icon" => "eyedropper", "url" => "my-colors",
								   "modulename_es" => $this->translate->_('my-colors'),
								   "modulename_en" => $this->translate->_('my-colors')),
					    1 => Array("id" => 0, "header" => 0, "icon" => "shield", "url" => "my-shields",
								   "modulename_es" => $this->translate->_('my-shields'),
								   "modulename_en" => $this->translate->_('my-shields')),
						2 => Array("id" => 0, "header" => 0, "icon" => "building", "url" => "my-directors",
								   "modulename_es" => $this->translate->_('my-directors'),
								   "modulename_en" => $this->translate->_('my-directors')),
						3 => Array("id" => 0, "header" => 0, "icon" => "charts", "url" => "my-stats",
								   "modulename_es" => $this->translate->_('my-stats'),
								   "modulename_en" => $this->translate->_('my-stats')),
						4 => Array("id" => 0, "header" => 0, "icon" => "more_windows", "url" => "my-modules",
								   "modulename_es" => $this->translate->_('my-modules'),
								   "modulename_en" => $this->translate->_('my-modules')),
						5 => Array("id" => 0, "header" => 0, "icon" => "shopping_cart", "url" => "my-plan",
								   "modulename_es" => $this->translate->_('my-plan'),
								   "modulename_en" => $this->translate->_('my-plan'))
						/*
						6 => Array("id" => 0, "header" => 0, "icon" => "coins", "url" => "my-sponsors",
								   "modulename_es" => $this->translate->_('my-sponsors'),
								   "modulename_en" => $this->translate->_('my-sponsors'))
						*/
						);
		return $modules;
	}
	
	function pageHeader($module, $default = ""){
		if($default == ""){
			$page = $this->dbc->runSelect("modules", "id = '".$module."'");
		}else{
			$mdl = $this->getGeneralModules();
			$page[0] = $mdl[$default];
		}
		if(count($page) == 1){
			if(!$this->cusers->getEntry($page[0]["id"]) && $module >=0) header("Location: index");
			?>
			<ul class="breadcrumb">
				<li><a href="index" class="glyphicons home"><i></i> <?php echo $this->cusers->name; ?></a></li>
				<li class="divider"></li>
				<li><?php echo $page[0]["modulename_".$this->locale]; ?></li>
			</ul>
			<div class="separator"></div>
			<h3 class="glyphicons <?php echo $page[0]["icon"]; ?>"><i></i> <?php echo $page[0]["modulename_".$this->locale]; ?></h3>
			<?php
		}else{
			?><br /><?php
			$this->showMessage("");
		}
	}
	
	function showRol($rol){
		/*Socio, entrenador, jugador, directivo*/
		if(substr($rol,0,1) == 1){ ?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('j_rol_member'); ?>" title="<?php echo $this->translate->_('j_rol_member'); ?>" class="btn-action glyphicons vcard btn-warning"><i></i></a><?php }
		if(substr($rol,1,1) == 1){ ?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('j_rol_coach'); ?>" title="<?php echo $this->translate->_('j_rol_coach'); ?>" class="btn-action glyphicons tie btn-warning"><i></i></a><?php }
		if(substr($rol,2,1) == 1){ ?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('j_rol_player'); ?>" title="<?php echo $this->translate->_('j_rol_player'); ?>" class="btn-action glyphicons star btn-warning"><i></i></a><?php }
		if(substr($rol,3,1) == 1){ ?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('j_rol_director'); ?>" title="<?php echo $this->translate->_('j_rol_director'); ?>" class="btn-action glyphicons briefcase btn-warning"><i></i></a><?php }
	}
	function showGender($gender){
		if($gender == "F"){
			?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('t_female'); ?>" title="<?php echo $this->translate->_('t_female'); ?>" class="btn-action glyphicons female btn-danger"><i></i></a><?php
		}elseif($gender == "M"){
			?><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $this->translate->_('t_male'); ?>" title="<?php echo $this->translate->_('t_male'); ?>" class="btn-action glyphicons male btn-info"><i></i></a><?php
		}else{
			?><a href="#" class="btn-action glyphicons asterisk btn-success"><i></i></a><?php
		}
	}
	function showDniDe($de){
		$tipo = Array(1=>$this->translate->_('dni_owner'),
					  2=>$this->translate->_('dni_father'),
					  3=>$this->translate->_('dni_mother'),
					  4=>$this->translate->_('dni_tutor'));
		
		if(isset($tipo[$de])) return $tipo[$de]; else return "";
	}
	function uppermodules($id){
		$result=$this->recuuppermodules($id);
		$res= explode("**",substr($result, 0, -2));
		return $res;
	}
	function recuuppermodules($id,$res=""){
		$papi = $this->dbc->runSelect("modules", "id = '".$id."'", "parent_id",false, 1, false,false,true,true);
		//echo $papi;
		if($papi!=2){
			$res=$papi."**".$this->recuuppermodules($papi,$res);
		}
		//print_r($res);
		
		return $res;
	}
	function downermodules($id){
		$result=$this->recudownermodules($id);
		$res= explode("**",substr($result, 0, -2));
		return $res;
	}
	function recudownermodules($id,$res=""){
		$hiji = $this->dbc->runSelect("modules", "parent_id = '".$id."'", "id",false, false, false,false,false,false);
		//echo $papi;
		for($y=0; $y<count($hiji); $y++){ 
			//echo $hiji[$y]['id']."-";
			
			if($hiji[$y]['id']!= 2 && strpos($res,"**".$hiji[$y]['id']."**")==false){
				$res.=$hiji[$y]['id']."**".$this->recudownermodules($hiji[$y]['id'],$res);
			}
		}
		//print_r($res);
		
		return $res;
	}
	
	function getTemporada(){
		if(date("m") > 8){
			/*Estamos en la temporada del año actual + el siguiente*/
			$actual = date("Y")." - ".(date("Y")+1);
		}else{
			$actual = (date("Y")-1)." - ".date("Y");
		}
		return $actual;
	}
	
	function showCombo($data, $span, $field, $default){
		$var = "";
		?>
			<div class="span<?php echo $span; ?>">
				<label class="control-label" for="<?php echo $field; ?>"><?php echo $this->translate->_($field); ?></label>
				<select class="span<?php echo $span; ?>" name="<?php echo $field; ?>" id="<?php echo $field; ?>">
					<?php
					for($x=0; $x<count($data); $x++){
						if($data[$x]["id"] == $default) $sel = "selected"; else $sel = "";
						if($field == "p_competicion") $var = 'id="'.$data[$x]["categoria"].'" ambito="'.$data[$x]["ambito"].'" ';
						if($field == "p_categoria") $var = 'id="'.$data[$x]["categoria"].'" ';
						if($field == "p_ambito") $var = 'id="'.$data[$x]["ambito"].'" ';
						?><option value="<?php echo $data[$x]["id"]; ?>" <?php echo $var.$sel; ?>><?php echo $data[$x]["valor"]; ?></option><?php
					}
					?>
				</select>
			</div>
		<?php
	}
	function createfromsd($name){
		if(strpos($name,'.') == true){
			return $name;
		}else{
			return $name.".tuintra.com";
		}
	}
	
}
?>