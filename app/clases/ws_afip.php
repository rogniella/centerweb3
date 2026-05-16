<?php   namespace app\clases;

//  Para que funcione ver documentacion del componente composer require afipsdk/afip.php
use Afip; // Para Generar comprobante afip

class ws_afip  {
  		 
    // Propiedades
    // Salida: 
        public $ret = ""; // Retorno de Ejecución  "" = Ok  sino Retoran Mensaje de Error

    // Datos de Entrada del comprobante:
        public $tipo_de_factura = "";  // Tipo Interno Cod Numerico segun AFIP, ....
        public $punto_de_venta = ""; 
        public $cliente_cod_documento =""; //Nro o Cuit del Cliente
        public $cliente_documento =0; //Nro o Cuit del Cliente
        public  $fecha_factura = ""; 
        public $importe_gravado=0; //Importe sujeto al IVA (sin icluir IVA)
        public $importe_iva =0; // Importe de IVA
        public $importe_exento_iva =0; // Importe de IVA
      
        public $punto_facturaOriginal = "";  //Se completa solo en Anulaciones
        public $facturaOriginal = "";        //Se completa solo en Anulaciones

        // Retorna
        public $CAE = "";  // string de 14
        public $CAEFchVto  = "";  //  "2019-10-16"
        public $numero_de_factura = ""; // Numero Generado
 

  
  public function nuevo_comprobante(){

    // Se conecta con Sistema de AFIP
    $this->auxErrorAfip = ""; //Ok
    try {
      $cuitEmpresa = (float) env('CUIT');
      $tope_sin_ident = (float) env('AFIP_TOPE_SINIDENTIF_PES');
      // Pendiente discriminar forma de pago para la validacion
      // $tope_sin_ident = (float) env('AFIP_TOPE_SINIDENTIF_TAR');
      $afipProduccion = (boolean) env('AFIP_PRODUCCION');
      $afip = new Afip( [ 'CUIT' => $cuitEmpresa , 'production' => $afipProduccion ]  ); 

      /*  Numero del punto de venta  */
      $punto_de_venta = (int) $this->punto_de_venta;


//      dd($cuitEmpresa);  
 
      /**
       * Tipo de factura
             1 = Factura A
             3 = Nota de Crédito A   
             6 = Factura B
             8 = Nota de Crédito B
            11 = Factura C
            13 = Nota de Crédito C       
       **/
      $tipo_de_documento = $this->cliente_cod_documento ; // cod numerico para afip
      $numero_de_documento = (float) $this->cliente_documento; // Segun corresponda porel tipo
      /**
       * Tipo de documento del comprador
         80 = CUIT
         86 = CUIL
         96 = DNI
         99 = Consumidor Final
         Numero de documento del comprador (0 para consumidor final)
       **/
      if($tipo_de_documento == 99) $numero_de_documento = 0;

      // Validaciones
      if( $this->tipo_de_factura == 1 or $this->tipo_de_factura ==  3 ) {  // Facturas A o Notas de Cred A
        if ( $tipo_de_documento != 80 ) {
          throw new \ErrorException('Para comprobantes clase A y M el campo Tipo Documento debe ser igual a CUIT');
        }
      }

      $importe_total = $this->importe_gravado + $this->importe_iva + $this->importe_exento_iva;
      if ( $numero_de_documento == 0  and $importe_total > $tope_sin_ident ) {
         throw new \ErrorException('El comprobante supera Tope de $ ' . $tope_sin_ident . '.<br>Tiene que Identificar al Cliente (Cargar datos)'  );
      }

      if ( $tipo_de_documento != 99  and $numero_de_documento == 0) {
          throw new \ErrorException('Falta completar el DNI o CUIT');
      }

      /**  Número de la ultima Factura  **/

      //RAN  Si da error adentro ElectronicBilling no lo intercepta Verr
     $last_voucher = $afip->ElectronicBilling->GetLastVoucher($punto_de_venta, $this->tipo_de_factura);
     // dd($last_voucher);
      /*
        Concepto de la factura
         1 = Productos
         2 = Servicios
         3 = Productos y Servicios
      */
      $concepto = 1;

      /*  Numero de factura  */
      $numero_de_factura = $last_voucher + 1;
      $this->numero_de_factura = str_pad($numero_de_factura,8,"0" , STR_PAD_LEFT);

      /**
       * Fecha de la factura en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
       **/
      //$fecha = date('Y-m-d');
      $auxfecCom = date_create($this->fecha_factura);
      $fecha = date_format($auxfecCom ,'Y-m-d');

      /**
       * Importe sujeto al IVA (sin icluir IVA)
       **/
      $importe_gravado = $this->importe_gravado;

      /**
       * Importe exento al IVA
       **/
      $importe_exento_iva = $this->importe_exento_iva;;

      /**
       * Importe de IVA
       **/
      $importe_iva = $this->importe_iva;


      // Si es nota de crel lleva (unica diferencia) 'CbtesAsoc' =>  ...
      if( $this->tipo_de_factura == 3 or $this->tipo_de_factura == 8  ) { // Nota Cred A
        if( $this->tipo_de_factura == 3 ) { // Nota Cred A
          $factura_asociada = array( array(
              'Tipo'          => 1, // Factura A
              'PtoVta'        => $this->punto_facturaOriginal,
              'Nro'           => $this->facturaOriginal,
          ));        
        }
        if( $this->tipo_de_factura == 8 ) { // Nota Cred B
          $factura_asociada = array( array(
              'Tipo'          => 6, // Factura B
              'PtoVta'        => $this->punto_facturaOriginal,
              'Nro'           => $this->facturaOriginal,
          ));        
        }
        $data = array(
              'CantReg'       => 1, // Cantidad de facturas a registrar
              'PtoVta'        => $punto_de_venta,
              'CbteTipo'      => $this->tipo_de_factura,
              'Concepto'      => $concepto,
              'DocTipo'       => $tipo_de_documento,
              'DocNro'        => $numero_de_documento,
              'CbteDesde' => $numero_de_factura,
              'CbteHasta' => $numero_de_factura,
              'CbteFch'       => intval(str_replace('-', '', $fecha)),
              'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
              'ImpTotConc'=> 0, // Importe neto no gravado
              'ImpNeto'       => $importe_gravado,
              'ImpOpEx'       => $importe_exento_iva,
              'ImpIVA'        => $importe_iva,
              'ImpTrib'       => 0, //Importe total de tributos
              'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
              'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
              'CbtesAsoc' =>   $factura_asociada , //Factura asociada
              'Iva'           => array(// Alícuotas asociadas al factura
                      array(
                              'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                              'BaseImp'       => $importe_gravado,
                              'Importe'       => $importe_iva
                      )
              ),
        );
      }else{ // Es factura
        $data = array(
              'CantReg'       => 1, // Cantidad de facturas a registrar
              'PtoVta'        => $punto_de_venta,
              'CbteTipo'      => $this->tipo_de_factura,
              'Concepto'      => $concepto,
              'DocTipo'       => $tipo_de_documento,
              'DocNro'        => $numero_de_documento,
              'CbteDesde' => $numero_de_factura,
              'CbteHasta' => $numero_de_factura,
              'CbteFch'       => intval(str_replace('-', '', $fecha)),
              'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
              'ImpTotConc'=> 0, // Importe neto no gravado
              'ImpNeto'       => $importe_gravado,
              'ImpOpEx'       => $importe_exento_iva,
              'ImpIVA'        => $importe_iva,
              'ImpTrib'       => 0, //Importe total de tributos
              'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
              'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
              'Iva'           => array(// Alícuotas asociadas al factura
                      array(
                              'Id'            => 5, // Id del tipo de IVA (5 = 21%)
                              'BaseImp'       => $importe_gravado,
                              'Importe'       => $importe_iva
                      )
              ),
        );
      }  // Fin si es Nota de cred o Factura
      
      if( $this->tipo_de_factura == 11 ) { // Factura C
        $data = array(
          'CantReg'       => 1, // Cantidad de facturas a registrar
          'PtoVta'        => $punto_de_venta,
          'CbteTipo'      => $this->tipo_de_factura,
          'Concepto'      => $concepto,
          'DocTipo'       => $tipo_de_documento,
          'DocNro'        => $numero_de_documento,
          'CbteDesde' => $numero_de_factura,
          'CbteHasta' => $numero_de_factura,
          'CbteFch'       => intval(str_replace('-', '', $fecha)),
          'ImpTotal'      => $importe_gravado + $importe_iva + $importe_exento_iva,
          'ImpTotConc'=> 0, // Importe neto no gravado
          'ImpNeto'       => $importe_gravado + $importe_iva + $importe_exento_iva,
          'ImpOpEx'       => 0,
          'ImpIVA'        => 0,
          'ImpTrib'       => 0, //Importe total de tributos
          'MonId'         => 'PES', //Tipo de moneda usada en la factura ('PES' = pesos argentinos)
          'MonCotiz'      => 1, // Cotización de la moneda usada (1 para pesos argentinos)
        );
      }  // Fin si  Factura C



      /*
          Creamos la Factura
      */
   //    dd($data);
      $res = $afip->ElectronicBilling->CreateVoucher($data);
      //dd($res);
      $this->CAE = $res['CAE']; //CAE asignado a la Factura
      $this->CAEFchVto = $res['CAEFchVto']; //Fecha de vencimiento del CAE

      return ""; // Todo Ok

    } catch (\Exception $e) {
       //  dd ("capturo try de Genero Afip:", $e->getMessage() , $e); 

        if ($e->getCode() == 4 ) {
           return  "No hay conección con el Servidor de Afip <br>" .  $e->getMessage(); // Error de Coneccion Dejo Pendiente
        }else{
           return  "Captura Error en  nuevo_comprobante <br>" .  $e->getMessage();
        }
        // dd($data,$e );

    } //Fin try catch

 }  // fin crea_comprobante

