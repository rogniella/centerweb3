<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\MonedasController;
use App\Http\Controllers\MinformesController;
use App\Http\Controllers\EstadisticasController;
use App\Http\Controllers\OtController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\AfipController;
use App\Http\Controllers\CajasController;
use App\Http\Controllers\CierresController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\CtrolStockController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TarjetasController;


//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::get('/admin', 'App\Http\Controllers\HomeController@index')->name('home');

//ya no  Route::redirect('/', 'http://tienda.centerfotooptica.com.ar');
 Route::get('/', [HomeController::class, 'index'])->name('home');
 Route::post('/home/shortcuts', [HomeController::class, 'saveShortcuts'])->name('home.shortcuts.save');

Auth::routes();  // Todas las rutas del manejo de login. Se agregan con laravel/ui

Route::get('servicios/index', [
	'uses' => 'App\Http\Controllers\ServiciosController@index' ,  // nombreControlador@metodo
	'as' => 'servicios.index' //Nombre de la ruta
]);


// REQUIEREN PRIVILEGIOS DE ADMINISTRADOR	
Route::group( ['middleware' => ['auth','admin']], function() {

    // MANTENIMIENTO DE USUARIOS	
    Route::resource('users','App\Http\Controllers\UserController')->except(['destroy']);
    // la defino asi para llamarla directamente , no me tomaba 
    Route::get('user/mydestroy/{id}', 'App\Http\Controllers\UserController@destroy')->name('user.mydestroy');
    Route::get('user/password', 'App\Http\Controllers\UserController@password');
    Route::post('user/updatepassword', 'App\Http\Controllers\UserController@updatePassword');
    
}); //FIN Requiere priv de ADM



