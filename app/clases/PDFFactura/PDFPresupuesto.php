<?php namespace App\clases\PDFFactura;

//  Para incormporarlo al proyecto: 
//  composer require spipu/html2pdf

use Spipu\Html2Pdf\Html2Pdf;

/**
 * Clase para generar Comprobantes PDF similares a los de AFIP
 */

class PDFPresupuesto extends HTML2PDF {

    private $config = array();
    private $voucher = null;
    private $finished = false; //Determina si es la ultima pagina
    private $html = "";
    private $lang = array();
    const LANG_EN = 2;

    function __construct($voucher, $config) {
        parent::__construct('P', 'A4', 'es');
        $this->config = $config;
        $vconfig = array();
        //$vconfig["footer"] = false;
        //$vconfig["total_line"] = false;
        //$vconfig["header"] = false;
        //$vconfig["receiver"] = false;
        //$vconfig["footer"] = false;
        $this->config["VOUCHER_CONFIG"] = $vconfig;
        $this->voucher = $voucher;
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

    private function lang($key) {
        if (array_key_exists($key,$this->lang)) {
            return $this->lang[$key];
        } else {
            return $key;
        }
    }

    /**
     * Genera la cabecera del comprobante
     * @param String $logo_path - Ubicación de la imágen del logo
     * @param String $title - Ej: PRESUPUESTO
     */

    function addVoucherInformation($logo_path, $title) {
        if ($this->show_element("header")) {
            if ($title != "") {
                $this->html .= "<div class='border-div'>";
                $this->html .= "    <h3 class='center-text'>" . $title . "</h3>";
                $this->html .= "</div>";
            }

            $this->html .= "<div class='border-div'>";
            $type = $this->lang($this->voucher["TipoComprobante"]);
            $letter = "X";
            $number = "<strong>" . $this->lang("Punto de venta") . ": " . str_pad($this->voucher["numeroPuntoVenta"], 4, "0", STR_PAD_LEFT) . "   " . $this->lang("Comp. Nro") . ": " . str_pad($this->voucher["numeroComprobante"], 8, "0", STR_PAD_LEFT) . "</strong>";
            $tmp = \DateTime::createFromFormat('Ymd',$this->voucher["fechaComprobante"]);
            $date = "<strong>" . $this->lang("Fecha de emisi&oacute;n") . ": " . date_format($tmp, $this->lang('d/m/Y')) . "</strong>" ;
            $this->html .= "<table class='responsive-table table-header'>";
            $this->html .= "<tr><td style='width: 3%;'></td>";
            $this->html .= "  <td style='width: 27%;'>";
              if (file_exists($logo_path)) {
                $this->html .= "<img class='logo' src='" . $logo_path . "' alt='logo'>";
              }
              $this->html .= "<br> <br><strong> Doc no válido como factura</strong>";
            $this->html .= " </td>";
           /* $this->html .= "<td>";
            $id_type = "Doc no válido como factura";
            $this->html .= "        <p class='type'> $id_type</p> ";
  
            $this->html .= "</td>";
            */
            $this->html .= " <td class='right-text' style='width: 69%;'>";
            $this->html .= "    <span class='type_voucher header_margin'>$type</span><br>";
            $this->html .= "    <span class='header_margin'> </span><br>";
            $this->html .= "    <span class='header_margin'>$number</span><br>";
            $this->html .= "    <span class='header_margin'>$date</span>";
            $this->html .= " </td>";
            $this->html .= "</tr>";
            $this->html .= "</table>";

            $this->html .= "<table class='responsive-table table-header'>";
            $this->html .= "<tr>";
            $this->html .= "<td style='width:50%;'> Teléfono:" .  strtoupper($this->config["TRADE_TELEFONO"]) . "</td>";
            $this->html .= "<td class='right-text' style='width:49%;'>" . $this->lang("CUIT") . ": " . $this->config["TRADE_CUIT"] . "</td>";
            $this->html .= "</tr>";
            $this->html .= "<tr>";
            $this->html .= "<td style='width:50%;'>" . $this->lang("Domicilio") . ": " . $this->config["TRADE_ADDRESS"] . "</td>";
            $this->html .= "<td class='right-text' style='width:49%;'>" . $this->lang("Ingresos Brutos") . ": " . $this->config["TRADE_CUIT"] . "</td>";
            $this->html .= "</tr>";
            $this->html .= "<tr>";
            $this->html .= "<td style='width:50%;'>WhatsApp: " . strtoupper($this->config["TRADE_WHATSAPP"]) . "</td>";
            $tmp = \DateTime::createFromFormat('d/m/Y',$this->config["TRADE_INIT_ACTIVITY"]);
            $this->html .= "<td class='right-text' style='width:49%;'>" . $this->lang("Fecha de inicio de actividades") . ": " . date_format($tmp, $this->lang('d/m/Y')) . "</td>";
            $this->html .= "</tr>";
            $this->html .= "</table>";
            $this->html .= "</div>";
        }
    }

    /**
     * Genera la información del receptor (cliente)
     */
    function addReceiverInformation() {
        if ($this->show_element("receiver")) {
            $this->html .= "<div class='border-div'>";
            $this->html .= "<table class='responsive-table table-header'>";
            $this->html .= "<tr>";
            $text = $this->lang("Apellido y Nombre / Raz&oacute;n Social") . ": " . strtoupper($this->voucher["nombreCliente"]);
            $this->html .= "<td style='width:50%;'>" . $text . "</td>";
            $this->html .= "</tr>";
            $this->html .= "<tr>";
            $text = $this->lang($this->voucher["TipoDocumento"]) . ": " . $this->voucher["numeroDocumento"];
            $this->html .= "<td style='width:50%;'>" . $text . "</td>";
           // $this->html .= "<td class='right-text' style='width:49%;'>" . $text . "</td>";
            $this->html .= "</tr>";
            $this->html .= "<tr>";
              $text = $this->lang("Domicilio") . ": " . $this->voucher["domicilioCliente"];
              $this->html .= "<td style='width:50%;'>" . $text . "</td>";
            $this->html .= "</tr>";

            
            $this->html .= "<tr>";
            $text = $this->lang("Condici&oacute;n frente al IVA") . ": " . $this->lang($this->voucher["tipoResponsable"]);
            $this->html .= "<td style='width:50%;'>" . $text . "</td>";
           // $text = $this->lang("Domicilio") . ": " . $this->voucher["domicilioCliente"];
           // $this->html .= "<td class='right-text' style='width:49%;'>" . $text . "</td>";
            $this->html .= "</tr>";
            $this->html .= "</table>";
            $this->html .= "</div>";
        }
    }

    /**
     * Genera la tabla con los articulos del comprobante
     *
     * @author NeoComplexx Group S.A.
     */
    function fill() {
        $this->html .= "<table class='responsive-table table-article'>";
        if (strtoupper($this->voucher["letra"]) === 'A') {
            $this->fill_A();
        } else {
            $this->fill_B();
        }
        $this->html .= "</table>";
        $this->finished = true;
    }

    /**
     * Imprime el detalle para comprobantes tipo A
     * 
     * @author NeoComplexx Group S.A.
     */
    function fill_A() {
        $this->html .= "<tr>";
        $this->html .= "<th class='center-text' style='width=10%;'>" . $this->lang("C&oacute;digo") . "</th>";
        $this->html .= "<th style='width=26%;'>" . $this->lang("Producto / Servicio") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("Cantidad") . "</th>";
      //  $this->html .= "<th style='width=10%;'>" . $this->lang("U. Medida") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("Precio unit.") . "</th>";
        $this->html .= "<th class='right-text' style='width=8%;'>" . $this->lang("% Bonif") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("Imp. Bonif.") . "</th>";
        $this->html .= "<th class='right-text' style='width=6%;'>" . $this->lang("IVA") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("Subtotal") . "</th>";
        $this->html .= "</tr>";
        foreach ($this->voucher["items"] as $item) {
            $this->html .= "<tr>";
            if (isset($this->config["TYPE_CODE"]) && $this->config["TYPE_CODE"] == 'scanner') {
                $this->html .= "<td class='center-text' style='width=10%;'>" . $item["scanner"] . "</td>";
            } else {
                $this->html .= "<td class='center-text' style='width=10%;'>" . $item["codigo"] . "</td>";
            }
            $this->html .= "<td style='width=26%;'>" . $item["descripcion"] . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["cantidad"], 3) . "</td>";
            $this->html .= "<td style='width=10%;'>" . $item["UnidadMedida"] . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["precioUnitario"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=8%;'>" . number_format($item["porcBonif"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["impBonif"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=6%;'>" . number_format($item["Alic"], 0) . "%</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["importeItem"], 2) . "</td>";
            $this->html .= "</tr>";
        }
    }

