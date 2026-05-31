<?php
session_start();
include 'includes/db.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ ADDED username
    $username = $conn->real_escape_string($_POST['username']);

    $fullName = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $bloodGroup = $conn->real_escape_string($_POST['blood_group']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $city = $conn->real_escape_string($_POST['city']);
    $role = $conn->real_escape_string($_POST['role']);

    if(strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {

        // (kept your original email check)
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // ✅ FIXED: username added
            $insert_sql = "INSERT INTO users (username, full_name, email, password, gender, blood_group, contact, city, role) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssssssss", $username, $fullName, $email, $hashed_password, $gender, $bloodGroup, $contact, $city, $role);

            if($stmt->execute()) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body { background: #f5f7fa; }
.card { border-radius: 15px; }
.form-control, .form-select { height: 45px; border-radius: 8px; }
label { margin-bottom: 6px; }
.left-panel {
    background: linear-gradient(rgba(179,0,0,0.8), rgba(179,0,0,0.8)),
                url('https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=600&q=80');
    background-size: cover;
    background-position: center;
}
</style>
</head>

<body>

<div class="container py-5">
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card shadow-lg overflow-hidden">
<div class="row g-0">

<div class="col-md-5 d-none d-md-flex left-panel text-white align-items-center justify-content-center text-center p-4">
    <div>
        <i class="fa-solid fa-droplet fa-4x mb-3"></i>
        <h3>Join the Force</h3>
        <p>Every drop counts. Register today.</p>
    </div>
</div>

<div class="col-md-7 p-4 bg-white">

<h4 class="text-center mb-3">Create Account</h4>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php else: ?>

<form method="POST">

<div class="row g-3">

<div class="col-12">
<label>Full Name</label>
<input type="text" name="full_name" class="form-control" required>
</div>

<div class="col-md-6">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="col-md-6">
<label>Contact</label>
<input type="text" name="contact" class="form-control" required>
</div>

<div class="col-md-6">
<label>Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="col-md-6 position-relative">
<label>Password</label>

<input type="password" id="password" name="password" class="form-control pe-5" required>

<i class="fa-solid fa-eye position-absolute" id="togglePassword"
   style="top: 50%; right: 15px; transform: translateY(50%); cursor: pointer;">
</i>
</div>

<div class="col-md-4">
<label>Gender</label>
<select name="gender" class="form-select" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>
</div>

<div class="col-md-4">
<label>Blood Group</label>
<select name="blood_group" class="form-select" required>
<option value="">Select</option>
<option>A+</option>
<option>B+</option>
<option>O+</option>
<option>AB+</option>
<option>A-</option>
<option>B-</option>
<option>O-</option>
<option>AB-</option>
</select>
</div>

<div class="col-md-4">
<label>City</label>
<input type="text" name="city" class="form-control" required>
</div>

<div class="col-12">
<label>Role</label><br>

<?php
$selected_role = $_GET['role'] ?? '';
?>

<div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="role" value="Donor"
        <?php if($selected_role == 'donor') echo 'checked'; ?>>
    <label class="form-check-label">Donor</label>
</div>

<div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" name="role" value="Recipient"
        <?php if($selected_role == 'recipient') echo 'checked'; ?>>
    <label class="form-check-label">Recipient</label>
</div>

</div>

<div class="col-12">
<button class="btn btn-danger w-100">Register</button>
</div>

</div>
</form>

<?php endif; ?>

<p class="text-center mt-3">
Already have account? <a href="login.php">Login</a>
</p>

</div>
</div>
</div>

</div>
</div>
</div>

<script>
const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");

togglePassword.addEventListener("click", function () {
    const type = password.type === "password" ? "text" : "password";
    password.type = type;

    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});
</script>

</body>
</html>