/**
 * ============================================================
 *  LSUC Site Configuration
 * ============================================================
 *  To update the institution name across the entire website,
 *  change the value of SITE_NAME below. The change will
 *  automatically propagate to every page that loads this script.
 * ============================================================
 */

const SITE_CONFIG = {
    SITE_NAME: "Lusaka South University College",
    SITE_SHORT_NAME: "LSUC",
    SITE_TAGLINE: "Dream, Explore, Acquire",
    SITE_URL: "https://lsc.edu.zm",
};

/**
 * Returns the full institution name.
 * @returns {string}
 */
function getSiteName() {
    return SITE_CONFIG.SITE_NAME;
}

/**
 * Returns the short institution name / acronym.
 * @returns {string}
 */
function getSiteShortName() {
    return SITE_CONFIG.SITE_SHORT_NAME;
}

/**
 * Returns the institution tagline.
 * @returns {string}
 */
function getSiteTagline() {
    return SITE_CONFIG.SITE_TAGLINE;
}

/**
 * Injects the site name into every element that carries the
 * data-site-name attribute and, optionally, updates the page
 * <title> if it contains the placeholder text.
 *
 * Usage in HTML:
 *   <span data-site-name></span>
 *   <span data-site-short-name></span>
 *   <span data-site-tagline></span>
 */
(function injectSiteConfig() {
    function applyConfig() {
        // -- Full site name placeholders --
        document.querySelectorAll("[data-site-name]").forEach(function (el) {
            el.textContent = SITE_CONFIG.SITE_NAME;
        });

        // -- Short name / acronym placeholders --
        document.querySelectorAll("[data-site-short-name]").forEach(function (el) {
            el.textContent = SITE_CONFIG.SITE_SHORT_NAME;
        });

        // -- Tagline placeholders --
        document.querySelectorAll("[data-site-tagline]").forEach(function (el) {
            el.textContent = SITE_CONFIG.SITE_TAGLINE;
        });

        // -- <title> tag: replace the placeholder token if present --
        if (document.title.includes("{{SITE_NAME}}")) {
            document.title = document.title.replace(/\{\{SITE_NAME\}\}/g, SITE_CONFIG.SITE_NAME);
        }
    }

    // Run as soon as the DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", applyConfig);
    } else {
        applyConfig();
    }
})();
