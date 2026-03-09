/**
 * Admin Dashboard JavaScript
 * Handles admin dashboard interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh pending emergencies count every 30 seconds
    if (document.querySelector('.badge-danger')) {
        setInterval(function() {
            refreshEmergencyCount();
        }, 30000);
    }
    
    // Confirm actions
    const confirmLinks = document.querySelectorAll('[href*="respond_emergency"]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Mark this emergency as responded? This will update the status and response time.')) {
                e.preventDefault();
            }
        });
    });
});

/**
 * Refresh emergency count (for real-time updates)
 */
function refreshEmergencyCount() {
    // This could be implemented with AJAX to fetch real-time data
    console.log('Checking for new emergencies...');
}
