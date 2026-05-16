<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>@yield('titulo')</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description" content="Nube Center FotoOptica">
<meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link href="{{ asset('css/tienda/app.css') }}" rel="stylesheet">

	<link href="{{ asset('css/tienda/all.css') }}" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="{{ asset('asset/plugins/font-awesome-4.7.0/css/font-awesome.min.css') }}">

    <link href="{{ asset('css/tienda/modern-business.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tienda/estilos.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans|Kalam" rel="stylesheet">


@yield('estilos')



</head>
<body>

<div id="app">
	


		<!-- Menu -->

		<div class="menu">

			<!-- Search -->
			<div class="menu_search">
				<form action="#" id="menu_search_form" class="menu_search_form">
					<input type="text" class="search_input" placeholder="Buscar Articulo" required="required">
					<button class="menu_search_button"><img src="{{ asset('asset/images/search.png') }}" alt=""></button>
				</form>
			</div>
			<!-- Navigation -->
			<div class="menu_nav">
				<ul>
					<li><a href="#">Receta</a></li>
					<li><a href="#">Sol</a></li>
					<li><a href="#">Reloj</a></li>
					<li><a href="#">Celular</a></li>					
				</ul>
			</div>
			<!-- Contact Info -->
			<div class="menu_contact">
				<div class="menu_phone d-flex flex-row align-items-center justify-content-start">
					<div><div><img src="{{ asset('asset/images/phone.svg') }}" alt="https://www.flaticon.com/authors/freepik"></div></div>
					<div>+54 3772-424548</div>
				</div>
				<div class="menu_social">
					<ul class="menu_social_list d-flex flex-row align-items-start justify-content-start">
						<li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
						<li><a href="#"><i class="fa fa-youtube-play" aria-hidden="true"></i></a></li>
						<li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
						<li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="super_container">

			<!-- Header -->

			<header class="header">
				<div class="header_overlay"></div>
				<div class="header_content d-flex flex-row align-items-center justify-content-start">
					<div class="logo">
						<a href="#">
							<div class="d-flex flex-row align-items-center justify-content-start">
								<div><img src="asset/images/logo_1.png" alt=""></div>
								<div>Center FotoOptica</div>
							</div>
						</a>	
					</div>
					<div class="hamburger"><i class="fa fa-bars" aria-hidden="true"></i></div>
					<nav class="main_nav">
						<ul class="d-flex flex-row align-items-start justify-content-start">
							<li class="active"><a href="#">Receta</a></li>
							<li><a href="#">Sol</a></li>
							<li><a href="#">Relojeria</a></li>					
						</ul>
					</nav>
					<div class="header_right d-flex flex-row align-items-center justify-content-start ml-auto">
						<!-- Search -->
						<div class="header_search">
							<form action="#" id="header_search_form">
								<input type="text" class="search_input" placeholder="Buscar " required="required">
								<button class="header_search_button"><img src="asset/images/search.png" alt=""></button>
							</form>
						</div>
						
						<!-- Cart -->
						<div class="cart"><a href="cart.html"><div><img class="svg" src="asset/images/cart.svg" alt="https://www.flaticon.com/authors/freepik"><div>1</div></div></a></div>
						<!-- Phone -->
						<div class="header_phone d-flex flex-row align-items-center justify-content-start">
							<div><div><img src="asset/images/phone.svg" alt="https://www.flaticon.com/authors/freepik"></div></div>
							<div>+54 3772 424548</div>
						</div>
					</div>
				</div>
			</header>





		@yield('contenido')

		<!-- Footer -->

        <!-- Features Section -->
        <div class="container">            
            <ul class="timeline">
                <li>
                  <div class="timeline-badge"><i class="glyphicon glyphicon-check"></i></div>
                  <div class="timeline-panel">
                    <div class="timeline-heading">
                      <h4 class="timeline-title page-header">Misión</h4>                      
                    </div>
                    <div class="timeline-body">
                      <p>Brindar la mejor atención y servicio en salud visual, ofreciendo calidad y trato amable en el día a día, teniendo siempre como premisa la satisfacción de nuestros clientes.</p>
                    </div>
                  </div>
                </li>
                <li class="timeline-inverted">
                  <div class="timeline-badge warning"><i class="glyphicon glyphicon-credit-card"></i></div>
                  <div class="timeline-panel">
                    <div class="timeline-heading">
                      <h4 class="timeline-title page-header">Visión</h4>
                    </div>
                    <div class="timeline-body">
                      <p>Ser el grupo óptico número uno en la región y el estado, manteniendo siempre nuestros altos estándares de calidad y vanguardia en el ramo.</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="timeline-badge danger"><i class="glyphicon glyphicon-credit-card"></i></div>
                  <div class="timeline-panel">
                    <div class="timeline-heading">
                      <h4 class="timeline-title page-header">Filosofia</h4>
                    </div>
                    <div class="timeline-body">
                      <p>Trabajar siempre dando nuestro mejor esfuerzo, teniendo presente en cada momento que el cliente es primero, demostrando así la excelencia que nos caracteriza.</p>
                    </div>
                  </div>
                </li>                
            </ul>
        </div>
        

        <div class="container" id="contacto">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h3 class="section-heading page-header">Estas buscando algo distinto? Acercate o consultá</h3>
                                       
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 text-center">
                    <img src="{{ asset('/imagenes/iconos/location.png')}}" id="iconos">
                    <a href="https://www.google.com/maps/place/Col%C3%B3n+746,+W3230AAN+Paso+de+los+Libres,+Corrientes/@-29.7148063,-57.0870614,3a,75y,22.97h,87.19t/data=!3m7!1e1!3m5!1sUdezEbiXtgwBagyWdJxQmw!2e0!6s%2F%2Fgeo1.ggpht.com%2Fmaps%2Fphotothumb%2Ffd%2Fv1%3Fbpb%3DChAKDnNlYXJjaC5UQUNUSUxFEmYKOAnrWenYxFxTlBGXyg0fuUPm_RokCxDThbhCGhsSGQoUChIJ61np2MRcU5QRGjsnzMU7D7kQ6gUMEgoNbOJJ7hWYM_ndGhIJV_-hd9pcU5QRzKkYRPf4e9QqCg0x5EnuFXY2-d0aBAhWEFY%26gl%3DAR!7i13312!8i6656!4m5!3m4!1s0x94535cc4d8e959eb:0xfde643b91f0dca97!8m2!3d-29.7147343!4d-57.0870154" target="_blank" style="text-decoration:none"><p>Colon 746</p></a>
                </div>
                <div class="col-lg-3 text-center">
                    <img src="{{ asset('/imagenes/iconos/viber.png')}}" id="iconos">
                    <p>03772-424548</p>
                </div>
                <div class="col-lg-3 text-center">
                    <img src="{{ asset('/imagenes/iconos/whatsapp.png')}}" id="iconos">
                    <p>03772-616464</p>
                </div>
                <div class="col-lg-3 text-center">
                    <img src="{{ asset('/imagenes/iconos/gmail.png')}}" id="iconos">
                    <p><a href="mailto:fotooptica@hotmail.com" style="text-decoration:none">fotooptica@hotmail.com</a></p>
                </div>                
            </div>
        </div><br>
        <div class="container-fluid">
            <h4 class="section-heading" id="ubicacion">PASO DE LOS LIBRES - CORRIENTES</h4>
        </div>
    </div>











				
				<footer class="footer">
					<div class="footer_content">
						<div class="container">
							<div class="row">
								
								<!-- About -->
								<div class="col-lg-4 footer_col">
									<div class="footer_about">
										<div class="footer_logo">
											<a href="#">
												<div class="d-flex flex-row align-items-center justify-content-start">
													<div class="footer_logo_icon"><img src="asset/images/logo_2.png" alt=""></div>
													<div>Center Nube</div>
												</div>
											</a>		
										</div>
										<div class="footer_about_text">
											<p>Estamos a su servicio, brindando la mejoer antensión, nos pueden encotrar en nuestros locales</p>
										</div>
									</div>
								</div>

								<!-- Footer Links -->
								<div class="col-lg-4 footer_col">
									<div class="footer_menu">
										<div class="footer_title">Soporte</div>
										<ul class="footer_list">
											<li>
												<a href="#"><div>Customer Service<div class="footer_tag_1">online now</div></div></a>
											</li>
											<li>
												<a href="#"><div>Return Policy</div></a>
											</li>
											<li>
												<a href="#"><div>Size Guide<div class="footer_tag_2">recommended</div></div></a>
											</li>
											<li>
												<a href="#"><div>Terms and Conditions</div></a>
											</li>
											<li>
												<a href="#"><div>Contact</div></a>
											</li>
										</ul>
									</div>
								</div>

								<!-- Footer Contact -->
								<div class="col-lg-4 footer_col">
									<div class="footer_contact">
										<div class="footer_title">Mantente en contacto</div>
										<div class="newsletter">
											<form action="#" id="newsletter_form" class="newsletter_form">
												<input type="email" class="newsletter_input" placeholder="Suscríbete a nuestro boletín" required="required">
												<button class="newsletter_button">+</button>
											</form>
										</div>
										<div class="footer_social">
											<div class="footer_title">Social</div>
											<ul class="footer_social_list d-flex flex-row align-items-start justify-content-start">
												<li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
												<li><a href="#"><i class="fa fa-youtube-play" aria-hidden="true"></i></a></li>
												<li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
												<li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>











					<div class="footer_bar">
						<div class="container">
							<div class="row">
								<div class="col">
									<div class="footer_bar_content d-flex flex-md-row flex-column align-items-center justify-content-start">
										<div class="copyright order-md-1 order-2"><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->RAN System
		<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></div>
										<nav class="footer_nav ml-md-auto order-md-2 order-1">
											<ul class="d-flex flex-row align-items-center justify-content-start">
												<li><a href="category.html">Recetas</a></li>
												<li><a href="category.html">Sol</a></li>
												<li><a href="category.html">Relojeria</a></li>
												<li><a href="category.html">Otra categoria</a></li>
												<li><a href="#">Contacto</a></li>
											</ul>
										</nav>
									</div>
								</div>
							</div>
						</div>
					</div>
				</footer>
			</div>
				
		</div>

		<!-- Lo utiliza, y no esta incluido en all.js -->
		<script src="{{ asset('asset/js/jquery-3.2.1.min.js') }}"></script>

		<!-- Scripts -->
		<script src="{{ asset('js/tienda/app.js') }}" defer></script>
		<script src="{{ asset('js/tienda/all.js') }}" defer></script>
			

</div>	


</body>
</html>