// REQUIEREN ESTAR LOGUEADO	
Route::group( ['middleware' => ['auth'] ], function() {
	// CLIENTES  
    Route::controller(ClientesController::class)->group(function () {
        Route::get('clientes/index', 'index')->name('clientes.index');
        Route::get('clientes/consulta', 'consulta')->name('clientes.consulta');
        Route::get('clientes/buscar', 'buscar');
        Route::get('clientes/show', 'show');
        Route::get('clientes/store', 'store');
        Route::get('clientes/delete', 'delete');
        Route::get('clientes/update', 'update');
        Route::get('clientes/validate_delete', 'validate_delete');
        Route::get('clientes/informecc', 'informecc')->name('clientes.informecc');
        Route::get('clientes/informecc_proceso', 'informecc_proceso');
        Route::get('clientes/busca_autocompletar', 'busca_autocompletar');
        Route::get('clientes/validate_dni_exists', 'validate_dni_exists');
    });

	// PROVEEDORES
    Route::controller(ProveedoresController::class)->group(function () {
        Route::get('proveedores/index', 'index')->name('proveedores.index');
        Route::get('proveedores/buscar', 'buscar');
        Route::get('proveedores/show', 'show');
        Route::get('proveedores/store', 'store');
        Route::get('proveedores/delete', 'delete');
        Route::get('proveedores/update', 'update');
        Route::get('proveedores/validate_delete', 'validate_delete');
        Route::get('proveedores/busca_autocompletar', 'busca_autocompletar');
        Route::get('proveedores/validate_cuit_exists', 'validate_cuit_exists');
    });

	// MONEDAS  Opciones de ABM por Ajax
    Route::controller(MonedasController::class)->group(function () {
        Route::get('monedas/index', 'index')->name('monedas.index');
        Route::get('monedas/buscar', 'buscar');
        Route::get('monedas/show', 'show');
        Route::get('monedas/store', 'store');
        Route::get('monedas/delete', 'delete');
        Route::get('monedas/update', 'update');
        Route::get('monedas/validate_delete', 'validate_delete');
        Route::get('monedas/graba_cotizacion', 'graba_cotizacion');
    });

	// Minformes  Opciones de ABM por Ajax
    Route::controller(MinformesController::class)->group(function () {
        Route::get('minformes/index', 'index')->name('minformes.index');
        Route::get('minformes/buscar', 'buscar');
        Route::get('minformes/show', 'show');
        Route::get('minformes/store', 'store');
        Route::get('minformes/delete', 'delete');
        Route::get('minformes/update', 'update');
        Route::get('minformes/validate_delete', 'validate_delete');
        Route::get('minformes/graba_codigos', 'graba_codigos');
        Route::get('minformes/graba_rendimiento', 'graba_rendimiento');
        Route::get('minformes/lee_Tipo2', 'lee_Tipo2');
    });

    // ORDENES DE TRABAJO   
    Route::controller(OtController::class)->group(function () {
        Route::get('ot/consulta', 'consulta')->name('ot.consulta');
        Route::get('ot/index', 'index')->name('ot.index');
        Route::get('ot/buscar', 'buscar');
        Route::get('ot/show', 'show');
    });

    //VENTAS
    Route::controller(VentasController::class)->group(function () {
        Route::get('ventas/altas', 'altas')->name('ventas.altas');
        Route::get('ventas/show', 'show');
        Route::get('ventas/generaComprobanteAFIP', 'generaComprobanteAFIP');
        Route::get('ventas/imprimePDF', 'imprimePDF');
        Route::get('ventas/forma_pago', 'forma_pago')->name('ventas.forma_pago');
        Route::post('ventas/store', 'store');
        Route::get('ventas/forma_pago_carga','forma_pago_carga');
        Route::get('ventas/cuotas_tarjeta','cuotas_tarjeta');
    });

    //CAJAS
    Route::controller(CajasController::class)->group(function () {
        Route::get('cajas/ventas', 'ventas')->name('cajas.ventas');
        Route::get('cajas/ventas2', 'ventas2');
        Route::get('cajas/show', 'show');
        Route::get('cajas/store2', 'store2');
        Route::get('cajas/transferencias', 'transferencias')->name('cajas.transferencias');
        Route::get('cajas/altas', 'altas')->name('cajas.altas');
        Route::get('cajas/store', 'store');
        Route::get('cajas/combo_moneda_cuenta', 'combo_moneda_cuenta');
        Route::get('cajas/combo_cuenta_sucursal', 'combo_cuenta_sucursal');
    });    

    //CIERRES
    Route::controller(CierresController::class)->group(function () {
        Route::get('cierres', 'cierres')->name('cierres.index');
        Route::get('cierres/listar', 'listar')->name('cierres.listar');
        Route::get('cierres/guardar-cierre', 'cierreCuenta');
        Route::get('cierres/saldos', 'saldosCuentasDetalle')->name('cierres.saldosCuentasDetalle');
        Route::get('cierres/saldos-listar', 'saldosCuentasDetalle2');
        Route::get('cierres/arqueo', 'arqueo')->name('cajas.arqueo');
        Route::post('cierres/arqueo-guardar', 'arqueoGuardar');
        Route::get('cierres/arqueo-comprobante', 'arqueoComprobante');
    });    
    
    //AFIP
    Route::controller(AfipController::class)->group(function () {
        Route::get('afip/valida_estado_servidor', 'valida_estado_servidor');
        Route::get('afip/valida_cuit', 'valida_cuit');
        Route::get('afip/consulta_factura', 'consulta_factura');
        Route::get('afip/carga_comp_recibido_afip', 'carga_comp_recibido_afip');
        Route::post('afip/carga_comp_recibido_afip2', 'carga_comp_recibido_afip2');
    });

    //FACTURAS
    Route::controller(FacturasController::class)->group(function () {
        Route::get('facturas/index', 'index')->name('facturas.index');
        Route::get('facturas/buscar', 'buscar');
        Route::get('facturas/delete', 'delete');
    });

    //COMPRAS
    Route::controller(ComprasController::class)->group(function () {
        Route::get('compras/index', 'index')->name('compras.index');
        Route::get('compras/buscar', 'buscar');
        Route::get('compras/create', 'create');
        Route::get('compras/Finalizar', 'Finalizar');
        Route::get('compras/CargaItems', 'CargaItems');
        Route::get('compras/AddItem', 'AddItem');
        Route::get('compras/UpdateItem', 'UpdateItem');
        Route::get('compras/DeleteItem', 'DeleteItem');
        Route::get('compras/ActualizaDatosLote', 'ActualizaDatosLote');
        Route::get('compras/ActualizaTotalesLote', 'ActualizaTotalesLote');
    });

    Route::controller(SucursalesController::class)->group(function () {
        Route::get('sucursales/lista_remitos', 'lista_remitos')->name('sucursales.lista_remitos');
        Route::get('sucursales/lista_pedidos', 'lista_pedidos')->name('sucursales.lista_pedidos');
        Route::get('sucursales/buscar', 'buscar');
        Route::get('sucursales/genera_remito', 'genera_remito');
        Route::get('sucursales/carga_remito', 'carga_remito');
        Route::get('sucursales/procesa_remito', 'procesa_remito');
        Route::get('sucursales/envia_email', 'envia_email');
    });

    // ESTADISTICAS
    Route::controller(EstadisticasController::class)->group(function () {
	    Route::get('estadisticas/rubro', 'rubro')->name('estadisticas.rubro');
        Route::get('estadisticas/rubro_proceso', 'rubro_proceso');
        Route::get('estadisticas/iva', 'iva');
        Route::get('estadisticas/iva_proceso', 'iva_proceso');
        Route::get('estadisticas/ot', 'ot')->name('estadisticas.ot');
        Route::get('estadisticas/ot_proceso', 'ot_proceso');
        Route::get('estadisticas/codmov', 'codmov')->name('estadisticas.codmov');
        Route::get('estadisticas/codmov_proceso', 'codmov_proceso');
        Route::get('estadisticas/combo_codmov_informe', 'combo_codmov_informe');
        Route::get('estadisticas/codigos_codmov_informe', 'codigos_codmov_informe');
        Route::get('estadisticas/infmov_detalle', 'infmov_detalle');
        Route::get('estadisticas/infmov_detalle_proceso', 'infmov_detalle_proceso');
        Route::get('estadisticas/consolidado', 'consolidado')->name('estadisticas.consolidado');
        Route::get('estadisticas/consolidado_proceso', 'consolidado_proceso');
        Route::get('estadisticas/infrubro_detalle', 'infrubro_detalle');
        Route::get('estadisticas/infrubro_detalle_proceso', 'infrubro_detalle_proceso');
    });

	// Control de Stock
    Route::controller(CtrolStockController::class)->group(function () {
        Route::get('ctrol_stock/index', 'index')->name('ctrol_stock.index');
        Route::get('ctrol_stock/buscar', 'buscar');
        Route::get('ctrol_stock/create', 'create');
        Route::get('ctrol_stock/index_partes', 'index_partes');
        Route::get('ctrol_stock/calcular_ajuste', 'calcular_ajuste');
        Route::get('ctrol_stock/CargaItems', 'CargaItems');
        Route::get('ctrol_stock/AddItem', 'AddItem');
        Route::get('ctrol_stock/UpdateItem', 'UpdateItem');
        Route::get('ctrol_stock/DeleteItem', 'DeleteItem');
        Route::get('ctrol_stock/consulta', 'consulta');
        Route::get('ctrol_stock/consulta_datos', 'consulta_datos');
        Route::get('ctrol_stock/ActualizaDatosLote', 'ActualizaDatosLote');
    });

    // PRODUCTOS
    Route::controller(ProductosController::class)->group(function () {
        Route::get('productos/publicaciones', 'publicaciones')->name('productos.publicaciones');
        Route::get('productos/index', 'index')->name('productos.index');
        Route::get('productos/registrar_precio_masivo', 'registrar_precio_masivo');
        Route::get('productos/buscar', 'buscar');
        Route::get('productos/show', 'show');
        Route::get('productos/consolida_codigo', 'consolida_codigo');
        Route::get('productos/genera_pedido', 'genera_pedido');
        Route::get('productos/publicaciones2', 'publicaciones2');
        Route::get('productos/add_publicaciones', 'add_publicaciones');
        Route::get('productos/regitrar_ventaOnline', 'regitrar_ventaOnline');
        Route::get('productos/lista_auditoria', 'lista_auditoria');
        Route::get('productos/lista_movimientos', 'lista_movimientos');
        Route::get('productos/movimientos', 'movimientos')->name('productos.movimientos');
        Route::get('productos/buscar_movimientos', 'buscar_movimientos');
        Route::post('productos/store', 'store');
        Route::get('productos/delete', 'delete');
        Route::get('productos/GeneroNvoCodigo', 'GeneroNvoCodigo');
        Route::get('productos/planilla_cristales', 'planilla_cristales')->name('productos.planilla_cristales');
        Route::get('productos/lee_precio', 'lee_precio');
        Route::get('productos/graba_precio', 'graba_precio');
        Route::get('productos/consultaprecio', 'consultaprecio')->name('productos.consultaprecio');
        Route::get('productos/consultaprecio2', 'consultaprecio2');
        Route::get('productos/modificaprecio', 'modificaprecio')->name('productos.modificaprecio');
        Route::get('productos/modificaprecio2', 'modificaprecio2');
        Route::get('productos/buscaproducto', 'buscaproducto');
        Route::get('productos/cambia_codigo', 'cambia_codigo');
        Route::get('productos/actualiza_precio', 'actualiza_precio')->name('productos.actualiza_precio');
        Route::post('productos/actualiza_precio_proceso', 'actualiza_precio_proceso');
    });

    Route::get('marcas/combo_marca', [
        'uses' => 'App\Http\Controllers\MarcasController@combo_marca' ,  
        'as' => 'marcas.combo_marca' //Nombre de la ruta
    ]);

    //  TARJETAS
    Route::controller(TarjetasController::class)->group(function () {
        Route::get('tarjetas/carga', 'carga')->name('tarjetas.carga');
        Route::post('tarjetas/upload', 'upload')->name('tarjetas.upload');
        Route::get('tarjetas/lista_operaciones', 'lista_operaciones')->name('tarjetas.lista_operaciones');
        Route::get('tarjetas/lista_liquidaciones', 'lista_liquidaciones')->name('tarjetas.lista_liquidaciones');            
        Route::get('tarjetas/buscar_operaciones', 'buscar_operaciones')->name('tarjetas.buscar_operaciones');
        Route::get('tarjetas/buscar_liquidaciones', 'buscar_liquidaciones')->name('tarjetas.buscar_liquidaciones');

    });

    

}); //FIN Requiere estar conectado


