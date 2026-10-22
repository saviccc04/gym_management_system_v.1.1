<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

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

        <!-- Flash poruke -->
        <!-- <div class="alert alert-success">Trainer added.</div> -->
        <!-- <div class="alert alert-danger">Email already exists.</div> -->

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add trainer</h2>
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
                                                    <form method="POST" action="delete_trainer.php" class="d-inline">
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