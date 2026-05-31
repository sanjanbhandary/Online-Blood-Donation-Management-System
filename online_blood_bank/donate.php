<?php
session_start();
require_once 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

// ================== FORM SUBMIT ==================
if (isset($_POST['submit'])) {

    $user_id = $_SESSION['user_id'];

    if (empty($user_id)) {
        die("User ID missing. Session not working.");
    }

    $age = intval($_POST['age']);
    $weight = intval($_POST['weight']);
    $health = $_POST['health'];
    $date = $_POST['date'];
    $time = $_POST['time_slot'];

    // ================= VALIDATIONS =================

    if ($age < 18) {
        $error = "You must be at least 18 years old.";
    }

    elseif ($weight < 50) {
        $error = "Weight must be above 50kg.";
    }

    elseif ($health !== "Yes") {
        $error = "You must be healthy to donate.";
    }

    else {

        // 🔥 Check last donation (90 days rule)
        $check_sql = "SELECT last_donation_date FROM users WHERE id = ?";
        $c_stmt = $conn->prepare($check_sql);
        $c_stmt->bind_param("i", $user_id);
        $c_stmt->execute();
        $res = $c_stmt->get_result()->fetch_assoc();

        if (!empty($res['last_donation_date'])) {
            $last = new DateTime($res['last_donation_date']);
            $next = clone $last;
            $next->modify('+90 days');

            if (new DateTime() < $next) {
                $error = "You cannot donate before 90 days.";
            }
        }
    }

    // ================= INSERT ONLY IF NO ERROR =================
    if (empty($error)) {

        $sql = "INSERT INTO donations (user_id, donation_date, time_slot, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Donation Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("iss", $user_id, $date, $time);

    

            if ($stmt->execute()) {
                // Get user name
$user_sql = "SELECT full_name FROM users WHERE id = $user_id";
$user_res = $conn->query($user_sql);
$user = $user_res->fetch_assoc();

$userName = $user['full_name'];

// Notification message
$message = "Your donation appointment has been booked successfully.";

// Insert notification for that user
$conn->query("INSERT INTO notifications (user_id, message) VALUES ('$user_id', '$message')");

    // 🔥 Get user name & blood group
   $user_sql = "SELECT username, blood_group FROM users WHERE id = $user_id";
    $user_res = $conn->query($user_sql);
    $user = $user_res->fetch_assoc();

    $userName = $user['username'];
    $bloodGroup = $user['blood_group'];

    // 🔥 Create admin notification message
    $message = "New blood donation appointment booked by $userName (Blood Group: $bloodGroup) on $date at $time.";

    // 🔥 Insert notification
    

            $notif_sql = "INSERT INTO notifications (user_id, message) 
                        VALUES ('$user_id', '$message')";

            $conn->query($notif_sql);

            $success = "Donation booked successfully";
            

        } else {
            die("Donation Error: " . $stmt->error);
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow rounded-4 border-0">
                
                <div class="card-header bg-danger text-white text-center rounded-top-4">
                    <h4 class="mb-0">
                        <i class="fa-solid fa-hand-holding-droplet"></i> Book Blood Donation
                    </h4>
                </div>

                <div class="card-body p-4">

                    <div class="alert alert-info small">
                        <strong>Eligibility Checklist:</strong>
                        <ul class="mb-0">
                            <li>You must be at least 18 years old</li>
                            <li>Weight should be above 50kg</li>
                            <li>Must be healthy</li>
                            <li>No donation in last 90 days</li>
                        </ul>
                    </div>

                    <!-- ✅ ERROR MESSAGE INSIDE BOX -->
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
    <div class="alert alert-success">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

                    <form method="POST">

                        <h6 class="fw-bold mt-3">1. Health Screening</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Age</label>
                                <input type="number" name="age" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Weight (kg)</label>
                                <input type="number" name="weight" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Are you healthy?</label>
                            <select name="health" class="form-control" required>
                                <option value="">Select</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <h6 class="fw-bold mt-4">2. Appointment Slot</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Time Slot</label>
                                <select name="time_slot" class="form-control" required>
                                    <option value="">Select</option>
                                    <option>09:00 AM - 11:00 AM</option>
                                    <option>11:00 AM - 01:00 PM</option>
                                    <option>02:00 PM - 04:00 PM</option>
                                    <option>04:00 PM - 06:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Select Hospital</label>
                           <select name="hospital_select" class="form-control" onchange="toggleHospitalInput(this)" required>
    <option value="">-- Select Hospital --</option>
    <option>AJ Hospital, Mangalore</option>
    <option>Father Muller Hospital, Kankanady</option>
    <option>KMC Hospital, Attavar</option>
    <option>Wenlock Hospital, Mangalore</option>
    <option>Unity Hospital, Mangalore</option>
    <option>Indiana Hospital, Pumpwell</option>
    <option>Highland Hospital, Mangalore</option>
    <option>Tejasvini Hospital, Kadri</option>
    <option>Mangala Hospital, Mangalore</option>
    <option>Mulki Government Hospital, Mulki</option>
    <option value="other">Other (Enter Manually)</option>
</select>

<input type="text" name="hospital" id="otherHospital" 
       class="form-control mt-2" placeholder="Enter Hospital Name" 
       style="display:none;">
                        </div>

                        <button type="submit" name="submit" class="btn btn-danger w-100">
                            Confirm Blood Donation Appointment
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script>
function toggleHospitalInput(select) {
    var input = document.getElementById("otherHospital");

    if (select.value === "other") {
        input.style.display = "block";
        input.required = true;
    } else {
        input.style.display = "none";
        input.required = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>