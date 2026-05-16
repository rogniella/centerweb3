<?PHP

$consulta = "select codigo,descripcion from sucursales order by codigo";
$ret = DB::select($consulta);
foreach ( $ret as $row) {
	echo "<option value='{$row->codigo}'> {$row->descripcion} </option>";
}

?>