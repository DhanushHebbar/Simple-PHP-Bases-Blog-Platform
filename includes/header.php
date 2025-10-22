<?php
// Define the base URL relative to the server root for links.
$base_url = '/'; // Assuming your site is at http://domain.com/

// Recommended categories for the dynamic list
$nav_categories = ["Web Dev", "PHP", "MySQL", "Security"]; 

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP Based Blog Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fb; }
        /* Style adjustments for better appearance */
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }

        /* FIX: Ensure the main article list uses the correct grid structure */
        .grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr)); /* Default to 1 column on mobile */
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)); /* 2 columns on small screens */
            }
        }
        @media (min-width: 1024px) {
            .grid {
                grid-template-columns: repeat(3, minmax(0, 1fr)); /* 3 columns on large screens (Tailwind default) */
            }
        }
    </style>
</head>
<body>

<nav class="bg-indigo-700 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-white text-xl font-bold rounded-lg p-2 transition">
                        Simple PHP Based Blog Platform
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-2 sm:space-x-4 nav-links">
                <?php if (is_logged_in()): ?>
                    <a href="admin.php" class="text-white hover:bg-indigo-600 px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition whitespace-nowrap">
                        Post / Admin Panel
                    </a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition whitespace-nowrap">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-white hover:bg-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition whitespace-nowrap">
                        Login / Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="py-10">