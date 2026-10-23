<?php
require_once __DIR__ . "/auth.php";
include "db.php";
$message = "";
$found = null;
if (isset($_POST["btn_add"])) {
    $v = trim($_POST["usertype"] ?? "");
    $stmt = $conn->prepare("INSERT INTO tbl_usertype (Usertype) VALUES (?)");
    $stmt->bind_param("s", $v);
    $message = $stmt->execute() ? "Usertype added successfully." : "Add failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_search"])) {
    $v = trim($_POST["search_usertype"] ?? "");
    $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE USERTYPE=? LIMIT 1");
    $stmt->bind_param("s", $v);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Usertype not found.";
}
if (isset($_POST["btn_edit"])) {
    $id = (int)($_POST["edit_id"] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE ID=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Usertype not found.";
}
if (isset($_POST["btn_update"])) {
    $id = (int)$_POST["id"];
    $v = trim($_POST["usertype"] ?? "");
    $stmt = $conn->prepare("UPDATE tbl_usertype SET USERTYPE=? WHERE ID=?");
    $stmt->bind_param("si", $v, $id);
    $message = $stmt->execute() ? "Usertype successfully updated." : "Update failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_delete"])) {
    $id = (int)$_POST["id"];
    $stmt = $conn->prepare("DELETE FROM tbl_usertype WHERE ID=?");
    $stmt->bind_param("i", $id);
    $message = $stmt->execute() ? "Usertype successfully deleted." : "Delete failed: " . $stmt->error;
    $stmt->close();
}
$rows = $conn->query("SELECT * FROM tbl_usertype ORDER BY ID");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Usertype</title>
    <link rel="stylesheet" href="act2.css">
</head>

<body>
    <?php include "navbar.php"; ?><div class="container">
        <h2>Usertype</h2>
        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="card">
            <h3>Add Usertype</h3>
            <form method="POST"><label>Usertype</label><select name="usertype" required>
                    <option value="">Select Usertype</option>
                    <option>Admin</option>
                    <option>Registrar</option>
                    <option>Program Head</option>
                    <option>Professor</option>
                    <option>Student</option>
                </select>
                <div class="actions"><button name="btn_add">Add Usertype</button></div>
            </form>
        </div>
        <div class="card">
            <h3>Search Usertype</h3>
            <form method="POST" class="search"><input name="search_usertype" placeholder="e.g. Admin" required><button name="btn_search">Search</button></form>
        </div>
        <?php if ($found): ?><form method="POST" class="card">
                <h3>Edit Record</h3><input type="hidden" name="id" value="<?php echo (int)$found["ID"]; ?>"><label>ID</label><input value="<?php echo (int)$found["ID"]; ?>" disabled><label>Usertype</label><select name="usertype" required><?php foreach (["Admin", "Registrar", "Program Head", "Professor", "Student"] as $type): ?><option <?php echo $found["Usertype"] === $type ? "selected" : ""; ?>><?php echo $type; ?></option><?php endforeach; ?></select>
                <div class="actions"><button name="btn_update">Update</button><button name="btn_delete" class="danger" onclick="return confirm('Delete this usertype?')">Delete</button></div>
            </form><?php endif; ?>
        <div class="card">
            <h3>All Usertypes</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Usertype</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row["ID"]; ?></td>
                        <td><?php echo htmlspecialchars($row["Usertype"]); ?></td>
                        <td>
                            <div class="actions">
                                <form method="POST"><input type="hidden" name="edit_id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_edit" class="secondary">Edit</button></form>
                                <form method="POST" onsubmit="return confirm('Delete this usertype?')"><input type="hidden" name="id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_delete" class="danger">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>