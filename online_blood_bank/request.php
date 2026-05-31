<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conn.php';
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';
$matched_donors = [];

// Blood compatibility logic
function getCompatibleBloodGroups($bloodGroup) {
    switch ($bloodGroup) {
        case 'A+': return ['A+', 'A-', 'O+', 'O-'];
        case 'A-': return ['A-', 'O-'];
        case 'B+': return ['B+', 'B-', 'O+', 'O-'];
        case 'B-': return ['B-', 'O-'];
        case 'AB+': return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        case 'AB-': return ['AB-', 'A-', 'B-', 'O-'];
        case 'O+': return ['O+', 'O-'];
        case 'O-': return ['O-'];
        default: return [];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $patient_name = $_POST['patient_name'];
    $blood_group = $_POST['blood_group'];
    $units_required = intval($_POST['units_required']);
    $hospital_name = $_POST['hospital_name'];
    $city = $_POST['city'];
    $urgency = $_POST['urgency'];

    // Insert request
    $r_sql = "INSERT INTO blood_requests 
              (user_id, patient_name, blood_group, units_required, hospital_name, city, urgency, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $r_stmt = $conn->prepare($r_sql);
    $r_stmt->bind_param("ississs", $user_id, $patient_name, $blood_group, $units_required, $hospital_name, $city, $urgency);

    if ($r_stmt->execute()) {
        $user_sql = "SELECT full_name FROM users WHERE id = $user_id";
    $user_res = $conn->query($user_sql);
    $user = $user_res->fetch_assoc();

    $userName = $user['full_name'];

        $success = "Your blood request has been successfully submitted.";
        $message = "New blood request from $userName for $units_required units of $blood_group blood.";

$conn->query("INSERT INTO notifications (user_id, message)
VALUES ('$user_id', '$message')");

        // Smart Matching
        $compatible = getCompatibleBloodGroups($blood_group);

        if (!empty($compatible)) {

            $placeholders = implode(',', array_fill(0, count($compatible), '?'));

            $match_sql = "SELECT full_name, contact, blood_group, city, last_donation_date 
                          FROM users 
                          WHERE role = 'Donor' 
                          AND city = ? 
                          AND blood_group IN ($placeholders)";

            $m_stmt = $conn->prepare($match_sql);

            $types = str_repeat("s", count($compatible) + 1);
            $params = array_merge([$city], $compatible);

            $bind_names = [];
            $bind_names[] = $types;

            for ($i = 0; $i < count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }

            call_user_func_array([$m_stmt, 'bind_param'], $bind_names);

            $m_stmt->execute();
            $res = $m_stmt->get_result();

            while ($donor = $res->fetch_assoc()) {

                $can_donate = true;

                if ($donor['last_donation_date']) {
                    $last = new DateTime($donor['last_donation_date']);
                    $next = clone $last;
                    $next->modify('+90 days');

                    if (new DateTime() < $next) {
                        $can_donate = false;
                    }
                }

                if ($can_donate) {
                    $matched_donors[] = $donor;
                }
            }
        }

    } else {
        $error = "Failed to submit blood request.";
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-start">

        <!-- FORM -->
        <div class="col-lg-7 mb-4">
            <div class="card p-4 shadow">
                <h3>Request Blood</h3>

                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="text" name="patient_name" placeholder="Patient Name" class="form-control mb-2" required>
                    
                    <select name="blood_group" class="form-control mb-2" required>
                        <option value="">Select Blood Group</option>
                        <option>A+</option><option>A-</option>
                        <option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option>
                        <option>O+</option><option>O-</option>
                    </select>

                    <input type="number" name="units_required" class="form-control mb-2" placeholder="Units" required>

                    <input type="text" name="hospital_name" class="form-control mb-2" placeholder="Hospital">

                    <input type="text" name="city" class="form-control mb-2" placeholder="City">

                    <!-- ✅ FIXED URGENCY -->
                    <select name="urgency" class="form-control mb-3">
                        <option value="Normal">Normal (1–3 days)</option>
                        <option value="Urgent">Urgent (within 1 day)</option>
                        <option value="Emergency">Priority (within 2 hours)</option>
                    </select>

                    <button class="btn btn-danger w-100">Submit</button>
                </form>
            </div>
        </div>

        <!-- MATCHING -->
        <div class="col-lg-5">
            <div class="card p-4 shadow">
                <h4>Matched Donors</h4>

                <?php if(count($matched_donors) > 0): ?>
                    <?php foreach($matched_donors as $d): ?>
                        <div class="border p-2 mb-2">
                            <strong><?php echo $d['full_name']; ?></strong><br>
                            <?php echo $d['blood_group']; ?> - <?php echo $d['city']; ?><br>
                            <a href="tel:<?php echo $d['contact']; ?>">Call</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No donors found.</p>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>