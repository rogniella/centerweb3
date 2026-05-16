<?PHP
// Configurar en cada Cliente la tabla comprobantesfiscal
$consulta = "select compfis_codafip, compfis_descripcion, compfis_adm, compfis_defecto from comprobantesfiscal where compfis_venta = 'S' order by compfis_tipo";
$ret = DB::select($consulta);
foreach ( $ret as $objrow) {
	$row = (array) $objrow ;  // Para adaptar a la vs que ya tenia

    if(Auth::user() and Auth::user()->perfil_id == 'ADM' or $row["compfis_adm"] <> "S"  ) {
        if ( "S" == $row["compfis_defecto"]){
            echo "<option value='{$row["compfis_codafip"]}' selected> {$row["compfis_descripcion"]} </option>";
        } else{
	        echo "<option value='{$row["compfis_codafip"]}'> {$row["compfis_descripcion"]} </option>";
        }
    }
}

?>