<?PHP
// Deja seleccionado lo que tiene la vbl $FLIA_ID
//

if (!isset($FLIA_ID)) {$FLIA_ID = '';}

$consulta = "select FLIA_ID, FLIA_DESCRIPCION from familias where Flia_VentaDir = True";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	 $row = (array) $objrow ;  // Para adaptar a la vs que ya tenia
    if ($FLIA_ID == $row["FLIA_ID"]){
        echo "<option value='{$row["FLIA_ID"]}' selected> {$row["FLIA_ID"]}-{$row["FLIA_DESCRIPCION"]} </option>";
    } else{
		echo "<option value='{$row["FLIA_ID"]}'> {$row["FLIA_ID"]}-{$row["FLIA_DESCRIPCION"]} </option>";
    }
}

?>