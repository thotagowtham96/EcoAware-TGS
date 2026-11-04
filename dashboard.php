
<?php
session_start();
require_once "db.php";

// Protect the dashboard: login is required
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION["name"] ?? $_SESSION["user_name"] ?? "Eco Warrior";
$userName = htmlspecialchars($userName, ENT_QUOTES, "UTF-8");

// Get resource count
$resourceCount = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM resources");

if ($result) {
    $row = $result->fetch_assoc();
    $resourceCount = (int)($row["total"] ?? 0);
}

// Get environmental tips count if the table exists
$tipCount = 0;
$tipResult = $conn->query("SELECT COUNT(*) AS total FROM tips");

if ($tipResult) {
    $row = $tipResult->fetch_assoc();
    $tipCount = (int)($row["total"] ?? 0);
}

// Get latest resources
$latestResources = [];
$latestResult = $conn->query(
    "SELECT id, title, category, description
     FROM resources
     ORDER BY id DESC
     LIMIT 3"
);

if ($latestResult) {
    while ($row = $latestResult->fetch_assoc()) {
        $latestResources[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your EcoAware environmental awareness dashboard. Explore resources, learn sustainable habits, and protect our planet.">
    <title>Dashboard | EcoAware</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green: #237a42;
            --green-dark: #185b30;
            --green-light: #e7f6e9;
            --text: #20352a;
            --muted: #718076;
            --white: #ffffff;
            --background: #f5f8f4;
            --border: #e5ece5;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav-inner {
            width: min(1180px, 92%);
            min-height: 76px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .logo {
            font-size: 27px;
            font-weight: 800;
            color: var(--green);
            white-space: nowrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 26px;
            list-style: none;
        }

        .nav-links a {
            color: #526157;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--green);
        }

        .logout-link {
            background: #edf7ee;
            color: var(--green) !important;
            padding: 10px 16px;
            border-radius: 9px;
        }

        .container {
            width: min(1180px, 92%);
            margin: 34px auto 0;
        }

        .welcome-banner {
            position: relative;
            overflow: hidden;
            padding: 42px;
            border-radius: 22px;
            background: linear-gradient(120deg, #d9f7df, #eff9ed);
            border: 1px solid #d4eed8;
        }

        .welcome-banner::after {
            content: "🌿";
            position: absolute;
            right: 55px;
            top: 12px;
            font-size: 100px;
            opacity: 0.20;
            transform: rotate(-12deg);
            pointer-events: none;
        }

        .eyebrow {
            color: var(--green);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .welcome-banner h1 {
            font-size: clamp(27px, 4vw, 38px);
            line-height: 1.25;
            margin-bottom: 12px;
            max-width: 700px;
        }

        .welcome-banner p {
            color: #526b58;
            max-width: 650px;
            font-size: 16px;
        }

        .welcome-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 12px 19px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .btn-primary {
            background: var(--green);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--green);
            border: 1px solid #d6e8d9;
        }

        .section {
            margin-top: 34px;
        }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-heading h2 {
            font-size: 23px;
            line-height: 1.3;
        }

        .section-heading p {
            color: var(--muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .text-link {
            color: var(--green);
            font-size: 14px;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .stat-card {
            padding: 23px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 16px rgba(30, 60, 35, 0.025);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 25px;
            background: var(--green-light);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: #fff2d9;
        }

        .stat-card:nth-child(3) .stat-icon {
            background: #e3f1fb;
        }

        .stat-number {
            font-size: 27px;
            font-weight: 800;
            line-height: 1.2;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .quick-card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .quick-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(30, 60, 35, 0.08);
        }

        .quick-icon {
            font-size: 31px;
            margin-bottom: 14px;
        }

        .quick-card h3 {
            font-size: 17px;
            margin-bottom: 8px;
        }

        .quick-card p {
            color: var(--muted);
            font-size: 14px;
            min-height: 46px;
            margin-bottom: 16px;
        }

        .resource-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .resource-card {
            padding: 23px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
        }

        .category {
            display: inline-block;
            padding: 5px 10px;
            margin-bottom: 12px;
            background: var(--green-light);
            color: var(--green);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }

        .resource-card h3 {
            font-size: 17px;
            margin-bottom: 8px;
        }

        .resource-card p {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 17px;
            overflow-wrap: anywhere;
        }

        .empty-state {
            padding: 28px;
            text-align: center;
            background: var(--white);
            border: 1px dashed #cbd9cc;
            border-radius: 14px;
            color: var(--muted);
        }

        .callout {
            margin-top: 34px;
            padding: 25px 28px;
            border-radius: 15px;
            background: #214f31;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }

        .callout h2 {
            font-size: 21px;
            margin-bottom: 5px;
        }

        .callout p {
            color: #d5e8d8;
            font-size: 14px;
        }

        .callout .btn {
            background: var(--white);
            color: var(--green-dark);
            white-space: nowrap;
        }

        footer {
            text-align: center;
            padding: 28px 16px;
            color: var(--muted);
            font-size: 13px;
            margin-top: 28px;
        }

        @media (max-width: 850px) {
            .nav-inner {
                padding: 15px 0;
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }

            .nav-links {
                gap: 18px;
                flex-wrap: wrap;
            }

            .stats-grid,
            .quick-grid,
            .resource-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .welcome-banner {
                padding: 30px;
            }

            .welcome-banner::after {
                right: 20px;
                font-size: 70px;
            }
        }

        @media (max-width: 560px) {
            .container {
                margin-top: 20px;
            }

            .nav-links {
                gap: 14px;
            }

            .nav-links a {
                font-size: 13px;
            }

            .welcome-banner {
                padding: 25px 20px;
            }

            .welcome-banner::after {
                display: none;
            }

            .stats-grid,
            .quick-grid,
            .resource-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 18px;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .callout {
                align-items: flex-start;
                flex-direction: column;
            }

            .quick-card p {
                min-height: 0;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="nav-inner">
        <a href="index.html" class="logo">🌿 EcoAware</a>

        <nav aria-label="Main navigation">
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="resources.php">Resources</a></li>
                <li><a href="tips.html">Environmental Tips</a></li>
                <li><a href="issues.html">Environmental Issues</a></li>
                <li><a href="logout.php" class="logout-link">Logout ↗</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container">

    <section class="welcome-banner">
        <div class="eyebrow">Your personal environmental hub</div>

        <h1>Welcome back, <?= $userName ?>! 🌱</h1>

        <p>
            Every small action makes a difference. Explore environmental
            resources, discover sustainable habits, and help build a greener
            future for everyone.
        </p>

        <div class="welcome-actions">
            <a href="resources.php" class="btn btn-primary">
                Explore Resources →
            </a>

            <a href="tips.html" class="btn btn-secondary">
                Discover Green Tips
            </a>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <h2>Your Environmental Hub</h2>
                <p>Explore what is available on EcoAware.</p>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon">📚</div>
                <div>
                    <div class="stat-number"><?= $resourceCount ?></div>
                    <div class="stat-label">Environmental Resources</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon">🌿</div>
                <div>
                    <div class="stat-number"><?= $tipCount ?></div>
                    <div class="stat-label">Environmental Tips</div>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon">♻️</div>
                <div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">Planet to Protect</div>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <h2>Explore EcoAware</h2>
                <p>Choose a topic and start learning.</p>
            </div>
        </div>

        <div class="quick-grid">
            <article class="quick-card">
                <div class="quick-icon">🌍</div>
                <h3>Environmental Issues</h3>
                <p>Understand pollution, climate change, and other environmental challenges.</p>
                <a href="issues.html" class="text-link">Explore issues →</a>
            </article>

            <article class="quick-card">
                <div class="quick-icon">♻️</div>
                <h3>Waste Management</h3>
                <p>Learn about waste segregation, recycling, and responsible disposal.</p>
                <a href="waste.html" class="text-link">Manage waste →</a>
            </article>

            <article class="quick-card">
                <div class="quick-icon">💧</div>
                <h3>Conservation Tips</h3>
                <p>Discover practical ways to save water, energy, and natural resources.</p>
                <a href="conservation.html" class="text-link">Save resources →</a>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <div>
                <h2>Latest Resources</h2>
                <p>Recently added environmental learning materials.</p>
            </div>

            <a href="resources.php" class="text-link">View all →</a>
        </div>

        <?php if (count($latestResources) > 0): ?>
            <div class="resource-grid">
                <?php foreach ($latestResources as $resource): ?>
                    <article class="resource-card">
                        <span class="category">
                            <?= htmlspecialchars(
                                $resource["category"] ?: "Environment",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars(
                                $resource["title"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars(
                                $resource["description"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </p>

                        <a href="resources.php" class="text-link">
                            Explore resource →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No resources have been added yet.</p>
                <p>Check back soon for new environmental learning materials.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="callout">
        <div>
            <h2>Small actions. Big impact. 🌱</h2>
            <p>Choose sustainable habits and encourage others to do the same.</p>
        </div>

        <a href="practices.html" class="btn">Explore Green Practices →</a>
    </section>

</main>

<footer>
    © 2026 EcoAware. Learn today, protect tomorrow. 🌍
</footer>

</body>
</html>
