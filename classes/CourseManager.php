<?php
require_once __DIR__ . '/../public/includes/db.php';

class CourseManager
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả các danh mục khóa học
     * 
     * @return array Danh sách các danh mục
     */
    public function getAllCategories()
    {
        $stmt = $this->conn->prepare("SELECT id, name FROM categories");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = array();

        while ($row = $result->fetch_assoc()) {
            $row['course_count'] = $this->countCoursesByCategory($row['id']);
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Đếm số lượng khóa học theo danh mục
     * 
     * @param int $categoryId ID của danh mục
     * @return int Số lượng khóa học
     */
    public function countCoursesByCategory($categoryId)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(c.id) as total 
            FROM courses c 
            JOIN course_categories cc ON c.id = cc.course_id 
            WHERE cc.category_id = ?
        ");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    /**
     * Lấy tất cả khóa học với các thông tin bổ sung
     * 
     * @return array Danh sách khóa học
     */
    public function getAllCourses()
    {
        $stmt = $this->conn->prepare("SELECT c.*, u.name as instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.user_id");
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = array();

        while ($row = $result->fetch_assoc()) {
            $row['lesson_count'] = $this->countLessonsByCourse($row['id']);
            $row['student_count'] = $this->countStudentsByCourse($row['id']);
            $row['categories'] = $this->getCategoriesByCourse($row['id']);
            $courses[] = $row;
        }

        return $courses;
    }

    /**
     * Lấy các khóa học theo danh mục
     * 
     * @param int $categoryId ID của danh mục
     * @return array Danh sách khóa học thuộc danh mục
     */
    public function getCoursesByCategory($categoryId)
    {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.name as instructor_name 
            FROM courses c 
            LEFT JOIN users u ON c.instructor_id = u.user_id
            JOIN course_categories cc ON c.id = cc.course_id 
            WHERE cc.category_id = ?
        ");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = array();

        while ($row = $result->fetch_assoc()) {
            $row['lesson_count'] = $this->countLessonsByCourse($row['id']);
            $row['student_count'] = $this->countStudentsByCourse($row['id']);
            $row['categories'] = $this->getCategoriesByCourse($row['id']);
            $courses[] = $row;
        }

        return $courses;
    }

    /**
     * Lấy chi tiết về danh mục
     * 
     * @param int $categoryId ID của danh mục
     * @return array|null Thông tin về danh mục
     */
    public function getCategoryById($categoryId)
    {
        $stmt = $this->conn->prepare("SELECT id, name FROM categories WHERE id = ?");
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $category = $result->fetch_assoc();
            $category['course_count'] = $this->countCoursesByCategory($categoryId);
            return $category;
        }

        return null;
    }

    /**
     * Lấy chi tiết khóa học theo ID
     * 
     * @param int $courseId ID của khóa học
     * @return array|null Thông tin khóa học
     */
    public function getCourseById($courseId)
    {
        $stmt = $this->conn->prepare("SELECT c.*, u.name as instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.user_id WHERE c.id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $course = $result->fetch_assoc();
            $course['lesson_count'] = $this->countLessonsByCourse($course['id']);
            $course['student_count'] = $this->countStudentsByCourse($course['id']);
            $course['categories'] = $this->getCategoriesByCourse($course['id']);
            return $course;
        }

        return null;
    }

    /**
     * Lấy danh sách bài học theo khóa học
     * 
     * @param int $courseId ID của khóa học
     * @return array Danh sách bài học
     */
    public function getLessonsByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT id, title, content, youtube_id, sort_order FROM lessons WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lessons = array();

        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }

        return $lessons;
    }

    /**
     * Kiểm tra xem người dùng đã đăng ký khóa học chưa
     * 
     * @param int $userId ID của người dùng
     * @param int $courseId ID của khóa học
     * @return bool True nếu người dùng đã đăng ký, False nếu chưa
     */
    public function isUserEnrolled($userId, $courseId)
    {
        // Nếu không có user_id, người dùng chưa đăng nhập
        if (!$userId) {
            return false;
        }

        $stmt = $this->conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Đăng ký khóa học cho người dùng
     * 
     * @param int $userId ID của người dùng
     * @param int $courseId ID của khóa học
     * @return bool True nếu đăng ký thành công, False nếu thất bại
     */
    public function enrollUserToCourse($userId, $courseId)
    {
        // Kiểm tra xem đã đăng ký chưa
        if ($this->isUserEnrolled($userId, $courseId)) {
            return true; // Đã đăng ký rồi
        }

        // Thêm vào bảng enrollments
        $stmt = $this->conn->prepare("INSERT INTO enrollments (user_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $userId, $courseId);
        $success = $stmt->execute();

        return $success;
    }

    /**
     * Đếm số lượng bài học trong một khóa học
     * 
     * @param int $courseId ID của khóa học
     * @return int Số lượng bài học
     */
    private function countLessonsByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM lessons WHERE course_id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    /**
     * Đếm số lượng học viên của một khóa học
     * 
     * @param int $courseId ID của khóa học
     * @return int Số lượng học viên
     */
    private function countStudentsByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM enrollments WHERE course_id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    /**
     * Lấy danh sách các danh mục của một khóa học
     * 
     * @param int $courseId ID của khóa học
     * @return array Danh sách các danh mục
     */
    private function getCategoriesByCourse($courseId)
    {
        $stmt = $this->conn->prepare("
            SELECT c.id, c.name
            FROM categories c
            JOIN course_categories cc ON c.id = cc.category_id
            WHERE cc.course_id = ?
        ");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = array();

        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Lấy danh sách khóa học mà người dùng đã đăng ký
     * 
     * @param int $userId ID của người dùng
     * @return array Danh sách các khóa học đã đăng ký
     */
    public function getEnrolledCourses($userId)
    {
        $stmt = $this->conn->prepare("
            SELECT c.id AS course_id, c.title, c.description, c.price, c.instructor_id, c.created_at, c.thumbnail, 
                   u.name as instructor_name, e.enrolled_at
            FROM courses c
            JOIN enrollments e ON c.id = e.course_id
            LEFT JOIN users u ON c.instructor_id = u.user_id
            WHERE e.user_id = ?
            ORDER BY e.enrolled_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = array();

        while ($row = $result->fetch_assoc()) {
            $row['lesson_count'] = $this->countLessonsByCourse($row['course_id']);
            $row['completed_lessons'] = $this->countCompletedLessons($userId, $row['course_id']);
            $row['categories'] = $this->getCategoriesByCourse($row['course_id']);
            $row['student_count'] = $this->countStudentsByCourse($row['course_id']);
            $courses[] = $row;
        }

        return $courses;
    }

    /**
     * Đếm số lượng bài học đã hoàn thành của một khóa học
     * 
     * @param int $userId ID của người dùng
     * @param int $courseId ID của khóa học
     * @return int Số lượng bài học đã hoàn thành
     */
    public function countCompletedLessons($userId, $courseId)
    {
        // Kiểm tra xem bảng lesson_progress đã tồn tại chưa
        $query = "SHOW TABLES LIKE 'lesson_progress'";
        $result = $this->conn->query($query);

        // Nếu bảng chưa tồn tại, tạo mới
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE lesson_progress (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                lesson_id INT NOT NULL,
                completed TINYINT(1) DEFAULT 0,
                completed_at DATETIME NULL,
                UNIQUE KEY user_lesson (user_id, lesson_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
            )";
            $this->conn->query($createTable);
            return 0; // Bảng mới tạo, chưa có dữ liệu
        }

        // Đếm số bài học đã hoàn thành
        $stmt = $this->conn->prepare("
            SELECT COUNT(lp.id) as total 
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = ? AND l.course_id = ? AND lp.completed = 1
        ");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    /**
     * Đánh dấu bài học đã hoàn thành hoặc chưa hoàn thành
     * 
     * @param int $userId ID của người dùng
     * @param int $lessonId ID của bài học
     * @param int $completed 1 nếu hoàn thành, 0 nếu chưa hoàn thành
     * @return bool True nếu cập nhật thành công, False nếu thất bại
     */
    public function markLessonComplete($userId, $lessonId, $completed = 1)
    {
        // Kiểm tra xem bảng lesson_progress đã tồn tại chưa
        $this->ensureLessonProgressTableExists();

        // Kiểm tra xem bài học thuộc khóa học mà người dùng đã đăng ký không
        $stmt = $this->conn->prepare("
            SELECT c.id
            FROM courses c
            JOIN lessons l ON c.id = l.course_id
            JOIN enrollments e ON c.id = e.course_id
            WHERE l.id = ? AND e.user_id = ?
        ");
        $stmt->bind_param("ii", $lessonId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Người dùng không có quyền truy cập vào bài học này
            return false;
        }

        // Kiểm tra xem đã tồn tại bản ghi chưa
        $stmt = $this->conn->prepare("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->bind_param("ii", $userId, $lessonId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật bản ghi hiện có
            $stmt = $this->conn->prepare("
                UPDATE lesson_progress
                SET completed = ?,
                    completed_at = CASE WHEN ? = 1 THEN NOW() ELSE NULL END
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->bind_param("iiii", $completed, $completed, $userId, $lessonId);
        } else {
            // Thêm bản ghi mới
            $stmt = $this->conn->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at)
                VALUES (?, ?, ?, CASE WHEN ? = 1 THEN NOW() ELSE NULL END)
            ");
            $stmt->bind_param("iiii", $userId, $lessonId, $completed, $completed);
        }

        return $stmt->execute();
    }

    /**
     * Lấy trạng thái hoàn thành của các bài học trong một khóa học
     * 
     * @param int $userId ID của người dùng
     * @param int $courseId ID của khóa học
     * @return array Mảng trạng thái hoàn thành với lesson_id làm key
     */
    public function getLessonCompletionStatus($userId, $courseId)
    {
        // Kiểm tra xem bảng lesson_progress đã tồn tại chưa
        if (!$this->ensureLessonProgressTableExists()) {
            return [];
        }

        $stmt = $this->conn->prepare("
            SELECT lp.lesson_id, lp.completed, lp.completed_at
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = ? AND l.course_id = ?
        ");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        $completionStatus = [];
        while ($row = $result->fetch_assoc()) {
            $completionStatus[$row['lesson_id']] = [
                'completed' => (bool)$row['completed'],
                'completed_at' => $row['completed_at']
            ];
        }

        return $completionStatus;
    }

    /**
     * Đảm bảo bảng lesson_progress đã tồn tại
     * 
     * @return bool True nếu bảng đã tồn tại hoặc được tạo thành công
     */
    private function ensureLessonProgressTableExists()
    {
        // Kiểm tra xem bảng lesson_progress đã tồn tại chưa
        $query = "SHOW TABLES LIKE 'lesson_progress'";
        $result = $this->conn->query($query);

        // Nếu bảng chưa tồn tại, tạo mới
        if ($result->num_rows == 0) {
            $createTable = "CREATE TABLE lesson_progress (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                lesson_id INT NOT NULL,
                completed TINYINT(1) DEFAULT 0,
                completed_at DATETIME NULL,
                UNIQUE KEY user_lesson (user_id, lesson_id),
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
            )";
            return $this->conn->query($createTable);
        }

        return true;
    }

    /**
     * Lấy tất cả các khóa học của một giảng viên cụ thể
     *
     * @param int $instructor_id ID của giảng viên
     * @return array Danh sách các khóa học
     */
    public function getCoursesByInstructor($instructor_id)
    {
        $stmt = $this->conn->prepare("SELECT c.*, u.name as instructor_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.user_id WHERE c.instructor_id = ? ORDER BY c.created_at DESC");
        $stmt->bind_param("i", $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = array();

        while ($row = $result->fetch_assoc()) {
            $row['lesson_count'] = $this->countLessonsByCourse($row['id']);
            $row['student_count'] = $this->countStudentsByCourse($row['id']);
            $row['categories'] = $this->getCategoriesByCourse($row['id']); // Giả sử hàm này trả về mảng các đối tượng category hoặc mảng tên category
            $courses[] = $row;
        }

        return $courses;
    }

    /**
     * Thêm một khóa học mới
     *
     * @param array $data Dữ liệu khóa học bao gồm title, description, price, instructor_id, thumbnail (đường dẫn), và category_ids (mảng ID)
     * @return int|false ID của khóa học mới nếu thành công, ngược lại là false
     */
    public function addCourse($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO courses (title, description, price, instructor_id, thumbnail, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssdis", $data['title'], $data['description'], $data['price'], $data['instructor_id'], $data['thumbnail']);

        if ($stmt->execute()) {
            $course_id = $this->conn->insert_id;
            // Thêm vào bảng course_categories
            if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
                $stmt_cat = $this->conn->prepare("INSERT INTO course_categories (course_id, category_id) VALUES (?, ?)");
                foreach ($data['category_ids'] as $category_id) {
                    $stmt_cat->bind_param("ii", $course_id, $category_id);
                    $stmt_cat->execute();
                }
                $stmt_cat->close();
            }
            $stmt->close();
            return $course_id;
        }
        $stmt->close();
        return false;
    }

    /**
     * Cập nhật thông tin khóa học
     *
     * @param int $course_id ID của khóa học cần cập nhật
     * @param array $data Dữ liệu khóa học bao gồm title, description, price, thumbnail (đường dẫn), và category_ids (mảng ID)
     * @return bool True nếu cập nhật thành công, ngược lại là false
     */
    public function updateCourse($course_id, $data)
    {
        // Chuẩn bị câu lệnh SQL, không cập nhật instructor_id và created_at
        $sql = "UPDATE courses SET title = ?, description = ?, price = ?";
        $params = [$data['title'], $data['description'], $data['price']];
        $types = "ssd";

        if (!empty($data['thumbnail'])) {
            $sql .= ", thumbnail = ?";
            $params[] = $data['thumbnail'];
            $types .= "s";
        }

        $sql .= " WHERE id = ? AND instructor_id = ?"; // Chỉ chủ sở hữu mới được sửa
        $params[] = $course_id;
        $params[] = $data['instructor_id']; // Cần instructor_id để xác thực
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            // Cập nhật bảng course_categories
            // Trước tiên, xóa các category cũ của khóa học này
            $stmt_delete_cat = $this->conn->prepare("DELETE FROM course_categories WHERE course_id = ?");
            $stmt_delete_cat->bind_param("i", $course_id);
            $stmt_delete_cat->execute();
            $stmt_delete_cat->close();

            // Thêm các category mới
            if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
                $stmt_cat = $this->conn->prepare("INSERT INTO course_categories (course_id, category_id) VALUES (?, ?)");
                foreach ($data['category_ids'] as $category_id) {
                    $stmt_cat->bind_param("ii", $course_id, $category_id);
                    $stmt_cat->execute();
                }
                $stmt_cat->close();
            }
            return true;
        }
        return false;
    }

    /**
     * Xóa một khóa học
     *
     * @param int $course_id ID của khóa học cần xóa
     * @param int $instructor_id ID của giảng viên thực hiện xóa (để xác thực)
     * @param bool $force_delete Có cho phép xóa khóa học đã có học viên hay không (mặc định: false)
     * @return bool|string True nếu xóa thành công, string thông báo lỗi nếu thất bại
     */
    public function deleteCourse($course_id, $instructor_id, $force_delete = false)
    {
        // Kiểm tra xem có học viên đã đăng ký khóa học này chưa
        if (!$force_delete) {
            $stmt_check = $this->conn->prepare("SELECT COUNT(*) as student_count FROM enrollments WHERE course_id = ?");
            $stmt_check->bind_param("i", $course_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $student_count = $result_check->fetch_assoc()['student_count'];
            $stmt_check->close();
            
            if ($student_count > 0) {
                return "Không thể xóa khóa học này vì đã có {$student_count} học viên đăng ký. Nếu bạn chắc chắn muốn xóa, hãy liên hệ quản trị viên.";
            }
        }

        // Bắt đầu một transaction
        $this->conn->begin_transaction();

        try {
            // 1. Xóa các liên kết trong course_categories
            $stmt_cat = $this->conn->prepare("DELETE FROM course_categories WHERE course_id = ?");
            $stmt_cat->bind_param("i", $course_id);
            $stmt_cat->execute();
            $stmt_cat->close();

            // 2. Xóa các bản ghi trong enrollments (tùy chọn, có thể bạn muốn giữ lại lịch sử)
            // $stmt_enroll = $this->conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
            // $stmt_enroll->bind_param("i", $course_id);
            // $stmt_enroll->execute();
            // $stmt_enroll->close();

            // 3. Xóa các bản ghi trong lesson_progress liên quan đến các bài học của khóa này
            $stmt_lesson_ids = $this->conn->prepare("SELECT id FROM lessons WHERE course_id = ?");
            $stmt_lesson_ids->bind_param("i", $course_id);
            $stmt_lesson_ids->execute();
            $result_lessons = $stmt_lesson_ids->get_result();
            $lesson_ids = [];
            while ($row = $result_lessons->fetch_assoc()) {
                $lesson_ids[] = $row['id'];
            }
            $stmt_lesson_ids->close();

            if (!empty($lesson_ids)) {
                $placeholders = implode(',', array_fill(0, count($lesson_ids), '?'));
                $types = str_repeat('i', count($lesson_ids));
                $stmt_progress = $this->conn->prepare("DELETE FROM lesson_progress WHERE lesson_id IN ($placeholders)");
                $stmt_progress->bind_param($types, ...$lesson_ids);
                $stmt_progress->execute();
                $stmt_progress->close();
            }

            // 4. Xóa các bài học (lessons) thuộc khóa học
            $stmt_lessons = $this->conn->prepare("DELETE FROM lessons WHERE course_id = ?");
            $stmt_lessons->bind_param("i", $course_id);
            $stmt_lessons->execute();
            $stmt_lessons->close();

            // 5. Xóa khóa học khỏi bảng courses (chỉ khi đúng instructor_id)
            $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = ? AND instructor_id = ?");
            $stmt->bind_param("ii", $course_id, $instructor_id);
            $success = $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($success && $affected_rows > 0) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                // Có thể do $instructor_id không khớp hoặc $course_id không tồn tại
                error_log("Failed to delete course or instructor ID mismatch. Course ID: $course_id, Instructor ID: $instructor_id, Affected Rows: $affected_rows");
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error deleting course: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy ID các danh mục của một khóa học
     *
     * @param int $courseId ID của khóa học
     * @return array Danh sách ID các danh mục
     */
    public function getCourseCategoryIds($courseId)
    {
        $stmt = $this->conn->prepare("SELECT category_id FROM course_categories WHERE course_id = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $category_ids = [];
        while ($row = $result->fetch_assoc()) {
            $category_ids[] = $row['category_id'];
        }
        $stmt->close();
        return $category_ids;
    }

    /**
     * Xóa khóa học với quyền admin (có thể xóa cả khóa học đã có học viên)
     *
     * @param int $course_id ID của khóa học cần xóa
     * @return bool|string True nếu xóa thành công, string thông báo lỗi nếu thất bại
     */
    public function adminDeleteCourse($course_id)
    {
        // Bắt đầu một transaction
        $this->conn->begin_transaction();

        try {
            // 1. Xóa các liên kết trong course_categories
            $stmt_cat = $this->conn->prepare("DELETE FROM course_categories WHERE course_id = ?");
            $stmt_cat->bind_param("i", $course_id);
            $stmt_cat->execute();
            $stmt_cat->close();

            // 2. Xóa các bản ghi trong enrollments (admin có quyền xóa)
            $stmt_enroll = $this->conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
            $stmt_enroll->bind_param("i", $course_id);
            $stmt_enroll->execute();
            $stmt_enroll->close();

            // 3. Xóa các bản ghi trong lesson_progress liên quan đến các bài học của khóa này
            $stmt_lesson_ids = $this->conn->prepare("SELECT id FROM lessons WHERE course_id = ?");
            $stmt_lesson_ids->bind_param("i", $course_id);
            $stmt_lesson_ids->execute();
            $result_lessons = $stmt_lesson_ids->get_result();
            $lesson_ids = [];
            while ($row = $result_lessons->fetch_assoc()) {
                $lesson_ids[] = $row['id'];
            }
            $stmt_lesson_ids->close();

            if (!empty($lesson_ids)) {
                $placeholders = implode(',', array_fill(0, count($lesson_ids), '?'));
                $types = str_repeat('i', count($lesson_ids));
                $stmt_progress = $this->conn->prepare("DELETE FROM lesson_progress WHERE lesson_id IN ($placeholders)");
                $stmt_progress->bind_param($types, ...$lesson_ids);
                $stmt_progress->execute();
                $stmt_progress->close();
            }

            // 4. Xóa các bài học (lessons) thuộc khóa học
            $stmt_lessons = $this->conn->prepare("DELETE FROM lessons WHERE course_id = ?");
            $stmt_lessons->bind_param("i", $course_id);
            $stmt_lessons->execute();
            $stmt_lessons->close();

            // 5. Xóa khóa học khỏi bảng courses (admin không cần kiểm tra instructor_id)
            $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->bind_param("i", $course_id);
            $success = $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($success && $affected_rows > 0) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                error_log("Failed to delete course. Course ID: $course_id, Affected Rows: $affected_rows");
                return "Khóa học không tồn tại hoặc đã bị xóa trước đó.";
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error deleting course: " . $e->getMessage());
            return "Lỗi hệ thống khi xóa khóa học: " . $e->getMessage();
        }
    }

    /**
     * Lấy thông tin số lượng học viên đăng ký khóa học
     *
     * @param int $course_id ID của khóa học
     * @return int Số lượng học viên đã đăng ký
     */
    public function getEnrollmentCount($course_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        return $count;
    }
}
