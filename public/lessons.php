<?php
require_once '../classes/LessonManager.php';
require_once '../classes/CourseManager.php';
require_once '../includes/bootstrap.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Lưu URL hiện tại để quay lại sau khi đăng nhập
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['alert_message'] = 'Vui lòng đăng nhập để xem bài học';
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$lessonManager = new LessonManager();
$courseManager = new CourseManager();
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Kiểm tra xem course_id có hợp lệ không
if ($course_id <= 0) {
    $_SESSION['alert_message'] = 'Khóa học không tồn tại';
    header('Location: courses.php');
    exit();
}

// Kiểm tra xem người dùng đã đăng ký khóa học này chưa
if (!$courseManager->isUserEnrolled($userId, $course_id)) {
    $_SESSION['alert_message'] = 'Bạn chưa đăng ký khóa học này';
    header('Location: course-detail.php?id=' . $course_id);
    exit();
}

// Lấy danh sách bài học của khóa học
$lessons = json_decode($lessonManager->getLessonsByCourseId($course_id), true);

// Lấy trạng thái hoàn thành của các bài học nếu đã đăng nhập
$lessonCompletionStatus = [];
if ($userId) {
    $lessonCompletionStatus = $lessonManager->getLessonCompletionStatus($userId, $course_id);
}

// Chọn bài học active (nếu có)
$activeLesson = null;
$activeIndex = 0;
if (!empty($lessons)) {
    $lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : null;
    if ($lessonId) {
        foreach ($lessons as $index => $lesson) {
            if ($lesson['id'] == $lessonId) {
                $activeLesson = $lesson;
                $activeIndex = $index;
                break;
            }
        }
    }
    
    // Nếu không có lesson_id được chỉ định hoặc không tìm thấy, sử dụng bài học đầu tiên
    if (!$activeLesson) {
        $activeLesson = $lessons[0];
    }
}

