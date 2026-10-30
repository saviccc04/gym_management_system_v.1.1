<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

    $errors = [];

    $member_id = $_GET['id'] ?? '';

    $member_id = filter_var($member_id, FILTER_VALIDATE_INT);

    redirect_if($member_id === false, 'members.php');

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

    $first_name = $member['first_name'] ?? ''; 
    $last_name = $member['last_name'] ?? ''; 
    $email = $member['email'] ?? ''; 
    $phone_number = $member['phone_number'] ?? ''; 
    $trainer_id = $member['trainer_id'] ?? ''; 
    $plan_id = $member['plan_id'] ?? ''; 
    $old_photo_path = $member['photo_path'] ?? NULL; 

    $trainers_sql = "SELECT CONCAT(t.first_name,' ',  t.last_name) as trainer_name, t.trainer_id
    FROM trainers as t
    ORDER BY t.last_name, t.first_name";
    $result = $conn->query($trainers_sql);
    $trainers = $result->fetch_all(MYSQLI_ASSOC);

    $plans_sql = "SELECT * FROM training_plans
    ORDER BY training_plans.name";
    $result = $conn->query($plans_sql);
    $plans = $result->fetch_all(MYSQLI_ASSOC);

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
            $new_photo_path = NULL;
            
            if($photo !== NULL) {
                
                $extension = isset($allowed_types[$actual_mime_type]) ? $allowed_types[$actual_mime_type] : '' ;
                $photo_name = bin2hex(random_bytes(16)) . "." . $extension;
                $new_photo_path = 'uploads/' . $photo_name;    
        
                if(!move_uploaded_file($photo['tmp_name'], $new_photo_path)) {
                    $errors[] = 'Something went wrong.'; 
                }
            }     

            if(empty($errors)) {
                try {
                    $photo_path = $new_photo_path ?? $old_photo_path;
                    $sql = "UPDATE members
                    SET first_name = ?, last_name = ?, email = ?, phone_number = ?, plan_id = ?, trainer_id = ?, photo_path = ?
                    WHERE member_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('ssssiisi', $first_name, $last_name, $email, $phone_number, $plan_id, $trainer_id, $photo_path,  $member_id);
                    $stmt->execute();
        
                    $_SESSION['success_message'] = 'Member updated.';

                    if($old_photo_path !== NULL && file_exists($old_photo_path)) {
                        unlink($old_photo_path);
                    }
    
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

                    if ($new_photo_path !== NULL && file_exists($new_photo_path)) {
                        unlink($new_photo_path);
                    }
                }
            }
        }
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
        
        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                       value="<?php echo htmlspecialchars($first_name); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($last_name); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                       value="<?php echo htmlspecialchars($phone_number ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Training plan</label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <?php foreach($plans as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['plan_id']); ?>"
                                        <?php if ((int)$plan_id === (int)$plan['plan_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($plan['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <option value="">No trainer</option>
                                    <?php foreach($trainers as $trainer): ?>
                                        <option value="<?php echo htmlspecialchars($trainer['trainer_id']); ?>"
                                        <?php if (isset($trainer_id) && (int)$trainer_id === (int)$trainer['trainer_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($trainer['trainer_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo <span class="text-muted">(optional)</span></label>

                                <?php if (!empty($old_photo_path)): ?>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <img src="<?= htmlspecialchars($old_photo_path) ?>"
                                            width="64" height="64" class="rounded object-fit-cover"
                                            alt="<?= htmlspecialchars($first_name) ?>">
                                        <span class="text-muted small">Current photo</span>
                                    </div>
                                <?php endif; ?>

                                <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">
                                    JPG, PNG or WEBP, max 2 MB.
                                    <?php if (!empty($old_photo_path)): ?>Leave empty to keep the current photo.<?php endif; ?>
                                </div>
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