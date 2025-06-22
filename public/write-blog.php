<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../classes/manage_blog.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Bạn cần đăng nhập để viết blog.';
    header('Location: login.php?redirect=write-blog.php');
    exit();
}

$blogManager = new BlogManager();

// Lấy danh mục
$categories = $blogManager->getAllCategories();

// Xử lý edit mode
$edit_mode = false;
$blog_data = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $blog_id = $_GET['edit'];
    $blog_data = $blogManager->getBlogById($blog_id);

    if (!$blog_data || !$blogManager->canEditBlog($blog_id, $_SESSION['user_id'])) {
        $_SESSION['error_message'] = 'Bạn không có quyền chỉnh sửa bài viết này.';
        header('Location: blogs.php');
        exit();
    }

    $edit_mode = true;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title><?= $edit_mode ? 'Chỉnh sửa bài viết' : 'Viết bài mới' ?> - eLEARNING</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Favicon -->
    <link href="assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Quill.js - Hoàn toàn miễn phí, hỗ trợ drag & drop images -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Editor container styling */
        #editor-container {
            height: 450px;
            border: 2px solid #e3f2fd;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #editor-container:focus-within {
            border-color: #2196f3;
            box-shadow: 0 4px 20px rgba(33, 150, 243, 0.15);
        }

        /* Toolbar styling */
        .ql-toolbar {
            border: none !important;
            border-bottom: 2px solid #f5f5f5 !important;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px 16px;
        }

        .ql-toolbar .ql-formats {
            margin-right: 15px;
        }

        .ql-toolbar button {
            border-radius: 6px;
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .ql-toolbar button:hover {
            background: rgba(33, 150, 243, 0.1);
            transform: translateY(-1px);
        }

        .ql-toolbar button.ql-active {
            background: #2196f3;
            color: white;
        }

        /* Editor content area */
        .ql-container {
            border: none !important;
            border-radius: 0 0 12px 12px;
            background: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .ql-editor {
            min-height: 350px;
            font-size: 15px;
            line-height: 1.7;
            color: #333;
            background: #ffffff;
            padding: 20px 24px;
        }

        .ql-editor.ql-blank::before {
            color: #999;
            font-style: italic;
            font-size: 15px;
        }

        /* Form styling improvements */
        .form-control,
        .form-select {
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .form-label i {
            font-size: 16px;
        }

        /* Card styling */
        .bg-light {
            background: #ffffff !important;
            border: 1px solid #e3f2fd;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        /* Button improvements */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Image preview styling */
        #imagePreview {
            border: 2px dashed #e3f2fd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #fafafa;
        }

        #imagePreview img {
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            #editor-container {
                height: 350px;
            }

            .ql-editor {
                min-height: 250px;
                padding: 15px;
                font-size: 14px;
            }

            .ql-toolbar {
                padding: 8px 12px;
            }
        }

        /* Custom scrollbar for editor */
        .ql-editor::-webkit-scrollbar {
            width: 8px;
        }

        .ql-editor::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .ql-editor::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .ql-editor::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body>
    <!-- Navbar Start -->
    <?php include 'includes/header.php'; ?>
    <!-- Navbar End -->

    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 page-header">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="display-3 text-white animated slideInDown">
                        <?= $edit_mode ? 'Chỉnh sửa bài viết' : 'Viết bài mới' ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a class="text-white" href="index.php">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a class="text-white" href="blogs.php">Blog</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">
                                <?= $edit_mode ? 'Chỉnh sửa' : 'Viết bài' ?>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Write Blog Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <!-- Flash Messages -->
            <?= displayFlashMessages() ?>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="bg-light rounded p-5">
                        <form method="POST" action="processors/blog_process.php" enctype="multipart/form-data" id="blogForm">
                            <input type="hidden" name="action" value="<?= $edit_mode ? 'update_blog' : 'create_blog' ?>">
                            <input type="hidden" name="redirect_to" value="<?= $edit_mode ? 'blog-detail.php?id=' . $blog_data['id'] : 'blogs.php' ?>">

                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="blog_id" value="<?= $blog_data['id'] ?>">
                                <input type="hidden" name="current_featured_image" value="<?= htmlspecialchars($blog_data['featured_image'] ?? '') ?>">
                            <?php endif; ?>

                            <!-- Tiêu đề -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading text-primary me-2"></i>Tiêu đề bài viết
                                    </label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?= htmlspecialchars($blog_data['title'] ?? '') ?>"
                                        placeholder="Nhập tiêu đề hấp dẫn cho bài viết..." required>
                                </div>
                            </div>

                            <!-- Danh mục và Trạng thái -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label for="categories" class="form-label">
                                        <i class="fas fa-tags text-primary me-2"></i>Danh mục
                                    </label>
                                    <select class="form-select" id="categories" name="categories[]" multiple>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"
                                                <?php if ($edit_mode): ?>
                                                <?php foreach ($blog_data['categories'] as $blog_cat): ?>
                                                <?= $cat['id'] == $blog_cat['id'] ? 'selected' : '' ?>
                                                <?php endforeach; ?>
                                                <?php endif; ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Giữ Ctrl để chọn nhiều danh mục</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-eye text-primary me-2"></i>Trạng thái
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?= (!$edit_mode || $blog_data['status'] == 'draft') ? 'selected' : '' ?>>
                                            Bản nháp
                                        </option>
                                        <option value="published" <?= ($edit_mode && $blog_data['status'] == 'published') ? 'selected' : '' ?>>
                                            Công khai
                                        </option>
                                        <option value="private" <?= ($edit_mode && $blog_data['status'] == 'private') ? 'selected' : '' ?>>
                                            Riêng tư
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Hình ảnh đại diện -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="featured_image" class="form-label">
                                        <i class="fas fa-image text-primary me-2"></i>Hình ảnh đại diện
                                    </label>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image"
                                        accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">Chọn hình ảnh JPG, PNG, GIF hoặc WebP (tối đa 5MB)</div>

                                    <!-- Preview ảnh -->
                                    <div id="imagePreview" class="mt-3" style="display: <?= ($edit_mode && $blog_data['featured_image']) ? 'block' : 'none' ?>">
                                        <img id="previewImg" src="<?= htmlspecialchars($blog_data['featured_image'] ?? '') ?>"
                                            class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                        <br>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                            <i class="fas fa-trash"></i> Xóa ảnh
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Nội dung -->
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="content" class="form-label">
                                        <i class="fas fa-edit text-primary me-2"></i>Nội dung bài viết
                                    </label>
                                    <div id="editor-container">
                                        <div class="ql-editor">
                                            <?= htmlspecialchars($blog_data['content'] ?? '') ?>
                                        </div>
                                    </div>
                                    <!-- Hidden input để submit content -->
                                    <input type="hidden" id="content" name="content">
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="row">
                                <div class="col-12 text-end">
                                    <a href="<?= $edit_mode ? 'blog-detail.php?id=' . $blog_data['id'] : 'blogs.php' ?>"
                                        class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                    <button type="submit" name="status" value="draft" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-save me-2"></i>Lưu nháp
                                    </button>
                                    <button type="submit" name="status" value="published" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i><?= $edit_mode ? 'Cập nhật' : 'Xuất bản' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Write Blog End -->

    <!-- Footer Start -->
    <?php include 'includes/footer.php'; ?>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Biến lưu trữ instance Quill
        let quill;

        // Khởi tạo Quill.js
        document.addEventListener('DOMContentLoaded', function() {
            // Định nghĩa toolbar với đầy đủ tính năng
            const toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'], // toggled buttons
                ['blockquote', 'code-block'],

                [{
                    'header': 1
                }, {
                    'header': 2
                }], // custom button values
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }], // superscript/subscript
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }], // outdent/indent
                [{
                    'direction': 'rtl'
                }], // text direction

                [{
                    'size': ['small', false, 'large', 'huge']
                }], // custom dropdown
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],

                [{
                    'color': []
                }, {
                    'background': []
                }], // dropdown with defaults from theme
                [{
                    'font': []
                }],
                [{
                    'align': []
                }],

                ['link', 'image', 'video'], // link and image, video
                ['clean'] // remove formatting button
            ];

            quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Nhập nội dung bài viết của bạn...',
                scrollingContainer: '#editor-container'
            });

            // Set nội dung ban đầu nếu đang edit
            <?php if ($edit_mode && !empty($blog_data['content'])): ?>
                quill.root.innerHTML = <?= json_encode($blog_data['content']) ?>;
            <?php endif; ?>

            console.log('Quill.js đã khởi tạo thành công!');
        });

        // Preview hình ảnh
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Xóa hình ảnh
        function removeImage() {
            $('#featured_image').val('');
            $('#imagePreview').hide();
            $('#previewImg').attr('src', '');
        }

        // Xử lý submit form
        $('#blogForm').on('submit', function(e) {
            // Lấy content từ Quill và set vào hidden input
            if (quill) {
                const content = quill.root.innerHTML;
                $('#content').val(content);
            }

            // Kiểm tra tiêu đề
            if (!$('#title').val().trim()) {
                e.preventDefault();
                alert('Vui lòng nhập tiêu đề bài viết!');
                return false;
            }

            // Kiểm tra nội dung Quill.js
            if (!quill || !quill.getText().trim()) {
                e.preventDefault();
                alert('Vui lòng nhập nội dung bài viết!');
                return false;
            }

            // Disable submit button để tránh double submit
            $(this).find('button[type="submit"]').prop('disabled', true);
        });

        // Tự động lưu draft mỗi 2 phút
        setInterval(function() {
            if (quill && quill.getText().trim()) {
                console.log('Auto saving draft...');
                // Có thể implement auto-save ở đây
            }
        }, 120000); // 2 phút
    </script>
</body>

</html>