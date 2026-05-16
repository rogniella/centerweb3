<?php namespace app\clases\PDFFactura;

//  Para incormporarlo al proyecto: 
//  composer require spipu/html2pdf

use Spipu\Html2Pdf\Html2Pdf;

/**
 * Clase para generar Comprobantes PDF similares a los de AFIP
 *
 * @author NeoComplexx Group S.A.
 */

class PDFRemito extends HTML2PDF {

    private $config = array();
    private $voucher = null;
    private $finished = false; //Determina si es la ultima pagina
    private $html = "";
    private $lang = array();
    private $cantidad = 0;
    private $importeTotal = 0;

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
     * @param String $title - Ej: ORIGINAL/DUPLICADO
     *
     * @author NeoComplexx Group S.A.
     */
    function addVoucherInformation($logo_path, $title) {
        if ($this->show_element("header")) {
            if ($title != "") {
                $this->html .= "<div class='border-div'>";
                $this->html .= "    <h3 class='center-text'>" . $this->voucher["titulo"] . "</h3>";
                $this->html .= "</div>";
                $this->html .= "<div class='border-div'>";
            }

            $type = "";  // $this->lang($this->voucher["TipoComprobante"]);
            $letter = "R";
            $number = "<strong>" .  $this->lang("Comp. Nro") . ": " . str_pad($this->voucher["idlote"], 8, "0", STR_PAD_LEFT) . "</strong>";
            $tmp = \DateTime::createFromFormat('Ymd',$this->voucher["fecha"]);
            $date = $this->lang("Fecha de emisi&oacute;n") . ": " . date_format($tmp, $this->lang('d/m/Y'));

            $this->html .= "    <div class='letter'>";
            $this->html .= "        <p class='title'>$letter  </p> ";

            $id_type = ""; // $this->voucher["codigoTipoComprobante"];
            $this->html .= "        <p class='type'>Cod. $id_type</p> ";
            $this->html .= "    </div>";

            $this->html .= "<table class='responsive-table table-header'>";
            $this->html .= "<tr><td style='width: 3%;'></td>";
            $this->html .= "<td style='width: 27%;'>";
          // dd($logo_path);
            if (file_exists($logo_path)) {
                $this->html .= "<img class='logo' src='" . $logo_path . "' alt='logo'>";
            //    dd($logo_path);
            }
            $this->html .= "</td>";
            $this->html .= "<td class='right-text' style='width: 69%;'>";
            $this->html .= "    <span class='type_voucher header_margin'>$type</span><br>";
            $this->html .= "    <span class='header_margin'>$number</span><br>";
            $this->html .= "    <span class='header_margin'>$date</span>";
            $this->html .= "</td>";
            $this->html .= "</tr>";
            $this->html .= "</table>";

            $this->html .= "</div>";
        }
    }

