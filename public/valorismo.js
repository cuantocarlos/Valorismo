window.addEventListener('DOMContentLoaded', () => {
    // IDs requeridos
    const requiredInputIds = [
        'nTrabajadores',
        'totalVentas',
        'costeAnualTrabajador',
        'IVA',
    ];

    // Obtener referencias a los campos
    const inputs = [
        document.getElementById('nTrabajadores'), //A
        document.getElementById('totalVentas'), //B
        document.getElementById('costeAnualTrabajador'), //C
        document.getElementById('IVA'), //D
        document.getElementById('nombreEmpresa'),
        document.getElementById('CIF'),
    ];

    function calcularYMostrarFacturas() {
        const requiredInputs = inputs.filter(input => requiredInputIds.includes(input.id));
        if (requiredInputs.some(input => input.value.trim() === '')) {
            document.getElementById('facturaActual').innerHTML = '';
            document.getElementById('facturaNueva').innerHTML = '';
            return;
        }

        const nTrabajadores = parseFloat(inputs[0].value);
        const totalVentas = parseFloat(inputs[1].value);
        const costeAnualTrabajador = parseFloat(inputs[2].value);
        const IVA = parseFloat(inputs[3].value);
        const nombreEmpresa = inputs[4].value;
        const CIF = inputs[5].value;

              //Logica de cálculo
        const baseImponible = (totalVentas * 100) / (100 + IVA);//1
        const importeIVA = (baseImponible * IVA ) / 100;//2
        const valorEmpresarial = baseImponible - importeIVA;//3
        const valorSocial = (importeIVA*100)/3 //4


        const facturaActualHTML = `
            <p>Conociendo los siguientes datos de las empresas o sectores, podemos elaborar la factura resumen anual, y elaborar otra con los cambios propuestos para ver su repercusión en el empleo</p>
            <table border="1">
                <tr>
                    <td colspan="2">
                        <h3>Factura ACTUAL</h3>
                        ${nombreEmpresa ? `<strong>${nombreEmpresa}</strong><br>` : ''}
                        ${CIF ? `<strong>CIF: ${CIF}</strong>` : ''}
                    </td>
                </tr>
                <tr><td>Concepto</td><td>Importe</td></tr>
                <tr><td>Ventas</td><td>${formatoMoneda(totalVentas)} €</td></tr>
                
                <tr style="height: 40px;"><td colspan="2"></td></tr>
                <tr><td>Base Imponible</td><td>${formatoMoneda(baseImponible)} €</td></tr>
                <tr><td>IVA (${IVA}%)</td><td>${formatoMoneda(importeIVA)} €</td></tr>
                <tr><td>Total de las ventas</td><td>${formatoMoneda(totalVentas)} €</td></tr>
            </table>
        `;

        const facturaNuevaHTML = `
            <p>Factura NUEVA Con los mismos datos que la actual, pero aplicando las reglas del Valorismo</p>
            <table border="1">
                <tr><td colspan="2"><h3>Factura NUEVA</h3></td></tr>
                <tr><td>Concepto</td><td>Importe</td></tr>
                <tr><td>Ventas</td><td>${formatoMoneda(totalVentas)} €</td></tr>
                <tr><td>Valor Empresarial</td><td>${formatoMoneda(valorEmpresarial)}</td></tr>
                <tr><td>Valor Social</td><td>${formatoMoneda(valorSocial)}</td></tr>
                
                <tr style="height: 40px;"><td colspan="2"></td></tr>
                <tr><td>Base Imponible</td><td>${formatoMoneda(baseImponible)} €</td></tr>
                <tr><td>IVA (${IVA}%)</td><td>${formatoMoneda(importeIVA)} €</td></tr>
                <tr><td>Total de las ventas</td><td>${formatoMoneda(totalVentas)} €</td></tr>
            </table>
        `;

        document.getElementById('facturaActual').innerHTML = facturaActualHTML;
        document.getElementById('facturaNueva').innerHTML = facturaNuevaHTML;
    }

    // Listeners
    inputs.forEach(input => {
        input.addEventListener('input', calcularYMostrarFacturas);
    });

    // Render inicial
    calcularYMostrarFacturas();
});


// Añade esta función en tu código
function formatoMoneda(numero) {
    return numero.toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}