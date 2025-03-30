<?php
//error_reporting(E_ALL);
cabecera($sql, $cusers, $gene, 5);

/*ACTIONS*/
if(isset($_GET["msg"]) && is_numeric($_GET["msg"]) && isset($_GET["token"]) && $_GET["token"] == sha1("Sec_".$_GET["msg"]."_rec:".$cusers->id)){
	/*Comprobar si el mensaje le pertenece y sacar conversacion*/
	$getmsg = $sql->runSelect("mensajes", "id = '".$sql->fstr($_GET["msg"])."' AND (emisor = ".$cusers->id." OR receptor = '".$cusers->id."')", "emisor, receptor");
	if(count($getmsg)==1){
		/*Sacamos la conversacion*/
		if($getmsg[0]["emisor"] == $cusers->id){
			$otro = $getmsg[0]["receptor"];
		}else{
			$otro = $getmsg[0]["emisor"];
		}
		if(isset($_GET["action"])){
			if($_GET["action"] == "dele"){
				/*MARCAMOS COMO BORRADO (emisor)*/
				changeState($sql, $otro, $cusers->id, "2", 2);
				/*MARCAMOS COMO BORRADO (receptor)*/
				changeState($sql, $cusers->id, $otro, "2", 1);
			}elseif($_GET["action"] == "spam"){
				/*ALERTAS*/
				$tsv["id_alertado"] = $sql->fstr($_GET["msg"]);
				$tsv["id_usuario"] = $sql->fstr($cusers->id);
				$sql->runInsert("alertas", $tsv);
				$gene->showMessage("El mensaje ha sido marcado como spam!<br />Este mensaje lo revisará un administrador y efectuará las acciones necesarias", "success");
			}elseif($_GET["action"] == "notr"){
				/*MARCAMOS COMO NO LEIDO (emisor)*/
				changeState($sql, $otro, $cusers->id, "0", 2);
				/*MARCAMOS COMO NO LEIDO (receptor)*/
				changeState($sql, $cusers->id, $otro, "0", 1);
			}
		}
		if(isset($_POST["respuesta"]) && strlen($_POST["respuesta"])>0){
			$parasave["emisor"] = $cusers->id;
			$parasave["receptor"] = $otro;
			$parasave["mensaje"] = $sql->fstr($_POST["respuesta"]);
			$parasave["estado"] = "10";
			$sql->runInsert("mensajes", $parasave);
		}
		
	}
}
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('.msglist li').click(function(){
        jQuery('.msglist li').each(function(){ jQuery(this).removeClass('selected')});
        jQuery(this).addClass('selected');
        
        // for mobile
        jQuery('.msglist').click(function(){
            if(jQuery(window).width() < 480) {
                jQuery('.messageright, .messagemenu .back').show();
                jQuery('.messageleft').hide();
            }
        });
        
        jQuery('.messagemenu .back').click(function(){
            if(jQuery(window).width() < 480) {
                jQuery('.messageright, .messagemenu .back').hide();
                jQuery('.messageleft').show();
            }
        });
    });
});
</script>
<div class="messagepanel">
	<!--
	<div class="messagehead">
		<button class="btn btn-success btn-large">Nuevo Mensaje</button>
	</div>
	-->
	<div class="messagemenu">
		<ul>
			<?php
			$sec_be = ""; $sec_en = ""; $sec_pa = ""; $where = "1 = 1"; $select = "*, id as ide";
			if(isset($_GET["sec"])){
				if($_GET["sec"] == "be"){
					$sec_be = 'class="active"';
					$where = "receptor = '".$cusers->id."' AND estado NOT LIKE '%2'";
					$select = "DISTINCT emisor as ide";
				}elseif($_GET["sec"] == "en"){
					$sec_en = 'class="active"';
					$where = "emisor = '".$cusers->id."' AND estado NOT LIKE '2%'";
					$select = "DISTINCT receptor as ide";
				}elseif($_GET["sec"] == "pa"){
					$sec_pa = 'class="active"';
					$where = "receptor = '".$cusers->id."' AND estado LIKE '%2'";
					$select = "DISTINCT emisor as ide";
				}else{
					$sec_be = 'class="active"';
					$where = "receptor = '".$cusers->id."'";
					$select = "DISTINCT emisor as ide";
				}
				$sec = "?sec=".$_GET["sec"];
			}else{
				$sec_be = 'class="active"';
				$where = "receptor = '".$cusers->id."'";
				$select = "DISTINCT emisor as ide";
				$sec = "?sec=be";
			}
			?>
			<li class="back"><a><span class="iconfa-chevron-left"></span> Atras</a></li>
			<li <?php echo $sec_be; ?>><a href="?sec=be"><span class="iconfa-inbox"></span> Bandeja de entrada</a></li>
			<li <?php echo $sec_en; ?>><a href="?sec=en"><span class="iconfa-plane"></span> Enviados</a></li>
			<li <?php echo $sec_pa; ?>><a href="?sec=pa"><span class="iconfa-trash"></span> Papelera</a></li>
		</ul>
	</div>
	<div class="messagecontent">
		<div class="messageleft">
			<ul class="msglist">
			<?php
			/*
			selected
			unread
			-
			*/
			/*SACAR SI HAY ALGUNO DE LOS CRITERIOS*/
			$cabs = $sql->runSelect("mensajes", $where, $select, "id DESC", false, false, false, false, false);
			if(count($cabs)>0){
				for($x=0; $x<count($cabs); $x++){
					$other = $sql->runSelect("usuarios", "id = '".$sql->fstr($cabs[$x]["ide"])."'", "avatar, email, rol, id_perfil", false, false, false, false, false, false);
					if(count($other) == 1){
						if($other[0]["rol"] == 1){
							$dataother = $sql->runSelect("consumidor", "id = '".$other[0]["id_perfil"]."'", "nombre, apellidos");
						}elseif($other[0]["rol"] == 2){
							$dataother = $sql->runSelect("empresario", "id = '".$other[0]["id_perfil"]."'", "nombre");
							$dataother[0]["apellidos"] = "";
						}else{
							$dataother[0]["nombre"] = "Administrador";
							$dataother[0]["apellidos"] = "";
						}
					}else{
						$dataother[0]["nombre"] = "Unk";
						$dataother[0]["apellidos"] = "";
						$other[0]["avatar"] = "thumb1.png";
					}
					/*Sacamos la ultima fecha y creamos token*/
					$last = $sql->runSelect("mensajes", "(emisor = '".$cusers->id."' AND receptor = '".$cabs[$x]["ide"]."') OR (receptor = '".$cusers->id."' AND emisor = '".$cabs[$x]["ide"]."')", "emisor, receptor, mensaje, fecha, id, estado", "fecha DESC");
					if($last[0]["receptor"] == $cusers->id && substr($last[0]["estado"], 1, 1) == "0"){
						$leido = "unread ";
					}else{
						$leido = "";
					}
					if(isset($_GET["msg"]) && $last[0]["id"] == $_GET["msg"]){
						$sel = "selected";
					}else{
						$sel = "";
					}
					?>
					<li class="<?php echo $leido.$sel; ?>">
						<a href="<?php echo $sec; ?>&msg=<?php echo $last[0]["id"]; ?>&token=<?php echo sha1("Sec_".$last[0]["id"]."_rec:".$cusers->id); ?>">
							<div class="thumb"><img src="images/photos/<?php echo $other[0]["avatar"]; ?>" alt="<?php echo $dataother[0]["nombre"]." ".$dataother[0]["apellidos"]; ?>" /></div>
							<div class="summary">
								<span class="date pull-right"><small><?php echo date("d/m/Y H:i", strtotime($last[0]["fecha"])); ?></small></span>
								<h4><?php echo $dataother[0]["nombre"]." ".$dataother[0]["apellidos"]; ?></h4>
								<p><?php echo substr($last[0]["mensaje"], 0, 50); ?>...</p>
							</div>
						</a>
					</li>
					<?php
				}
			}else{
				echo "<p style='padding: 20px;'>No se han encontrado mensajes.</p>";
			}
			?>
				
			</ul>
		</div><!--messageleft-->
		<div class="messageright">
			<?php
			if(isset($_GET["msg"]) && is_numeric($_GET["msg"]) && isset($_GET["token"]) && $_GET["token"] == sha1("Sec_".$_GET["msg"]."_rec:".$cusers->id)){
				/*Comprobar si el mensaje le pertenece y sacar conversacion*/
				$getmsg = $sql->runSelect("mensajes", "id = '".$sql->fstr($_GET["msg"])."' AND (emisor = ".$cusers->id." OR receptor = '".$cusers->id."')", "emisor, receptor");
				if(count($getmsg)==1){
					
					
					/*Sacamos la conversacion*/
					if($getmsg[0]["emisor"] == $cusers->id){
						$otro = $getmsg[0]["receptor"];
					}else{
						$otro = $getmsg[0]["emisor"];
					}
					/*sacamos data del otro*/
					$other = $sql->runSelect("usuarios", "id = '".$sql->fstr($otro)."'", "avatar, email, rol, id_perfil", false, false, false, false, false, false);
					if(count($other) == 1){
						if($other[0]["rol"] == 1){
							$dataother = $sql->runSelect("consumidor", "id = '".$other[0]["id_perfil"]."'", "nombre, apellidos");
						}elseif($other[0]["rol"] == 2){
							$dataother = $sql->runSelect("empresario", "id = '".$other[0]["id_perfil"]."'", "nombre");
							$dataother[0]["apellidos"] = "";
						}else{
							$dataother[0]["nombre"] = "Administrador";
							$dataother[0]["apellidos"] = "";
						}
					}else{
						$dataother[0]["nombre"] = "Unk";
						$dataother[0]["apellidos"] = "";
						$other[0]["avatar"] = "thumb1.png";
					}
					if(!isset($_GET["action"])){
						/*MARCAMOS COMO LEIDO (emisor)*/
						changeState($sql, $otro, $cusers->id, "1", 2);
						/*MARCAMOS COMO LEIDO (receptor)*/
						changeState($sql, $cusers->id, $otro, "1", 1);
					}
					
					$msgs = $sql->runSelect("mensajes", "(emisor = '".$cusers->id."' AND receptor = '".$otro."') OR (receptor = '".$cusers->id."' AND emisor = '".$otro."')", "emisor, mensaje, fecha", "fecha DESC");
					if(count($msgs)>0){
					?>
					<div class="messageview">
						<div class="btn-group pull-right">
							<button data-toggle="dropdown" class="btn dropdown-toggle">Acciones <span class="caret"></span></button>
							<ul class="dropdown-menu">	
								<!-- <li><a href="<?php echo "mensajes?sec=".$_GET["sec"]."&msg=".$_GET["msg"]."&token=".$_GET["token"]."&action=send"; ?>">Enviar conversación por e-mail</a></li> -->
								<li><a href="<?php echo "mensajes?sec=".$_GET["sec"]."&msg=".$_GET["msg"]."&token=".$_GET["token"]."&action=spam"; ?>">Reportar como Spam</a></li>
								<li><a href="<?php echo "mensajes?sec=pa&msg=".$_GET["msg"]."&token=".$_GET["token"]."&action=dele"; ?>">Borrar Mensajes</a></li>
								<li><a href="<?php echo "mensajes?sec=be&msg=".$_GET["msg"]."&token=".$_GET["token"]."&action=notr"; ?>">Marcar como no leído</a></li>
							</ul>
						</div>
						<?php
						for($x=0; $x<count($msgs); $x++){
							if($msgs[$x]["emisor"] == $otro){
								/*datos del otro*/
								$nombre = $dataother[0]["nombre"];
								$apellidos = $dataother[0]["apellidos"];
								$avatar = $other[0]["avatar"];
							}else{
								/*datos del propio user*/
								$nombre = "Yo";
								$apellidos = "";
								$avatar = $cusers->avatar;
							}
							if($x==0 || $msgs[$x]["emisor"] != $msgs[$x-1]["emisor"]){
								?>
								<div class="msgauthor">
									<div class="thumb"><img src="images/photos/<?php echo $avatar; ?>" alt="<?php echo $nombre; ?>" /></div>
									<div class="authorinfo">
										<span class="date pull-right"><?php echo date("d/m/Y H:i", strtotime($msgs[$x]["fecha"])); ?></span>
										<h5><strong><?php echo $nombre." ".$apellidos; ?></strong></h5>
									</div>
								</div>
								<?php
							}
							?>
							<div class="msgbody">
								<p><?php echo $msgs[$x]["mensaje"]; ?></p>
							</div>
							<?php
						}
						?>
					</div>
					<div class="msgreply">
						<div class="thumb"><img src="images/photos/<?php echo $cusers->avatar; ?>" alt="" /></div>
						<div class="reply">
							<form method="POST">
								<textarea style="height: 90px;" name="respuesta" placeholder="Escribe tu respuesta"></textarea>
								<input type="submit" value="Responder" class="btn btn-default btn-block" />
							</form>
						</div>
					</div>
					<?php
					}else{
						echo "<p style='padding: 20px;'>Error inesperado al cargar los mensajes</p>";
					}
				}else{
					echo "<p style='padding: 20px;'>Error al cargar los mensajes</p>";
				}
			}else{
				echo "<p style='padding: 20px;'>Selecciona un mensaje</p>";
			}
			?>
		</div>
	</div>
</div>
<?php
pie();


function changeState($sql, $rec, $emi, $val, $tip){
	$where = "(receptor = '".$rec."' AND emisor = '".$emi."')";
	
	if($val == "1"){
		//Solo marcar como leido si esta a 0
		if($tip == 1){
			$where .= " AND estado LIKE '%0'";
		}else{
			$where .= " AND estado LIKE '0%'";
		}
	}
	
	$getsms = $sql->runSelect("mensajes", $where, "id, estado");
	for($x=0; $x<count($getsms); $x++){
		if($tip == 1){
			$st["estado"] = substr($getsms[$x]["estado"], 0, 1).$val;
		}else{
			$st["estado"] = $val.substr($getsms[$x]["estado"], 1, 1);
		}
		$sql->runUpdate("mensajes", $st, "id = '".$getsms[$x]["id"]."'");
	}
}
?>