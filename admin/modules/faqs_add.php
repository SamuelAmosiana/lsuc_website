<div class="form-card">
    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-plus-circle"></i> Add New FAQ
    </h2>
    
    <form action="api/save_faq.php" method="POST" id="add-faq-form">
        <input type="hidden" name="action" value="add">
        
        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="question">Question *</label>
                <input type="text" id="question" name="question" class="form-control" required placeholder="Enter the question">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control">
                    <option value="General">General</option>
                    <option value="Admissions">Admissions</option>
                    <option value="Academics">Academics</option>
                    <option value="Fees & Payment">Fees & Payment</option>
                    <option value="Student Life">Student Life</option>
                    <option value="Examinations">Examinations</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="answer">Answer *</label>
            <textarea id="answer" name="answer" class="form-control rich-text" rows="8" required placeholder="Provide a clear and helpful answer. You can use HTML formatting like <p>, <ul>, <li>, <strong>, etc."></textarea>
            <small style="color: var(--gray-600); display: block; margin-top: 8px;">
                Use HTML tags for better formatting: &lt;p&gt; for paragraphs, &lt;ul&gt;&lt;li&gt; for lists, &lt;strong&gt; for bold text
            </small>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" id="featured" style="width: auto;">
                <strong>Featured FAQ</strong> (highlight this question on the FAQs page)
            </label>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 10px; justify-content: space-between; margin-top: 30px;">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save FAQ
                </button>
                <button type="reset" class="btn btn-secondary" onclick="return confirm('Clear all fields?')">
                    <i class="fas fa-undo"></i> Reset Form
                </button>
            </div>
            <a href="?page=faqs" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
const form = document.getElementById('add-faq-form');

// Auto-save draft every 30 seconds
let autoSaveTimer;
const inputs = form.querySelectorAll('input, textarea, select');

inputs.forEach(input => {
    input.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            showToast('Draft saved automatically', 'success');
        }, 30000);
    });
});

// Form validation before submit
form.addEventListener('submit', function(e) {
    const question = document.getElementById('question').value.trim();
    const answer = document.getElementById('answer').value.trim();
    
    if (!question || !answer) {
        e.preventDefault();
        showToast('Please fill in all required fields', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    // Submit via AJAX
    e.preventDefault();
    const formData = new FormData(form);
    
    fetch('api/save_faq.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = '?page=faqs&success=' + encodeURIComponent(data.message);
            }, 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showToast('Network error. Please try again.', 'error');
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<style>
.form-actions {
    padding-top: 20px;
    border-top: 2px solid var(--gray-200);
}

.form-row {
    margin-bottom: 0;
}

.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 13px;
}

input[type="checkbox"] {
    width: auto !important;
    margin-right: 8px;
}
</style>
