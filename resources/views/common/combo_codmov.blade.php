<?PHP
// Deja seleccionado lo que tiene la vbl $cod_cuenta
//

if ( !isset( $cod_codmov )   ) {
	$cod_codmov = '';
}	

$consulta = "SELECT MCod_Codigo,MCod_Descripcion,MNiv1_Descripcion  FROM mcodigo INNER JOIN mnivel1 ON  mcodigo.MCod_Nivel1= mnivel1.MNiv1_Nivel1  WHERE   MCod_Estado<>'I' ORDER BY MCod_Codigo";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	 $row = (array) $objrow ;  // Para adaptar a la vs que ya tenia
    if ($cod_codmov == $row["MCod_Codigo"]) {
	echo '<option data-subtext="' .  utf8_encode( $row["MNiv1_Descripcion"]) . '" value= "' . $row["MCod_Codigo"] . '"selected>' . $row["MCod_Codigo"] ." " .  $row["MCod_Descripcion"] . '</option>';
    }else{
	echo '<option data-subtext="' .  utf8_encode( $row["MNiv1_Descripcion"]) . '" value= "' . $row["MCod_Codigo"] . '">' . $row["MCod_Codigo"] ." " .  $row["MCod_Descripcion"] . '</option>';
    }
}

?>