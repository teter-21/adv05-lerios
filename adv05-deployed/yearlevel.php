<?php
require_once __DIR__ . "/auth.php";
include "db.php";
$message = "";
$found = null;

if (isset($_POST["btn_add"])) {
    $value = trim($_POST["value"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $stmt = $conn->prepare("INSERT INTO tbl_yearlevel (YEARLEVEL, DESCRIPTION) VALUES (?, ?)");
    $stmt->bind_param("ss", $value, $description);
    $message = $stmt->execute() ? "Year Level added successfully." : "Add failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_search"])) {
    $value = trim($_POST["search_value"] ?? "");
    $stmt = $conn->prepare("SELECT * FROM tbl_yearlevel WHERE YEARLEVEL = ? LIMIT 1");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Year Level not found.";
}
if (isset($_POST["btn_edit"])) {
    $id = (int)($_POST["edit_id"] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM tbl_yearlevel WHERE ID = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Year Level not found.";
}
if (isset($_POST["btn_update"])) {
    $id = (int)($_POST["id"] ?? 0);
    $value = trim($_POST["value"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $stmt = $conn->prepare("UPDATE tbl_yearlevel SET YEARLEVEL=?, DESCRIPTION=? WHERE ID=?");
    $stmt->bind_param("ssi", $value, $description, $id);
    $message = $stmt->execute() ? "Year Level successfully updated." : "Update failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_delete"])) {
    $id = (int)($_POST["id"] ?? 0);
    $stmt = $conn->prepare("DELETE FROM tbl_yearlevel WHERE ID=?");
    $stmt->bind_param("i", $id);
    $message = $stmt->execute() ? "Year Level successfully deleted." : "Delete failed: " . $stmt->error;
    $stmt->close();
}
$rows = $conn->query("SELECT * FROM tbl_yearlevel ORDER BY ID DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Year Level</title>
    <link rel="stylesheet" href="act2.css">
</head>

<body>
    <?php include "navbar.php"; ?><div class="container">
        <h2>Year Level</h2>
        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

        <div class="card">
            <h3>Add Year Level</h3>
            <form method="POST" class="grid">
                <div><label>Year Level</label><input name="value" required></div>
                <div><label>Description</label><input name="description" required></div>
                <div class="full"><button name="btn_add">Add Year Level</button></div>
            </form>
        </div>

        <div class="card">
            <h3>Search Year Level</h3>
            <form method="POST" class="search"><input name="search_value" placeholder="Enter Year Level" required><button name="btn_search">Search</button></form>
        </div>

        <?php if ($found): ?><form method="POST" class="card">
                <h3>Edit Record</h3>
                <input type="hidden" name="id" value="<?php echo (int)$found["ID"]; ?>">
                <label>ID</label><input value="<?php echo (int)$found["ID"]; ?>" disabled>
                <label>Year Level</label><input name="value" value="<?php echo htmlspecialchars($found["YEARLEVEL"]); ?>" required>
                <label>Description</label><input name="description" value="<?php echo htmlspecialchars($found["DESCRIPTION"]); ?>" required>
                <div class="actions"><button name="btn_update">Update</button><button name="btn_delete" class="danger" onclick="return confirm('Delete this record?')">Delete</button></div>
            </form><?php endif; ?>

        <div class="card">
            <h3>All Records</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Year Level</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row["ID"]; ?></td>
                        <td><?php echo htmlspecialchars($row["YEARLEVEL"]); ?></td>
                        <td><?php echo htmlspecialchars($row["DESCRIPTION"]); ?></td>
                        <td>
                            <div class="actions">
                                <form method="POST"><input type="hidden" name="edit_id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_edit" class="secondary">Edit</button></form>
                                <form method="POST" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_delete" class="danger">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>