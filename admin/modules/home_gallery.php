<?php
// Load campus gallery images
$gallery_file = __DIR__ . '/../data/home_gallery.json';
$images = [];

if (file_exists($gallery_file)) {
    $images = json_decode(file_get_contents($gallery_file), true) ?: [];
}
?>

<div class="form-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
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
                    <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                    <div class="gallery-item-info">
                        <strong><?php echo htmlspecialchars($image['caption']); ?></strong>
                    </div>
                    <div class="gallery-item-actions">
                        <button onclick="editImage('<?php echo htmlspecialchars($image['id']); ?>')" class="btn-icon edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteImage('<?php echo htmlspecialchars($image['id']); ?>')" class="btn-icon delete" title="Delete">
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
    <div class="modal-content" style="background: white; padding: 40px; border-radius: 15px; max-width: 600px; width: 90%;">
        <h3 id="image-modal-title" style="margin-bottom: 25px; color: var(--primary-green);">Add Campus Image</h3>
        
        <form id="image-form" onsubmit="saveImage(event)">
            <input type="hidden" id="image-id" name="id">
            
            <div class="form-group">
                <label for="image-path">Image Path *</label>
                <input type="text" id="image-path" name="path" class="form-control" required placeholder="./img/campus.jpg">
                <small style="color: var(--gray-600); display: block; margin-top: 8px;">
                    Relative path from website root, e.g., ./img/library.jpg
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
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Save
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

function saveImage(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('image-form'));
    formData.append('action', isEditingImage ? 'update' : 'add');
    
    fetch('api/save_home_gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeImageModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showToast('Network error', 'error');
        console.error('Error:', error);
    });
}

function deleteImage(id) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    fetch('api/save_home_gallery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showToast('Network error', 'error');
        console.error('Error:', error);
    });
}

document.getElementById('image-modal').addEventListener('click', function(e) {
    if (e.target === this) closeImageModal();
});
</script>
