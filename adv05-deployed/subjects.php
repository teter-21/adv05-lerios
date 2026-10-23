<?php
require_once __DIR__ . "/auth.php";
include "db.php";
$message = "";
$found = null;
if (isset($_POST["btn_add"])) {
    $course = trim($_POST["course"]);
    $yearlevel = trim($_POST["yearlevel"]);
    $term = trim($_POST["term"]);
    $code = trim($_POST["code"]);
    $title = trim($_POST["title"]);
    $lecture = (float)$_POST["lecture"];
    $laboratory = (float)$_POST["laboratory"];
    $credit = (float)$_POST["credit"];
    $grade = $_POST["grade"] === "" ? null : (float)$_POST["grade"];
    $pre = $_POST["pre_requisite"] === "" ? null : trim($_POST["pre_requisite"]);
    $stmt = $conn->prepare("INSERT INTO tbl_subjects (COURSE,YEARLEVEL,TERM,CODE,TITLE,LECTURE,LABORATORY,CREDIT,GRADE,PRE_REQUISITE) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssddds", $course, $yearlevel, $term, $code, $title, $lecture, $laboratory, $credit, $grade, $pre);
    $message = $stmt->execute() ? "Subject added successfully." : "Add failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_search"])) {
    $v = trim($_POST["search_code"]);
    $stmt = $conn->prepare("SELECT * FROM tbl_subjects WHERE CODE=? LIMIT 1");
    $stmt->bind_param("s", $v);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Subject not found.";
}
if (isset($_POST["btn_edit"])) {
    $id = (int)($_POST["edit_id"] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM tbl_subjects WHERE ID=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) $message = "Subject not found.";
}
if (isset($_POST["btn_update"])) {
    $id = (int)$_POST["id"];
    $course = trim($_POST["course"]);
    $yearlevel = trim($_POST["yearlevel"]);
    $term = trim($_POST["term"]);
    $code = trim($_POST["code"]);
    $title = trim($_POST["title"]);
    $lecture = (float)$_POST["lecture"];
    $laboratory = (float)$_POST["laboratory"];
    $credit = (float)$_POST["credit"];
    $grade = $_POST["grade"] === "" ? null : (float)$_POST["grade"];
    $pre = $_POST["pre_requisite"] === "" ? null : trim($_POST["pre_requisite"]);
    $stmt = $conn->prepare("UPDATE tbl_subjects SET COURSE=?,YEARLEVEL=?,TERM=?,CODE=?,TITLE=?,LECTURE=?,LABORATORY=?,CREDIT=?,GRADE=?,PRE_REQUISITE=? WHERE ID=?");
    $stmt->bind_param("sssssddddsi", $course, $yearlevel, $term, $code, $title, $lecture, $laboratory, $credit, $grade, $pre, $id);
    $message = $stmt->execute() ? "Subject successfully updated." : "Update failed: " . $stmt->error;
    $stmt->close();
}
if (isset($_POST["btn_delete"])) {
    $id = (int)$_POST["id"];
    $stmt = $conn->prepare("DELETE FROM tbl_subjects WHERE ID=?");
    $stmt->bind_param("i", $id);
    $message = $stmt->execute() ? "Subject successfully deleted." : "Delete failed: " . $stmt->error;
    $stmt->close();
}
$rows = $conn->query("SELECT * FROM tbl_subjects ORDER BY ID DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Subjects</title>
    <link rel="stylesheet" href="act2.css">
</head>

<body>
    <?php include "navbar.php"; ?><div class="container">
        <h2>Subjects</h2>
        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="card">
            <h3>Add Subject</h3>
            <form method="POST" class="grid">
                <div><label>Course</label><input name="course" required></div>
                <div><label>Year Level</label><input name="yearlevel" required></div>
                <div><label>Term</label><input name="term" required></div>
                <div><label>Subject Code</label><input name="code" required></div>
                <div><label>Subject Title</label><input name="title" required></div>
                <div><label>Lecture</label><input type="number" step="0.01" name="lecture" required></div>
                <div><label>Laboratory</label><input type="number" step="0.01" name="laboratory" required></div>
                <div><label>Credit</label><input type="number" step="0.01" name="credit" required></div>
                <div><label>Grade</label><input type="number" step="0.01" name="grade"></div>
                <div><label>Pre-requisite</label><input name="pre_requisite"></div>
                <div class="full"><button name="btn_add">Add Subject</button></div>
            </form>
        </div>
        <div class="card">
            <h3>Search Subject</h3>
            <form method="POST" class="search"><input name="search_code" placeholder="Enter subject code" required><button name="btn_search">Search</button></form>
        </div>
        <?php if ($found): ?><form method="POST" class="card">
                <h3>Edit Subject</h3><input type="hidden" name="id" value="<?php echo (int)$found["ID"]; ?>">
                <div class="grid"><?php foreach (["course" => "COURSE", "yearlevel" => "YEARLEVEL", "term" => "TERM", "code" => "CODE", "title" => "TITLE", "lecture" => "LECTURE", "laboratory" => "LABORATORY", "credit" => "CREDIT", "grade" => "GRADE", "pre_requisite" => "PRE_REQUISITE"] as $field => $col): ?><div><label><?php echo htmlspecialchars($col); ?></label><input name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($found[$col] ?? ""); ?>" <?php echo in_array($field, ["lecture", "laboratory", "credit", "grade"]) ? 'type="number" step="0.01"' : ''; ?>></div><?php endforeach; ?></div>
                <div class="actions"><button name="btn_update">Update Subject</button><button name="btn_delete" class="danger" onclick="return confirm('Delete this subject?')">Delete Subject</button></div>
            </form><?php endif; ?>
        <div class="card">
            <h3>All Subjects</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Term</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $rows->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row["ID"]; ?></td>
                        <td><?php echo htmlspecialchars($row["CODE"]); ?></td>
                        <td><?php echo htmlspecialchars($row["TITLE"]); ?></td>
                        <td><?php echo htmlspecialchars($row["COURSE"]); ?></td>
                        <td><?php echo htmlspecialchars($row["YEARLEVEL"]); ?></td>
                        <td><?php echo htmlspecialchars($row["TERM"]); ?></td>
                        <td>
                            <div class="actions">
                                <form method="POST"><input type="hidden" name="edit_id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_edit" class="secondary">Edit</button></form>
                                <form method="POST" onsubmit="return confirm('Delete this subject?')"><input type="hidden" name="id" value="<?php echo (int)$row["ID"]; ?>"><button name="btn_delete" class="danger">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>