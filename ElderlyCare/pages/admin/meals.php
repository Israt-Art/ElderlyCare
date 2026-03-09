<!-- Meal Selections Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-utensils"></i> Today's Meal Selections
    </h1>
    <p class="dashboard-subtitle">Total Meal Updates Today: <?php echo count($today_meals); ?></p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Meal Intake Selections - <?php echo formatDate(date('Y-m-d')); ?></h2>
    </div>
    <div class="card-body">
        <?php
        // Group meals by resident
        $meals_by_resident = [];
        foreach ($today_meals as $meal) {
            $resident_id = $meal['resident_id'];
            if (!isset($meals_by_resident[$resident_id])) {
                $meals_by_resident[$resident_id] = [
                    'name' => $meal['name_en'],
                    'room' => $meal['room_number'],
                    'sugar_intake' => $meal['sugar_intake'] ?? null,
                    'salt_intake' => $meal['salt_intake'] ?? null,
                    'spicy_intake' => $meal['spicy_intake'] ?? null,
                    'selected_at' => $meal['selected_at'] ?? null
                ];
            }
        }
        ?>

        <?php if (count($meals_by_resident) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Resident</th>
                            <th>Sugar Intake</th>
                            <th>Salt Intake</th>
                            <th>Spicy Intake</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meals_by_resident as $resident_id => $data): ?>
                        <tr>
                            <td><?php echo $data['selected_at'] ? formatDateTime($data['selected_at']) : '-'; ?></td>
                            <td><strong><?php echo htmlspecialchars($data['room']); ?></strong></td>
                            <td><?php echo htmlspecialchars($data['name']); ?></td>
                            <td>
                                <?php if ($data['sugar_intake']): ?>
                                    <span class="badge badge-<?php echo $data['sugar_intake'] === 'High' ? 'danger' : ($data['sugar_intake'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['sugar_intake']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['salt_intake']): ?>
                                    <span class="badge badge-<?php echo $data['salt_intake'] === 'High' ? 'danger' : ($data['salt_intake'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['salt_intake']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['spicy_intake']): ?>
                                    <span class="badge badge-<?php echo $data['spicy_intake'] === 'High' ? 'danger' : ($data['spicy_intake'] === 'Normal' ? 'info' : 'success'); ?>">
                                        <?php echo htmlspecialchars($data['spicy_intake']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-light">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-light">No meal selections recorded for today.</p>
        <?php endif; ?>
    </div>
</div>
