<?php
session_start();
require_once 'db_config.php';

// Ambil ID pengguna sebelum session dimusnahkan
$displayUserNo = isset($_SESSION['gop_id']) ? $_SESSION['gop_id'] : null;

if (!$displayUserNo && (!isset($_GET['step']) || $_GET['step'] != 'thankyou')) {
    echo "<script>window.location.href='./gop.php';</script>";
    exit();
}

$step = isset($_GET['step']) ? $_GET['step'] : 'choose';

if (isset($_GET['action']) && $_GET['action'] === "confirm_yes") {
    if (isset($_SESSION['temp_rating']) && isset($_SESSION['gop_id'])) {
        $ratingValue = $_SESSION['temp_rating'];
        $currentGop = $_SESSION['gop_id'];

        // Table name must be lowercase 'ratingvalue'
        $stmt = $conn->prepare("INSERT INTO ratingvalue (gop_id, RatingValue) VALUES (?, ?)");
        $stmt->bind_param("ii", $currentGop, $ratingValue);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            header("Location: ./RatingValue.php?step=thankyou");
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['RatingValue'])) {
    $_SESSION['temp_rating'] = $_POST['RatingValue'];
    header("Location: ./RatingValue.php?step=confirm");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finish & Rate | PhishShield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0; padding: 20px; min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; 
            justify-content: flex-start; 
            /* Memberikan ruang yang sangat sedikit di atas */
            padding-top: 30px; 
            font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at center, #0a1128 0%, #00040a 100%);
            position: relative; box-sizing: border-box; 
            overflow-y: auto; 
            overflow-x: hidden;
        }

        .galaxy-container { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star { position: absolute; background: white; border-radius: 50%; opacity: 0.5; animation: twinkle var(--duration) infinite ease-in-out; }
        @keyframes twinkle { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.2); opacity: 0.8; } }

        .orbit-ring { position: absolute; top: 50%; left: 50%; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50%; transform: translate(-50%, -50%); }
        .planet-container { position: absolute; top: 50%; left: 50%; transform-origin: center; animation: rotateOrbit var(--speed) linear infinite; }
        .planet-body { position: absolute; border-radius: 50%; box-shadow: inset -5px -5px 15px rgba(0,0,0,0.8), 0 0 15px var(--glow); }
        @keyframes rotateOrbit { from { transform: translate(-50%, -50%) rotate(0deg); } to { transform: translate(-50%, -50%) rotate(360deg); } }

        .user-profile { position: fixed; top: 20px; right: 20px; background: rgba(93, 173, 226, 0.2); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 10px 20px; color: white; display: flex; align-items: center; gap: 10px; z-index: 10; font-weight: bold; }
        
        .outer-header { 
            text-align: center; 
            margin-bottom: 20px; 
            z-index: 2; 
            position: relative;
            /* Menaikkan tajuk ke atas */
            margin-top: 10px;
        }
        .system-title-outer {
            font-weight: 900; letter-spacing: 8px; color: #ffffff; font-size: 3.8rem;
            text-transform: uppercase; display: block;
            text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.5);
            animation: floatTitle 3s ease-in-out infinite;
        }
        @keyframes floatTitle { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        .card {
            background: #ffffff; padding: 40px; border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8); text-align: center;
            width: 100%; max-width: 420px; z-index: 2; color: #2e86c1; position: relative; box-sizing: border-box;
            /* Merapatkan kad dengan tajuk */
            margin-top: 5px; 
        }

        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 12px; margin: 25px 0; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2.8rem; color: #ddd; cursor: pointer; transition: 0.3s; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #ffea00; text-shadow: 0 0 15px rgba(255, 234, 0, 0.6); }

        .btn { width: 100%; padding: 15px; border-radius: 12px; border: none; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none; display: block; box-sizing: border-box; margin-top: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-primary { background: linear-gradient(135deg, #5dade2, #2e86c1); color: white; box-shadow: 0 5px 15px rgba(46, 134, 193, 0.3); }
        .btn-secondary { background: #f8f9fa; color: #2e86c1; border: 2px solid #e9ecef; }
        .btn:hover { transform: translateY(-3px); filter: brightness(1.1); }

        .power-icon { font-size: 5rem; color: #ff5f6d; margin-bottom: 20px; display: block; filter: drop-shadow(0 0 10px rgba(255,95,109,0.3)); }

        @media (max-width: 600px) {
            body { padding-top: 20px; }
            .system-title-outer { font-size: 2.2rem; letter-spacing: 4px; }
            .card { padding: 30px 20px; width: 95%; margin-top: 5px; }
            .user-profile { font-size: 0.8rem; padding: 8px 15px; top: 10px; right: 10px; }
            .star-rating label { font-size: 2.2rem; }
            .outer-header { margin-top: 0px; margin-bottom: 10px; }
        }
    </style>
</head>
<body>

<div class="galaxy-container" id="galaxyBox"></div>

<?php if ($step != 'thankyou' && $displayUserNo): ?>
    <div class="user-profile">
        <i class="fas fa-user-circle"></i> User #<?php echo $displayUserNo; ?>
    </div>
<?php endif; ?>

<div class="outer-header">
    <h1 class="system-title-outer">PHISHSHIELD</h1>
</div>

<div class="card">
    <?php if ($step === 'choose'): ?>
        <h2 style="margin-top: 0;">Rate Your Experience</h2>
        <p style="color: #666; font-size: 0.9rem;">How was your URL analysis session?</p>
        <form method="post">
            <div class="star-rating">
                <input type="radio" id="star5" name="RatingValue" value="5" required>
                <label for="star5"><i class="fas fa-star"></i></label>
                <input type="radio" id="star4" name="RatingValue" value="4">
                <label for="star4"><i class="fas fa-star"></i></label>
                <input type="radio" id="star3" name="RatingValue" value="3">
                <label for="star3"><i class="fas fa-star"></i></label>
                <input type="radio" id="star2" name="RatingValue" value="2">
                <label for="star2"><i class="fas fa-star"></i></label>
                <input type="radio" id="star1" name="RatingValue" value="1">
                <label for="star1"><i class="fas fa-star"></i></label>
            </div>
            <button type="submit" class="btn btn-primary">NEXT <i class="fas fa-chevron-right"></i></button>
        </form>

    <?php elseif ($step === 'confirm'): ?>
        <h1 style="color: #f1c40f; font-size: 3.5rem; margin: 0; text-shadow: 0 0 20px rgba(241, 196, 15, 0.4);">
            <?php echo $_SESSION['temp_rating']; ?> <i class="fas fa-star"></i>
        </h1>
        <h2>Submit & Finish?</h2>
        <p style="font-size: 0.9rem; color: #666;">Data will be saved and your session will end.</p>
        <a href="./RatingValue.php?action=confirm_yes" class="btn btn-primary">YES (FINISH)</a>
        <a href="./ResultHeuristicAnalysis.php" class="btn btn-secondary">NO (BACK)</a>

    <?php elseif ($step === 'thankyou'): ?>
        <i class="fas fa-power-off power-icon"></i>
        <h1 style="margin: 0; color: #0a1128;">Thank You!</h1>
        <p style="color: #666;">Your feedback has been successfully recorded.</p>
        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee;">
            <p style="opacity: 0.6; font-size: 0.8rem;">You may now close this window safely.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    const galaxyBox = document.getElementById('galaxyBox');
    for (let i = 0; i < 80; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        const size = Math.random() * 2 + 1;
        star.style.width = size + 'px';
        star.style.height = size + 'px';
        star.style.setProperty('--duration', (Math.random() * 3 + 2) + 's');
        galaxyBox.appendChild(star);
    }

    const planets = [
        { color: '#ff5f6d', size: 15, distance: 320, speed: '18s', glow: '#ff5f6d' },
        { color: '#5dade2', size: 25, distance: 480, speed: '30s', glow: '#5dade2' },
        { color: '#f1c40f', size: 12, distance: 400, speed: '22s', glow: '#f1c40f' }
    ];

    planets.forEach(p => {
        const ring = document.createElement('div');
        ring.className = 'orbit-ring';
        ring.style.width = (p.distance * 2) + 'px';
        ring.style.height = (p.distance * 2) + 'px';
        galaxyBox.appendChild(ring);

        const container = document.createElement('div');
        container.className = 'planet-container';
        container.style.width = (p.distance * 2) + 'px';
        container.style.height = (p.distance * 2) + 'px';
        container.style.setProperty('--speed', p.speed);

        const body = document.createElement('div');
        body.className = 'planet-body';
        body.style.width = p.size + 'px';
        body.style.height = p.size + 'px';
        body.style.backgroundColor = p.color;
        body.style.top = '0px';
        body.style.left = '50%';
        body.style.transform = 'translate(-50%, -50%)';
        body.style.setProperty('--glow', p.glow);

        container.appendChild(body);
        galaxyBox.appendChild(container);
    });
</script>
</body>
</html>