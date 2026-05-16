<?PHP
// Deja seleccionado lo que tiene la vbl $INF_ID
//   $INF_TIPO   1 ´= Inter Anuales Linea     2 = Diferencial Anuales de Linea
$consulta = "select inf_idInforme, inf_descripcion from minforme where inf_tipo = $INF_TIPO order by inf_descripcion";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	$row = (array) $objrow ;  // Para adaptar a la vs que ya tenia
    if ($INF_ID == $row["inf_idInforme"]){
        echo "<option value='{$row["inf_idInforme"]}' selected> {$row["inf_descripcion"]} </option>";
    } else{
	echo "<option value='{$row["inf_idInforme"]}'> {$row["inf_descripcion"]} </option>";
    }
}

?>