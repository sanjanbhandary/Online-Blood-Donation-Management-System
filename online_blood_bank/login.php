<?php
session_start();
include 'includes/db.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="p-4 p-md-5 bg-white">

                    <div class="text-center mb-4">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fa-solid fa-lock fa-xl"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Welcome Back</h3>
                        <p class="text-muted">Sign in to your account to continue.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger shadow-sm rounded-3">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">

                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-user text-muted"></i>
                                </span>
                                <input type="text" name="username" class="form-control border-start-0" required placeholder="Enter your username">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted small uppercase">Password</label>
                            
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-key text-muted"></i>
                                </span>

                                <input type="password" id="login_pass" name="password" class="form-control border-start-0" required placeholder="Enter your password">

                                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Button -->
                        <button type="submit" class="btn btn-danger w-100 py-2 mb-3 fw-bold text-uppercase rounded-3">
                            Login to Dashboard
                        </button>

                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted mb-0">
                            Don't have an account?
                            <a href="register.php" class="text-danger fw-bold text-decoration-none">Register here</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Password Toggle Script -->
<script>
function togglePassword() {
    var pass = document.getElementById("login_pass");
    var icon = document.getElementById("eyeIcon");

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

<?php include 'includes/footer.php'; ?>