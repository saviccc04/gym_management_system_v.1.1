<?php
    require_once 'config.php';
    require_once 'auth.php';
    require_once 'helpers.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');

    $members_count = count_table($conn, 'members');
    $trainers_count = count_table($conn, 'trainers');
    $training_plans_count = count_table($conn, 'training_plans');
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h1 class="h3 mb-4">Dashboard</h1>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Members</p>
                        <p class="h2 mb-3"><?php echo $members_count; ?></p>
                        <a href="members.php" class="btn btn-sm btn-primary">View members</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Trainers</p>
                        <p class="h2 mb-3"><?php echo $trainers_count; ?></p>
                        <a href="trainers.php" class="btn btn-sm btn-primary">View trainers</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-1">Training plans</p>
                        <p class="h2 mb-3"><?php echo $training_plans_count; ?></p>
                        <a href="plans.php" class="btn btn-sm btn-primary">View plans</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>