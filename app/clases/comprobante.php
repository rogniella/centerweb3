<?php namespace app\clases;

use Illuminate\Support\Facades\DB;
use App\clases\correlativo;
use App\clases\ws_afip;
use App\clases\PDFFactura\PDFVoucher;
use App\clases\PDFFactura\PDFPresupuesto;
use App\Models\producto;
use App\Models\caja;
use App\Models\mcaja;
use App\Models\factura;
use App\Models\cliente;
use App\Models\ot;
use App\Models\comprobantebd;

class comprobante {

    // Propiedades
    public $ret = ''; // Retorno de Ejecución  "" = Ok  sino Retoran Mensaje de Error

    // Datos del comprobante de Venta o Presupuesto:
    private $comp_sucursal = 0;
    public $comp_tipoot = ''; // FC = Ventas directas Suc
                              // VT = Venta en sistema WEB
                              // PR = Presupuesto
                              // A,L,C,R,G  Tipos de OT
    public $comp_id = 0;
    public $comp_fecmov = '';
    public $comp_responsable = 'CAJA'; //POR DEFECTO
    public $comp_idcli = 0;
    public $comp_monto = 0;
    private $comp_idanula = 0;
    private $comp_estado = '';
    private $comp_idfactura = 0;
    public $comp_observaciones = '';

    // Datos para Factura AFIP
    public $tipo_de_factura = 0; // Cod Numerico Segun AFIP
    public $fecha_factura = '';
    private $tipo_de_comprobante = ''; // Tipo Interno para tabla de facturas  A, B , ....
    private $CAE = ''; // string de 14
    private $CAEFchVto = ''; //  "2019-10-16"
    private $punto_de_venta = '';
    private $numero_de_factura = '';
    private $punto_facturaOriginal = '';
    private $facturaOriginal = '';

    // Linea de Detalles de Items Vendidos
    public $linea_detalle = [];
    private $linea_alicuotas = [];

    // Lineas de Formas de Pago
    public $linea_pago = [];

    // Lista de campos Internos del Obj
    private $importe_iva = 0;
    private $importe_gravado = 0;
    private $cliente = '';
    private $auxEstadoFactua = '';
    private $auxErrorAfip = '';



  public static function find( $suc, $tipoOT, $id) {

    // Trae todos los datos de 1 comprobante
    //  Puede ser de una Ot o de Una venta Directa

    try {
      $comprobante = new self;
      $comprobante->comp_tipoot = $tipoOT; 
      $comprobante->comp_id = $id;
      $comprobante->comp_sucursal= $suc; 

      // Si son Ordenes de Trabajos busco en tabla Ot
      if (in_array($tipoOT, ['A', 'L', 'C', 'R', 'G'])) {
        $ot = ot::find_suc_id( $suc,$tipoOT, $id );
        $comprobante->comp_fecmov =  $ot->Ot_FecPedido;
        $comprobante->comp_responsable =  $ot->Ot_UsuUltMan; 
        $comprobante->comp_monto =  $ot->Ot_Precio; 
        $comprobante->comp_estado =  "ver"; 
        $comprobante->comp_idfactura = 0; //ver  $datos[0]->Comp_IdFactura; 
        $comprobante->comp_idcli =  $ot->Ot_IdCli; 
      }else{
        $comprobante->ret = $comprobante->lee_tabla_comprobante();
      }  

      if ($comprobante->ret <> "") { 
        return $comprobante; //Dio error
      }
      
      $comprobante->CompletoDatosCliente();

      // Leo datos de factura
      $factura = factura::findIdComprobante( $comprobante->comp_tipoot, $comprobante->comp_id ,  $comprobante->comp_sucursal ) ;
      if($factura ) {
          $comprobante->auxEstadoFactua = $factura->Fac_Estado; 
          $comprobante->CAE = $factura->Fac_CAE; // "14121145121421"
          $comprobante->CAEFchVto = date_format(date_create($factura->Fac_VencimientoCAE) ,'Y-m-d'); // "2019-10-16"
          $comprobante->tipo_de_comprobante = $factura->Fac_Comprobante; // A, B , S, R
          $comprobante->tipo_de_factura  = $comprobante->ConvTipoCompLetra_Nro( $comprobante->tipo_de_comprobante);  // Cod numerico segun afip
          $comprobante->numero_de_factura = $factura->Fac_NroFactura; 
           $comprobante->punto_de_venta = $factura->Fac_NroPuntoVta;

          // Si es una Nota de Credito Busco el comprobante Original
          if($factura->Fac_NotaCredito != 0 ) {
              $facturaOriginal = factura::find( $factura->Fac_NotaCredito);
              $comprobante->punto_facturaOriginal = $facturaOriginal->Fac_NroPuntoVta;
              $comprobante->facturaOriginal = $facturaOriginal->Fac_NroFactura;
          }

      }else{
          $comprobante->CAE = ""; 
          $comprobante->CAEFchVto = "";
          $comprobante->tipo_de_comprobante = ""; 
          $comprobante->auxEstadoFactua = ""; // Marcar que no Tiene ?? 
          if ($comprobante->comp_tipoot == "PR" ){
            $comprobante->tipo_de_comprobante = "P";
            $comprobante->punto_de_venta  = str_pad( $comprobante->comp_sucursal , 4, "0", STR_PAD_LEFT) ;
            $comprobante->numero_de_factura = $comprobante->comp_id;
          }
      } 

      // Cargo las lineas de detalle
      $consulta = "SELECT * FROM moviproductos  WHERE    Mov_TipoOT=? and Mov_IdOt=? and Mov_Sucursal=?  and  Mov_Familia <> 'PROC' and  Mov_Familia <> 'REP' and   Mov_Familia <> 'CRI' " ;
      $datos = DB::select($consulta,[ $comprobante->comp_tipoot, $comprobante->comp_id ,  $comprobante->comp_sucursal] );

      foreach ($datos as $row) {
          // En las ventas o presu las cantidades estan en negativo
          if ($row->Mov_Operacion == 'V' OR $row->Mov_Operacion == 'P' ) $row->Mov_Cantidad = $row->Mov_Cantidad * -1;   
          $precio_unitario_tomado =  $row->Mov_PrecioUnitario;
          $importe_bonif = 0;
          if($row->Mov_Bonif > 0 ) {
            $importe_bonif = round( $row->Mov_PrecioUnitario * ( 1 - ( $row->Mov_Bonif /100)),2) ;
            $precio_unitario_tomado =  $importe_bonif;
          }
          // Si es factura A , va sin Iva
          $precio_unitario_a = 0;
          $importe_bonif_a = 0;
          $precio_unitario_tomado_a =  0;
          if($comprobante->tipo_de_comprobante == "A" or $comprobante->tipo_de_comprobante == "R" ) {
            $precio_unitario_a = $row->Mov_PrecioUnitario  - round( $row->Mov_PrecioUnitario  - ( $row->Mov_PrecioUnitario / 1.21)  , 2) ;
            $precio_unitario_tomado_a = $precio_unitario_a ;
            if($importe_bonif > 0 ) {
                $importe_bonif_a = $importe_bonif  - round( $importe_bonif  - ( $importe_bonif / 1.21)  , 2) ;
                $precio_unitario_tomado_a =  $importe_bonif_a;
            }
          }
          
          $comprobante->linea_detalle [] = [
            "familia" => $row->Mov_Familia,
            "codigo" => $row->Mov_IdProd,
            "detalle" => $row->Mov_Descripcion,
            "cantidad" => $row->Mov_Cantidad,
            "bonif" => $row->Mov_Bonif, // % bonif
            "precio_unitario" => $row->Mov_PrecioUnitario,
            "precio_unitario_a" => $precio_unitario_a,
            "importe_bonif" => $importe_bonif , // Es el unitario con desc o cero
            "importe_bonif_a" => $importe_bonif_a , // Es el unitario con desc o cero
            "precio_unitario_tomado" => $precio_unitario_tomado, // Es el que se concidera para los calculos 
            "precio_unitario_tomado_a" => $precio_unitario_tomado_a, // Es el que se concidera para los calculos 
            "tipoiva" => 5, //FALTA por ahora todo 21%
            "importe_iva" => 0 //lo completa despues
          ];          
      }  
      // Con linea_detalle  carga linea_alicuotas
      $comprobante->cargaAlicuotasIva();

      // Cargo las formas de Pago
      $consulta = "SELECT * FROM caja  WHERE    Caj_TipoOT=? and Caj_IdOt=? and Caj_SucursalOri=? " ;
      $datos = DB::select($consulta,[ $comprobante->comp_tipoot, $comprobante->comp_id ,  $comprobante->comp_sucursal] );
      //dd($datos,$tipoOT , $id , $suc);
      foreach ($datos as $row) {
          $comprobante->linea_pago [] = [
            "detalle" => $row->Caj_Detalle,
            "formapago" => $row->Caj_Moneda,
            "fecha" => $row->Caj_FecMov,
            "monto" => $row->Caj_Monto,
            "tarjeta" => $row->Caj_Tarjeta ,
            "cuotas" =>  $row->Caj_Cuotas
          ];          
      }

    } catch (\Exception $e) {
      $comprobante->ret =  $e->getMessage();

    }
    return $comprobante;
  }

