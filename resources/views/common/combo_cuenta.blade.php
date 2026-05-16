<?PHP
// Deja seleccionado lo que tiene la vbl $cod_cuenta
//

if ( !isset( $cod_cuenta )   ) {
	$cod_cuenta = '';
}	

$consulta = "SELECT MCta_CodCta,MCta_Descripcion FROM mcuenta  WHERE    MCta_Estado<>'I' ORDER BY MCta_CodCta";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	 $row = (array) $objrow ;  // Para adaptar a la vs que ya tenia
	if ($cod_cuenta == $row["MCta_CodCta"])
	{
		echo '<option value= "' . $row["MCta_CodCta"] . '"selected>' . $row["MCta_Descripcion"] . '</option>';
	}
	else
	{
		echo '<option value= "' . $row["MCta_CodCta"] . '">' . $row["MCta_Descripcion"] . '</option>';
	}
}
?>