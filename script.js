```javascript
// EcoAware - JavaScript functionality

document.addEventListener("DOMContentLoaded", function () {

    // 1. Highlight the current page in the navigation
    const currentPage = window.location.pathname.split("/").pop() || "index.html";

    document.querySelectorAll(".nav-links a").forEach(function (link) {
        const linkPage = link.getAttribute("href");

        if (linkPage === currentPage) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });

    // 2. Smooth scrolling for links to sections on the same page
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener("click", function (event) {
            const targetId = link.getAttribute("href");

            if (targetId.length > 1) {
                const target = document.querySelector(targetId);

                if (target) {
                    event.preventDefault();
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            }
        });
    });

    // 3. Green Challenge checkbox feedback
    const challengeList = document.querySelector(".challenge-list");
    const challengeNote = document.querySelector(".challenge-note");

    if (challengeList && challengeNote) {
        const checkboxes = challengeList.querySelectorAll(
            'input[type="checkbox"]'
        );

        function updateChallenge() {
            let completed = 0;

            checkboxes.forEach(function (checkbox) {
                if (checkbox.checked) {
                    completed++;
                }
            });

            if (completed === 0) {
                challengeNote.textContent =
                    "Choose an action to begin your green challenge.";
            } else {
                challengeNote.textContent =
                    "Great work! You completed " + completed +
                    " of " + checkboxes.length + " green actions.";
            }
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", updateChallenge);
        });

        updateChallenge();
    }

    // 4. Display the current year in the footer
    document.querySelectorAll(".copyright").forEach(function (footer) {
        footer.textContent = "© " + new Date().getFullYear() +
            " EcoAware. Environmental awareness for everyone.";
    });

});
```