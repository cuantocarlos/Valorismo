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

    <!-- Modificar el HTML para incluir la estructura fija -->
<div id="facturaActual">
    <p>Conociendo los siguientes datos de las empresas o sectores, podemos elaborar la factura resumen anual, y elaborar otra con los cambios propuestos para ver su repercusión en el empleo</p>
    <table border="1">
        <tr>
            <td colspan="2">
                <h3>Factura ACTUAL</h3>
                <strong id="nombreEmpresaActual"></strong><br>
                <strong id="cifActual"></strong>
            </td>
        </tr>
        <tr><td>Concepto</td><td>Importe</td></tr>
        <tr><td>Ventas</td><td id="ventasActual"></td></tr>
        <tr style="height: 40px;"><td colspan="2"></td></tr>
        <tr><td>Base Imponible</td><td id="baseImponibleActual"></td></tr>
        <tr><td>IVA (<span id="ivaRateActual"></span>%)</td><td id="ivaActual"></td></tr>
        <tr><td>Total de las ventas</td><td id="totalVentasActual"></td></tr>
    </table>
</div>

<div id="facturaNueva">
    <p>Factura NUEVA Con los mismos datos que la actual, pero aplicando las reglas del Valorismo</p>
    <table border="1">
        <tr><td colspan="2"><h3>Factura NUEVA</h3></td></tr>
        <tr><td>Concepto</td><td>Importe</td></tr>
        <tr><td>Ventas</td><td id="ventasNueva"></td></tr>
        <tr><td>Valor Empresarial</td><td id="valorEmpresarial"></td></tr>
        <tr style="height: 40px;"><td colspan="2"></td></tr>
        <tr><td>Valor Social</td><td id="valorSocial"></td></tr>
        <tr><td>Base Imponible</td><td id="baseImponibleNueva"></td></tr>
        <tr><td>IVA (<span id="ivaRateNueva"></span>%)</td><td id="ivaNueva"></td></tr>
        <tr><td>Total de las ventas</td><td id="totalVentasNueva"></td></tr>
    </table>
</div>
<div id="facturaNuevaModificada">
    <h2>Factura NUEVA MODIFICADA</h2>
    <p>Aplicando los Ajustes necesarios en el IVA y en el valor social obtenemos una factura que no aumente el total o lo haga de forma mínima para no encarecer el producto y no perjudicar al consumidor aumentamos el valor social… Del 26,26 % al 19,26 % disminuimos el IVA tres puntos del 21 al 18%</p>
    <table border="1">
        <tr><td colspan="2"><h3>Factura NUEVA MODIFICADA</h3></td></tr>
        <tr><td>Concepto</td><td>Importe</td></tr>
        <tr><td>Ventas</td><td id="ventasNuevaModificada"></td></tr>
        <tr><td>Valor Empresarial</td><td id="valorEmpresarialModificado"></td></tr>
        <tr style="height: 40px;"><td colspan="2"></td></tr>
        <tr><td>Valor Social</td><td id="valorSocialModificado"></td></tr>
        <tr><td>Base Imponible</td><td id="baseImponibleNuevaModificada"></td></tr>
        <tr><td>IVA (<span id="ivaRateNuevaModificada"></span>%)</td><td id="ivaNuevaModificada"></td></tr>
        <tr><td>Total de las ventas</td><td id="totalVentasNuevaModificada"></td></tr>
</div>

    <script src="valorismo.js" defer></script>




<!--Mirar si puede ser mas eficiente teniendo el esqueleto de la pagina puesto oculto y que JS solo tenga que actualizar los valores.-->
    </body>
</html>
