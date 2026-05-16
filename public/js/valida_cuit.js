function validaCuit(sCUIT) 
{     
    var aMult = '5432765432'; 
    var aMult = aMult.split(''); 

    cuit_rearmado=""; 
    for (i=0; i < sCUIT.length; i++) {    
        caracter=sCUIT.charAt( i); 
        if ( caracter.charCodeAt(0) >= 48 && caracter.charCodeAt(0) <= 57 )     { 
            cuit_rearmado +=caracter; 
        } 
    } 
    sCUIT=cuit_rearmado; 
    
//    msgerror( 'eeeeexhr.responseText');
   if (sCUIT && sCUIT.length == 11) 
    { 
        aCUIT = sCUIT.split(''); 
        var iResult = 0; 
        for(i = 0; i <= 9; i++) 
        { 
            iResult += aCUIT[i] * aMult[i]; 
        } 
        iResult = (iResult % 11); 
        iResult = 11 - iResult; 
         
        if (iResult == 11) iResult = 0; 
        if (iResult == 10) iResult = 9; 
         
        if (iResult == aCUIT[10]) 
        { 
            return ""; 
        }
    } else {
        return "Tiene que contener 11 dígitos ";         
    }     

    return "El dígito tiene que ser " + iResult; 
    
} 
 