// Lấy thông tin khóa học để hiển thị tên
$course = $courseManager->getCourseById($course_id);
$courseTitle = $course ? $course['title'] : 'Khóa học';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($courseTitle); ?> - eLEARNING</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="../public/assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../public/assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="../public/assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../public/assets/css/style.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
        }

        .lesson-item {
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 12px 15px;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .lesson-item:hover {
            background-color: #f0f0f0;
        }

        .active-lesson {
            background-color: #007bff !important;
            color: white !important;
        }

        .lesson-list-container {
            max-height: 80vh;
            /* Chiếm phần lớn chiều cao màn hình */
            overflow-y: auto;
            padding-right: 10px;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Tỷ lệ 80% video, 20% danh sách */
        @media (min-width: 768px) {
            .video-column {
                flex: 0 0 80%;
                max-width: 80%;
            }

            .lesson-column {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }

        /* Thanh cuộn đẹp hơn */
        .lesson-list-container::-webkit-scrollbar {
            width: 6px;
        }

        .lesson-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .lesson-list-container::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 3px;
        }

        .lesson-list-container::-webkit-scrollbar-thumb:hover {
            background: #0056b3;
        }

        /* Đảm bảo video lớn hơn */
        .video-container iframe {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Style cho checkbox đánh dấu hoàn thành */
        .completion-status {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Biểu tượng đã hoàn thành */
        .completed-icon {
            color: #28a745;
            margin-right: 5px;
        }

        #progressBar {
            height: 8px;
            transition: width 0.3s ease;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1050;
            display: none;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div> -->
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <?php include("../public/includes/header.php"); ?>
    <!-- Navbar End -->

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h6 class="section-title bg-white text-center text-primary px-3">Bài học</h6>
                    <h1 class="mb-5"><?php echo htmlspecialchars($courseTitle); ?></h1>
                </div>
                <div class="row g-4 align-items-start">
                    <!-- Video Player -->
                    <div class="col-md-8 video-column wow fadeInUp" data-wow-delay="0.3s">
                        <div class="video-container">
                            <div id="player"></div>
                        </div>

                        <?php if ($userId): ?>
                            <!-- Trạng thái hoàn thành -->
                            <div class="completion-status mt-3">
                                <div>
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div id="progressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="progressText">0% đã xem</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Lesson List -->
                    <div class="col-md-4 lesson-column wow fadeInUp" data-wow-delay="0.5s">
                        <div class="lesson-list-container">
                            <div id="lessonList" class="list-group">
                                <?php if (!empty($lessons)): ?>
                                    <?php foreach ($lessons as $index => $lesson): ?>
                                        <?php
                                        $isCompleted = isset($lessonCompletionStatus[$lesson['id']]) &&
                                            $lessonCompletionStatus[$lesson['id']]['completed'];
                                        $isActive = ($activeLesson && $lesson['id'] == $activeLesson['id']);
                                        ?>
                                        <div class="lesson-item list-group-item <?php echo $isActive ? 'active-lesson' : ''; ?>"
                                            data-video-id="<?php echo htmlspecialchars($lesson['youtube_id']); ?>"
                                            data-lesson-id="<?php echo $lesson['id']; ?>"
                                            data-completed="<?php echo $isCompleted ? '1' : '0'; ?>">
                                            <?php if ($isCompleted): ?>
                                                <i class="fas fa-check-circle text-success completed-icon"></i>
                                            <?php endif; ?>
                                            <?php echo ($index + 1) . '. ' . htmlspecialchars($lesson['title']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="list-group-item text-danger">No lessons found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast thông báo -->
    <div id="toastMessage" class="toast">
        Bài học đã được đánh dấu hoàn thành!
    </div>

    <!-- Footer Start -->
    <?php include("../public/includes/footer.php"); ?>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/assets/lib/wow/wow.min.js"></script>
    <script src="../public/assets/lib/easing/easing.min.js"></script>
    <script src="../public/assets/lib/waypoints/waypoints.min.js"></script>
    <script src="../public/assets/lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../public/assets/js/main.js"></script>

    <!-- YouTube API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Custom JavaScript -->
    <script>
        let player;
        let currentLessonId = null;
        let currentVideoId = null;
        let isMarkingInProgress = false;
        const userId = <?php echo $userId ? $userId : 'null'; ?>;

        // Đảm bảo API YouTube sẵn sàng
        function loadYouTubePlayer() {
            const lessonItems = document.querySelectorAll(".lesson-item");
            if (lessonItems.length > 0) {
                const activeLesson = document.querySelector('.active-lesson') || lessonItems[0];
                const firstVideoId = activeLesson.dataset.videoId;
                currentVideoId = firstVideoId;
                currentLessonId = activeLesson.dataset.lessonId;

                if (typeof YT !== 'undefined' && YT.Player) {
                    createPlayer(firstVideoId);
                } else {
                    // Nếu API chưa sẵn sàng, đặt biến toàn cục để onYouTubeIframeAPIReady sử dụng sau
                    window.videoIdToLoad = firstVideoId;
                }
            }
        }

        // Tạo player
        function createPlayer(videoId) {
            player = new YT.Player('player', {
                height: '100%',
                width: '100%',
                videoId: videoId,
                playerVars: {
                    'playsinline': 1,
                    'rel': 0
                },
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }

        // YouTube Player API callback
        function onYouTubeIframeAPIReady() {
            if (window.videoIdToLoad) {
                createPlayer(window.videoIdToLoad);
            } else {
                loadYouTubePlayer();
            }
        }

        function onPlayerReady(event) {
            // Player sẵn sàng
            event.target.playVideo();
        }

        function onPlayerStateChange(event) {
            // Theo dõi tiến trình xem video
            if (event.data == YT.PlayerState.PLAYING) {
                checkVideoProgress();
            }
        }

        // Kiểm tra tiến trình xem video định kỳ
        function checkVideoProgress() {
            if (!player || !userId) return;

            const interval = setInterval(() => {
                if (!player.getCurrentTime || !player.getDuration || player.getPlayerState() !== YT.PlayerState.PLAYING) {
                    clearInterval(interval);
                    return;
                }

                const currentTime = player.getCurrentTime();
                const duration = player.getDuration();
                const progress = (currentTime / duration) * 100;

                // Cập nhật thanh tiến trình
                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('progressText').textContent = Math.round(progress) + '% đã xem';

                // Kiểm tra nếu đã xem được 50% video
                if (progress >= 50 && currentLessonId && !isMarkingInProgress) {
                    // Kiểm tra nếu bài học chưa được đánh dấu hoàn thành
                    const currentLessonItem = document.querySelector(`.lesson-item[data-lesson-id="${currentLessonId}"]`);
                    if (currentLessonItem && currentLessonItem.dataset.completed !== '1') {
                        markLessonComplete(currentLessonId, true);
                        clearInterval(interval);
                    }
                }
            }, 1000); // Cập nhật mỗi giây
        }

        document.addEventListener("DOMContentLoaded", () => {
            const lessonItems = document.querySelectorAll(".lesson-item");

            // Đảm bảo luôn có một bài học active
            const activeLesson = document.querySelector('.active-lesson');
            if (!activeLesson && lessonItems.length > 0) {
                lessonItems[0].classList.add('active-lesson');
            }

            // Đảm bảo hiển thị đúng icon hoàn thành
            lessonItems.forEach(item => {
                const isCompleted = item.dataset.completed === '1';
                if (isCompleted) {
                    let icon = item.querySelector('.completed-icon');
                    if (!icon) {
                        icon = document.createElement('i');
                        icon.className = 'fas fa-check-circle text-success completed-icon';
                        item.prepend(icon);
                    }
                }

                item.addEventListener("click", () => {
                    // Lưu ID bài học đang xem vào URL
                    const lessonId = item.dataset.lessonId;
                    const url = new URL(window.location);
                    url.searchParams.set('lesson_id', lessonId);
                    history.pushState({}, '', url);

                    lessonItems.forEach(el => el.classList.remove("active-lesson"));
                    item.classList.add("active-lesson");

                    const videoId = item.dataset.videoId;
                    currentVideoId = videoId;
                    currentLessonId = item.dataset.lessonId;

                    // Thay đổi video
                    if (player && player.loadVideoById) {
                        player.loadVideoById(videoId);

                        // Reset thanh tiến trình
                        document.getElementById('progressBar').style.width = '0%';
                        document.getElementById('progressText').textContent = '0% đã xem';
                    }
                });
            });

            // Gọi hàm tải player
            loadYouTubePlayer();
        });

        // Đánh dấu bài học hoàn thành
        function markLessonComplete(lessonId, completed) {
            if (!userId || isMarkingInProgress) return;

            isMarkingInProgress = true;

            // Gửi request AJAX đánh dấu hoàn thành
            const formData = new FormData();
            formData.append('lesson_id', lessonId);
            formData.append('completed', completed ? 1 : 0);

            fetch('mark-lesson-complete.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    isMarkingInProgress = false;

                    if (data.success) {
                        // Cập nhật giao diện
                        updateLessonUI(lessonId, completed);

                        // Hiển thị thông báo
                        showToast(data.message);
                    } else {
                        // Hiển thị lỗi
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật trạng thái bài học');
                    isMarkingInProgress = false;
                });
        }

        // Cập nhật giao diện khi bài học được đánh dấu hoàn thành
        function updateLessonUI(lessonId, completed) {
            // Cập nhật trạng thái trong danh sách bài học
            const lessonItems = document.querySelectorAll(".lesson-item");
            lessonItems.forEach(item => {
                if (item.dataset.lessonId === lessonId) {
                    // Cập nhật data-completed
                    item.dataset.completed = completed ? '1' : '0';

                    // Thêm hoặc xóa biểu tượng hoàn thành
                    let icon = item.querySelector('.completed-icon');
                    if (completed) {
                        if (!icon) {
                            icon = document.createElement('i');
                            icon.className = 'fas fa-check-circle text-success completed-icon';
                            item.prepend(icon);
                        }
                    } else {
                        if (icon) icon.remove();
                    }
                }
            });
        }

        // Hiển thị thông báo
        function showToast(message) {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.textContent = message;
                toast.style.display = 'block';

                // Tự động ẩn sau 3 giây
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            }
        }
    </script>
</body>

</html>