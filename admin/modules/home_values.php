<?php
$draft_file = __DIR__ . '/../data/home_values_draft.json';
$published_file = __DIR__ . '/../data/home_values.json';

$values = [];
$published_values = [];

if (file_exists($published_file)) {
    $published_values = json_decode(file_get_contents($published_file), true) ?: [];
}

if (file_exists($draft_file)) {
    $values = json_decode(file_get_contents($draft_file), true) ?: [];
} else {
    $values = $published_values;
}

// Sort values by order
usort($values, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

usort($published_values, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Check if draft has unpublished changes
$has_unpublished_changes = false;
$values_clean = $values;
$published_clean = $published_values;

// Unset dynamic timestamps for comparison
foreach ($values_clean as &$val) {
    unset($val['created_at'], $val['updated_at']);
}
foreach ($published_clean as &$val) {
    unset($val['created_at'], $val['updated_at']);
}

if ($values_clean !== $published_clean) {
    $has_unpublished_changes = true;
}
?>

<div class="form-card">
    <?php if ($has_unpublished_changes): ?>
        <div class="alert warning-alert" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #ffc107; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>You have unpublished changes in Core Values!</strong> Click "Publish Live" to update the public website.</span>
            </div>
            <button onclick="publishCoreValues(this)" class="btn btn-primary" style="background: var(--primary-green); border-color: var(--primary-green); padding: 8px 20px; font-size: 0.9rem;">
                <i class="fas fa-paper-plane"></i> Publish Live
            </button>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h2 style="color: var(--primary-green); margin: 0;">
            <i class="fas fa-star"></i> Core Values Editor
        </h2>
        <button onclick="openAddValueModal()" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Add New Value
        </button>
    </div>
    
    <?php if (empty($values)): ?>
        <div class="empty-state" style="text-align: center; padding: 60px 20px; color: var(--gray-600);">
            <i class="fas fa-star" style="font-size: 60px; color: var(--gray-300); margin-bottom: 20px;"></i>
            <h3>No Core Values Yet</h3>
            <p>Start by adding your first core value</p>
        </div>
    <?php else: ?>
        <div id="values-list">
            <?php foreach ($values as $index => $value): ?>
                <div class="value-item" data-id="<?php echo htmlspecialchars($value['id']); ?>">
                    <div class="value-icon">
                        <i class="fas <?php echo htmlspecialchars($value['icon'] ?? 'fa-star'); ?>"></i>
                    </div>
                    <div class="value-content">
                        <h3><?php echo htmlspecialchars($value['title']); ?></h3>
                        <p style="color: var(--gray-600); margin-top: 10px;">
                            <?php echo htmlspecialchars($value['description']); ?>
                        </p>
                        <div class="value-actions">
                            <button onclick="editValue('<?php echo htmlspecialchars($value['id']); ?>')" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteValue(this, '<?php echo htmlspecialchars($value['id']); ?>')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="value-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; padding: 40px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 id="modal-title" style="margin-bottom: 25px; color: var(--primary-green);">Add Core Value</h3>
        
        <form id="value-form" onsubmit="saveValue(event)">
            <input type="hidden" id="value-id" name="id">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <div class="form-group">
                <label for="value-title">Title *</label>
                <input type="text" id="value-title" name="title" class="form-control" required placeholder="e.g., Excellence">
            </div>
            
            <div class="form-group">
                <label for="value-description">Description *</label>
                <textarea id="value-description" name="description" class="form-control" rows="4" required placeholder="Describe this value..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="value-icon">Icon</label>
                <select id="value-icon" name="icon" class="form-control">
                    <option value="fa-star">⭐ Star</option>
                    <option value="fa-lightbulb">💡 Light Bulb (Innovation)</option>
                    <option value="fa-users">👥 Users (Teamwork)</option>
                    <option value="fa-shield-alt">🛡️ Shield (Integrity)</option>
                    <option value="fa-trophy">🏆 Trophy (Excellence)</option>
                    <option value="fa-book">📚 Book (Knowledge)</option>
                    <option value="fa-handshake">🤝 Handshake (Partnership)</option>
                    <option value="fa-globe">🌍 Globe (Global)</option>
                    <option value="fa-heart">❤️ Heart (Care)</option>
                    <option value="fa-rocket">🚀 Rocket (Progress)</option>
                </select>
                <small style="color: var(--gray-600); display: block; margin-top: 8px;">
                    Choose an icon that represents this value
                </small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" id="save-value-btn" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Save Value
                </button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex: 1;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditing = false;

function openAddValueModal() {
    document.getElementById('modal-title').textContent = 'Add Core Value';
    document.getElementById('value-form').reset();
    document.getElementById('value-id').value = '';
    document.getElementById('value-modal').style.display = 'flex';
    isEditing = false;
}

function closeModal() {
    document.getElementById('value-modal').style.display = 'none';
}

function editValue(id) {
    const valuesList = <?php echo json_encode($values); ?>;
    const value = valuesList.find(v => v.id === id);
    
    if (!value) return;
    
    document.getElementById('modal-title').textContent = 'Edit Core Value';
    document.getElementById('value-id').value = value.id;
    document.getElementById('value-title').value = value.title;
    document.getElementById('value-description').value = value.description;
    document.getElementById('value-icon').value = value.icon || 'fa-star';
    document.getElementById('value-modal').style.display = 'flex';
    isEditing = true;
}

function publishCoreValues(btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
    
    const formData = new FormData();
    formData.append('action', 'publish');
    formData.append('csrf_token', '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>');
    
    fetch('api/save_home_values.php', {
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

function saveValue(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('save-value-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const formData = new FormData(document.getElementById('value-form'));
    formData.append('action', isEditing ? 'update' : 'add');
    
    fetch('api/save_home_values.php', {
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
            closeModal();
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

function deleteValue(btn, id) {
    if (!confirm('Are you sure you want to delete this core value draft?')) {
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    formData.append('csrf_token', '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>');
    
    fetch('api/save_home_values.php', {
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

// Close modal when clicking outside
document.getElementById('value-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