  public function GeneraPRESUPUESTO( $esReimpresion = false){

    // Genera Impresión Presupuesto en formato PDF
    //DD($this);
    if( $this->cliente->Aux_CodDocumento == 99) {
      $this->cliente->Cli_Documento = "0";
      $this->cliente->Cli_CodDocumento = ""; //  "No Requerido"
    } 

    $voucher = Array
    (
        "idVoucher" => 1, //??
        "numeroComprobante" => $this->numero_de_factura,
        "numeroPuntoVenta" =>  $this->punto_de_venta,
        "letra" => $this->ConvTipoCompNro_Letra_PDF(  $this->tipo_de_factura ) ,
        "observacion" => $this->comp_observaciones,
        "tipoResponsable" => $this->descripcionTipoResponsable( $this->cliente->Cli_CodRespIVA ), //"Consumidor Final",
        "nombreCliente" =>  $this->cliente->Cli_ApeNom,
        "domicilioCliente" => $this->cliente->Cli_Calle,
        "fechaComprobante" => date_format(date_create($this->comp_fecmov) ,'Ymd'),  //  "20190303",
        "codigoTipoComprobante" => $this->tipo_de_factura , //cod numerico segun afip
        "TipoComprobante" => $this->descripcionTipoComprobante( $this->tipo_de_comprobante ),  //"Factura",
        "codigoConcepto" => 1,
        "codigoMoneda" => "$",
        "cotizacionMoneda" => 1.000,
        "codigoTipoDocumento" => $this->cliente->Aux_CodDocumento,  //  cod nro 96,
        "TipoDocumento" => $this->cliente->Cli_CodDocumento, 
        "numeroDocumento" => $this->cliente->Cli_Documento, // Debe ser diferente al DNI del emisor
        "vendedor"  => $this->comp_responsable,
        "importeTotal" => $this->comp_monto,
        "importeOtrosTributos" => 0.000,
        "importeGravado" =>  $this->importe_gravado,
        "importeNoGravado" => 0.000,
        "importeExento" => 0.000,
        "importeIVA" => $this->importe_iva,
        "codigoPais" => 200,
        "idiomaComprobante" => 1,
        "NroRemito" => 0,
        "CondicionVenta" => "Efectivo",
        "items" => [],
        "subtotivas" => [],
        "Tributos" => Array(),
        "CbtesAsoc" => Array()
    );

    // Recorro todas las lineas de Detalle
    foreach ($this->linea_detalle as  $linea) {
         $voucher["items"][] = [
                "codigo" => $linea['familia'] . $linea['codigo'],
                "scanner" => 0,
                "descripcion" => $linea['detalle'],
                "cantidad" => $linea['cantidad'],
                "porcBonif" => $linea['bonif'],
                "impBonif" =>  $linea['importe_bonif'] , 
                "precioUnitario" => $linea['precio_unitario'],
                "importeItem" => $linea['precio_unitario_tomado'] * $linea['cantidad']
        ];
    } //fin  Recorro todas las lineas de Detalle
 

    $config = array( 
       "TRADE_TELEFONO" => env('TELEFONO'),
       "TRADE_CUIT"=> env('CUIT'),
       "TRADE_ADDRESS" => env('DIRECCION'),
       "TRADE_WHATSAPP" => env('WHATSAPP'),
       "TRADE_INIT_ACTIVITY" => env('INICIO_ACTIVIDAD') 
    );

    $logo_path =  public_path() . '/imagenes/logo.jpg' ; // "c:/logo.jpg";

    //RAN agregado porque daba error
    error_reporting(E_ALL & ~E_NOTICE);
      ini_set('display_errors', 0);
      ini_set('log_errors', 1);
      ob_end_clean();

    try {

      $pdf = new PDFPresupuesto($voucher, $config);

      $pdf->emitirPDF($logo_path);

      $file =  "/presupuestos/" . $this->tipo_de_comprobante  . $this->punto_de_venta ."-" . $this->numero_de_factura .".pdf";

      $file_completo =  public_path() . $file;

      // Guardamos a PDF
      $pdf->Output( $file_completo ,'F' ); //genera el archivo

      return $file;

    } catch (Exception $e) {
        displaylog( 'Falló la Generación del PDF: ' . $e->getMessage() );
        echo 'Falló la Generación del PDF: ' . $e->getMessage();
    }

  } // GeneraPRESUPUESTO

