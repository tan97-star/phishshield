<?php
session_start();
require_once 'db_config.php';

// 1. KAWALAN AKSES - Pastikan admin telah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ./AdminLogin.php");
    exit();
}

// --- 2. PENGUMPULAN DATA ---

// A. GOP details (D1)
$demographic_query = $conn->query("SELECT GOP, COUNT(*) as volume FROM gop GROUP BY GOP");
$labels_usergroup = []; $data_usergroup = [];
if ($demographic_query) {
    while($row = $demographic_query->fetch_assoc()) {
        $labels_usergroup[] = $row['GOP'];
        $data_usergroup[] = $row['volume'];
    }
}

// B. Global Counter (D2)
$grand_total_analyzed_res = $conn->query("SELECT COUNT(*) as total FROM linkurl");
$grand_total_analyzed = ($grand_total_analyzed_res) ? $grand_total_analyzed_res->fetch_assoc()['total'] : 0;

// C. Result Heuristic Analysis (D4)
$risk_assessment_query = $conn->query("SELECT RiskLevelColorCode, COUNT(*) as volume FROM resultheuristicanalysis GROUP BY RiskLevelColorCode");
$risk_distribution = ['green'=>0, 'yellow'=>0, 'red'=>0];
if ($risk_assessment_query) {
    while($row = $risk_assessment_query->fetch_assoc()) {
        $risk_distribution[$row['RiskLevelColorCode']] = $row['volume'];
    }
}