    /**
     * Genera la información del receptor (cliente)
     *
     * @author: NeoComplexx Group S.A.
     */
    function addReceiverInformation() {
        if ($this->show_element("receiver")) {
            $this->html .= "<div class='border-div'>";
            $this->html .= "<table class='responsive-table table-header'>";
            $this->html .= "<tr>";
            $text =  strtoupper($this->voucher["Origen"]);
            $this->html .= "<td style='width:50%;'>" . $text . "</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $text = "Sucursal de Destino : " . strtoupper($this->voucher["sucursalDestinoDescri"]);
            $this->html .= "<td style='width:50%;'>" . $text . "</td>";
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

            $this->fill_A();
 
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
        $this->html .= "<th style='width=25%;'>" . $this->lang("Producto / Servicio") . "</th>";
        $this->html .= "<th style='width=9%;'>Categoria</th>";
        $this->html .= "<th class='right-text' style='width=7%;'>Cantidad</th>";
        $this->html .= "<th class='right-text' style='width=9%;'>Costo</th>";
        $this->html .= "<th class='right-text' style='width=9%;'>Precio</th>";
        $this->html .= "<th class='right-text' style='width=9%;'>Precio2</th>";
        $this->html .= "</tr>";
        foreach ($this->voucher["productos"] as $item) {
            $this->html .= "<tr>";
            $this->html .= "<td class='center-text' style='width=10%;'>" . $item->MLot_Familia . " " . $item->MLot_IdProd . "</td>";
            
            $this->html .= "<td style='width=26%;'>" .  $item->Prod_Descripcion . "</td>";
            $this->html .= "<td style='width=26%;'>" .  $item->Prod_Categoria . "</td>";

            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item->MLot_Cantidad, 3) . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item->MLot_Precio, 2) . "</td>";

            $this->importeTotal = $this->importeTotal  +  ( $item->MLot_Cantidad *  $item->MLot_Precio);
            $this->cantidad = $this->cantidad +  $item->MLot_Cantidad; 

            $this->html .= "<td class='right-text' style='width=8%;'>" . number_format($item->Prod_Precio, 2) . "</td>";
            $this->html .= "<td class='right-text' style='width=10%;'>" . number_format($item->Prod_Precio2, 2) . "</td>";
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

        $this->html .= '    <table class="responsive-table">';
        $this->html .= '        <tr>';
        $this->html .= '        <td class="right-text" style="width: 75%;">Cantidad de Articulos: </td>';

        $text = number_format((float) $this->cantidad, 0, '.', '');
        $this->html .= '        <td class="right-text" style="width: 25%;"> ' . $text . '</td>';


        $this->html .= '        </tr>';
        $this->html .= '        <tr>';
        $this->html .= '        <td class="right-text" style="width: 75%;">' . $this->lang("Importe total") . ': </td>';
        $text = number_format((float) round($this->importeTotal, 2), 2, '.', '');
        $this->html .= '        <td class="right-text" style="width: 25%;">$  ' . $text . '</td>';
        $this->html .= '        </tr>';
        $this->html .= '    </table>';

            $this->html .= "</div>";
        }
    }



    function extra_line() {
        $extra = $this->voucher["observacion"];
        if ($extra != "") {
            $this->html .= "<div class='border-div'>";
            $this->html .= '    <table class="responsive-table">';
            $this->html .= "        <tr><td class='center-text'style='width: 100%;'>$extra</td></tr>";
            $this->html .= '    </table>';
            $this->html .= "</div>";
        }
    }

    /**
     * Imprime el pie de pagina
     *
     * @author NeoComplexx Group S.A.
     */
    function footer() {
        if ($this->show_element("footer")) {
            $this->html .= '<page_footer>';
            $this->total_line();
            $this->extra_line();
            $this->html .= '    <table class="responsive-table page_footer">';

            $text_left = $this->lang("Documento no v&aacute;lido como factura");
                $text_1 = "&nbsp;";
                $text_2 = "&nbsp;";
                $text_3 = "&nbsp;";
                $text_4 = "&nbsp;";
                $text_5 = "&nbsp;";
            
            $this->html .= '        <tr>';
            $this->html .= '		<td class="" style="width: 30%;">' . $text_left . "</td>";
            $this->html .= '		<td class="center-text" style="width: 40%;">';
            //ran$this->html .= '                ' . $this->lang("Pag.") . ' [[page_cu]]/[[page_nb]]';
            $this->html .= '                ' . $this->lang("Pag.") . ' 1/1';
            $this->html .= '		</td>';
            $this->html .= '		<td class="right-text" style="width: 15%;">' . $text_1 . "</td>";
            $this->html .= '		<td class="left-text" style="width: 15%;">' . $text_2 . "</td>";
            $this->html .= '        </tr>';

            $this->html .= '        <tr>';
            $this->html .= '		<td class="" style="width: 30%;">' . $text_5 . "</td>";
            $this->html .= '		<td class="center-text" style="width: 40%;">&nbsp;</td>';
            $this->html .= '		<td class="right-text" style="width: 15%;">' . $text_3 . "</td>";
            $this->html .= '		<td class="left-text" style="width: 15%;">' . $text_4 . "</td>";
            $this->html .= '        </tr>';

            $this->html .= '    </table>';

            $this->html .= '</page_footer>';
        }
    }


//ran agregado
private function GetChecksumChar($code)
{
    //Step one
    $number_odd = 0;
    for ($i=0; $i < strlen($code); $i+=2) { 
        $number_odd+=$code[$i];
    }

    //Step two
    $number_odd *= 3;

    //Step three
    $number_even = 0;
    for ($i=1; $i < strlen($code); $i+=2) { 
        $number_even+=$code[$i];
    }

    //Step four
    $sum = $number_odd+$number_even;

    //Step five
    $checksum_char = 10 - ($sum % 10);

    return $checksum_char == 10 ? 0 : $checksum_char;
}

    /**
     * Determina si mostrar o no una parte del comprobante
     * @param element TAG del elemento a controlar
     * 
     * @author NeoComplexx Group S.A.
     */
    private function show_element($element) {
        if (array_key_exists("VOUCHER_CONFIG", $this->config) && array_key_exists($element, $this->config["VOUCHER_CONFIG"])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Genera un comprobante Tipo Remito
     */
    function emitirPDF($logo_path , $solo_original = false) {
        //ORIGINAL
        $this->html .= "<page>";
        $this->addVoucherInformation($logo_path, "REMITO");
        $this->addReceiverInformation();
        $this->fill();
    
  //  dd($this->html);

        $this->footer();
        $this->html .= "</page>";

        $this->WriteHTML($this->html);
        return;      
    }

}