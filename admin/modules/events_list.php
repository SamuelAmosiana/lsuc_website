<div class="filter-bar">
    <div class="filter-group">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="Latest News">Latest News</button>
        <button class="filter-btn" data-filter="Upcoming Event">Upcoming</button>
        <button class="filter-btn" data-filter="Past Event">Past</button>
        <button class="filter-btn" data-filter="Job Vacancy">Vacancies</button>
    </div>
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search events..." id="event-search">
    </div>
</div>

<?php
// Load events from JSON file
$events_file = __DIR__ . '/../data/events.json';
$events = [];

if (file_exists($events_file)) {
    $events = json_decode(file_get_contents($events_file), true) ?: [];
} else {
    // Migrate from existing localStorage format if needed
    // For now, empty array
}

if (empty($events)):
?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h2>No Events Yet</h2>
        <p>Start by adding your first event</p>
        <a href="?page=events&action=add" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-plus-circle"></i> Add New Event
        </a>
    </div>
<?php else: ?>
    <div class="events-grid">
        <?php foreach ($events as $event): ?>
            <div class="event-card <?php echo $event['featured'] ? 'featured' : ''; ?>" 
                 data-category="<?php echo htmlspecialchars($event['category']); ?>">
                <div class="event-image">
                    <?php if (!empty($event['image']) && strpos($event['image'], 'data:image') === 0): ?>
                        <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php elseif (!empty($event['image'])): ?>
                        <img src="../<?php echo ltrim(htmlspecialchars($event['image']), './'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php else: ?>
                        <i class="fas fa-calendar-alt"></i>
                    <?php endif; ?>
                </div>
                <div class="event-content">
                    <span class="event-category"><?php echo htmlspecialchars($event['category']); ?></span>
                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <div class="event-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['author'] ?? 'Admin'); ?></span>
                    </div>
                    <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 15px;">
                        <?php echo htmlspecialchars(substr($event['shortDescription'], 0, 100)); ?>...
                    </p>
                    <div class="event-actions">
                        <a href="?page=events&action=edit&id=<?php echo urlencode($event['id']); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="#" onclick="if(confirmDelete()) window.location.href='?page=events&action=delete&id=<?php echo urlencode($event['id']); ?>'; return false;" 
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Search functionality
document.getElementById('event-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.event-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
