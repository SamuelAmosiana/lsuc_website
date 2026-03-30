<?php
// Edit FAQ Form
$faq_id = $_GET['id'] ?? '';
$faq = null;

if ($faq_id) {
    $faqs_file = __DIR__ . '/../data/faqs.json';
    if (file_exists($faqs_file)) {
        $faqs = json_decode(file_get_contents($faqs_file), true) ?: [];
        foreach ($faqs as $f) {
            if ($f['id'] === $faq_id) {
                $faq = $f;
                break;
            }
        }
    }
}

if (!$faq) {
    echo '<div class="error-page" style="text-align: center; padding: 60px 20px;">';
    echo '<i class="fas fa-exclamation-circle" style="font-size: 60px; color: var(--gray-300); margin-bottom: 20px;"></i>';
    echo '<h2>FAQ Not Found</h2>';
    echo '<a href="?page=faqs" class="btn btn-primary" style="margin-top: 20px;">Back to FAQs</a>';
    echo '</div>';
    return;
}
?>

<div class="form-card">
    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-edit"></i> Edit FAQ
    </h2>
    
    <form action="api/save_faq.php" method="POST" id="edit-faq-form">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($faq['id']); ?>">
        
        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="question">Question *</label>
                <input type="text" id="question" name="question" class="form-control" required 
                       value="<?php echo htmlspecialchars($faq['question']); ?>" placeholder="Enter the question">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control">
                    <option value="General" <?php echo ($faq['category'] ?? 'General') === 'General' ? 'selected' : ''; ?>>General</option>
                    <option value="Admissions" <?php echo ($faq['category'] ?? '') === 'Admissions' ? 'selected' : ''; ?>>Admissions</option>
                    <option value="Academics" <?php echo ($faq['category'] ?? '') === 'Academics' ? 'selected' : ''; ?>>Academics</option>
                    <option value="Fees & Payment" <?php echo ($faq['category'] ?? '') === 'Fees & Payment' ? 'selected' : ''; ?>>Fees & Payment</option>
                    <option value="Student Life" <?php echo ($faq['category'] ?? '') === 'Student Life' ? 'selected' : ''; ?>>Student Life</option>
                    <option value="Examinations" <?php echo ($faq['category'] ?? '') === 'Examinations' ? 'selected' : ''; ?>>Examinations</option>
                    <option value="Other" <?php echo ($faq['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="answer">Answer *</label>
            <textarea id="answer" name="answer" class="form-control rich-text" rows="8" required 
                      placeholder="Provide a clear and helpful answer"><?php echo htmlspecialchars($faq['answer'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" id="featured" style="width: auto;" 
                       <?php echo !empty($faq['featured']) ? 'checked' : ''; ?>>
                <strong>Featured FAQ</strong>
            </label>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 10px; justify-content: space-between; margin-top: 30px;">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update FAQ
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-undo"></i> Reset Changes
                </button>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="#" onclick="if(confirm('Delete this FAQ?')) window.location.href='?page=faqs&action=delete&id=<?php echo urlencode($faq['id']); ?>'; return false;" 
                   class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <a href="?page=faqs" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
const form = document.getElementById('edit-faq-form');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_faq.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = '?page=faqs&success=' + encodeURIComponent(data.message), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showToast('Network error', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
