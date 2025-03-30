<?php
class CSearch{
	var $dbc;
	var $where;
	var $cual;
	var $data = Array();
	function CSearch($_sql, $cual = "") {
		$this->dbc = $_sql;
		$this->cual = $cual;
		$this->data = $this->checkForSearch();
	}
	
	function checkForSearch(){
		$data = Array();
		if(isset($_POST["dni"]) && $this->cual == ""){
			if(strlen($_POST["dni"])>3){
				$this->where = "c.dni LIKE '%".$this->dbc->fstr($_POST["dni"])."%'";
			}else{
				$data["dni"] = "El DNI no es válido";
			}
		}elseif(isset($_POST["cif"]) && $this->cual != ""){
			if(strlen($_POST["cif"])>3){
				$this->where = "c.cif LIKE '%".$this->dbc->fstr($_POST["cif"])."%'";
			}else{
				$data["cif"] = "El CIF no es válido";
			}
		}else{
			if($this->cual == "") $role = "1"; else $role = "2";
			$this->where = "u.is_demo = 0 AND u.rol = ".$role;
			$this->where .= $this->checkFilter($this->where, "cp");
			$this->where .= $this->checkFilter($this->where, "localidad");
			$this->where .= $this->checkFilterArr($this->where, "provincia", "p");
			$this->where .= $this->checkFilterArr($this->where, "autonomia", "p");
			$this->where .= $this->checkFilterArr($this->where, "cnae", "cnae");
			if($this->cual == ""){
				$this->where .= $this->checkFilter2($this->where, "puesto");
				$this->where .= $this->checkFilter($this->where, "preparacion", "c", true);
			}
		}
		return $data;
	}
	
	function showField($label, $name, $descripcion = "", $width = "col-lg-3"){
		if(isset($_POST[$name])){
			$value = $_POST[$name];
		}else{
			$value = "";
		}
		if(isset($this->data[$name])){
			$msg = $this->data[$name];
		}else{
			$msg = "";
		}
		?>
			<div class="<?php echo $width; ?>">
				<label style="font-size: 12px;" for="<?php echo $name; ?>"><?php echo $label; ?> <span style="color: darkgrey; font-style: italic;"><?php echo $descripcion; ?></span></label>
				<input type="text" class="form-control" id="<?php echo $name; ?>" name="<?php echo $name; ?>" placeholder="<?php echo $label; ?>" value="<?php echo $value; ?>" />
				<span style="color: darkred;"><?php echo $msg; ?></span>
			</div>
		<?php
	}
	
	function showTags($label, $name, $descripcion = "", $width = "col-lg-3"){
		if(isset($_POST[$name])){
			$value = $_POST[$name];
		}else{
			$value = "";
		}
		if(isset($this->data[$name])){
			$msg = $this->data[$name];
		}else{
			$msg = "";
		}
		?>
		<script>
			jQuery(document).ready(function(){
				jQuery('#<?php echo $name; ?>').tagsInput();
			});
		</script>
		<div class="<?php echo $width; ?>">
			<label style="font-size: 12px;" for="<?php echo $name; ?>"><?php echo $label; ?> <span style="color: darkgrey; font-style: italic;"><?php echo $descripcion; ?></span></label>
			<span class="field">
				<input id="<?php echo $name; ?>" class="input-large" name="<?php echo $name; ?>" placeholder="<?php echo $label; ?>" value="<?php echo $value; ?>" />
            </span>
			<span style="color: darkred;"><?php echo $msg; ?></span>
		</div>
		<?php
	}
	function showTagsSearch($label, $name, $descripcion = "", $width = "col-lg-3", $datos = Array(), $width2 = "width:350px;"){
		if(isset($this->data[$name])){
			$msg = $this->data[$name];
		}else{
			$msg = "";
		}
		?>
	<script>
		jQuery(document).ready(function(){
			jQuery('#<?php echo $name; ?>').chosen();
		});
	</script>
		 
		<div class="<?php echo $width; ?>">
			<label style="font-size: 12px;" for="<?php echo $name; ?>"><?php echo $label; ?> <span style="color: darkgrey; font-style: italic;"><?php echo $descripcion; ?></span></label>
			<span class="formwrapper"> <!-- field -->
				<select  id="<?php echo $name; ?>" name="<?php echo $name; ?>[]" data-placeholder="<?php echo $label; ?>" class="chzn-select" multiple  style="<?php echo $width2; ?>" tabindex="4">
					<option value=""></option>
					<?php
					for($x=0; $x<count($datos); $x++){
						$sel = "";
						foreach($_POST[$name] as $key => $value){
							if($datos[$x]["id"] == $value){ 
								$sel = 'selected = "selected"';
							}
						}
						?><option value="<?php echo $datos[$x]["id"]; ?>" <?php echo $sel; ?>><?php echo $datos[$x]["valor"]; ?></option><?php
					}
					?>
				</select>
			</span>
			<span style="color: darkred;"><?php echo $msg; ?></span>
		</div>
		<?php
	}
	
