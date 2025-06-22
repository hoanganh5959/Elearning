<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/CourseManager.php';
require_once __DIR__ . '/../../classes/LessonManager.php';

// Kiểm tra quyền truy cập
requireLogin(['instructor', 'admin']);


$instructor_id = $_SESSION['user_id'];
$instructor_name = $_SESSION['user'] ?? 'Giảng viên';

$courseManager = new CourseManager();
$lessonManager = new LessonManager();

// Lấy thống kê tổng quan
$instructor_courses = $courseManager->getCoursesByInstructor($instructor_id);
$total_courses = count($instructor_courses);

// Tính tổng số bài học và học viên
$total_lessons = 0;
$total_students = 0;
foreach ($instructor_courses as $course) {
    $total_lessons += $course['lesson_count'];
    $total_students += $course['student_count'];
}

// Lấy 5 khóa học gần đây nhất
$recent_courses = array_slice($instructor_courses, 0, 5);

// Kiểm tra thông báo
$success_message = '';
$error_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Giảng viên - eLEARNING</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white !important;
        }

        .navbar-custom .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-custom .navbar-toggler:focus {
            outline: none;
            box-shadow: none;
        }

        .main-content {
            padding: 20px 0;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-card.courses {
            border-left-color: #28a745;
        }

        .stat-card.lessons {
            border-left-color: #007bff;
        }

        .stat-card.students {
            border-left-color: #ffc107;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 15px;
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.1;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .quick-action-card {
            /* background: white;
            border-radius: 10px;
            padding: 25px; */
            text-align: center;
            /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
            /* transition: all 0.3s ease; */
            text-decoration: none;
            color: inherit;
            /* margin-bottom: 20px; */
        }

        .quick-action-card:hover {
            /* transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2); */
            text-decoration: none;
            color: inherit;
        }

        .quick-action-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #667eea;
        }

        .recent-courses-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .course-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .course-item:last-child {
            border-bottom: none;
        }

        .course-thumbnail {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .course-info {
            flex: 1;
        }

        .course-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .course-stats {
            font-size: 0.9rem;
            color: #666;
        }

        .welcome-text {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .chart-container {
            position: relative;
            height: 350px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .chart-wrapper {
            position: relative;
            width: 100%;
            max-width: 280px;
            height: 250px;
            margin: 0 auto;
        }

        #courseChart {
            max-width: 100%;
            max-height: 100%;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                text-align: center;
            }

            .stat-number {
                font-size: 2rem;
            }

            .quick-action-icon {
                font-size: 2.5rem;
            }

            .course-actions {
                margin-top: 10px;
            }

            .course-item {
                flex-direction: column;
                text-align: center;
            }

            .course-thumbnail {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .chart-container {
                height: 300px;
                padding: 20px;
            }

            .chart-wrapper {
                max-width: 250px;
                height: 200px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-graduation-cap mr-2"></i>eLEARNING
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon">
                    <i class="fas fa-bars text-white"></i>
                </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <?php if ($_SESSION['phanquyen'] === 'admin'): ?>
                            <a class="nav-link" href="../admin/index.php">
                                <i class="fas fa-tachometer-alt mr-1"></i>Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a class="nav-link" href="../my-courses.php">
                                <i class="fas fa-book mr-1"></i>Khóa học của tôi
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../personal-infor.php">
                            <i class="fas fa-user mr-1"></i>Hồ sơ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-1"></i>Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="welcome-text">
                                <i class="fas fa-chalkboard-teacher mr-3"></i>
                                Xin chào, <?php echo htmlspecialchars($instructor_name); ?>!
                            </h1>
                            <p class="subtitle">Chào mừng bạn đến với bảng điều khiển giảng viên</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <i class="fas fa-chart-line" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card courses position-relative">
                        <div class="stat-number text-success"><?php echo $total_courses; ?></div>
                        <div class="stat-label">Khóa học</div>
                        <a href="manage_courses.php" class="btn btn-outline-success">Quản lý</a>
                        <i class="fas fa-book stat-icon text-success"></i>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card lessons position-relative">
                        <div class="stat-number text-primary"><?php echo $total_lessons; ?></div>
                        <div class="stat-label">Bài học</div>
                        <a href="manage_lessons.php" class="btn btn-outline-primary">Quản lý</a>
                        <i class="fas fa-play-circle stat-icon text-primary"></i>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card students position-relative">
                        <div class="stat-number text-warning"><?php echo $total_students; ?></div>
                        <div class="stat-label">Học viên</div>
                        <a href="#students-section" class="btn btn-outline-warning">Xem chi tiết</a>
                        <i class="fas fa-users stat-icon text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="mb-3">Hành động nhanh</h3>
                </div>
                <div class="col-lg-3 col-md-6 ">
                    <a href="add_course.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <h5>Tạo khóa học mới</h5>
                        <p class="text-muted">Thêm khóa học mới với đầy đủ thông tin</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="manage_courses.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h5>Quản lý khóa học</h5>
                        <p class="text-muted">Chỉnh sửa, xóa và cập nhật khóa học</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="manage_lessons.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <h5>Quản lý bài học</h5>
                        <p class="text-muted">Thêm và chỉnh sửa bài học</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="../personal-infor.php" class="quick-action-card">
                        <div class="quick-action-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h5>Cài đặt hồ sơ</h5>
                        <p class="text-muted">Cập nhật thông tin cá nhân</p>
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Recent Courses -->
                <div class="col-lg-8">
                    <div class="recent-courses-card">
                        <h4 class="mb-3">
                            <i class="fas fa-clock text-primary mr-2"></i>
                            Khóa học gần đây
                        </h4>
                        <?php if (empty($recent_courses)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Bạn chưa có khóa học nào.</p>
                                <a href="add_course.php" class="btn btn-primary">Tạo khóa học đầu tiên</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_courses as $course): ?>
                                <div class="course-item">
                                    <img src="../<?php echo htmlspecialchars($course['thumbnail'] ?: 'assets/img/default-course.png'); ?>"
                                        alt="<?php echo htmlspecialchars($course['title']); ?>"
                                        class="course-thumbnail">
                                    <div class="course-info">
                                        <div class="course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                                        <div class="course-stats">
                                            <span class="badge badge-primary"><?php echo $course['lesson_count']; ?> bài học</span>
                                            <span class="badge badge-success"><?php echo $course['student_count']; ?> học viên</span>
                                            <span class="badge badge-warning"><?php echo number_format($course['price'], 0, ',', '.'); ?> VNĐ</span>
                                        </div>
                                    </div>
                                    <div class="course-actions">
                                        <a href="edit_course.php?course_id=<?php echo $course['id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="manage_lessons.php?course_id=<?php echo $course['id']; ?>"
                                            class="btn btn-sm btn-outline-success" title="Quản lý bài học">
                                            <i class="fas fa-list-ul"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($instructor_courses) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="manage_courses.php" class="btn btn-outline-primary">
                                        Xem tất cả <?php echo $total_courses; ?> khóa học
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Charts and Analytics -->
                <div class="col-lg-4">
                    <div class="chart-container">
                        <h5 class="mb-3"> <i class="fas fa-chart-pie text-info mr-2"></i> Thống kê khóa học </h5>
                        <div class="chart-wrapper"> <canvas id="courseChart"></canvas> </div>
                    </div>

                    <!-- Students by Course Section -->
                    <div id="students-section" class="recent-courses-card">
                        <h5 class="mb-3">
                            <i class="fas fa-users text-warning mr-2"></i>
                            Học viên theo khóa học
                        </h5>
                        <?php if (empty($instructor_courses)): ?>
                            <p class="text-muted text-center">Chưa có dữ liệu</p>
                        <?php else: ?>
                            <?php foreach (array_slice($instructor_courses, 0, 5) as $course): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small"><?php echo htmlspecialchars(substr($course['title'], 0, 25)) . (strlen($course['title']) > 25 ? '...' : ''); ?></span>
                                    <span class="badge badge-warning"><?php echo $course['student_count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Chart for course statistics
        <?php if (!empty($instructor_courses)): ?>
            const ctx = document.getElementById('courseChart').getContext('2d');
            const courseChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Khóa học', 'Bài học', 'Học viên'],
                    datasets: [{
                        data: [<?php echo $total_courses; ?>, <?php echo $total_lessons; ?>, <?php echo $total_students; ?>],
                        backgroundColor: [
                            '#28a745',
                            '#007bff',
                            '#ffc107'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1,
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            fontSize: 11,
                            boxWidth: 12
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.labels[tooltipItem.index];
                                const value = data.datasets[0].data[tooltipItem.index];
                                return label + ': ' + value;
                            }
                        }
                    },
                    cutoutPercentage: 50,
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    }
                }
            });
        <?php else: ?>
            // No data message for chart
            document.getElementById('courseChart').parentElement.innerHTML =
                '<div class="text-center py-4"><i class="fas fa-chart-pie fa-2x text-muted mb-2"></i><p class="text-muted">Chưa có dữ liệu để hiển thị</p></div>';
        <?php endif; ?>

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
</body>

</html>