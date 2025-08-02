<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap JS and Popper.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom CSS -->
    <style>
        /* Sidebar Toggle */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -250px;
            width: 0;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        /* Scrollable menu container */
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 80px);
            padding-bottom: 20px;
        }

        /* Custom scrollbar styling */
        .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: #2c3e50;
            border-radius: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: #777;
        }

        /* Firefox scrollbar */
        .sidebar-menu {
            scrollbar-width: thin;
            scrollbar-color: #555 #2c3e50;
        }

        #page-content-wrapper {
            transition: margin-left 0.3s ease, width 0.3s ease;
            padding-left: 250px;
        }

        #wrapper.toggled #page-content-wrapper {
            padding-left: 0;
            width: 100%;
        }

        .navbar {
            padding: 10px 20px;
        }

        /* Sidebar Heading */
        .sidebar-heading {
            flex-shrink: 0;
            padding: 20px 0 10px 0;
        }

        .sidebar-heading h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Menu Items */
        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            color: #fff !important;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-link:hover {
            background-color: #555;
            color: #fff !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: #007bff;
            color: #fff !important;
        }

        /* Dropdown styles */
        .dropdown-menu {
            background-color: #2c3e50;
            border: none;
            border-radius: 8px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2px;
            padding: 8px 0;
            position: static;
            float: none;
            width: 100%;
            margin-top: 0;
            display: none;
            transform: none;
        }

        .dropdown-menu.show {
            display: block !important;
        }

        .dropdown-item {
            color: #fff !important;
            padding: 8px 20px 8px 40px;
            transition: all 0.2s ease;
            border-radius: 6px;
            margin: 2px 8px;
            text-decoration: none;
            display: block;
        }

        .dropdown-item:hover {
            background-color: #495057;
            color: #fff !important;
            transform: translateX(5px);
        }

        .dropdown-item:focus {
            background-color: #495057;
            color: #fff !important;
        }

        /* Dropdown toggle arrow */
        .dropdown-toggle::after {
            content: "▼";
            float: right;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
            margin-left: auto;
        }

        .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* Remove Bootstrap's default dropdown arrow */
        .dropdown-toggle::after {
            border: none;
            vertical-align: 0;
        }

        /* Icons */
        .nav-link i, .dropdown-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 18px;
            text-align: center;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: -250px;
            }

            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                padding-left: 0;
            }

            #wrapper.toggled #page-content-wrapper {
                padding-left: 250px;
            }
        }
    </style>
