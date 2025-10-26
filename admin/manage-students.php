<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db_config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $student_id = intval($_POST['student_id'] ?? 0);
        
        switch ($_POST['action']) {
            case 'suspend':
                $sql = "UPDATE users SET active = 0 WHERE user_id = ? AND role = 'student'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_id);
                
                if ($stmt->execute()) {
                    $message = "Student suspended successfully";
                    $message_type = "warning";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Suspend Student', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Suspended student ID: $student_id";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'activate':
                $sql = "UPDATE users SET active = 1 WHERE user_id = ? AND role = 'student'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_id);
                
                if ($stmt->execute()) {
                    $message = "Student activated successfully";
                    $message_type = "success";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Activate Student', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Activated student ID: $student_id";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'delete':
                $sql = "DELETE FROM users WHERE user_id = ? AND role = 'student'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_id);
                
                if ($stmt->execute()) {
                    $message = "Student deleted successfully";
                    $message_type = "danger";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Delete Student', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Deleted student ID: $student_id";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'update':
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
                $email = trim($_POST['email']);
                
                $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ? 
                        WHERE user_id = ? AND role = 'student'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $first_name, $last_name, $email, $student_id);
                
                if ($stmt->execute()) {
                    $message = "Student information updated successfully";
                    $message_type = "info";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Update Student', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Updated student: $first_name $last_name";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
        }
    }
}

// Get all students
$students_query = "SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC";
$students_result = mysqli_query($conn, $students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
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
                        <a class="nav-link" href="admin-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="manage-students.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-users text-primary"></i> Manage Students
                    </h2>
                    <a href="admin-dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">All Students</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Email Verified</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($students_result) > 0): ?>
                                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                            <tr>
                                                <td><?php echo $student['user_id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td>
                                                    <?php if ($student['email_verified'] == 1): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Verified
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($student['active'] == 1): ?>
                                                        <span class="badge bg-success status-badge">
                                                            <i class="fas fa-check-circle"></i> Active
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger status-badge">
                                                            <i class="fas fa-ban"></i> Suspended
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <!-- Edit Button -->
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?php echo $student['user_id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <!-- Suspend/Activate Button -->
                                                        <?php if ($student['active'] == 1): ?>
                                                            <form method="POST" style="display:inline;" 
                                                                  onsubmit="return confirm('Are you sure you want to suspend this student?');">
                                                                <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                                                <input type="hidden" name="action" value="suspend">
                                                                <button type="submit" class="btn btn-sm btn-warning" title="Suspend Student">
                                                                    <i class="fas fa-user-slash"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                                                <input type="hidden" name="action" value="activate">
                                                                <button type="submit" class="btn btn-sm btn-success" title="Activate Student">
                                                                    <i class="fas fa-user-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Delete Button -->
                                                        <form method="POST" style="display:inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                                                            <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Student">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $student['user_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Student Information</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                                                <input type="hidden" name="action" value="update">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">First Name</label>
                                                                    <input type="text" class="form-control" name="first_name" 
                                                                           value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Last Name</label>
                                                                    <input type="text" class="form-control" name="last_name" 
                                                                           value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Email</label>
                                                                    <input type="email" class="form-control" name="email" 
                                                                           value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted">No students found.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>