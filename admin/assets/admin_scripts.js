// Admin Dashboard JavaScript

// Toast notification system
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Auto-save functionality
let autoSaveTimer;
function enableAutoSave(formSelector, saveUrl) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    form.setAttribute('data-unsaved', 'true');
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                autoSave(form, saveUrl);
            }, 30000); // Save every 30 seconds
        });
    });
}

async function autoSave(form, saveUrl) {
    const formData = new FormData(form);
    
    try {
        const response = await fetch(saveUrl, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            form.removeAttribute('data-unsaved');
            showToast('Changes saved automatically', 'success');
        }
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
}

// Image preview
function setupImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (!input || !preview) return;
    
    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Confirm delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Bulk actions
function setupBulkActions(selectAllCheckbox, itemCheckboxes, actionButton) {
    if (!selectAllCheckbox || !itemCheckboxes || !actionButton) return;
    
    selectAllCheckbox.addEventListener('change', (e) => {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateBulkActionButton();
    });
    
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateBulkActionButton();
        });
    });
    
    function updateBulkActionButton() {
        const checkedCount = Array.from(itemCheckboxes).filter(cb => cb.checked).length;
        if (checkedCount > 0) {
            actionButton.style.display = 'flex';
            actionButton.querySelector('span').textContent = 
                `${checkedCount} selected`;
        } else {
            actionButton.style.display = 'none';
        }
    }
}

// Search functionality
function setupSearch(searchInput, targetElements) {
    if (!searchInput || !targetElements) return;
    
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        
        targetElements.forEach(element => {
            const text = element.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                element.style.display = '';
            } else {
                element.style.display = 'none';
            }
        });
    });
}

// Modal handling
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modals on outside click
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    return isValid;
}

// File upload with progress
function uploadFile(input, progressCallback) {
    const file = input.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        const percentComplete = (e.loaded / e.total) * 100;
        if (progressCallback) {
            progressCallback(percentComplete);
        }
    });
    
    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            showToast('File uploaded successfully', 'success');
        } else {
            showToast('Upload failed', 'error');
        }
    });
    
    xhr.addEventListener('error', () => {
        showToast('Upload error', 'error');
    });
    
    xhr.open('POST', 'api/upload_file.php');
    xhr.send(formData);
}

// Session timer
function startSessionTimer(duration, display) {
    let timer = duration, minutes, seconds;
    const interval = setInterval(() => {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        
        display.textContent = minutes + ":" + seconds;
        
        if (--timer < 0) {
            clearInterval(interval);
            showToast('Session expired. Please login again.', 'warning');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        }
    }, 1000);
}

// Initialize session timer on page load
window.addEventListener('load', () => {
    const sessionTimerDisplay = document.getElementById('session-timer');
    if (sessionTimerDisplay) {
        startSessionTimer(1800, sessionTimerDisplay); // 30 minutes
    }
    
    // Setup all image previews
    setupImagePreview('image-upload', 'image-preview');
});

// Rich text editor placeholder (for future TinyMCE/CKEditor integration)
function initRichTextEditor(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    // Future: Initialize TinyMCE or CKEditor here
    // For now, just enhance the textarea
    textarea.style.minHeight = '200px';
    textarea.style.fontFamily = 'Arial, sans-serif';
    textarea.style.fontSize = '14px';
}

// Initialize all rich text editors on page load
document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea.rich-text');
    textareas.forEach(textarea => {
        initRichTextEditor(textarea.id);
    });
});

// Export data
function exportData(type) {
    window.location.href = `api/export_data.php?type=${type}`;
}

// Import data
function importData(input) {
    const file = input.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    fetch('api/import_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Data imported successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Import failed: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showToast('Import error', 'error');
    });
}

// Print function
function printSection(sectionId) {
    const content = document.getElementById(sectionId);
    if (!content) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