</head>
<body class="bg-light">

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper">
            <div class="sidebar-heading text-center">
                <h3>API Docs</h3>
            </div>

            <!-- Scrollable menu container -->
            <div class="sidebar-menu">
                <nav class="nav flex-column">
                    <!-- Home -->
                    <div class="nav-item">
                        <a href="{{ route('docs.index') }}" class="nav-link">
                            <span><i class="fas fa-home"></i> Home</span>
                        </a>
                    </div>

                    <!-- Authentication -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-right-to-bracket"></i> Authentication</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('authentication') }}" class="dropdown-item">
                                <i class="fa-solid fa-sign-in-alt"></i> Login
                            </a>
                            <a href="{{ url('docs/logout') }}" class="dropdown-item">
                                <i class="fa-solid fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>

                    <!-- Districts -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-location-dot"></i> Districts</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('get-districts') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Districts
                            </a>
                            <a href="{{ url('create-districts') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Districts
                            </a>
                            <a href="{{ url('single-districts') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Districts
                            </a>
                            <a href="{{ url('update-districts') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Districts
                            </a>
                        </div>
                    </div>

                    <!-- Seat Plans -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-chair"></i> Seat Plans</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/seat-plans') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Seat Plans
                            </a>
                            <a href="{{ url('/docs/seat-plans/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Seat Plans
                            </a>
                            <a href="{{ url('/docs/seat-plans/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Seat Plan
                            </a>
                            <a href="{{ url('/docs/seat-plans/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Seat Plan
                            </a>
                        </div>
                    </div>

                    <!-- Seats -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-chair"></i> Seats</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/seats/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Seats
                            </a>
                            <a href="{{ url('/docs/seats/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Seats
                            </a>
                            <a href="{{ url('/docs/seats/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Seats
                            </a>
                        </div>
                    </div>

                    <!-- Routes -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-route"></i> Routes</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/routes') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Routes
                            </a>
                            <a href="{{ url('/docs/routes/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Routes
                            </a>
                            <a href="{{ url('/docs/routes/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Routes
                            </a>
                            <a href="{{ url('/docs/routes/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Routes
                            </a>
                            <a href="{{ url('/docs/routes/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Routes
                            </a>
                        </div>
                    </div>

                    <!-- Stations -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-building"></i> Stations</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/stations') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Stations
                            </a>
                            <a href="{{ url('/docs/stations/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Station
                            </a>
                            <a href="{{ url('/docs/stations/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Station
                            </a>
                            <a href="{{ url('/docs/stations/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Station
                            </a>
                            <a href="{{ url('/docs/stations/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Station
                            </a>
                        </div>
                    </div>

                    <!-- Schedules -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-calendar-days"></i> Schedules</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/schedules') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Schedules
                            </a>
                            <a href="{{ url('/docs/schedules/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Schedule
                            </a>
                            <a href="{{ url('/docs/schedules/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Schedule
                            </a>
                            <a href="{{ url('/docs/schedules/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Schedule
                            </a>
                            <a href="{{ url('/docs/schedules/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Schedule
                            </a>
                        </div>
                    </div>

                    <!-- Coaches -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-bus"></i> Coaches</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/coaches') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Coaches
                            </a>
                            <a href="{{ url('/docs/coaches/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Coaches
                            </a>
                            <a href="{{ url('/docs/coaches/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Coaches
                            </a>
                            <a href="{{ url('/docs/coaches/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Coaches
                            </a>
                            <a href="{{ url('/docs/coaches/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Coaches
                            </a>
                        </div>
                    </div>

                     <!-- Buses -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-bus"></i> Buses</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/buses') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Buses
                            </a>
                            <a href="{{ url('/docs/buses/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Bus
                            </a>
                            <a href="{{ url('/docs/buses/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Bus
                            </a>
                            <a href="{{ url('/docs/buses/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Bus
                            </a>
                            <a href="{{ url('/docs/buses/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Bus
                            </a>
                        </div>
                    </div>

                    <!-- Fares -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-dollar-sign"></i> Fares</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/fares') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Fares
                            </a>
                            <a href="{{ url('/docs/fares/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Fare
                            </a>
                            <a href="{{ url('/docs/fares/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Fare
                            </a>
                            <a href="{{ url('/docs/fares/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Fare
                            </a>
                            <a href="{{ url('/docs/fares/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Fare
                            </a>
                        </div>
                    </div>

                    <!-- Counters -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-map-location-dot"></i> Counters</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/counters') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Counters
                            </a>
                            <a href="{{ url('/docs/counters/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Counter
                            </a>
                            <a href="{{ url('/docs/counters/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Counter
                            </a>
                            <a href="{{ url('/docs/counters/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Counter
                            </a>
                            <a href="{{ url('/docs/counters/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Counter
                            </a>
                        </div>
                    </div>

                    <!-- Designations -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span><i class="fa-solid fa-user-tie"></i> Designations</span>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ url('/docs/designations') }}" class="dropdown-item">
                                <i class="fa-solid fa-list"></i> Get Designations
                            </a>
                            <a href="{{ url('/docs/designations/create') }}" class="dropdown-item">
                                <i class="fa-solid fa-plus"></i> Create Designation
                            </a>
                            <a href="{{ url('/docs/designations/single') }}" class="dropdown-item">
                                <i class="fa-solid fa-eye"></i> Single Designation
                            </a>
                            <a href="{{ url('/docs/designations/update') }}" class="dropdown-item">
                                <i class="fa-solid fa-edit"></i> Update Designation
                            </a>
                            <a href="{{ url('/docs/designations/delete') }}" class="dropdown-item">
                                <i class="fa-solid fa-trash"></i> Delete Designation
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <button class="btn btn-dark" id="menu-toggle"><i class="fa-solid fa-list"></i></button>
            </nav>
            <div class="container mt-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            document.getElementById("menu-toggle").addEventListener("click", function() {
                document.getElementById("wrapper").classList.toggle("toggled");
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                const href = anchor.getAttribute('href');
                if (href && href !== '#' && href.length > 1) {
                    anchor.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
