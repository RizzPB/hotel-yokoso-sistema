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
    <link rel="stylesheet" href="css/style.css">

    <!-- Favicon (opcional) -->
    <link rel="icon" href="img/favicon.ico">
</head>
<body>
    <!-- Encabezado -->
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="img/logo.png" alt="Logo Hotel Yokoso" width="60" class="me-3">
                <div>
                    <h1 class="h4 mb-0">Hotel Yokoso</h1>
                    <p class="mb-0 small">Hospitalidad única en el corazón de Uyuni, Bolivia</p>
                </div>
            </div>
            <nav>
                <a href="#" class="btn btn-outline-light me-2">Inicio</a>
                <a href="login.php" class="btn btn-outline-light me-2">Log In</a>
                <a href="registro.php" class="btn btn-warning">Regístrate</a>
            </nav>
        </div>
    </header>

    <!-- Sección Principal -->
    <main class="container my-5">
        <!-- Bienvenida -->
        <section class="text-center mb-5">
            <h2 class="display-5 fw-bold text-mostaza">¡Bienvenido a Hotel Yokoso!</h2>
            <p class="lead mt-3">Tu refugio auténtico en Uyuni, donde la magia del Salar se encuentra con la calidez boliviana.</p>
        </section>

        <!-- Carrusel de Imágenes -->
        <section class="mb-5">
            <div id="carouselYokoso" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselYokoso" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselYokoso" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselYokoso" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="img/carrusel1.jpeg" class="d-block w-100" alt="Habitación de Sal">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Habitaciones de Sal</h5>
                            <p>Una experiencia única construida con bloques del Salar de Uyuni.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="img/carrusel2.jpeg" class="d-block w-100" alt="Paquete Turístico">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Paquetes al Salar</h5>
                            <p>Recorridos de 2 o 3 días con transporte, guía y comida personalizada.</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="img/carrusel3.jpeg" class="d-block w-100" alt="Vista del Salar">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Magia del Salar</h5>
                            <p>El espejo natural más grande del mundo, a solo minutos de tu habitación.</p>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselYokoso" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselYokoso" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </section>

        <!-- Acerca de Nosotros -->
        <section class="row mb-5">
            <div class="col-md-6">
                <h3 class="text-rojo">Acerca de Nosotros</h3>
                <p>Ubicado en el corazón de Uyuni, Bolivia, Hotel Yokoso nace del amor por nuestra tierra y la pasión por compartir la belleza del Salar con viajeros del mundo. Ofrecemos una experiencia íntima, personalizada y conectada con la naturaleza.</p>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="text-rojo">Misión</h5>
                        <p>Brindar una estancia inolvidable, respetuosa con el entorno y adaptada a las necesidades únicas de cada huésped.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="text-rojo">Visión</h5>
                        <p>Ser el hotel más auténtico y recomendado en Uyuni, reconocido por su trato humano y experiencias memorables.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Habitaciones -->
        <section class="mb-5">
            <h3 class="text-center text-rojo mb-4">Nuestras Habitaciones</h3>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5><i class="fas fa-home text-mostaza me-2"></i>Habitaciones Normales / Rústicas</h5>
                            <p>22 habitaciones diseñadas con materiales locales, cálidas, cómodas y con vista al entorno natural de Uyuni. Ideal para quienes buscan autenticidad.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5><i class="fas fa-mountain text-mostaza me-2"></i>Habitaciones de Sal</h5>
                            <p>6 habitaciones únicas construidas con bloques de sal del Salar de Uyuni. Una experiencia mística y exclusiva que no encontrarás en ningún otro lugar del mundo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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

        <!-- Pie de Página (ahora ocupa todo el ancho) -->
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
                            <a href="#" class="me-3"><i class="fab fa-facebook-f fa-2x text-white"></i></a>
                            <a href="#" class="me-3"><i class="fab fa-instagram fa-2x text-white"></i></a>
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
</body>
</html>