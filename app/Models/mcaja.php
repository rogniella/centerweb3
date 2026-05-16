<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\auditoria_caja;  

class mcaja extends Model {

	protected $table = "mcaja";
	protected $primaryKey = 'Mcaj_IdWEB';
	public $timestamps = false;  //no tiene los campos Timestamps "created_at" y "updated_at"

    protected $fillable = [
        'Mcaj_IdWEB','MCaj_Sucursal', 'MCaj_FecMov', 'MCaj_Codigo', 'MCaj_Moneda' , 'MCaj_Monto','MCaj_CtaOri','MDes_Descripcion'
    ];

    public function save(array $options = array()) {

      // Si la instancia ya está en base de datos, es un Update, sino es un Insert

      if ($this->exists) {
          //Update ***  PARA PRODUCTOS no funciona
          $campos_modificados = $this->getDirty();
          if (count($campos_modificados) > 1 ) {
            $detalle = "Antes";
	          foreach($campos_modificados as $campo=>$valor) {
              If (trim($valor) != trim($this->getOriginal( $campo) )  and 
                  $campo != 'Mcaj_IdWEB'  ) {
                 $detalle .= ' ' .  $campo . ':' .  $this->getOriginal( $campo );
              }
              If ( $campo == 'MCaj_Sucursal'  ) {
                 abort(402, 'Error: No se Permite Modificar Sucursal' );
                 return ;
              }
            }        
            // Inserta en Auditoria
              $auditoria  = new auditoria_caja;
              $auditoria->aud_sucursal = $this->MCaj_Sucursal;
              $auditoria->AUD_AUTOID = 0; // Solo para los que se originan en sucursales
              $auditoria->AUD_TIPO = 'M';
              $auditoria->AUD_ID = $this->Mcaj_IdWEB;
              $auditoria->AUD_ACCION = substr($detalle,0,60);
              $auditoria->AUD_USUARIO = Auth::user()->name ;

              if ( ! $auditoria->save() ) {
                 abort(402, 'Error: No se Permite Modificar Sucursal' );
                 return ;
              };  

          } // Si tiene modificados


      }else{
          // Insert Alta
      }   
  
      return parent::save($options );
      
  } // Fin Save


}
