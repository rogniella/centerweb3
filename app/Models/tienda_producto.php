<?php namespace App\Models;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

// Para usar API de la Tienda
// composer require codexshaper/laravel-woocommerce
// https://codexshaper.github.io/docs/laravel-woocommerce/
use Codexshaper\WooCommerce\Facades\Product;


class tienda_producto  {

    public  $id = 0; 
    public  $sku = ""; 
    public  $descripcion = ""; 
    public  $precio = 0; 

    public static function listar( $filtro_nombre = '' ){

        // Trabajo directo con la BD porque es mucho mas rapido que la API
        $consulta = "SELECT sku, max_price as regular_price,min_price,stock_status as stock ,ID , post_title as name
        FROM wpmi_posts INNER JOIN  wpmi_wc_product_meta_lookup ON ID = product_id WHERE post_type = 'product' and post_title LIKE ?
      "  ;
        $array_prod  = DB::connection('bdtienda')->select($consulta , [ '%' . $filtro_nombre . '%' ]);

        // Recorro para Completar 
        foreach ($array_prod as $row) {
                $stock = "";
                if ($row->stock == 'outofstock' ) {
                    $row->stock = "Agotado";
                } elseif ($row->stock == 'instock' ) {
                    $row->stock = "";
                }    
        }

        return $array_prod;    

    } // Fin 
  
    public static function find( $sku) {   
       $prod = new self;
       $consulta = "SELECT ID ,sku, max_price as regular_price,min_price,stock_status as stock ,ID , post_title as name
       FROM wpmi_posts INNER JOIN  wpmi_wc_product_meta_lookup ON ID = product_id WHERE post_type = 'product' and sku = ?";
      $datos = DB::connection('bdtienda')->select($consulta,[ $sku] );
      //dd($sku, $datos);
      if($datos ) {
        $prod->id =  $datos[0]->ID;
        $prod->sku =  $datos[0]->sku;
        $prod->descripcion =  $datos[0]->name;
        $prod->precio =  $datos[0]->regular_price;
      }  
       
       return $prod;
    } // Fin 

    public function save() {

            // Estas tablas no actualiza
            $sql = "UPDATE wpmi_wc_product_meta_lookup SET min_price = ?, max_price = ?  WHERE sku = ?";
            $datos = DB::connection('bdtienda')->update($sql,[ $this->precio,$this->precio,$this->sku] );
      
            $sql ="UPDATE wpmi_postmeta SET meta_value=? where  post_id = ? and meta_key ='_price'";
            $datos = DB::connection('bdtienda')->update($sql,[ $this->precio,$this->id] );
      
            $sql ="UPDATE wpmi_postmeta SET meta_value=? where  post_id = ? and meta_key ='_regular_price'";
            $datos = DB::connection('bdtienda')->update($sql,[ $this->precio,$this->id] );

            $sql = "update wpmi_posts set post_modified_gmt = ? , post_modified = ? WHERE ID = ?";
            $datos = DB::connection('bdtienda')->update($sql,[ fechahoy(),fechahoy(),$this->id] );
      
      // Uso funciones de la API
      $product_id = $this->id;
      $data       = [
          'regular_price' => $this->precio,
          'sale_price'    => $this->precio, // Promo off
      ];
      $product2 = Product::update($product_id, $data);



    } // Fin 


} //Fin Clase

/*
    // Ej de Listar con API
         - Mucho mas lento
         - Uso limite de 100 porque sino da error
         - No puedo seleccionar los campos, trae todos

//  $product1 = Product::where('sku','<>', ' ')->options(['status' => 'publish','page'=> 1, 'per_page' => 100 ])->get(['name','sku','regular_price']);
    $product1 = Product::options(['status' => 'publish','page'=> 1, 'per_page' => 100 ])->get();
    $product2 = Product::options(['status' => 'publish','page'=> 2, 'per_page' => 100 ])->get();
    $product3 = Product::options(['status' => 'publish','page'=> 3, 'per_page' => 100 ])->get();
    $array_prod =  array_merge( $product1->toArray() , $product2->toArray() , $product3->toArray() ) ;

      // Recorro para Completar los stock de las sucursales
      $datostienda = [];
      foreach ($array_prod as $row) {
        //php8 if (str_contains($row->name,$request->filtroDescri)) {
        // "stock_status" => "outofstock"   Agotado
        $seleccionado = false;
        if($request->filtroDescri == '' ){
          $seleccionado = true;
        }else{
          if (stripos($row->name,strval($request->filtroDescri) ) !== false) {
            $seleccionado = true;
          }  
        }  
        if($seleccionado == true ) {
          $stock = "";
          if ($row->stock_status == 'outofstock' ) {
             $stock = "Agotado";
          }
          $datostienda[] = array(
            'name'  => $row->name ,
            'sku'  => $row->sku ,
            'regular_price'  => $row->regular_price  ,
            'stock'  => $stock  
          );
        }  
      }


*/