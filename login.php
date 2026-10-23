<?php
session_start();

require_once "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: course.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {

        $error = "Please enter your username/email and password.";
    } else {

        $sql = "SELECT u.ID, u.USERNAME, u.FULLNAME, u.PHOTO, u.PASSWORD, u.USERTYPE_ID, t.Usertype AS USERTYPE
                FROM tbl_users u
                LEFT JOIN tbl_usertype t ON u.USERTYPE_ID = t.ID
                WHERE USERNAME = ? OR EMAIL = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Database query failed.");
        }

        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if ($user) {

            $storedPassword = $user["PASSWORD"];

            // Verify the entered password against the stored hash
            if (password_verify($password, $storedPassword)) {

                // Regenerate session ID after successful login
                session_regenerate_id(true);

                $_SESSION["user_id"] = (int)$user["ID"];
                $_SESSION["username"] = $user["USERNAME"];
                $_SESSION["fullname"] = $user["FULLNAME"];
                $_SESSION["photo"] = $user["PHOTO"] ?? null;
                $_SESSION["usertype_id"] = (int)$user["USERTYPE_ID"] ?? 0;
                $_SESSION["usertype"] = $user["USERTYPE"] ?? "";

                $landing = "subjects.php";
                switch ($_SESSION["usertype"]) {
                    case "Admin":
                        $landing = "course.php";
                        break;
                    case "Registrar":
                        $landing = "subjects.php";
                        break;
                    case "Dean":
                        $landing = "subjects.php";
                        break;
                    case "Instructor":
                        $landing = "subjects.php";
                        break;
                    case "Student":
                        $landing = "subjects.php";
                        break;
                }
                header("Location: " . $landing);
                exit();
            } else {

                $error = "Invalid username/email or password.";
            }
        } else {

            $error = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Login | ACT 6</title>

    <link rel="stylesheet" href="act2.css">

    <style>
        body.login-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8eef2;
            font-family: Arial, sans-serif;
        }

        .login-shell {
            width: 380px;
            max-width: calc(100% - 40px);
            background: #fff;
            border: 1px solid #c5cdd3;
        }

        .login-head {
            background: #c8e1f1;
            border-bottom: 5px solid #78b800;
            padding: 22px 25px;
            color: #263b50;
        }

        .login-head h1 {
            font-size: 25px;
            margin: 0 0 8px;
        }

        .login-head p {
            font-size: 13px;
            margin: 0;
        }

        .login-form {
            padding: 25px;
        }

        .login-form h2 {
            font-size: 20px;
            color: #24364b;
            border-bottom: 1px solid #ddd;
            padding-bottom: 12px;
            margin: 0 0 20px;
        }

        .login-form label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 6px;
        }

        .login-form input {
            width: 100%;
            box-sizing: border-box;
            height: 38px;
            padding: 8px;
            border: 1px solid #c8c8c8;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .login-form input:focus {
            outline: none;
            border-color: #00a6d6;
        }

        .login-form button {
            width: 100%;
            border: none;
            background: #f39200;
            color: white;
            padding: 11px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-form button:hover {
            background: #dc8000;
        }

        .login-error {
            background: #fff0ef;
            border: 1px solid #dca5a0;
            color: #9b3028;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .login-foot {
            padding: 12px;
            text-align: center;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body class="login-page">

    <main class="login-shell">

        <header class="login-head">
            <h1>ACT 6</h1>
            <p>Student Registration &amp; Academic Records</p>
        </header>

        <form class="login-form"
            method="POST"
            action="login.php">

            <h2>User Login</h2>

            <?php if (!empty($error)): ?>

                <div class="login-error">
                    <?php
                    echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>
                </div>

            <?php endif; ?>

            <label for="login">
                Username or Email
            </label>

            <input
                type="text"
                id="login"
                name="login"
                placeholder="Enter username or email"
                value="<?php
                        echo htmlspecialchars(
                            $_POST["login"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>"
                autocomplete="username"
                required
                autofocus>

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required>

            <button type="submit">
                Log In
            </button>

        </form>



    </main>

</body>

</html>