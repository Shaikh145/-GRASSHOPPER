<?php
include 'header.php';
include 'db_connect.php';

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Generate virtual number
        $virtual_number = '+1' . rand(2000000000, 9999999999);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, virtual_number) VALUES (?, ?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$username, $email, $hashed_password, $virtual_number]);
            
            $success = 'Account created successfully! You can now login.';
        } catch(PDOException $e) {
            if($e->getCode() == 23000) {
                $error = 'Email or username already exists';
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .signup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .signup-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
        }
        
        .btn-signup {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            width: 100%;
        }
        
        .btn-signup:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-container">
            <h2 class="signup-title">Create Account</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-signup">Sign Up</button>
            </form>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
