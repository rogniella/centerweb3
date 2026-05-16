<?php

//  LOTES DE COMPRAS

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class lote extends Model
{
    
    protected $table = "lotes";
	protected $primaryKey = 'Lot_Numlot';

    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at "
    
    // Campos que pueden ser accedidos y modificados
    protected $fillable = ['Lot_Numlot','Lot_IdProv','Lot_FecMov','Lot_Observ','Lot_Observ','Lot_Monto','Lot_Cantidad', 'Lot_Rendimiento','Lot_Factor' ,'Lot_idFactProv','Lot_Sucursal'];

    public static function buscarCtrolStock($lot_estado,$tipo_lote,$numlot, $sucursal ) {

        // Se la define static  para llamarla sin objeto con :: 
        // Busca las COMPRAS pendientes
        $filtro = '';
        if ( !$sucursal == 0 )  {
            $filtro = ' and lot_sucursal=' . $sucursal;
        }
        
        if ($tipo_lote == "T") {
            $consulta = "SELECT Lot_Numlot,DATE_FORMAT(Lot_fecmov, '%d/%m/%Y') as Fecha, Lot_Sucursal, Lot_Familia,Lot_Observ, Lot_Numlot as lote, Lot_Monto,Lot_Filtro, Lot_Cant_bd,Lot_Cantidad ,
                   ( SELECT sum(MLot_Cantidad) FROM lotesmovpend  WHERE MLot_NumLot IN (SELECT Lot_Numlot FROM lotes  WHERE    Lot_IdProv= lote ) ) as  Lot_Cant_ing FROM lotes ";
            $consulta.=" where lot_operacion= 'T' and lot_estado = '$lot_estado'" . $filtro .  " ORDER BY Lot_fecmov desc"; 
        }else{
            $consulta = "SELECT Lot_Numlot,DATE_FORMAT(Lot_fecmov, '%d/%m/%Y') as Fecha, Lot_Sucursal, Lot_Familia,Lot_Observ, Lot_Numlot,
                 ( SELECT sum(MLot_Cantidad) FROM lotesmovpend  WHERE    MLot_NumLot= Lot_Numlot ) as  Lot_Cantidad FROM lotes ";
            $consulta.=" where lot_operacion= 'S' and Lot_IdProv = '$numlot' ORDER BY Lot_fecmov desc";     
        }    
        
  
        $datos = DB::select($consulta);
  
        return $datos;
  
    } // Fin Buscar Pendiente
  

    public static function buscarPendientes($lot_estado) {

      // Se la define static  para llamarla sin objeto con :: 
      // Busca las COMPRAS pendientes

      $consulta = "SELECT Lot_Numlot,DATE_FORMAT(Lot_fecmov, '%d/%m/%Y') as Fecha, Lot_Sucursal, Prov_NomFant,Lot_Observ, Lot_Numlot, Lot_Monto,Lot_Cantidad FROM lotes LEFT JOIN proveedores ON lotes.Lot_IdProv = proveedores.Prov_id ";
      $consulta.=" where lot_operacion= 'C' and lot_estado = '$lot_estado' ORDER BY Lot_fecmov desc"; 

      $datos = DB::select($consulta);

      return $datos;

    } // Fin Buscar Pendiente

    public static function buscarRemitos($lot_estado) {

      // Se la define static  para llamarla sin objeto con :: 
      // Lista de Remitos Inter Sucursal Segun su estado
      //  lot_operacion= 'W'  Indica que es un remito

      $consulta = "SELECT Lot_Numlot, DATE_FORMAT(Lot_fecmov, '%d/%m/%Y') as Fecha,
          sucdes.descripcion as Suc_Destino,
          sucori.descripcion as Suc_Origen,
          Lot_Observ, Lot_Numlot, Lot_Monto,Lot_Cantidad FROM lotes 
              LEFT JOIN sucursales as sucdes ON lotes.Lot_IdProv = sucdes.codigo
              LEFT JOIN sucursales as sucori ON lotes.Lot_Sucursal = sucori.codigo ";

      $consulta.=" where lot_operacion= 'W' and lot_estado = '$lot_estado' ORDER BY Lot_fecmov desc"; 

      $datos = DB::select($consulta);

     // dd($datos,$consulta);

      return $datos;

    } // Fin Buscar Remitos


    public function delete_id($id ,  $id_nuevo ){

      // Elimina por Id
      return parent::delete();

    } // Fin Delete

} //Fin del Modulo