  public function GeneraPDF( $esReimpresion = false){

    // Genera Impresión Factura en formato PDF

    if( $this->cliente->Aux_CodDocumento == 99) {
      $this->cliente->Cli_Documento = "0";
      $this->cliente->Cli_CodDocumento = "NR"; //  "No Requerido"
    } 
    if($this->facturaOriginal != "") {
      $tipOriginal = "B";
      if($this->tipo_de_comprobante == "R") { $tipOriginal = "A";}
      $texto_comprobante_asociado = "Factura " . $tipOriginal . " " .  $this->punto_facturaOriginal  .  "-" . $this->facturaOriginal ; 
    }else{
      $texto_comprobante_asociado = "";
    }

    $voucher = Array
    (
        "idVoucher" => 1, //??
        "numeroComprobante" => $this->numero_de_factura, // Debe estar sincronizado con el último AFIP
        "numeroPuntoVenta" =>  $this->punto_de_venta,
        "cae" => $this->CAE,
        "letra" => $this->ConvTipoCompNro_Letra_PDF(  $this->tipo_de_factura ) ,
        "fechaVencimientoCAE" => $this->CAEFchVto,
        "observacion" => $this->comp_observaciones,
        "tipoResponsable" => $this->descripcionTipoResponsable( $this->cliente->Cli_CodRespIVA ), //"Consumidor Final",
        "nombreCliente" =>  $this->cliente->Cli_ApeNom,
        "domicilioCliente" => $this->cliente->Cli_Calle,
        "fechaComprobante" => date_format(date_create($this->comp_fecmov) ,'Ymd'),  //  "20190303",
        "codigoTipoComprobante" => $this->tipo_de_factura , //cod numerico segun afip
        "TipoComprobante" => $this->descripcionTipoComprobante( $this->tipo_de_comprobante ),  //"Factura",
        "codigoConcepto" => 1,
        "codigoMoneda" => "$",
        "cotizacionMoneda" => 1.000,
        "texto_comprobante_asociado" =>  $texto_comprobante_asociado,
        "codigoTipoDocumento" => $this->cliente->Aux_CodDocumento,  //  cod nro 96,
        "TipoDocumento" => $this->cliente->Cli_CodDocumento, 
        "numeroDocumento" => $this->cliente->Cli_Documento, // Debe ser diferente al DNI del emisor
        "importeTotal" => $this->comp_monto,
        "importeOtrosTributos" => 0.000,
        "importeGravado" =>  $this->importe_gravado,
        "importeNoGravado" => 0.000,
        "importeExento" => 0.000,
        "importeIVA" => $this->importe_iva,
        "codigoPais" => 200,
        "idiomaComprobante" => 1,
        "NroRemito" => 0,
        "CondicionVenta" => "Efectivo",
        "items" => [],
        "subtotivas" => [],
        "Tributos" => Array(),
        "CbtesAsoc" => Array()
    );


    // Recorro todas las lineas de Detalle
    foreach ($this->linea_detalle as  $linea) {     

        $voucher["items"][] = [
                "codigo" => '' , //$linea['familia'] + $linea['codigo'],
                "scanner" => 0,
                "descripcion" => $linea['detalle'],
                "codigoUnidadMedida" => 7,
                "UnidadMedida" => "Unidades",
                "codigoCondicionIVA" => $linea['tipoiva'],
                "Alic" => 21,
                "cantidad" => $linea['cantidad'],
                "porcBonif" => $linea['bonif'],
                "impBonif" =>  $linea['importe_bonif'] , 
                "precioUnitario" => $linea['precio_unitario'],
                "impBonif_a" =>  $linea['importe_bonif_a'] , 
                "precioUnitario_a" => $linea['precio_unitario_a'],
                "importeIVA" => $linea['importe_iva'],
                "importeItem" => $linea['precio_unitario_tomado']  * $linea['cantidad'],
                "importeItem_a" => $linea['precio_unitario_tomado_a']  * $linea['cantidad']
        ];
    } //fin  Recorro todas las lineas de Detalle
 
    // Recorro todas las lineas de AlicuotasIva
    foreach ($this->linea_alicuotas as  $linea) {     
        $voucher["subtotivas"][] = [
                "codigo" => $linea['tipoIva'] , // tipo numerico 5, ..
                "Alic" => $linea['descriIva'],  // Descripcion 21 ,10.05 ...
                "importe" => $linea['importe'],
                "BaseImp" => $linea['baseImp']
        ];
    } //fin  Recorro todas las lineas de Alicuotas

    $config = array( 
       "TRADE_SOCIAL_REASON" => env('RAZON_SOCIAL'),
       "TRADE_CUIT"=> env('CUIT'),
       "TRADE_ADDRESS" => env('DIRECCION'),
       "TRADE_TAX_CONDITION" => env('CONDICION_IVA'),
       "TRADE_INIT_ACTIVITY" => env('INICIO_ACTIVIDAD') 
    );

    $logo_path =  public_path() . '/imagenes/logo.jpg' ; // "c:/logo.jpg";

    //RAN agregado porque daba error
    error_reporting(E_ALL & ~E_NOTICE);
      ini_set('display_errors', 0);
      ini_set('log_errors', 1);
      ob_end_clean();

    try {
      $pdf = new PDFVoucher($voucher, $config);

      $pdf->emitirPDF($logo_path);

      $file =  "/facturas/copias/" . $this->tipo_de_comprobante  . $this->punto_de_venta ."-" . $this->numero_de_factura .".pdf";

      $file_completo =  public_path() . $file;

      // Guardamos a PDF
      $pdf->Output( $file_completo ,'F' ); //genera el archivo

      if ($esReimpresion)   {
        // Imprime con copia y duplicado
        return $file;
      }

      $pdf = new PDFVoucher($voucher, $config);
      $pdf->emitirPDF($logo_path , true );

      $file =  "/facturas/" . $this->tipo_de_comprobante  . $this->punto_de_venta ."-" . $this->numero_de_factura .".pdf";

      $file_completo =  public_path() . $file;

      // Guardamos a PDF
      $pdf->Output( $file_completo ,'F' ); //genera el archivo

      return $file;

    } catch (Exception $e) {
        displaylog( 'Falló la Generación del PDF: ' . $e->getMessage() );
        echo 'Falló la Generación del PDF: ' . $e->getMessage();
    }

  } // GeneraPDF

