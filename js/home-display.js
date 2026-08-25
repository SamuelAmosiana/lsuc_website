/**
 * ============================================================
 *  LSUC Homepage Dynamic Content Display
 * ============================================================
 *  Loads hero section, core values, and campus gallery images
 *  from the admin JSON files dynamically.
 * ============================================================
 */

let campusCarouselInterval = null;

window.initCampusCarousel = function() {
    // Clear old interval if exists
    if (campusCarouselInterval) {
        clearInterval(campusCarouselInterval);
    }
    
    const slides = document.querySelectorAll('.carousel-image');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;
    
    let currentSlide = 0;
    
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        if (slides[index]) slides[index].classList.add('active');
        if (indicators[index]) indicators[index].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }
    
    campusCarouselInterval = setInterval(nextSlide, 3000);
    
    // Add click handlers for indicators
    indicators.forEach((indicator, index) => {
        // Clone and replace to remove old event listeners if any
        const newIndicator = indicator.cloneNode(true);
        indicator.parentNode.replaceChild(newIndicator, indicator);
        
        newIndicator.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            
            clearInterval(campusCarouselInterval);
            campusCarouselInterval = setInterval(nextSlide, 3000);
        });
    });
    
    // Pause on hover
    const carousel = document.querySelector('.campus-carousel');
    if (carousel) {
        // Clone and replace to clear old listeners
        const newCarousel = carousel.cloneNode(false);
        while (carousel.firstChild) {
            newCarousel.appendChild(carousel.firstChild);
        }
        carousel.parentNode.replaceChild(newCarousel, carousel);
        
        newCarousel.addEventListener('mouseenter', () => {
            clearInterval(campusCarouselInterval);
        });
        
        newCarousel.addEventListener('mouseleave', () => {
            campusCarouselInterval = setInterval(nextSlide, 3000);
        });
    }
};

