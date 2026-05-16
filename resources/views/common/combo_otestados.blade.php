<?PHP
// Deja seleccionado lo que tiene la vbl $FLIA_ID
//

$consulta = "select codigo,descripcion from ot_estados order by orden";
$ret = DB::select($consulta);
foreach ( $ret as $row) {
	echo "<option value='{$row->codigo}'> {$row->descripcion} </option>";
}

?>