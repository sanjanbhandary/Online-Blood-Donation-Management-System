<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Handle Accept/Reject Actions
if(isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $type = $_GET['type'];
    
    if($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        if($type === 'donation') {
            $conn->query("UPDATE donations SET status = '$status' WHERE id = $id");
            
            // If approved, notify donor
            if($status === 'Approved') {
                $d = $conn->query("SELECT user_id, donation_date FROM donations WHERE id = $id")->fetch_assoc();
                $msg = "Your blood donation appointment on {$d['donation_date']} has been approved.";
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ({$d['user_id']}, '$msg')");
            }
            
        } elseif($type === 'request') {
            $conn->query("UPDATE blood_requests SET status = '$status' WHERE id = $id");
            
            if($status === 'Approved') {
                $r = $conn->query("SELECT user_id, blood_group, units_required FROM blood_requests WHERE id = $id")->fetch_assoc();
                $msg = "Your emergency request for {$r['units_required']} units of {$r['blood_group']} blood has been approved.";
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ({$r['user_id']}, '$msg')");
            }
        }
        header("Location: dashboard.php");
        exit();
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch stats
$total_donors = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Donor'")->fetch_assoc()['count'];
$total_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests")->fetch_assoc()['count'];
$available_blood = $conn->query("SELECT SUM(quantity) as calc FROM blood_inventory")->fetch_assoc()['calc'];

// Fetch Pending Donations
$pending_donations = $conn->query("
    SELECT d.id, u.full_name, u.blood_group, d.donation_date, d.time_slot 
    FROM donations d 
    JOIN users u ON d.user_id = u.id 
    WHERE d.status = 'Pending'
");

// Fetch Pending Requests
$pending_requests = $conn->query("
    SELECT r.id, r.patient_name, r.blood_group, r.units_required, r.urgency, r.city 
    FROM blood_requests r 
    WHERE r.status = 'Pending' 
    ORDER BY r.urgency ASC, r.request_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background-color: #212529; min-height: 100vh; color: white; padding-top: 2rem;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 20px; display: block; border-left: 3px solid transparent;}
        .sidebar a:hover, .sidebar a.active { background-color: #343a40; color: white; border-left-color: #dc3545;}
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar position-fixed">
            <h4 class="text-center text-danger fw-bold mb-4"><i class="fa-solid fa-shield-halved"></i> Admin</h4>
            <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="inventory.php"><i class="fa-solid fa-boxes-stacked me-2"></i> Blood Inventory</a>
            <a href="?logout=true" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 offset-md-2 p-4 bg-light min-vh-100">
            <h2 class="fw-bold mb-4">Dashboard Overview</h2>
            
            <!-- Key Metrics -->
            <div class="row mb-5 g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 border-bottom border-danger border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase">Total Donors</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $total_donors; ?></h2>
                                </div>
                                <div class="bg-danger text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                    <i class="fa-solid fa-users fa-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 border-bottom border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase">Total Requests</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $total_requests; ?></h2>
                                </div>
                                <div class="bg-warning text-dark rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                    <i class="fa-solid fa-notes-medical fa-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 border-bottom border-success border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted fw-bold text-uppercase">Blood Units (Total)</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $available_blood ? $available_blood : 0; ?></h2>
                                </div>
                                <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                    <i class="fa-solid fa-droplet fa-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Pending Blood Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white pt-3 pb-2 border-0">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-bed-pulse text-danger me-2"></i> Pending Patient Requests</h5>
                        </div>
                        <div class="card-body">
                            <?php if($pending_requests->num_rows > 0): ?>
                                <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                                <?php while($req = $pending_requests->fetch_assoc()): ?>
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($req['patient_name']); ?> <span class="badge bg-danger rounded-pill"><?php echo $req['blood_group']; ?></span></h6>
                                                <small class="text-muted"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($req['city']); ?> &middot; <?php echo $req['units_required']; ?> units</small>
                                                <?php if($req['urgency'] == 'Emergency') echo '<span class="badge bg-danger ms-2">Urgent</span>'; ?>
                                            </div>
                                            <div class="btn-group btn-group-sm shadow-sm">
                                                <a href="?action=approve&type=request&id=<?php echo $req['id']; ?>" class="btn btn-outline-success" title="Approve"><i class="fa-solid fa-check"></i></a>
                                                <a href="?action=reject&type=request&id=<?php echo $req['id']; ?>" class="btn btn-outline-danger" title="Reject"><i class="fa-solid fa-xmark"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No pending requests at this time.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Donations -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white pt-3 pb-2 border-0">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-calendar-check text-success me-2"></i> Pending Donor Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if($pending_donations->num_rows > 0): ?>
                                <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                                <?php while($don = $pending_donations->fetch_assoc()): ?>
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($don['full_name']); ?> <span class="badge bg-danger rounded-pill"><?php echo $don['blood_group']; ?></span></h6>
                                                <small class="text-muted"><i class="fa-solid fa-calendar"></i> <?php echo date('d M, Y', strtotime($don['donation_date'])); ?> &middot; <?php echo htmlspecialchars($don['time_slot']); ?></small>
                                            </div>
                                            <div class="btn-group btn-group-sm shadow-sm">
                                                <a href="?action=approve&type=donation&id=<?php echo $don['id']; ?>" class="btn btn-outline-success" title="Approve"><i class="fa-solid fa-check"></i></a>
                                                <a href="?action=reject&type=donation&id=<?php echo $don['id']; ?>" class="btn btn-outline-danger" title="Reject"><i class="fa-solid fa-xmark"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No pending appointments.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
