<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    // Defino Clave Primaria
    protected $primaryKey = 'Prov_id';

    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at "

    // Campos que pueden ser accedidos y modificados
    protected $fillable = ['Prov_id', 'Prov_RazSocial', 'Prov_NomFant', 'Prov_Telefono', 'Prov_Calle', 'Prov_EMail', 'Prov_Cuit', 'Prov_CtaCon', 'Prov_TipoProv', 'Prov_Observ', 'Prov_FormaPago'];

    public static function buscar($filtro_razsocial = '', $filtro_cuit = '', $limite = 1000)
    {

        // Se la define static  para llamarla sin objeto con ::
        // Busqueda de AutoCompletar y Listado principal, dependiendo de los filtros
        // Concatenar según la consulta. Armo scrip de consulta con los campos segun columnas de la pantalla principal

        $filter = ' where 1=1';
        $valores = [];
        if ($filtro_razsocial != '') {
            if (is_numeric($filtro_razsocial)) {
                $filter .= ' AND prov_cuit LIKE ?';
                $valores[] = '%'.$filtro_razsocial.'%';
            } else {
                $filter .= ' AND (prov_razsocial LIKE ? OR prov_nomfant LIKE ? )';
                $valores[] = '%'.$filtro_razsocial.'%';
                $valores[] = '%'.$filtro_razsocial.'%';
            }
        }

        if ($filtro_cuit != '') {
            $filter .= ' AND prov_cuit LIKE ?';
            $valores[] = '%'.$filtro_cuit.'%';
        }
        $filter .= ' order by prov_razsocial';

        $consulta = 'SELECT  prov_razsocial,prov_nomfant,prov_cuit, prov_telefono,prov_tipoprov, prov_id,prov_ctacon  FROM proveedores '.$filter." LIMIT $limite";

        $datos = DB::select($consulta, $valores);

        return $datos;

    } // Fin Buscar

    public function valida_delete_id($id)
    {

        $ret = '';

        $resultado = DB::select("SELECT count(*) as cantidad FROM lotes WHERE Lot_IdProv = ? AND Lot_Operacion = 'C'", [$id]);
        if (isset($resultado[0]) and $resultado[0]->cantidad > 0) {
            $ret = 'Proveedor Tiene '.$resultado[0]->cantidad.' Compras ingresadas';

            return $ret;
        }

        return $ret;

    } // Fin Valida_Delete

    public function delete_id($id, $id_nuevo)
    {

        if ($id_nuevo != 0) {
            DB::update("UPDATE lotes SET Lot_IdProv = ? WHERE Lot_IdProv = ? AND Lot_Operacion = 'C'", [$id_nuevo, $id]);
        }

        return parent::delete();

    } // Fin Delete

    public function save(array $options = [])
    {

        // Si la instancia ya está en base de datos, es un Update, sino es un Insert
        if ($this->exists) {
            // Update
            $this->Prov_FecUltMan = fechahorahoy();

        } else {
            // Insert
            // Lo genero y gravo
            $this->Prov_FecAlta = fechahorahoy();
        }

        return parent::save($options);

    }
} // Fin del Modulo
