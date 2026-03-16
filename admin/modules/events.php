<?php
// Events Management Module
$action = $_GET['action'] ?? 'list';
$success = $_GET['success'] ?? '';
?>

<div class="module-container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Events Management</h1>
        <p>Manage news, events, and announcements</p>
    </div>
    
    <?php if ($success): ?>
        <div class="toast success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Navigation Tabs -->
    <div class="module-tabs">
        <a href="?page=events&action=list" class="tab <?php echo $action === 'list' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> All Events
        </a>
        <a href="?page=events&action=add" class="tab <?php echo $action === 'add' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> Add New Event
        </a>
        <a href="../index.html#news" target="_blank" class="tab">
            <i class="fas fa-external-link-alt"></i> View on Site
        </a>
    </div>
    
    <?php
    switch ($action) {
        case 'add':
            include __DIR__ . '/events_add.php';
            break;
        case 'edit':
            include __DIR__ . '/events_edit.php';
            break;
        case 'delete':
            include __DIR__ . '/events_delete.php';
            break;
        default:
            include __DIR__ . '/events_list.php';
    }
    ?>
</div>

<style>
.module-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.module-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid var(--gray-200);
    padding-bottom: 15px;
}

.tab {
    padding: 12px 25px;
    background: var(--gray-100);
    color: var(--gray-800);
    text-decoration: none;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab:hover {
    background: var(--gray-200);
}

.tab.active {
    background: var(--primary-green);
    color: white;
}

.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-btn {
    padding: 8px 16px;
    border: 2px solid var(--gray-200);
    background: white;
    color: var(--gray-800);
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary-green);
    color: white;
    border-color: var(--primary-green);
}

.search-box {
    position: relative;
}

.search-box input {
    padding: 8px 15px 8px 35px;
    border: 2px solid var(--gray-200);
    border-radius: 20px;
    width: 250px;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-600);
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.event-card {
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.event-card:hover {
    border-color: var(--primary-orange);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.event-card.featured {
    border-color: var(--primary-orange);
    position: relative;
}

.event-card.featured::before {
    content: '⭐ Featured';
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--primary-orange);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    z-index: 1;
}

.event-image {
    height: 200px;
    background: var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-content {
    padding: 20px;
}

.event-category {
    display: inline-block;
    padding: 5px 12px;
    background: var(--gray-100);
    color: var(--gray-600);
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 10px;
}

.event-category.Latest\\ News { background: #e3f2fd; color: #1976d2; }
.event-category.Upcoming\\ Event { background: #e8f5e9; color: #388e3c; }
.event-category.Past\\ Event { background: #fce4ec; color: #c2185b; }
.event-category.Job\\ Vacancy { background: #fff3e0; color: #f57c00; }

.event-title {
    font-size: 18px;
    color: var(--gray-800);
    margin: 0 0 10px 0;
}

.event-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: var(--gray-600);
    margin-bottom: 15px;
}

.event-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--gray-200);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--gray-600);
}

.empty-state i {
    font-size: 80px;
    color: var(--gray-300);
    margin-bottom: 20px;
}
</style>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const eventCards = document.querySelectorAll('.event-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.getAttribute('data-filter');
            
            eventCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
