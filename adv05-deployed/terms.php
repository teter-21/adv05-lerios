<?php
require_once __DIR__ . "/auth.php";

require_once __DIR__ . "/db.php";

$terms = $conn->query("SELECT TERM, DESCRIPTION FROM tbl_term");

$message = "";

if (isset($_POST['submit'])) {

    $term = $_POST['term'];
    $description = $_POST['description'];

    $sql = "INSERT INTO tbl_term (TERM, DESCRIPTION)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $term, $description);

    if ($stmt->execute()) {
        $message = "Added to tbl_term.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Term</title>
    <link rel="stylesheet" href="act2.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Add Term</h2>

        <?php if ($message != "") { ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <form method="POST">

            <div class="card">

                <label>Term</label>

                <select name="term" id="term" required>

                    <option value="">Select Term</option>

                    <?php while ($row = $terms->fetch_assoc()) { ?>

                        <option
                            value="<?php echo htmlspecialchars($row['TERM']); ?>"
                            data-description="<?php echo htmlspecialchars($row['DESCRIPTION']); ?>">
                            <?php echo htmlspecialchars($row['DESCRIPTION']); ?>
                        </option>

                    <?php } ?>

                </select>

                <input type="hidden" name="description" id="description">

            </div>

            <button type="submit" name="submit">
                Add Term
            </button>

        </form>
    </div>

    <script>
        const termSelect = document.getElementById("term");
        const descriptionInput = document.getElementById("description");

        termSelect.addEventListener("change", function() {

            const selectedOption =
                this.options[this.selectedIndex];

            descriptionInput.value =
                selectedOption.getAttribute("data-description");

        });
    </script>

</body>

</html>