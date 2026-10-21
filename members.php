<?php
    require_once 'config.php';
    require_once 'auth.php';

    redirect_if(!isset($_SESSION['admin_id']), 'index.php');
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
                                    <option value="">Select plan</option>
                                    <!-- Opcije puniš iz tabele training_plans -->
                                    <option value="1">12 sessions plan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="trainer_id" class="form-label">Trainer</label>
                                <select class="form-select" id="trainer_id" name="trainer_id">
                                    <!-- Prazna vrijednost = bez trenera (NULL u bazi) -->
                                    <option value="">No trainer</option>
                                    <!-- Opcije puniš iz tabele trainers -->
                                    <option value="1">Marko Marković</option>
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
                                    <!-- Ovaj red ponavljaš u petlji (LEFT JOIN na planove i trenere) -->
                                    <tr>
                                        <td>Nikola Savić</td>
                                        <td>nikola@email.com</td>
                                        <td>065403283</td>
                                        <td>12 sessions plan</td>
                                        <td><span class="text-muted">No trainer</span></td>
                                        <td>21.10.2026.</td>
                                        <td class="text-end">
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="delete_id" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
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