  public function generaComprobanteAFIP() {

    // Es llamado desde Vb , o para reintentar si quedo con error 
    // Genera Factura en AFIP
    // Completo datos en tablas:     
    //   -  Actualiza en tabla Facturas
    //         Lo deja en estado Emitida y completa el nro CAE
    //   -  Y actuliza en tabla de correlativos

    $this->ret =  ""   ; // todo Ok por defecto
    try {
     
        $this->comp_fecmov = fechahoy();
        // Si ingreso un Cliente, tomo los datos
        $this->CompletoDatosCliente();
        if ( $this->cliente->Aux_CodDocumento == 80 ){
         $this->ret = validarCUIT($this->cliente->Cli_Documento);
         if ($this->ret != "") {
           throw new \ErrorException($this->ret  );
         }   
        }
        $this->ret =  $this->crea_comprobante_afip();
        if($this->ret == "") {
            // Todo correctos ya se gravo en Afip y retorno Nro Fact, CAE , y fecha Venc CAE
            // Actualizo datos de factura
            $factura = factura::findIdComprobante( $this->comp_tipoot, $this->comp_id , $this->comp_sucursal);
            $factura->Fac_NroPuntoVta = $this->punto_de_venta;
            $factura->Fac_NroFactura = $this->numero_de_factura ;
            $factura->Fac_CAE = $this->CAE;
            $factura->Fac_VencimientoCAE =  $this->CAEFchVto;
            $factura->Fac_Estado = "E"; //Emitida
            $factura->save();  

            $ret =  correlativo::gravo_correlativo($this->tipo_de_factura,$this->numero_de_factura);
            if ($ret == -1   ) {
              displaylog('Error: GeneraFAC AFIP En AFIP bien. Error Al grabar Correlativo en tabla interna. Comp:' . $this->numero_de_factura );
              $this->ret = "Error: Al grabar Correlativo Comprobante Factura"  ;
            }
        }else{
            displaylog('Error: GeneraFAC EN AFIP, NO se genero:' . $this->ret . ' Comp:' .$this->numero_de_factura );
        }  
    } catch (\Exception $e) {
        $this->ret =  $e->getMessage() ;
        displaylog('Error: GeneraFAC AFIP, NO se genero:' . $this->ret . ' Comp:' .$this->numero_de_factura );
        return  $this->ret;
    }

    return  $this->ret;

  } // fin generaComprobanteAFIP


