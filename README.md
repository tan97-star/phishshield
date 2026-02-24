PHISHSHIELD: Heuristic-Based Phishing Detection System
PHISHSHIELD is a security-focused web application built with PHP and MySQL that analyzes URLs to detect potential phishing threats using a multi-factor heuristic analysis engine.

🛡️ Core Features
🔍 Heuristic Analysis Engine
The system evaluates every submitted URL against 10 specific risk factors:

SSL Verification: Checks for missing HTTPS protocol.

Identity Check: Detects IP-based hosts and Punycode homograph attacks.

Lexical Analysis: Scans for suspicious keywords (e.g., "login", "bank") and excessive URL length.

Redirection & Obfuscation: Identifies high percent-encoding and redirect chains.

Brand Protection: Flags URLs mimicking well-known brands like Maybank, PayPal, or Google.

📊 Admin Intelligence Hub
A comprehensive dashboard for security administrators to monitor threats in real-time:

Global Counter: Tracks the total volume of URLs scanned.

Demographic Analytics: Categorizes users by groups (Students, Employees, Senior Citizens).

Threat Distribution: Visualizes risk levels (Safe, Warning, Danger) using Chart.js.

Satisfaction Index: Monitors user feedback through a star-rating system.

🛠️ Technical Stack
Backend: PHP (Object-Oriented via db_config.php).

Database: MySQL.

Frontend: HTML5, CSS3 (Galaxy/Space Theme), JavaScript.

Charts: Chart.js for data visualization.

📂 Project Structure
index.php: Entry point for user demographic selection.

InsertMethod.php: URL submission via manual typing or clipboard paste.

ResultHeuristicAnalysis.php: Primary logic for calculating risk scores.

MoreDetailsAnalysis.php: Detailed breakdown of each heuristic rule applied.

AdminDashboard.php: High-level analytics and intelligence reporting.
