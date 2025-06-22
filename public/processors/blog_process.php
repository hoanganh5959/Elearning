<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/manage_blog.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Bạn cần đăng nhập để thực hiện thao tác này.';
    header('Location: ../login.php');
    exit();
}

$blogManager = new BlogManager();
$redirect_to = $_POST['redirect_to'] ?? 'blogs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_blog':
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $categories = $_POST['categories'] ?? [];

            // Validate
            if (empty($title)) {
                $_SESSION['error_message'] = 'Tiêu đề không được để trống.';
                break;
            }

            if (empty($content)) {
                $_SESSION['error_message'] = 'Nội dung không được để trống.';
                break;
            }

            // Xử lý upload featured image
            $featured_image = null;
            if (!empty($_FILES['featured_image']['name'])) {
                $featured_image = handleImageUpload($_FILES['featured_image'], 'blog');
                if (!$featured_image) {
                    $_SESSION['error_message'] = 'Lỗi khi upload hình ảnh đại diện.';
                    break;
                }
            }

            $data = [
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'featured_image' => $featured_image,
                'author_id' => $_SESSION['user_id'],
                'categories' => $categories
            ];

            $blog_id = $blogManager->createBlog($data);

            if ($blog_id) {
                $_SESSION['success_message'] = 'Tạo bài viết thành công!';
                $redirect_to = 'blog-detail.php?id=' . $blog_id;
            } else {
                $_SESSION['error_message'] = 'Có lỗi khi tạo bài viết.';
            }
            break;

        case 'update_blog':
            $blog_id = $_POST['blog_id'] ?? 0;
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $categories = $_POST['categories'] ?? [];

            // Validate
            if (empty($title) || empty($content)) {
                $_SESSION['error_message'] = 'Tiêu đề và nội dung không được để trống.';
                break;
            }

            // Xử lý upload featured image mới
            $featured_image = $_POST['current_featured_image'] ?? null;
            if (!empty($_FILES['featured_image']['name'])) {
                $new_image = handleImageUpload($_FILES['featured_image'], 'blog');
                if ($new_image) {
                    // Xóa ảnh cũ nếu có
                    if ($featured_image && file_exists('../' . $featured_image)) {
                        unlink('../' . $featured_image);
                    }
                    $featured_image = $new_image;
                }
            }

            $data = [
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'featured_image' => $featured_image,
                'categories' => $categories
            ];

            if ($blogManager->updateBlog($blog_id, $data, $_SESSION['user_id'])) {
                $_SESSION['success_message'] = 'Cập nhật bài viết thành công!';
                $redirect_to = 'blog-detail.php?id=' . $blog_id;
            } else {
                $_SESSION['error_message'] = 'Có lỗi khi cập nhật bài viết hoặc bạn không có quyền.';
            }
            break;

        case 'delete_blog':
            $blog_id = $_POST['blog_id'] ?? 0;

            if ($blogManager->deleteBlog($blog_id, $_SESSION['user_id'])) {
                $_SESSION['success_message'] = 'Xóa bài viết thành công!';
                $redirect_to = 'my-blogs.php';
            } else {
                $_SESSION['error_message'] = 'Có lỗi khi xóa bài viết hoặc bạn không có quyền.';
            }
            break;

        default:
            $_SESSION['error_message'] = 'Thao tác không hợp lệ.';
            break;
    }
}

// Redirect
header('Location: ../' . $redirect_to);
exit();

/**
 * Xử lý upload hình ảnh
 */
function handleImageUpload($file, $type = 'blog')
{
    $upload_dir = '../assets/uploads/' . $type . '/';

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }

    // Kiểm tra kích thước file (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return 'assets/uploads/' . $type . '/' . $filename;
    }

    return false;
}
