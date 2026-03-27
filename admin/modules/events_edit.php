<?php
// Edit Event Form
$event_id = $_GET['id'] ?? '';
$event = null;

// Load event data
if ($event_id) {
    $events_file = __DIR__ . '/../data/events.json';
    if (file_exists($events_file)) {
        $events = json_decode(file_get_contents($events_file), true) ?: [];
        foreach ($events as $e) {
            if ($e['id'] === $event_id) {
                $event = $e;
                break;
            }
        }
    }
}

if (!$event) {
    echo '<div class="error-page" style="text-align: center; padding: 60px 20px;">';
    echo '<i class="fas fa-exclamation-circle" style="font-size: 60px; color: var(--gray-300); margin-bottom: 20px;"></i>';
    echo '<h2>Event Not Found</h2>';
    echo '<p>The requested event could not be found.</p>';
    echo '<a href="?page=events" class="btn btn-primary" style="margin-top: 20px;">Back to Events</a>';
    echo '</div>';
    return;
}
?>

<div class="form-card">
    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-edit"></i> Edit Event
    </h2>
    
    <form action="api/save_event.php" method="POST" enctype="multipart/form-data" id="edit-event-form">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
        
        <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="title">Event Title *</label>
                <input type="text" id="title" name="title" class="form-control" required 
                       value="<?php echo htmlspecialchars($event['title']); ?>" placeholder="Enter event title">
            </div>
            
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" id="author" name="author" class="form-control" 
                       value="<?php echo htmlspecialchars($event['author'] ?? 'Admin'); ?>" placeholder="e.g., Admin">
            </div>
        </div>
        
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Event Date *</label>
                <input type="date" id="date" name="date" class="form-control" required 
                       value="<?php echo htmlspecialchars($event['date']); ?>">
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <option value="Latest News" <?php echo ($event['category'] ?? '') === 'Latest News' ? 'selected' : ''; ?>>Latest News</option>
                    <option value="Upcoming Event" <?php echo ($event['category'] ?? '') === 'Upcoming Event' ? 'selected' : ''; ?>>Upcoming Event</option>
                    <option value="Past Event" <?php echo ($event['category'] ?? '') === 'Past Event' ? 'selected' : ''; ?>>Past Event</option>
                    <option value="Job Vacancy" <?php echo ($event['category'] ?? '') === 'Job Vacancy' ? 'selected' : ''; ?>>Job Vacancy</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="image_upload">Upload New Image</label>
            <input type="file" id="image_upload" name="image_upload" class="form-control" accept="image/*" onchange="previewImage(this)">
            <small style="display: block; margin-top: 8px; color: var(--gray-600);">
                <i class="fas fa-info-circle"></i> Leave empty to keep current image. Supported: JPG, PNG, GIF, WebP. Max: 2MB
            </small>
            
            <!-- Current Image Display -->
            <?php if (!empty($event['image'])): ?>
                <div id="current_image_container" style="margin-top: 15px;">
                    <strong>Current Image:</strong><br>
                    <img id="current_image" src="<?php 
                        if (strpos($event['image'], 'data:image') === 0) {
                            echo htmlspecialchars($event['image']);
                        } else {
                            echo '../' . ltrim(htmlspecialchars($event['image']), './');
                        }
                    ?>" alt="Current Image" style="max-width: 100%; max-height: 300px; border-radius: 10px; border: 2px solid var(--gray-200); margin-top: 10px;">
                    <br>
                    <button type="button" onclick="removeCurrentImage()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                        <i class="fas fa-trash"></i> Remove Current Image
                    </button>
                    <input type="hidden" id="remove_current_image" name="remove_current_image" value="0">
                </div>
            <?php endif; ?>
            
            <!-- Image Preview for New Upload -->
            <div id="image_preview_container" style="display: none; margin-top: 15px;">
                <strong>New Image Preview:</strong><br>
                <img id="image_preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 10px; border: 2px solid var(--primary-green); margin-top: 10px;">
                <br>
                <button type="button" onclick="removeNewImage()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                    <i class="fas fa-trash"></i> Remove New Image
                </button>
            </div>
            <input type="hidden" id="image_base64" name="image_base64">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="featured" id="featured" style="width: auto;" 
                       <?php echo !empty($event['featured']) ? 'checked' : ''; ?>>
                <strong>Featured Event</strong> (show on homepage with star badge)
            </label>
        </div>
        
        <div class="form-group">
            <label for="short_description">Short Description * (for cards)</label>
            <textarea id="short_description" name="short_description" class="form-control" rows="3" required 
                      placeholder="2-3 sentence summary that appears on event cards"><?php echo htmlspecialchars($event['shortDescription'] ?? ''); ?></textarea>
            <small style="color: var(--gray-600);">Keep it concise - this appears in listing views</small>
        </div>
        
        <div class="form-group">
            <label for="full_description">Full Description * (complete details)</label>
            <textarea id="full_description" name="full_description" class="form-control rich-text" rows="12" required 
                      placeholder="Complete event details"><?php echo htmlspecialchars($event['fullDescription'] ?? ''); ?></textarea>
            <small style="color: var(--gray-600);">Use HTML formatting for better presentation</small>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 10px; justify-content: space-between; margin-top: 30px;">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Event
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset Changes
                </button>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="#" onclick="if(confirm('Delete this event?')) window.location.href='?page=events&action=delete&id=<?php echo urlencode($event['id']); ?>'; return false;" 
                   class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <a href="?page=events" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
const originalFormData = new FormData(document.getElementById('edit-event-form'));

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

// Remove current image
function removeCurrentImage() {
    if (confirm('Are you sure you want to remove the current image? The event will have no image unless you upload a new one.')) {
        document.getElementById('current_image_container').style.display = 'none';
        document.getElementById('remove_current_image').value = '1';
        showToast('Current image marked for removal', 'warning');
    }
}

// Remove new image
function removeNewImage() {
    const input = document.getElementById('image_upload');
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    const base64Field = document.getElementById('image_base64');
    
    input.value = '';
    previewImage.src = '';
    previewContainer.style.display = 'none';
    base64Field.value = '';
}

// Reset form to original values
function resetForm() {
    if (confirm('Discard all changes and reset to original values?')) {
        window.location.reload();
    }
}

// Auto-save draft every 30 seconds
let autoSaveTimer;
const form = document.getElementById('edit-event-form');
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
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
});

// Warn about unsaved changes
window.addEventListener('beforeunload', function(e) {
    const formData = new FormData(form);
    for (let [key, value] of formData.entries()) {
        const originalValue = originalFormData.get(key);
        if (value !== originalValue) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    }
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
