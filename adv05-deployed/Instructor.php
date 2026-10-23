<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

$message = "";
$found = null;
$uploadDir = __DIR__ . "/uploads/instructors/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

function saveInstructorPhoto(string $field, string $uploadDir): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) return null;
    $filename = uniqid('instructor_', true) . '.' . $allowed[$mime];
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename)) return $filename;
    return null;
}

if (isset($_POST['btn_add'])) {
    $salutation = trim($_POST['salutation'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $photo = saveInstructorPhoto('photo', $uploadDir);

    $stmt = $conn->prepare("INSERT INTO tbl_instructors (SALUTATION, FIRSTNAME, MIDDLENAME, LASTNAME, PHOTO) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $salutation, $firstname, $middlename, $lastname, $photo);
    $message = $stmt->execute() ? 'Instructor added successfully.' : 'Add failed: ' . $stmt->error;
    $stmt->close();
}

if (isset($_POST['btn_search'])) {
    $v = trim($_POST['search_lastname'] ?? '');
    $stmt = $conn->prepare("SELECT * FROM tbl_instructors WHERE LASTNAME = ? LIMIT 1");
    $stmt->bind_param('s', $v);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = 'Instructor not found.';
}

if (isset($_POST['btn_edit'])) {
    $id = (int)($_POST['edit_id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM tbl_instructors WHERE ID = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = 'Instructor not found.';
}

if (isset($_POST['btn_update'])) {
    $id = (int)($_POST['id'] ?? 0);
    $salutation = trim($_POST['salutation'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $oldPhoto = $_POST['old_photo'] ?? '';
    $newPhoto = saveInstructorPhoto('photo', $uploadDir);
    $photo = $newPhoto ?: ($oldPhoto !== '' ? $oldPhoto : null);

    $stmt = $conn->prepare("UPDATE tbl_instructors SET SALUTATION=?, FIRSTNAME=?, MIDDLENAME=?, LASTNAME=?, PHOTO=? WHERE ID=?");
    $stmt->bind_param('sssssi', $salutation, $firstname, $middlename, $lastname, $photo, $id);
    $message = $stmt->execute() ? 'Instructor successfully updated.' : 'Update failed: ' . $stmt->error;
    $stmt->close();
}

if (isset($_POST['btn_delete'])) {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("SELECT PHOTO FROM tbl_instructors WHERE ID=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM tbl_instructors WHERE ID=?");
    $stmt->bind_param('i', $id);
    $message = $stmt->execute() ? 'Instructor successfully deleted.' : 'Delete failed: ' . $stmt->error;
    $stmt->close();
    if ($old && !empty($old['PHOTO']) && is_file($uploadDir . $old['PHOTO'])) @unlink($uploadDir . $old['PHOTO']);
}

$rows = $conn->query("SELECT * FROM tbl_instructors ORDER BY ID DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor</title>
    <link rel="stylesheet" href="act2.css">
    <style>
        .instructor-photo {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .photo-preview {
            margin: 8px 0;
        }

        .photo-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="container">
        <h2>Instructor</h2>
        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

        <div class="card">
            <h3>Add Instructor</h3>
            <form method="POST" enctype="multipart/form-data" class="grid">
                <div><label>Salutation</label><select name="salutation" required>
                        <option value="">Select Salutation</option>
                        <option>Mr.</option>
                        <option>Ms.</option>
                        <option>Mrs.</option>
                        <option>Dr.</option>
                        <option>Prof.</option>
                    </select></div>
                <div><label>First Name</label><input name="firstname" required></div>
                <div><label>Middle Name</label><input name="middlename"></div>
                <div><label>Last Name</label><input name="lastname" required></div>
                <div><label>Photo</label><input type="file" name="photo" accept="image/*"></div>
                <div class="full"><button name="btn_add">Add Instructor</button></div>
            </form>
        </div>

        <div class="card">
            <h3>Search Instructor</h3>
            <form method="POST" class="search"><input name="search_lastname" placeholder="Enter last name" required><button name="btn_search">Search</button></form>
        </div>

        <?php if ($found): ?>
            <form method="POST" enctype="multipart/form-data" class="card">
                <h3>Edit Instructor</h3>
                <input type="hidden" name="id" value="<?php echo (int)$found['ID']; ?>">
                <input type="hidden" name="old_photo" value="<?php echo htmlspecialchars($found['PHOTO'] ?? '', ENT_QUOTES); ?>">
                <div class="grid">
                    <div><label>ID</label><input value="<?php echo (int)$found['ID']; ?>" disabled></div>
                    <div><label>Salutation</label><input name="salutation" value="<?php echo htmlspecialchars($found['SALUTATION']); ?>" required></div>
                    <div><label>First Name</label><input name="firstname" value="<?php echo htmlspecialchars($found['FIRSTNAME']); ?>" required></div>
                    <div><label>Middle Name</label><input name="middlename" value="<?php echo htmlspecialchars($found['MIDDLENAME']); ?>"></div>
                    <div><label>Last Name</label><input name="lastname" value="<?php echo htmlspecialchars($found['LASTNAME']); ?>" required></div>
                    <div><label>New Photo</label><input type="file" name="photo" accept="image/*"></div>
                </div>
                <?php if (!empty($found['PHOTO'])): ?><div class="photo-preview"><img src="uploads/instructors/<?php echo rawurlencode($found['PHOTO']); ?>" alt="Instructor photo"></div><?php endif; ?>
                <div class="actions"><button name="btn_update">Update Instructor</button><button name="btn_delete" class="danger" onclick="return confirm('Delete this instructor?')">Delete Instructor</button></div>
            </form>
        <?php endif; ?>

        <div class="card">
            <h3>All Instructors</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Salutation</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['ID']; ?></td>
                        <td><?php if (!empty($row['PHOTO'])): ?><img class="instructor-photo" src="uploads/instructors/<?php echo rawurlencode($row['PHOTO']); ?>" alt="Photo"><?php else: ?>No photo<?php endif; ?></td>
                        <td><?php echo htmlspecialchars($row['SALUTATION']); ?></td>
                        <td><?php echo htmlspecialchars($row['FIRSTNAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['MIDDLENAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['LASTNAME']); ?></td>
                        <td>
                            <div class="actions">
                                <form method="POST"><input type="hidden" name="edit_id" value="<?php echo (int)$row['ID']; ?>"><button name="btn_edit" class="secondary">Edit</button></form>
                                <form method="POST" onsubmit="return confirm('Delete this instructor?')"><input type="hidden" name="id" value="<?php echo (int)$row['ID']; ?>"><button name="btn_delete" class="danger">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>