  public function nuevo(){

    // Completo datos en tablas:
    //     Inserta en tabla Comprobante 
    //     Inserta en Tabla Movimientos de Productos
    //     Actualiza Stock en tabla Productos
    //     Inserta en tabla Caja (1 * forma de pago)
    //     Actualiza Base Caja y tabla Mcaja  ** pero considera solo 1 forma de pago ***  
    //        FALTA   *** si es Multiple Formas Pagos ***
    //     Crea comprobante en sistema AFIP (Con F9 no genero Factura)
    //     Inserta tabla de Facturas (Con F9 no genero Factura)

    $this->ret =  ""   ; // todo Ok por defecto

    $operacion = match ($this->comp_tipoot) {
        'VT' => 'V',
        'PR' => 'P',
        default => 'X',
    };
    $this->tipo_de_comprobante = $operacion === 'V'
        ? $this->ConvTipoCompNro_Letra($this->tipo_de_factura)
        : 'P';
    if ($operacion === 'P') {
        // Si es presupuesto no actualiza pagos
        $this->numero_de_factura = $this->comp_id;
        $this->CAE = "";
        $this->tipo_de_factura = 'Z';
    }
               

    $this->punto_de_venta=  str_pad( $this->punto_de_venta ,4,"0" , STR_PAD_LEFT) ; //LE doy formato por las dudas

  try {

    DB::beginTransaction();

    // Si ingreso un Cliente, tomo los datos
    $this->CompletoDatosCliente();
    if ($this->ret != "") {
        throw new \ErrorException( $this->ret );
    }
    if ( $this->cliente->Aux_CodDocumento == 80 ){
      $this->ret = validarCUIT($this->cliente->Cli_Documento);
      if ($this->ret != "") {
        throw new \ErrorException($this->ret  );
      }  
    }
    // Con las lineas de Detalle , carga los campos de totales, ivas y alicuoas de iva
    $this->cargaAlicuotasIva();

    // Inserta en tabla de comprobantes y retorna el Id Generado
    if ( $this->comp_tipoot == "VT" or $this->comp_tipoot == "PR" ){  // Si es una venta Web o Presupuesto
        $this->ret = $this->insert_comprobante($this->comp_tipoot);
        if ($this->ret != "") {
           throw new \ErrorException('Error: Al Insertar en Tabla Comprobantes <br>' . $this->ret );
      }
    }
                    
 	  // Recorro todas las lineas de Detalle y actualizo Stock de los productos
    foreach ($this->linea_detalle as  $items) {     
        // Grabo tabla de movimientos de Productos
		    $producto = new producto();
        if  ( ! $producto  = Producto::findCodigo($items["familia"],$items["codigo"] ) ) {
           throw new \ErrorException("Error al buscar producto:" . $items["familia"] ."&nbsp;" . $items["codigo"]);
        } 
        $producto->Prod_UsuUltMan =   $this->comp_responsable;
        if ($items['detalle'] == "" ) {
            $items['detalle'] = $producto->Prod_Descripcion ;  
        }    
        $producto->mov_sucursal =  $this->comp_sucursal;
        // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock en tabla Producto si corresponde
        $this->ret = $producto->addMovimiento($operacion,$items['cantidad'],$items['precio_unitario'],
               $this->comp_id , 0 , $items['detalle'] , $this->comp_tipoot , $producto->mov_sucursal, $items['bonif'] );
        if ($this->ret != "") {
          throw new \ErrorException("Error al actualizar linea Detalle:" . $this->ret );
        }          
    } //fin  Recorro todas las lineas de Detalle


    // Si es Vta Web Graba los pagos  (tablas Mcaja y Caja)
    //    Y genero Factura en Afip + grabo tabla de facturas
    if ( $this->comp_tipoot == 'VT') {
      $correlativo = 0;
      $this->graba_MCaja();
      
      // Recorro todas las Formas de Pago
      foreach ($this->linea_pago as  $items) {     
        $correlativo ++;
        // Grabo tabla de Pagos
        $caja = New caja;
        $caja->Caj_IdOt = $this->comp_id;
        $caja->Caj_TipoOT = $this->comp_tipoot ;
        $caja->Caj_FecMov=fechahorahoy();
        $caja->Caj_FecAlta=fechahorahoy();
        $caja->Caj_Estado="";
        $caja->Caj_Sucursal = $this->comp_sucursal; 
        $caja->Caj_Correlativo = $correlativo ;
        $caja->Caj_Detalle = $items["detalle"];
        $caja->Caj_Moneda = $items["moneda"];
        $caja->Caj_Tarjeta = $items["tarjeta"];
        $caja->Caj_Cuotas = $items["cuotas"];
        $caja->Caj_Monto = numdec($items["monto"] ,2) ;
        $caja->Caj_MontoMonOri = numdec($items["montomonori"] ,2) ;
        $caja->Caj_Operacion = 'V'; // Venta
        $caja->Caj_Cotizacion = $items["cotizacion"];
        $caja->Caj_Responsable = $this->comp_responsable;
        $caja->Caj_UsuAlta = $this->comp_responsable;
        $caja->Caj_SucursalOri = $this->comp_sucursal; 
        $caja->Caj_Id = 0; //Es el que toma en los clientes
        $caja->save();

      } //fin  Recorro Formas de Pago

      if ($this->tipo_de_factura != 'Z') {
          $this->ret =  $this->crea_comprobante_afip();
          if($this->ret == "") {
             // todo correctos ya se gravo en Afip y retorno Nro Fact, CAE , y fecha Venc CAE
             $this->auxEstadoFactua = "E"; // Emitida          
          }else{
              displaylog('Error: GeneraFAC EN AFIP, NO se genero:' . $this->ret . ' Comp:'. $this->comp_id  . ' Error AuxAfip:' . $this->auxErrorAfip );
              $this->auxErrorAfip = $this->ret;
              $this->auxEstadoFactua = "K"; // Error Afip  Registro y dejo marcado
              $this->numero_de_factura = $this->obtengocorrelativo("K");
              $this->CAEFchVto = fechahoy(); 
              $this->ret = ""; // Limpio el error del comprobante, dejo solo el de AFIP 
          }
          $this->graba_factura();
      }  
    } // Fin de Graba los Pagos

  } catch (\Exception $e) {
      DB::rollBack();
      $this->ret =  $e->getMessage() ;

      return  $this->ret;
  }


    DB::commit();
    return  $this->ret;

                				
  } // Fin Metodo nuevo
	
