/**
 * Simple Snowfall Effect for LSUC Website
 * Creates a festive snowfall effect with colorful particles
 */

(function() {
    // Check if snowfall effect is already initialized
    if (window.simpleSnowfallInitialized) return;
    window.simpleSnowfallInitialized = true;

    // Create snowfall effect
    function createSnowfall() {
        // Create container for snow particles
        const snowContainer = document.createElement('div');
        snowContainer.id = 'simple-snow-container';
        snowContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 99998;
            overflow: hidden;
        `;
        
        // Insert as first child to ensure it covers the entire viewport
        if (document.body.firstChild) {
            document.body.insertBefore(snowContainer, document.body.firstChild);
        } else {
            document.body.appendChild(snowContainer);
        }

        // Check if container was successfully added
        if (!document.getElementById('simple-snow-container')) {
            console.error('Failed to create snow container');
            return;
        }

        // Festive color palette for particles
        const snowColors = [
            '#ffffff', // White
            '#ffd700', // Gold
            '#ff8c00', // Orange (school color)
            '#2e8b57', // Green (school color)
            '#87ceeb', // Sky blue
            '#ffc0cb', // Pink
            '#c0c0c0', // Silver
            '#98fb98', // Pale green
            '#dda0dd', // Plum
            '#f0e68c'  // Khaki
        ];

        // Create individual snowflake
        function createSnowflake() {
            const snowflake = document.createElement('div');
            snowflake.className = 'simple-snowflake';
            
            // Random properties for natural look
            const startX = Math.random() * 100;
            const size = Math.random() * 8 + 3;
            const duration = Math.random() * 8 + 4;
            const delay = Math.random() * 5;
            const opacity = Math.random() * 0.8 + 0.2;
            const color = snowColors[Math.floor(Math.random() * snowColors.length)];
            
            snowflake.style.cssText = `
                position: absolute;
                top: -10px;
                left: ${startX}%;
                width: ${size}px;
                height: ${size}px;
                background-color: ${color};
                border-radius: 50%;
                opacity: ${opacity};
                animation: simpleSnowFall ${duration}s linear ${delay}s infinite;
                z-index: 99999;
                box-shadow: 0 0 ${size}px ${color};
            `;
            
            return snowflake;
        }

        // Generate snowflakes
        function generateSnowflakes(count = 100) {
            const snowContainer = document.getElementById('simple-snow-container');
            if (!snowContainer) return;
            
            for (let i = 0; i < count; i++) {
                setTimeout(() => {
                    const snowflake = createSnowflake();
                    if (snowContainer) {
                        snowContainer.appendChild(snowflake);
                        
                        // Remove after animation
                        setTimeout(() => {
                            if (snowflake.parentNode) {
                                snowflake.remove();
                            }
                        }, (duration + 1) * 1000);
                    }
                }, i * 100);
            }
        }

        // Initial generation
        generateSnowflakes(100);
        
        // Continuous generation
        setInterval(() => {
            if (document.getElementById('simple-snow-container')) {
                generateSnowflakes(5);
            }
        }, 2000);
    }

    // Add CSS animations
    function addSnowStyles() {
        // Check if styles already exist
        if (document.getElementById('simple-snowfall-styles')) return;
        
        const snowStyles = document.createElement('style');
        snowStyles.id = 'simple-snowfall-styles';
        snowStyles.textContent = `
            @keyframes simpleSnowFall {
                0% {
                    transform: translateY(-10px) translateX(0);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh) translateX(30px);
                    opacity: 0;
                }
            }
            
            /* Reduce motion for accessibility */
            @media (prefers-reduced-motion: reduce) {
                .simple-snowflake {
                    animation: none !important;
                    display: none;
                }
            }
        `;
        document.head.appendChild(snowStyles);
    }

    // Initialize snowfall effect when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            addSnowStyles();
            createSnowfall();
        });
    } else {
        // DOM is already loaded
        addSnowStyles();
        createSnowfall();
    }
})();