<?php
$draft_file = __DIR__ . '/../data/home_gallery_draft.json';
$published_file = __DIR__ . '/../data/home_gallery.json';

$images = [];
$published_images = [];

if (file_exists($published_file)) {
    $published_images = json_decode(file_get_contents($published_file), true) ?: [];
}

if (file_exists($draft_file)) {
    $images = json_decode(file_get_contents($draft_file), true) ?: [];
} else {
    $images = $published_images;
}

// Function to convert root paths for preview in admin console
if (!function_exists('getAdminImagePath')) {
    function getAdminImagePath($path) {
        if (empty($path)) return '';
        if (strpos($path, 'http') === 0 || strpos($path, 'data:') === 0) {
            return $path;
        }
        if (strpos($path, './') === 0) {
            return '../' . substr($path, 2);
        }
        return '../' . $path;
    }
}

// Check if draft has unpublished changes
$has_unpublished_changes = false;
$images_clean = $images;
$published_clean = $published_images;

// Unset dynamic timestamps for comparison
foreach ($images_clean as &$img) {
    unset($img['created_at'], $img['updated_at']);
}
foreach ($published_clean as &$img) {
    unset($img['created_at'], $img['updated_at']);
}

if ($images_clean !== $published_clean) {
    $has_unpublished_changes = true;
}
?>

<div class="form-card">
    <?php if ($has_unpublished_changes): ?>
        <div class="alert warning-alert" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #ffc107; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>You have unpublished changes in Campus Gallery!</strong> Click "Publish Live" to update the public website.</span>
            </div>
            <button onclick="publishGallery(this)" class="btn btn-primary" style="background: var(--primary-green); border-color: var(--primary-green); padding: 8px 20px; font-size: 0.9rem;">
                <i class="fas fa-paper-plane"></i> Publish Live
            </button>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="color: var(--primary-green); margin: 0;">
            <i class="fas fa-images"></i> Campus Gallery
        </h2>
        <button onclick="openAddImageModal()" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add Image
        </button>
    </div>
    
    <?php if (empty($images)): ?>
        <div class="empty-state" style="text-align: center; padding: 60px 20px; color: var(--gray-600);">
            <i class="fas fa-images" style="font-size: 60px; color: var(--gray-300); margin-bottom: 20px;"></i>
            <h3>No Gallery Images Yet</h3>
            <p>Add images to showcase your campus facilities</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($images as $index => $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars(getAdminImagePath($image['path'])); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                    <div class="gallery-item-info">
                        <strong><?php echo htmlspecialchars($image['caption']); ?></strong>
                    </div>
                    <div class="gallery-item-actions">
                        <button onclick="editImage('<?php echo htmlspecialchars($image['id']); ?>')" class="btn-icon edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteImage(this, '<?php echo htmlspecialchars($image['id']); ?>')" class="btn-icon delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="image-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; padding: 40px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 id="image-modal-title" style="margin-bottom: 25px; color: var(--primary-green);">Add Campus Image</h3>
        
        <form id="image-form" onsubmit="saveImage(event)" enctype="multipart/form-data">
            <input type="hidden" id="image-id" name="id">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <div class="form-group">
                <label for="image-path">Image Path</label>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="image-path" name="path" class="form-control" style="flex: 2; min-width: 200px;" placeholder="./img/campus.jpg">
                    <span style="color: var(--gray-600);">or upload file:</span>
                    <input type="file" id="gallery_image_file" name="gallery_image_file" accept="image/*" class="form-control" style="flex: 2; min-width: 200px;">
                </div>
                <small style="color: var(--gray-600); display: block; margin-top: 8px;">
                    Relative path from website root (e.g. <code>./img/library.jpg</code>) or select a file to upload.
                </small>
            </div>
            
            <div class="form-group">
                <label for="image-caption">Caption/Title *</label>
                <input type="text" id="image-caption" name="caption" class="form-control" required placeholder="e.g., Main Library">
            </div>
            
            <div class="form-group">
                <label for="image-description">Description</label>
                <textarea id="image-description" name="description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" id="save-image-btn" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Save Image
                </button>
                <button type="button" onclick="closeImageModal()" class="btn btn-secondary" style="flex: 1;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditingImage = false;

function openAddImageModal() {
    document.getElementById('image-modal-title').textContent = 'Add Campus Image';
    document.getElementById('image-form').reset();
    document.getElementById('image-id').value = '';
    document.getElementById('image-modal').style.display = 'flex';
    isEditingImage = false;
}

function closeImageModal() {
    document.getElementById('image-modal').style.display = 'none';
}

function editImage(id) {
    const imagesList = <?php echo json_encode($images); ?>;
    const image = imagesList.find(i => i.id === id);
    
    if (!image) return;
    
    document.getElementById('image-modal-title').textContent = 'Edit Campus Image';
    document.getElementById('image-id').value = image.id;
    document.getElementById('image-path').value = image.path;
    document.getElementById('image-caption').value = image.caption;
    document.getElementById('image-description').value = image.description || '';
    document.getElementById('image-modal').style.display = 'flex';
    isEditingImage = true;
}

function publishGallery(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
    
    const formData = new FormData();
    formData.append('action', 'publish');
    formData.append('csrf_token', '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>');
    
    fetch('api/save_home_gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'Server error'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function saveImage(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('save-image-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const formData = new FormData(document.getElementById('image-form'));
    formData.append('action', isEditingImage ? 'update' : 'add');
    
    fetch('api/save_home_gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'Server error'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeImageModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function deleteImage(btn, id) {
    if (!confirm('Are you sure you want to delete this image draft?')) return;
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    formData.append('csrf_token', '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>');
    
    fetch('api/save_home_gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'Server error'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

document.getElementById('image-modal').addEventListener('click', function(e) {
    if (e.target === this) closeImageModal();
});
</script>
