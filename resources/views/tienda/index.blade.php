@extends('template.plantilla_tienda')

@section('titulo','Center FotoOptica Tienda')

@section('estilos') 

<link rel="stylesheet" type="text/css" href="{{ asset('asset/styles/main_styles.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('asset/styles/responsive.css') }}">

@endsection


@section('contenido')


<div class="super_container_inner">
    <div class="super_overlay"></div>

    <!-- Home -->

    <div class="home">
        <!-- Home Slider -->
        <div class="home_slider_container">
            <div class="owl-carousel owl-theme home_slider">
                
            @foreach($destacados as $item)                        
                <!-- Slide -->
                <div class="owl-item">
                    <div class="background_image" style="background-image:url({{ $item->imagen }})"></div>
                    <div class="container fill_height">
                        <div class="row fill_height">
                            <div class="col fill_height">
                                <div class="home_container d-flex flex-column align-items-center justify-content-start">
                                    <div class="home_content">
                                        <div class="home_title">{{ $item->titulo }}</div>
                                        <div class="home_subtitle">{{ $item->subtitulo }}</div>


                                        <div class="home_items">
                                            <div class="row">
                                                <div class="col-sm-3 offset-lg-1">														
                                                </div>
                                                <div class="col-lg-4 col-md-6 col-sm-8 offset-sm-2 offset-md-0">
                                                    <div class="product home_item_large">
                                                        <div class="product_tag d-flex flex-column align-items-center justify-content-center">
                                                            <div>
                                                                <div> Desde</div>
                                                                <div>${{ $item->precio2 }}</div>
                                                                <del class="price-oldslider">$ {{ $item->precio }}</del>
                                                            </div>
                                                        </div>
                                                        <div class="product_image"><img src="{{ $item->imagen }}" alt=""></div>
                                                        <div class="product_content">																
                                                            <div class="product_buttons">
                                                                <div class="text-right d-flex flex-row align-items-start justify-content-start">																		
                                                                    <div class="product_button product_cart text-center d-flex flex-column align-items-center justify-content-center">
                                                                        <div><div><img src="asset/images/cart_2.svg" alt=""><div>+</div></div></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>	
                </div>
            @endforeach

            </div>
            <div class="home_slider_nav home_slider_nav_prev"><i class="fa fa-chevron-left" aria-hidden="true"></i></div>
            <div class="home_slider_nav home_slider_nav_next"><i class="fa fa-chevron-right" aria-hidden="true"></i></div>


            <!-- Home Slider Dots -->
            
            <div class="home_slider_dots_container">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="home_slider_dots">
                                <ul id="home_slider_custom_dots" class="home_slider_custom_dots d-flex flex-row align-items-center justify-content-center">
                                    <li class="home_slider_custom_dot active">01</li>
                                    <li class="home_slider_custom_dot">02</li>
                                    <li class="home_slider_custom_dot">03</li>
                                    <li class="home_slider_custom_dot">04</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>	
            </div>

        </div>
    </div>






    <!-- Productos -->

    <div class="products">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="section_title text-center">Populares en Center FotoOptica</div>
                </div>
            </div>
            <div class="row page_nav_row">
                <div class="col">
                    <div class="page_nav">
                        <ul class="d-flex flex-row align-items-start justify-content-center">
                          @foreach($categorias as $item)                        
                            <li class="{{$item->clase}}"><a href="{{$item->valor}}">{{ $item->titulo }}</a></li>
                          @endforeach  								
                        </ul>
                    </div>
                </div>
            </div>


<!-- Lo mas visto -->

<div class="lomasvendidocontenedor">
<div class="section_title text-center">Trabajamos con las mejores marcas</div>	
<br> 	 
        <div class="lomasvendido owl-carousel owl-theme">   
            @foreach($marcas as $item)                        
                <div class="">
                    <div class="product">
                        <div class="product_image"><img src="{{ $item->imagen }}" alt=""></div>
                            <div class="product_content">
                                <div class="product_info d-flex flex-row align-items-start justify-content-start">
                                </div>

<a href="marcas/rayban.html" style="text-decoration:none">Ver colección &nbsp;<span class="glyphicon  glyphicon-chevron-right" ></span></a>


                            </div>
                        </div>
                    </div>

            @endforeach
            </div>

        </div>		
</div>
</div>

<br>

<br>




<br>


<br>


<


<div class="lomasvendidocontenedor">
    <div class="section_title text-center">En oferta Pru</div>  
    <br>     
        <div class="lomasvendido owl-carousel owl-theme">
            <div class=""> <!-- item-->
        @foreach($mas_vendido as $item)                           
          @if ($item->images->count() > 0 )
              <div class="product">                                   
                <div class="product_image"><img src="{{ asset($item->images->first()->url) }}" alt=""></div>
                <div class="product_content">
                <div class="product_info">
                                                <div>
                                                    <div>
                                                        <div class="product_name product_namesinwidth text-center"><a href="product.html">{{ $item->Prod_Descripcion }}</a></div>
                                                        
                                                    </div>
                                                </div>
                                                <div class="ml-auto">                                                   
                                                    <div class="product_price text-center">$3<span>.99</span><del class="price-old"> $1980.00</del></div>                                                       
                                                    
                                                </div>
                                            </div>
                                    <div class="product_buttons">
                                        <div class="text-right d-flex flex-row align-items-start justify-content-start">                                        
                                            <div class="product_button product_cart text-center d-flex flex-column align-items-center justify-content-center">
                                                <div><div><img src="asset/images/cart.svg" class="svg" alt=""><div>+</div></div></div>
                                            </div>
                                        </div>
                                    </div>
                        </div>
          @endif
        @endforeach            
                        
            </div> <!-- item-->
        </div> <!-- carrusel--> 
    </div> <!-- titulo-->      