// D. Heuristic Intelligence Aggregate (D3)
$intelligence_metrics_res = $conn->query("
    SELECT 
        SUM(https_presence) as https_pos, COUNT(*) - SUM(https_presence) as https_neg,
        SUM(ip_host) as ip_pos, COUNT(*) - SUM(ip_host) as ip_neg,
        SUM(url_length) as len_pos, COUNT(*) - SUM(url_length) as len_neg,
        SUM(user_info) as user_pos, COUNT(*) - SUM(user_info) as user_neg,
        SUM(subdomain_depth) as sub_pos, COUNT(*) - SUM(subdomain_depth) as sub_neg,
        SUM(punycode) as puny_pos, COUNT(*) - SUM(punycode) as puny_neg,
        SUM(keywords) as key_pos, COUNT(*) - SUM(keywords) as key_neg,
        SUM(encoding) as enc_pos, COUNT(*) - SUM(encoding) as enc_neg,
        SUM(brand_impersonation) as brand_pos, COUNT(*) - SUM(brand_impersonation) as brand_neg,
        SUM(redirect_chain) as red_pos, COUNT(*) - SUM(redirect_chain) as red_neg
    FROM heuristicfactor
");
$intelligence_metrics = ($intelligence_metrics_res) ? $intelligence_metrics_res->fetch_assoc() : [];

// E. Satisfaction Index (D5)
$rating_query = $conn->query("SELECT RatingValue, COUNT(*) as volume FROM ratingvalue GROUP BY RatingValue ORDER BY RatingValue DESC");
$labels_rating = []; $data_rating = [];
if ($rating_query) {
    while($row = $rating_query->fetch_assoc()) {
        $labels_rating[] = $row['RatingValue']." Stars";
        $data_rating[] = $row['volume'];
    }
}

$heuristic_dictionary = [
    "https"=>["title"=>"SSL Protocol Verification","danger_msg"=>"Insecure HTTP","safe_msg"=>"Secure HTTPS"],
    "ip"=>["title"=>"Host Identity Check","danger_msg"=>"IP Literal Detected","safe_msg"=>"Domain Format"],
    "len"=>["title"=>"String Length Analysis","danger_msg"=>"URL > 75 chars","safe_msg"=>"Standard Length"],
    "user"=>["title"=>"User-Info Detection","danger_msg"=>"Embedded @ char","safe_msg"=>"Clean URL Path"],
    "sub"=>["title"=>"DNS Hierarchy Depth","danger_msg"=>"Multilevel Subdomains","safe_msg"=>"Standard Depth"],
    "puny"=>["title"=>"Homograph Verification","danger_msg"=>"Punycode Detected","safe_msg"=>"ASCII Set"],
    "key"=>["title"=>"Lexical Keyword Scan","danger_msg"=>"Phishing Keywords","safe_msg"=>"Clean Lexical"],
    "enc"=>["title"=>"Encoding Percentage","danger_msg"=>">20% Encoding","safe_msg"=>"Minimal Encoding"],
    "brand"=>["title"=>"Entity Impersonation","danger_msg"=>"Target Brand Mimicry","safe_msg"=>"Generic Structure"],
    "red"=>["title"=>"Navigation Chain Depth","danger_msg"=>"High Redirect Chain","safe_msg"=>"Direct Path"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Hub | Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 20px; min-height: 100vh; font-family: 'Poppins', sans-serif; background-color: #000; color: #fff; position: relative; overflow-x: hidden; }

        /* KEKALKAN BACKGROUND ANIMATION ASAL */
        .bg-animation { position: fixed; inset: 0; z-index: 0; }
        .bg-animation::before {
            content: ""; position: absolute; inset: -145%; rotate: -45deg; background: #000000;
            background-image: 
                radial-gradient(4px 100px at 0px 235px, rgb(255, 140, 17), #0000),
                radial-gradient(4px 100px at 300px 235px, rgb(255, 119, 0), #884e2800),
                radial-gradient(1.5px 1.5px at 150px 117.5px, rgb(255, 144, 9) 100%, #0000 150%),
                radial-gradient(4px 100px at 0px 252px, rgb(156, 14, 137), #0000),
                radial-gradient(4px 100px at 300px 252px, rgb(23, 41, 206), #0000),
                radial-gradient(1.5px 1.5px at 150px 126px, rgb(247, 102, 18) 100%, #0000 150%),
                radial-gradient(4px 100px at 0px 150px, rgb(249, 121, 16), #0000),
                radial-gradient(4px 100px at 300px 150px, rgb(255, 128, 18), #0000),
                radial-gradient(1.5px 1.5px at 150px 75px, rgb(255, 116, 10) 100%, #0000 150%);
            background-size: 300px 235px, 300px 252px, 300px 150px;
            animation: hi 150s linear infinite;
        }
        @keyframes hi {
            0% { background-position: 0px 220px, 3px 220px, 151.5px 337.5px; }
            to { background-position: 0px 6800px, 3px 6800px, 151.5px 6917.5px; }
        }

        .wrapper { max-width: 1200px; margin: auto; position: relative; z-index: 2; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); padding: 40px; border-radius: 40px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.6); }

        /* KESAN TAJUK 3D */
        .header-main { 
            font-size: 3rem; margin-bottom: 40px; text-align:center; 
            letter-spacing: 8px; font-weight: 800; text-transform: uppercase; 
            text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 10px 20px rgba(255, 140, 17, 0.5); 
        }

        .summary-card { background: linear-gradient(135deg, #ff9100, #ff3d00); padding: 30px; border-radius:25px; margin-bottom:40px; text-align:center; color:#fff; font-weight: 800; font-size: 1.6rem; text-transform: uppercase; }
        h3 { color: #ff9100; margin-bottom: 25px; font-size: 1rem; font-weight: 600; text-transform: uppercase; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .chart-box { background: rgba(0, 0, 0, 0.5); padding: 25px; border-radius: 25px; border: 1px solid rgba(255,255,255,0.05); }
        .data-table { width:100%; border-collapse: separate; border-spacing:0 12px; margin-top:30px; }
        .data-table th { color: #ff9100; text-align:left; padding:15px; font-size:0.7rem; letter-spacing: 2px; }
        .data-table td { padding:15px; background: rgba(255,255,255,0.03); font-size: 0.8rem; }
        .data-table td:first-child { border-radius: 15px 0 0 15px; border-left: 4px solid #ff3d00; font-weight: 600; }
        .data-table td:last-child { border-radius: 0 15px 15px 0; }
        .exit-btn { display: inline-block; padding: 15px 50px; border-radius: 50px; background: transparent; color: #ff5252; border: 2px solid #ff5252; text-decoration: none; font-weight: 800; margin-top: 40px; transition: 0.3s; }
        .exit-btn:hover { background: #ff5252; color: #fff; transform: translateY(-5px); }

        @media (max-width: 768px) {
            .header-main { font-size: 1.8rem; letter-spacing: 4px; }
            .glass-panel { padding: 20px; }
            .summary-card { font-size: 1.1rem; }
            .data-table th { font-size: 0.6rem; }
            .data-table td { font-size: 0.7rem; }
        }
    </style>
</head>
<body>
<div class="bg-animation"></div>

<div class="wrapper">
    <div class="glass-panel">
        <div class="header-main">ADMIN DASHBOARD</div>

        <div class="summary-card">
            <i class="fas fa-satellite-dish"></i> Total Scan URL: <?php echo number_format($grand_total_analyzed); ?>
        </div>

        <div class="dashboard-grid">
            <div class="chart-box">
                <h3><i class="fas fa-users"></i> GROUP OF PEOPLE</h3>
                <canvas id="audienceChart"></canvas>
            </div>
            <div class="chart-box">
                <h3><i class="fas fa-biohazard"></i> RISK LEVEL </h3>
                <canvas id="threatChart"></canvas>
            </div>
            <div class="chart-box">
                <h3><i class="fas fa-smile"></i> USER'S FEEDBACK</h3>
                <canvas id="satisfactionChart"></canvas>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Heuristic Rule</th><th>Danger Context</th><th>Safe Context</th>
                        <th style="text-align:center;">Flagged</th><th style="text-align:center;">Verified</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($heuristic_dictionary as $key=>$info): ?>
                    <tr>
                        <td><?php echo $info['title']; ?></td>
                        <td style="color:#ff8a80; opacity: 0.8;"><?php echo $info['danger_msg']; ?></td>
                        <td style="color:#b9f6ca; opacity: 0.8;"><?php echo $info['safe_msg']; ?></td>
                        <td style="text-align:center; color:#ff1744; font-weight:800;"><?php echo $intelligence_metrics[$key.'_pos'] ?? 0; ?></td>
                        <td style="text-align:center; color:#00e676; font-weight:800;"><?php echo $intelligence_metrics[$key.'_neg'] ?? 0; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align:center;">
            <a href="./AdminLogin.php" class="exit-btn">LOGOUT </a>
        </div>
    </div>
</div>

<script>
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color = '#fff';

new Chart(document.getElementById('audienceChart'), {
    type:'doughnut',
    data:{ 
        labels: <?php echo json_encode($labels_usergroup); ?>, 
        datasets:[{ 
            data:<?php echo json_encode($data_usergroup); ?>, 
            backgroundColor:['#00d2ff','#00ff7f','#ffea00','#ff1744','#aa00ff'],
            borderWidth: 0
        }] 
    },
    options: { plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
});

new Chart(document.getElementById('threatChart'), {
    type:'bar',
    data:{ 
        labels:['Safe','Warning','Danger'], 
        datasets:[{ 
            label: 'Volume',
            data:[<?php echo $risk_distribution['green']; ?>,<?php echo $risk_distribution['yellow']; ?>,<?php echo $risk_distribution['red']; ?>], 
            backgroundColor:['#00ff7f','#ffea00','#ff1744'],
            borderRadius: 10
        }] 
    }
});

new Chart(document.getElementById('satisfactionChart'), {
    type:'bar',
    data:{ 
        labels: <?php echo json_encode($labels_rating); ?>, 
        datasets:[{ 
            label:'Entries', 
            data:<?php echo json_encode($data_rating); ?>, 
            backgroundColor:'rgba(0, 210, 255, 0.4)',
            borderColor: '#00d2ff',
            borderWidth: 2,
            borderRadius: 10
        }] 
    }
});
</script>
</body>
</html>