<?php
require_once __DIR__ . "/auth.php";
include "db.php";
require_once __DIR__ . "/user_images.php";

$message = "";
$found = null;


/* =========================
   ADD USER
========================= */

/* =========================
   ADD USER WITH IMAGE UPLOAD
========================= */
if (isset($_POST["btn_add"])) {

    $msg = "";
    $imagePath = "";
    $savedImage = "";

    $username = trim($_POST["username"] ?? "");
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $userType = (int)($_POST["usertype"] ?? 0);
    $plainPassword = $_POST["password"] ?? "";

    $targetDir = __DIR__ . "/images/users/";

    try {

        // Validate user information
        if (
            $username === "" ||
            $fullname === "" ||
            $email === "" ||
            $plainPassword === "" ||
            $userType <= 0
        ) {
            throw new Exception("Please complete all required fields.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }

        // Use safe characters for filename
        if (
            !preg_match('/^[a-zA-Z0-9_.-]{1,50}$/', $username)
            || $username === "."
            || $username === ".."
        ) {
            throw new Exception("Invalid username.");
        }

        // Check duplicate username
        $check = $conn->prepare(
            "SELECT ID FROM tbl_users WHERE USERNAME = ?"
        );

        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            throw new Exception("Username already exists.");
        }

        $check->close();

        // Check image upload
        if (
            !isset($_FILES["imageUpload"]) ||
            $_FILES["imageUpload"]["error"] !== UPLOAD_ERR_OK
        ) {
            throw new Exception("Please upload a valid image.");
        }


        if ($_FILES["imageUpload"]["size"] > 5 * 1024 * 1024) {
            throw new Exception("Image must not exceed 5 MB.");
        }

        $fileName = basename($_FILES["imageUpload"]["name"]);

        $fileType = strtolower(
            pathinfo($fileName, PATHINFO_EXTENSION)
        );

        $allowTypes = array("jpg", "jpeg", "png", "gif");

        if (!in_array($fileType, $allowTypes, true)) {
            throw new Exception("Invalid file type.");
        }

        $tempFile = $_FILES["imageUpload"]["tmp_name"];

        if (!is_uploaded_file($tempFile)) {
            throw new Exception("Invalid uploaded file.");
        }


        $imageInfo = @getimagesize($tempFile);

        if ($imageInfo === false) {
            throw new Exception("Uploaded file is not an image.");
        }

        if (!in_array($imageInfo[2], [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF
        ], true)) {
            throw new Exception("Unsupported image format.");
        }

        if (!extension_loaded("gd")) {
            throw new Exception("Enable PHP GD extension.");
        }

        // Create image directory
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new Exception("Cannot create image folder.");
            }
        }


        $targetFilePath = $targetDir . $username . ".JPG";
        $imagePath = "images/users/" . $username . ".JPG";

        
        if (file_exists($targetFilePath)) {
            throw new Exception("Image filename already exists.");
        }


        $image = @imagecreatefromstring(
            file_get_contents($tempFile)
        );

        if ($image === false) {
            throw new Exception("Unable to process image.");
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $jpegImage = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate(
            $jpegImage,
            255,
            255,
            255
        );

        imagefill($jpegImage, 0, 0, $white);

        imagecopy(
            $jpegImage,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height
        );

        imagedestroy($image);

        
        if (!imagejpeg($jpegImage, $targetFilePath, 90)) {
            imagedestroy($jpegImage);
            throw new Exception("Error saving image.");
        }

        imagedestroy($jpegImage);

        $savedImage = $targetFilePath;

        
        $password = password_hash(
            $plainPassword,
            PASSWORD_DEFAULT
        );

        // Insert user into database
        $stmt = $conn->prepare(
            "INSERT INTO tbl_users
            (USERNAME, FULLNAME, EMAIL, PASSWORD, USERTYPE_ID, PHOTO)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssis",
            $username,
            $fullname,
            $email,
            $password,
            $userType,
            $imagePath
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();

        $message = "User added successfully.";
    } catch (Throwable $e) {

        
        if ($savedImage !== "" && file_exists($savedImage)) {
            unlink($savedImage);
        }

        $message = "Add failed: " . $e->getMessage();
    }
}



/* =========================
   SEARCH USER
========================= */
if (isset($_POST["btn_search"])) {

    $v = trim($_POST["search_username"]);

    $stmt = $conn->prepare("
        SELECT 
            u.*,
            t.Usertype AS USERTYPE
        FROM tbl_users u
        LEFT JOIN tbl_usertype t
            ON u.USERTYPE_ID = t.ID
        WHERE u.USERNAME = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $v);
    $stmt->execute();

    $found = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$found) {
        $message = "User not found.";
    }
}