</div> <!-- contenedor-->


<!-- En oferta -->


<div class="lomasvendidocontenedor">
        <div class="section_title text-center">En oferta</div>	
        <br> 	 
                <div class="lomasvendido owl-carousel owl-theme">

                        <!-- item-->
    
                        <div class="">
                                <div class="product">
                                    <span class="badge-offer"><b> - 50%</b></span>
                                    <div class="product_image"><img src="asset/images/product_5.jpg" alt=""></div>
                                    <div class="product_content">
                                            <div class="product_info">
                                                    <div>
                                                        <div>
                                                            <div class="product_name product_namesinwidth text-center"><a href="product.html">Cool Clothing with Brown Stripes</a></div>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="ml-auto">													
                                                        <div class="product_price text-center">$3<span>.99</span><del class="price-old"> $1980.00</del></div>														
                                                        
                                                    </div>
                                                </div>
                                        <div class="product_buttons">
                                            <div class="text-right d-flex flex-row align-items-start justify-content-start">										
                                                <div class="product_button product_cart text-center d-flex flex-column align-items-center justify-content-center">
                                                    <div><div><img src="asset/images/cart.svg" class="svg" alt=""><div>+</div></div></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    
                    <!-- item-->
                    <div class="item">
                            <div class="">
                                    <div class="product">
                                            <span class="badge-new"><b> Nuevo</b></span>
                                    <span class="badge-offer"><b> - 50%</b></span>

                                        <div class="product_image"><img src="asset/images/product_6.jpg" alt=""></div>
                                        <div class="product_content">
                                                <div class="product_info">
                                                    <div>
                                                        <div>
                                                            <div class="product_name product_namesinwidth text-center"><a href="product.html">Cool Clothing with Brown Stripes</a></div>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="ml-auto">													
                                                        <div class="product_price text-center">$3<span>.99</span><del class="price-old"> $1980.00</del></div>														
                                                        
                                                    </div>
                                                </div>
                                                <div class="product_buttons">
                                                    <div class="text-right d-flex flex-row align-items-start justify-content-start">										
                                                        <div class="product_button product_cart text-center d-flex flex-column align-items-center justify-content-center">
                                                            <div><div><img src="asset/images/cart.svg" class="svg" alt=""><div>+</div></div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </div>
    
    
    
    
    
    
                    </div>
        
                </div>		
        </div>
    </div>
    
    <br>
    
    <br>
    
    <br>


    <!-- Boxes -->

    <div class="boxes">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="boxes_container d-flex flex-row align-items-start justify-content-between flex-wrap">

                        <!-- Box -->
                        <div class="box">
                            <div class="background_image" style="background-image:url(asset/images/box_1.jpg)"></div>
                            <div class="box_content d-flex flex-row align-items-center justify-content-start">
                                <div class="box_left">
                                    <div class="box_image">
                                        <a href="category.html">
                                            <div class="background_image" style="background-image:url(asset/images/box_1_img.jpg)"></div>
                                        </a>
                                    </div>
                                </div>
                                <div class="box_right text-center">
                                    <div class="box_title">Trendsetter Collection</div>
                                </div>
                            </div>
                        </div>

                        <!-- Box -->
                        <div class="box">
                            <div class="background_image" style="background-image:url(asset/images/box_2.jpg)"></div>
                            <div class="box_content d-flex flex-row align-items-center justify-content-start">
                                <div class="box_left">
                                    <div class="box_image">
                                        <a href="category.html">
                                            <div class="background_image" style="background-image:url(asset/images/box_2_img.jpg)"></div>
                                        </a>
                                    </div>
                                </div>
                                <div class="box_right text-center">
                                    <div class="box_title">Popular Choice</div>
                                </div>
                            </div>
                        </div>

                        <!-- Box -->
                        <div class="box">
                            <div class="background_image" style="background-image:url(asset/images/box_3.jpg)"></div>
                            <div class="box_content d-flex flex-row align-items-center justify-content-start">
                                <div class="box_left">
                                    <div class="box_image">
                                        <a href="category.html">
                                            <div class="background_image" style="background-image:url(asset/images/box_3_img.jpg)"></div>
                                        </a>
                                    </div>
                                </div>
                                <div class="box_right text-center">
                                    <div class="box_title">Popular Choice</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->

    <div class="features">
        <div class="container">
            <div class="row">
                
                <!-- Feature -->
                <div class="col-lg-4 feature_col">
                    <div class="feature d-flex flex-row align-items-start justify-content-start">
                        <div class="feature_left">
                            <div class="feature_icon"><img src="asset/images/icon_1.svg" alt=""></div>
                        </div>
                        <div class="feature_right d-flex flex-column align-items-start justify-content-center">
                            <div class="feature_title">Pagos rápidos y seguros</div>
                        </div>
                    </div>
                </div>

                <!-- Feature -->
                <div class="col-lg-4 feature_col">
                    <div class="feature d-flex flex-row align-items-start justify-content-start">
                        <div class="feature_left">
                            <div class="feature_icon ml-auto mr-auto"><img src="asset/images/icon_2.svg" alt=""></div>
                        </div>
                        <div class="feature_right d-flex flex-column align-items-start justify-content-center">
                            <div class="feature_title">Productos de calidad</div>
                        </div>
                    </div>
                </div>

                <!-- Feature -->
                <div class="col-lg-4 feature_col">
                    <div class="feature d-flex flex-row align-items-start justify-content-start">
                        <div class="feature_left">
                            <div class="feature_icon"><img src="asset/images/icon_3.svg" alt=""></div>
                        </div>
                        <div class="feature_right d-flex flex-column align-items-start justify-content-center">
                            <div class="feature_title">Entrega gratis después de $100</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>




@endsection