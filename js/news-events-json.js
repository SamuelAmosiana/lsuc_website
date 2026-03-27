/**
 * LSUC News & Events Manager - JSON File Version
 * 
 * This version reads events from a JSON file instead of localStorage
 * to avoid storage quota errors with large image data.
 * 
 * Usage: Include this script instead of news-events-manager.js
 */

// Configuration
const EVENTS_JSON_URL = 'admin/data/events.json';

// Cache for events
let eventsCache = null;
let lastFetchTime = 0;
const CACHE_DURATION = 30000; // 30 seconds

/**
 * Fetch events from JSON file
 */
async function fetchEvents() {
    const now = Date.now();
    
    // Return cached data if still valid
    if (eventsCache && (now - lastFetchTime) < CACHE_DURATION) {
        return eventsCache;
    }
    
    try {
        const response = await fetch(EVENTS_JSON_URL + '?t=' + now); // Prevent caching
        
        if (!response.ok) {
            if (response.status === 404) {
                console.warn('Events JSON file not found. Using empty array.');
                return [];
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        eventsCache = Array.isArray(data) ? data : [];
        lastFetchTime = now;
        
        return eventsCache;
    } catch (error) {
        console.error('Error fetching events:', error);
        return [];
    }
}

/**
 * Get all events (public API)
 */
async function getEvents() {
    return await fetchEvents();
}

/**
 * Get latest news events
 */
async function getLatestNews(limit = 6) {
    const events = await fetchEvents();
    return events
        .filter(event => event.category === 'Latest News')
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, limit);
}

/**
 * Get upcoming events
 */
async function getUpcomingEvents(limit = 6) {
    const events = await fetchEvents();
    const today = new Date().toISOString().split('T')[0];
    
    return events
        .filter(event => 
            event.category === 'Upcoming Event' && 
            event.date >= today
        )
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, limit);
}

/**
 * Get past events
 */
async function getPastEvents(page = 1, limit = 12) {
    const events = await fetchEvents();
    
    const pastEvents = events.filter(event => 
        event.category === 'Past Event'
    ).sort((a, b) => new Date(b.date) - new Date(a.date));
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    
    return {
        events: pastEvents.slice(startIndex, endIndex),
        total: pastEvents.length,
        page: page,
        totalPages: Math.ceil(pastEvents.length / limit)
    };
}

/**
 * Get job vacancies
 */
async function getJobVacancies(limit = 10) {
    const events = await fetchEvents();
    return events
        .filter(event => event.category === 'Job Vacancy')
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, limit);
}

/**
 * Get event by ID
 */
async function getEventById(id) {
    const events = await fetchEvents();
    return events.find(event => event.id === id);
}

/**
 * Search events
 */
async function searchEvents(query, category = 'all') {
    const events = await fetchEvents();
    const searchTerm = query.toLowerCase();
    
    let filtered = events;
    
    if (category !== 'all') {
        filtered = filtered.filter(event => event.category === category);
    }
    
    if (searchTerm) {
        filtered = filtered.filter(event => {
            return event.title.toLowerCase().includes(searchTerm) ||
                   event.shortDescription.toLowerCase().includes(searchTerm) ||
                   event.fullDescription.toLowerCase().includes(searchTerm);
        });
    }
    
    return filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
}

/**
 * Filter events by category
 */
async function filterByCategory(category) {
    const events = await fetchEvents();
    
    if (category === 'all') {
        return events;
    }
    
    return events.filter(event => event.category === category);
}

/**
 * Sort events by date
 */
function sortEvents(events, order = 'newest') {
    return [...events].sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        
        return order === 'newest' ? dateB - dateA : dateA - dateB;
    });
}

/**
 * Get featured events
 */
async function getFeaturedEvents(limit = 3) {
    const events = await fetchEvents();
    return events
        .filter(event => event.featured === true)
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, limit);
}

/**
 * Format date for display
 */
function formatDate(dateString, format = 'long') {
    const date = new Date(dateString);
    const options = format === 'long' 
        ? { year: 'numeric', month: 'long', day: 'numeric' }
        : { year: 'numeric', month: 'short', day: 'numeric' };
    
    return date.toLocaleDateString('en-US', options);
}

/**
 * Time ago formatter
 */
