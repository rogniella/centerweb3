<?php namespace app\clases\PDFFactura;

//  Para incormporarlo al proyecto: 
//  composer require spipu/html2pdf
//  Pagina Ofical: https://html2pdf.fr/es/home       https://github.com/spipu/html2pdf
//                 https://github.com/spipu/html2pdf/tree/master/doc

use Spipu\Html2Pdf\Html2Pdf;

class PDFOtQR extends HTML2PDF {

    private $config = array();
    private $dato = null;
    private $finished = false; //Determina si es la ultima pagina
    private $html = "";
    private $lang = array();
    private $cantidad = 0;
    private $importeTotal = 0;

    const LANG_EN = 2;


    function __construct($voucher) {

//        dd('1222');

        parent::__construct('P', 'A4', 'es');
     //   $this->config = $config;
    //    $vconfig = array();
        //$vconfig["footer"] = false;
        //$vconfig["total_line"] = false;
        //$vconfig["header"] = false;
        //$vconfig["receiver"] = false; 
        //$vconfig["footer"] = false;
     //   $this->config["VOUCHER_CONFIG"] = $vconfig;
        $this->dato = $voucher;
  //      dd($voucher);
        $this->finished = false;
        $cssfile = fopen(dirname(__FILE__) . "/voucher.css", "r");
        $css = fread($cssfile, filesize(dirname(__FILE__) . "/voucher.css"));
        fclose($cssfile);
        $this->html = "<style>" . $css . "</style>";

        if (array_key_exists("idiomaComprobante",$voucher) && $voucher["idiomaComprobante"] == $this::LANG_EN) {
            include(__DIR__.'/language/en.php');
        } else {
            include(__DIR__.'/language/es.php');
        }
        $this->lang = array_merge($this->lang, $lang);
    }

    /**
      Genera comprobante  
    */
    function emitirPDF($logo_path ) {

        $this->html .= "<page>";
        $this->addVoucherInformation($logo_path);
        $this->html .= "</page>";

        $this->WriteHTML($this->html);
        return;      
    }

  /**
     * Genera contenido del comprobante
     * @param String $logo_path - Ubicación de la imágen del logo
  */
  function addVoucherInformation($logo_path) {



        $qr = '<qrcode value="https://tienda.centerfotooptica.com.ar/consulta/?ot=' .  $this->dato["ot"] .'&cli='. $this->dato["nrocli"]  . '" ec="H" style="width: 32mm; background-color: white; color: black;border:none;"></qrcode>';

        //    $qr = '<qrcode value="centerfotooptica.com.ar" ec="H" style="width: 40mm; background-color: white; color: black;border:none;"></qrcode>';

//https://tienda.centerfotooptica.com.ar/consulta/?ot=76380&cli=14266

// http://localhost/gestion/public/ot/imprimir?ot=76380&tipoot=A

    $bloque = <<<EOF
    <table class='table-ot'>
        <tr>
            <td style="width:28%">
                <div style="text-align:center">
                 <img class='logo' src='$logo_path' alt='logo'>
                </div>
            </td>
            <td style="width:7%">
            </td>
            <td style="font-size:17px;background-color:white; width:70%; text-align:left">ORDEN DE TRABAJO {$this->dato["tipo"]} N° <b>{$this->dato["ot"]}</b> 
            </td>
        </tr>


    </table>

    <table class='table-ot'>

        <tr>
            <td style="width:35%">
                <div style="font-size:9.5px; line-height:10px;text-align:center">
                    {$this->dato["direccion"]}                   
                    <br>
                    Teléfono: {$this->dato["telefono"]}                    
                    <br style="font-size:15px">
                    <b>WhatsApp: {$this->dato["celu"]}</b> 
                </div>
                <br>
            <div style="font-size:15px; text-align:left">OT {$this->dato["tipo"]} N° <b>{$this->dato["ot"]}</b> 
            </div>

            </td>
            <td style="width:14%;text-align:left">
                <div style="font-family:Courier">
                    Recibido      
                    <br>
                    Prometido     
                    <br>
                    <br>
                    Cliente     
                    <br>
                    Tel.     
                </div>
            </td>
            <td style="width:51%">
                <div style="font-size:14px;font-family:Courier;text-align:left">
                    :   {$this->dato["fecha_recibida"]}
                    <br>
                    :   {$this->dato["fecha_prometida"]}
                    <br>
                    <br>
                    :   <b>{$this->dato["cliente"]} ({$this->dato["nrocli"]})</b>
                    <br>
                    :   <b>{$this->dato["cliente_telefono"]}</b>
                </div>
            </td>
        </tr>


    </table>
EOF;
    
    $cliente_corto = substr($this->dato["cliente"], 0, 12);

    $this->html .= $bloque;

    $bloque = <<<EOF
    <table class='table-ot'>
        <tr>        
            <td style="width:10%">
                <div style="font-family:Courier;text-align:left">
                    Cliente     
                </div>
            </td>
            <td style="width:25%">
                <div style="font-family:Courier;text-align:left">
                    :   <b>{$cliente_corto}</b>
                </div>
            </td>
            <td style="width:65%>
                <div style="font-size:14px;font-family:Courier;text-align:left;border-top:1px">
                    <b>LEJOS </b>  {$this->dato["lej_tipo"]}
                </div>
            </td>
        </tr>        
    </table>
EOF;
    $this->html .= $bloque;

    $bloque = <<<EOF
    <table class='table-ot'>
        <tr>        
            <td style="width:10%">
                <div style="font-family:Courier;text-align:left">
                    Recibido      
                    <br>
                    Prometido     
                </div>
            </td>
            <td style="width:25%">
                <div style="font-size:14px;font-family:Courier;text-align:left">
                    :   {$this->dato["fecha_recibida"]}
                    <br>
                    :   {$this->dato["fecha_prometida"]}
                </div>
            </td>
            <td style="width:45%>
                <div style="line-height: 140%;font-size:14px;font-family:Courier;">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$this->dato["lej_mat"]}
                    <br>
                    &nbsp;<b>{$this->dato["lej_OD"]}
                    <br>
                    &nbsp;{$this->dato["lej_OI"]}</b>
                </div>
            </td>
        </tr>        
    </table>
EOF;
    $this->html .= $bloque;

