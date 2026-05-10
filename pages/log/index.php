<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

cekLogin();

$page_title = 'Log Aktivitas';

// Admin melihat semua log, user lain hanya melihat log sendiri
if ($_SESSION['role'] == 'administrator') {
    $stmt = $pdo->query("
        SELECT la.*, u.nama, u.role
        FROM log_aktivitas la
        JOIN users u ON la.user_id = u.id
        ORDER BY la.tanggal DESC
        LIMIT 100
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT la.*, u.nama, u.role
        FROM log_aktivitas la
        JOIN users u ON la.user_id = u.id
        WHERE la.user_id = ?
        ORDER BY la.tanggal DESC
        LIMIT 100
    ");
    $stmt->execute([$_SESSION['user_id']]);
}

$logs = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-clock-history"></i> Log Aktivitas</h2>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <th>User</th>
                        <th>Role</th>
                        <?php endif; ?>
                        <th>Aksi</th>
                        <th>Detail</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <?php if ($_SESSION['role'] == 'administrator'): ?>
                        <td><?php echo htmlspecialchars($log['nama']); ?></td>
                        <td>
                            <?php
                            $badge_class = [
                                'administrator' => 'bg-danger',
                                'procurement' => 'bg-primary',
                                'gudang' => 'bg-success'
                            ];
                            ?>
                            <span class="badge <?php echo $badge_class[$log['role']]; ?>">
                                <?php echo ucfirst($log['role']); ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($log['aksi']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($log['detail']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['tanggal'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($logs) >= 100): ?>
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> Menampilkan 100 aktivitas terakhir
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
