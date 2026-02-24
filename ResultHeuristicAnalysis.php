<?php
session_start();
require_once 'db_config.php';

// Check if a URL session is active
if (!isset($_SESSION['LinkURL_id'])) { 
    header("Location: ./InsertMethod.php"); 
    exit(); 
}

$url_id = $_SESSION['LinkURL_id'];
$url = $_SESSION['stored_LinkURL'];
$userNo = $_SESSION['gop_id'];
$host = parse_url($url, PHP_URL_HOST);

// --- 1. IDENTIFY HEURISTIC FACTORS ---
$https = (strpos($url, 'https') === false) ? 1 : 0;
$ip = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $host) ? 1 : 0;
$len = (strlen($url) > 75) ? 1 : 0;
$user = (strpos($url, '@') !== false) ? 1 : 0;
$sub = (substr_count($host, '.') >= 4) ? 1 : 0;
$puny = (strpos($url, 'xn--') !== false) ? 1 : 0;
$keys = preg_match('/(login|verify|update|bank|free|bonus|lucky)/i', $url) ? 1 : 0;
// FIXED: Added quotes around the percent symbol
$enc = (substr_count($url, '%') > 5) ? 1 : 0;
$brand = preg_match('/(maybank|cimb|shopee|pbebank|google|facebook|instagram|paypal|netflix|microsoft)/i', $url) ? 1 : 0;
$red = preg_match('/(\?url=|\?next=|\?redirect=)/i', $url) ? 1 : 0;

// --- 2. CALCULATE TOTAL RISK SCORE ---
$score = $https + $ip + $len + $user + $sub + $puny + $keys + $enc + $brand + $red;

// --- 3. CATEGORIZE RISK LEVEL ---
if ($score == 0) { 
    $status = "SAFE"; $color = "green"; $hex = "#2ecc71"; $bg = "#e8fdf0"; 
} elseif ($score <= 3) { 
    $status = "WARNING"; $color = "yellow"; $hex = "#f1c40f"; $bg = "#fef9e7"; 
} else { 
    $status = "DANGER"; $color = "red"; $hex = "#e74c3c"; $bg = "#fdedec"; 
}

$check = $conn->prepare("SELECT id FROM resultheuristicanalysis WHERE url_id = ?");
$check->bind_param("i", $url_id); 
$check->execute();

if ($check->get_result()->num_rows == 0) {
    $stmt3 = $conn->prepare("INSERT INTO heuristicfactor (url_id, https_presence, ip_host, url_length, user_info, subdomain_depth, punycode, keywords, encoding, brand_impersonation, redirect_chain) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt3->bind_param("iiiiiiiiiii", $url_id, $https, $ip, $len, $user, $sub, $puny, $keys, $enc, $brand, $red);
    $stmt3->execute();

    $stmt4 = $conn->prepare("INSERT INTO resultheuristicanalysis (url_id, TotalRiskLevelScore, RiskLevelColorCode) VALUES (?, ?, ?)");
    $stmt4->bind_param("iis", $url_id, $score, $color);
    $stmt4->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Heuristic Analysis | PhishShield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; background-color: #051622; position: relative; box-sizing: border-box; overflow-y: auto; overflow-x: hidden; }
        .stars-container { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star { position: absolute; color: #5dade2; opacity: 0.4; animation: twinkle var(--duration) infinite ease-in-out; }
        @keyframes twinkle { 0%, 100% { transform: scale(1); opacity: 0.2; } 50% { transform: scale(1.1); opacity: 0.5; } }
        .user-profile { position: fixed; top: 20px; right: 20px; background: rgba(93, 173, 226, 0.2); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 10px 20px; color: white; display: flex; align-items: center; gap: 10px; z-index: 10; font-weight: bold; }
        .system-title-outer { font-weight: 900; letter-spacing: 8px; color: #ffffff; font-size: 3.5rem; text-transform: uppercase; margin-bottom: 40px; text-align: center; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.5); animation: float 3s ease-in-out infinite; z-index: 2; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .main-content { display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 40px; width: 100%; max-width: 1000px; z-index: 2; }
        .card { background: <?php echo $bg; ?>; padding: 40px; border-radius: 25px; box-shadow: 0 20px 60px rgba(0,0,0,0.6); text-align: center; flex: 1; max-width: 450px; border: 3px solid <?php echo $hex; ?>; color: #333; box-sizing: border-box; }
        .status-badge { display: inline-block; padding: 12px 30px; border-radius: 50px; font-size: 1.5rem; font-weight: 900; color: #ffffff; background-color: <?php echo $hex; ?>; margin: 20px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .circle-wrap { width: 240px; height: 240px; border-radius: 50%; border: 10px solid <?php echo $hex; ?>; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(5px); text-align: center; padding: 10px; }
        .score-num { font-size: 5rem; font-weight: 900; color: #ffffff; line-height: 1; margin-bottom: 5px; }
        .score-label { color: #ffffff; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; max-width: 160px; line-height: 1.2; }
        .score-sub { color: white; font-weight: bold; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; font-size: 0.7rem; margin-top: 5px; }
        .btn { display: block; width: 100%; max-width: 300px; padding: 14px; margin: 0 auto 12px; border-radius: 12px; font-weight: bold; text-decoration: none; text-align: center; transition: 0.3s; box-sizing: border-box; }
        .btn-primary { background: linear-gradient(135deg, #5dade2, #2e86c1); color: white; border: none; }
        .btn-secondary { background: white; color: #34495e; border: 2px solid #e9ecef; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        @media (max-width: 900px) { .main-content { flex-direction: column; gap: 30px; } .system-title-outer { font-size: 2.2rem; letter-spacing: 4px; margin-top: 50px; } .circle-wrap { order: -1; width: 200px; height: 200px; } .score-num { font-size: 4rem; } .card { width: 100%; padding: 25px; } }
    </style>
</head>
<body>
    <div class="stars-container" id="starsBox"></div>
    <div class="user-profile"><i class="fas fa-user-circle"></i> User #<?php echo $userNo; ?></div>
    <h1 class="system-title-outer">PHISHSHIELD</h1>
    <div class="main-content">
        <div class="card">
            <h2 style="margin-bottom: 5px; font-size: 1.4rem;">RESULT HEURISTIC ANALYSIS</h2>
            <div class="status-badge"><?php echo $status; ?></div>
            <div style="margin-top: 10px;">
                <a href="./MoreDetailsAnalysis.php" class="btn btn-primary">More Details Analysis</a>
                <a href="./InsertMethod.php" class="btn btn-secondary">Check Another Link</a>
                <a href="./RatingValue.php" class="btn btn-secondary">Rating Value</a>
            </div>
        </div>
        <div class="circle-wrap">
            <div class="score-label">Total Risk Level Score</div>
            <div class="score-num"><?php echo $score; ?></div>
            <div class="score-sub">of 10 Points</div> 
        </div>
    </div>
    <script>
        const starsBox = document.getElementById('starsBox');
        for (let i = 0; i < 40; i++) {
            const starIcon = document.createElement('i');
            starIcon.className = 'fas fa-star star';
            starIcon.style.left = Math.random() * 100 + '%';
            starIcon.style.top = Math.random() * 100 + '%';
            starIcon.style.fontSize = (Math.random() * 20 + 10) + 'px';
            starIcon.style.setProperty('--duration', (Math.random() * 4 + 3) + 's');
            starsBox.appendChild(starIcon);
        }
    </script>
</body>
</html>