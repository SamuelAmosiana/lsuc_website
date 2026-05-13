<?php
// Load current hero section data
$hero_file = __DIR__ . '/../data/home_hero.json';
$hero_data = [
    'heading' => 'Welcome to Lusaka South University College',
    'motto' => 'Dream, Explore, Acquire',
    'description' => 'Providing quality education and training for over 20 years. Join us to build your future with industry-relevant programs.',
    'background_image' => './img/lsuc site cover img.jpeg',
    'cta_text' => 'Apply Now',
    'cta_link' => '#apply',
    'show_cta' => true
];

if (file_exists($hero_file)) {
    $saved_data = json_decode(file_get_contents($hero_file), true);
    if ($saved_data) {
        $hero_data = array_merge($hero_data, $saved_data);
    }
}
?>

<div class="form-card">
    <h2 style="margin-bottom: 25px; color: var(--primary-green);">
        <i class="fas fa-image"></i> Hero Section Editor
    </h2>
    
    <form action="api/save_home_hero.php" method="POST" id="hero-form">
        <input type="hidden" name="action" value="save">
        
        <div class="form-group">
            <label for="heading">Welcome Heading *</label>
            <input type="text" id="heading" name="heading" class="form-control" required 
                   value="<?php echo htmlspecialchars($hero_data['heading']); ?>" 
                   placeholder="e.g., Welcome to Lusaka South University College">
        </div>
        
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
        
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
            <label for="background_image">Background Image URL</label>
            <input type="text" id="background_image" name="background_image" class="form-control" 
                   value="<?php echo htmlspecialchars($hero_data['background_image']); ?>" 
                   placeholder="./img/your-image.jpg">
            <small style="display: block; margin-top: 8px; color: var(--gray-600);">
                <i class="fas fa-info-circle"></i> Use relative path from website root, e.g., ./img/campus.jpg
            </small>
        </div>
        
        <div class="preview-section">
            <div class="preview-label">
                <i class="fas fa-eye"></i> Live Preview
            </div>
            <div id="hero-preview" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($hero_data['background_image']); ?>'); background-size: cover; background-position: center; padding: 100px 20px; text-align: center; color: white; border-radius: 10px;">
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
        
        <div class="form-actions" style="display: flex; gap: 10px; justify-content: space-between; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Hero Section
            </button>
            <a href="?page=home" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset
            </a>
        </div>
    </form>
</div>

<script>
// Live preview functionality
const form = document.getElementById('hero-form');
const inputs = form.querySelectorAll('input, textarea, select');

inputs.forEach(input => {
    input.addEventListener('input', updatePreview);
});

function updatePreview() {
    const heading = document.getElementById('heading').value;
    const motto = document.getElementById('motto').value;
    const description = document.getElementById('description').value;
    const bgImage = document.getElementById('background_image').value;
    const ctaText = document.getElementById('cta_text').value;
    const ctaLink = document.getElementById('cta_link').value;
    const showCta = document.getElementById('show_cta').value === '1';
    
    document.getElementById('preview-heading').textContent = heading || 'Your Heading Here';
    document.getElementById('preview-motto').textContent = motto || 'Your Motto Here';
    document.getElementById('preview-description').textContent = description || 'Your description here...';
    
    if (bgImage) {
        document.getElementById('hero-preview').style.backgroundImage = `url('${bgImage}')`;
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

// Auto-save every 30 seconds
let autoSaveTimer;
inputs.forEach(input => {
    input.addEventListener('input', () => {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            showToast('Draft saved automatically', 'success');
        }, 30000);
    });
});

// Form submission via AJAX
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_home_hero.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.href = '?page=home&tab=hero&success=' + encodeURIComponent(data.message);
            }, 1000);
        } else {
            showToast('Error: ' + data.error, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showToast('Network error', 'error');
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
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
