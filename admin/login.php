<?php
session_start();
include '../includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Clean input
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, password FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // FIX: allow multiple rows (in case duplicates exist)
    if ($result->num_rows > 0) {

        $login_success = false;

        // Loop through all matching admins
        while ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                header("Location: dashboard.php");
                exit();
            }
        }

        // If no password matched
        $error = "Invalid credentials";

    } else {
        $error = "Admin not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Blood Bank System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #212529; color: white; display:flex; align-items:center; justify-content:center; height: 100vh; }
        .card { background-color: #343a40; border-color: #495057; border-top: 5px solid #dc3545;}
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-shield-halved fa-4x text-danger mb-3"></i>
                <h2>Admin Control Panel</h2>
                <p class="text-muted">Authorized Personnel Only</p>
            </div>
            
            <div class="card shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Authenticate</button>
                    </form>

                    <div class="mt-4 text-center border-top border-secondary pt-3">
                        <a href="../index.php" class="text-decoration-none text-muted">
                            <i class="fa-solid fa-arrow-left"></i> Return to public site
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>