    $bloque = <<<EOF
    <table class='table-ot'>
        <tr>        
            <td style="width:10%">
                <div style="font-family:Courier;text-align:left">
                    Total         
                    <br>
                    Seña          
                    <br>
                    <br>
                    Saldo         
                </div>
            </td>
            <td style="width:2%">
                <div style="font-family:Courier;text-align:left">
                    :         
                    <br>
                    :          
                    <br>
                    <br>
                    :         
                </div>
            </td>
            <td style="width:15%">
                <div style="font-family:Courier;text-align:right">
                    {$this->dato["total"]}         
                    <br>
                    {$this->dato["senia"]}          
                    <br>
                    -----------       
                    <br>
                    {$this->dato["saldo"]}         
                </div>
            </td>
            <td style="width:8%">
            </td>

            <td style="width:45%>
                <div style="font-size:14px;font-family:Courier;text-align:left;border-top:1px">
                    <b>CERCA {$this->dato["adision"]} </b>  {$this->dato["cer_tipo"]}
                </div>
                <div style="line-height:140%;font-size:14px;font-family:Courier;text-align:left">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$this->dato["cer_mat"]}
                    <br>
                    &nbsp;<b>{$this->dato["cer_OD"]}
                    <br>
                    &nbsp;{$this->dato["cer_OI"]}</b>
                </div>
            </td>
              
        </tr>

    </table>
EOF;
    $this->html .= $bloque;

    $bloque = <<<EOF
    <table class='table-ot'>

        <tr>
            <td style="width:35%;font-size:11px; line-height:10px;">
                <br>
                ** La casa no se responsabiliza<br>
                      por su armazón en uso **
                <br>
                * NO VALIDO COMO FACTURA *<br>
                <br>
                <b>En la fecha indicada los trabajos<br>
                se retiran DESPUES DE <u>18:30 hs.</u> **</b>
                <br>
            </td>
            <td style="width:65% ;border-top:1px">
                <div style="font-size:14px;font-family:Courier;text-align:left">
                    <b>{$this->dato["tipolente"]}</b>  {$this->dato["claselente"]}
                </div>
                <div style="font-family:Courier;text-align:left">
                    <br>
                    Distacia IP:   {$this->dato["distancia"]}
                    <br>
                    OBSERV:  <b>{$this->dato["observ"]}</b>
                    <br>
                    Procesos  :  <b>{$this->dato["procesos"]}</b>
                </div>

            </td>
        </tr>
    </table>
EOF;
    $this->html .= $bloque;


    $total_form = str_replace (' ', '&nbsp;', str_pad( $this->dato["total"] ,12," ",STR_PAD_LEFT));
    $senia_form = str_replace (' ', '&nbsp;', str_pad( $this->dato["senia"] ,12," ",STR_PAD_LEFT));
    $saldo_form = str_replace (' ', '&nbsp;', str_pad( $this->dato["saldo"] ,12," ",STR_PAD_LEFT));
    $osocial_form = str_replace (' ', '&nbsp;', str_pad( $this->dato["osocial"] ,12," ",STR_PAD_LEFT));
    $descuento_form = str_replace (' ', '&nbsp;', str_pad( $this->dato["descuento"] ,12," ",STR_PAD_LEFT));
    $bloque = <<<EOF

    <table class='table-ot'>
        <tr>
            <td style="background-color:white; width:28%">                
                <div style="text-align:center">
                   $qr                
                </div>
                <div style="font-size:10px;text-align:center">
                  ** QR consulta estado trabajo **
                </div>
            </td>
            <td style="width:7%">
            </td>
            <td style="width:65%">
                <div style="font-size:14px;font-family:Courier;text-align:left">
                    Dr:  {$this->dato["medico"]}
                    <br>
                    Obra Social: <b>{$this->dato["obrasocial"]}</b>
                    <br>
                    <br>
                    Total     :  {$total_form} $  
                    <br>
                    Seña   &nbsp;: {$senia_form} $    &nbsp;&nbsp;&nbsp;&nbsp;O.Social  :{$osocial_form} $
                    <br>
                    Saldo   :  {$saldo_form} $       &nbsp;&nbsp;&nbsp;&nbsp;Descuento:{$descuento_form} $
                    <br>
                    <br>
                    OT  : {$this->dato["ot"]}                                      Vendedor: {$this->dato["vendedor"]}
                 </div>
            </td>

        </tr>



    </table>

EOF;
    $this->html .= $bloque;



  }

    private function lang($key) {
        if (array_key_exists($key,$this->lang)) {
            return $this->lang[$key];
        } else {
            return $key;
        }
    }

} // Fin clase