/* =========================
   EDIT USER
========================= */
if (isset($_POST["btn_edit"])) {

    $id = (int)($_POST["edit_id"] ?? 0);

    $stmt = $conn->prepare("
        SELECT 
            u.*,
            t.Usertype AS USERTYPE
        FROM tbl_users u
        LEFT JOIN tbl_usertype t
            ON u.USERTYPE_ID = t.ID
        WHERE u.ID = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $found = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$found) {
        $message = "User not found.";
    }
}


/* =========================
   UPDATE USER
========================= */
if (isset($_POST["btn_update"])) {

    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usertype = (int)($_POST['usertype'] ?? 0);
    try {
        $oldStmt = $conn->prepare('SELECT USERNAME, PHOTO FROM tbl_users WHERE ID = ?');
        $oldStmt->bind_param('i', $id);
        $oldStmt->execute();
        $old = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();
        if (!$old) throw new RuntimeException('User not found.');
        $hasUpload = isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] !== UPLOAD_ERR_NO_FILE;
        $photo = $old['PHOTO'];
        if ($hasUpload) {
            $photo = userImageUpload($username); // converts provided file to JPEG
        } elseif ($old['USERNAME'] !== $username && $photo && is_file(__DIR__ . '/' . $photo)) {
            if (!preg_match('/^[a-zA-Z0-9_.-]{1,50}$/', $username)) throw new RuntimeException('Invalid username for image file.');
            $newPath = 'images/users/' . $username . '.JPG';
            if (!rename(__DIR__ . '/' . $photo, __DIR__ . '/' . $newPath)) throw new RuntimeException('Could not rename profile image.');
            $photo = $newPath;
        }
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE tbl_users SET USERNAME=?, FULLNAME=?, EMAIL=?, USERTYPE_ID=?, PHOTO=?, PASSWORD=? WHERE ID=?');
            $stmt->bind_param('sssissi', $username, $fullname, $email, $usertype, $photo, $password, $id);
        } else {
            $stmt = $conn->prepare('UPDATE tbl_users SET USERNAME=?, FULLNAME=?, EMAIL=?, USERTYPE_ID=?, PHOTO=? WHERE ID=?');
            $stmt->bind_param('sssisi', $username, $fullname, $email, $usertype, $photo, $id);
        }
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        $stmt->close();
        if ($hasUpload && $old['PHOTO'] !== $photo) removeUserImage($old['PHOTO']);
        if ((int)($_SESSION['user_id'] ?? 0) === $id) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['photo'] = $photo;
        }
        $message = 'User successfully updated.';
    } catch (Throwable $e) {
        $message = 'Update failed: ' . $e->getMessage();
    }
}


