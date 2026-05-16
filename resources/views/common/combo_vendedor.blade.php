<?PHP
// Deja seleccionado lo que tiene la vbl $vendedor
$consulta = "select usu_nombre, usu_apeynom from usuarios where usu_estado = 'H' order by usu_apeynom";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	$row = (array) $objrow ;  // Para adaptar a la vs que ya tenia

    if ($vendedor == $row["usu_nombre"]){
        echo "<option value='{$row["usu_nombre"]}' selected> {$row["usu_nombre"]} </option>";
    } else{
	    echo "<option value='{$row["usu_nombre"]}'> {$row["usu_nombre"]} </option>";
    }
}

?>