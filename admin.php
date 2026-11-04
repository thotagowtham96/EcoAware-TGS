

<?php
session_start();
require_once "db.php";

// 1. Protect the admin page
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? $_SESSION["user_role"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

// 2. Handle resource actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // Add a resource
    if ($action === "add") {
        $title = trim($_POST["title"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $image_url = trim($_POST["image_url"] ?? "");

        if ($title === "" || $category === "" || $description === "") {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO resources
                (title, category, description, image_url)
                VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssss",
                $title,
                $category,
                $description,
                $image_url
            );

            if ($stmt->execute()) {
                $message = "Resource added successfully.";
            } else {
                $error = "Could not add the resource.";
            }
            $stmt->close();
        }
    }

    // Update a resource
    elseif ($action === "update") {
        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
        $title = trim($_POST["title"] ?? "");
        $category = trim($_POST["category"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $image_url = trim($_POST["image_url"] ?? "");

        if (!$id || $title === "" || $category === "" || $description === "") {
            $error = "Please provide valid resource details.";
        } else {
            $stmt = $conn->prepare(
                "UPDATE resources
                 SET title = ?, category = ?, description = ?, image_url = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "ssssi",
                $title,
                $category,
                $description,
                $image_url,
                $id
            );

            if ($stmt->execute()) {
                $message = $stmt->affected_rows > 0
                    ? "Resource updated successfully."
                    : "No changes made, or resource not found.";
            } else {
                $error = "Could not update the resource.";
            }
            $stmt->close();
        }
    }

    // Delete a resource
    elseif ($action === "delete") {
        $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

        if (!$id) {
            $error = "Invalid resource ID.";
        } else {
            $stmt = $conn->prepare(
                "DELETE FROM resources WHERE id = ?"
            );
            $stmt->bind_param("i", $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "Resource deleted successfully.";
            } else {
                $error = "Resource not found or could not be deleted.";
            }
            $stmt->close();
        }
    }

    // Prevent accidental resubmission on refresh
    if ($message !== "") {
        $_SESSION["admin_message"] = $message;
        header("Location: admin.php");
        exit;
    }
}

// Show success messages after redirect
if (isset($_SESSION["admin_message"])) {
    $message = $_SESSION["admin_message"];
    unset($_SESSION["admin_message"]);
}

// 3. Search resources
$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $term = "%" . $search . "%";
    $stmt = $conn->prepare(
        "SELECT id, title, category, description, image_url, created_at
         FROM resources
         WHERE title LIKE ? OR category LIKE ? OR description LIKE ?
         ORDER BY id DESC"
    );
    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT id, title, category, description, image_url, created_at
         FROM resources
         ORDER BY id DESC"
    );
}

$resources = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | EcoAware</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f7f1;
            color: #20352a;
        }
        header {
            background: #176b42;
            color: white;
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        header h1 { margin: 0; font-size: 25px; }
        header a {
            color: white;
            text-decoration: none;
            margin-left: 16px;
            font-weight: bold;
        }
        main { max-width: 1200px; margin: 28px auto; padding: 0 18px; }
        .panel {
            background: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 18px #173e2110;
        }
        h2 { color: #176b42; margin-top: 0; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }
        .full { grid-column: 1 / -1; }
        label { display: block; font-weight: bold; margin-bottom: 7px; }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd8ce;
            border-radius: 8px;
            font: inherit;
        }
        textarea { min-height: 105px; resize: vertical; }
        button, .btn {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 11px 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .primary { background: #176b42; color: white; }
        .secondary { background: #e6f0e8; color: #176b42; }
        .danger { background: #b83232; color: white; }
        .notice {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .success { background: #dff5e5; color: #155b2e; }
        .failure { background: #ffe4e4; color: #8d2020; }
        .search-row { display: flex; gap: 10px; margin-bottom: 18px; }
        .search-row input { flex: 1; }
        .resource {
            border: 1px solid #dce8de;
            border-radius: 12px;
            padding: 18px;
            margin-top: 15px;
        }
        .resource h3 { margin: 0 0 8px; }
        .category {
            display: inline-block;
            background: #e5f4e8;
            color: #176b42;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        details { margin-top: 14px; }
        summary { color: #176b42; cursor: pointer; font-weight: bold; }
        .actions { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 12px; }
        .inline-form { display: inline; }
        .empty { color: #66756a; padding: 18px 0; }
        footer { text-align: center; color: #66756a; padding: 20px; }
        @media (max-width: 650px) {
            .form-grid { grid-template-columns: 1fr; }
            .full { grid-column: auto; }
            .search-row { flex-direction: column; }
            header { align-items: flex-start; }
            header a { margin: 8px 12px 0 0; display: inline-block; }
            .panel { padding: 17px; }
        }
    </style>
</head>
<body>
<header>
    <h1>🌿 EcoAware Admin</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="resources.php">Resources</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h2>Resource Management</h2>
    <p>Welcome, <?= e($_SESSION["name"] ?? $_SESSION["user_name"] ?? "Admin") ?>.
       Manage environmental resources below.</p>

    <?php if ($message !== ""): ?>
        <div class="notice success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="notice failure"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2>➕ Add New Resource</h2>
        <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="add">
            <div class="form-grid">
                <div>
                    <label for="title">Resource title *</label>
                    <input id="title" name="title" required maxlength="200">
                </div>
                <div>
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        <option value="Pollution">Pollution</option>
                        <option value="Waste Management">Waste Management</option>
                        <option value="Water Conservation">Water Conservation</option>
                        <option value="Energy Conservation">Energy Conservation</option>
                        <option value="Biodiversity">Biodiversity</option>
                        <option value="Eco-Friendly Practices">Eco-Friendly Practices</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="full">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="full">
                    <label for="image_url">Image URL (optional)</label>
                    <input id="image_url" type="url" name="image_url"
                           placeholder="https://example.com/image.jpg">
                </div>
                <div class="full">
                    <button class="primary" type="submit">Add Resource</button>
                </div>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>📚 Existing Resources (<?= count($resources) ?>)</h2>

        <form class="search-row" method="GET" action="admin.php">
            <input type="search" name="search"
                   placeholder="Search title, category, or description..."
                   value="<?= e($search) ?>">
            <button class="primary" type="submit">Search</button>
            <a class="btn secondary" href="admin.php">Clear</a>
        </form>

        <?php if (count($resources) === 0): ?>
            <p class="empty">No resources found.</p>
        <?php else: ?>
            <?php foreach ($resources as $resource): ?>
                <article class="resource">
                    <h3><?= e($resource["title"]) ?></h3>
                    <span class="category"><?= e($resource["category"]) ?></span>
                    <p><?= nl2br(e($resource["description"])) ?></p>

                    <?php if (!empty($resource["image_url"])): ?>
                        <p>
                            <a href="<?= e($resource["image_url"]) ?>"
                               target="_blank" rel="noopener noreferrer">
                                View resource image
                            </a>
                        </p>
                    <?php endif; ?>

                    <small>Resource ID: <?= (int)$resource["id"] ?></small>

                    <details>
                        <summary>Edit this resource</summary>
                        <form method="POST" action="admin.php" style="margin-top:15px">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id"
                                   value="<?= (int)$resource["id"] ?>">

                            <div class="form-grid">
                                <div>
                                    <label>Title *</label>
                                    <input name="title" required maxlength="200"
                                           value="<?= e($resource["title"]) ?>">
                                </div>
                                <div>
                                    <label>Category *</label>
                                    <input name="category" required maxlength="100"
                                           value="<?= e($resource["category"]) ?>">
                                </div>
                                <div class="full">
                                    <label>Description *</label>
                                    <textarea name="description" required><?= e($resource["description"]) ?></textarea>
                                </div>
                                <div class="full">
                                    <label>Image URL</label>
                                    <input type="url" name="image_url"
                                           value="<?= e($resource["image_url"]) ?>">
                                </div>
                            </div>
                            <div class="actions">
                                <button class="primary" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </details>

                    <div class="actions">
                        <form class="inline-form" method="POST" action="admin.php"
                              onsubmit="return confirm('Delete this resource permanently?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$resource["id"] ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<footer>EcoAware — Learn, Act, Protect 🌍</footer>
</body>
</html>
<?php
$conn->close();
?>
