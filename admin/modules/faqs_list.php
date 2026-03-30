<div class="search-sort-bar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search FAQs..." id="faq-search" style="padding: 8px 15px 8px 35px; border: 2px solid var(--gray-200); border-radius: 20px; width: 300px;">
    </div>
    <div class="sort-controls">
        <button class="btn btn-primary btn-sm" onclick="window.location.href='?page=faqs&action=add'">
            <i class="fas fa-plus-circle"></i> Add New FAQ
        </button>
        <button class="btn btn-secondary btn-sm" onclick="exportFAQs()">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<?php
// Load FAQs from JSON file
$faqs_file = __DIR__ . '/../data/faqs.json';
$faqs = [];

if (file_exists($faqs_file)) {
    $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
}

// Sort by order
usort($faqs, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

if (empty($faqs)):
?>
    <div class="empty-state">
        <i class="fas fa-question-circle"></i>
        <h2>No FAQs Yet</h2>
        <p>Start by adding your first frequently asked question</p>
        <a href="?page=faqs&action=add" class="btn btn-primary" style="margin-top: 20px;">
            <i class="fas fa-plus-circle"></i> Add New FAQ
        </a>
    </div>
<?php else: ?>
    <div id="faq-list">
        <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item" data-id="<?php echo htmlspecialchars($faq['id']); ?>">
                <div class="faq-question">
                    <div class="drag-handle">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="faq-number"><?php echo $index + 1; ?></div>
                    <div class="faq-content">
                        <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                        <div class="faq-answer">
                            <?php echo nl2br(htmlspecialchars(substr($faq['answer'], 0, 200))); ?>...
                        </div>
                        <div class="faq-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($faq['created_at'])); ?></span>
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($faq['category'] ?? 'General'); ?></span>
                        </div>
                        <div class="faq-actions">
                            <a href="?page=faqs&action=edit&id=<?php echo urlencode($faq['id']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="#" onclick="if(confirmDelete()) window.location.href='?page=faqs&action=delete&id=<?php echo urlencode($faq['id']); ?>'; return false;" 
                               class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Search functionality
document.getElementById('faq-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.faq-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

function exportFAQs() {
    window.location.href = 'api/export_faqs.php';
}
</script>
