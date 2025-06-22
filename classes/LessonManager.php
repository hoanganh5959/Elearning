<?php
require_once __DIR__ . '/../public/includes/db.php';
class LessonManager
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllCourses()
    {
        $stmt = $this->conn->prepare("SELECT id, title FROM courses ORDER BY id");
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        $stmt->close();
        return json_encode($courses);
    }

    public function getLessonsByCourseId($course_id)
    {
        $stmt = $this->conn->prepare("SELECT id, title, youtube_id FROM lessons WHERE course_id = ? ORDER BY id");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }
        $stmt->close();
        return json_encode($lessons);
    }

    /**
     * Kiểm tra quyền sở hữu khóa học
     * @param int $course_id ID khóa học
     * @param int $instructor_id ID instructor
     * @return bool
     */
    public function checkCourseOwnership($course_id, $instructor_id)
    {
        $stmt = $this->conn->prepare("SELECT id FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $course_id, $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Kiểm tra quyền sở hữu bài học
     * @param int $lesson_id ID bài học
     * @param int $instructor_id ID instructor
     * @return bool
     */
    public function checkLessonOwnership($lesson_id, $instructor_id)
    {
        $stmt = $this->conn->prepare("
            SELECT l.id 
            FROM lessons l 
            JOIN courses c ON l.course_id = c.id 
            WHERE l.id = ? AND c.instructor_id = ?
        ");
        $stmt->bind_param("ii", $lesson_id, $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Thêm bài học mới (updated signature)
     * @param array $data Dữ liệu bài học
     * @return bool
     */
    public function addLesson($data)
    {
        $title = trim($data['title']);
        $content = trim($data['content'] ?? '');
        $video_url = trim($data['video_url'] ?? '');
        $course_id = $data['course_id'];
        $sort_order = $data['lesson_order'] ?? 1;

        if (empty($title) || !is_numeric($course_id)) {
            error_log("LessonManager::addLesson - Validation failed: title=" . $title . ", course_id=" . $course_id);
            return false;
        }

        // Extract YouTube ID if it's a full URL
        $youtube_id = $this->extractYouTubeId($video_url);

        $stmt = $this->conn->prepare("
            INSERT INTO lessons (title, content, youtube_id, course_id, sort_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("sssii", $title, $content, $youtube_id, $course_id, $sort_order);
        
        $success = $stmt->execute();
        if (!$success) {
            error_log("LessonManager::addLesson - Database error: " . $this->conn->error);
        }
        $stmt->close();
        return $success;
    }

    /**
     * Cập nhật bài học (updated signature)
     * @param int $lesson_id ID bài học
     * @param array $data Dữ liệu cập nhật
     * @return bool
     */
    public function updateLesson($lesson_id, $data)
    {
        $title = trim($data['title']);
        $content = trim($data['content'] ?? '');
        $video_url = trim($data['video_url'] ?? '');
        $sort_order = $data['lesson_order'] ?? 1;

        if (empty($title) || !is_numeric($lesson_id)) {
            error_log("LessonManager::updateLesson - Validation failed: title=" . $title . ", lesson_id=" . $lesson_id);
            return false;
        }

        // Extract YouTube ID if it's a full URL
        $youtube_id = $this->extractYouTubeId($video_url);

        $stmt = $this->conn->prepare("
            UPDATE lessons 
            SET title = ?, content = ?, youtube_id = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssii", $title, $content, $youtube_id, $sort_order, $lesson_id);
        $success = $stmt->execute();
        if (!$success) {
            error_log("LessonManager::updateLesson - Database error: " . $this->conn->error);
        }
        $stmt->close();
        return $success;
    }

    /**
     * Xóa bài học (updated to return bool)
     * @param int $lesson_id ID bài học
     * @return bool
     */
    public function deleteLesson($lesson_id)
    {
        if (!is_numeric($lesson_id)) {
            error_log("LessonManager::deleteLesson - Validation failed: lesson_id=" . $lesson_id);
            return false;
        }
        
        $stmt = $this->conn->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->bind_param("i", $lesson_id);
        $success = $stmt->execute();
        if (!$success) {
            error_log("LessonManager::deleteLesson - Database error: " . $this->conn->error);
        }
        $stmt->close();
        return $success;
    }

    /**
     * Extract YouTube ID từ URL hoặc trả về như cũ nếu đã là ID
     * @param string $video_url URL hoặc ID YouTube
     * @return string|null YouTube ID hoặc null
     */
    private function extractYouTubeId($video_url)
    {
        if (empty($video_url) || trim($video_url) === '') {
            return null;
        }

        $video_url = trim($video_url);

        // Nếu đã là YouTube ID (11 ký tự)
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_url)) {
            return $video_url;
        }

        // Extract từ các dạng URL YouTube khác nhau
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $video_url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    // Legacy methods để tương thích ngược
    public function addLessonLegacy($title, $youtube_id, $course_id)
    {
        $title = trim($title);
        $youtube_id = trim($youtube_id);
        if (empty($title) || empty($youtube_id) || !is_numeric($course_id)) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin."];
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube_id)) {
            return ["success" => false, "message" => "YouTube ID không hợp lệ."];
        }
        $stmt = $this->conn->prepare("INSERT INTO lessons (title, youtube_id, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $youtube_id, $course_id);
        if ($stmt->execute()) {
            $stmt->close();
            return ["success" => true, "message" => "Thêm bài học thành công."];
        } else {
            $stmt->close();
            return ["success" => false, "message" => "Lỗi khi thêm bài học: " . $this->conn->error];
        }
    }

    public function updateLessonLegacy($id, $title, $youtube_id)
    {
        $title = trim($title);
        $youtube_id = trim($youtube_id);
        if (empty($title) || empty($youtube_id) || !is_numeric($id)) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin."];
        }
        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube_id)) {
            return ["success" => false, "message" => "YouTube ID không hợp lệ."];
        }
        $stmt = $this->conn->prepare("UPDATE lessons SET title = ?, youtube_id = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $youtube_id, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ["success" => true, "message" => "Cập nhật bài học thành công."];
        } else {
            $stmt->close();
            return ["success" => false, "message" => "Lỗi khi cập nhật bài học: " . $this->conn->error];
        }
    }

    /**
     * Lấy trạng thái hoàn thành của các bài học trong một khóa học
     * @param int $userId ID của người dùng
     * @param int $courseId ID của khóa học
     * @return array Mảng trạng thái hoàn thành của các bài học, với khóa là lesson_id
     */
    public function getLessonCompletionStatus($userId, $courseId) {
        if (!$userId || !$courseId) {
            return [];
        }
        
        $result = [];
        
        try {
            // Kiểm tra xem bảng lesson_progress đã tồn tại chưa
            $query = "SHOW TABLES LIKE 'lesson_progress'";
            $tableCheck = $this->conn->query($query);
            
            if ($tableCheck->num_rows == 0) {
                return []; // Bảng không tồn tại
            }
            
            // Sử dụng bảng lesson_progress thay vì lesson_completions
            $query = "SELECT lp.lesson_id, lp.completed, lp.completed_at 
                     FROM lesson_progress lp
                     INNER JOIN lessons l ON l.id = lp.lesson_id
                     WHERE lp.user_id = ? AND l.course_id = ?";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $userId, $courseId);
            $stmt->execute();
            
            $queryResult = $stmt->get_result();
            while ($row = $queryResult->fetch_assoc()) {
                $result[$row['lesson_id']] = [
                    'completed' => (bool)$row['completed'],
                    'completed_at' => $row['completed_at']
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting lesson completion status: " . $e->getMessage());
            return [];
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

