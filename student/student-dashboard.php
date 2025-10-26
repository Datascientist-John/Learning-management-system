<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db_config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Get student statistics
$enrolled_courses_query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ?";
$stmt = $conn->prepare($enrolled_courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_result = $stmt->get_result();
$enrolled_courses = $enrolled_result->fetch_assoc()['total'];

$completed_courses_query = "SELECT COUNT(*) as total FROM enrollments WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($completed_courses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_courses = $completed_result->fetch_assoc()['total'];

$pending_assignments_query = "SELECT COUNT(DISTINCT a.assignment_id) as total 
                               FROM assignments a 
                               INNER JOIN enrollments e ON a.course_id = e.course_id 
                               LEFT JOIN grades g ON a.assignment_id = g.assignment_id AND g.user_id = ?
                               WHERE e.user_id = ? AND g.grade_id IS NULL";
$stmt = $conn->prepare($pending_assignments_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$pending_result = $stmt->get_result();
$pending_assignments = $pending_result->fetch_assoc()['total'];

// Get recent enrollments
$recent_enrollments_query = "SELECT c.course_name, e.enrolled_at, e.status 
                              FROM enrollments e 
                              INNER JOIN courses c ON e.course_id = c.course_id 
                              WHERE e.user_id = ? 
                              ORDER BY e.enrolled_at DESC LIMIT 5";
$stmt = $conn->prepare($recent_enrollments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_enrollments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card.enrolled { border-color: #0d6efd; }
        .stat-card.completed { border-color: #198754; }
        .stat-card.pending { border-color: #ffc107; }
        
        .quick-links {
            margin-top: 30px;
        }
        .quick-link-card {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .quick-link-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap"></i> LMS Student Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="student-dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-courses.php">
                            <i class="fas fa-book"></i> My Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse-courses.php">
                            <i class="fas fa-search"></i> Browse Courses
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($first_name); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Message -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> 
            <strong>Welcome back, <?php echo htmlspecialchars($first_name); ?>!</strong> 
            Ready to continue your learning journey today?
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <h2 class="mb-4">
            <i class="fas fa-tachometer-alt"></i> My Dashboard
        </h2>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card enrolled">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Enrolled Courses</h6>
                                <h2 class="mb-0"><?php echo $enrolled_courses; ?></h2>
                            </div>
                            <div class="fs-1 text-primary">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stat-card completed">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Completed Courses</h6>
                                <h2 class="mb-0"><?php echo $completed_courses; ?></h2>
                            </div>
                            <div class="fs-1 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stat-card pending">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending Assignments</h6>
                                <h2 class="mb-0"><?php echo $pending_assignments; ?></h2>
                            </div>
                            <div class="fs-1 text-warning">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row quick-links mb-4">
            <div class="col-md-3 mb-3">
                <a href="my-courses.php" class="quick-link-card card bg-primary text-white">
                    <i class="fas fa-book-reader"></i>
                    <h5>My Courses</h5>
                    <p class="mb-0">View enrolled courses</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="browse-courses.php" class="quick-link-card card bg-success text-white">
                    <i class="fas fa-search-plus"></i>
                    <h5>Browse Courses</h5>
                    <p class="mb-0">Find new courses</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="my-assignments.php" class="quick-link-card card bg-warning text-white">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>Assignments</h5>
                    <p class="mb-0">View assignments</p>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="my-grades.php" class="quick-link-card card bg-info text-white">
                    <i class="fas fa-chart-line"></i>
                    <h5>My Grades</h5>
                    <p class="mb-0">Check your progress</p>
                </a>
            </div>
        </div>

        <!-- Recent Enrollments -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Recent Enrollments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_enrollments && mysqli_num_rows($recent_enrollments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Course Name</th>
                                            <th>Enrolled Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($enrollment = mysqli_fetch_assoc($recent_enrollments)): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($enrollment['course_name']); ?></strong>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    $status_icon = '';
                                                    switch ($enrollment['status']) {
                                                        case 'active':
                                                            $status_class = 'bg-primary';
                                                            $status_icon = 'fa-play';
                                                            break;
                                                        case 'completed':
                                                            $status_class = 'bg-success';
                                                            $status_icon = 'fa-check';
                                                            break;
                                                        case 'dropped':
                                                            $status_class = 'bg-danger';
                                                            $status_icon = 'fa-times';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <i class="fas <?php echo $status_icon; ?>"></i>
                                                        <?php echo ucfirst($enrollment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($enrollment['status'] == 'active'): ?>
                                                        <a href="course-view.php" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-arrow-right"></i> Continue
                                                        </a>
                                                    <?php elseif ($enrollment['status'] == 'completed'): ?>
                                                        <a href="certificate.php" class="btn btn-sm btn-success">
                                                            <i class="fas fa-certificate"></i> Certificate
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> No enrollments yet. 
                                <a href="browse-courses.php" class="alert-link">Browse courses</a> to get started!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>