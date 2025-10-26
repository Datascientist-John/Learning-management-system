php<?php
session_start();
require_once '../config/db_config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle course actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $course_id = intval($_POST['course_id'] ?? 0);
        
        switch ($_POST['action']) {
            case 'delete':
                $sql = "DELETE FROM courses WHERE course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                
                if ($stmt->execute()) {
                    $message = "Course deleted successfully";
                    $message_type = "danger";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Delete Course', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Deleted course ID: $course_id";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'update':
                $course_name = trim($_POST['course_name']);
                $description = trim($_POST['description']);
                $category = trim($_POST['category']);
                $duration = trim($_POST['duration']);
                $status = $_POST['status'];
                
                $sql = "UPDATE courses SET 
                        course_name = ?, 
                        description = ?, 
                        category = ?,
                        duration = ?,
                        status = ?
                        WHERE course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $course_name, $description, $category, $duration, $status, $course_id);
                
                if ($stmt->execute()) {
                    $message = "Course information updated successfully";
                    $message_type = "info";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Update Course', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Updated course: $course_name";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'add':
                $course_name = trim($_POST['course_name']);
                $description = trim($_POST['description']);
                $category = trim($_POST['category']);
                $duration = trim($_POST['duration']);
                $instructor_id = $_SESSION['user_id'];
                
                $sql = "INSERT INTO courses (course_name, description, category, duration, instructor_id, status) 
                        VALUES (?, ?, ?, ?, ?, 'active')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $course_name, $description, $category, $duration, $instructor_id);
                
                if ($stmt->execute()) {
                    $message = "Course added successfully";
                    $message_type = "success";
                    
                    // Log activity
                    $admin_id = $_SESSION['user_id'];
                    $log_sql = "INSERT INTO activity_logs (user_id, action, description) 
                                VALUES (?, 'Add Course', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $desc = "Added new course: $course_name";
                    $log_stmt->bind_param("is", $admin_id, $desc);
                    $log_stmt->execute();
                }
                break;
                
            case 'toggle_status':
                // Toggle between active and inactive
                $sql = "UPDATE courses SET status = IF(status = 'active', 'inactive', 'active') WHERE course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $course_id);
                
                if ($stmt->execute()) {
                    $message = "Course status updated successfully";
                    $message_type = "info";
                }
                break;
        }
    }
}

// Get all courses
$courses_query = "SELECT c.*, u.first_name, u.last_name 
                  FROM courses c 
                  LEFT JOIN users u ON c.instructor_id = u.user_id 
                  ORDER BY c.created_at DESC";
$courses_result = mysqli_query($conn, $courses_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin</title>
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
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .course-description {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                        <a class="nav-link" href="manage-students.php">
                            <i class="fas fa-users"></i> Manage Students
                        </a>
                        <a class="nav-link active" href="manage-courses.php">
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
                        <i class="fas fa-book text-primary"></i> Manage Courses
                    </h2>
                    <div>
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fas fa-plus"></i> Add New Course
                        </button>
                        <a href="admin-dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">All Courses</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Course Name</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Duration</th>
                                        <th>Instructor</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($courses_result) > 0): ?>
                                        <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                            <tr>
                                                <td><?php echo $course['course_id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="course-description" title="<?php echo htmlspecialchars($course['description']); ?>">
                                                        <?php echo htmlspecialchars($course['description']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($course['category'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($course['first_name']) {
                                                        echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']);
                                                    } else {
                                                        echo '<span class="text-muted">Not assigned</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($course['status'] == 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <!-- Edit Button -->
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal<?php echo $course['course_id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <!-- Toggle Status -->
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                                    title="Toggle Status">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Delete Button -->
                                                        <form method="POST" style="display:inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this course? This will also delete all associated lessons and assignments.');">
                                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $course['course_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Course</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                                                <input type="hidden" name="action" value="update">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Course Name</label>
                                                                    <input type="text" class="form-control" name="course_name" 
                                                                           value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Description</label>
                                                                    <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Category</label>
                                                                    <input type="text" class="form-control" name="category" 
                                                                           value="<?php echo htmlspecialchars($course['category']); ?>" 
                                                                           placeholder="e.g., Programming, Design">
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Duration</label>
                                                                    <input type="text" class="form-control" name="duration" 
                                                                           value="<?php echo htmlspecialchars($course['duration']); ?>" 
                                                                           placeholder="e.g., 4 weeks" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status</label>
                                                                    <select class="form-select" name="status" required>
                                                                        <option value="active" <?php echo $course['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                        <option value="inactive" <?php echo $course['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                    </select>
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
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-book fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted">No courses found. Add your first course!</p>
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

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" 
                                   placeholder="Enter course name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Enter course description" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" 
                                   placeholder="e.g., Programming, Design, Business">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" 
                                   placeholder="e.g., 4 weeks, 2 months" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>