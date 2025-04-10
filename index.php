<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .features {
            padding: 80px 0;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1>Welcome to Grasshopper Clone</h1>
            <p class="lead">Your Virtual Phone System Solution</p>
            <a href="signup.php" class="btn btn-light btn-lg mt-3">Get Started</a>
        </div>
    </div>

    <div class="features">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-phone feature-icon"></i>
                        <h3>Virtual Phone Numbers</h3>
                        <p>Get a professional business phone number instantly</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-forward feature-icon"></i>
                        <h3>Call Forwarding</h3>
                        <p>Never miss a call with smart call forwarding</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-voicemail feature-icon"></i>
                        <h3>Voicemail</h3>
                        <p>Professional voicemail with transcription</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 Grasshopper Clone. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
