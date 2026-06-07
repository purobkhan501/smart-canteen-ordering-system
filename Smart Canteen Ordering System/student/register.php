<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - UIU Smart Canteen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/student_login.css">
</head>
<body>

    <div class="login-wrapper d-flex flex-column">
        <div class="container mt-4">
            <a class="home-button btn btn-light" href="../index.php" role="button"> <i class="bi bi-arrow-left-circle fs-3"></i></a>
        </div>

        <div class="container d-flex flex-grow-1 justify-content-center align-items-center">
            <div class="login-card p-5 shadow-sm text-center bg-white">
                
                <div class="icon-box-purple mx-auto mb-4">
                    <i class="bi bi-person-plus text-white"></i>
                </div>

                <h3 class="fw-bold mb-1">Student Sign Up</h3>
                <p class="text-muted small mb-4">Create your account to order from UIU Canteen</p>

                <form action="../includes/register_process.php" method="POST">
                    <input type="hidden" name="role" value="student">
                    
                    <div class="text-start mb-3">
                        <label for="name" class="form-label small fw-semibold text-muted">Full Name</label>
                        <div class="input-group border rounded-3 p-1">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-fonts text-muted"></i>
                            </span>
                            <input type="text" name="name" id="name" class="form-control border-0 shadow-none" placeholder="Enter full name" required>
                        </div>
                    </div>

                    <div class="text-start mb-3">
                        <label for="email" class="form-label small fw-semibold text-muted">Email</label>
                        <div class="input-group border rounded-3 p-1">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email" id="email" class="form-control border-0 shadow-none" placeholder="Enter email address" required>
                        </div>
                    </div>

                    <div class="text-start mb-3">
                        <label for="username" class="form-label small fw-semibold text-muted">Username</label>
                        <div class="input-group border rounded-3 p-1">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" name="username" id="username" class="form-control border-0 shadow-none" placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="text-start mb-3">
                        <label for="password" class="form-label small fw-semibold text-muted">Password</label>
                        <div class="input-group border rounded-3 p-1">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control border-0 shadow-none" placeholder="Create a password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-custom w-100 py-2">Register Now</button>
                </form>

                <p class="small text-muted mb-0 mt-3">Already have an account? <a href="login.php" class="text-success text-decoration-none fw-bold">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

