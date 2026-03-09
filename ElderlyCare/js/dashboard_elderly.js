/**
 * Elderly Dashboard JavaScript
 * Handles dynamic content loading and form interactions
 */

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadHealthHistory();
    loadMealHistory();
    
    // Confirm emergency button press
    const emergencyForm = document.getElementById('emergencyForm');
    if (emergencyForm) {
        emergencyForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to send an emergency alert? Help will be dispatched immediately.')) {
                e.preventDefault();
            }
        });
    }
    
    // Confirm payment
    const payBillForm = document.getElementById('payBillForm');
    if (payBillForm) {
        payBillForm.addEventListener('submit', function(e) {
            const amount = document.getElementById('payment_amount').value;
            if (!confirm(`Confirm payment of ${amount} BDT?`)) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Load health history
 */
function loadHealthHistory() {
    fetch('php/get_health_history.php?limit=10')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayHealthHistory(data.data);
            } else {
                document.getElementById('health-history').innerHTML = 
                    '<p class="text-center text-light">No health records found.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading health history:', error);
            document.getElementById('health-history').innerHTML = 
                '<p class="alert alert-danger">Error loading health history.</p>';
        });
}

/**
 * Display health history
 */
function displayHealthHistory(records) {
    const container = document.getElementById('health-history');
    
    if (records.length === 0) {
        container.innerHTML = '<p class="text-center text-light">No health records found.</p>';
        return;
    }
    
    let html = '<table class="table"><thead><tr>';
    html += '<th>Date</th><th>Sugar Level</th><th>Blood Pressure</th>';
    html += '</tr></thead><tbody>';
    
    records.forEach(record => {
        const date = new Date(record.recorded_date).toLocaleDateString();
        const sugar = record.sugar_level ? `${record.sugar_level} mg/dL` : '-';
        const bp = record.blood_pressure ? escapeHtml(record.blood_pressure) : '-';
        
        html += '<tr>';
        html += `<td>${date}</td>`;
        html += `<td>${sugar}</td>`;
        html += `<td>${bp}</td>`;
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

/**
 * Load meal history
 */
function loadMealHistory() {
    fetch('php/get_meal_history.php?days=7')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayMealHistory(data.data);
            } else {
                document.getElementById('meal-history').innerHTML = 
                    '<p class="text-center text-light">No meal records found.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading meal history:', error);
            document.getElementById('meal-history').innerHTML = 
                '<p class="alert alert-danger">Error loading meal history.</p>';
        });
}

/**
 * Display meal history
 */
function displayMealHistory(meals) {
    const container = document.getElementById('meal-history');
    
    if (meals.length === 0) {
        container.innerHTML = '<p class="text-center text-light">No meal records found.</p>';
        return;
    }
    
    // Group meals by date
    const mealsByDate = {};
    meals.forEach(meal => {
        const date = meal.meal_date;
        if (!mealsByDate[date]) {
            mealsByDate[date] = [];
        }
        mealsByDate[date].push(meal);
    });
    
    let html = '<div style="display: flex; flex-direction: column; gap: 15px;">';
    
    Object.keys(mealsByDate).sort().reverse().forEach(date => {
        html += '<div style="border: 1px solid #dee2e6; border-radius: 8px; padding: 10px;">';
        html += `<strong>${new Date(date).toLocaleDateString()}</strong><br>`;
        
        // Display sugar/salt/spicy intake
        const meal = mealsByDate[date][0]; // Get first record (all intake data is in one record)
        if (meal) {
            html += `<div style="padding: 5px 0;">`;
            html += `<strong>Sugar Intake:</strong> ${escapeHtml(meal.sugar_intake || '-')}<br>`;
            html += `<strong>Salt Intake:</strong> ${escapeHtml(meal.salt_intake || '-')}<br>`;
            html += `<strong>Spicy Intake:</strong> ${escapeHtml(meal.spicy_intake || '-')}`;
            html += `</div>`;
        }
        
        html += '</div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