 public static function consulta_comprobante ($numero_de_factura, $punto_de_venta, $tipo_de_factura)  {

  /**
      Entrada:
      * Tipo de comprobante  
           1 = Factura A
           3 = Nota de Crédito A   
           6 = Factura B
           8 = Nota de Crédito B
          11 = Factura C
          13 = Nota de Crédito C
      * Numero del punto de venta
      * Numero de factura
  **/

  $msgError = "";
  $informacion = ""; 
  try {
      // Busco en pagina de afip para sacar los datos
      $cuitEmpresa = (float) env('CUIT');
      $afipProduccion = (boolean) env('AFIP_PRODUCCION');
      $afip = new Afip( [ 'CUIT' => $cuitEmpresa , 'production' => $afipProduccion ]  ); 
      /**
      * Informacion de la factura
      **/      
      $informacion = $afip->ElectronicBilling->GetVoucherInfo($numero_de_factura, $punto_de_venta, $tipo_de_factura);
 //  dd($informacion);
      if($informacion === NULL){
        $msgError = 'El Comprobante no existe en AFIP';
      }

  } catch (\Exception $e) {
    //dd ("capturo el try", $e);
    $msgError = $e->getMessage();
  }

  return [
    'informacion' =>  $informacion,
    'msgError' => $msgError  
  ];

  return response()->json([
      'informacion' =>  $informacion,
      'msgError' => $msgError  
  ]);

} // Fin consulta_comprobante   


