<?php
class LessonManager {
    private $conn;

    public function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "elearning";

        $this->conn = mysqli_connect($servername, $username, $password, $dbname);
        if (!$this->conn) {
            die("Kết nối database thất bại: " . mysqli_connect_error());
        }
        mysqli_set_charset($this->conn, "utf8mb4");
    }

    public function getAllCourses() {
        $sql = "SELECT id, title FROM courses ORDER BY title";
        $result = mysqli_query($this->conn, $sql);
        $courses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        return json_encode($courses);
    }

    public function getLessonsByCourseId($course_id) {
        $course_id = (int)$course_id;
        $sql = "SELECT id, title, youtube_id FROM lessons WHERE course_id = $course_id ORDER BY id";
        $result = mysqli_query($this->conn, $sql);
        $lessons = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $lessons[] = $row;
        }
        return json_encode($lessons);
    }

    public function addLesson($title, $youtube_id, $course_id) {
        $title = trim($title);
        $youtube_id = trim($youtube_id);
        $course_id = (int)$course_id;

        if (empty($title) || empty($youtube_id) || !$course_id) {
            return ["success" => false, "message" => "Vui lòng điền đủ thông tin."];
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube_id)) {
            return ["success" => false, "message" => "YouTube ID không hợp lệ."];
        }

        $sql = "INSERT INTO lessons (title, youtube_id, course_id) VALUES ('$title', '$youtube_id', $course_id)";
        if (mysqli_query($this->conn, $sql)) {
            return ["success" => true, "message" => "Thêm bài học thành công."];
        } else {
            return ["success" => false, "message" => "Lỗi khi thêm bài học: " . mysqli_error($this->conn)];
        }
    }

    public function updateLesson($id, $title, $youtube_id) {
        $id = (int)$id;
        $title = trim($title);
        $youtube_id = trim($youtube_id);

        if (empty($title) || empty($youtube_id) || !$id) {
            return ["success" => false, "message" => "Vui lòng điền đủ thông tin."];
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtube_id)) {
            return ["success" => false, "message" => "YouTube ID không hợp lệ."];
        }

        $sql = "UPDATE lessons SET title = '$title', youtube_id = '$youtube_id' WHERE id = $id";
        if (mysqli_query($this->conn, $sql)) {
            return ["success" => true, "message" => "Cập nhật bài học thành công."];
        } else {
            return ["success" => false, "message" => "Lỗi khi cập nhật bài học: " . mysqli_error($this->conn)];
        }
    }

    public function deleteLesson($id) {
        $id = (int)$id;
        if (!$id) {
            return ["success" => false, "message" => "ID bài học không hợp lệ."];
        }

        $sql = "DELETE FROM lessons WHERE id = $id";
        if (mysqli_query($this->conn, $sql)) {
            return ["success" => true, "message" => "Xóa bài học thành công."];
        } else {
            return ["success" => false, "message" => "Lỗi khi xóa bài học: " . mysqli_error($this->conn)];
        }
    }

    public function __destruct() {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}
?>
