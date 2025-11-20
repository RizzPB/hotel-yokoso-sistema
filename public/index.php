<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Yokoso - Bienvenido</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (para íconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- estilos para fuente-titulos -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- para animaciones -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.ico">
</head>
<body>
    <!-- Navbar fijo -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--color-rojo-quemado);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/empresaLogoYokoso.png" alt="Logo Hotel Yokoso" class="logo-navbar">
                <span class="fw-bold">Hotel Yokoso</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link text-white" href="#bienvenida">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#habitaciones">Habitaciones</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#paquetes">Paquetes</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#contacto">Contacto</a></li>
                    <li class="nav-item ms-2"><a href="login.php" class="btn btn-outline-light btn-sm">Iniciar Sesión</a></li>
                    <li class="nav-item ms-2"><a href="registro.php" class="btn btn-warning btn-sm text-dark">Regístrate</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sección de bienvenida (full viewport) -->
    <section id="bienvenida" class="section-full text-center">
        <div class="container">
            <div class="p-4 rounded-4 bg-white shadow-sm mx-auto" style="max-width: 700px; background-color: rgba(255, 255, 255, 0.85);">
                <h2 class="display-4 fw-bold text-rojo">¡Bienvenido a Hotel Yokoso!</h2>
                <p class="lead mt-3 text-muted">Tu refugio auténtico en Uyuni, donde la magia del Salar se encuentra con la calidez boliviana.</p>
                <a href="#habitaciones" class="btn btn-rojo btn-lg mt-4">Explorar Habitaciones</a>
            </div>
        </div>
    </section>

    <!-- Hero Full-Screen Carousel -->
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('assets/img/carrusel4.jpg');">
            <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100">
                <h1 class="display-4 fw-bold text-white text-shadow">Habitaciones de Sal</h1>
                <p class="lead text-white text-shadow">Una experiencia única en el corazón del Salar.</p>
                <a href="#" class="btn btn-warning btn-lg mt-3 text-dark">Descubre más</a>
            </div>
            </div>
            <div class="carousel-item" style="background-image: url('assets/img/carrusel2.jpg');">
            <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100">
                <h1 class="display-4 fw-bold text-white text-shadow">Paquetes Turísticos</h1>
                <p class="lead text-white text-shadow">2 o 3 días de magia en el Salar de Uyuni.</p>
                <a href="#" class="btn btn-warning btn-lg mt-3 text-dark">Ver paquetes</a>
            </div>
            </div>
            <div class="carousel-item" style="background-image: url('assets/img/carrusel1.jpg');">
            <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100">
                <h1 class="display-4 fw-bold text-white text-shadow">Bajo el Cielo del Salar</h1>
                <p class="lead text-white text-shadow">El espejo más grande del mundo te espera.</p>
                <a href="#" class="btn btn-warning btn-lg mt-3 text-dark">Reserva ahora</a>
            </div>
            </div>
            <div class="carousel-item" style="background-image: url('assets/img/carrusel3.jpg');">
            <div class="carousel-caption d-flex flex-column justify-content-center align-items-center h-100">
                <h1 class="display-4 fw-bold text-white text-shadow">Hospitalidad Andina</h1>
                <p class="lead text-white text-shadow">Calidez, autenticidad y conexión con la Pachamama.</p>
                <a href="#" class="btn btn-warning btn-lg mt-3 text-dark">Contáctanos</a>
            </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- Acerca de Nosotros (ahora con AOS) -->
    <section id="acerca" class="section-full" style="background-color: var(--color-gris-claro);">
        <div class="container acerca-container">
            <div class="acerca-texto" data-aos="fade-up">
                <h3 class="text-rojo mb-4">Acerca de Nosotros</h3>
                <p class="lead">
                    Ubicado en el corazón de Uyuni, Bolivia, Hotel Yokoso nace del amor por nuestra tierra y la pasión por compartir la belleza del Salar con viajeros del mundo. Ofrecemos una experiencia íntima, personalizada y conectada con la naturaleza.
                </p>
            </div>
            <div class="row acerca-cards mt-4">
                <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="text-rojo">Misión</h5>
                            <p>Brindar una estancia inolvidable, respetuosa con el entorno y adaptada a las necesidades únicas de cada huésped.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="text-rojo">Visión</h5>
                            <p>Ser el hotel más auténtico y recomendado en Uyuni, reconocido por su trato humano y experiencias memorables.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Habitaciones - Full View -->
