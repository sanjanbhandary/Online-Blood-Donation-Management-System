<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

// Handle adding new inventory
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_inventory'])) {
    $blood_group = $conn->real_escape_string($_POST['blood_group']);
    $quantity = intval($_POST['quantity']);
    $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
    
    $stmt = $conn->prepare("INSERT INTO blood_inventory (blood_group, quantity, expiry_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $blood_group, $quantity, $expiry_date);
    $stmt->execute();
    
    header("Location: inventory.php?success=1");
    exit();
}

// Remove/Expire blood logic
$conn->query("UPDATE blood_inventory SET quantity = 0 WHERE expiry_date < CURDATE() AND quantity > 0");

// Fetch Grouped Inventory Data
$inventory_q = $conn->query("
    SELECT blood_group, SUM(quantity) as total_units 
    FROM blood_inventory 
    WHERE quantity > 0 AND expiry_date >= CURDATE()
    GROUP BY blood_group
    ORDER BY blood_group ASC
");

// Fetch Detailed Logs (Active & Expired)
$logs_q = $conn->query("
    SELECT * FROM blood_inventory 
    ORDER BY expiry_date ASC 
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Inventory - Admin</title>
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
            <a href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
            <a href="inventory.php" class="active"><i class="fa-solid fa-boxes-stacked me-2"></i> Blood Inventory</a>
            <a href="?logout=true" class="text-danger mt-5"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 offset-md-2 p-4 bg-light min-vh-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Inventory Management</h2>
                <button class="btn btn-danger fw-bold shadow" data-bs-toggle="modal" data-bs-target="#addInventoryModal"><i class="fa-solid fa-plus me-2"></i> Add Blood Units</button>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success shadow-sm alert-dismissible fade show">
                    <i class="fa-solid fa-circle-check me-2"></i> Inventory updated successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Grouped Stats -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-4 pb-2">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-pie text-danger me-2"></i> Available Units By Group</h5>
                            <p class="text-muted small mt-1">Shows only active, unexpired units.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php 
                                $bg_dict = ['A+'=>0,'A-'=>0,'B+'=>0,'B-'=>0,'AB+'=>0,'AB-'=>0,'O+'=>0,'O-'=>0];
                                while($row = $inventory_q->fetch_assoc()) {
                                    $bg_dict[$row['blood_group']] = $row['total_units'];
                                }
                                foreach($bg_dict as $group => $units): 
                                ?>
                                <div class="col-6">
                                    <div class="d-flex align-items-center p-3 border rounded-3 <?php echo $units < 5 ? 'bg-danger-subtle border-danger' : 'bg-white'; ?>">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 40px; height: 40px;">
                                            <?php echo $group; ?>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold"><?php echo $units; ?></h4>
                                            <small class="text-muted">Units</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Logs Table -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-2">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list text-primary me-2"></i> Recent Inventory Batches</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Group</th>
                                            <th>Qty Added</th>
                                            <th>Added On</th>
                                            <th>Expiry Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($log = $logs_q->fetch_assoc()): ?>
                                            <?php 
                                                $expired = false;
                                                if($log['quantity'] == 0 || strtotime($log['expiry_date']) < time()) {
                                                    $expired = true;
                                                }
                                            ?>
                                            <tr class="<?php echo $expired ? 'table-secondary opacity-50' : ''; ?>">
                                                <td><span class="badge bg-danger"><?php echo htmlspecialchars($log['blood_group']); ?></span></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($log['quantity']); ?></td>
                                                <td><?php echo date('d M, Y', strtotime($log['added_at'])); ?></td>
                                                <td><?php echo date('d M, Y', strtotime($log['expiry_date'])); ?></td>
                                                <td>
                                                    <?php if($expired): ?>
                                                        <span class="badge bg-secondary">Expired/Empty</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-danger me-2"></i> Add Blood Units</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form action="inventory.php" method="POST">
            <input type="hidden" name="add_inventory" value="1">
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small uppercase">Blood Group</label>
                <select name="blood_group" class="form-select" required>
                    <option value="">Select...</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small uppercase">Quantity (Units)</label>
                <input type="number" name="quantity" class="form-control" required min="1">
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small uppercase">Expiry Date</label>
                <input type="date" name="expiry_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+35 days')); ?>">
                <div class="form-text">Standard expiry relies on local policy, commonly 35-42 days.</div>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold py-2">Add to Inventory</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