function timeAgo(dateString) {
    const seconds = Math.floor((new Date() - new Date(dateString)) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " years ago";
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " months ago";
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " days ago";
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " hours ago";
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " minutes ago";
    
    return Math.floor(seconds) + " seconds ago";
}

/**
 * Truncate text
 */
function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
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
 * Initialize news sections on homepage
 */
async function initNewsSections() {
    try {
        // Latest News
        const latestNews = await getLatestNews(6);
        displayLatestNews(latestNews);
        
        // Upcoming Events
        const upcomingEvents = await getUpcomingEvents(6);
        displayUpcomingEvents(upcomingEvents);
        
        // Past Events
        const pastEventsData = await getPastEvents(1, 12);
        displayPastEvents(pastEventsData.events);
        
    } catch (error) {
        console.error('Error initializing news sections:', error);
    }
}

/**
 * Display latest news cards
 */
function displayLatestNews(events) {
    const container = document.getElementById('latest-news-container');
    if (!container) return;
    
    if (events.length === 0) {
        container.innerHTML = '<p class="no-events">No latest news available.</p>';
        return;
    }
    
    container.innerHTML = events.map(event => `
        <div class="event-card" data-category="${escapeHtml(event.category)}">
            ${event.image ? `
                <div class="event-image">
                    <img src="${event.image.startsWith('data:') ? event.image : event.image}" 
                         alt="${escapeHtml(event.title)}" 
                         onerror="this.style.display='none'">
                </div>
            ` : ''}
            <div class="event-content">
                <span class="event-category">${escapeHtml(event.category)}</span>
                <h3>${escapeHtml(event.title)}</h3>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> ${formatDate(event.date)}</span>
                    <span><i class="fas fa-user"></i> ${escapeHtml(event.author || 'Admin')}</span>
                </div>
                <p>${escapeHtml(truncateText(event.shortDescription, 120))}</p>
                <a href="index.html#event-detail?id=${encodeURIComponent(event.id)}" class="read-more">
                    Read More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    `).join('');
}

/**
 * Display upcoming events cards
 */
function displayUpcomingEvents(events) {
    const container = document.getElementById('upcoming-events-container');
    if (!container) return;
    
    if (events.length === 0) {
        container.innerHTML = '<p class="no-events">No upcoming events scheduled.</p>';
        return;
    }
    
    container.innerHTML = events.map(event => `
        <div class="event-card ${event.featured ? 'featured' : ''}" data-category="${escapeHtml(event.category)}">
            ${event.image ? `
                <div class="event-image">
                    <img src="${event.image.startsWith('data:') ? event.image : event.image}" 
                         alt="${escapeHtml(event.title)}"
                         onerror="this.style.display='none'">
                </div>
            ` : ''}
            <div class="event-content">
                <span class="event-category">${escapeHtml(event.category)}</span>
                <h3>${escapeHtml(event.title)}</h3>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> ${formatDate(event.date)}</span>
                    <span><i class="fas fa-user"></i> ${escapeHtml(event.author || 'Admin')}</span>
                </div>
                <p>${escapeHtml(truncateText(event.shortDescription, 120))}</p>
                <a href="index.html#event-detail?id=${encodeURIComponent(event.id)}" class="read-more">
                    Read More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    `).join('');
}

/**
 * Display past events cards
 */
function displayPastEvents(events) {
    const container = document.getElementById('past-events-container');
    if (!container) return;
    
    if (events.length === 0) {
        container.innerHTML = '<p class="no-events">No past events to display.</p>';
        return;
    }
    
    container.innerHTML = events.map(event => `
        <div class="event-card" data-category="${escapeHtml(event.category)}">
            ${event.image ? `
                <div class="event-image">
                    <img src="${event.image.startsWith('data:') ? event.image : event.image}" 
                         alt="${escapeHtml(event.title)}"
                         onerror="this.style.display='none'">
                </div>
            ` : ''}
            <div class="event-content">
                <span class="event-category">${escapeHtml(event.category)}</span>
                <h3>${escapeHtml(event.title)}</h3>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> ${formatDate(event.date)}</span>
                    <span><i class="fas fa-user"></i> ${escapeHtml(event.author || 'Admin')}</span>
                </div>
                <p>${escapeHtml(truncateText(event.shortDescription, 120))}</p>
                <a href="index.html#event-detail?id=${encodeURIComponent(event.id)}" class="read-more">
                    Read More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    `).join('');
}

/**
 * Display event detail page
 */
async function displayEventDetail(eventId) {
    const event = await getEventById(eventId);
    
    if (!event) {
        document.getElementById('event-detail-content').innerHTML = `
            <div class="error-message">
                <h2>Event Not Found</h2>
                <p>The requested event could not be found.</p>
                <a href="index.html#news" class="btn btn-primary">Back to News</a>
            </div>
        `;
        return;
    }
    
    const content = `
        <div class="event-detail-full">
            ${event.image ? `
                <div class="event-detail-image">
                    <img src="${event.image.startsWith('data:') ? event.image : event.image}" 
                         alt="${escapeHtml(event.title)}">
                </div>
            ` : ''}
            
            <div class="event-detail-header">
                <span class="event-category-badge">${escapeHtml(event.category)}</span>
                ${event.featured ? '<span class="featured-badge">⭐ Featured</span>' : ''}
                <h1>${escapeHtml(event.title)}</h1>
                
                <div class="event-meta-large">
                    <span><i class="fas fa-calendar"></i> ${formatDate(event.date, 'long')}</span>
                    <span><i class="fas fa-user"></i> ${escapeHtml(event.author || 'Admin')}</span>
                    <span><i class="fas fa-clock"></i> ${timeAgo(event.date)}</span>
                </div>
            </div>
            
            <div class="event-detail-body">
                ${event.fullDescription}
            </div>
            
            <div class="event-detail-footer">
                <a href="index.html#news" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to All Events
                </a>
            </div>
        </div>
    `;
    
    document.getElementById('event-detail-content').innerHTML = content;
}

// Export functions for external use
if (typeof window !== 'undefined') {
    window.LSUCEvents = {
        getEvents,
        getLatestNews,
        getUpcomingEvents,
        getPastEvents,
        getJobVacancies,
        getEventById,
        searchEvents,
        filterByCategory,
        sortEvents,
        getFeaturedEvents,
        formatDate,
        timeAgo,
        truncateText,
        escapeHtml,
        initNewsSections,
        displayEventDetail
    };
}

// Auto-initialize if on news page
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    
    if (hash === '#news' || hash.includes('#news/')) {
        initNewsSections();
    } else if (hash.startsWith('#event-detail')) {
        const params = new URLSearchParams(hash.split('?')[1]);
        const eventId = params.get('id');
        if (eventId) {
            displayEventDetail(eventId);
        }
    }
});
