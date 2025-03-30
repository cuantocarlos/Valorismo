<?php
cabecera($sql, $cusers, $gene, 0);
if(isset($_GET["noperm"])){
	$gene->showMessage("No tienes permisos para ver esta sección.");
}
$gene->showMessage("No se ha encontrado la página seleccionada.", "info");
pie();
?>