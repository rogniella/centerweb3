    const cantDecMonto = 0;  //General Cantidad de Decimales que se muestra por pantalla en los montos totales


    
    function send_demora( num ) { 

        // Envio de Msg Demora de OT 
        let msg= "Hola+me+estoy+contactando+desde+CenterOptica+para+informarle que su trabajo esta Demorado";
        //codigoPais = "+54"

   //     if (validarNumeroWhatsApp(codigoPais, numeroWhatsApp)) {
            // Aquí puedes enviar el número a tu servidor o realizar otras acciones
            var win = window.open(`https://api.whatsapp.com/send/?phone=${num}&text=${msg}&type=phone_number&app_absent=0`, '_blank');
   //     } else {
   //         alert('Número de WhatsApp no válido. Por favor, ingrese un número válido.');
   //     }

    }



    function dateSorter(a, b){
     //   return(new Date(a).getTime() - new Date(b).getTime());
       // Entrada un Srting dd/mm/yyyy

       // console.log(a,b)
       //  console.log( typeof(a), typeof(b))

        var dateMomentObject = moment(a, "DD/MM/YYYY"); // 1st argument - string, 2nd argument - format
        var aaa = dateMomentObject.toDate(); // convert moment.js object to Date object

        var dateMomentObjectb = moment(b, "DD/MM/YYYY"); // 1st argument - string, 2nd argument - format
        var bbb = dateMomentObjectb.toDate(); // convert moment.js object to Date object

      // da error  console.log( Date(dateMomentObject).getTime() , Date(dateMomentObjectb).getTime() ) // da error

        return Date.parse(aaa) - Date.parse(bbb);
   

    }

    function priceSorter(a, b) {
        // Utilizada por boostraptable columnas montos
        var aa = a.replaceAll('.', '')
        var bb = b.replaceAll('.', '')
         aa = aa.replace(',', '.')
         bb = bb.replace(',', '.') 
        return aa - bb
    }
 
    function codigoCristal ( material, color, esf, cil ) {

        // Segun los datos ingresados por pantalla, genera Cod del Cristal
        /* Formato: mcseeeccc    Lo utiliza la flia CRI en Productos
                    m  = Material
                    c  = Color / tipo de tratamiento
                    s  = Signo del Esferico +/-
                    eee= Valor Esferico
                    ccc= Valor Cilindrico
        */
        // console.log('Antes: ' ,esf , cil     ) 
        // Esf siempre con signo , y cil simpre en positivo sin signo
        esf = esf.replace(',','.')
        esf = esf.replace('+','')
        cil = cil.replace(',','.')
        cil = cil.replace('+','')
        esf = Number.parseFloat(esf).toFixed(2)        

        //console.log('Corregido: ' ,esf , cil     ) 

        if  ( cil < 0 ) {
            //lo convieto si en - lo cambio a +
            //  nesf = nesf + ncil               
            //mal  esf2 = Number.parseFloat(esf).toFixed(2) + Number.parseFloat(cil).toFixed(2)
            //  cil2 = Number.parseFloat(cil).toFixed(2) * -1
            //  console.log('pri', esf2 , cil2     ) 

            esf2 = Number.parseFloat(esf) + Number.parseFloat(cil)
            cil2 = Number.parseFloat(cil) * -1
            grado = esf2.toFixed(2) + cil2.toFixed(2) 

            esf = esf2.toFixed(2) 
            cil = cil2.toFixed(2) 
            //  if(esf >= 0 ) esf =  "+" + esf;
            grado2 = esf + cil   
            //console.log('Convertido:', esf , cil ) 
        }else if  ( cil > 0 ) {
            cil = Number.parseFloat(cil).toFixed(2)        
        }else{
            cil = ''
        }    

        if(esf >= 0 )  esf =  "+" + esf;
        esf = esf.replace('.','')
        cil = cil.replace('.','')
        
        cod = material + color + esf +  cil

        return cod

    }

    function descripcionMoneda(moneda)  {
        switch  (moneda ) {
            case 'P': 
               return 'Pesos'
               break;
            case 'R': 
               return 'Reales'
               break;
            case 'D': 
               return 'Dolares'
               break;
            case 'T':      
               return 'Tarjetas'
               break;
            case 'A': 
               return 'Credito Argentino'
               break;
            case 'Y': 
               return 'Cta.Corriente'
               break;
            default:   
               return moneda
               break;
        }   
    }

    function numberFormatBd(ent_numero){
        // A un numero formateado como pantalla lo deja para la Bd
         // Lo formatea para bd, saca separador de miles
        //  111.111,11  pasa  1111111.11
        if (typeof ent_numero === 'number') {
            numero = ent_numero.toString()
        }else{
            numero = ent_numero
        }
        numero = numero.replaceAll( '.' , '');
        numero = numero.replace(',', '.');
        return numero
    }

    function numberFormat(ent_numero){
        // Variable que contendra el resultado final
        var resultado = "";
        if (typeof ent_numero === 'number') {
            numero = ent_numero.toString()
        }else{
            numero = ent_numero
        }
        numero = numero || 0; // ran por si la variable no fue fue pasada

       if(numero=="")        {
            return resultado; 
        }
        //console.log ('nume:',numero,ent_numero)
        // Si el numero empieza por el valor "-" (numero negativo)
        if(numero[0]=="-")
        {
            // Cogemos el numero eliminando los posibles puntos que tenga, y sin
            // el signo negativo
            nuevoNumero=numero.replace(/\./g,'').substring(1);

        }else{
            // Cogemos el numero eliminando los posibles puntos que tenga
            nuevoNumero=numero.replace(/\./g,'');
        }
 
        // Si tiene decimales, se los quitamos al numero
        if(numero.indexOf(",")>=0)
            nuevoNumero=nuevoNumero.substring(0,nuevoNumero.indexOf(","));
 
        // Ponemos un punto cada 3 caracteres
        for (var j, i = nuevoNumero.length - 1, j = 0; i >= 0; i--, j++)
            resultado = nuevoNumero.charAt(i) + ((j > 0) && (j % 3 == 0)? ".": "") + resultado;
 
        // Si tiene decimales, se lo añadimos al numero una vez forateado con 
        // los separadores de miles
        if(numero.indexOf(",")>=0)
            resultado+=numero.substring(numero.indexOf(","));
 
        if(numero[0]=="-")
        {
            // Devolvemos el valor añadiendo al inicio el signo negativo
            return "-"+resultado;
        }else{
            return resultado;
        }
    }
 
    function formatearNumeroConSeparadorDeMiles(numero, decimales) {

        const separadorMiles = '.';     
        const separadorDecimales = ',';

        const numeroConDecimales = Number.parseFloat(numero).toFixed(decimales);
        const [parteEntera, parteDecimal] = numeroConDecimales.split('.');
      
        const parteEnteraFormateada = parteEntera.replace(/\B(?=(\d{3})+(?!\d))/g, separadorMiles);
      
        return parteEnteraFormateada + (parteDecimal ? separadorDecimales + parteDecimal : '');
      


    }

// ******** Configuraciones *********//

// Sobreescribimos la propiedad que indica cuales formatos de exportar usamos.
$.fn.bootstrapTable.defaults.exportTypes = ['pdf', 'excel'];

function fotmatoColSel(value,row,index) {
    // Lo usamos para hacer que una columna quede como hipervinculo y pueda pedir mas detalle
      //value: el valor del campo. 
      //row: los datos de la fila (un vector con toda la fila.
      //index: el indice de la fila.
      var Id = value;
      //console.log(row)
      return '<a>'+ Id  + '</a>'            ;
}    // Vbles Generales de Entreda
