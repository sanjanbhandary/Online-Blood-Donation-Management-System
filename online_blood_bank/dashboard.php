<?php
require_once 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// User
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Notifications
$notif_sql = "SELECT * FROM notifications 
              WHERE user_id = ? 
              AND message NOT LIKE '%New blood donation appointment%' 
              ORDER BY created_at DESC";
$n_stmt = $conn->prepare($notif_sql);
$n_stmt->bind_param("i", $user_id);
$n_stmt->execute();
$notifications = $n_stmt->get_result();

// Next eligible
$last_donation = $user['last_donation_date'];
$next_eligible = "Eligible Now";
$can_donate = true;

if (!empty($last_donation)) {
    $last_date = new DateTime($last_donation);
    $next_date = clone $last_date;
    $next_date->modify('+90 days');

    if (new DateTime() < $next_date) {
        $can_donate = false;
        $next_eligible = $next_date->format('d M, Y');
    }
}

// History
if ($user['role'] === 'Donor') {
    $history_sql = "SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
} else {
    $history_sql = "SELECT * FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
}

$h_stmt = $conn->prepare($history_sql);
$h_stmt->bind_param("i", $user_id);
$h_stmt->execute();
$history = $h_stmt->get_result();

// Donations
$d_sql = "SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$d_stmt = $conn->prepare($d_sql);
$d_stmt->bind_param("i", $user_id);
$d_stmt->execute();
$donations = $d_stmt->get_result();

include 'includes/header.php';
?>

<script>
function toggleNotifications() {
    let box = document.getElementById("moreNotifications");
    box.style.display = (box.style.display === "none") ? "block" : "none";
}
</script>

<div class="container py-5">

<div class="row mb-4">

    <!-- LEFT -->
    <div class="col-lg-8">
        <h2 class="fw-bold">
            Welcome back,
            <span class="text-danger"><?php echo htmlspecialchars($user['full_name']); ?></span>
        </h2>

        <p class="text-muted mb-0">
            <i class="fa-solid fa-location-dot text-danger"></i>
            <?php echo htmlspecialchars($user['city']); ?> |
            <i class="fa-solid fa-droplet text-danger"></i>
            Blood Group:
            <strong class="border px-2 py-1 bg-light rounded">
                <?php echo htmlspecialchars($user['blood_group']); ?>
            </strong>
        </p>
    </div>

    <!-- RIGHT -->
    <div class="col-lg-4 text-end">

        <?php if ($user['role'] === 'Donor'): ?>
            <div class="bg-danger text-white px-4 py-3 rounded-4 shadow-sm">
                <small>Next Eligible Donation</small><br>
                <strong class="fs-5"><?php echo $next_eligible; ?></strong>
            </div>
        <?php else: ?>

            <a href="request.php" class="btn btn-danger btn-lg shadow-sm mb-3">
                <i class="fa-solid fa-notes-medical"></i> New Blood Request
            </a>

            <div class="bg-light px-3 py-2 rounded shadow-sm">
                <small>Next Eligible Donation</small><br>
                <strong><?php echo $next_eligible; ?></strong>
            </div>

        <?php endif; ?>

        <!-- NOTIFICATIONS -->
        <div class="card border-0 shadow-sm rounded-4 mt-3 text-start">
            <div class="card-header bg-white border-0" style="cursor:pointer;" onclick="toggleNotifications()">
                <h6 class="fw-bold mb-0">
                    <i class="fa-solid fa-bell text-warning me-2"></i>
                    Notifications
                </h6>
            </div>

            <div class="card-body">

                <?php if($notifications->num_rows > 0): ?>

                    <?php 
                        $notifications->data_seek(0);
                        $first = $notifications->fetch_assoc();
                    ?>

                    <!-- Latest -->
                    <div>
                        <p class="mb-1"><?php echo htmlspecialchars($first['message']); ?></p>
                        <small class="text-muted">
                            <?php echo date('M d, h:i A', strtotime($first['created_at'])); ?>
                        </small>
                    </div>

                    <!-- Hidden -->
                    <div id="moreNotifications" style="display:none;">
                        <hr>
                        <?php while($n = $notifications->fetch_assoc()): ?>
                            <div class="mt-2">
                                <p class="mb-1"><?php echo htmlspecialchars($n['message']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('M d, h:i A', strtotime($n['created_at'])); ?>
                                </small>
                            </div>
                            <hr>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <p class="text-muted mb-0">No notifications</p>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- MAIN -->
<div class="row g-4">

<div class="col-lg-8">

<!-- HISTORY -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0">
        <h5 class="fw-bold">
            <i class="fa-solid fa-clock-rotate-left text-danger me-2"></i>
            Your Recent <?php echo $user['role'] === 'Donor' ? 'Donations' : 'Requests'; ?>
        </h5>
    </div>

    <div class="card-body">
        <?php if ($history->num_rows > 0): ?>
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <?php if ($user['role'] === 'Donor'): ?>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Status</th>
                        <?php else: ?>
                            <th>Patient</th>
                            <th>Units</th>
                            <th>Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php while($row = $history->fetch_assoc()): ?>
                    <tr>
                        <?php if ($user['role'] === 'Donor'): ?>
                            <td><?php echo date('d M, Y', strtotime($row['donation_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['time_slot']); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['units_required']); ?></td>
                        <?php endif; ?>

                        <td>
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center">No history found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- DONATIONS -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white">
        <h5 class="fw-bold">
            <i class="fa-solid fa-droplet text-danger me-2"></i>
            Your Recent Donations
        </h5>
    </div>

    <div class="card-body">
        <?php if ($donations->num_rows > 0): ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($d = $donations->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M, Y', strtotime($d['donation_date'])); ?></td>
                        <td><?php echo htmlspecialchars($d['time_slot']); ?></td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars($d['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center">No donations yet.</p>
        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>

<?php
$update_n = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$un_stmt = $conn->prepare($update_n);
$un_stmt->bind_param("i", $user_id);
$un_stmt->execute();

include 'includes/footer.php';
?>