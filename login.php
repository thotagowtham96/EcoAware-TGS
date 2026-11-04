
<?php
session_start();
require_once "db.php";

$error = "";
$success = "";

// Show registration success message
if (isset($_GET["registered"])) {
    $success = "Registration successful! Please log in.";
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Unable to process your login. Please try again.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {

                $stmt->bind_result(
                    $id,
                    $name,
                    $dbEmail,
                    $hashedPassword,
                    $role
                );

                $stmt->fetch();

                // Verify the hashed password
                if (password_verify($password, $hashedPassword)) {

                    // Accept only recognized roles
                    if (!in_array($role, ["admin", "user"], true)) {
                        $error = "Your account role is invalid. Contact the administrator.";
                    } else {
                        session_regenerate_id(true);

                        // Store session values
                        $_SESSION["user_id"] = $id;
                        $_SESSION["name"] = $name;
                        $_SESSION["user_name"] = $name;
                        $_SESSION["email"] = $dbEmail;

                        // Keep both role keys for compatibility
                        $_SESSION["role"] = $role;
                        $_SESSION["user_role"] = $role;

                        // Close resources before redirecting
                        $stmt->close();
                        $conn->close();

                        // Redirect according to database role
                        if ($role === "admin") {
                            header("Location: admin.php");
                        } else {
                            header("Location: dashboard.php");
                        }

                        exit;
                    }

                } else {
                    $error = "Incorrect email or password.";
                }

            } else {
                $error = "Incorrect email or password.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | EcoAware</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f8f3;
            color: #26372b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: #ffffff;
            padding: 18px 6%;
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
        }

        .nav-links {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 26px;
        }

        .nav-links a {
            color: #34483a;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
        }

        .nav-links a:hover {
            color: #287a40;
        }

        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 18px;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            padding: 42px;
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(35, 79, 44, 0.10);
        }

        .leaf-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #e7f5e9;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 32px;
        }

        h1 {
            text-align: center;
            font-size: 29px;
            color: #214c2d;
            margin-bottom: 12px;
        }

        .subtitle {
            text-align: center;
            color: #6b796e;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .message {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .error {
            background: #fff0f0;
            border: 1px solid #f3cccc;
            color: #a32626;
        }

        .success {
            background: #eaf7ec;
            border: 1px solid #c8e8cf;
            color: #216c37;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #34483a;
            margin-bottom: 8px;
        }

        input {
            display: block;
            width: 100%;
            padding: 14px;
            border: 1px solid #d4dfd5;
            border-radius: 8px;
            background: #fbfdfb;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #31834a;
            box-shadow: 0 0 0 3px rgba(49, 131, 74, 0.12);
        }

        .login-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #287a40;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background: #1e6032;
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            color: #6b796e;
            font-size: 14px;
        }

        .register-link a {
            color: #287a40;
            font-weight: 700;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #748075;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px 5%;
            }

            .nav-links {
                flex-wrap: wrap;
                gap: 16px;
            }

            .login-card {
                padding: 30px 22px;
            }

            h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a href="index.html" class="logo">🌿 EcoAware</a>

    <nav aria-label="Main navigation">
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="resources.php">Resources</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>
</header>

<main class="main">
    <section class="login-card">

        <div class="leaf-icon">🌱</div>

        <h1>Welcome Back!</h1>

        <p class="subtitle">
            Log in to EcoAware and continue making a difference.
        </p>

        <?php if ($success !== ""): ?>
            <div class="message success">
                <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="message error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
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
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="login-btn">
                Log In →
            </button>

        </form>

        <p class="register-link">
            Don't have an account?
            <a href="register.php">Create an account</a>
        </p>

    </section>
</main>

<footer>
    © 2026 EcoAware · Small actions, greener future. 🌍
</footer>

</body>
</html>
