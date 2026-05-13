<?php
// FAQs Management Module
$action = $_GET['action'] ?? 'list';
$success = $_GET['success'] ?? '';
?>

<div class="module-container">
    <div class="page-header">
        <h1><i class="fas fa-question-circle"></i> FAQs Management</h1>
        <p>Manage frequently asked questions and answers</p>
    </div>
    
    <?php if ($success): ?>
        <div class="toast success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Navigation Tabs -->
    <div class="module-tabs">
        <a href="?page=faqs&action=list" class="tab <?php echo $action === 'list' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> All FAQs
        </a>
        <a href="?page=faqs&action=add" class="tab <?php echo $action === 'add' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> Add New FAQ
        </a>
        <a href="../index.html#faqs" target="_blank" class="tab">
            <i class="fas fa-external-link-alt"></i> View on Site
        </a>
    </div>
    
    <?php
    switch ($action) {
        case 'add':
            include __DIR__ . '/faqs_add.php';
            break;
        case 'edit':
            include __DIR__ . '/faqs_edit.php';
            break;
        case 'delete':
            include __DIR__ . '/faqs_delete.php';
            break;
        default:
            include __DIR__ . '/faqs_list.php';
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

.faq-item {
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.faq-item:hover {
    border-color: var(--primary-orange);
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

.faq-question {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 15px;
}

.faq-number {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    flex-shrink: 0;
}

.faq-content {
    flex: 1;
}

.faq-question h3 {
    margin: 0 0 10px 0;
    color: var(--gray-800);
    font-size: 18px;
}

.faq-answer {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 15px;
}

.faq-meta {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: var(--gray-600);
    margin-bottom: 15px;
}

.faq-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid var(--gray-200);
}

.drag-handle {
    cursor: move;
    color: var(--gray-400);
    font-size: 20px;
    padding: 5px;
}

.drag-handle:hover {
    color: var(--primary-orange);
}

.search-sort-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.sort-controls {
    display: flex;
    gap: 10px;
    align-items: center;
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
// Initialize drag-and-drop reordering
document.addEventListener('DOMContentLoaded', function() {
    const faqList = document.getElementById('faq-list');
    if (faqList) {
        new Sortable(faqList, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                // Update order numbers
                const items = faqList.querySelectorAll('.faq-item');
                items.forEach((item, index) => {
                    const numberEl = item.querySelector('.faq-number');
                    if (numberEl) {
                        numberEl.textContent = index + 1;
                    }
                });
                
                // Save new order to server
                saveOrder();
            }
        });
    }
});

function saveOrder() {
    const items = document.querySelectorAll('.faq-item');
    const order = Array.from(items).map(item => item.getAttribute('data-id'));
    
    fetch('api/save_faqs_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order: order })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order saved successfully', 'success');
        } else {
            showToast('Error saving order', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error saving order', 'error');
    });
}
</script>
