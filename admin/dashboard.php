<?php
session_start();
require_once __DIR__ . '/../config/site_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Check session timeout (30 minutes)
if (time() - ($_SESSION['login_time'] ?? 0) > 1800) {
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}

// Update login time
$_SESSION['login_time'] = time();

$current_page = $_GET['page'] ?? 'dashboard';
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Load statistics
$stats = loadStatistics();

function loadStatistics() {
    $data_dir = __DIR__ . '/data/';
    
    // Count events
    $events_file = $data_dir . 'events.json';
    $events = [];
    if (file_exists($events_file)) {
        $events = json_decode(file_get_contents($events_file), true) ?: [];
    } else {
        // Migrate from localStorage format if needed
        $events_file_old = __DIR__ . '/../js/news-events-manager.js';
        // For now, use empty array
    }
    
    $latest_news = count(array_filter($events, fn($e) => $e['category'] === 'Latest News'));
    $upcoming_events = count(array_filter($events, fn($e) => $e['category'] === 'Upcoming Event'));
    $past_events = count(array_filter($events, fn($e) => $e['category'] === 'Past Event'));
    
    // Count FAQs
    $faqs_file = $data_dir . 'faqs.json';
    $faqs_count = 0;
    if (file_exists($faqs_file)) {
        $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
        $faqs_count = count($faqs);
    }
    
    // Count gallery images
    $gallery_file = $data_dir . 'gallery.json';
    $gallery_count = 0;
    if (file_exists($gallery_file)) {
        $gallery = json_decode(file_get_contents($gallery_file), true) ?: [];
        $gallery_count = count($gallery);
    }
    
    // Count downloads
    $downloads_file = $data_dir . 'downloads.json';
    $downloads_count = 0;
    if (file_exists($downloads_file)) {
        $downloads = json_decode(file_get_contents($downloads_file), true) ?: [];
        $downloads_count = count($downloads);
    }
    
    return [
        'total_events' => count($events),
        'latest_news' => $latest_news,
        'upcoming_events' => $upcoming_events,
        'past_events' => $past_events,
        'faqs' => $faqs_count,
        'gallery_images' => $gallery_count,
        'downloads' => $downloads_count
    ];
}

function getRecentActivity() {
    $log_file = __DIR__ . '/data/activity_log.json';
    if (!file_exists($log_file)) {
        return [];
    }
    
    $logs = json_decode(file_get_contents($log_file), true) ?: [];
    return array_slice(array_reverse($logs), 0, 10);
}

$recent_activity = getRecentActivity();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>LSUC Admin</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="nav-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="nav-section">Content Management</div>
            
            <a href="?page=home" class="nav-item <?php echo $current_page === 'home' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Home Page</span>
            </a>
            
            <a href="?page=about" class="nav-item <?php echo $current_page === 'about' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i>
                <span>About Section</span>
            </a>
            
            <a href="?page=events" class="nav-item <?php echo $current_page === 'events' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
            
            <a href="?page=schools" class="nav-item <?php echo $current_page === 'schools' ? 'active' : ''; ?>">
                <i class="fas fa-school"></i>
                <span>Schools & Programs</span>
            </a>
            
            <a href="?page=faqs" class="nav-item <?php echo $current_page === 'faqs' ? 'active' : ''; ?>">
                <i class="fas fa-question-circle"></i>
                <span>FAQs</span>
            </a>
            
            <a href="?page=gallery" class="nav-item <?php echo $current_page === 'gallery' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
            
            <a href="?page=downloads" class="nav-item <?php echo $current_page === 'downloads' ? 'active' : ''; ?>">
                <i class="fas fa-download"></i>
                <span>Downloads</span>
            </a>
            
            <div class="nav-section">Settings</div>
            
            <a href="?page=settings" class="nav-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="admin-info">
                    <div class="admin-name"><?php echo htmlspecialchars($admin_username); ?></div>
                    <div class="admin-status">Online</div>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                    <?php if ($current_page !== 'dashboard'): ?>
                        <i class="fas fa-chevron-right"></i>
                        <span><?php echo ucfirst(htmlspecialchars($current_page)); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="header-right">
                <button class="header-btn" onclick="window.open('../index.html', '_blank')" title="View Site">
                    <i class="fas fa-external-link-alt"></i>
                </button>
                <button class="header-btn" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </header>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php
            // Include page modules
            $module_file = __DIR__ . "/modules/{$current_page}.php";
            
            if ($current_page === 'dashboard') {
                include __DIR__ . '/modules/dashboard_home.php';
            } elseif (file_exists($module_file)) {
                include $module_file;
            } else {
                echo "<div class='error-page'><h2>Page Not Found</h2><p>The requested module does not exist.</p></div>";
            }
            ?>
        </div>
    </main>
    
    <script src="assets/admin_scripts.js"></script>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
        
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
        }
        
        // Auto-save warning
        window.addEventListener('beforeunload', function(e) {
            const unsavedForms = document.querySelectorAll('form[data-unsaved]');
            if (unsavedForms.length > 0) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    </script>
</body>
</html>