	function checkFilter($where, $name, $table = "c", $commas = false){
		if(isset($_POST[$name]) && strlen($_POST[$name])>0){
			if(strlen($where)>0) $and = " AND "; else $and = "";
			/*Check varios*/
			$arr = explode(",", $_POST[$name]);
			if(count($arr)==1 || $commas){
				return $and.$table.".".$name." LIKE '%".$this->dbc->fstr($_POST[$name])."%'";
			}else{
				$var = "";
				for($x=0; $x<count($arr); $x++){
					if($x==0) $ap = "("; else $ap = "";
					if(($x+1)==count($arr)){ $cp = ")"; $or = ""; }else{ $cp = ""; $or = " OR "; }
					$var .= $and.$ap.$table.".".$name." LIKE '%".$this->dbc->fstr(trim($arr[$x], " "))."%'".$or.$cp;
				}
				return $var;
			}
		}
		return "";
	}
	function checkFilterArr($where, $name, $table = "c", $commas = false){
		if(isset($_POST[$name]) && count($_POST[$name])>0){
			$var = ""; $x = 0;
			foreach($_POST[$name] as $key => $value){
				if($x==0) $ap = "("; else $ap = "";
				if(($x+1)==count($_POST[$name])){ $cp = ")"; $or = ""; }else{ $cp = ""; $or = " OR "; }
				$var .= $and.$ap.$table.".".$name." LIKE '%".$this->dbc->fstr(trim($value, " "))."%'".$or.$cp;
				$x++;
			}
			return $var;
		}
		return "";
	}

