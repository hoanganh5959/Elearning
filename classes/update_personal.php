<?php

require_once __DIR__ . '/../config.php';

class PersonalUpdater
{
    private $conn;

    public function __construct($conn = null)
    {
        if ($conn === null) {
            $this->conn = getDbConnection();
        } else {
            $this->conn = $conn;
        }
    }

    /**
     * Cập nhật thông tin cá nhân của người dùng
     * 
     * @param int $userId ID của người dùng
     * @param string $name Tên mới của người dùng
     * @param array $avatarFile File ảnh đại diện (từ $_FILES)
     * @return array Kết quả cập nhật ['success' => bool, 'message' => string, 'avatar' => string]
     */
    public function update($userId, $name, $avatarFile = null)
    {
        $result = [
            'success' => false,
            'message' => '',
            'avatar' => ''
        ];

        // Kiểm tra ID người dùng
        if (empty($userId)) {
            $result['message'] = 'ID người dùng không hợp lệ';
            return $result;
        }

        // Cập nhật tên
        if (!empty($name)) {
            $stmt = $this->conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmt->bind_param("si", $name, $userId);
            if (!$stmt->execute()) {
                $result['message'] = 'Lỗi khi cập nhật tên: ' . $stmt->error;
                return $result;
            }
        }

        // Xử lý ảnh đại diện nếu có
        if (!empty($avatarFile) && $avatarFile['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $this->uploadAvatar($avatarFile, $userId);
            if ($avatarPath === false) {
                $result['message'] = 'Lỗi khi tải lên ảnh đại diện';
                return $result;
            }

            // Cập nhật đường dẫn ảnh trong cơ sở dữ liệu
            $stmt = $this->conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
            $stmt->bind_param("si", $avatarPath, $userId);
            if (!$stmt->execute()) {
                $result['message'] = 'Lỗi khi cập nhật ảnh đại diện: ' . $stmt->error;
                return $result;
            }

            $result['avatar'] = $avatarPath;
        }

        $result['success'] = true;
        $result['message'] = 'Cập nhật thông tin thành công';
        return $result;
    }

    /**
     * Tải lên và xử lý ảnh đại diện
     * 
     * @param array $file Thông tin file ảnh từ $_FILES
     * @param int $userId ID của người dùng
     * @return string|false Đường dẫn tới ảnh đại diện hoặc false nếu có lỗi
     */
    private function uploadAvatar($file, $userId)
    {
        // Kiểm tra file có hợp lệ không
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            error_log("Avatar upload: Không có file tạm");
            return false;
        }

        // Kiểm tra loại file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            error_log("Avatar upload: Loại file không hợp lệ: " . $file['type']);
            return false;
        }

        // Tạo thư mục uploads/avatars nếu chưa tồn tại
        $uploadBaseDir = __DIR__ . '/../public/assets/uploads/';
        $avatarDir = $uploadBaseDir . 'avatars/';

        // Đảm bảo thư mục gốc tồn tại
        if (!file_exists($uploadBaseDir)) {
            if (!mkdir($uploadBaseDir, 0755, true)) {
                error_log("Avatar upload: Không thể tạo thư mục: " . $uploadBaseDir);
                return false;
            }
        }

        // Đảm bảo thư mục avatars tồn tại
        if (!file_exists($avatarDir)) {
            if (!mkdir($avatarDir, 0755, true)) {
                error_log("Avatar upload: Không thể tạo thư mục: " . $avatarDir);
                return false;
            }
        }

        // Tạo tên file duy nhất
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        $uploadPath = $avatarDir . $filename;

        // Tải lên file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("Avatar upload: Không thể di chuyển file từ " . $file['tmp_name'] . " đến " . $uploadPath);
            return false;
        }

        // Trả về đường dẫn tương đối để lưu vào database
        return 'assets/uploads/avatars/' . $filename;
    }
}