    /**
     * Imprime el detalle para comprobantes tipo B
     * 
     * @author NeoComplexx Group S.A.
     */
    function fill_B() {
        $this->html .= "<tr>";
        $this->html .= "<th class='center-text' style='width=10%;'>" . $this->lang("C&oacute;digo") . "</th>";
        $this->html .= "<th style='width=35%;'>" . $this->lang("Producto / Servicio") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("Cantidad") . "</th>";
     //   $this->html .= "<th style='width=10%;'>" . $this->lang("U. Medida") . "</th>";
        $this->html .= "<th class='right-text' style='width=12%;'>" . $this->lang("Precio unit.") . "</th>";
        $this->html .= "<th class='right-text' style='width=10%;'>" . $this->lang("% Bonif") . "</th>";
        $this->html .= "<th class='right-text' style='width=11%;'>" . $this->lang("Imp. Bonif.") . "</th>";
        $this->html .= "<th class='right-text' style='width=12%;'>" . $this->lang("Subtotal") . "</th>";
        $this->html .= "</tr>";
        foreach ($this->voucher["items"] as $item) {
            $this->html .= "<tr>";
            $this->html .= "<td class='center-text' style='width=10%;'>" . $item["codigo"] . "</td>";
            $this->html .= "<td style='width=35%;'>" . $item["descripcion"] . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["cantidad"], 3) . "</td>";
            $this->html .= "<td class='right-text' style='width=12%;'>" . number_format($item["precioUnitario"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item["porcBonif"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=11%;'>" . number_format($item["impBonif"], 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=12%;'>" . number_format($item["importeItem"], 2) . "</td>";
            $this->html .= "</tr>";
        }
    }

    /**
     * Imprime la linea de totales
     *
     * @author NeoComplexx Group S.A.
     */
    function total_line() {
        if ($this->show_element("total_line")) {
            $this->html .= "<div class='border-div'>";
            $this->total_line_B();
            $this->html .= "</div>";
        }
    }

    /**
     * Imprime la linea de totales para comprobantes con letra B
    */

    private function total_line_B() {
        $this->html .= '    <table class="responsive-table">';
        $this->html .= '        <tr>';
        $this->html .= '		<td class="right-text" style="width: 75%;">' . $this->lang("Subtotal") . ': '. $this->lang($this->voucher["codigoMoneda"]) .'</td>';
        $text = number_format((float) round($this->voucher["importeTotal"], 2), 2);
        $this->html .= '		<td class="right-text" style="width: 25%;">' . $text . '</td>';
        $this->html .= '        </tr>';
        $this->html .= '        <tr>';
        $this->html .= '		<td class="right-text" style="width: 75%;">' . $this->lang("Importe otros tributos") . ': '. $this->lang($this->voucher["codigoMoneda"]) .'</td>';
        $text = number_format((float) round($this->voucher["importeOtrosTributos"], 2), 2);
        $this->html .= '		<td class="right-text" style="width: 25%;">' . $text . '</td>';
        $this->html .= '        </tr>';
        $this->html .= '        <tr>';
        $this->html .= '		<td class="right-text" style="width: 75%;">' . $this->lang("Importe total") . ': '. $this->lang($this->voucher["codigoMoneda"]) .'</td>';
        $text = number_format((float) round($this->voucher["importeTotal"], 2), 2);
        $this->html .= '		<td class="right-text" style="width: 25%;">' . $text . '</td>';
        $this->html .= '        </tr>';
        $this->html .= '    </table>';
    }

    function extra_line() {
        $extra = $this->voucher["observacion"] ; 
        if ($extra != "") {
            $this->html .= "<div class='border-div'>";
            $this->html .= '    <table class="responsive-table">';
            $this->html .= "        <tr><td class='center-text'style='width: 100%;'> Observación:$extra</td></tr>";
            $this->html .= '    </table>';
            $this->html .= "</div>";
        }
    }

    /**
     * Imprime el pie de pagina
     */
    function footer() {
        $text_1 = " "; 
        $text_2 = "Vendedor:" . $this->voucher["vendedor"] ;
        $text_3 = "" ; // Si por configuracion tiene los dias de validez
        $validez = env('PRESUPUESTO_VALIDEZ');
        if ($validez > 0) {
            $date = \DateTime::createFromFormat('Ymd',time());
            $suma = "+" . $validez . " days";   // '+10 days'
            $text_3 = "El siguiente presupuesto tiene validez hasta:"  . date('d/m/Y', strtotime($suma, time())) ; // En el pie acontinuacion de Nro de Paginas
        }

        if ($this->show_element("footer")) {
            $this->html .= '<page_footer>';
            $this->total_line();
            $this->extra_line();
            $this->html .= '<table class="responsive-table page_footer">';
            $this->html .= '<tr>';
            $this->html .= "   <td class='center-text'style='width: 100%;'>Página 1/1</td>";
           /*
            $this->html .= '  <td class="left-text" style="width: 30%;">' .  " " . "</td>";
            $this->html .= '		<td  class="center-text" style="width: 40%;">';
            //ran$this->html .= '             ' . $this->lang("Pag.") . ' [[page_cu]]/[[page_nb]]';
            $this->html .=  $this->lang("Página") . ' 1/1';
            $this->html .= '		</td>';
            $this->html .= '		<td valign="bottom" class="right-text" style="width: 15%;"> ' . $text_1 . "</td>";
            $this->html .= '		<td valign="bottom" class="left-text" style="width: 15%;"> ' . $text_2 . "</td>";
            */
            $this->html .= '    </tr>';
            $this->html .= '    <tr>';
            $this->html .= '		<td class="left-text">' . $text_2 . "</td>";
            $this->html .= '    </tr>';
            $this->html .= '    <tr>';
            $this->html .= '		<td class="left-text">' . $text_3 . "</td>";
            $this->html .= '    </tr>';
            $this->html .= '    </table>';
            $this->html .= '</page_footer>';
        }
    }

    private function show_element($element) {
        if (array_key_exists("VOUCHER_CONFIG", $this->config) && array_key_exists($element, $this->config["VOUCHER_CONFIG"])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Genera un comprobante de AFIP con su correspondiente original/duplicado
     *
     * @param type $logo_path Ubicación de la imágen del logo
     * 
     * @author NeoComplexx Group S.A.
     */
    function emitirPDF($logo_path ) {
        $this->html .= "<page>";
//        $this->addVoucherInformation($logo_path, $this->lang("PRESUPUESTO"));
        $this->addVoucherInformation($logo_path , "" );
        $this->addReceiverInformation();
        $this->fill();
        $this->footer();
        $this->html .= "</page>";
        $this->WriteHTML($this->html);
        return;      
    }

}