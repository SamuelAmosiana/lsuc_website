/**
 * LSUC FAQs Display Manager
 * Loads and displays FAQs from admin/data/faqs.json
 */

// Configuration
const FAQS_JSON_URL = 'admin/data/faqs.json';
let faqsCache = null;

/**
 * Fetch FAQs from JSON file
 */
async function fetchFAQs() {
    if (faqsCache) {
        return faqsCache;
    }
    
    try {
        const response = await fetch(FAQS_JSON_URL + '?t=' + Date.now()); // Prevent caching
        
        if (!response.ok) {
            if (response.status === 404) {
                console.warn('FAQs JSON file not found. Using empty array.');
                return [];
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        faqsCache = Array.isArray(data) ? data : [];
        
        return faqsCache;
    } catch (error) {
        console.error('Error fetching FAQs:', error);
        return [];
    }
}

/**
 * Get all FAQs sorted by order
 */
async function getAllFAQs() {
    const faqs = await fetchFAQs();
    
    // Sort by order field
    return faqs.sort((a, b) => (a.order || 999) - (b.order || 999));
}

/**
 * Get FAQs by category
 */
async function getFAQsByCategory(category) {
    const faqs = await fetchFAQs();
    
    if (category === 'all') {
        return getAllFAQs();
    }
    
    return faqs.filter(faq => faq.category === category);
}

/**
 * Get featured FAQs
 */
async function getFeaturedFAQs(limit = 5) {
    const faqs = await fetchFAQs();
    
    return faqs
        .filter(faq => faq.featured === true)
        .sort((a, b) => (a.order || 999) - (b.order || 999))
        .slice(0, limit);
}

/**
 * Search FAQs
 */
async function searchFAQs(query) {
    const faqs = await fetchFAQs();
    const searchTerm = query.toLowerCase();
    
    return faqs.filter(faq => 
        faq.question.toLowerCase().includes(searchTerm) ||
        faq.answer.toLowerCase().includes(searchTerm)
    );
}

/**
 * Format answer text (convert line breaks to paragraphs)
 */
function formatAnswer(answer) {
    if (!answer) return '';
    
    // If already contains HTML paragraphs, return as is
    if (answer.includes('<p>') || answer.includes('<br>')) {
        return answer;
    }
    
    // Otherwise, convert line breaks to paragraphs
    return answer.replace(/\n\n/g, '</p><p>').replace(/^/g, '<p>').replace(/$/g, '</p>');
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Display FAQs in the FAQs section
 */
async function displayFAQs() {
    const faqsContainer = document.querySelector('#faqs .cards-grid');
    
    if (!faqsContainer) {
        console.warn('FAQs container not found');
        return;
    }
    
    const faqs = await getAllFAQs();
    
    if (faqs.length === 0) {
        // Show default static FAQs if no dynamic FAQs exist
        console.log('No FAQs found in JSON file, keeping static content');
        return;
    }
    
    // Clear existing content
    faqsContainer.innerHTML = '';
    
    // Create FAQ cards
    faqs.forEach((faq, index) => {
        const card = document.createElement('div');
        card.className = 'card' + (faq.featured ? ' featured' : '');
        card.setAttribute('data-category', faq.category || 'General');
        
        card.innerHTML = `
            <div class="faq-header">
                <span class="faq-number">${index + 1}</span>
                <span class="faq-category-badge">${escapeHtml(faq.category || 'General')}</span>
            </div>
            <h3>${escapeHtml(faq.question)}</h3>
            <div class="faq-answer">
                ${formatAnswer(faq.answer)}
            </div>
            ${faq.featured ? '<div class="featured-badge"><i class="fas fa-star"></i> Featured</div>' : ''}
        `;
        
        faqsContainer.appendChild(card);
    });
}

/**
 * Initialize FAQs when page loads
 */
function initFAQs() {
    // Check if we're on the FAQs page
    const hash = window.location.hash;
    
    if (hash === '#faqs' || hash.includes('#faqs/')) {
        displayFAQs();
    }
    
    // Also check when hash changes
    window.addEventListener('hashchange', () => {
        if (window.location.hash === '#faqs' || window.location.hash.includes('#faqs/')) {
            displayFAQs();
        }
    });
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFAQs);
} else {
    initFAQs();
}

// Export functions for external use
if (typeof window !== 'undefined') {
    window.LSUCFAQs = {
        fetchFAQs,
        getAllFAQs,
        getFAQsByCategory,
        getFeaturedFAQs,
        searchFAQs,
        displayFAQs,
        formatAnswer,
        escapeHtml
    };
}
