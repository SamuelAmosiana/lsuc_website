<?php
$draft_file = __DIR__ . '/../data/home_hero_draft.json';
$published_file = __DIR__ . '/../data/home_hero.json';

$default_data = [
    'heading' => 'Welcome to Lusaka South University College',
    'motto' => 'Dream, Explore, Acquire',
    'description' => 'Providing quality education and training for over 20 years. Join us to build your future with industry-relevant programs.',
    'background_image' => './img/lsuc site cover img.jpeg',
    'cta_text' => 'Apply Now',
    'cta_link' => '#apply',
    'show_cta' => true
];

$hero_data = $default_data;
$published_data = null;

if (file_exists($published_file)) {
    $published_data = json_decode(file_get_contents($published_file), true) ?: [];
}

// Load draft data if available, fallback to published, fallback to default
if (file_exists($draft_file)) {
    $draft_data = json_decode(file_get_contents($draft_file), true);
    if ($draft_data) {
        $hero_data = array_merge($default_data, $draft_data);
    }
} elseif ($published_data) {
    $hero_data = array_merge($default_data, $published_data);
}

// Check if draft has unpublished changes
$has_unpublished_changes = false;
if (file_exists($draft_file) && $published_data) {
    $draft_clean = json_decode(file_get_contents($draft_file), true) ?: [];
    // Unset updated_at for comparison
    unset($draft_clean['updated_at'], $published_data['updated_at']);
    if ($draft_clean !== $published_data) {
        $has_unpublished_changes = true;
    }
} elseif (file_exists($draft_file) && !$published_data) {
    $has_unpublished_changes = true;
}

// Function to convert root paths for preview in admin console
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
?>

<div class="form-card">
    <?php if ($has_unpublished_changes): ?>
        <div class="alert warning-alert" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #ffc107; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>You have unpublished changes!</strong> Click the "Publish Live" button to make them visible on the public website.
            </div>
        </div>
    <?php endif; ?>

    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-image"></i> Hero Section Editor
    </h2>
    
    <form action="api/save_home_hero.php" method="POST" id="hero-form" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="publish_action" id="publish_action" value="draft">
        
        <div class="form-group">
            <label for="heading">Welcome Heading *</label>
            <input type="text" id="heading" name="heading" class="form-control" required 
                   value="<?php echo htmlspecialchars($hero_data['heading']); ?>" 
                   placeholder="e.g., Welcome to Lusaka South University College">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="motto">Motto/Slogan *</label>
                <input type="text" id="motto" name="motto" class="form-control" required 
                       value="<?php echo htmlspecialchars($hero_data['motto']); ?>" 
                       placeholder="e.g., Dream, Explore, Acquire">
            </div>
            
            <div class="form-group">
                <label for="show_cta">Show Call-to-Action Button</label>
                <select id="show_cta" name="show_cta" class="form-control">
                    <option value="1" <?php echo $hero_data['show_cta'] ? 'selected' : ''; ?>>Yes, Show it</option>
                    <option value="0" <?php echo !$hero_data['show_cta'] ? 'selected' : ''; ?>>No, Hide it</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="cta_text">CTA Button Text</label>
                <input type="text" id="cta_text" name="cta_text" class="form-control" 
                       value="<?php echo htmlspecialchars($hero_data['cta_text']); ?>" 
                       placeholder="e.g., Apply Now">
            </div>
            
            <div class="form-group">
                <label for="cta_link">CTA Button Link</label>
                <input type="text" id="cta_link" name="cta_link" class="form-control" 
                       value="<?php echo htmlspecialchars($hero_data['cta_link']); ?>" 
                       placeholder="e.g., #apply or admissions.html">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description/Subtitle</label>
            <textarea id="description" name="description" class="form-control" rows="4" 
                      placeholder="Brief description or welcome message"><?php echo htmlspecialchars($hero_data['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="background_image">Background Image</label>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="text" id="background_image" name="background_image" class="form-control" style="flex: 2; min-width: 200px;" 
                       value="<?php echo htmlspecialchars($hero_data['background_image']); ?>" 
                       placeholder="./img/your-image.jpg">
                <span style="color: var(--gray-600);">or upload file:</span>
                <input type="file" id="hero_image_file" name="hero_image_file" accept="image/*" class="form-control" style="flex: 2; min-width: 200px;">
            </div>
            <small style="display: block; margin-top: 8px; color: var(--gray-600);">
                <i class="fas fa-info-circle"></i> Use relative path from website root (e.g. <code>./img/campus.jpg</code>) or upload a new image.
            </small>
        </div>
        
        <div class="preview-section">
            <div class="preview-label">
                <i class="fas fa-eye"></i> Live Preview <span style="font-size: 0.8rem; font-weight: normal; color: var(--gray-600); margin-left: 5px;">(showing draft changes)</span>
            </div>
            <div id="hero-preview" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars(getAdminImagePath($hero_data['background_image'])); ?>'); background-size: cover; background-position: center; padding: 100px 20px; text-align: center; color: white; border-radius: 10px;">
                <h1 id="preview-heading" style="font-size: 48px; margin-bottom: 10px;"><?php echo htmlspecialchars($hero_data['heading']); ?></h1>
                <p id="preview-motto" style="font-size: 24px; color: var(--primary-orange); margin-bottom: 20px;"><?php echo htmlspecialchars($hero_data['motto']); ?></p>
                <p id="preview-description" style="font-size: 18px; max-width: 600px; margin: 0 auto;"><?php echo htmlspecialchars($hero_data['description']); ?></p>
                <?php if ($hero_data['show_cta']): ?>
                    <button id="preview-cta" style="margin-top: 30px; padding: 15px 40px; background: var(--primary-orange); color: white; border: none; border-radius: 8px; font-size: 18px; cursor: pointer;">
                        <?php echo htmlspecialchars($hero_data['cta_text']); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 15px; justify-content: flex-start; margin-top: 30px; flex-wrap: wrap;">
            <button type="submit" id="save-draft-btn" onclick="setPublishAction('draft')" class="btn btn-secondary" style="min-width: 150px;">
                <i class="fas fa-save"></i> Save Draft
            </button>
            <button type="submit" id="publish-btn" onclick="setPublishAction('publish')" class="btn btn-primary" style="min-width: 150px; background: var(--primary-green); border-color: var(--primary-green);">
                <i class="fas fa-paper-plane"></i> Publish Live
            </button>
            <a href="?page=home" class="btn btn-secondary" style="min-width: 100px;">
                <i class="fas fa-undo"></i> Reset
            </a>
        </div>
    </form>
</div>

<script>
function setPublishAction(action) {
    document.getElementById('publish_action').value = action;
}

// Live preview functionality
const form = document.getElementById('hero-form');
const inputs = form.querySelectorAll('input, textarea, select');

inputs.forEach(input => {
    if (input.type !== 'file') {
        input.addEventListener('input', updatePreview);
    }
});

// Setup image file preview
const fileInput = document.getElementById('hero_image_file');
if (fileInput) {
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('hero-preview').style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${e.target.result}')`;
            };
            reader.readAsDataURL(file);
        }
    });
}

