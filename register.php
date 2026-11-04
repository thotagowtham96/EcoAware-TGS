
<?php
session_start();
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all required fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered. Please log in.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                $stmt->close();
                $check->close();
                $conn->close();

                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | EcoAware</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f8f3;
            color: #26372b;
            min-height: 100vh;
        }

        .navbar {
            background: #ffffff;
            min-height: 76px;
            padding: 16px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .logo {
            color: #24743b;
            font-size: 28px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
        }

        .nav-links a {
            color: #34483a;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: #258344;
        }

        .page {
            min-height: calc(100vh - 76px);
            padding: 48px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-card {
            width: 100%;
            max-width: 510px;
            background: #ffffff;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(35, 79, 44, 0.10);
        }

        .leaf-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: #e7f5e9;
            font-size: 30px;
        }

        h1 {
            text-align: center;
            font-size: 29px;
            margin-bottom: 12px;
            color: #214c2d;
        }

        .subtitle {
            text-align: center;
            color: #6b796e;
            line-height: 1.6;
            margin-bottom: 28px;
            font-size: 15px;
        }

        .message {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .message.error {
            background: #fff0f0;
            color: #a32626;
            border: 1px solid #f3cccc;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #34483a;
        }

        .form-group input {
            display: block;
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d4dfd5;
            border-radius: 8px;
            background: #fbfdfb;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            border-color: #31834a;
            box-shadow: 0 0 0 3px rgba(49, 131, 74, 0.12);
            background: #ffffff;
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #287a40;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .register-btn:hover {
            background: #1e6032;
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            margin-top: 23px;
            color: #6b796e;
            font-size: 14px;
        }

        .login-link a {
            color: #287a40;
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #748075;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px 5%;
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                gap: 18px;
                flex-wrap: wrap;
            }

            .page {
                padding: 30px 16px;
                min-height: auto;
            }

            .register-card {
                padding: 28px 22px;
            }

            h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>
    <header class="navbar">
        <a class="logo" href="index.html">🌿 EcoAware</a>

        <nav aria-label="Main navigation">
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="resources.php">Resources</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="page">
        <section class="register-card">
            <div class="leaf-icon">🌱</div>

            <h1>Create Your Account</h1>
            <p class="subtitle">
                Join EcoAware and help protect our planet.
            </p>

            <?php if ($message !== ""): ?>
                <div class="message <?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter your password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button class="register-btn" type="submit">
                    Create Account
                </button>
            </form>

            <p class="login-link">
                Already registered?
                <a href="login.php">Log in</a>
            </p>
        </section>
    </main>

    <footer class="footer">
        © 2026 EcoAware · Small actions, greener future.
    </footer>
</body>
</html>
