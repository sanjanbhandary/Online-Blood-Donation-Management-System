<?php
session_start();
require_once 'conn.php';
// Total Donors
$donor_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'Donor'");
$donor_data = $donor_query->fetch_assoc();
$total_donors = $donor_data['total'];

// Protect admin panel
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$message = "";
$notif_count_res = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0");
$notif_count_row = $notif_count_res->fetch_assoc();
$notif_count = $notif_count_row['total'];
// ==== CRUD Operations ==== //

// Delete User (Donor/Recipient)
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: adminpanel.php?page=users&msg=deleted");
    exit;
}

// Delete Request
if (isset($_GET['delete_request'])) {
    $id = (int)$_GET['delete_request'];
    $conn->query("DELETE FROM blood_requests WHERE id=$id");
    header("Location: adminpanel.php?page=requests&msg=deleted");
    exit;
}

// Update Request Status + Send Notification
if (isset($_POST['update_request'])) {
    $id = (int)$_POST['req_id'];
    $status = $conn->real_escape_string($_POST['status']);

    // Update request status
    $conn->query("UPDATE blood_requests SET status='$status' WHERE id=$id");

   // Get user_id + blood details
$res = $conn->query("SELECT user_id, blood_group, units_required FROM blood_requests WHERE id=$id");
$row = $res->fetch_assoc();

$user_id = $row['user_id'];
$blood = $row['blood_group'];
$units = $row['units_required'];

// Create better notification message
$message = "Your request for $units units of $blood blood has been $status";

    // Insert into notifications table
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$message')");

    header("Location: adminpanel.php?page=requests&msg=updated");
    exit;
}
if (isset($_POST['update_donation'])) {
    $id = (int)$_POST['donation_id'];
    $status = $conn->real_escape_string($_POST['status']);

    // Update status
$conn->query("UPDATE donations SET status='$status' WHERE id=$id");

// ✅ Send notification if completed
if ($status === 'Completed') {

    // Get user_id of that donation
    $res = $conn->query("SELECT user_id FROM donations WHERE id = $id");
    $row = $res->fetch_assoc();
    $user_id = $row['user_id'];

    // Notification message
    $message = "Your blood has been collected. Thank you for sharing your smile with others ";

    // Insert into notifications table
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ('$user_id', '$message')");
}

    if ($status == 'Approved') {
        $res = $conn->query("SELECT user_id, donation_date FROM donations WHERE id=$id");
        $row = $res->fetch_assoc();

        $user_id = $row['user_id'];
        $donation_date = $row['donation_date'];

        $conn->query("UPDATE users SET last_donation_date='$donation_date' WHERE id=$user_id");
    }

    header("Location: adminpanel.php?page=donations&msg=updated");
    exit;
}

// Update Blood Inventory
if (isset($_POST['update_inventory'])) {
    foreach ($_POST['qty'] as $id => $quantity) {
        $id = (int)$id;
        $qty = (int)$quantity;
        $conn->query("UPDATE blood_availability SET quantity=$qty WHERE id=$id");
    }
    header("Location: adminpanel.php?page=inventory&msg=updated");
    exit;
}

// Change Password
if (isset($_POST['change_password'])) {
    $old = $_POST['old_pass'];
    $new = $_POST['new_pass'];
    
    $admin_id = $_SESSION['admin_id'];
    $res = $conn->query("SELECT password FROM register WHERE id=$admin_id");
    $row = $res->fetch_assoc();
    
    if (password_verify($old, $row['password'])) {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $conn->query("UPDATE register SET password='$hash' WHERE id=$admin_id");
        $message = "<div class='alert alert-success'>Password changed successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Incorrect old password.</div>";
    }
}

