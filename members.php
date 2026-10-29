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
    ORDER BY training_plans.name";
    $result = $conn->query($plans_sql);
    $plans = $result->fetch_all(MYSQLI_ASSOC);
    
    $errors = [];

    if(isset($_SESSION['error_message'])) {
        $errors[] = $_SESSION['error_message'];
    }

    unset($_SESSION['error_message']);

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $trainer_id = (int)($_POST['trainer_id'] ?? '');
        $plan_id = trim( $_POST['plan_id'] ?? '');
        $photo = $_FILES['photo'] ?? NULL;

        if($first_name === '') {
            $errors[] = 'First name is required.';
        }

        if($last_name === '') {
            $errors[] = 'Last name is required.';
        }

        if($email === '') {
            $errors[] = 'Email is required.';
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email'; 
        }

        if($phone_number === '') {
            $phone_number = NULL;
        }

        if(!$trainer_id) {
            $trainer_id = NULL;
        }

        if ($plan_id === '') {                         
            $errors[] = 'Training plan is required.';
        } else {
            $plan_id = filter_var($plan_id, FILTER_VALIDATE_INT); 
            if ($plan_id === false) {
                $errors[] = 'Invalid training plan.';
            }
        }

        if($photo === NULL || $photo['error'] === UPLOAD_ERR_NO_FILE) {
            $photo = NULL;
        }

        if($photo !== NULL) {
            if($photo['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Photo upload failed.';
            } else if($photo['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Photo must be 2 MB or smaller.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $actual_mime_type = $finfo->file($photo['tmp_name']); 

                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];
    
    
                if(!isset($allowed_types[$actual_mime_type])) {
                    $errors[] = 'Photo must be JPG, PNG or WEBP.';
                }
            }
        }



        if(empty($errors)) {
            $photo_path = NULL;

            if($photo !== NULL) {
                $extension = isset($allowed_types[$actual_mime_type]) ? $allowed_types[$actual_mime_type] : '' ;
                $photo_name = bin2hex(random_bytes(16)) . "." . $extension;
                $photo_path = 'uploads/' . $photo_name;
    
                if(!move_uploaded_file($photo['tmp_name'], $photo_path)) {
                    $errors[] = 'Something went wrong.'; 
                }
            }

            if(empty($errors)) {
                try {
                    $sql = "INSERT INTO members (first_name, last_name, email, phone_number, plan_id, trainer_id, photo_path)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssssiis', $first_name, $last_name, $email, $phone_number, $plan_id, $trainer_id, $photo_path);
                    $stmt->execute();
        
                    $_SESSION['success_message'] = 'Member added.';
    
                    header('location: members.php');
                    exit();
                } catch(mysqli_sql_exception $e) {
                    if ($e->getCode() === 1062) {
                        $errors[] = 'A member with this email already exists.';
                    } elseif($e->getCode() === 1452) {
                        $errors[] = "Selected plan or trainer doesn't exist.";
                    } else {
                        $errors[] = 'Something went wrong. Please try again.';
                    }
    
                    if ($photo_path !== NULL && file_exists($photo_path)) {
                        unlink($photo_path);
                    }
                }
            }

        }
    }

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

        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add member</h2>
                        <form method="POST" enctype="multipart/form-data" action="">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                value="<?php echo htmlspecialchars($phone_number ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Training plan</label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <option value="" disabled <?php if(!isset($plan_id) || !is_int($plan_id)) echo 'selected' ?> hidden>Select plan</option>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['plan_id']); ?>" <?php if (isset($plan_id) && (int)$plan['plan_id'] === $plan_id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($plan['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <option value="">No trainer</option>
                                    <?php foreach($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer['trainer_id']); ?>"
                                             <?php if (isset($trainer_id) && (int)$trainer['trainer_id'] === $trainer_id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($trainer['trainer_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo <span class="text-muted">(optional)</span></label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">JPG, PNG or WEBP, max 2 MB.</div>
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
                                        <th>Photo</th>
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
                                                <td>
                                                    <?php if(isset($member['photo_path'])): ?>
                                                        <img src="<?php echo htmlspecialchars($member['photo_path']); ?>"
                                                        width="40px" height="40px" class="rounded object-fit-cover" alt="<?= htmlspecialchars($member['first_name']) ?>">
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="edit_member.php?id=<?= htmlspecialchars($member['member_id']) ?>"
                                                    class="btn btn-sm btn-outline-primary me-1">Edit</a>

                                                    <form method="POST" action="delete_member.php" class="d-inline" onsubmit="return confirm('Delete this member?');">
                                                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No members yet</td>
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