<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

    $trainers_sql = "SELECT CONCAT(t.first_name,' ',  t.last_name) as trainer_name, t.trainer_id
    FROM trainers as t
    ORDER BY t.last_name, t.first_name";
    $result = $conn->query($trainers_sql);
    $trainers = $result->fetch_all(MYSQLI_ASSOC);

    $plans_sql = "SELECT * FROM training_plans
    ORDER BY trianing_plans.name";
    $result = $conn->query($plans_sql);
    $plans = $result->fetch_all(MYSQLI_ASSOC);
    

    // if($_SERVER['REQUEST_METHOD'] == "POST") {

    // }

    $sql = "SELECT m.*,  tp.name as plan_name,  CONCAT(t.first_name,' ',  t.last_name) as trainer_name
    FROM members as m
    LEFT JOIN training_plans as tp
    ON m.plan_id = tp.plan_id
    LEFT JOIN trainers as t 
    on m.trainer_id = t.trainer_id
    ORDER BY m.last_name, m.first_name";
    $result = $conn->query($sql);

    $members = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h1 class="h3 mb-4">Members</h1>

        <!-- Flash poruke -->
        <!-- <div class="alert alert-success">Member added.</div> -->
        <!-- <div class="alert alert-danger">Email already exists.</div> -->

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add member</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number">
                            </div>
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Training plan</label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <option value="" disabled selected hidden>Select plan</option>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['plan_id']); ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <option value="">No trainer</option>
                                    <?php foreach($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer['trainer_id']); ?>"><?php echo htmlspecialchars($trainer['trainer_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add member</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">All members</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Plan</th>
                                        <th>Trainer</th>
                                        <th>Created at</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($members)): ?>
                                        <?php foreach($members as $member): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($member['first_name'] . " " . $member['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <?php if(!$member['phone_number']): ?>
                                                    <td><span class="text-muted">-</span></td>
                                                <?php else: ?>
                                                    <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo htmlspecialchars($member['plan_name']); ?></td>
                                                <?php if(!$member['trainer_name']): ?>
                                                    <td><span class="text-muted">-</span></td>
                                                <?php else: ?>
                                                    <td><?php echo htmlspecialchars($member['trainer_name']); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo htmlspecialchars(date("Y/m/d", strtotime($member['created_at']))); ?></td>
                                                <td class="text-end">
                                                    <form method="POST" action="delete_member.php" class="d-inline" onsubmit="return confirm('Delete this member?');">
                                                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No members yet</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>