// Notifications
if(isset($_GET['msg'])) {
    if($_GET['msg'] == 'deleted') $message = "<div class='alert alert-warning alert-dismissible'>Record deleted.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    if($_GET['msg'] == 'updated') $message = "<div class='alert alert-success alert-dismissible'>Record updated.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { min-height: 100vh; background-color: #212529; color: white; padding-top: 20px;}
        .admin-sidebar .nav-link { color: rgba(255,255,255,.75); margin-bottom: 5px; border-radius: 5px; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background-color: var(--primary-red); color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 admin-sidebar">
                <div class="text-center mb-4">
                    <h5 class="text-white"><i class="fa-solid fa-droplet text-primary-red"></i> Admin Panel</h5>
                </div>
                <ul class="nav flex-column px-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='dashboard'?'active':''; ?>" href="?page=dashboard"><i class="fa-solid fa-chart-line me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='users'?'active':''; ?>" href="?page=users"><i class="fa-solid fa-users me-2"></i> Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='requests'?'active':''; ?>" href="?page=requests"><i class="fa-solid fa-hand-holding-medical me-2"></i> Blood Requests</a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link <?php echo $page=='donations'?'active':''; ?>" href="?page=donations">
        <i class="fa-solid fa-droplet me-2"></i> Donations
    </a>
</li>


                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='inventory'?'active':''; ?>" href="?page=inventory"><i class="fa-solid fa-vials me-2"></i> Blood Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='feedback'?'active':''; ?>" href="?page=feedback"><i class="fa-solid fa-comments me-2"></i> Feedback</a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link <?php echo $page=='notifications'?'active':''; ?>" href="?page=notifications">
    <i class="fa-solid fa-bell me-2"></i> Notifications
    
    <?php if($notif_count > 0): ?>
        <span class="badge bg-danger ms-2"><?php echo $notif_count; ?></span>
    <?php endif; ?>
</a>
</li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo $page=='settings'?'active':''; ?>" href="?page=settings"><i class="fa-solid fa-gear me-2"></i> Settings</a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link text-warning" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4 bg-light">
                <?php echo $message; ?>
                
                <?php if ($page == 'dashboard'): 
                    // Quick Stats
                   $donors = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='donor'")->fetch_assoc()['cnt'];
$recipients = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='recipient'")->fetch_assoc()['cnt'];
                  $reqs = $conn->query("SELECT COUNT(*) as cnt FROM blood_requests")->fetch_assoc()['cnt'];
                  $stock = $conn->query("SELECT SUM(quantity) as cnt FROM blood_availability")->fetch_assoc()['cnt'];  
                ?>
                    <h2 class="mb-4">Dashboard Overview</h2>
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fa-solid fa-user-plus me-2"></i> Total Donors</h5>
                                    <h2 class="display-6"><?php echo $donors; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-secondary shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fa-solid fa-user-injured me-2"></i> Total Recipients</h5>
                                    <h2 class="display-6"><?php echo $recipients; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning text-dark shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fa-solid fa-clock me-2"></i> Pending Requests</h5>
                                    <h2 class="display-6 mt-2"><?php echo $reqs; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-danger shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fa-solid fa-droplet me-2"></i> Blood Stock (Units)</h5>
                                    <h2 class="display-6"><?php echo $stock; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($page == 'users'): 
                   $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
                ?>
                    <h2 class="mb-4">Manage Users</h2>
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Blood Grp</th><th>Contact</th><th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $res->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['full_name']; ?></td>
                                            <td><?php echo $row['email']; ?></td>
                                            <td><span class="badge bg-<?php echo $row['role']=='donor'?'success':'info'; ?>"><?php echo ucfirst($row['role']); ?></span></td>
                                            <td><strong class="text-danger"><?php echo $row['blood_group']; ?></strong></td>
                                            <td><?php echo $row['contact']; ?></td>
                                            <td>
                                                <a href="?delete_user=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?');"><i class="fa-solid fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

               <?php if ($page == 'requests'): 
    $res = $conn->query("SELECT * FROM blood_requests ORDER BY id DESC");
?>
<h2 class="mb-4">Manage Blood Requests</h2>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Blood</th>
                        <th>Units</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <!-- ID -->
                        <td><?php echo $row['id']; ?></td>

                        <!-- Patient Name + City -->
                        <td>
                            <?php echo $row['patient_name']; ?><br>
                            <small class="text-muted"><?php echo $row['city']; ?></small>
                        </td>

                        <!-- Blood Group -->
                        <td>
                            <strong class="text-danger"><?php echo $row['blood_group']; ?></strong>
                        </td>

                        <!-- Units -->
                        <td><?php echo $row['units_required']; ?></td>

                        <!-- Urgency -->
                        <td>
                           <?php 
    $urgency = strtolower(trim($row['urgency']));

    if ($urgency == 'priority' || $urgency == 'emergency') {
        echo "<span class='badge bg-danger'>Emergency</span>";
    } elseif ($urgency == 'urgent') {
        echo "<span class='badge bg-warning'>Urgent</span>";
    } else {
        echo "<span class='badge bg-secondary'>Normal</span>";
    }
?>
                        </td>

                        <!-- Status -->
                        <td>
                            <form action="adminpanel.php" method="POST" class="d-flex">
                                <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                    <option <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option <?php if($row['status']=='Approved') echo 'selected'; ?>>Approved</option>
                                    <option <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                    <option <?php if($row['status']=='Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <input type="hidden" name="update_request" value="1">
                            </form>
                        </td>

                        <!-- Delete -->
                        <td>
                            <a href="?delete_request=<?php echo $row['id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Are you sure?');">
                               <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?><?php if ($page == 'donations'): 
   $res = $conn->query("SELECT donations.*, users.username 
                     FROM donations 
                     JOIN users ON donations.user_id = users.id 
                     ORDER BY donations.id DESC");
?>
<h2 class="mb-4">Manage Donations</h2>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                     <td>
    <?php echo $row['user_id']; ?> - 
    <?php echo htmlspecialchars($row['username']); ?>
</td>
                        <td><?php echo $row['donation_date']; ?></td>
                        <td><?php echo $row['time_slot']; ?></td>

                        <td>
                            <form method="POST" action="adminpanel.php">
                                <input type="hidden" name="donation_id" value="<?php echo $row['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                    <option <?php if($row['status']=='Approved') echo 'selected'; ?>>Approved</option>
                                    <option <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                    <option <?php if($row['status']=='Rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <input type="hidden" name="update_donation" value="1">
                            </form>
                        </td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

                <?php if ($page == 'inventory'): 
                    $res = $conn->query("SELECT * FROM blood_availability ORDER BY blood_type ASC");
                ?>
                    <h2 class="mb-4">Blood Inventory Update</h2>
                    <div class="card shadow-sm border-0" style="max-width: 600px;">
                        <div class="card-body">
                            <form action="adminpanel.php" method="POST">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr><th>Blood Group</th><th>Quantity (Units)</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $res->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fs-5 fw-bold text-danger pt-3"><?php echo $row['blood_type']; ?></td>
                                                <td>
                                                    <input type="number" class="form-control" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $row['quantity']; ?>" min="0">
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" name="update_inventory" class="btn btn-primary mt-3 w-100">Update All Quantities</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($page == 'feedback'): 
                    $res = $conn->query("SELECT * FROM feedback ORDER BY id DESC");
                ?>
                    <h2 class="mb-4">User Feedback</h2>
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="bg-light">
                                        <tr><th>Name</th><th>Contact info</th><th>Message</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $res->fetch_assoc()): ?>
                                        <tr>
                                            <td style="width: 20%;"><?php echo $row['name']; ?></td>
                                            <td style="width: 25%;"><?php echo $row['email']; ?><br><?php echo $row['mobilenumber']; ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($page == 'notifications'): ?>
                    
                    <?php
$conn->query("UPDATE notifications SET is_read = 1");
?>
    <h2>Notifications</h2>

    <?php
$res = $conn->query("SELECT * FROM notifications WHERE user_id = 1 ORDER BY created_at DESC");
    while($row = $res->fetch_assoc()):
    ?>
      <div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fa-solid fa-bell text-danger me-2"></i>
                <?php echo $row['message']; ?>
            </div>
            <small class="text-muted">
                <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
            </small>
        </div>
    </div>
</div>
    <?php endwhile; ?>

<?php endif; ?>


                <?php if ($page == 'settings'): ?>
                    <h2 class="mb-4">Admin Settings</h2>
                    <div class="card shadow-sm border-0" style="max-width: 500px;">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Change Password</h5>
                            <form action="adminpanel.php?page=settings" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control" name="old_pass" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                <div class="input-group mb-3">
    <input type="password" class="form-control" id="new_pass" name="new_pass" required minlength="6">
    <span class="input-group-text" onclick="togglePassword('new_pass', this)" style="cursor:pointer;">
        <i class="fa-solid fa-eye"></i>
    </span>
</div>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-warning w-100">Update Password</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
