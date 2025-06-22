<?php
session_start();
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../classes/CourseManager.php';

requireLogin(['instructor', 'admin']);


$instructor_id = $_SESSION['user_id'];
$courseManager = new CourseManager();
$allCategories = $courseManager->getAllCategories();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_ids = isset($_POST['category_ids']) && is_array($_POST['category_ids']) ? $_POST['category_ids'] : [];

    // Validate input
    if (empty($title)) {
        $errors[] = "Tiêu đề không được để trống.";
    }
    if ($price === false || $price < 0) {
        $errors[] = "Giá không hợp lệ.";
    }
    if (empty($category_ids)) {
        $errors[] = "Vui lòng chọn ít nhất một danh mục.";
    }

    // Thumbnail upload
    $thumbnail_path = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/course_thumbnails/'; // Thay đổi đường dẫn nếu cần
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = uniqid('course_' . $instructor_id . '_') . '-' . basename($_FILES['thumbnail']['name']);
        $target_file = $upload_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['thumbnail']['tmp_name']);
        if ($check === false) {
            $errors[] = "Tệp không phải là hình ảnh.";
        }
        // Check file size (e.g., 5MB)
        if ($_FILES['thumbnail']['size'] > 5000000) {
            $errors[] = "Xin lỗi, tệp của bạn quá lớn (tối đa 5MB).";
        }
        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $errors[] = "Chỉ cho phép tải lên tệp JPG, JPEG, PNG & GIF.";
        }

        if (empty($errors) && move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
            $thumbnail_path = 'assets/uploads/course_thumbnails/' . $filename; // Đường dẫn lưu vào DB
        } elseif(empty($errors)) {
            $errors[] = "Xin lỗi, đã có lỗi khi tải lên tệp của bạn.";
        }
    } else if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Đã có lỗi với tệp thumbnail được tải lên. Mã lỗi: " . $_FILES['thumbnail']['error'];
    }

    if (empty($errors)) {
        $courseData = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'instructor_id' => $instructor_id,
            'thumbnail' => $thumbnail_path,
            'category_ids' => $category_ids
        ];

        $new_course_id = $courseManager->addCourse($courseData);

        if ($new_course_id) {
            $_SESSION['success_message'] = "Khóa học đã được thêm thành công!";
            header("Location: manage_courses.php");
            exit;
        } else {
            $errors[] = "Không thể thêm khóa học. Vui lòng thử lại.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Khóa học Mới</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; padding-top: 20px; }
        .container-custom { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .page-title { margin-bottom: 30px; text-align: center; color: #333; }
        .form-group label { font-weight: bold; }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #0069d9;
            color: #fff;
            padding: 0.375rem 0.75rem;
            margin-right: 0.25rem;
        }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.7);
            float: right;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container container-custom">
    <h1 class="page-title">Thêm Khóa học Mới</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="add_course.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Tiêu đề Khóa học <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Giá (VNĐ) <span class="text-danger">*</span></label>
            <input type="number" step="any" class="form-control" id="price" name="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0'; ?>" required>
        </div>

        <div class="form-group">
            <label for="category_ids">Danh mục <span class="text-danger">*</span> (Chọn một hoặc nhiều)</label>
            <select class="form-control select2-multiple" id="category_ids" name="category_ids[]" multiple="multiple" required>
                <?php foreach ($allCategories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php 
                        if (isset($_POST['category_ids']) && is_array($_POST['category_ids']) && in_array($category['id'], $_POST['category_ids'])) {
                            echo 'selected';
                        }
                        ?>
                    >
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="thumbnail">Ảnh Thumbnail</label>
            <input type="file" class="form-control-file" id="thumbnail" name="thumbnail" accept="image/png, image/jpeg, image/gif">
            <small class="form-text text-muted">Định dạng: JPG, JPEG, PNG, GIF. Kích thước tối đa: 5MB.</small>
        </div>

        <button type="submit" class="btn btn-primary">Thêm Khóa học</button>
        <a href="manage_courses.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder: "Chọn danh mục",
            allowClear: true,
            theme: 'bootstrap4' // Sử dụng theme bootstrap4 cho Select2
        });
    });
</script>

</body>
</html> 