  // -----------------------------------------
  //  Funciones Internas
  // -----------------------------------------

  private function graba_MCaja(){
    // Esta tabla tiene el mismo registro que Caja pero con el monto en moneda original, y el codigo de origen segun la forma de pago
    $esUnSoloPago = count($this->linea_pago) == 1;

    // Recorro todas las lineas de Detalle
    foreach ($this->linea_detalle as  $items) {
        // Saltear items especiales VAR codigo 99
        if ($items["familia"] == "VAR" and $items['codigo'] == "99") {
            continue;
        }

        $ocaja = new mcaja();
        $ocaja->MCaj_FecMov = fechahoy();

        if ($items["familia"] == "VAR") {
            $ocaja->MCaj_Codigo = sprintf("%'.04d\n", $items['codigo']);
        } else {
            $consulta = "SELECT Flia_Ctacon FROM familias WHERE Flia_Id = ?";
            $datos = DB::select($consulta, [$items["familia"]]);
            $ocaja->MCaj_Codigo = sprintf("%'.04d\n", $datos[0]->Flia_Ctacon);
        }

        if ($esUnSoloPago) {
            // Una sola forma de pago: en la moneda original
            $ocaja->MCaj_Moneda = $this->linea_pago[0]["moneda"];
            $ocaja->MCaj_Monto = ($items['precio_unitario_tomado'] * $items['cantidad']) / $this->linea_pago[0]["cotizacion"];

            switch ($ocaja->MCaj_Moneda) {
                case 'C':
                    $ocaja->MCaj_CtaOri = "06";
                    $ocaja->MCaj_Moneda = "P";
                    break;
                case 'H':
                    $ocaja->MCaj_CtaOri = "06";
                    $ocaja->MCaj_Moneda = "R";
                    break;
                default:
                    $ocaja->MCaj_CtaOri = "01";
            }
        } else {
            // Multiples formas de pago: siempre en pesos
            $ocaja->MCaj_Moneda = "P";
            $ocaja->MCaj_Monto = $items['precio_unitario_tomado'] * $items['cantidad'];
            $ocaja->MCaj_CtaOri = "01";
        }

        $ocaja->MCaj_CtaDes = "";
        $ocaja->MDes_IdFac = $this->comp_id;
        $ocaja->MDes_TipoOT = $this->comp_tipoot;
        $ocaja->MDes_Descripcion = $items['detalle'];

        $ocaja->MCaj_Origen = "15";
        $ocaja->MCaj_FecAlta = fechahorahoy();
        $ocaja->MCaj_IdWEB = 0;
        $ocaja->MCaj_Id = 0;
        $ocaja->MCaj_UsuAlta = $this->comp_responsable;
        $ocaja->MCaj_SucursalOrig = $this->comp_sucursal;
        $ocaja->MCaj_SucursalDes = $this->comp_sucursal;
        $ocaja->MCaj_Sucursal = $this->comp_sucursal;

        $ocaja->save();
    }

    // Si hay multiples formas de pago, crear transferencias para las que no son pesos
    if (!$esUnSoloPago) {
        foreach ($this->linea_pago as $pago) {
            $monedaPago = $pago["moneda"];

            if ($monedaPago === "C") {
                $monedaReal = "P";
                $ctaOri = "06";
            } elseif ($monedaPago === "H") {
                $monedaReal = "R";
                $ctaOri = "06";
            } else {
                $monedaReal = $monedaPago;
                $ctaOri = "01";
            }

            if ($monedaReal !== "P") {
                $oCajaTransf = new mcaja();
                $oCajaTransf->MCaj_Codigo = "0900";
                $oCajaTransf->MCaj_FecMov = fechahoy();
                $oCajaTransf->MCaj_Moneda = $monedaReal;
                $oCajaTransf->MCaj_Monto = $pago["montomonori"] ?? ($pago["monto"] / ($pago["cotizacion"] ?? 1));
                $oCajaTransf->MCaj_CtaOri = $ctaOri;
                $oCajaTransf->MCaj_CtaDes = "01";
                $oCajaTransf->MCaj_MonedaDes = "P";
                $oCajaTransf->MCaj_MontoDes = $pago["monto"];
                $oCajaTransf->MCaj_SucursalDes = $this->comp_sucursal;
                $oCajaTransf->MDes_IdFac = $this->comp_id;
                $oCajaTransf->MDes_TipoOT = $this->comp_tipoot;
                $oCajaTransf->MDes_Descripcion = "Transf " . $monedaReal . " a P";
                $oCajaTransf->MCaj_Origen = "15";
                $oCajaTransf->MCaj_FecAlta = fechahorahoy();
                $oCajaTransf->MCaj_IdWEB = 0;
                $oCajaTransf->MCaj_Id = 0;
                $oCajaTransf->MCaj_UsuAlta = $this->comp_responsable;
                $oCajaTransf->MCaj_SucursalOrig = $this->comp_sucursal;
                $oCajaTransf->MCaj_Sucursal = $this->comp_sucursal;
                $oCajaTransf->save();
            }
        }
    }
  }
  