	function checkFilter2($where, $name, $table = "c"){
		if(isset($_POST[$name]) && strlen($_POST[$name])>0){
			if(strlen($where)>0) $and = " AND "; else $and = "";
			/*Check varios*/
			$arr = explode(",", $_POST[$name]);
			if(count($arr)==1){
				return $and.$table.".id IN (SELECT id_consumidor FROM consumidor_historico WHERE tipo_historico = '".$name."' AND historico LIKE '%".$this->dbc->fstr($_POST[$name])."%')";
			}else{
				$var = "";
				for($x=0; $x<count($arr); $x++){
					if($x==0) $ap = "("; else $ap = "";
					if(($x+1)==count($arr)){ $cp = ")"; $or = ""; }else{ $cp = ""; $or = " OR "; }
					$var .= $and.$ap.$table.".id IN (SELECT id_consumidor FROM consumidor_historico WHERE tipo_historico = '".$name."' AND historico LIKE '%".$this->dbc->fstr(trim($arr[$x], " "))."%')".$or.$cp;
				}
				return $var;
			}
		}
		return "";
	}
	function showTable($gene){
		if(isset($this->where) && $this->where != ""){
			$rows = $this->dbc->runSelect("consumidor c INNER JOIN provincias p ON c.provincia = p.id LEFT JOIN cnae ON cnae.id = c.cnae INNER JOIN usuarios u ON u.id_perfil = c.id", 
										  $this->where, 
										  "DISTINCT c.*,  p.provincia as pr, p.autonomia as aut, cnae.cnae as cnaedesc", false, "100", false, false, false, false, 0);
		}else{
			$rows = Array();
		}
		
		if(count($rows)>0){
			?>
			<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="js/responsive-tables.js"></script>
			<div class="masthead">
				<h3 class="text-muted">Resultado de la Búsqueda</h3>
			</div>
			<script>
			jQuery(document).ready(function(){
				jQuery('#dyntable').dataTable({
					"oLanguage": {
					"sProcessing":     "Procesando...",
					"sLengthMenu":     "Mostrar _MENU_ registros",
					"sZeroRecords":    "No se encontraron resultados",
					"sEmptyTable":     "Ningún dato disponible en esta tabla",
					"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
					"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
					"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix":    "",
					"sSearch":         "Buscar:",
					"sUrl":            "",
					"sInfoThousands":  ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst":    "Primero",
						"sLast":     "Último",
						"sNext":     "Siguiente",
						"sPrevious": "Anterior"
					},
					"oAria": {
						"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				},
				"bStateSave": true,
				"sPaginationType": "full_numbers"
				});
				
			});
			</script>
			<style>
				#dyntable_wrapper {
					overflow-x: auto;
				}
			</style>
			<table id="dyntable" class="table table-bordered responsive">
				<thead>
					<tr>
						<th>Nombre</th>
						<th>DNI</th>
						<!--
						<th>Ámbito</th>
						-->
						<th>Preparación</th>
						<th>Último Cargo</th>
						<th>CNAE</th>
						<th>Disponibilidad</th>
						<th>Curriculum</th>
					</tr>
				</thead>
				<tbody>
					<?php
					for($x=0; $x<count($rows); $x++){
						?>
						<tr>
							<td><?php echo $rows[$x]["nombre"]; ?></td>
							<td><?php echo $rows[$x]["dni"]; ?></td>
							<!--
							<td><?php echo $rows[$x]["pr"]; ?></td>
							-->
							<td><?php echo $rows[$x]["preparacion"]; ?></td>
							<?php
							$get = $this->dbc->runSelect("consumidor_historico", "tipo_historico = 'puesto' AND id_consumidor = '".$rows[$x]["id"]."'", "historico", "fecha DESC");
							if(isset($get[0]["historico"])) $cargo = $get[0]["historico"]; else $cargo = "-";
							?>
							<td><?php echo $cargo; ?></td>
							<td><?php echo $rows[$x]["cnaedesc"]; ?></td>
							<?php
							if($rows[$x]["estado"] == "0"){
								$disp = "Con empleo";
							}else{
								$disp = "Disponible";
							}
							if($rows[$x]["ofertas"] == "0"){
								$contactar = "";
							}else{
								if(isset($_GET["trabajadores"])){
									$contactar = "";
								}else{
									$contactar = '&nbsp;&nbsp;<a href="#modal_box_co" data-toggle="modal" class="modales_se btn btn-block btn-success" style="display: inline;" id="'.$rows[$x]["id"].'" titulo="Contactar con: '.$rows[$x]["nombre"].'" token2="'.md5($rows[$x]["id"]."_Jhsy26_").'">Contactar</a>';
								}
							}
							?>
							<td><?php echo $disp.$contactar; ?></td>
							<td style="text-align: center;">
								<?php
								$curris = $this->dbc->runSelect("consumidor_archivos", "id_consumidor = '".$rows[$x]["id"]."' AND tipo_documento = 'curriculum'", "*", "fecha DESC");
								if(count($curris)>0){
									if(count($_GET) == 1){
									$y = 0;
									?>
									<a href="#modal_box" data-toggle="modal" class="modales_cu btn btn-block btn-warning" style="display: inline;" id="<?php echo $curris[$y]["id"]; ?>" titulo="<?php echo "Archivo: ".$curris[$y]["titulo"]." ".date("d/m/Y", strtotime($curris[$y]["fecha"])); ?>" token="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/archivos/<?php echo $curris[$y]["enlace"]; ?>">Ver</a>
									<?php
									}else{
										echo "<a href='inicio'>Regístrate</a>";
									}
								}else{
									echo "No dispone";
								}
								?>
								<?php echo $rows[$x]["curriculum"]; ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
			<?php
		}else{
			if(count($_POST)>0){
				?>
				<div class="masthead">
					<h3 class="text-muted">Resultado de la Búsqueda</h3>
				</div>
				<?php
				$gene->showMessage("No se han encontado registros", "info");
			}
		}
	}
	function showTableEmp($gene){
		if(isset($this->where) && $this->where == "" && count($_POST)>4) $this->where = "1 = 1";
		if(isset($this->where) && $this->where != ""){
			$limit = "200";
			$rows = $this->dbc->runSelect("empresario c INNER JOIN provincias p ON c.provincia = p.id 
														LEFT JOIN empresario_puestos ep ON ep.id_empresario = c.id 
														LEFT JOIN cnae ON cnae.id = c.cnae 
														LEFT JOIN cnae_provincia cp ON cp.cnae = cnae.id AND cp.provincia = c.provincia 
														INNER JOIN usuarios u ON u.id_perfil = c.id", 
			                              $this->where, 
										  "c.*, p.provincia as pr, p.autonomia as aut, cnae.cnae as cnaedesc, IF(cp.porcentaje_admin IS NULL, cp.porcentaje_auto, cp.porcentaje_admin) AS porcentaje,
										  IF(SUM(ep.personas_admitir) IS NULL, '0', SUM(ep.personas_admitir)) AS pa, IF(SUM(ep.personas_salida) IS NULL, '0', SUM(ep.personas_salida)) AS ps,
										  (IF(SUM(ep.personas_admitir) IS NULL, '0', SUM(ep.personas_admitir))-IF(SUM(ep.personas_salida) IS NULL, '0', SUM(ep.personas_salida))) AS total",
										  "total DESC", $limit, false, "c.id", false, false, 0);
		}else{
			$rows = Array();
		}
		
		if(count($rows)>0 && (!isset($_POST["disponibles"]) || $rows[0]["total"]>0)){
			?>
			<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="js/responsive-tables.js"></script>
			<div class="masthead">
				<h3 class="text-muted">Resultado de la Búsqueda</h3>
			</div>
			<script>
			jQuery(document).ready(function(){
				jQuery('#dyntable').dataTable({
					"oLanguage": {
					"sProcessing":     "Procesando...",
					"sLengthMenu":     "Mostrar _MENU_ registros",
					"sZeroRecords":    "No se encontraron resultados",
					"sEmptyTable":     "Ningún dato disponible en esta tabla",
					"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
					"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
					"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix":    "",
					"sSearch":         "Buscar:",
					"sUrl":            "",
					"sInfoThousands":  ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
						"sFirst":    "Primero",
						"sLast":     "Último",
						"sNext":     "Siguiente",
						"sPrevious": "Anterior"
					},
					"oAria": {
						"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
						"sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				},
				"bStateSave": true,
				"sPaginationType": "full_numbers"
				});
				
			});
			</script>
			<style>
				#dyntable_wrapper {
					overflow-x: auto;
				}
			</style>
			<table id="dyntable" class="table table-bordered responsive">
				<thead>
					<tr>
						<th>Empresa</th>
						<th>Domicilio Social</th>
						<th>CIF</th>
						<th>CNAE</th>
						<th>Ventas Totales</th>
						<th>% Sector</th>
						<th>% V.S.</th>
						<th>Valor Social</th>
						<th>Base Imponible</th>
						<th>% IVA</th>
						<th>IVA</th>
						<th>Puestos disponibles</th>
					</tr>
				</thead>
				<tbody>
					<?php
					for($x=0; $x<count($rows); $x++){
						/*Sacamos el ultimo registro de esa empresa... si lo tiene*/
						if($rows[$x]["total"]>0 || !isset($_POST["disponibles"])){
							if($rows[$x]["total"]>0){
								$enlace = 'href="#modal_boxe" data-toggle="modal" class="modales" style="cursor: pointer;"';
							}else{
								$enlace = "";
							}
							?>
							<tr <?php echo $enlace; ?> titulo="<?php echo $rows[$x]["nombre"]; ?>" ide="<?php echo $rows[$x]["id"]; ?>">
								<td><?php echo $rows[$x]["nombre"]; ?></td>
								<td><?php echo $rows[$x]["domicio_social"]; ?></td>
								<td><?php echo $rows[$x]["cif"]; ?></td>
								<td><?php echo $rows[$x]["cnaedesc"]; ?></td>
								<?php
								$get = $this->dbc->runSelect("empresario_historico", "id_empresario = '".$rows[$x]["id"]."'", "*", "fecha DESC");
								if(count($get)>0){
									$vt = $get[0]["ventas_totales"];
									$ps = $get[0]["porc_sector"];
									$pvs = $get[0]["valor_social_p"];
									$vs = $get[0]["valor_social"];
									$bi = $get[0]["base_imponible"];
									$piva = $get[0]["iva_p"];
									$iva = $get[0]["iva"];
								}else{
									$vt = "-";
									$ps = $rows[$x]["porcentaje"];
									$pvs = "-";
									$vs = "-";
									$bi = "-";
									$piva = "-";
									$iva = "-";
								}
								?>
								<td><?php echo $this->seeNum($vt); ?> €</td>
								<td><?php echo $this->seeNum($ps); ?> %</td>
								<td><?php echo $this->seeNum($pvs); ?> %</td>
								<td><?php echo $this->seeNum($vs); ?> €</td>
								<td><?php echo $this->seeNum($bi); ?> €</td>
								<td><?php echo $this->seeNum($piva); ?> %</td>
								<td><?php echo $this->seeNum($iva); ?> €</td>
								<td style="text-align: center;"><?php echo ($rows[$x]["total"]); ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			<?php
		}else{
			if(count($_POST)>0){
				?>
				<div class="masthead">
					<h3 class="text-muted">Resultado de la Búsqueda</h3>
				</div>
				<?php
				$gene->showMessage("No se han encontado registros con los criterios seleccionados", "info");
			}
		}
	}
	function showModal(){
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
		<div id="modal_boxe" class="modal hide fade" style="min-width: 45%;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="titulo_mod">TITULO</h3>
			</div>
			<div id="cargando" style="width: 50px; margin: auto; display: none;"><img src="images/loading.gif" alt="cargando..." title="cargando..." /></div>
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
			<!-- data-dismiss="modal" aria-hidden="true" -->
				<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
			</div>
		</div>
		<?php
	}
	function showModalContact(){
		?>
		<script language="javascript">
			jQuery(document).ready(function(){
				jQuery(".modales_se").click(function(){
					jQuery("#cargando").css("display", "inline");
					var id = jQuery(this).attr("id");	
					var token2 = jQuery(this).attr("token2");			
					
					jQuery("#titulo_mod").text(jQuery(this).attr("titulo"));
					jQuery("#trabajador").attr("value", id);
					jQuery("#token2").attr("value", token2);
					
					jQuery("#cargando").css("display", "none");
				});
			});
		</script>
		<div id="modal_box_co" class="modal hide fade" style="min-width: 45%;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="titulo_mod">TITULO</h3>
			</div>
			<div id="cargando" style="width: 50px; margin: auto; display: none;"><img src="images/loading.gif" alt="cargando..." title="cargando..." /></div>
			<div class="modal-body">
				<form action="busqueda-trabajadores" method="POST">
					<?php
					/*Reenviamos ademas todas las posts anteriores*/
					foreach($_POST as $key => $value){
						if($key != "token2" && $key != "mensaje" && $key != "trabajador"){
							?><input type="hidden" value="<?php echo $value; ?>" name="<?php echo $key; ?>" /><?php
						}
					}
					?>
					<input type="hidden" value="0" name="trabajador" id="trabajador" />
					<input type="hidden" value="0" name="token2" id="token2" />
					<textarea name="mensaje" style="width: 100%; min-height: 50px;"></textarea>
					<input type="submit" value="Enviar mensaje" class="btn btn-block btn-success">
				</form>
			</div>
			<div class="modal-footer">
			<!-- data-dismiss="modal" aria-hidden="true" -->
				<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
			</div>
		</div>
		<?php
	}
	
	function showModal2(){
		?>
		<script type="text/javascript" src="js/jquery.gdocsviewer.min.js"></script> 
		<script>
		jQuery(document).ready(function(){
			jQuery(".modales_cu").click(function(){
				var id = jQuery(this).attr("id");
				var token = jQuery(this).attr("token");
				jQuery('#embed').attr("href", token);
				jQuery('#embed').gdocsViewer({ width: "100%", height: 350 });
				jQuery("#titulo_mod").text(jQuery(this).attr("titulo"));
			});
		});
		</script>
		<!-- MODAL -->
		<div id="modal_box" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="titulo_mod">TITULO</h3>
			</div>
			<div class="modal-body">
				<a id="embed" href=""></a>
			</div>
			<div class="modal-footer">
				<a href="#" data-dismiss="modal" aria-hidden="true" id="backbutton" class="btn btn-icon glyphicons unshare"><i></i>Cerrar</a>
			</div>
		</div>
		<?php
	}
	
	function seeNum($num){
		return number_format($num, 2, ",", ".");
	}
}
?>