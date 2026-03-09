<!-- Emergencies Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-exclamation-triangle"></i> Emergency Alerts
    </h1>
    <p class="dashboard-subtitle">All emergency incidents and alerts</p>
</div>

<?php
// Get all emergencies (not just pending)
$conn = getDBConnection();
$all_emergencies_sql = "SELECT e.*, r.name_en, r.room_number, r.phone, r.emergency_contact, r.emergency_contact_name
                        FROM emergency_logs e
                        JOIN residents r ON e.resident_id = r.resident_id
                        ORDER BY e.emergency_time DESC
                        LIMIT 50";
$all_emergencies = $conn->query($all_emergencies_sql)->fetch_all(MYSQLI_ASSOC);
closeDBConnection($conn);
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Emergency Logs</h2>
        <span class="badge badge-danger"><?php echo count(array_filter($all_emergencies, function($e) { return $e['status'] === 'pending'; })); ?> Pending</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Emergency Contact</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Response Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_emergencies as $emergency): ?>
                    <tr class="<?php echo $emergency['status'] === 'pending' ? 'table-row-pending' : ''; ?>">
                        <td><strong><?php echo formatDateTime($emergency['emergency_time']); ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($emergency['room_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($emergency['name_en']); ?></td>
                        <td>
                            <?php if ($emergency['emergency_contact']): ?>
                                <?php echo htmlspecialchars($emergency['emergency_contact_name'] ?? 'N/A'); ?><br>
                                <small><?php echo htmlspecialchars($emergency['emergency_contact']); ?></small>
                            <?php else: ?>
                                <small class="text-light">Not available</small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-danger"><?php echo ucfirst($emergency['emergency_type']); ?></span></td>
                        <td><?php echo htmlspecialchars($emergency['description'] ?? 'Emergency alert'); ?></td>
                        <td>
                            <?php
                            $status_class = 'badge-info';
                            if ($emergency['status'] === 'pending') $status_class = 'badge-danger';
                            if ($emergency['status'] === 'resolved') $status_class = 'badge-success';
                            if ($emergency['status'] === 'in-progress') $status_class = 'badge-warning';
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('-', ' ', $emergency['status'])); ?></span>
                        </td>
                        <td>
                            <?php if ($emergency['response_time']): ?>
                                <?php echo formatDateTime($emergency['response_time']); ?>
                                <?php
                                $alert_time = strtotime($emergency['emergency_time']);
                                $response_time = strtotime($emergency['response_time']);
                                $duration = round(($response_time - $alert_time) / 60);
                                echo '<br><small>' . $duration . ' min</small>';
                                ?>
                            <?php else: ?>
                                <span class="text-light">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emergency['status'] === 'pending'): ?>
                                <a href="php/admin/respond_emergency.php?id=<?php echo $emergency['emergency_id']; ?>" 
                                   class="btn btn-sm btn-success">Respond</a>
                            <?php else: ?>
                                <span class="text-light">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