  private function graba_factura(){

      // Grabo tabla de Facturas
      $factura = New factura;
      $factura->Fac_IdOt = $this->comp_id;
      // Si ingreso un Cliente, tomo los datos
      $factura->Fac_RazonSocial = $this->cliente->Cli_ApeNom;
      $factura->Fac_CUIT = $this->cliente->Cli_Documento;
      $factura->Fac_CodRespIVA = $this->cliente->Cli_CodRespIVA;

      $factura->Fac_Fecha = $this->fecha_factura;

      $factura->Fac_Comprobante = $this->tipo_de_comprobante; // A B ...
      if( $this->auxEstadoFactua == "K") {
        $factura->Fac_NroPuntoVta = "9999";
        $this->punto_de_venta =  $factura->Fac_NroPuntoVta; //Para que quede bien
      }else{
        $factura->Fac_NroPuntoVta =  $this->punto_de_venta ;
      }
      $factura->Fac_NroFactura = $this->numero_de_factura ;

      $factura->Fac_Subtot = numdec($this->importe_gravado,2);  // iva 21
      $factura->Fac_Subtot1 = 0;  // iva 10,

      $factura->Fac_IVAInscrip = numdec($this->importe_iva,2);
      $factura->Fac_IVAInscrip1 = 0;
      $factura->Fac_SubtotIVA = numdec($this->importe_iva +  $factura->Fac_IVAInscrip1 ,2); 

      $factura->Fac_Total = numdec($this->comp_monto,2);

      $factura->Fac_Estado = $this->auxEstadoFactua; // Emitida  o Error Afip

      $factura->Fac_UsuUltMan = $this->comp_responsable;
      $factura->Fac_TipoOT = $this->comp_tipoot ;

      $factura->Fac_CAE = $this->CAE;
      $factura->Fac_VencimientoCAE =  $this->CAEFchVto;
      $factura->Fac_Sucursal =  $this->comp_sucursal; 
      $factura->save();

  } // fin graba_factura

  private function crea_comprobante_afip(){

    // Se conecta con Sistema de AFIP y genera el Comprobante
    $comp_afip = new ws_afip();
    $comp_afip->tipo_de_factura = $this->tipo_de_factura; // Tipo Factura Numerico segun AFIP 
    $comp_afip->punto_de_venta = $this->punto_de_venta;
    $comp_afip->cliente_cod_documento = $this->cliente->Aux_CodDocumento ; // cod numerico para afip
    $comp_afip->cliente_documento = (float) $this->cliente->Cli_Documento; // Segun corresponda porel tipo
    $comp_afip->fecha_factura = $this->comp_fecmov;
    $comp_afip->importe_gravado = $this->importe_gravado;
    $comp_afip->importe_iva = $this->importe_iva;
    $comp_afip->punto_facturaOriginal = $this->punto_facturaOriginal;   //Se completa solo en Anulaciones
    $comp_afip->facturaOriginal = $this->facturaOriginal;        //Se completa solo en Anulaciones
    $ret  = $comp_afip->nuevo_comprobante();
    if($ret == "") {
      $this->CAE = $comp_afip->CAE; //CAE asignado a la Factura
      $this->CAEFchVto = $comp_afip->CAEFchVto; //Fecha de vencimiento del CAE
      $this->numero_de_factura = $comp_afip->numero_de_factura;
      return ""; // Todo Ok
    }else{
      $this->auxErrorAfip = $ret;
    }  
    return $ret;

  }  // fin crea_comprobante_afip


	private function insert_comprobante($tipocomprobante){

    // Para los casos de VtaDirecta y Presupuestos

    $retorno = ""; //Todo Ok

     try {
          if ($this->comp_sucursal == '') $this->comp_sucursal = env('SUCURSAL_LOCAL') ; // Sucursal + 099 Web  
          if ($this->comp_idanula == '') $this->comp_idanula = 0;
          if ($this->comp_idfactura == '') $this->comp_idfactura = 0;
          if ($this->comp_idcli == '') $this->comp_idcli = 0;

          $ocomp = new comprobantebd();
          $ocomp->Comp_TipoOT = $this->comp_tipoot;
          $ocomp->Comp_Id = 0; // Es para los casos generados en Sucursales
          $ocomp->Comp_FecMov = $this->comp_fecmov;
          $ocomp->Comp_Sucursal =  $this->comp_sucursal;
          $ocomp->Comp_Monto = $this->comp_monto;
          $ocomp->Comp_Responsable = $this->comp_responsable; 
          $ocomp->Comp_IdAnula = $this->comp_idanula;
          $ocomp->Comp_Estado = $this->comp_estado;
          $ocomp->Comp_IdCli = $this->comp_idcli;
          $ocomp->Comp_IdFactura = $this->comp_idfactura; //VER todavia no lo tenemos
          $ocomp->Comp_observaciones = $this->comp_observaciones;
          $ocomp->save();
          $this->comp_id = $ocomp->Comp_idWEB; // Retorna el Comprobante Generado 


    } catch (\Exception $e) {
      if ( $e->getCode() == 23000 )  {
           // Duplicado
            $retorno = "DUPLICADO";
      }else{
            $retorno =  $e->getMessage() ;
      }  

    } 
    return $retorno;	

  } // End insert_comprobante

