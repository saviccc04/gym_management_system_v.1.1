<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

    $errors = [];

    $member_id = $_GET['id'] ?? '';

    $trainers_sql = "SELECT CONCAT(t.first_name,' ',  t.last_name) as trainer_name, t.trainer_id
    FROM trainers as t
    ORDER BY t.last_name, t.first_name";
    $result = $conn->query($trainers_sql);
    $trainers = $result->fetch_all(MYSQLI_ASSOC);

    var_dump($trainers);

    $plans_sql = "SELECT * FROM training_plans
    ORDER BY training_plans.name";
    $result = $conn->query($plans_sql);
    $plans = $result->fetch_all(MYSQLI_ASSOC);

    if($member_id === '') {
        header('location: members.php');
        exit();
    } else {
        $member_id = filter_var($member_id, FILTER_VALIDATE_INT);

        if(!$member_id) {
            header('location: members.php');
            exit();
        }
    }

    $sql = "SELECT * FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();

    if($result->num_rows === 0) {
        $_SESSION['error_message'] = 'Member not found.';
        header('location: members.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Edit member</h1>
            <a href="members.php" class="btn btn-outline-secondary btn-sm">Back to members</a>
        </div>

        <!-- Prikaz grešaka ($errors), isto kao u members.php -->
        <!-- <div class="alert alert-danger"><ul class="mb-0"><li>...</li></ul></div> -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- action="" šalje na isti URL, zajedno sa ?id=... -->
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                       value="<?php echo htmlspecialchars($member['first_name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($member['last_name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($member['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                       value="<?php echo htmlspecialchars($member['phone_number']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Training plan</label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['plan_id']); ?>"
                                        <?php if ((int)$member['plan_id'] === (int)$plan['plan_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($plan['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <?php foreach($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer['trainer_id']); ?>"
                                        <?php if (isset($member['trainer_id']) && (int)$member['trainer_id'] === (int)$trainer['trainer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($trainer['trainer_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                                <a href="members.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>