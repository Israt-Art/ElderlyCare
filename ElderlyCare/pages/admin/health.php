<!-- Health Updates Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-heartbeat"></i> Today's Health Updates
    </h1>
    <p class="dashboard-subtitle">Total Health Updates Today: <?php echo count($today_health); ?></p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Health Records - <?php echo formatDate(date('Y-m-d')); ?></h2>
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
                        <?php foreach ($today_health as $health): ?>
                        <tr>
                            <td><?php echo date('h:i A', strtotime($health['recorded_time'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($health['room_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($health['name_en']); ?></td>
                            <td>
                                <?php if ($health['sugar_level']): ?>
                                    <?php echo $health['sugar_level']; ?> mg/dL
                                    <?php
                                    // Normal range: 70-100 mg/dL (fasting), 140-180 (after meals)
                                    $sugar = $health['sugar_level'];
                                    if ($sugar < 70 || $sugar > 200) {
                                        echo ' <span class="badge badge-danger">!</span>';
                                    }
                                    ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($health['blood_pressure']): ?>
                                    <?php echo htmlspecialchars($health['blood_pressure']); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-light">No health updates recorded for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-3">
    <strong>Note:</strong> Admin cannot edit user-entered health values. All values are recorded as entered by residents.
</div>
