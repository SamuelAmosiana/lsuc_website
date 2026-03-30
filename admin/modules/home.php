<?php
// Home Page Management Module
$success = $_GET['success'] ?? '';
$active_tab = $_GET['tab'] ?? 'hero';
?>

<div class="module-container">
    <div class="page-header">
        <h1><i class="fas fa-home"></i> Home Page Management</h1>
        <p>Edit and manage your homepage content without coding</p>
    </div>
    
    <?php if ($success): ?>
        <div class="toast success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Navigation Tabs -->
    <div class="module-tabs">
        <a href="?page=home&tab=hero" class="tab <?php echo $active_tab === 'hero' ? 'active' : ''; ?>">
            <i class="fas fa-image"></i> Hero Section
        </a>
        <a href="?page=home&tab=values" class="tab <?php echo $active_tab === 'values' ? 'active' : ''; ?>">
            <i class="fas fa-star"></i> Core Values
        </a>
        <a href="?page=home&tab=gallery" class="tab <?php echo $active_tab === 'gallery' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i> Campus Gallery
        </a>
        <a href="../index.html#home" target="_blank" class="tab">
            <i class="fas fa-external-link-alt"></i> View Site
        </a>
    </div>
    
    <?php
    switch ($active_tab) {
        case 'values':
            include __DIR__ . '/home_values.php';
            break;
        case 'gallery':
            include __DIR__ . '/home_gallery.php';
            break;
        default:
            include __DIR__ . '/home_hero.php';
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

.preview-section {
    background: var(--gray-100);
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.preview-label {
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.value-item {
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-orange), var(--light-orange));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
}

.value-content {
    flex: 1;
}

.value-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.gallery-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gallery-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.gallery-item-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
    padding: 15px;
}

.gallery-item-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 5px;
}

.btn-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-icon.edit {
    background: white;
    color: var(--primary-green);
}

.btn-icon.delete {
    background: #dc3545;
    color: white;
}

.btn-icon:hover {
    transform: scale(1.1);
}
</style>