/* =========================
   DELETE USER
========================= */
if (isset($_POST["btn_delete"])) {

    $id = (int)$_POST["id"];

    $imageStmt = $conn->prepare('SELECT PHOTO FROM tbl_users WHERE ID=?');
    $imageStmt->bind_param('i', $id);
    $imageStmt->execute();
    $deletedUser = $imageStmt->get_result()->fetch_assoc();
    $imageStmt->close();
    $stmt = $conn->prepare("
        DELETE FROM tbl_users
        WHERE ID = ?
    ");

    $stmt->bind_param("i", $id);

    $deleted = $stmt->execute();
    if ($deleted) removeUserImage($deletedUser['PHOTO'] ?? null);
    $message = $deleted ? "User successfully deleted." : "Delete failed: " . $stmt->error;

    $stmt->close();
}


/* =========================
   GET USERTYPES
========================= */
$types = $conn->query("
    SELECT *
    FROM tbl_usertype
    ORDER BY ID
");


/* =========================
   GET ALL USERS
========================= */
$rows = $conn->query("
    SELECT
        u.ID,
        u.USERNAME,
        u.FULLNAME,
        u.EMAIL,
        u.PHOTO,
        u.USERTYPE_ID,
        t.Usertype AS USERTYPE
    FROM tbl_users u
    LEFT JOIN tbl_usertype t
        ON u.USERTYPE_ID = t.ID
    ORDER BY u.ID DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Users</title>

    <link rel="stylesheet" href="act2.css">

</head>

<body>

    <?php include "navbar.php"; ?>

    <div class="container">

        <h2>Users</h2>

        <?php if ($message): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <!-- =========================
         ADD USER
    ========================== -->

        <div class="card">

            <h3>Add User</h3>

            <form method="POST" enctype="multipart/form-data" class="grid">

                <div>

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        required>

                </div>


                <div>

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="fullname"
                        required>

                </div>


                <div>

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        required>

                </div>


                <div>

                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        required>

                </div>


                <div>

                    <label>Usertype</label>

                    <select name="usertype" required>

                        <option value="">
                            Select Usertype
                        </option>

                        <?php while ($t = $types->fetch_assoc()): ?>

                            <option value="<?php echo (int)$t["ID"]; ?>">

                                <?php
                                echo htmlspecialchars($t["Usertype"]);
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div>
                    <label>Profile Image (JPG, PNG, GIF; max 5 MB)</label>
                    <input type="file" name="imageUpload" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" required>
                </div>
                <div class="full">

                    <button name="btn_add">

                        Add User

                    </button>

                </div>

            </form>

        </div>


        <!-- =========================
         SEARCH USER
    ========================== -->

        <div class="card">

            <h3>Search User</h3>

            <form method="POST" class="search">

                <input
                    type="text"
                    name="search_username"
                    placeholder="Enter username"
                    required>

                <button name="btn_search">

                    Search

                </button>

            </form>

        </div>


        <!-- =========================
         EDIT USER
    ========================== -->

        <?php if ($found): ?>

            <?php

            $types2 = $conn->query("
            SELECT *
            FROM tbl_usertype
            ORDER BY ID
        ");

            ?>

            <form method="POST" enctype="multipart/form-data" class="card">

                <h3>Edit User</h3>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo (int)$found["ID"]; ?>">


                <div class="grid">

                    <div>

                        <label>Username</label>

                        <input
                            type="text"
                            name="username"
                            value="<?php echo htmlspecialchars($found["USERNAME"]); ?>"
                            required>

                    </div>


                    <div>

                        <label>Full Name</label>

                        <input
                            type="text"
                            name="fullname"
                            value="<?php echo htmlspecialchars($found["FULLNAME"]); ?>"
                            required>

                    </div>


                    <div>

                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($found["EMAIL"]); ?>"
                            required>

                    </div>


                    <div>

                        <label>
                            New Password
                            <span class="hint">
                                (leave blank to keep current)
                            </span>
                        </label>

                        <input
                            type="password"
                            name="password">

                    </div>


                    <div>

                        <label>Usertype</label>

                        <select
                            name="usertype"
                            required>

                            <?php while ($t = $types2->fetch_assoc()): ?>

                                <option
                                    value="<?php echo (int)$t["ID"]; ?>"
                                    <?php
                                    echo (
                                        (int)$found["USERTYPE_ID"]
                                        ===
                                        (int)$t["ID"]
                                    )
                                        ? "selected"
                                        : "";
                                    ?>>

                                    <?php
                                    echo htmlspecialchars($t["Usertype"]);
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </div>


                <div>
                    <label>Replace Profile Image (optional)</label>
                    <?php if (!empty($found['PHOTO'])): ?>
                        <img class="user-photo-preview" src="<?php echo htmlspecialchars($found['PHOTO'], ENT_QUOTES); ?>" alt="Current profile photo">
                    <?php endif; ?>
                    <input type="file" name="imageUpload" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
                </div>
                <div class="actions">

                    <button name="btn_update">

                        Update User

                    </button>


                    <button
                        name="btn_delete"
                        class="danger"
                        onclick="return confirm('Delete this user?')">

                        Delete User

                    </button>

                </div>

            </form>

        <?php endif; ?>


        <!-- =========================
         ALL USERS
    ========================== -->

        <div class="card">

            <h3>All Users</h3>

            <table>

                <tr>

                    <th>ID</th>

                    <th>Photo</th>
                    <th>Username</th>

                    <th>Full Name</th>

                    <th>Email</th>

                    <th>Usertype</th>

                    <th>Action</th>

                </tr>


                <?php while ($row = $rows->fetch_assoc()): ?>

                    <tr>

                        <td><?php echo (int)$row["ID"]; ?></td>
                        <td><?php if (!empty($row['PHOTO'])): ?><img class="user-photo-preview" src="<?php echo htmlspecialchars($row['PHOTO'], ENT_QUOTES); ?>" alt="Profile photo"><?php else: ?>—<?php endif; ?></td>


                        <td>
                            <?php
                            echo htmlspecialchars($row["USERNAME"]);
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars($row["FULLNAME"]);
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars($row["EMAIL"]);
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["USERTYPE"] ?? "Not Assigned"
                            );
                            ?>
                        </td>


                        <td>

                            <div class="actions">

                                <!-- EDIT -->

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="edit_id"
                                        value="<?php echo (int)$row["ID"]; ?>">

                                    <button
                                        name="btn_edit"
                                        class="secondary">

                                        Edit

                                    </button>

                                </form>


                                <!-- DELETE -->

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Delete this user?')">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo (int)$row["ID"]; ?>">

                                    <button
                                        name="btn_delete"
                                        class="danger">

                                        Delete

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

    </div>

</body>

</html>