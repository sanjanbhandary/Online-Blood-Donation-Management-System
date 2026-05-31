<?php
session_start();

require_once 'conn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admins WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
           $_SESSION['admin_user'] = $admin['username'];
            header("Location: adminpanel.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Admin not found.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Blood Bank</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (IMPORTANT for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-dark d-flex flex-column min-vh-100">

<div class="container flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="glass-panel" style="width: 100%; max-width: 400px;">

        <div class="text-center mb-4">
            <i class="fa-solid fa-user-shield fa-4x text-primary-red mb-3"></i>
            <h3 class="fw-bold">Admin Portal</h3>
            <p class="text-muted">Secure Access Only</p>
        </div>

     <?php if($message != ""): ?>
    <div class="alert alert-danger text-center fw-bold shadow-sm">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

        <form action="admin.php" method="POST">

            <!-- Username -->
            <div class="mb-4">
                <label class="form-label fw-bold">Admin Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-user text-muted"></i>
                    </span>
                    <input type="text" class="form-control" name="username" required>
                </div>
            </div>

            <!-- Password (FIXED) -->
            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <div class="input-group">

                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-lock text-muted"></i>
                    </span>

                    <input type="password" id="admin_pass" class="form-control" name="password" required>

                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </span>

                </div>
            </div>

            <button type="submit" class="btn btn-danger w-100 py-3 fw-bold shadow">
    <i class="fa-solid fa-right-to-bracket me-2"></i> Login
</button>

            <div class="text-center mt-3">
                <a href="index.php" class="text-secondary text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>

        </form>
    </div>
</div>

<!-- JS FIXED -->
<script>
function togglePassword() {
    var pass = document.getElementById("admin_pass");
    var icon = document.getElementById("eyeIcon");

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>

