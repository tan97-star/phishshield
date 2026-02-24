<?php
session_start();
require_once 'db_config.php';

// 1. SECURITY CHECK — Ensure user has selected a GOP first
if (!isset($_SESSION['gop_id'])) { 
    header("Location: ./gop.php"); 
    exit(); 
}

$userNo = $_SESSION['gop_id']; 
$step = isset($_GET['step']) ? $_GET['step'] : 'choose';

// Helper to keep the "Paste" title active during errors
$actionParam = (isset($_GET['action']) && $_GET['action'] === 'paste') ? "&action=paste" : "";

// 2. FORM HANDLING
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Step: Selecting method (Paste or Manual)
    if (isset($_POST['method'])) {
        $next = ($_POST['method'] === "paste") ? "clipboard" : "enter_url";
        header("Location: ./InsertMethod.php?step=$next");
        exit();
    }

    // Step: Clipboard Permission
    if (isset($_POST['access'])) {
        if ($_POST['access'] === "allow") {
            header("Location: ./InsertMethod.php?step=enter_url&action=paste");
        } else {
            header("Location: ./InsertMethod.php?step=choose");
        }
        exit();
    }

    // Step: Save URL to Database
    if (isset($_POST['link_url'])) {
        $LinkURL = trim($_POST['link_url']);

        if (filter_var($LinkURL, FILTER_VALIDATE_URL) && strpos($LinkURL, 'http') === 0) {
            $gop_id = $_SESSION['gop_id'];

            $stmt = $conn->prepare("INSERT INTO linkurl (gop_id, url_address) VALUES (?, ?)");
            $stmt->bind_param("is", $gop_id, $LinkURL);

            if ($stmt->execute()) {
                $_SESSION['LinkURL_id'] = $conn->insert_id;
                $_SESSION['stored_LinkURL'] = $LinkURL;
                header("Location: ./ResultHeuristicAnalysis.php");
                exit();
            } else {
                $_SESSION['error'] = "Database Error: " . $stmt->error;
                header("Location: ./InsertMethod.php?step=enter_url" . $actionParam);
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid URL. Must include http:// or https://"; 
            header("Location: ./InsertMethod.php?step=enter_url" . $actionParam);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert URL | PhishShield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; background-color: #051622; position: relative; box-sizing: border-box; overflow-y: auto; overflow-x: hidden; }
        .stars-container { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star { position: absolute; color: #5dade2; opacity: 0.4; filter: drop-shadow(0 0 5px rgba(93, 173, 226, 0.5)); animation: twinkle var(--duration) infinite ease-in-out; }
        @keyframes twinkle { 0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.2; } 50% { transform: scale(1.1) rotate(10deg); opacity: 0.5; } }
        .user-profile { position: fixed; top: 20px; right: 20px; background: rgba(93, 173, 226, 0.2); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 10px 20px; color: white; display: flex; align-items: center; gap: 10px; z-index: 10; font-weight: bold; backdrop-filter: blur(5px); }
        .user-profile i { font-size: 1.5rem; color: #5dade2; }
        .system-title-outer { font-weight: 900; letter-spacing: 8px; color: #ffffff; font-size: 4rem; margin: 0 0 30px; text-transform: uppercase; text-align: center; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.5); animation: float 3s ease-in-out infinite; z-index: 2; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .card { background: #ffffff; padding: 40px; border-radius: 25px; box-shadow: 0 20px 60px rgba(0,0,0,0.6); text-align: center; width: 100%; max-width: 480px; z-index: 2; box-sizing: border-box; }
        h2 { color: #2e86c1; margin-top: 0; text-transform: uppercase; margin-bottom: 25px; }
        .btn { width: 100%; padding: 16px; border-radius: 12px; border: none; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; text-transform: uppercase; margin-bottom: 15px; transition: 0.3s; font-size: 0.95rem; box-sizing: border-box; }
        .btn-primary { background: #5dade2; color: white; }
        .btn-secondary { background: #f0f7ff; color: #2e86c1; border: 2px solid #ddecff; }
        .btn:hover { transform: translateY(-2px); filter: brightness(1.05); }
        input[type="text"] { width: 100%; padding: 15px; border-radius: 12px; background: #f8f9fa; border: 2px solid #e9ecef; font-size: 1rem; margin-bottom: 20px; outline: none; box-sizing: border-box; color: #333; }
        .error-text { color: #e74c3c; background: #fdf2f2; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; font-weight: bold; }
        @media (max-width: 600px) { .system-title-outer { font-size: 2.2rem; letter-spacing: 4px; margin-top: 40px; } .card { padding: 25px; width: 95%; } .user-profile { font-size: 0.8rem; padding: 8px 15px; top: 10px; right: 10px; } }
    </style>
</head>
<body>
    <div class="stars-container" id="starsBox"></div>
    <div class="user-profile"><i class="fas fa-user-circle"></i> User #<?php echo $userNo; ?></div>
    <h1 class="system-title-outer">PHISHSHIELD</h1>
    <div class="card">
        <?php if ($step == 'choose'): ?>
            <h2>INSERT METHOD</h2>
            <form method="post">
                <button name="method" value="paste" class="btn btn-secondary">Paste from Clipboard</button>
                <button name="method" value="manually" class="btn btn-secondary">Type Link URL Manually</button>
            </form>
        <?php elseif ($step == 'clipboard'): ?>
            <h2>Allow Access?</h2>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">The system will read your copied link from the clipboard.</p>
            <form method="post">
                <button name="access" value="allow" class="btn btn-primary">Allow Access</button>
                <button name="access" value="block" class="btn btn-secondary">DON'T ALLOW</button>
            </form>
        <?php elseif ($step == 'enter_url'): ?>
            <h2><?php echo (isset($_GET['action']) && $_GET['action'] === 'paste') ? 'Paste Link URL' : 'Enter Link URL'; ?></h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-text"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <input type="text" name="link_url" id="urlInput" placeholder="https://example.com" required autocomplete="off">
                <button type="submit" class="btn btn-primary">CHECK LINK URL</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const starsBox = document.getElementById('starsBox');
        for (let i = 0; i < 40; i++) {
            const starIcon = document.createElement('i');
            starIcon.className = 'fas fa-star star';
            starIcon.style.left = Math.random() * 100 + '%';
            starIcon.style.top = Math.random() * 100 + '%';
            starIcon.style.fontSize = (Math.random() * 30 + 15) + 'px';
            starIcon.style.setProperty('--duration', (Math.random() * 4 + 3) + 's');
            starsBox.appendChild(starIcon);
        }

        window.onload = async function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'paste') {
                try {
                    const text = await navigator.clipboard.readText();
                    if (text) {
                        const input = document.getElementById('urlInput');
                        if (input) input.value = text;
                    }
                } catch (err) { console.log("Clipboard access denied"); }
            }
        };
    </script>
</body>
</html>