<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');
    
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $training_name = trim($_POST['name']);
       
        if($training_name !== '') {
            $sql = "INSERT INTO training_plans (name) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $training_name);
            $stmt->execute();

            $_SESSION['success_message'] = "Plan added.";

            header('location: plans.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Plan name is required.";

            header('location: plans.php');
            exit();
        }
    }

    $sql = "SELECT * FROM training_plans";
    $result = $conn->query($sql);

    $training_plans = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h1 class="h3 mb-4">Training Plans</h1>

        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add plan</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add plan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-3">All plans</h2>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Created at</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($training_plans)): ?>

                                        <?php foreach($training_plans as $plan): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($plan['name']) ; ?></td>
                                                <td><?php echo htmlspecialchars(date("Y/m/d", strtotime($plan['created_at']))); ?></td>
                                                <td class="text-end">
                                                    <form method="POST" action="delete_plan.php" class="d-inline">
                                                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($plan['plan_id']) ; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No plans yet</td>
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