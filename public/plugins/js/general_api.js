/****************************
/* Funciones comunes a toda la aplicación e
/* implementaciones directas de las mismas.
/****************************/

/**
 * Setea datos del encabezado que va a presentar la página de impresión
 * del componente boostrap-table-print y además muestra la fecha del día.
 * Útil para usar en cada página en la que se habilite opción de imprimir un table.
 * @param headingTitle      string     Título grande a mostrar.
 * @param getHeadingText    function   Callback en el que podemos disponer forma obtención del texto
 *                                      a utilizar en el momento de solicitar la impresión.
 * @example setTablePrintHeading("Resultados de empleados", function() {$("#nombreEmpleado").val});
 */
function setTablePrintHeading(headingTitle, getHeadingText) {

    // Sobreescribimos el método de bootstrap table encargado de formatear
    // el html para la impresión de la tabla y que se ejecuta cada vez que
    // se produce una nueva impresión.
    // La intención es darle el encabezado y los datos que queramos agregar.
    $.fn.bootstrapTable.defaults.printPageBuilder = function(table) {
        var now = new Date();
        var curDay = now.getDate()+"/"+(now.getMonth()+1)+"/"+now.getFullYear();

        // Ejecutamos la función para obtener el resultado.
        var headingText = getHeadingText();

        return '<html><head>' +
                '<style type="text/css" media="print">' +
                '  @page { size: auto;   margin: 25px 0 25px 0; }' +
                '</style>' +
                '<style type="text/css" media="all">' +
                'table{border-collapse: collapse; font-size: 12px; }\n' +
                'table, th, td {border: 1px solid grey}\n' +
                'th, td {text-align: center; vertical-align: middle;}\n' +
                'p {font-weight: bold; margin-left:20px }\n' +
                'table { width:94%; margin-left:3%; margin-right:3%}\n' +
                'div.bs-table-print { text-align:center;}\n' +
                '</style></head><title>Imprimir</title><body>' +

                '<div style="text-align: right;">Impreso el '+ curDay +'</div>'+
                '<h2>'+headingTitle+'</h2>'+
                    headingText +
                    '<br><br><div class="bs-table-print">' + table + '</div>'+
                '</body></html>';
    }
}

// ******** Configuraciones *********//

// Sobreescribimos la propiedad que indica cuales formatos de exportar usamos.
$.fn.bootstrapTable.defaults.exportTypes = ['pdf', 'excel'];
