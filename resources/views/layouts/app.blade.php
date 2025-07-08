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

    <!-- Custom CSS (if needed) -->
    <style>
        /* Sidebar Toggle */
        #wrapper.toggled #sidebar-wrapper {
            margin-left: -250px;  /* Hide the sidebar completely */
            width: 0;  /* Reduce width to 0 when hidden */
        }

        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
        }

        #page-content-wrapper {
            transition: margin-left 0.3s ease, width 0.3s ease;
            padding-left: 250px;
        }

        #wrapper.toggled #page-content-wrapper {
            padding-left: 0;  /* Remove padding when the sidebar is hidden */
            width: 100%;  /* Allow the content to use full width */
        }

        /* Optional: Better spacing for the navbar */
        .navbar {
            padding: 10px 20px;
        }

        /* Sidebar Heading */
        .sidebar-heading h3 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Customize the look of the links */
        .list-group-item {
            border: none;
            background-color: transparent;
        }

        .list-group-item:hover {
            background-color: #555;
            cursor: pointer;
        }

        /* Styling for the content area */
        .container {
            max-width: 1200px;
        }

        /* Spacing between icons and text */
        .list-group-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="bg-light">

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white p-4" id="sidebar-wrapper">
            <div class="sidebar-heading text-center">
                <h3>API Docs</h3>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('docs.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="{{ url('authentication') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-right-to-bracket"></i> Authentication
                </a>
                <a href="{{ url('get-districts') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-location-dot"></i> Get Districts
                </a>
                <a href="{{ url('create-districts') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-location-dot"></i> Create Districts
                </a>
                <a href="{{ url('single-districts') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-location-dot"></i> Single Districts
                </a>
                <a href="{{ url('update-districts') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-location-dot"></i> Update Districts
                </a>


                <!-- Seat Plans Section -->
                <a href="{{ url('/docs/seat-plans') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Seat Plans
                </a>
                <a href="{{ url('/docs/seat-plans/create') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Create Seat Plans
                </a>
                <a href="{{ url('/docs/seat-plans/single') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Single Seat Plan
                </a>
                <a href="{{ url('/docs/seat-plans/update') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Update Seat Plan
                </a>

                <!-- Seats Section -->
                <a href="{{ url('/docs/seats/create') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Create Seats
                </a>
                <a href="{{ url('/docs/seats/update') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Update Seats
                </a>
                <a href="{{ url('/docs/seats/delete') }}" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa-solid fa-chair"></i> Delete Seats
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <button class="btn btn-primary" id="menu-toggle">Toggle Menu</button>
            </nav>
            <div class="container mt-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybpa5b+Y2KrlB2fdqzO4pE5P/J6X01H+14jGFyFZzGhwkpg6o" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0l1b0hfQ8b5pF/s1V1xQIXHzVjTmt9P9k9H3pxuLgM4g6qb5" crossorigin="anonymous"></script>

    <!-- Custom JS to toggle sidebar -->
    <script>
        document.getElementById("menu-toggle").addEventListener("click", function() {
            document.getElementById("wrapper").classList.toggle("toggled");
        });
    </script>
</body>
</html>
