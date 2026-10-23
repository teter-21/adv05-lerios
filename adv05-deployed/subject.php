<?php
require_once __DIR__ . "/auth.php";

require_once __DIR__ . "/db.php";

$message = "";

if (isset($_POST['submit'])) {

    $course = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];
    $term = $_POST['term'];
    $code = $_POST['code'];
    $title = $_POST['title'];
    $lecture = $_POST['lecture'];
    $laboratory = $_POST['laboratory'];
    $credit = $_POST['credit'];
    $grade = $_POST['grade'];
    $pre_requisite = $_POST['pre_requisite'];

    /*
        Convert empty grade and prerequisite
        to NULL because these columns allow NULL.
    */

    $grade = ($grade === "") ? null : $grade;
    $pre_requisite = ($pre_requisite === "") ? null : $pre_requisite;

    $sql = "INSERT INTO tbl_subjects
            (
                COURSE,
                YEARLEVEL,
                TERM,
                CODE,
                TITLE,
                LECTURE,
                LABORATORY,
                CREDIT,
                GRADE,
                PRE_REQUISITE
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssssddds",
        $course,
        $yearlevel,
        $term,
        $code,
        $title,
        $lecture,
        $laboratory,
        $credit,
        $grade,
        $pre_requisite
    );

    if ($stmt->execute()) {
        $message = "Added to tbl_subjects.";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Subject</title>
    <link rel="stylesheet" href="act2.css">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Add Subject</h2>

        <?php if ($message != "") { ?>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php } ?>

        <form method="POST">

            <!-- COURSE -->
            <div class="subject-card">

                <label>Course</label>

                <select name="course" required>

                    <option value="">Select Course</option>

                    <option value="BSIS">
                        Bachelor of Science in Information Systems (BSIS)
                    </option>

                    <option value="BSCPE">
                        Bachelor of Science in Computer Engineering (BSCPE)
                    </option>

                    <option value="BSA">
                        Bachelor of Science in Accountancy (BSA)
                    </option>

                    <option value="BSCrim">
                        Bachelor of Science in Criminology (BSCrim)
                    </option>

                    <option value="BSTM">
                        Bachelor of Science in Tourism Management (BSTM)
                    </option>

                    <option value="BSP">
                        Bachelor of Science in Psychology (BSP)
                    </option>

                    <option value="BSE">
                        Bachelor of Science in Entrepreneurship (BSE)
                    </option>

                    <option value="BTVTEd">
                        Bachelor in Technical-Vocational Teacher Education (BTVTEd)
                    </option>

                </select>

            </div>


            <!-- YEAR LEVEL -->
            <div class="subject-card">

                <label>Year Level</label>

                <select name="yearlevel" required>

                    <option value="">Select Year Level</option>

                    <option value="1">
                        First Year
                    </option>

                    <option value="2">
                        Second Year
                    </option>

                    <option value="3">
                        Third Year
                    </option>

                    <option value="4">
                        Fourth Year
                    </option>

                </select>

            </div>


            <!-- TERM -->
            <div class="subject-card">

                <label>Term</label>

                <select name="term" required>

                    <option value="">Select Term</option>

                    <option value="1st Sem">
                        First Semester
                    </option>

                    <option value="2nd Sem">
                        Second Semester
                    </option>

                    <option value="Tutorial Class">
                        Tutorial
                    </option>

                    <option value="Summer Class">
                        Summer Class
                    </option>


                </select>

            </div>


            <!-- SUBJECT INFORMATION -->
            <div class="subject-card full-width">

                <label>Subject Code</label>

                <input
                    type="text"
                    name="code"
                    placeholder="Enter subject code"
                    required>


                <label>Subject Title</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Enter subject title"
                    required>


                <label>Lecture</label>

                <input
                    type="text"
                    name="lecture"
                    placeholder="Enter lecture units"
                    required>


                <label>Laboratory</label>

                <input
                    type="text"
                    name="laboratory"
                    placeholder="Enter laboratory units"
                    required>


                <label>Credit</label>

                <input
                    type="text"
                    name="credit"
                    placeholder="Enter credit"
                    required>


                <label>Grade</label>

                <input
                    type="text"
                    name="grade"
                    placeholder="Enter grade">


                <label>Pre-requisite</label>

                <input
                    type="text"
                    name="pre_requisite"
                    placeholder="Enter pre-requisite">

            </div>


            <button type="submit" name="submit">
                Add Subject
            </button>

        </form>
    </div>

</body>

</html>