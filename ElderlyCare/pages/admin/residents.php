<!-- Residents Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-users"></i> Residents List
    </h1>
    <p class="dashboard-subtitle">Total Active Residents: <?php echo count($residents); ?></p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">All Residents</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Diet Type</th>
                        <th>Medical Conditions</th>
                        <th>Health Update Today</th>
                        <th>Premium</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($residents as $resident): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($resident['room_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($resident['name_en']); ?></td>
                        <td><?php echo $resident['age']; ?> years</td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo ucfirst(str_replace('-', ' ', $resident['diet_type'] ?? 'normal')); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $conditions = [];
                            if ($resident['has_diabetes']) $conditions[] = 'Diabetes';
                            if ($resident['has_blood_pressure']) $conditions[] = 'BP';
                            if ($resident['has_heart_condition']) $conditions[] = 'Heart';
                            echo implode(', ', $conditions) ?: 'None';
                            ?>
                        </td>
                        <td>
                            <?php if ($resident['has_health_update_today']): ?>
                                <span class="badge badge-success">✓ Updated</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($resident['is_premium']): ?>
                                <span class="badge badge-success">Premium</span>
                            <?php else: ?>
                                <span class="badge badge-info">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency($resident['current_balance'] ?? 0); ?></td>
                        <td>
                            <a href="?tab=health&resident_id=<?php echo $resident['resident_id']; ?>" 
                               class="btn btn-sm btn-primary">View Health</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
