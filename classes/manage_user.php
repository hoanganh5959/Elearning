<?php
class UserManager
{
    private $conn;

    public function __construct()
    {
        require_once __DIR__ . '/../config.php';
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Kiểm tra xem cột status có tồn tại không
     */
    private function hasStatusColumn()
    {
        $result = $this->conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        return $result->num_rows > 0;
    }

    /**
     * Lấy tất cả người dùng với phân trang
     */
    public function getAllUsers($page = 1, $limit = 10, $search = '')
    {
        $offset = ($page - 1) * $limit;

        $searchCondition = '';
        $params = [];
        $types = '';

        if (!empty($search)) {
            $searchCondition = "WHERE u.username LIKE ? OR u.name LIKE ? OR u.email LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
            $types = 'sss';
        }

        $statusField = $this->hasStatusColumn() ? "u.status," : "'active' as status,";

        $sql = "SELECT u.user_id, u.username, u.name, u.email, u.avatar, u.role, u.created_at, {$statusField}
                       COUNT(DISTINCT c.id) as course_count,
                       COUNT(DISTINCT e.id) as enrollment_count
                FROM users u
                LEFT JOIN courses c ON u.user_id = c.instructor_id
                LEFT JOIN enrollments e ON u.user_id = e.user_id
                {$searchCondition}
                GROUP BY u.user_id
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Đếm tổng số người dùng
     */
    public function getTotalUsers($search = '')
    {
        $searchCondition = '';
        $params = [];
        $types = '';

        if (!empty($search)) {
            $searchCondition = "WHERE username LIKE ? OR name LIKE ? OR email LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
            $types = 'sss';
        }

        $sql = "SELECT COUNT(*) as total FROM users {$searchCondition}";
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    /**
     * Lấy thông tin người dùng theo ID
     */
    public function getUserById($user_id)
    {
        $statusField = $this->hasStatusColumn() ? "status," : "'active' as status,";
        $sql = "SELECT user_id, username, name, email, avatar, password, role, {$statusField} created_at FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Thêm người dùng mới
     */
    public function addUser($data)
    {
        $username = $data['username'];
        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'];
        $status = isset($data['status']) ? $data['status'] : 'active';

        // Kiểm tra username và email đã tồn tại chưa
        if ($this->checkUserExists($username, $email)) {
            return false;
        }

        if ($this->hasStatusColumn()) {
            $sql = "INSERT INTO users (username, name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $username, $name, $email, $password, $role, $status);
        } else {
            $sql = "INSERT INTO users (username, name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $name, $email, $password, $role);
        }

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function updateUser($user_id, $data)
    {
        $username = $data['username'];
        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];
        $status = isset($data['status']) ? $data['status'] : 'active';

        // Kiểm tra username và email đã tồn tại chưa (trừ chính user này)
        if ($this->checkUserExists($username, $email, $user_id)) {
            return false;
        }

        if ($this->hasStatusColumn()) {
            $sql = "UPDATE users SET username = ?, name = ?, email = ?, role = ?, status = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssi", $username, $name, $email, $role, $status, $user_id);
        } else {
            $sql = "UPDATE users SET username = ?, name = ?, email = ?, role = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $name, $email, $role, $user_id);
        }

        return $stmt->execute();
    }

    /**
     * Đổi mật khẩu người dùng
     */
    public function changePassword($user_id, $new_password)
    {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_password, $user_id);

        return $stmt->execute();
    }

    /**
     * Xóa người dùng (soft delete hoặc hard delete)
     */
    public function deleteUser($user_id)
    {
        // Kiểm tra xem user có phải admin không
        $user = $this->getUserById($user_id);
        if (!$user || $user['role'] === 'admin') {
            return false; // Không cho phép xóa admin
        }

        // Bắt đầu transaction
        $this->conn->begin_transaction();

        try {
            // Xóa các liên kết trong google_users
            $stmt = $this->conn->prepare("DELETE FROM google_users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Xóa các enrollment
            $stmt = $this->conn->prepare("DELETE FROM enrollments WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Xóa lesson progress
            $stmt = $this->conn->prepare("DELETE FROM lesson_progress WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Nếu là instructor, xử lý các khóa học
            if ($user['role'] === 'instructor') {
                $stmt = $this->conn->prepare("SELECT id FROM courses WHERE instructor_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $courses = $stmt->get_result();

                while ($course = $courses->fetch_assoc()) {
                    $this->deleteCourseData($course['id']);
                }

                $stmt = $this->conn->prepare("DELETE FROM courses WHERE instructor_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }

            // Cuối cùng xóa user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Thay đổi trạng thái tài khoản (active/inactive)
     */
    public function toggleUserStatus($user_id)
    {
        if (!$this->hasStatusColumn()) {
            return false; // Không thể thay đổi status nếu cột không tồn tại
        }

        $user = $this->getUserById($user_id);
        if (!$user || $user['role'] === 'admin') {
            return false; // Không cho phép khóa admin
        }

        $currentStatus = $user['status'] ?? 'active';
        $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

        $stmt = $this->conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newStatus, $user_id);

        return $stmt->execute();
    }

    /**
     * Kiểm tra username hoặc email đã tồn tại
     */
    private function checkUserExists($username, $email, $exclude_id = null)
    {
        $sql = "SELECT user_id FROM users WHERE (username = ? OR email = ?)";
        $params = [$username, $email];
        $types = "ss";

        if ($exclude_id) {
            $sql .= " AND user_id != ?";
            $params[] = $exclude_id;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Xóa dữ liệu liên quan đến khóa học
     */
    private function deleteCourseData($course_id)
    {
        // Xóa lesson progress của course
        $stmt = $this->conn->prepare("DELETE lp FROM lesson_progress lp 
                                     INNER JOIN lessons l ON lp.lesson_id = l.id 
                                     WHERE l.course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();

        // Xóa lessons
        $stmt = $this->conn->prepare("DELETE FROM lessons WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();

        // Xóa course_categories
        $stmt = $this->conn->prepare("DELETE FROM course_categories WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();

        // Xóa enrollments
        $stmt = $this->conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
    }

    /**
     * Lấy thống kê người dùng
     */
    public function getUserStats()
    {
        if ($this->hasStatusColumn()) {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
                        SUM(CASE WHEN role = 'instructor' THEN 1 ELSE 0 END) as instructors,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users
                    FROM users";
        } else {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
                        SUM(CASE WHEN role = 'instructor' THEN 1 ELSE 0 END) as instructors,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        COUNT(*) as active_users,
                        0 as inactive_users
                    FROM users";
        }

        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    /**
     * Lấy danh sách roles
     */
    public function getRoles()
    {
        return ['student', 'instructor', 'admin'];
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
