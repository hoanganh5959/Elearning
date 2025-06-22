<?php
require_once __DIR__ . '/../public/includes/db.php';

class BlogManager
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả bài viết blog với phân trang
     */
    public function getAllBlogs($page = 1, $limit = 10, $status = 'published', $category_id = null)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT b.*, u.name as author_name, u.avatar as author_avatar, 
                       COUNT(c.id) as comment_count
                FROM blogs b 
                LEFT JOIN users u ON b.author_id = u.user_id 
                LEFT JOIN blog_comments c ON b.id = c.blog_id AND c.status = 'approved'";

        $conditions = [];
        $params = [];
        $types = "";

        if ($status) {
            $conditions[] = "b.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if ($category_id) {
            $sql .= " LEFT JOIN blog_category_relations bcr ON b.id = bcr.blog_id";
            $conditions[] = "bcr.category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $blogs = [];
        while ($row = $result->fetch_assoc()) {
            $row['categories'] = $this->getBlogCategories($row['id']);
            $blogs[] = $row;
        }

        return $blogs;
    }

    /**
     * Lấy chi tiết bài viết theo ID
     */
    public function getBlogById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.name as author_name, u.avatar as author_avatar, u.role as author_role
            FROM blogs b 
            LEFT JOIN users u ON b.author_id = u.user_id 
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $blog = $result->fetch_assoc();
            $blog['categories'] = $this->getBlogCategories($blog['id']);
            return $blog;
        }

        return null;
    }

    /**
     * Lấy bài viết của người dùng
     */
    public function getBlogsByUser($user_id, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $stmt = $this->conn->prepare("
            SELECT b.*, COUNT(c.id) as comment_count
            FROM blogs b 
            LEFT JOIN blog_comments c ON b.id = c.blog_id AND c.status = 'approved'
            WHERE b.author_id = ?
            GROUP BY b.id 
            ORDER BY b.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $blogs = [];
        while ($row = $result->fetch_assoc()) {
            $row['categories'] = $this->getBlogCategories($row['id']);
            $blogs[] = $row;
        }

        return $blogs;
    }

    /**
     * Tạo bài viết mới
     */
    public function createBlog($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO blogs (title, content, excerpt, featured_image, author_id, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $excerpt = $this->generateExcerpt($data['content']);
        $featured_image = $data['featured_image'] ?? null;
        $status = $data['status'] ?? 'draft';

        $stmt->bind_param(
            "ssssii",
            $data['title'],
            $data['content'],
            $excerpt,
            $featured_image,
            $data['author_id'],
            $status
        );

        if ($stmt->execute()) {
            $blog_id = $this->conn->insert_id;

            // Thêm categories nếu có
            if (!empty($data['categories'])) {
                $this->updateBlogCategories($blog_id, $data['categories']);
            }

            return $blog_id;
        }

        return false;
    }

    /**
     * Cập nhật bài viết
     */
    public function updateBlog($blog_id, $data, $user_id)
    {
        // Kiểm tra quyền sở hữu
        if (!$this->canEditBlog($blog_id, $user_id)) {
            return false;
        }

        $excerpt = $this->generateExcerpt($data['content']);
        $featured_image = $data['featured_image'] ?? null;
        $status = $data['status'] ?? 'draft';

        $stmt = $this->conn->prepare("
            UPDATE blogs 
            SET title = ?, content = ?, excerpt = ?, featured_image = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssssi",
            $data['title'],
            $data['content'],
            $excerpt,
            $featured_image,
            $status,
            $blog_id
        );

        if ($stmt->execute()) {
            // Cập nhật categories
            if (isset($data['categories'])) {
                $this->updateBlogCategories($blog_id, $data['categories']);
            }
            return true;
        }

        return false;
    }

    /**
     * Xóa bài viết
     */
    public function deleteBlog($blog_id, $user_id = null)
    {
        // Nếu có user_id, kiểm tra quyền sở hữu (cho user thường)
        if ($user_id !== null && !$this->canEditBlog($blog_id, $user_id)) {
            return false;
        }

        // Xóa categories liên quan
        $this->conn->query("DELETE FROM blog_category_relations WHERE blog_id = $blog_id");

        // Xóa comments liên quan
        $this->conn->query("DELETE FROM blog_comments WHERE blog_id = $blog_id");

        // Xóa bài viết
        $stmt = $this->conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param("i", $blog_id);

        return $stmt->execute();
    }

    /**
     * Kiểm tra quyền chỉnh sửa bài viết
     */
    public function canEditBlog($blog_id, $user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT author_id FROM blogs WHERE id = ?
        ");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $blog = $result->fetch_assoc();
            return $blog['author_id'] == $user_id;
        }

        return false;
    }

    /**
     * Tăng view count
     */
    public function incrementViewCount($blog_id)
    {
        $stmt = $this->conn->prepare("UPDATE blogs SET view_count = view_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
    }

    /**
     * Lấy danh mục của bài viết
     */
    public function getBlogCategories($blog_id)
    {
        $stmt = $this->conn->prepare("
            SELECT bc.* 
            FROM blog_categories bc
            JOIN blog_category_relations bcr ON bc.id = bcr.category_id
            WHERE bcr.blog_id = ?
        ");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Lấy tất cả danh mục blog
     */
    public function getAllCategories()
    {
        $stmt = $this->conn->prepare("
            SELECT bc.*, COUNT(bcr.blog_id) as blog_count
            FROM blog_categories bc
            LEFT JOIN blog_category_relations bcr ON bc.id = bcr.category_id
            LEFT JOIN blogs b ON bcr.blog_id = b.id AND b.status = 'published'
            GROUP BY bc.id
            ORDER BY bc.name
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Cập nhật danh mục cho bài viết
     */
    private function updateBlogCategories($blog_id, $categories)
    {
        // Xóa danh mục cũ
        $stmt = $this->conn->prepare("DELETE FROM blog_category_relations WHERE blog_id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();

        // Thêm danh mục mới
        if (!empty($categories)) {
            $stmt = $this->conn->prepare("INSERT INTO blog_category_relations (blog_id, category_id) VALUES (?, ?)");
            foreach ($categories as $category_id) {
                $stmt->bind_param("ii", $blog_id, $category_id);
                $stmt->execute();
            }
        }
    }

    /**
     * Tạo excerpt từ content
     */
    private function generateExcerpt($content, $length = 200)
    {
        $text = strip_tags($content);
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    /**
     * Tìm kiếm bài viết
     */
    public function searchBlogs($keyword, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $stmt = $this->conn->prepare("
            SELECT b.*, u.name as author_name, u.avatar as author_avatar,
                   COUNT(c.id) as comment_count
            FROM blogs b 
            LEFT JOIN users u ON b.author_id = u.user_id 
            LEFT JOIN blog_comments c ON b.id = c.blog_id AND c.status = 'approved'
            WHERE b.status = 'published' AND (b.title LIKE ? OR b.content LIKE ?)
            GROUP BY b.id 
            ORDER BY b.created_at DESC 
            LIMIT ? OFFSET ?
        ");

        $search_term = "%$keyword%";
        $stmt->bind_param("ssii", $search_term, $search_term, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $blogs = [];
        while ($row = $result->fetch_assoc()) {
            $row['categories'] = $this->getBlogCategories($row['id']);
            $blogs[] = $row;
        }

        return $blogs;
    }

    /**
     * Đếm tổng số bài viết
     */
    public function countBlogs($status = 'published', $category_id = null, $user_id = null, $keyword = null)
    {
        $sql = "SELECT COUNT(DISTINCT b.id) as total FROM blogs b";
        $conditions = [];
        $params = [];
        $types = "";

        if ($category_id) {
            $sql .= " JOIN blog_category_relations bcr ON b.id = bcr.blog_id";
            $conditions[] = "bcr.category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if ($status) {
            $conditions[] = "b.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if ($user_id) {
            $conditions[] = "b.author_id = ?";
            $params[] = $user_id;
            $types .= "i";
        }

        if ($keyword) {
            $conditions[] = "(b.title LIKE ? OR b.content LIKE ?)";
            $search_term = "%$keyword%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    /**
     * Cập nhật trạng thái bài viết (cho admin)
     */
    public function updateBlogStatus($blog_id, $status)
    {
        if (!in_array($status, ['draft', 'published', 'private'])) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE blogs SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $blog_id);
        return $stmt->execute();
    }

    /**
     * Lấy tất cả bài viết cho admin
     */
    public function getAllBlogsForAdmin($page = 1, $limit = 10, $search = '', $status_filter = '')
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT b.*, u.name as author_name, u.avatar as author_avatar
                FROM blogs b 
                LEFT JOIN users u ON b.author_id = u.user_id";

        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($search)) {
            $conditions[] = "(b.title LIKE ? OR b.content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        if (!empty($status_filter)) {
            $conditions[] = "b.status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $blogs = [];
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }

        return $blogs;
    }

    /**
     * Đếm tổng số blog cho admin
     */
    public function getTotalBlogs($search = '', $status_filter = '')
    {
        $sql = "SELECT COUNT(*) as total FROM blogs b";

        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($search)) {
            $conditions[] = "(b.title LIKE ? OR b.content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        if (!empty($status_filter)) {
            $conditions[] = "b.status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

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
     * Lấy thống kê blog cho admin
     */
    public function getBlogStats()
    {
        $stats = [
            'total' => 0,
            'published' => 0,
            'draft' => 0,
            'private' => 0
        ];

        // Tổng số blog
        $result = $this->conn->query("SELECT COUNT(*) as total FROM blogs");
        $stats['total'] = $result->fetch_assoc()['total'];

        // Theo trạng thái
        $result = $this->conn->query("SELECT status, COUNT(*) as count FROM blogs GROUP BY status");
        while ($row = $result->fetch_assoc()) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = $row['count'];
            }
        }

        return $stats;
    }
}
