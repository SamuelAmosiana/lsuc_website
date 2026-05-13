<div class="dashboard-home">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard Overview</h1>
        <p>Welcome to the LSUC Content Management System</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total_events']; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-details">
                <span class="stat-detail"><i class="fas fa-newspaper"></i> <?php echo $stats['latest_news']; ?> Latest</span>
                <span class="stat-detail"><i class="fas fa-clock"></i> <?php echo $stats['upcoming_events']; ?> Upcoming</span>
                <span class="stat-detail"><i class="fas fa-history"></i> <?php echo $stats['past_events']; ?> Past</span>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['faqs']; ?></div>
                <div class="stat-label">FAQs</div>
            </div>
            <a href="?page=faqs" class="stat-link">Manage FAQs →</a>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['gallery_images']; ?></div>
                <div class="stat-label">Gallery Images</div>
            </div>
            <a href="?page=gallery" class="stat-link">Manage Gallery →</a>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['downloads']; ?></div>
                <div class="stat-label">Downloads</div>
            </div>
            <a href="?page=downloads" class="stat-link">Manage Files →</a>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        <div class="action-buttons">
            <a href="?page=events&action=add" class="action-btn primary">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Event</span>
            </a>
            <a href="?page=faqs&action=add" class="action-btn success">
                <i class="fas fa-plus-circle"></i>
                <span>Add New FAQ</span>
            </a>
            <a href="?page=gallery&action=upload" class="action-btn info">
                <i class="fas fa-upload"></i>
                <span>Upload Images</span>
            </a>
            <a href="?page=downloads&action=upload" class="action-btn warning">
                <i class="fas fa-file-upload"></i>
                <span>Upload Document</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="activity-section">
        <h2><i class="fas fa-history"></i> Recent Activity</h2>
        <div class="activity-list">
            <?php if (empty($recent_activity)): ?>
                <div class="no-activity">
                    <i class="fas fa-inbox"></i>
                    <p>No recent activity recorded</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item <?php echo $activity['status']; ?>">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo $activity['status'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">
                                <strong><?php echo htmlspecialchars($activity['username']); ?></strong>
                                <?php echo htmlspecialchars($activity['action']); ?>
                            </div>
                            <div class="activity-meta">
                                <span><i class="fas fa-clock"></i> <?php echo time_ago($activity['timestamp']); ?></span>
                                <span><i class="fas fa-globe"></i> <?php echo htmlspecialchars($activity['ip_address']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- System Info -->
    <div class="system-info">
        <h2><i class="fas fa-info-circle"></i> System Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>PHP Version:</label>
                <span><?php echo phpversion(); ?></span>
            </div>
            <div class="info-item">
                <label>Server Time:</label>
                <span><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="info-item">
                <label>Session Expires:</label>
                <span id="session-timer">30:00</span>
            </div>
            <div class="info-item">
                <label>Storage:</label>
                <span>JSON Files</span>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-home { padding: 20px; }
.page-header { margin-bottom: 30px; }
.page-header h1 { color: #1e5a3a; margin: 0 0 10px 0; font-size: 28px; }
.page-header p { color: #666; margin: 0; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-card.primary { border-left: 4px solid #ff8c00; }
.stat-card.success { border-left: 4px solid #2e8b57; }
.stat-card.info { border-left: 4px solid #4a90e2; }
.stat-card.warning { border-left: 4px solid #f5a623; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-card.primary .stat-icon { background: rgba(255, 140, 0, 0.1); color: #ff8c00; }
.stat-card.success .stat-icon { background: rgba(46, 139, 87, 0.1); color: #2e8b57; }
.stat-card.info .stat-icon { background: rgba(74, 144, 226, 0.1); color: #4a90e2; }
.stat-card.warning .stat-icon { background: rgba(245, 166, 35, 0.1); color: #f5a623; }

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-details {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.stat-detail {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat-link {
    display: inline-block;
    margin-top: 15px;
    color: #2e8b57;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.3s ease;
}

.stat-link:hover {
    color: #1e5a3a;
}

.quick-actions {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.quick-actions h2 {
    color: #1e5a3a;
    margin: 0 0 20px 0;
    font-size: 20px;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    color: white;
}

.action-btn.primary { background: linear-gradient(135deg, #ff8c00, #ffb347); }
.action-btn.success { background: linear-gradient(135deg, #2e8b57, #90ee90); }
.action-btn.info { background: linear-gradient(135deg, #4a90e2, #87ceeb); }
.action-btn.warning { background: linear-gradient(135deg, #f5a623, #ffd700); }

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.activity-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.activity-section h2 {
    color: #1e5a3a;
    margin: 0 0 20px 0;
    font-size: 20px;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background 0.3s ease;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.activity-item.success .activity-icon {
    background: rgba(46, 139, 87, 0.1);
    color: #2e8b57;
}

.activity-item.failed .activity-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: #333;
    margin-bottom: 5px;
}

.activity-meta {
    font-size: 12px;
    color: #666;
    display: flex;
    gap: 15px;
}

.no-activity {
    text-align: center;
    padding: 40px;
    color: #999;
}

.no-activity i {
    font-size: 48px;
    margin-bottom: 15px;
}

.system-info {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.system-info h2 {
    color: #1e5a3a;
    margin: 0 0 20px 0;
    font-size: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 14px;
}

.info-item span {
    color: #333;
    font-weight: 500;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

#session-timer {
    color: #ff8c00;
    animation: pulse 2s infinite;
}
</style>

<?php
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}
?>
