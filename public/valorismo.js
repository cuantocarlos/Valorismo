window.addEventListener('DOMContentLoaded', () => {
    // Cachear los elementos del DOM
    const inputFields = {
        nTrabajadores: document.getElementById('nTrabajadores'),
        totalVentas: document.getElementById('totalVentas'),
        costeAnualTrabajador: document.getElementById('costeAnualTrabajador'),
        IVA: document.getElementById('IVA'),
        nombreEmpresa: document.getElementById('nombreEmpresa'),
        CIF: document.getElementById('CIF')
    };
    
    // Elementos de la factura actual
    const facturaActual = {
        nombreEmpresa: document.getElementById('nombreEmpresaActual'),
        cif: document.getElementById('cifActual'),
        ventas: document.getElementById('ventasActual'),
        baseImponible: document.getElementById('baseImponibleActual'),
        ivaRate: document.getElementById('ivaRateActual'),
        iva: document.getElementById('ivaActual'),
        totalVentas: document.getElementById('totalVentasActual')
    };
    
    // Elementos de la factura nueva
    const facturaNueva = {
        ventas: document.getElementById('ventasNueva'),
        valorEmpresarial: document.getElementById('valorEmpresarial'),
        valorSocial: document.getElementById('valorSocial'),
        baseImponible: document.getElementById('baseImponibleNueva'),
        ivaRate: document.getElementById('ivaRateNueva'),
        iva: document.getElementById('ivaNueva'),
        totalVentas: document.getElementById('totalVentasNueva')
    };
    
    function calcularYMostrarFacturas() {
        // Verificar que todos los campos requeridos tienen valor
        const campos = ['nTrabajadores', 'totalVentas', 'costeAnualTrabajador', 'IVA'];
        for (const campo of campos) {
            if (!inputFields[campo].value.trim()) {
                ocultarFacturas();
                return;
            }
        }
        
        // Obtener valores
        const nTrabajadores = parseFloat(inputFields.nTrabajadores.value);
        const totalVentas = parseFloat(inputFields.totalVentas.value);
        const costeAnualTrabajador = parseFloat(inputFields.costeAnualTrabajador.value);
        const IVA = parseFloat(inputFields.IVA.value);
        const nombreEmpresa = inputFields.nombreEmpresa.value;
        const CIF = inputFields.CIF.value;
        
        // Calcular valores una sola vez
        const uno = (totalVentas * 100) / (100 + IVA);
        const dos = costeAnualTrabajador * nTrabajadores;
        const tres = uno - dos;
        const cuatro = dos * 100 / tres;
        const d1 = (uno * IVA) / 100;
        
        // Actualizar factura actual
        if (nombreEmpresa) {
            facturaActual.nombreEmpresa.textContent = nombreEmpresa;
            facturaActual.nombreEmpresa.parentElement.style.display = 'block';
        } else {
            facturaActual.nombreEmpresa.parentElement.style.display = 'none';
        }
        
        if (CIF) {
            facturaActual.cif.textContent = `CIF: ${CIF}`;
            facturaActual.cif.style.display = 'block';
        } else {
            facturaActual.cif.style.display = 'none';
        }
        
        facturaActual.ventas.textContent = `${formatoMoneda(uno)} €`;
        facturaActual.baseImponible.textContent = `${formatoMoneda(uno)} €`;
        facturaActual.ivaRate.textContent = IVA;
        facturaActual.iva.textContent = `${formatoMoneda(d1)} €`;
        facturaActual.totalVentas.textContent = `${formatoMoneda(totalVentas)} €`;
        
        // Actualizar factura nueva
        facturaNueva.ventas.textContent = `${formatoMoneda(uno)} €`;
        facturaNueva.valorEmpresarial.textContent = `${formatoMoneda(tres)} €`;
        facturaNueva.valorSocial.textContent = `${formatoMoneda(dos)} €`;
        facturaNueva.baseImponible.textContent = `${formatoMoneda(uno)} €`;
        facturaNueva.ivaRate.textContent = IVA;
        facturaNueva.iva.textContent = `${formatoMoneda(d1)} €`;
        facturaNueva.totalVentas.textContent = `${formatoMoneda(totalVentas)} €`;
        
        // Actualizar factura nueva moodificada
        facturaNueva.ventas.textContent = `${formatoMoneda(uno)} €`;
        facturaNueva.valorEmpresarial.textContent = `${formatoMoneda(tres)} €`;
        facturaNueva.valorSocial.textContent = `${formatoMoneda(dos)} €`;
        facturaNueva.baseImponible.textContent = `${formatoMoneda(uno)} €`;
        facturaNueva.ivaRate.textContent = IVA;
        facturaNueva.iva.textContent = `${formatoMoneda(d1)} €`;
        facturaNueva.totalVentas.textContent = `${formatoMoneda(totalVentas)} €`;

        // Mostrar facturas
        document.getElementById('facturaActual').style.display = 'block';
        document.getElementById('facturaNueva').style.display = 'block';
    }
    
    function ocultarFacturas() {
        document.getElementById('facturaActual').style.display = 'none';
        document.getElementById('facturaNueva').style.display = 'none';
    }

    // Añadir listeners a los inputs - ahora sin debounce
    Object.values(inputFields).forEach(input => {
        input.addEventListener('input', calcularYMostrarFacturas);
    });

    // Render inicial
    calcularYMostrarFacturas();
});

function formatoMoneda(numero) {
    return numero.toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}