<?php
require_once 'conn.php';

// Check if user is logged in
//if (!isset($_SESSION['user_id'])) {
  //  header("Location: login.php");
   // exit;
//}

$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $search_query = "WHERE blood_type LIKE '%$search%'";
}

$query = "SELECT * FROM blood_availability $search_query ORDER BY blood_type ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Availability - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fa-solid fa-droplet"></i> BloodBank</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navDashboard">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navDashboard">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="available.php">Dashboard Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="request.php">Request Blood</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3 border-end pe-3">
    Welcome, 
    <strong><?php echo $_SESSION['fullname'] ?? "Guest"; ?></strong> 
    (<?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : "Guest"; ?>)
</span>
                    <a href="logout.php" class="btn btn-red btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container flex-grow-1">
        <div class="row mb-4 align-items-end">
            <div class="col-md-6">
                <h2 class="section-title mb-0">Blood Availability Status</h2>
                <p class="text-muted mt-2">Check real-time stock levels of different blood groups in our bank.</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <form action="available.php" method="GET" class="d-flex justify-content-md-end">
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control" name="search" placeholder="Search blood group (e.g., O+)" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn btn-dark" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="available.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-primary-red text-white">
                            <tr>
                                <th class="py-3 px-4">Blood Group</th>
                                <th class="py-3 px-4">Available Units (Bottles)</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $status = "In Stock";
                                    $badge = "badge bg-success";
                                    if ($row['quantity'] == 0) {
                                        $status = "Out of Stock";
                                        $badge = "badge bg-danger";
                                    } elseif ($row['quantity'] < 10) {
                                        $status = "Low Stock";
                                        $badge = "badge bg-warning text-dark";
                                    }
                            ?>
                            <tr>
                                <td class="py-3 px-4 fw-bold fs-5 text-primary-red"><?php echo $row['blood_type']; ?></td>
                                <td class="py-3 px-4 fs-5"><?php echo $row['quantity']; ?></td>
                                <td class="py-3 px-4"><span class="<?php echo $badge; ?> px-2 py-1 rounded-pill"><?php echo $status; ?></span></td>
                                <td class="py-3 px-4 text-end">
                                    <a href="request.php?bg=<?php echo urlencode($row['blood_type']); ?>" class="btn btn-sm btn-outline-secondary <?php echo ($row['quantity'] == 0) ? 'disabled' : ''; ?>">
                                        Request
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No records found matching your search.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Light -->
    <footer class="bg-white py-3 border-top mt-auto">
        <div class="container text-center text-muted">
            <small>&copy; 2026 Online Blood Bank Service System. Dashboard view.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
