<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $AdminID = $_POST['AdminID'];
    $AdminPass = $_POST['AdminPass'];

    if ($AdminID === "2TSK2GP5" && $AdminPass === "software25/26") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: ./AdminDashboard.php");
        exit();
    } else {
        $error = "Invalid Admin ID or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHISHSHIELD | Admin Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0; padding: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif; background-color: #000;
            overflow-x: hidden; position: relative;
        }

        .bg-animation { position: fixed; inset: 0; z-index: 0; }
        .bg-animation::before {
            content: ""; position: absolute; inset: -145%; rotate: -45deg; background: #000000;
            background-image: 
                radial-gradient(4px 100px at 0px 235px, rgb(255, 140, 17), #0000),
                radial-gradient(4px 100px at 300px 235px, rgb(255, 119, 0), #884e2800),
                radial-gradient(1.5px 1.5px at 150px 117.5px, rgb(255, 144, 9) 100%, #0000 150%),
                radial-gradient(4px 100px at 0px 252px, rgb(156, 14, 137), #0000),
                radial-gradient(4px 100px at 300px 252px, rgb(23, 41, 206), #0000),
                radial-gradient(1.5px 1.5px at 150px 126px, rgb(247, 102, 18) 100%, #0000 150%);
            background-size: 300px 235px, 300px 252px, 300px 150px, 300px 299px;
            animation: hi 150s linear infinite;
        }

        @keyframes hi {
            0% { background-position: 0px 220px, 3px 220px, 151.5px 337.5px; }
            to { background-position: 0px 6800px, 3px 6800px, 151.5px 6917.5px; }
        }

        .main-container {
            display: flex; flex-direction: row; align-items: center;
            justify-content: space-between; width: 90%;
            max-width: 1100px; z-index: 2; padding: 20px;
        }

        .brand-section { flex: 1; text-align: left; padding-right: 50px; }

        .system-title-outer {
            font-weight: 900; letter-spacing: 10px; color: #ffffff; font-size: 5rem;
            margin: 0; text-transform: uppercase;
            text-shadow: 
                0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 
                0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,0.5), 
                0 0 10px rgba(255, 140, 17, 0.8), 
                0 10px 20px rgba(0,0,0,0.5);
            animation: floatTitle 4s ease-in-out infinite;
        }

        .brand-sub { 
            font-size: 1.3rem; color: #ff9800; font-weight: 700; 
            letter-spacing: 4px; margin-top: 10px; 
            text-shadow: 0 0 10px rgba(255, 152, 0, 0.3);
        }

        .card {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px);
            padding: 40px; border-radius: 30px; border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
            width: 100%; max-width: 400px; text-align: center; color: #fff;
        }

        h2 { margin-bottom: 30px; font-size: 1.8rem; display: flex; align-items: center; justify-content: center; gap: 15px; }

        .input-icon { position: relative; margin-bottom: 20px; }
        .input-icon i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #ff9100; }

        input {
            width: 100%; padding: 14px 14px 14px 45px; border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3); background: rgba(0, 0, 0, 0.3);
            color: #fff; font-size: 1rem; box-sizing: border-box; outline: none;
            transition: 0.3s;
        }

        .btn-primary {
            width: 100%; padding: 16px; border-radius: 15px; border: none;
            background: linear-gradient(135deg, #ff9100, #ff3d00);
            color: white; font-weight: bold; font-size: 1.1rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }

        @keyframes floatTitle { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        /* --- MOBILE RESPONSIVE FIX --- */
        @media (max-width: 900px) {
            body { overflow-y: auto; align-items: flex-start; }
            .main-container { 
                flex-direction: column; 
                text-align: center; 
                padding: 60px 20px; 
                gap: 40px;
            }
            .brand-section { 
                padding-right: 0; 
                margin-bottom: 0; 
            }
            .system-title-outer { 
                font-size: 2.8rem; /* Kecilkan teks tajuk pada phone */
                letter-spacing: 5px; 
            }
            .brand-sub {
                font-size: 1rem;
                letter-spacing: 2px;
            }
            .card {
                max-width: 100%; /* Kotak login ambil lebar penuh skrin */
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="bg-animation"></div>

    <div class="main-container">
        <div class="brand-section">
            <h1 class="system-title-outer">PHISHSHIELD</h1>
            <p class="brand-sub">SECURE ACCESS PORTAL</p>
        </div>

        <div class="card">
            <h2><i class="fa-solid fa-user-shield"></i> ADMIN LOGIN</h2>

            <?php if(isset($error)): ?>
                <p style="color: #ff8a80; background: rgba(255,0,0,0.2); padding: 10px; border-radius: 10px; font-size: 0.8rem; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </p>
            <?php endif; ?>

            <form method="post">
                <div class="input-icon">
                    <i class="fa-solid fa-id-badge"></i>
                    <input type="text" name="AdminID" placeholder="ADMIN ID" required>
                </div>

                <div class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="AdminPass" placeholder="PASSWORD" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-right-to-bracket"></i> VERIFY & LOGIN
                </button>
            </form>
        </div>
    </div>

</body>
</html>