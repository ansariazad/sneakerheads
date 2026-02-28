<?php
require_once 'auth.php';
require_once 'functions.php';

// Get current user and counts
$currentUser = Auth::isLoggedIn() ? Auth::getCurrentUser() : null;
$cartCount = $currentUser ? getCartItemsCount($currentUser['user_id']) : 0;
$notificationCount = $currentUser ? getUnreadNotificationsCount($currentUser['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (isset($extraStyles)): ?>
        <?php echo $extraStyles; ?>
    <?php endif; ?>
    <style>
        /* Dropdown menu styles */
        .user-dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .dropdown-toggle:hover {
            background-color: var(--bg-light);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 220px;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            padding: 10px 0;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: var(--text-color);
            transition: all 0.2s ease;
        }
        
        .dropdown-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .dropdown-menu a:hover {
            background-color: var(--bg-light);
            padding-left: 20px;
        }
        
        /* Burger menu for mobile */
        .burger-menu {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 20px;
            cursor: pointer;
            z-index: 1001;
        }
        
        .burger-menu span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: var(--text-color);
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .burger-menu.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        
        .burger-menu.active span:nth-child(2) {
            opacity: 0;
        }
        
        .burger-menu.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        
        @media (max-width: 768px) {
            .main-nav {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100vh;
                background-color: var(--bg-color);
                z-index: 1000;
                padding: 80px 20px 20px;
                overflow-y: auto;
            }
            
            .main-nav.show {
                display: block;
            }
            
            .main-nav ul {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .main-nav li {
                margin: 10px 0;
                width: 100%;
            }
            
            .burger-menu {
                display: flex;
            }
            
            .mobile-menu-toggle {
                display: none;
            }
            
            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                margin-top: 10px;
                padding-left: 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="assets/css/footer-nav.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>">
                        <h1>Sneakerheads</h1>
                    </a>
                </div>
                <div class="search-bar">
                    <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                        <input type="text" name="q" placeholder="Search for sneakers...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <?php if (Auth::isLoggedIn()): ?>
                            <li class="notification-icon">
                                <a href="<?php echo SITE_URL; ?>/notifications.php">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($notificationCount > 0): ?>
                                        <span class="badge"><?php echo $notificationCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="cart-icon">
                                <a href="<?php echo SITE_URL; ?>/cart.php">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?php if ($cartCount > 0): ?>
                                        <span class="badge"><?php echo $cartCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="user-dropdown">
                                <div class="dropdown-toggle" id="userDropdownToggle">
                                    <i class="fas fa-user"></i>
                                    <span class="username"><?php echo $currentUser['username']; ?></span>
                                    <i class="fas fa-chevron-down ml-2"></i>
                                </div>
                                <div class="dropdown-menu" id="userDropdownMenu">
                                    <a href="<?php echo SITE_URL; ?>/account.php">
                                        <i class="fas fa-user-circle"></i> My Account
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/my-orders.php">
                                        <i class="fas fa-shopping-bag"></i> My Orders
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/wishlist.php">
                                        <i class="fas fa-heart"></i> My Wishlist
                                    </a>
                                    <?php if (Auth::canSell()): ?>
                                        <a href="<?php echo SITE_URL; ?>/seller/dashboard.php">
                                            <i class="fas fa-store"></i> Seller Dashboard
                                        </a>
                                    <?php endif; ?>
                                    <?php if (Auth::isSuperAdmin()): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                                            <i class="fas fa-user-shield"></i> Admin Dashboard
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li><a href="<?php echo SITE_URL; ?>/login.php">Login</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="burger-menu" id="burgerMenu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>
    <div class="content-wrapper">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // User dropdown functionality
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    
    if (userDropdownToggle && userDropdownMenu) {
        // Toggle dropdown on click
        userDropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdownToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Burger menu toggle
    const burgerMenu = document.getElementById('burgerMenu');
    const mainNav = document.querySelector('.main-nav');
    
    if (burgerMenu && mainNav) {
        burgerMenu.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('show');
            document.body.classList.toggle('menu-open');
        });
        
        // Close mobile menu when clicking a link
        const navLinks = mainNav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Check if we're in mobile view
                if (window.innerWidth <= 768) {
                    burgerMenu.classList.remove('active');
                    mainNav.classList.remove('show');
                    document.body.classList.remove('menu-open');
                }
            });
        });
    }
});
</script>

