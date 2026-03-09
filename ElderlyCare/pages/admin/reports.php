<!-- Reports Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-chart-line"></i> Reports & Analytics
    </h1>
    <p class="dashboard-subtitle">Generate and view reports</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Generate Monthly Report</h2>
    </div>
    <div class="card-body">
        <form action="php/admin/generate_report.php" method="POST" target="_blank">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="report_month">Select Month</label>
                    <input type="month" name="report_month" id="report_month" class="form-control" 
                           value="<?php echo date('Y-m'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <select name="report_type" id="report_type" class="form-control" required>
                        <option value="health">Health Summary</option>
                        <option value="meals">Meal Summary</option>
                        <option value="financial">Financial Summary</option>
                        <option value="emergencies">Emergency Incidents</option>
                        <option value="comprehensive">Comprehensive Report</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="resident_id">Resident (Optional - Leave empty for all residents)</label>
                <select name="resident_id" id="resident_id" class="form-control">
                    <option value="">All Residents</option>
                    <?php foreach ($residents as $resident): ?>
                        <option value="<?php echo $resident['resident_id']; ?>">
                            <?php echo htmlspecialchars($resident['name_en']); ?> (<?php echo htmlspecialchars($resident['room_number']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-file-pdf"></i> Generate Report
            </button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h2 class="card-title">Quick Statistics</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <?php
            $conn = getDBConnection();
            
            // Get health improvement stats
            $health_improvement_sql = "SELECT 
                COUNT(DISTINCT resident_id) as residents_with_improvement
                FROM health_records
                WHERE recorded_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $health_stats = $conn->query($health_improvement_sql)->fetch_assoc();
            
            // Get average ratings
            $ratings_sql = "SELECT 
                category,
                AVG(rating_value) as avg_rating,
                COUNT(*) as total_ratings
                FROM ratings
                WHERE rating_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY category";
            $ratings = $conn->query($ratings_sql)->fetch_all(MYSQLI_ASSOC);
            
            closeDBConnection($conn);
            ?>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $health_stats['residents_with_improvement']; ?></div>
                <div class="stat-label">Residents with Health Updates (30 days)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo count($residents); ?></div>
                <div class="stat-label">Total Active Residents</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($residents, function($r) { return $r['is_premium']; })); ?></div>
                <div class="stat-label">Premium Members</div>
            </div>
            
            <?php if (count($ratings) > 0): ?>
                <?php foreach ($ratings as $rating): ?>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($rating['avg_rating'], 1); ?>/5</div>
                    <div class="stat-label"><?php echo ucfirst(str_replace('_', ' ', $rating['category'])); ?> Rating</div>
                    <small><?php echo $rating['total_ratings']; ?> ratings</small>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
