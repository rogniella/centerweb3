function consulta_comprobante(tipo,nro,suc)  {

    if (nro == null || nro == 0 ) {
        msgerror( "Error: Este Comprobante no se puede consultar, esta en cero");
        return
    } // Fin si hay error

            // Busco los datos de la OT o Comprobante
            if (tipo == 'FC' || tipo == 'VT' || tipo == 'PR'  ) {
                if ( tipo == 'PR'  ) {
                    $('#titulo_consulta').html('Detalle del Presupuesto');
                }else{
                    $('#titulo_consulta').html('Detalle de Venta');
                }    
                datos = { sucursal: suc, tipo: tipo, id: nro}
                ruta = '../ventas/show'  
            }else{
                $('#titulo_consulta').html('Orden de Trabajo');
                if (tipo == 'ot_idweb' ) {
                    datos = { idWEB:nro}
                }else{
                    datos = { idWEB:0 , ot: nro}
                }
                ruta = '../ot/show'  
            }
            $.ajax({
                dataType: "html",
                data: datos ,
                url:   ruta,
                type:  'get',
                success: function(data){
                  $('#destino').html(data);
                  $("#consultaModal").modal("show")
                },
                error:  function(xhr,err){ 
                   msgerror( xhr.responseText);
                } // Fin si hay error
            }); // Fin llamado Ajax
    
}

// Nunca quedo bien, es para agregar un boton de imprimir
function GeneraPDF() {
    var doc = new jsPDF();
    var elementHTML = $('#destino').html();
    var specialElementHandlers = {
        '#elementH': function (element, renderer) {
            return true;
        }
    };
    doc.fromHTML(elementHTML, 15, 15, {
        'width': 170,
        'elementHandlers': specialElementHandlers
    });

    // Save the PDF
    doc.save('sample-document.pdf');

 }
