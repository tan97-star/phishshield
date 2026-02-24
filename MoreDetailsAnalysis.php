<?php
session_start();
require_once 'db_config.php';

// Kawalan keselamatan: Pastikan pengguna mempunyai sesi analisis yang aktif
if (!isset($_SESSION['LinkURL_id'])) {
    header("Location: ./InsertMethod.php");
    exit();
}

$url_id = $_SESSION['LinkURL_id'];
$userNo = $_SESSION['gop_id']; // Diambil untuk paparan profil

// Ambil data dari database: Pastikan nama table 'heuristicfactor' (huruf kecil)
$result = $conn->query("SELECT * FROM heuristicfactor WHERE url_id = $url_id ORDER BY id DESC LIMIT 1");
$factors = $result->fetch_assoc();

// Fallback jika data gagal diambil
if (!$factors) {
    $factors = array_fill_keys([
        "https_presence", "ip_host", "url_length", "user_info", 
        "subdomain_depth", "punycode", "keywords", "encoding", 
        "brand_impersonation", "redirect_chain"
    ], 0);
}

// Senarai peraturan heuristik
$rules = [
    "https_presence" => "HTTPS Presence",
    "ip_host" => "IP Literal Host",
    "url_length" => "Excessive URL Length",
    "user_info" => "Embedded User Info (@)",
    "subdomain_depth" => "Subdomain Depth",
    "punycode" => "Punycode Homograph",
    "keywords" => "Suspicious Keywords",
    "encoding" => "High Percent-Encoding",
    "brand_impersonation" => "Brand Impersonation",
    "redirect_chain" => "Redirect Chain Length"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analysis Details | PhishShield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Asas & Latar Belakang */
        body { 
            margin: 0; padding: 15px; min-height: 100vh; 
            background-color: #051622; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #fff; display: flex; flex-direction: column; align-items: center; 
            overflow-y: auto; box-sizing: border-box;
        }

        .stars-container { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star { position: absolute; color: #5dade2; opacity: 0.3; animation: twinkle var(--duration) infinite ease-in-out; }
        @keyframes twinkle { 0%, 100% { opacity: 0.2; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.1); } }

        /* User Profile Icon */
        .user-profile { position: fixed; top: 15px; right: 15px; background: rgba(93, 173, 226, 0.2); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 8px 15px; color: white; display: flex; align-items: center; gap: 8px; z-index: 10; font-size: 0.85rem; font-weight: bold; backdrop-filter: blur(5px); }

        /* Header Tittle Section */
        .outer-header { text-align: center; margin-top: 50px; margin-bottom: 30px; z-index: 2; width: 100%; max-width: 800px; }
        
        .system-title-outer { 
            font-weight: 900; letter-spacing: 6px; color: #ffffff; font-size: 2.8rem;
            text-transform: uppercase; margin: 0;
            text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 10px 20px rgba(0,0,0,0.5);
            animation: float 3s ease-in-out infinite;
        }
        
        .detail-sub-title { 
            font-size: 1.2rem; color: #5dade2; font-weight: 600; 
            margin-top: 10px; opacity: 0.9; letter-spacing: 1px;
        }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        /* Table Header Mimic (Desktop Only) */
        .table-header { 
            display: flex; width: 100%; max-width: 900px; padding: 10px 20px; 
            background: rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 10px; 
            font-weight: bold; font-size: 0.75rem; color: #5dade2; letter-spacing: 1px;
            z-index: 2; text-transform: uppercase;
        }

        /* Container & Cards */
        .details-wrapper { width: 100%; max-width: 900px; display: flex; flex-direction: column; gap: 15px; z-index: 2; }

        .info-card { 
            background: #ffffff; border-radius: 20px; padding: 20px; 
            display: flex; align-items: center; justify-content: space-between; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); color: #333; transition: 0.3s;
        }
        .info-card:hover { transform: scale(1.01); }

        /* Card Sections */
        .col-rule { flex: 1.2; }
        .col-rule h4 { margin: 0; font-size: 1.05rem; color: #051622; font-weight: 800; }

        .col-point { flex: 0.5; text-align: center; }
        .point-val { font-size: 2rem; font-weight: 900; }

        .col-explanation { flex: 2; padding-left: 15px; border-left: 2px solid #f0f0f0; }
        .status-badge { font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
        .desc-text { font-size: 0.8rem; line-height: 1.4; color: #555; }

        /* Buttons */
        .actions { text-align: center; margin: 40px 0; z-index: 2; }
        .btn-done { 
            background: linear-gradient(135deg, #5dade2, #2e86c1); color: white; 
            padding: 16px 60px; border-radius: 50px; text-decoration: none; 
            font-weight: bold; font-size: 1rem; letter-spacing: 2px;
            box-shadow: 0 10px 20px rgba(46, 134, 193, 0.3); transition: 0.3s;
        }
        .btn-done:hover { filter: brightness(1.1); transform: translateY(-3px); }

        /* Warna Status */
        .safe-theme { color: #2ecc71; }
        .danger-theme { color: #e74c3c; }

        /* MOBILE OPTIMIZATION */
        @media (max-width: 768px) {
            .table-header { display: none; } /* Sorok header column pada mobile */
            .system-title-outer { font-size: 1.8rem; letter-spacing: 3px; }
            .detail-sub-title { font-size: 1rem; }
            
            .info-card { 
                flex-direction: column; text-align: center; padding: 25px 20px; gap: 15px;
            }
            .col-explanation { 
                padding-left: 0; border-left: none; border-top: 1px solid #eee; padding-top: 15px; 
            }
            .status-badge { justify-content: center; }
            .col-point { margin: 10px 0; }
            .user-profile { top: 10px; right: 10px; font-size: 0.75rem; }
        }
    </style>
</head>
<body>

    <div class="stars-container" id="starsBox"></div>

    <div class="user-profile">
        <i class="fas fa-user-circle"></i> User #<?php echo $userNo; ?>
    </div>

    <div class="outer-header">
        <h1 class="system-title-outer">MORE DETAILS ANALYSIS</h1>
        <div class="detail-sub-title"><i class="fas fa-microscope"></i>  HEURISTIC ANALYSIS DETAILS</div>
    </div>

    <div class="table-header">
        <div style="flex: 1.2;">Heuristic Rule</div>
        <div style="flex: 0.5; text-align: center;">Point</div>
        <div style="flex: 2; padding-left: 15px;">Analysis Explanation</div>
    </div>

    <div class="details-wrapper">
        <?php foreach ($rules as $key => $name): 
            $val = $factors[$key]; 
            $isRisk = ($val == 1);
            $theme = $isRisk ? "danger-theme" : "safe-theme";
            $icon = $isRisk ? "fa-circle-exclamation" : "fa-circle-check";
            $statusText = $isRisk ? "DANGER" : "SAFE";
        ?>
        
        <div class="info-card">
            <div class="col-rule">
                <h4><?php echo $name; ?></h4>
            </div>

            <div class="col-point">
                <div class="point-val <?php echo $theme; ?>"><?php echo $val; ?></div>
            </div>

            <div class="col-explanation">
                <div class="status-badge <?php echo $theme; ?>">
                    <i class="fas <?php echo $icon; ?>"></i> <?php echo $statusText; ?>
                </div>
                <div class="desc-text">
                    <?php 
                    if ($key == "https_presence") { echo $isRisk ? "No HTTPS detected. Connection is using insecure HTTP protocol." : "HTTPS is present. Connection is encrypted and secure."; } 
                    elseif ($key == "ip_host") { echo $isRisk ? "URL uses an IP address (literal host) which is common in phishing." : "URL uses a standard and normal domain name."; }
                    elseif ($key == "url_length") { echo $isRisk ? "URL exceeds 75 characters, often used to hide malicious paths." : "URL length is within a normal and safe range."; }
                    elseif ($key == "user_info") { echo $isRisk ? "Contains the '@' symbol in the URL, which can misdirect users." : "No suspicious '@' symbol detected in the URL."; }
                    elseif ($key == "subdomain_depth") { echo $isRisk ? "Contains 4 or more subdomain levels, indicating a complex hidden path." : "Subdomain depth is at a normal level (1-2 levels)."; }
                    elseif ($key == "punycode") { echo $isRisk ? "Contains Punycode (xn--) or suspicious Unicode intended to mimic other characters." : "No Punycode or suspicious Unicode homographs detected."; }
                    elseif ($key == "keywords") { echo $isRisk ? "Contains suspicious phishing keywords like 'login', 'verify', or 'bank'." : "No suspicious or high-risk keywords found in the URL."; }
                    elseif ($key == "encoding") { echo $isRisk ? "URL encoding makes up more than 20% of the string, hiding the true destination." : "Minimal or low percent-encoding detected."; }
                    elseif ($key == "brand_impersonation") { echo $isRisk ? "The URL mimics a well-known brand name to deceive the user." : "No brand impersonation or suspicious mimicking detected."; }
                    elseif ($key == "redirect_chain") { echo $isRisk ? "Detected 3 or more redirects, which is a high-risk behavior." : "Detected only 0-1 redirect, which is normal."; }
                    ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="actions">
        <a href="./ResultHeuristicAnalysis.php" class="btn-done">DONE</a>
    </div>

    <script>
        const starsBox = document.getElementById('starsBox');
        for (let i = 0; i < 35; i++) {
            const star = document.createElement('i');
            star.className = 'fas fa-star star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.fontSize = (Math.random() * 20 + 10) + 'px';
            star.style.setProperty('--duration', (Math.random() * 3 + 2) + 's');
            starsBox.appendChild(star);
        }
    </script>
</body>
</html>