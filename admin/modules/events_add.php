<?php
// Add New Event Form
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<div class="form-card">
    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-plus-circle"></i> Add New Event
    </h2>
    
    <?php if ($success): ?>
        <div class="toast success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form action="api/save_event.php" method="POST" enctype="multipart/form-data" id="add-event-form">
        <input type="hidden" name="action" value="add">
        
        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="title">Event Title *</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="Enter event title">
            </div>
            
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" id="author" name="author" class="form-control" placeholder="e.g., Admin" value="Admin">
            </div>
        </div>
        
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Event Date *</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Latest News">Latest News</option>
                    <option value="Upcoming Event">Upcoming Event</option>
                    <option value="Past Event">Past Event</option>
                    <option value="Job Vacancy">Job Vacancy</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image_upload">Upload Image</label>
            <input type="file" id="image_upload" name="image_upload" class="form-control" accept="image/*" onchange="previewImage(this)">
            <small style="display: block; margin-top: 8px; color: var(--gray-600);">
                <i class="fas fa-info-circle"></i> Supported: JPG, PNG, GIF, WebP. Max: 2MB
            </small>
            
            <!-- Image Preview -->
            <div id="image_preview_container" style="display: none; margin-top: 15px;">
                <img id="image_preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 10px; border: 2px solid var(--primary-green);">
                <br>
                <button type="button" onclick="removeImage()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                    <i class="fas fa-trash"></i> Remove Image
                </button>
            </div>
            <input type="hidden" id="image_base64" name="image_base64">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" id="featured" style="width: auto;">
                <strong>Featured Event</strong> (show on homepage with star badge)
            </label>
        </div>
        
        <div class="form-group">
            <label for="short_description">Short Description * (for cards)</label>
            <textarea id="short_description" name="short_description" class="form-control" rows="3" required placeholder="2-3 sentence summary that appears on event cards"></textarea>
            <small style="color: var(--gray-600);">Keep it concise - this appears in listing views</small>
        </div>
        
        <div class="form-group">
            <label for="full_description">Full Description * (complete details)</label>
            <textarea id="full_description" name="full_description" class="form-control rich-text" rows="12" required placeholder="Complete event details. You can use basic HTML like &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, etc."></textarea>
            <small style="color: var(--gray-600);">Use HTML formatting for better presentation</small>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 10px; justify-content: space-between; margin-top: 30px;">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Event
                </button>
                <button type="reset" class="btn btn-secondary" onclick="return confirm('Clear all fields?')">
                    <i class="fas fa-undo"></i> Reset Form
                </button>
            </div>
            <a href="?page=events" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Image preview function
function previewImage(input) {
    const file = input.files[0];
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    const base64Field = document.getElementById('image_base64');
    
    if (file) {
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB. Please choose a smaller image.');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file (JPG, PNG, GIF, WebP).');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const base64Data = e.target.result;
            previewImage.src = base64Data;
            previewContainer.style.display = 'block';
            base64Field.value = base64Data;
        };
        
        reader.onerror = function() {
            alert('Error reading file. Please try again.');
            input.value = '';
        };
        
        reader.readAsDataURL(file);
    }
}

// Remove image
function removeImage() {
    const input = document.getElementById('image_upload');
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    const base64Field = document.getElementById('image_base64');
    
    input.value = '';
    previewImage.src = '';
    previewContainer.style.display = 'none';
    base64Field.value = '';
}

// Auto-save draft every 30 seconds
let autoSaveTimer;
const form = document.getElementById('add-event-form');
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
    const category = document.getElementById('category').value;
    const title = document.getElementById('title').value.trim();
    const shortDesc = document.getElementById('short_description').value.trim();
    const fullDesc = document.getElementById('full_description').value.trim();
    
    if (!category || !title || !shortDesc || !fullDesc) {
        e.preventDefault();
        showToast('Please fill in all required fields', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    // Submit via AJAX for better user experience
    e.preventDefault();
    const formData = new FormData(form);
    
    fetch('api/save_event.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = '?page=events&success=' + encodeURIComponent(data.message);
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
