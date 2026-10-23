<?php
require_once __DIR__ . "/auth.php";
$currentPage = basename($_SERVER['PHP_SELF']);
$displayName = $_SESSION['fullname'] ?? ($_SESSION['username'] ?? 'User');
$userType = $_SESSION['usertype'] ?? 'User';

$allLinks = [
    'course.php'      => 'Course',
    'yearlevel.php'   => 'Year Level',
    'term.php'        => 'Term',
    'section.php'     => 'Section',
    'schoolyear.php'  => 'School Year',
    'subjects.php'    => 'Subjects',
    'Instructor.php' => 'Instructor',
    'users.php'       => 'Users',
    'usertype.php'    => 'User Type'
];

$links = [];
foreach ($allLinks as $href => $label) {
    if (pageAllowed($href)) {
        $links[$href] = $label;
    }
}
?>

<nav class="navbar">
    <div class="nav-brand">
        <span>ACT 6</span>
        <small>Student Registration &amp; Academic Records</small>
    </div>

    <div class="nav-user">
        <?php if (!empty($_SESSION['photo']) && is_file(__DIR__ . '/' . $_SESSION['photo'])): ?>
            <img class="nav-avatar" src="<?php echo htmlspecialchars($_SESSION['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Profile photo">
        <?php else: ?>
            <span class="nav-avatar-placeholder" aria-hidden="true">👤</span>
        <?php endif; ?>
        <span>Signed in as <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>
            (<?php echo htmlspecialchars($userType, ENT_QUOTES, 'UTF-8'); ?>)</span>
        <a class="logout-link" href="logout.php">Log Out</a>
    </div>

    <div class="nav-links">
        <?php foreach ($links as $href => $label): ?>
            <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentPage === $href ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>