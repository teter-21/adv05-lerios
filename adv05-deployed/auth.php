<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

/*
 * Page access by user type.
 * Admin = all pages.
 * Registrar = Subjects, Term, Instructor, School Year.
 * Dean = Subjects, Section, Instructor, Year Level.
 * Instructor = Subjects, Section.
 * Student = Subjects only.
 */
$PAGE_ACCESS = [
    'Admin' => [
        'course.php', 'yearlevel.php', 'term.php', 'terms.php', 'section.php',
        'schoolyear.php', 'subjects.php', 'subject.php', 'users.php', 'usertype.php',
        'Instructor.php', 'instructors.php', 'instructor.php'
    ],
    'Registrar' => [
        'term.php', 'terms.php', 'schoolyear.php', 'subjects.php',
        'Instructor.php', 'instructors.php', 'instructor.php'
    ],
    'Dean' => [
        'yearlevel.php', 'section.php', 'subjects.php',
        'Instructor.php', 'instructors.php', 'instructor.php'
    ],
    'Instructor' => [
        'subjects.php', 'section.php'
    ],
    'Student' => [
        'subjects.php'
    ]
];

function currentUserType(): string {
    return $_SESSION['usertype'] ?? '';
}

function pageAllowed(string $page): bool {
    global $PAGE_ACCESS;
    $type = currentUserType();
    if ($type === 'Admin') return true;
    return isset($PAGE_ACCESS[$type]) && in_array($page, $PAGE_ACCESS[$type], true);
}

$currentPage = basename($_SERVER['PHP_SELF']);
if (!pageAllowed($currentPage)) {
    http_response_code(403);
    $home = 'subjects.php';
    if (currentUserType() === 'Admin') $home = 'course.php';
    elseif (currentUserType() === 'Registrar') $home = 'subjects.php';
    elseif (currentUserType() === 'Dean') $home = 'subjects.php';
    elseif (currentUserType() === 'Instructor') $home = 'subjects.php';
    elseif (currentUserType() === 'Student') $home = 'subjects.php';

    header('Location: ' . $home);
    exit();
}
?>