  private function lee_tabla_comprobante() {

    if($this->comp_tipoot <> "FC" ) { //Si se genero en WEB busco por id web  FC=Vta Directa en Suc
      $consulta = "SELECT * FROM comprobantes  WHERE  Comp_idWEB=?" ;
      $datos = DB::select($consulta,[ $this->comp_id] );
    }else{
      $consulta = "SELECT * FROM comprobantes  WHERE  Comp_Sucursal=? and  Comp_TipoOT=? and Comp_Id=?" ;
      $datos = DB::select($consulta,[ $this->comp_sucursal, $this->comp_tipoot , $this->comp_id] );
    }  
    if($datos ) {
      $this->comp_tipoot =  $datos[0]->Comp_TipoOT;
      $this->comp_fecmov =  $datos[0]->Comp_FecMov;
      $this->comp_sucursal =  $datos[0]->Comp_Sucursal;
      $this->comp_responsable =  $datos[0]->Comp_Responsable; 
      $this->comp_monto =  $datos[0]->Comp_Monto; 
      $this->comp_estado =  $datos[0]->Comp_Estado; 
      $this->comp_idfactura =  $datos[0]->Comp_IdFactura; 
      $this->comp_idcli =  $datos[0]->Comp_IdCli; 
      $this->comp_observaciones =  $datos[0]->Comp_observaciones;

    }else{

      return 'Error: Al Leer Tabla Comprobantes No encontro <br>' . $this->comp_sucursal ." " . $this->comp_tipoot . " " . $this->comp_id ;
    }
 
    return "";

  }

private function ConvTipoCompNro_Letra( $tipoNro){
 
    // Retorna Cod Afa Segun Afip
    return match ($tipoNro) {
        1 => 'A',
        3 => 'R',
        6 => 'B',
        8 => 'S',
        11 => 'C',
        default => '',
    };
}

private function ConvTipoCompNro_Letra_PDF( $tipoNro){

  return match ($tipoNro) {
      1, 3 => 'A',
      6, 8 => 'B',
      11 => 'C',
      default => '',
  };
}

private function ConvTipoCompLetra_Nro( $tipoletra){
 
    // Retorna Cod Numerico Segun Afip
    return match ($tipoletra) {
        'A' => 1,
        'B' => 6,
        'C' => 11,
        'R' => 3,
        'S' => 8,
        default => 0,
    };
}

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
    return match ($tipoletra) {
        '' => 99,
        'CUIT' => 80,
        'DNI' => 96,
        'CUIL' => 86,
        'LE' => 89,
        'LC' => 90,
        default => 0,
    };
}

private function descripcionTipoResponsable( $tipoResp){
 
    // Retorna Cod Numerico Segun Afip
    return match ($tipoResp) {
        'CF' => 'Consumidor Final',
        'RI' => 'Responsable Inscripto',
        'MO' => 'Monotributo',
        'EX' => 'IVA Sujeto Exento',
        default => $tipoResp,
    };
}

private function descripcionTipoComprobante( $tipoletra){
 
    // Retorna Descripcion para Imprimir del Tipo de Comprobante
    if ( $tipoletra == "A" or $tipoletra == "B" or $tipoletra == "C"   ) {
        return "Factura" ;  
    }
    if ( $tipoletra == "R" or $tipoletra == "S"  ) {
        return "NOTA CRÉDITO" ;  
    }
    if ( $tipoletra == "P"   ) {
      return "PRESUPUESTO" ;  
    }
    return  $tipoletra ; // "Falta Ver" ;  
                     
}

private function cargaAlicuotasIva() {
    // Recorro todas las lineas de Detalle 
    // Y acumular por tipo de Iva
    //FALTA  por ahora todo va al 21
    // Recorro todas las lineas de Detalle para calcular los totales
    $totalComp = 0;
    $auxlinea = 0;
    $i=0;

    foreach ($this->linea_detalle as  $items) {
        $totalComp = $totalComp + ( $items['precio_unitario_tomado'] *  $items['cantidad'] );
        $i++;
    } // Fin Calculos

    //FALTA PARA DISCRIMINAR CONCEPTOS DIFERENTES IVAS
    $this->comp_monto= $totalComp;
    $this->importe_iva =  round($totalComp - ( $totalComp / 1.21)  , 2) ;
    $this->importe_gravado = $totalComp  - $this->importe_iva ;

    $this->linea_alicuotas[] = [
        "tipoIva" => 5 , // tipo numerico 5, ..
        "descriIva" => 21,  // Descripcion 21 ,10.05 ...
        "importe" =>  $this->importe_iva,
        "baseImp" => $this->importe_gravado
    ];

}

private function CompletoDatosCliente(){

    if($this->comp_idcli > 0 ) {
      //  Se arreglo que para en todos los casos buscque por id
      $this->cliente = Cliente::find_id($this->comp_idcli); 
      if($this->cliente ) {     
          $this->cliente->Aux_CodDocumento = $this->ConvTipoDocumento_Nro($this->cliente->Cli_CodDocumento);
      }else{
        $this->cliente = new Cliente;      
        $this->cliente->Cli_ApeNom = "Error al buscar en Bd IdSuc";
        $this->cliente->Aux_CodDocumento = "Error";
        $this->ret = "No se Encontro Cliente en Bd  Id Suc:"  .$this->comp_idcli;      
      }    
    }else{
      $this->cliente = new Cliente;      
      $this->cliente->Cli_ApeNom = "Consumidor Final";
      $this->cliente->Cli_Documento = "27000000006";
      $this->cliente->Cli_CodRespIVA = "CF";
      $this->cliente->Cli_Calle = "";
      $this->cliente->Cli_CodDocumento = "CUIL";
      $this->cliente->Aux_CodDocumento = 99; // cod numerico para afip Sin identificar/venta global diaria
    }        
}

	private function obtengocorrelativo($tipocomprobante) {

    $tipocomprobante = match ($tipocomprobante) {
        'CA' => 'E',
        'FC' => 'D',
        'RE' => 'I',
        'PR' => 'H',
        'K' => 'K',
        default => 'H',
    };
    if ($tipocomprobante === 'D') {
        $this->comp_estado = 'P';
    }
      // Lo genero y gravo 
      $nvoid =  correlativo::leo_proximo($tipocomprobante);
      $ret =  correlativo::gravo_correlativo($tipocomprobante,$nvoid);
      if ($ret == -1   ) {
          $this->ret = "Error: Al grabar Correlativo Comprobante"  ;
          return 0;
      }
      return $nvoid;
		
  } //    End Function obtengocorrelativo

	public function __set($var, $valor) {
		if (property_exists(__CLASS__, $var)) {
			// le saca los caracteres malos (escapar caracteres depende de la Bd)
			$this->$var = limpiacaracteres($valor);
		} else {
			echo "No existe el atributo $var.";
		}
	}
	
	public function __get($var) {
		if (property_exists(__CLASS__, $var)) {
			return $this->$var;
		}
		return NULL;
	}
	
} // Fin de la clase 
?>