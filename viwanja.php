<?php
$host="localhost";
$user="root";
$password="";
$database="lvms_db";

$conn=new mysqli($host,$user,$password,$database);

if($conn->connect_error){
    die("Connection Failed: " . $conn->connect_error);
}

// ==========================================
// SEHEMU YA UKURASA WA FULL DETAILS (DETAILED VIEW)
// ==========================================
if (isset($_GET['view_land_id'])) {
    $land_id = intval($_GET['view_land_id']);
    
    // Vuta taarifa zote za kiwanja husika pamoja na muuzaji wake
    $detail_query = mysqli_query($conn, "
        SELECT l.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
        FROM lands l 
        JOIN users u ON l.seller_id = u.id 
        WHERE l.id = $land_id
    ");
    
    if (mysqli_num_rows($detail_query) > 0) {
        $land = mysqli_fetch_assoc($detail_query);
        $img = (!empty($land['image_path'])) ? $land['image_path'] : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80';
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo htmlspecialchars($land['title']); ?> - Details</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
            <style>
                body { background: #f5f7fb; font-family: 'Segoe UI', sans-serif; }
                .navbar { background: #003366; }
                .navbar-brand { font-size: 25px; font-weight: bold; color: white !important; }
                .detail-header { background: linear-gradient(to right, #003366, #0055a5); color: white; padding: 40px 0; }
                .utility-badge { font-size: 1rem; padding: 8px 16px; border-radius: 30px; }
            </style>
        </head>
        <body>
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg sticky-top">
                <div class="container">
                    <a class="navbar-brand" href="viwanja.php"><i class="fa fa-map-location-dot"></i> LVMS</a>
                    <a href="viwanja.php" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="fa fa-arrow-left me-2"></i>Rudi Nyumbani</a>
                </div>
            </nav>

            <!-- Header -->
            <div class="detail-header shadow-sm">
                <div class="container">
                    <span class="badge bg-warning text-dark text-uppercase fw-bold px-3 py-2 mb-2"><?php echo $land['zone']; ?> | <?php echo $land['type']; ?></span>
                    <h1 class="fw-bold"><?php echo htmlspecialchars($land['title']); ?></h1>
                    <p class="lead mb-0"><i class="fa fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($land['location']); ?> (Plot No: <?php echo htmlspecialchars($land['plot_number']); ?>)</p>
                </div>
            </div>

            <!-- Content -->
            <div class="container my-5">
                <div class="row g-4">
                    <!-- Image and Utilities -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <img src="<?php echo $img; ?>" class="img-fluid w-100" style="max-height: 450px; object-fit: cover;" alt="Land Image">
                        </div>
                        
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h4 class="fw-bold text-dark mb-3"><i class="fa fa-info-circle text-primary me-2"></i>Maelezo ya Kiwanja</h4>
                            <p class="text-secondary"><?php echo !empty($land['description']) ? nl2br(htmlspecialchars($land['description'])) : 'Hakuna maelezo ya ziada yaliyowekwa.'; ?></p>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <h4 class="fw-bold text-dark mb-3"><i class="fa fa-plug text-primary me-2"></i>Miundombinu na Huduma</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge utility-badge <?php echo $land['has_electricity'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <i class="fa fa-bolt me-1"></i> Umeme: <?php echo $land['has_electricity'] ? 'Upo' : 'Hakuna'; ?>
                                </span>
                                <span class="badge utility-badge <?php echo $land['has_water'] ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <i class="fa fa-droplet me-1"></i> Maji: <?php echo $land['has_water'] ? 'Yapo' : 'Hakuna'; ?>
                                </span>
                                <span class="badge utility-badge <?php echo $land['has_road'] ? 'bg-info text-white' : 'bg-secondary'; ?>">
                                    <i class="fa fa-road me-1"></i> Barabara: <?php echo $land['has_road'] ? 'Ipo' : 'Hakuna'; ?>
                                </span>
                                <span class="badge utility-badge <?php echo $land['has_sewage'] ? 'bg-dark' : 'bg-secondary'; ?>">
                                    <i class="fa fa-trowel-bricks me-1"></i> Mfumo wa Taka: <?php echo $land['has_sewage'] ? 'Upo' : 'Hakuna'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Side Information -->
                    <div class="col-lg-5">
                        <!-- Valuation and Status -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h5 class="text-muted small text-uppercase fw-bold">Hali ya Kiwanja</h5>
                            <span class="fs-4 fw-bold text-uppercase d-block mb-3 text-primary"><?php echo $land['status']; ?></span>
                            
                            <hr>
                            
                            <h5 class="text-muted small text-uppercase fw-bold">Thamani / Bei ya Kiwanja</h5>
                            <h2 class="text-success fw-bold">
                                <?php echo $land['valuation_amount'] ? 'TZS ' . number_format($land['valuation_amount'], 2) : 'Bado Hakijathaminiwa'; ?>
                            </h2>
                            
                            <div class="mt-3 bg-light p-3 rounded-3 small text-secondary">
                                <p class="mb-1"><strong>Ukubwa:</strong> <?php echo $land['area']; ?> SQM</p>
                                <p class="mb-1"><strong>Njia ya Barabara:</strong> <?php echo ucfirst($land['road_access']); ?> Access</p>
                                <p class="mb-0"><strong>Tarehe ya Usajili:</strong> <?php echo date('d-M-Y', strtotime($land['created_at'])); ?></p>
                            </div>
                        </div>

                        <!-- Seller Details -->
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                            <div class="mb-3">
                                <i class="fa-solid fa-user-tie fs-1 text-secondary bg-light p-3 rounded-circle"></i>
                            </div>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($land['seller_name']); ?></h5>
                            <p class="text-muted small">Miliki / Muuzaji wa Kiwanja</p>
                            <hr>
                            <div class="text-start">
                                <p class="mb-2"><i class="fa fa-phone text-success me-2"></i> <strong>Simu:</strong> <?php echo !empty($land['seller_phone']) ? htmlspecialchars($land['seller_phone']) : 'Haipatikani'; ?></p>
                                <p class="mb-0"><i class="fa fa-envelope text-primary me-2"></i> <strong>Email:</strong> <?php echo htmlspecialchars($land['seller_email']); ?></p>
                            </div>
                            <a href="index.html" class="btn btn-success w-100 mt-4 rounded-pill fw-bold">Nunua sasa</a>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="bg-dark text-white text-center py-4 border-top border-secondary mt-5">
                <div class="container"><p class="mb-0 small">&copy; <?php echo date('Y'); ?> LVMS. All Rights Reserved.</p></div>
            </footer>
        </body>
        </html>
        <?php
        exit; // Inazuia ukurasa mkuu usifunguke chini yake
    }
}

// ==========================================
// SEHEMU YA DATA ZA KAWAIDA ZA HOME PAGE
// ==========================================
$lands_query = mysqli_query($conn,"SELECT COUNT(*) total FROM lands");
$lands = mysqli_fetch_assoc($lands_query);

$payments_query = mysqli_query($conn,"SELECT COUNT(*) total FROM payments");
$payments = mysqli_fetch_assoc($payments_query);

$users_query = mysqli_query($conn,"SELECT COUNT(*) total FROM users");
$users = mysqli_fetch_assoc($users_query);

$all_lands_query = mysqli_query($conn, "SELECT l.*, u.name as seller_name FROM lands l JOIN users u ON l.seller_id = u.id ORDER BY l.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Land Valuation Management System</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.5.2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: #003366;
        }
        .navbar-brand {
            font-size: 25px;
            font-weight: bold;
            color: white !important;
        }
        .nav-link {
            color: white !important;
            margin-left: 15px;
        }
        .hero {
            height: 70vh;
            background: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)),
            url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero h1 {
            font-size: 50px;
            font-weight: bold;
        }
        .hero p {
            font-size: 20px;
        }
        .btn-main {
            background: #ffc107;
            border: none;
            padding: 12px 35px;
            font-size: 18px;
            font-weight: bold;
            color: #000;
            border-radius: 30px;
            transition: 0.3s;
        }
        .btn-main:hover {
            background: #e0a800;
            color: #000;
        }
        .card-box {
            border: none;
            border-radius: 20px;
            box-shadow: 0px 10px 30px rgba(0,0,0,.15);
            transition: .4s;
        }
        .card-box:hover {
            transform: translateY(-10px);
        }
        .counter {
            font-size: 40px;
            font-weight: bold;
            color: #003366;
        }
        /* Custom Styles for Slider & Cards */
        .carousel-item {
            height: 500px;
        }
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        .carousel-caption {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 20px;
            bottom: 40px;
        }
        .land-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: 0.3s;
            overflow: hidden;
        }
        .land-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .land-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fa fa-map-location-dot"></i> LVMS
        </a>
        <button class="navbar-toggler bg-white" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#slider-section">Featured Lands</a></li>
                <li class="nav-item"><a class="nav-link" href="#lands">All Lands</a></li>
                <li class="nav-item">
                    <a class="btn btn-warning ms-lg-3 px-4 fw-bold rounded-pill" href="index.html">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container px-3">
        <h1>Land Valuation Management System</h1>
        <p class="my-3">Modern Digital Platform for Land Registration, Valuation and Payment</p>
        <a href="#lands" class="btn btn-main mt-2">Explore Lands</a>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light" id="about">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=80" class="img-fluid rounded shadow w-100">
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold text-success">Welcome to Land Valuation Management System</h2>
                <p class="mt-4 text-muted">
                    The Land Valuation Management System (LVMS) is a modern digital platform designed to simplify land registration, valuation, ownership verification, and payment management. The system enables citizens and government officers to access land information quickly, securely, and efficiently.
                </p>
                <p class="text-muted">
                    Our goal is to improve transparency, reduce paperwork, and provide reliable digital services for land management.
                </p>
                <a href="#lands" class="btn btn-success btn-lg mt-3 rounded-pill px-4">View Available Lands</a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Our Services</h2>
            <p class="text-muted">Everything you need in one place</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png" width="80">
                <h4 class="mt-3 fw-bold">Land Registration</h4>
                <p class="text-muted">Register new land quickly and securely.</p>
            </div>
            <div class="col-md-4 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="80">
                <h4 class="mt-3 fw-bold">Land Valuation</h4>
                <p class="text-muted">Professional valuation based on government standards.</p>
            </div>
            <div class="col-md-4 text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/2331/2331941.png" width="80">
                <h4 class="mt-3 fw-bold">Online Payments</h4>
                <p class="text-muted">Pay valuation fees securely through the system.</p>
            </div>
        </div>
    </div>
</section>

<!-- PICHA ZINAZOTEMBEA (SLIDER/CAROUSEL) KUTOKA DATABASE -->
<section class="py-5 bg-secondary-subtle" id="slider-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark"><i class="fa-solid fa-images text-primary me-2"></i>Viwanja Vilivyotembelewa Zaidi</h2>
            <p class="text-muted">Tazama picha zinazotembea za viwanja vilivyopo kwenye database yetu kwa sasa.</p>
        </div>

        <?php
        $slider_query = mysqli_query($conn, "SELECT * FROM lands ORDER BY id DESC LIMIT 5");
        if (mysqli_num_rows($slider_query) > 0): 
        ?>
        <div id="landCarousel" class="carousel slide shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php 
                $i = 0;
                while($row = mysqli_fetch_assoc($slider_query)) {
                    $active = ($i == 0) ? 'class="active" aria-current="true"' : '';
                    echo '<button type="button" data-bs-target="#landCarousel" data-bs-slide-to="'.$i.'" '.$active.' aria-label="Slide '.($i+1).'"></button>';
                    $i++;
                }
                mysqli_data_seek($slider_query, 0);
                ?>
            </div>
            
            <div class="carousel-inner">
                <?php 
                $active_class = "active";
                while($land = mysqli_fetch_assoc($slider_query)):
                    $img = (!empty($land['image_path'])) ? $land['image_path'] : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80';
                ?>
                <div class="carousel-item <?php echo $active_class; ?>">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($land['title']); ?>">
                    <div class="carousel-caption d-none d-md-block text-start">
                        <span class="badge bg-warning text-dark mb-2 px-3 py-2 fw-bold text-uppercase"><?php echo $land['zone']; ?> | <?php echo $land['type']; ?></span>
                        <h3 class="fw-bold"><?php echo htmlspecialchars($land['title']); ?></h3>
                        <p class="mb-2"><i class="fa fa-map-marker-alt text-danger me-2"></i><?php echo htmlspecialchars($land['location']); ?> | <strong>Plot No:</strong> <?php echo htmlspecialchars($land['plot_number']); ?></p>
                        <h4 class="text-success fw-bold mb-0">
                            <?php echo $land['valuation_amount'] ? 'TZS ' . number_format($land['valuation_amount'], 2) : 'Bado Hakijathaminiwa'; ?>
                        </h4>
                    </div>
                </div>
                <?php 
                $active_class = ""; 
                endwhile; 
                ?>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#landCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#landCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php else: ?>
            <div class="alert alert-info text-center">Hakuna viwanja vilivyopatikana kwenye mfumo kwa sasa.</div>
        <?php endif; ?>
    </div>
</section>

<!-- ORODHA YA VIWANJA VYOTE CHINI YA UKURASA -->
<section class="py-5 bg-light" id="lands">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Orodha Kamili ya Viwanja Real-time</h2>
            <p class="text-muted">Hapa kuna viwanja vyote vilivyopo kwenye mfumo wetu wa `lvms_db` hivi sasa.</p>
        </div>

        <div class="row g-4">
            <?php 
            if(mysqli_num_rows($all_lands_query) > 0):
                while($land = mysqli_fetch_assoc($all_lands_query)):
                    $img = (!empty($land['image_path'])) ? $land['image_path'] : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=600&q=80';
                    
                    $status_bg = 'bg-warning text-dark';
                    if($land['status'] == 'valued') $status_bg = 'bg-info text-white';
                    if($land['status'] == 'sold') $status_bg = 'bg-success text-white';
                    if($land['status'] == 'rejected') $status_bg = 'bg-danger text-white';
            ?>
            <div class="col-md-4">
                <div class="card land-card h-100 position-relative bg-white">
                    <span class="land-badge badge <?php echo $status_bg; ?>"><?php echo $land['status']; ?></span>
                    <div style="height: 220px; overflow: hidden;">
                        <img src="<?php echo $img; ?>" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($land['title']); ?>">
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-dark text-truncate mb-1"><?php echo htmlspecialchars($land['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fa fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($land['location']); ?></p>
                            
                            <div class="mb-2">
                                <span class="badge bg-secondary-subtle text-dark border"><?php echo htmlspecialchars($land['type']); ?></span>
                                <span class="badge bg-secondary-subtle text-dark border"><?php echo $land['area']; ?> SQM</span>
                                <span class="badge bg-secondary-subtle text-dark border">Zone: <?php echo htmlspecialchars($land['zone']); ?></span>
                            </div>
                            
                            <p class="card-text text-secondary small mb-3 text-truncate">
                                <?php echo !empty($land['description']) ? htmlspecialchars($land['description']) : 'Hakuna maelezo ya ziada yaliyowekwa kwenye kiwanja hiki.'; ?>
                            </p>
                        </div>
                        
                        <div class="border-top pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 11px;">Thamani / Bei:</small>
                                    <span class="text-success fw-bold fs-5">
                                        <?php echo $land['valuation_amount'] ? 'TZS ' . number_format($land['valuation_amount'], 0) : '<span class="text-warning fs-6">Subiri Tathmini</span>'; ?>
                                    </span>
                                </div>
                                <!-- Kitufe kimebadilishwa kutoka alert kuwa Link ya kufungua Full Details kwenye Page hiyo hiyo -->
                                <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="viwanja.php?view_land_id=<?php echo $land['id']; ?>">
                                    Angalia
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else: 
            ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Hakuna viwanja vilivyosajiliwa bado.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 border-top border-secondary">
    <div class="container">
        <p class="mb-0 small">&copy; <?php echo date('Y'); ?> Land Valuation Management System (LVMS). Haki zote zimehifadhiwa.</p>
    </div>
</footer>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>