 public static function valida_cuit ( $cuit )  {

   	// Entrada	
  	 $cuit = (double) $cuit;
  	// Retorna
     $msgError = "";
     $direccion	= "";
     $razonSocial = "";


	  //$this->validate($request, [
	  //  'cuit'=>['required',new \App\Rules\Cuit()]
    //]);

    try {
        // Busco en pagina de afip para sacar los datos
		    $cuitEmpresa = (float) env('CUIT');
		    $afipProduccion = (boolean) env('AFIP_PRODUCCION');
        $afip = new Afip( [ 'CUIT' => $cuitEmpresa , 'production' => $afipProduccion ]  ); 

        $datos = $afip->RegisterScopeThirteen->GetTaxpayerDetails($cuit);
     //   dd($datos);
        if($datos === NULL){
          $msgError = ( 'El contribuyente no existe en el padrón Afip.');
        }else{
          if($datos->estadoClave == "ACTIVO") {
		        if($datos->tipoPersona == "FISICA") {
		        	$razonSocial = $datos->apellido . " " . $datos->nombre ;	
		        }else{
		        	$razonSocial = $datos->razonSocial;	
		        }
		        $direccion	=	$datos->domicilio[0]->direccion;
          }else{
              $msgError = "El contribuyente no esta ACTIVO en padrón Afip.";
          }
        }    
                
    } catch (\Exception $e) {
     //   dd ("capturo el try", $e); 
    	$msgError = $e->getMessage();
    }

    return response()->json([
        'direccion'  =>   $direccion,
        'razonSocial' =>  $razonSocial,
        'msgError' => $msgError  
    ]);
 

 }  // fin valida_cuit

 public static function valida_estado_servidor ()  {
 
  /*  Valida el estado del Servidor Afip
      Retorna  Mensaje de Error    
      Y en $informacion    mas detalle si es que hay error
  */
  
  $msgError = "";
  $informacion = "";

  try {
      // Busco en pagina de afip para sacar los datos
      $cuitEmpresa = (float) env('CUIT');
      $afipProduccion = (boolean) env('AFIP_PRODUCCION');
      $afip = new Afip( [ 'CUIT' => $cuitEmpresa , 'production' => $afipProduccion ]  ); 
      $informacion = $afip->RegisterScopeFour->GetServerStatus();
      if($informacion->appserver !=  "OK" ) $msgError = "Error en Servidor Afip (appserver)";
      if($informacion->authserver !=  "OK" ) $msgError = "Error de Autorización Servidor Afip (authserver)";
      if($informacion->dbserver !=  "OK" ) $msgError = "Error de Base en Servidor Afip (dbserver)";
      // Retorno   "appserver": "OK"
      // "authserver": "OK"
      // "dbserver": "OK
      //dd($informacion);    

  } catch (\Exception $e) {
//        dd ("capturo el try", $e);
    if ($e->getcode() == 4 ) {
      $msgError = "No hay conección con el Servidor de Afip <br> Verifique si hay Internet";
      $informacion = "Error Interno";
    }else{
      $msgError = $e->getMessage();        
      $informacion = "Error Desconocido";
    }
  }

  return response()->json([
    'informacion' =>  $informacion,
    'msgError' => $msgError  
  ]);

 } // Fin valida_estado   



 private function ConvTipoDocumento_Nro( $tipoletra){
 
    // Retorna Cod Numerico Segun Afip
      /**
       * Tipo de documento del comprador
         80 = CUIT
         86 = CUIL
         96 = DNI
         99 = Consumidor Final

         Numero de documento del comprador (0 para consumidor final)
       **/
    switch ($tipoletra ) {
      case "CUIT": 
        return 80 ;  
        break;
      case "DNI": 
        return 96 ;  
        break;
      case "CUIL": 
        return 86 ;  
        break;
      case "LE": 
        return 89 ;  
        break;        
      case "LC": 
        return 90 ;  
        break;        
    }        
 }
	
} // Fin de la clase 
?>