const LSUCHomepage = {
    init: function() {
        this.loadHero();
        this.loadCoreValues();
        this.loadGallery();
    },

    loadHero: function() {
        fetch('admin/data/home_hero.json')
            .then(response => {
                if (!response.ok) throw new Error('Hero file not found');
                return response.json();
            })
            .then(data => {
                if (!data) return;
                
                // Update heading
                const headingEl = document.querySelector('#heroSection .hero-content h1');
                if (headingEl && data.heading) {
                    headingEl.textContent = data.heading;
                }
                
                // Update motto
                const mottoEl = document.querySelector('#heroSection .hero-content .motto');
                if (mottoEl && data.motto) {
                    mottoEl.textContent = `"${data.motto}"`;
                }
                
                // Update description
                const descEl = document.querySelector('#heroSection .hero-content p:not(.motto)');
                if (descEl && data.description) {
                    descEl.textContent = data.description;
                }
                
                // Update CTA
                const ctaEl = document.querySelector('#heroSection .hero-content .cta-button');
                if (ctaEl) {
                    if (data.show_cta) {
                        ctaEl.style.display = 'inline-block';
                        ctaEl.textContent = data.cta_text || 'Discover More';
                        ctaEl.setAttribute('href', data.cta_link || '#about');
                        
                        // Handle internal hash links with showPage
                        ctaEl.onclick = function(e) {
                            const href = ctaEl.getAttribute('href');
                            if (href && href.startsWith('#')) {
                                e.preventDefault();
                                const pageId = href.substring(1);
                                if (typeof showPage === 'function') {
                                    showPage(pageId);
                                }
                            }
                        };
                    } else {
                        ctaEl.style.display = 'none';
                    }
                }
                
                // Update Background Image (replace slideshow slides with a single slide)
                if (data.background_image) {
                    const heroSection = document.getElementById('heroSection');
                    if (heroSection) {
                        // Remove existing slides
                        heroSection.querySelectorAll('.hero-slide').forEach(el => el.remove());
                        
                        // Create one active slide
                        const activeSlide = document.createElement('div');
                        activeSlide.className = 'hero-slide active';
                        activeSlide.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${data.background_image}')`;
                        
                        // Insert slide as the first child
                        heroSection.insertBefore(activeSlide, heroSection.firstChild);
                        
                        // Hide arrows and indicators since it's a single image
                        const prevBtn = document.getElementById('heroPrev');
                        const nextBtn = document.getElementById('heroNext');
                        const dots = document.getElementById('heroDots');
                        if (prevBtn) prevBtn.style.display = 'none';
                        if (nextBtn) nextBtn.style.display = 'none';
                        if (dots) dots.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Using default hero section content.', error.message);
            });
    },

    loadCoreValues: function() {
        fetch('admin/data/home_values.json')
            .then(response => {
                if (!response.ok) throw new Error('Values file not found');
                return response.json();
            })
            .then(data => {
                if (!data || data.length === 0) return;
                
                // Sort by order field
                data.sort((a, b) => (a.order ?? 999) - (b.order ?? 999));
                
                const gridEl = document.getElementById('home-core-values');
                if (!gridEl) return;
                
                // Clear existing cards
                gridEl.innerHTML = '';
                
                // Map standard titles to specific images
                const bgMap = {
                    'excellence': './img/excellence.jpg',
                    'integrity': './img/integrity.jpg',
                    'innovation': './img/innovation.jpg'
                };
                
                data.forEach(item => {
                    const titleLower = item.title.toLowerCase().trim();
                    const bgImage = bgMap[titleLower] || './img/lsuc site cover img.jpeg';
                    const icon = item.icon || 'fa-star';
                    
                    const cardHtml = `
                        <div class="card value-card">
                            <div class="value-card-bg" style="background-image: url('${bgImage}');"></div>
                            <div class="value-card-overlay"></div>
                            <div class="value-card-content">
                                <div class="card-icon"><i class="fas ${icon}"></i></div>
                                <h3>${item.title}</h3>
                                <p>${item.description}</p>
                            </div>
                        </div>
                    `;
                    gridEl.insertAdjacentHTML('beforeend', cardHtml);
                });
            })
            .catch(error => {
                console.log('Using default core values content.', error.message);
            });
    },

    loadGallery: function() {
        fetch('admin/data/home_gallery.json')
            .then(response => {
                if (!response.ok) throw new Error('Gallery file not found');
                return response.json();
            })
            .then(data => {
                if (!data || data.length === 0) return;
                
                // 1. Update Gallery Grid
                const gridEl = document.getElementById('home-campus-gallery');
                if (gridEl) {
                    gridEl.innerHTML = '';
                    
                    // Helper to map caption keywords to FA icons
                    const getIconClass = (caption) => {
                        const cap = caption.toLowerCase();
                        if (cap.includes('lab')) return 'fa-flask';
                        if (cap.includes('computer')) return 'fa-desktop';
                        if (cap.includes('library')) return 'fa-book';
                        if (cap.includes('sport') || cap.includes('field') || cap.includes('gym')) return 'fa-running';
                        if (cap.includes('cafeteria') || cap.includes('canteen') || cap.includes('dining')) return 'fa-utensils';
                        if (cap.includes('auditorium') || cap.includes('theatre') || cap.includes('hall')) return 'fa-theater-masks';
                        if (cap.includes('grounds') || cap.includes('campus') || cap.includes('garden')) return 'fa-tree';
                        return 'fa-images';
                    };
                    
                    data.forEach(image => {
                        const icon = getIconClass(image.caption);
                        const itemHtml = `
                            <div class="gallery-item" data-image="${image.path}" onclick="openImagePreview(this)">
                                <img src="${image.path}" alt="${image.caption}">
                                <div class="gallery-overlay">
                                    <h4><i class="fas ${icon}"></i> ${image.caption}</h4>
                                </div>
                            </div>
                        `;
                        gridEl.insertAdjacentHTML('beforeend', itemHtml);
                    });
                }
                
                // 2. Update Campus Carousel
                const carouselEl = document.querySelector('.campus-carousel');
                if (carouselEl) {
                    // We only use the first 6 gallery images for the carousel slider
                    const carouselImages = data.slice(0, 6);
                    
                    // Rebuild the carousel images and indicators
                    const oldImages = carouselEl.querySelectorAll('.carousel-image');
                    const oldIndicatorsContainer = carouselEl.querySelector('.carousel-indicators');
                    
                    // Remove old slides
                    oldImages.forEach(el => el.remove());
                    if (oldIndicatorsContainer) oldIndicatorsContainer.innerHTML = '';
                    
                    // Insert new slides before overlay
                    const overlayEl = carouselEl.querySelector('.carousel-overlay');
                    
                    carouselImages.forEach((image, index) => {
                        // Create slide
                        const slide = document.createElement('div');
                        slide.className = `carousel-image ${index === 0 ? 'active' : ''}`;
                        slide.style.backgroundImage = `url('${image.path}')`;
                        carouselEl.insertBefore(slide, overlayEl);
                        
                        // Create indicator
                        if (oldIndicatorsContainer) {
                            const indicator = document.createElement('div');
                            indicator.className = `indicator ${index === 0 ? 'active' : ''}`;
                            oldIndicatorsContainer.appendChild(indicator);
                        }
                    });
                    
                    // Re-initialize the campus carousel slider with the new elements!
                    if (typeof window.initCampusCarousel === 'function') {
                        window.initCampusCarousel();
                    }
                }
            })
            .catch(error => {
                console.log('Using default campus gallery content.', error.message);
            });
    }
};

// Initialize on page load
window.addEventListener('DOMContentLoaded', () => {
    LSUCHomepage.init();
});
