<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gop_type = $_POST['gop_value'];
    // Table names must be lowercase
    $stmt = $conn->prepare("INSERT INTO gop (GOP) VALUES (?)");
    $stmt->bind_param("s", $gop_type);
    
    if ($stmt->execute()) {
        $_SESSION['gop_id'] = $conn->insert_id;
        $_SESSION['gop_type'] = $gop_type;
        header("Location: ./InsertMethod.php?step=choose");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhishShield | Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; background-color: #051622; position: relative; box-sizing: border-box; overflow: hidden; }
        
        .stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
        .star { position: absolute; color: #5dade2; opacity: 0.4; filter: drop-shadow(0 0 5px rgba(93, 173, 226, 0.5)); animation: twinkle var(--duration) infinite ease-in-out; }
        
        @keyframes twinkle { 0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.2; } 50% { transform: scale(1.1) rotate(10deg); opacity: 0.5; } }
        
        .outer-header { text-align: center; margin-bottom: 30px; width: 100%; z-index: 2; }
        
        /* KESAN 3D TEBAL SEPERTI FAIL ASAL */
        .system-title-outer { 
            font-weight: 900; 
            letter-spacing: 8px; 
            color: #ffffff; 
            font-size: 4rem; 
            margin: 0; 
            text-transform: uppercase; 
            display: inline-block; 
            /* Bayangan berlapis untuk efek 3D */
            text-shadow: 
                0 1px 0 #ccc, 
                0 2px 0 #c9c9c9, 
                0 3px 0 #bbb, 
                0 4px 0 #b9b9b9, 
                0 5px 0 #aaa, 
                0 6px 1px rgba(0,0,0,.1), 
                0 0 5px rgba(0,0,0,.1), 
                0 1px 3px rgba(0,0,0,.3), 
                0 3px 5px rgba(0,0,0,.2), 
                0 5px 10px rgba(0,0,0,.25), 
                0 10px 10px rgba(0,0,0,.2), 
                0 20px 20px rgba(0,0,0,.15); 
            animation: float 3s ease-in-out infinite; 
        }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        
        .card { background: #ffffff; padding: 40px; border-radius: 25px; box-shadow: 0 20px 60px rgba(0,0,0,0.6); text-align: center; width: 100%; max-width: 450px; z-index: 2; }
        h2 { color: #333; margin-top: 0; text-transform: uppercase; font-size: 1.5rem; }
        
        select { width: 100%; padding: 15px; border-radius: 12px; background: #f0f7ff; border: 2px solid #ddecff; font-size: 1rem; margin-bottom: 25px; outline: none; box-sizing: border-box; }
        
        .btn-primary { 
            width: 100%; 
            padding: 16px; 
            border-radius: 12px; 
            border: none; 
            background: #5dade2; 
            color: white; 
            font-weight: bold; 
            cursor: pointer; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            transition: 0.3s;
        }
        .btn-primary:hover { background: #4a9bd1; transform: scale(1.02); }

        @media (max-width: 600px) {
            .system-title-outer { font-size: 2.2rem; letter-spacing: 4px; }
            .card { padding: 25px; }
        }
    </style>
</head>
<body>
    <div class="stars-container" id="starsBox"></div>
    
    <div class="outer-header">
        <h1 class="system-title-outer">PHISHSHIELD</h1>
    </div>

    <div class="card">
        <h2>Group of people</h2>
        <form method="post">
            <select name="gop_value">
                <option value="School Student">School Student</option>
                <option value="University Student">University Student</option>
                <option value="Employee">Employee</option>
                <option value="Senior Citizen">Senior Citizen</option>
                <option value="Unemployed">Unemployed</option>
            </select>
            <button type="submit" class="btn-primary">CONFIRM <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>

    <script>
        const starsBox = document.getElementById('starsBox');
        for (let i = 0; i < 30; i++) {
            const starIcon = document.createElement('i');
            starIcon.className = 'fas fa-star star';
            starIcon.style.left = Math.random() * 100 + '%';
            starIcon.style.top = Math.random() * 100 + '%';
            starIcon.style.fontSize = (Math.random() * 40 + 40) + 'px';
            starIcon.style.setProperty('--duration', (Math.random() * 4 + 3) + 's');
            starsBox.appendChild(starIcon);
        }
    </script>
</body>
</html>