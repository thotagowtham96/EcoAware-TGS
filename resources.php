<?php
require_once "db.php";

$sql = "SELECT id, title, category, description
        FROM resources
        ORDER BY id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Error loading resources: " . htmlspecialchars($conn->error));
}

$resources = [];

while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}

$conn->close();

// Build the category list from the database.
$categories = [];

foreach ($resources as $resource) {
    $category = trim($resource["category"]);

    if ($category !== "") {
        $categories[strtolower($category)] = $category;
    }
}

natcasesort($categories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Environmental Resources | EcoAware</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .resource-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .resource-filters input,
        .resource-filters select {
            flex: 1;
            min-width: 220px;
            padding: 14px;
            border: 1px solid #cbd8cb;
            border-radius: 12px;
            background: white;
            color: #243526;
            font: inherit;
            box-sizing: border-box;
        }

        .resource-filters input:focus,
        .resource-filters select:focus {
            outline: 2px solid #278447;
            outline-offset: 2px;
        }

        .resource-grid {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(min(100%, 260px), 1fr)
            );
            gap: 24px;
        }

        .resource-card {
            padding: 24px;
            border: 1px solid #dce8dc;
            border-radius: 16px;
            background: white;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            overflow-wrap: anywhere;
        }

        .resource-category {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 10px;
            border-radius: 20px;
            background: #e8f5e9;
            color: #236b36;
            font-size: 0.85rem;
        }

        .resource-card h2 {
            color: #17633d;
            margin-top: 8px;
        }

        #resourceStatus {
            margin: 18px 0;
            color: #526454;
        }

        #noResources {
            display: none;
            padding: 24px;
            border-radius: 12px;
            background: #f1f7ef;
            text-align: center;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 30px 0;
        }

        .pagination button {
            min-width: 42px;
            padding: 10px 14px;
            border: 1px solid #cbd8cb;
            border-radius: 8px;
            background: white;
            color: #17633d;
            cursor: pointer;
            font: inherit;
        }

        .pagination button:hover:not(:disabled),
        .pagination button.active {
            background: #17633d;
            color: white;
            border-color: #17633d;
        }

        .pagination button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .pagination button:focus-visible {
            outline: 2px solid #278447;
            outline-offset: 3px;
        }

        @media (max-width: 600px) {
            .resource-filters {
                flex-direction: column;
            }

            .resource-filters input,
            .resource-filters select {
                width: 100%;
                min-width: 0;
            }

            .resource-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header class="site-header">
    <div class="container navbar">
        <a href="index.html" class="logo">🌿 EcoAware</a>

        <nav class="nav-links">
            <a href="index.html">Home</a>
            <a href="issues.html">Environmental Issues</a>
            <a href="waste.html">Waste Management</a>
            <a href="conservation.html">Conservation Tips</a>
            <a href="practices.html">Eco-Friendly Practices</a>
            <a href="tips.html">Environmental Tips</a>
        </nav>
    </div>
</header>

<section class="page-banner">
    <div class="container">
        <p class="eyebrow">LEARN • ACT • PROTECT</p>
        <h1>Environmental Resources</h1>
        <p>
            Explore environmental information and discover
            ways to protect our planet.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Search and category filters -->
        <div class="resource-filters">

            <input
                type="search"
                id="resourceSearch"
                placeholder="Search title, description or category..."
                aria-label="Search resources"
            >

            <select
                id="categoryFilter"
                aria-label="Filter resources by category"
            >
                <option value="all">All Categories</option>

                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars(
                        strtolower($category),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>">
                        <?= htmlspecialchars(
                            $category,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <p id="resourceStatus" aria-live="polite">
            Loading resources...
        </p>

        <!-- Resource cards -->
        <div class="resource-grid" id="resourceGrid">

            <?php foreach ($resources as $row): ?>

                <article
                    class="resource-card"
                    data-category="<?= htmlspecialchars(
                        strtolower(trim($row["category"])),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                    <span class="resource-category">
                        <?= htmlspecialchars(
                            $row["category"],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <h2>
                        <?= htmlspecialchars(
                            $row["title"],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h2>

                    <p>
                        <?= nl2br(htmlspecialchars(
                            $row["description"],
                            ENT_QUOTES,
                            'UTF-8'
                        )) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        </div>

        <p id="noResources">
            No matching resources found. Try another search or category.
        </p>

        <!-- Pagination buttons are created by JavaScript -->
        <nav
            class="pagination"
            id="pagination"
            aria-label="Resource pages"
        ></nav>

    </div>
</section>

<footer class="footer">
    <div class="container">
        <a href="index.html" class="logo footer-brand">🌿 EcoAware</a>

        <p>Learn today. Protect tomorrow.</p>

        <p class="copyright">
            &copy; <?= date("Y") ?> EcoAware.
            Environmental awareness for everyone.
        </p>
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("resourceSearch");
    const categoryFilter = document.getElementById("categoryFilter");
    const resourceGrid = document.getElementById("resourceGrid");
    const status = document.getElementById("resourceStatus");
    const noResources = document.getElementById("noResources");
    const pagination = document.getElementById("pagination");

    const cards = Array.from(
        resourceGrid.querySelectorAll(".resource-card")
    );

    const itemsPerPage = 6;
    let currentPage = 1;

    function getFilteredCards() {
        const searchText = searchInput.value.trim().toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();

        return cards.filter(function (card) {
            const text = card.textContent.toLowerCase();

            const category = (
                card.dataset.category || ""
            ).trim().toLowerCase();

            const matchesSearch = text.includes(searchText);

            const matchesCategory =
                selectedCategory === "all" ||
                category === selectedCategory;

            return matchesSearch && matchesCategory;
        });
    }

    function createPageButton(label, page, options = {}) {
        const button = document.createElement("button");

        button.type = "button";
        button.textContent = label;

        if (options.active) {
            button.classList.add("active");
            button.setAttribute("aria-current", "page");
        }

        if (options.disabled) {
            button.disabled = true;
        }

        button.addEventListener("click", function () {
            currentPage = page;
            renderResources();

            // Return focus to the resource section after changing pages.
            resourceGrid.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        });

        pagination.appendChild(button);
    }

    function renderPagination(totalPages) {
        pagination.replaceChildren();

        if (totalPages <= 1) {
            return;
        }

        createPageButton(
            "Previous",
            currentPage - 1,
            { disabled: currentPage === 1 }
        );

        for (let page = 1; page <= totalPages; page++) {
            createPageButton(
                String(page),
                page,
                { active: page === currentPage }
            );
        }

        createPageButton(
            "Next",
            currentPage + 1,
            { disabled: currentPage === totalPages }
        );
    }

    function renderResources() {
        const filteredCards = getFilteredCards();
        const totalItems = filteredCards.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Keep the selected page within the valid range.
        if (totalPages === 0) {
            currentPage = 1;
        } else if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const visibleCards = new Set(
            filteredCards.slice(startIndex, endIndex)
        );

        cards.forEach(function (card) {
            card.style.display = visibleCards.has(card) ? "" : "none";
        });

        noResources.style.display =
            totalItems === 0 ? "block" : "none";

        if (totalItems === 0) {
            status.textContent = "Showing 0 resources";
        } else {
            const firstItem = startIndex + 1;
            const lastItem = Math.min(endIndex, totalItems);

            status.textContent =
                "Showing " + firstItem + "–" + lastItem +
                " of " + totalItems + " matching resources";
        }

        renderPagination(totalPages);
    }

    searchInput.addEventListener("input", function () {
        currentPage = 1;
        renderResources();
    });

    categoryFilter.addEventListener("change", function () {
        currentPage = 1;
        renderResources();
    });

    renderResources();
});
</script>

</body>
</html>
