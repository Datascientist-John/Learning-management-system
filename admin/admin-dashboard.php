<?php
session_start(); 
require_once '../config/db_config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

// Get system statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$active_students_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student' AND active = 1";
$active_students_result = mysqli_query($conn, $active_students_query);
$active_students = mysqli_fetch_assoc($active_students_result)['total'];

$total_courses_query = "SELECT COUNT(*) as total FROM courses";
$total_courses_result = mysqli_query($conn, $total_courses_query);
$total_courses = mysqli_fetch_assoc($total_courses_result)['total'];

$active_courses_query = "SELECT COUNT(*) as total FROM courses WHERE status = 'active'";
$active_courses_result = mysqli_query($conn, $active_courses_query);
$active_courses = mysqli_fetch_assoc($active_courses_result)['total'];

$total_enrollments_query = "SELECT COUNT(*) as total FROM enrollments";
$total_enrollments_result = mysqli_query($conn, $total_enrollments_query);
$total_enrollments = mysqli_fetch_assoc($total_enrollments_result)['total'];

$pending_assignments_query = "SELECT COUNT(*) as total FROM assignments WHERE due_date >= CURDATE()";
$pending_assignments_result = mysqli_query($conn, $pending_assignments_query);
$pending_assignments = mysqli_fetch_assoc($pending_assignments_result)['total'];

// Get recent activities
$recent_activities_query = "SELECT al.*, u.first_name, u.last_name 
                             FROM activity_logs al 
                             INNER JOIN users u ON al.user_id = u.user_id 
                             ORDER BY al.created_at DESC LIMIT 10";
$recent_activities = mysqli_query($conn, $recent_activities_query);

// Get recent registrations
$recent_users_query = "SELECT user_id, first_name, last_name, email, role, created_at 
                       FROM users 
                       ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $recent_users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            border-radius: 10px;
            transition: all 0.3s;
            border: none;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
            position: absolute;
            right: 15px;
            top: 15px;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .activity-item {
            border-left: 3px solid #667eea;
            padding-left: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4 class="mb-4">
                        <i class="fas fa-shield-alt"></i> Admin Panel
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="admin-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="manage-students.php">
                            <i class="fas fa-users"></i> Manage Students
                        </a>
                        <a class="nav-link" href="manage-courses.php">
                            <i class="fas fa-book"></i> Manage Courses
                        </a>
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Admin Dashboard</h2>
                        <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar"></i> <?php echo date('l, F d, Y'); ?>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body position-relative">
                                <i class="fas fa-users stat-icon"></i>
                                <h6 class="text-white-50">Total Users</h6>
                                <h2 class="mb-0"><?php echo $total_users; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body position-relative">
                                <i class="fas fa-user-graduate stat-icon"></i>
                                <h6 class="text-white-50">Active Students</h6>
                                <h2 class="mb-0"><?php echo $active_students; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body position-relative">
                                <i class="fas fa-book-open stat-icon"></i>
                                <h6 class="text-white-50">Active Courses</h6>
                                <h2 class="mb-0"><?php echo $active_courses; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body position-relative">
                                <i class="fas fa-clipboard-list stat-icon"></i>
                                <h6 class="text-white-50">Total Enrollments</h6>
                                <h2 class="mb-0"><?php echo $total_enrollments; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-7 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-history text-primary"></i> Recent Activities
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                                    <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
                                        <div class="activity-item">
                                            <strong><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></strong>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">No recent activities</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Registrations -->
                    <div class="col-md-5 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus text-success"></i> Recent Registrations
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($recent_users) > 0): ?>
                                    <div class="list-group">
                                        <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                        </h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo date('M d', strtotime($user['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-info mt-2"><?php echo ucfirst($user['role']); ?></span>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">No recent registrations</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>