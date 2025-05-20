<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
<title>Calcula Valorismo</title>
    <link rel="stylesheet" href="valorismo.css">
</head>

<body>

    <p>Conociendo los siguientes datos de las empresas o sectores, podemos elaborar la factura resumen anual, y elaborar
        otra con los cambios propuestos para ver su repercusión en el empleo</p>
    <!--Formulario para recoger los datos-->
    <form id="calculationForm">
        <!--Ocultos demomento-->
        <input type="hidden" id="nombreEmpresa">
        <input type="hidden" id="CIF" >



        <label for="nTrabajadores">Número de trabajadores:</label>
        <input type="number" id="nTrabajadores" required value="8504" inputmode="numeric" pattern="[0-9]*"><br>

        <label for="totalVentas">Total ventas:</label>
        <input type="number" id="totalVentas" required value="1858617000" inputmode="numeric" pattern="[0-9]*"><br>

        <label for="costeAnualTrabajador">Coste anual por trabajador:</label>
        <input type="number" id="costeAnualTrabajador" required value="25256" inputmode="numeric" pattern="[0-9]*"><br>

        <label for="IVA">IVA:</label>
        <input type="number" id="IVA" required value="21" inputmode="numeric" pattern="[0-9]*"><br>
    </form>

    <!--Factura actual-->
    <div id="facturaActual"></div>

    <!--Factura nueva-->
    <div id="facturaNueva"></div>

    <script src="valorismo.js" defer></script>




<!--Mirar si puede ser mas eficiente teniendo el esqueleto de la pagina puesto oculto y que JS solo tenga que actualizar los valores.-->
    </body>
</html>
