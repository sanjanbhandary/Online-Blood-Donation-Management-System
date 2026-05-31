<?php 
session_start();
include 'conn.php';
include 'includes/header.php';

// Handle feedback form
if (isset($_POST['submit_feedback'])) {

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobilenumber = $_POST['mobilenumber'];
    $message = $_POST['message'];

   $sql = "INSERT INTO feedback (name, email, mobilenumber, message) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $mobilenumber, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Feedback submitted successfully');</script>";
    } else {
        echo "<script>alert('Error submitting feedback');</script>";
    }
}
?>

<!-- Hero Section -->
<section class="hero d-flex align-items-center justify-content-center text-center" 
style="background: linear-gradient(rgba(179,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero-bg.png') center/cover no-repeat; min-height: 80vh; color: white;">
    <div class="container">
        <h1 class="display-3 fw-bold mb-4">Donate Blood, Save Lives</h1>
        <p class="lead mb-5">Your single donation can save up to three lives.</p>

        <div class="d-flex gap-3 justify-content-center">
            <?php if (!isset($_SESSION['user_id'])) { ?>
    <a href="register.php?role=donor" class="btn btn-danger btn-lg">Become a Donor</a>
<?php } ?>
            <?php if(isset($_SESSION['user_id'])): ?>
    <a href="request.php" class="btn btn-light text-danger btn-lg">Request Blood</a>
<?php else: ?>
   <?php if(isset($_SESSION['user_id'])): ?>
    <a href="request.php" class="btn btn-light text-danger btn-lg">Request Blood</a>
<?php else: ?>
    <a href="register.php?role=recipient" class="btn btn-light text-danger btn-lg">Request Blood</a>
<?php endif; ?>
<?php endif; ?>
            <a href="available.php" class="btn btn-outline-light btn-lg">Availability</a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-4">
                <h2>15k+</h2>
                <p>Registered Donors</p>
            </div>
            <div class="col-md-4">
                <h2>24/7</h2>
                <p>Emergency Support</p>
            </div>
            <div class="col-md-4">
                <h2>8k+</h2>
                <p>Lives Saved</p>
            </div>
        </div>
    </div>
</section>

<!-- ✅ IMPROVED ABOUT SECTION -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row align-items-center">

            <!-- Image -->
            <div class="col-md-6 mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/3774/3774299.png" 
                     class="img-fluid" alt="Blood Donation">
            </div>

            <!-- Content -->
            <div class="col-md-6">
                <h2 class="text-danger fw-bold mb-3">About Us</h2>

                <p class="text-muted">
                    We are committed to saving lives by connecting blood donors with patients in need.
                    Our platform makes it easy to request blood and find verified donors quickly and safely.
                </p>

                <p class="text-muted">
                    Every drop of blood matters. We ensure a secure and reliable system so that both
                    donors and recipients can trust the process completely.
                </p>

                <ul class="list-unstyled mt-3">
                    <li>✔ Verified and trusted donors</li>
                    <li>✔ Fast and simple request process</li>
                    <li>✔ 24/7 emergency availability</li>
                    <li>✔ Safe and secure system</li>
                </ul>

                <?php if (!isset($_SESSION['user_id'])) { ?>
    <a href="register.php" class="btn btn-danger mt-3">Join as Donor</a>
<?php } else { ?>
    <a href="donate.php" class="btn btn-danger mt-3">Donate Blood</a>
<?php } ?>
            </div>

        </div>
    </div>
</section>

<!-- Donation Process -->
<section id="process" class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="text-danger fw-bold mb-4">Donation Process</h2>
        <p class="text-muted mb-5">Simple steps to donate blood</p>

        <div class="row text-center">

            <div class="col-md-3 col-6 mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/747/747376.png" width="70">
                <h6 class="fw-bold">Register</h6>
                <p class="small text-muted">Create your donor account</p>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/2921/2921222.png" width="70">
                <h6 class="fw-bold">Health Check</h6>
                <p class="small text-muted">Quick medical screening</p>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png" width="70">
                <h6 class="fw-bold">Donate</h6>
                <p class="small text-muted">Safe & quick process</p>
            </div>

            <div class="col-md-3 col-6 mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/1046/1046784.png" width="70">
                <h6 class="fw-bold">Save Lives</h6>
                <p class="small text-muted">Help patients in need</p>
            </div>

        </div>
    </div>
</section>

<!-- Feedback Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-danger">Give Your Feedback</h2>
            <p class="text-muted">We value your opinion</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="POST" class="card p-4 shadow">

                    <input type="text" name="name" class="form-control mb-3" placeholder="Your Name" required>

                    <input type="email" name="email" class="form-control mb-3" placeholder="Your Email" required>

                    <input type="text" name="mobilenumber" class="form-control mb-3" placeholder="Mobile Number" required>

                    <textarea name="message" class="form-control mb-3" rows="4"
                        placeholder="Write your feedback..." required></textarea>

                    <button type="submit" name="submit_feedback" 
                        class="btn btn-danger w-100">
                        Submit Feedback
                    </button>

                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>