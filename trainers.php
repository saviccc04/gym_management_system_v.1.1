<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

    $errors = [];

    if(isset($_SESSION['error_message'])) {
        $errors[] = $_SESSION['error_message'];
    }

    unset($_SESSION['error_message']);

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']);

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

        if(empty($errors)) {
            try {
                $sql = "INSERT INTO trainers (first_name, last_name, email, phone_number)
                VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $first_name, $last_name, $email, $phone_number);
                $stmt->execute();
    
                $_SESSION['success_message'] = 'Trainer added.';

                header('location: trainers.php');
                exit();
            } catch(mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $errors[] = 'A trainer with this email already exists.';
                } else {
                    $errors[] = 'Something went wrong. Please try again.';
                }
            }
        }
    }

    $sql = "SELECT * FROM trainers ORDER BY last_name, first_name";
    $result = $conn->query($sql);

    $trainers = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h1 class="h3 mb-4">Trainers</h1>

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
                        <h2 class="h5 mb-3">Add trainer</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                value="<?php echo htmlspecialchars($phone_number ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add trainer</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">All trainers</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>First name</th>
                                        <th>Last name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Created at</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($trainers)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No trainers yet</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($trainers as $trainer): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($trainer['first_name']); ?></td>
                                                <td><?php echo htmlspecialchars($trainer['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                                                <td>
                                                    <?php if(empty($trainer['phone_number'])): ?>
                                                        <span class="text-muted">—</span>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($trainer['phone_number']); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(date("Y/m/d", strtotime($trainer['created_at']))); ?></td>
                                                <td class="text-end">
                                                    <form method="POST" action="delete_trainer.php" class="d-inline"  onsubmit="return confirm('Delete this trainer?');">
                                                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($trainer['trainer_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
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