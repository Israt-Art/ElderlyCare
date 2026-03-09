<!-- Admin Overview Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-tachometer-alt"></i> Dashboard Overview
    </h1>
    <p class="dashboard-subtitle">Today: <?php echo formatDate(date('Y-m-d'), 'l, d F Y'); ?></p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_residents']; ?></div>
        <div class="stat-label">Total Residents</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['today_health_updates']; ?></div>
        <div class="stat-label">Health Updates Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $stats['today_meals']; ?></div>
        <div class="stat-label">Meal Selections Today</div>
    </div>
    <div class="stat-card <?php echo $stats['pending_emergencies'] > 0 ? 'stat-card-danger' : ''; ?>">
        <div class="stat-value"><?php echo $stats['pending_emergencies']; ?></div>
        <div class="stat-label">Pending Emergencies</div>
    </div>
</div>

<!-- Pending Emergencies Alert -->
<?php if (count($emergencies) > 0): ?>
<div class="card card-danger">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-exclamation-triangle"></i> Pending Emergency Alerts
            <a href="?tab=emergencies" class="btn btn-sm btn-danger">View All</a>
        </h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($emergencies, 0, 5) as $emergency): ?>
                    <tr>
                        <td><?php echo formatDateTime($emergency['emergency_time']); ?></td>
                        <td><strong><?php echo htmlspecialchars($emergency['room_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($emergency['name_en']); ?></td>
                        <td><span class="badge badge-danger"><?php echo ucfirst($emergency['emergency_type']); ?></span></td>
                        <td>
                            <a href="php/admin/respond_emergency.php?id=<?php echo $emergency['emergency_id']; ?>" 
                               class="btn btn-sm btn-success">Respond</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Today's Health Updates -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-heartbeat"></i> Today's Health Updates
            <a href="?tab=health" class="btn btn-sm btn-secondary">View All</a>
        </h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Sugar Level</th>
                        <th>Blood Pressure</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($today_health) > 0): ?>
                        <?php foreach (array_slice($today_health, 0, 10) as $health): ?>
                        <tr>
                            <td><?php echo date('h:i A', strtotime($health['recorded_time'])); ?></td>
                            <td><?php echo htmlspecialchars($health['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($health['name_en']); ?></td>
                            <td><?php echo $health['sugar_level'] ? $health['sugar_level'] . ' mg/dL' : '-'; ?></td>
                            <td><?php echo htmlspecialchars($health['blood_pressure'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-light">No health updates for today yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Today's Meal Selections -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-utensils"></i> Today's Meal Selections
            <a href="?tab=meals" class="btn btn-sm btn-secondary">View All</a>
        </h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Sugar</th>
                        <th>Salt</th>
                        <th>Spicy</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($today_meals) > 0): ?>
                        <?php 
                        // Group by resident to show one row per resident
                        $meals_by_resident = [];
                        foreach (array_slice($today_meals, 0, 15) as $meal) {
                            $resident_id = $meal['resident_id'];
                            if (!isset($meals_by_resident[$resident_id])) {
                                $meals_by_resident[$resident_id] = [
                                    'name' => $meal['name_en'],
                                    'room' => $meal['room_number'],
                                    'sugar' => $meal['sugar_intake'] ?? '-',
                                    'salt' => $meal['salt_intake'] ?? '-',
                                    'spicy' => $meal['spicy_intake'] ?? '-',
                                    'time' => $meal['selected_at'] ?? null
                                ];
                            }
                        }
                        foreach ($meals_by_resident as $resident_id => $data): 
                        ?>
                        <tr>
                            <td><?php echo $data['time'] ? formatDateTime($data['time']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($data['room']); ?></td>
                            <td><?php echo htmlspecialchars($data['name']); ?></td>
                            <td>
                                <?php if ($data['sugar'] !== '-'): ?>
                                    <span class="badge badge-<?php echo $data['sugar'] === 'High' ? 'danger' : ($data['sugar'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['sugar']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['salt'] !== '-'): ?>
                                    <span class="badge badge-<?php echo $data['salt'] === 'High' ? 'danger' : ($data['salt'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['salt']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['spicy'] !== '-'): ?>
                                    <span class="badge badge-<?php echo $data['spicy'] === 'High' ? 'danger' : ($data['spicy'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['spicy']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-light">No meal selections for today yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