<section id="habitaciones" class="section-full bg-gris-claro d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <!-- Título -->
            <div class="col-12 mb-5 text-center" data-aos="fade-up">
                <h3 class="text-rojo display-5 fw-bold">Nuestras Habitaciones</h3>
                <p class="lead mt-3 text-muted">Descubre el refugio perfecto para tu estancia en Uyuni.</p>
            </div>

            <!-- Tarjeta 1: Rústicas Clásicas -->
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="habitacion-card rounded-top overflow-hidden shadow-sm">
                    <img src="assets/img/roonNormal.jpeg" alt="Habitaciones Rústicas" class="w-100" style="height: 280px; object-fit: cover;">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-rojo mb-3">
                            <i class="fas fa-home text-mostaza me-2"></i>Rústicas Clásicas
                        </h5>
                        <p class="mb-4">
                            Cálidas y auténticas, diseñadas con madera, piedra y textiles bolivianos. Ideal para quienes buscan confort con sabor andino.
                        </p>
                        <a href="habitaciones.php#rusticas" class="btn btn-outline-rojo w-100">Ver más</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Suites Familiares -->
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="habitacion-card rounded-top overflow-hidden shadow-sm">
                    <img src="assets/img/roomSuite.jpg" alt="Suites Familiares" class="w-100" style="height: 280px; object-fit: cover;">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-rojo mb-3">
                            <i class="fas fa-door-open text-mostaza me-2"></i>Suites Familiares
                        </h5>
                        <p class="mb-4">
                            Espacios amplios con dos habitaciones conectadas. Perfectos para familias o grupos que buscan privacidad y comodidad.
                        </p>
                        <a href="habitaciones.php#familiares" class="btn btn-outline-rojo w-100">Ver más</a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: De Sal -->
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="habitacion-card rounded-top overflow-hidden shadow-sm">
                    <img src="assets/img/roomSal.jpeg" alt="Habitaciones de Sal" class="w-100" style="height: 280px; object-fit: cover;">
                    <div class="card-body p-4 text-center">
                        <h5 class="text-rojo mb-3">
                            <i class="fas fa-mountain text-mostaza me-2"></i>De Sal
                        </h5>
                        <p class="mb-4">
                            Construidas íntegramente con bloques de sal del Salar de Uyuni. Una experiencia sensorial única bajo el cielo estrellado.
                        </p>
                        <a href="habitaciones.php#sal" class="btn btn-outline-rojo w-100">Ver más</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <main class="container my-5">
    

        <!-- Paquetes Turísticos -->
        <section class="mb-5">
            <h3 class="text-center text-rojo mb-4">Paquetes Turísticos al Salar de Uyuni</h3>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5><i class="fas fa-calendar-day text-mostaza me-2"></i>Paquete de 2 Días</h5>
                            <ul>
                                <li>Transporte privado desde el hotel</li>
                                <li>Visita a Laguna Colorada, Desierto de Siloli, Árbol de Piedra</li>
                                <li>Alojamiento en refugio básico</li>
                                <li>Comidas según preferencias (vegetariano, vegano, sin gluten, etc.)</li>
                                <li>Fotos profesionales del recorrido</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5><i class="fas fa-calendar-alt text-mostaza me-2"></i>Paquete de 3 Días</h5>
                            <ul>
                                <li>Recorrido completo por el Salar de Uyuni</li>
                                <li>Visita a Isla Incahuasi, Ojos de Sal, Termas de Polques</li>
                                <li>2 noches de alojamiento (1 en refugio, 1 en hotel)</li>
                                <li>Menú personalizado según tus preferencias alimentarias</li>
                                <li>Guía bilingüe y fotos incluidas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-center mt-3"><em>Todos nuestros paquetes incluyen transporte, guía, comidas adaptadas a tus necesidades y recuerdos fotográficos.</em></p>
        </section>

        <!-- Pie de Página -->
        <div class="footer-wrapper">
            <footer class="bg-dark text-white pt-4 pb-3">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <h5>Información de Contacto</h5>
                            <p><i class="fas fa-map-marker-alt me-2"></i> Uyuni, Potosí, Bolivia</p>
                            <p><i class="fas fa-phone me-2"></i> +591 777 888 999</p>
                            <p><i class="fas fa-envelope me-2"></i> contacto@hotelyokoso.com</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <h5>Redes Sociales</h5>
                            <a href="https://www.facebook.com/share/1L89sfZGV5/" class="me-3"><i class="fab fa-facebook-f fa-2x text-white"></i></a>
                            <a href="https://www.instagram.com/hostalyokoso?igsh=MW5tODFiNnoydndhcA==" class="me-3"><i class="fab fa-instagram fa-2x text-white"></i></a>
                            <a href="#" class="me-3"><i class="fab fa-whatsapp fa-2x text-white"></i></a>
                        </div>
                        <div class="col-md-4">
                            <h5>Mapa de Ubicación</h5>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3906.754488499243!2d-68.13866988530783!3d-17.38644448793947!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x93e34b5c4c4c4c4c%3A0xc4c4c4c4c4c4c4c4!2sUyuni%2C%20Bolivia!5e0!3m2!1ses!2sbo!4v1700000000000!5m2!1ses!2sbo" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="text-center">
                        <a href="tel:+591777888999" class="btn btn-warning btn-lg">
                            <i class="fas fa-phone me-2"></i>Llamar Ahora
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true // Solo anima una vez al cargar
    });
    </script>
</body>
</html>