<!-- Finance Page -->
<div class="dashboard-header">
    <h1 class="dashboard-title">
        <i class="fas fa-money-bill-wave"></i> Financial Management
    </h1>
    <p class="dashboard-subtitle">Resident accounts and transactions</p>
</div>

<?php
// Calculate financial summary
$conn = getDBConnection();
$finance_summary_sql = "SELECT 
    SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END) as total_withdrawals,
    SUM(CASE WHEN transaction_type = 'service_charge' THEN amount ELSE 0 END) as total_service_charges,
    SUM(CASE WHEN transaction_type = 'premium_payment' THEN amount ELSE 0 END) as total_premium_payments,
    SUM(CASE WHEN transaction_type = 'deposit' AND package_type = 'Normal' THEN amount ELSE 0 END) as normal_deposits,
    SUM(CASE WHEN transaction_type = 'deposit' AND package_type = 'Premium' THEN amount ELSE 0 END) as premium_deposits,
    COUNT(*) as total_transactions
    FROM payments
    WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$summary = $conn->query($finance_summary_sql)->fetch_assoc();

// Get all account balances
$balances_sql = "SELECT r.resident_id, r.name_en, r.room_number, a.current_balance, 
                 (SELECT COUNT(*) FROM payments p WHERE p.resident_id = r.resident_id AND p.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as monthly_transactions
                 FROM residents r
                 LEFT JOIN account_balance a ON r.resident_id = a.resident_id
                 WHERE r.is_active = TRUE
                 ORDER BY a.current_balance DESC";
$account_balances = $conn->query($balances_sql)->fetch_all(MYSQLI_ASSOC);
closeDBConnection($conn);
?>

<!-- Financial Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo formatCurrency($summary['total_deposits'] ?? 0); ?></div>
        <div class="stat-label">Total Deposits (30 days)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo formatCurrency($summary['total_withdrawals'] ?? 0); ?></div>
        <div class="stat-label">Total Withdrawals (30 days)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo formatCurrency($summary['total_service_charges'] ?? 0); ?></div>
        <div class="stat-label">Service Charges (30 days)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo formatCurrency($summary['total_premium_payments'] ?? 0); ?></div>
        <div class="stat-label">Premium Payments (30 days)</div>
    </div>
</div>

<!-- Package-Based Payment Breakdown -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Package-Based Deposits (30 days)</h2>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <strong>Normal Package Deposits:</strong><br>
                <span style="font-size: 1.5em; color: #17a2b8;"><?php echo formatCurrency($summary['normal_deposits'] ?? 0); ?></span>
            </div>
            <div>
                <strong>Premium Package Deposits:</strong><br>
                <span style="font-size: 1.5em; color: #28a745;"><?php echo formatCurrency($summary['premium_deposits'] ?? 0); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Account Balances -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Resident Account Balances</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Current Balance</th>
                        <th>Transactions (30 days)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($account_balances as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($account['name_en']); ?></td>
                        <td>
                            <strong><?php echo formatCurrency($account['current_balance'] ?? 0); ?></strong>
                            <?php if (($account['current_balance'] ?? 0) < 0): ?>
                                <span class="badge badge-danger">Negative</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $account['monthly_transactions'] ?? 0; ?> transactions</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="alert('Transaction details feature coming soon')">View Transactions</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Transactions</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Room</th>
                        <th>Resident</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Package</th>
                        <th>Balance After</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payments as $payment): ?>
                    <tr>
                        <td><?php echo formatDateTime($payment['transaction_time']); ?></td>
                        <td><?php echo htmlspecialchars($payment['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($payment['name_en']); ?></td>
                        <td>
                            <?php
                            $type_class = 'badge-info';
                            if ($payment['transaction_type'] === 'deposit') $type_class = 'badge-success';
                            if ($payment['transaction_type'] === 'withdrawal' || $payment['transaction_type'] === 'service_charge') $type_class = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $type_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $payment['transaction_type'])); ?>
                            </span>
                        </td>
                        <td>
                            <strong>
                                <?php
                                if ($payment['transaction_type'] === 'deposit') {
                                    echo '+';
                                } else {
                                    echo '-';
                                }
                                echo formatCurrency($payment['amount']);
                                ?>
                            </strong>
                        </td>
                        <td><span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span></td>
                        <td>
                            <?php if ($payment['package_type']): ?>
                                <span class="badge badge-<?php echo $payment['package_type'] === 'Premium' ? 'success' : 'info'; ?>">
                                    <?php echo htmlspecialchars($payment['package_type']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-light">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency($payment['balance_after'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($payment['description'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