function updatePreview() {
    const heading = document.getElementById('heading').value;
    const motto = document.getElementById('motto').value;
    const description = document.getElementById('description').value;
    const bgImage = document.getElementById('background_image').value;
    const ctaText = document.getElementById('cta_text').value;
    const showCta = document.getElementById('show_cta').value === '1';
    
    document.getElementById('preview-heading').textContent = heading || 'Your Heading Here';
    document.getElementById('preview-motto').textContent = motto || 'Your Motto Here';
    document.getElementById('preview-description').textContent = description || 'Your description here...';
    
    // Only update preview BG by text input if no file is selected
    if (bgImage && (!fileInput || !fileInput.files.length)) {
        // Convert to admin preview path
        let adminBgPath = bgImage;
        if (bgImage.startsWith('./')) {
            adminBgPath = '../' + bgImage.substring(2);
        } else if (!bgImage.startsWith('http') && !bgImage.startsWith('../')) {
            adminBgPath = '../' + bgImage;
        }
        document.getElementById('hero-preview').style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${adminBgPath}')`;
    }
    
    const ctaButton = document.getElementById('preview-cta');
    if (ctaButton) {
        if (!showCta) {
            ctaButton.style.display = 'none';
        } else {
            ctaButton.style.display = 'inline-block';
            ctaButton.textContent = ctaText || 'Call to Action';
        }
    }
}

// Form submission via AJAX
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const publishBtn = document.getElementById('publish-btn');
    const actionVal = document.getElementById('publish_action').value;
    
    const activeBtn = actionVal === 'publish' ? publishBtn : saveDraftBtn;
    const originalText = activeBtn.innerHTML;
    
    // Disable both buttons
    saveDraftBtn.disabled = true;
    publishBtn.disabled = true;
    
    activeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    const formData = new FormData(form);
    
    fetch('api/save_home_hero.php', {
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
            setTimeout(() => {
                window.location.href = '?page=home&tab=hero&success=' + encodeURIComponent(data.message);
            }, 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            // Re-enable buttons
            saveDraftBtn.disabled = false;
            publishBtn.disabled = false;
            activeBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
        console.error('Error:', error);
        // Re-enable buttons
        saveDraftBtn.disabled = false;
        publishBtn.disabled = false;
        activeBtn.innerHTML = originalText;
    });
});
</script>

<style>
#hero-preview {
    transition: all 0.3s ease;
}

.preview-section {
    margin-bottom: 25px;
}
</style>
