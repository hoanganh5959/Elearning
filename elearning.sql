-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2025 at 04:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elearning`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('draft','published','private') DEFAULT 'draft',
  `view_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Giáo dục', 'giao-duc', 'Bài viết về giáo dục và học tập', '2025-05-25 03:14:23'),
(2, 'Công nghệ', 'cong-nghe', 'Tin tức và kiến thức về công nghệ', '2025-05-25 03:14:23'),
(3, 'Lập trình', 'lap-trinh', 'Hướng dẫn và chia sẻ về lập trình', '2025-05-25 03:14:23'),
(4, 'Thiết kế', 'thiet-ke', 'Xu hướng và kỹ thuật thiết kế', '2025-05-25 03:14:23'),
(5, 'Kinh nghiệm', 'kinh-nghiem', 'Chia sẻ kinh nghiệm học tập và làm việc', '2025-05-25 03:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `blog_category_relations`
--

CREATE TABLE `blog_category_relations` (
  `blog_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Web Design'),
(2, 'Graphic Design'),
(3, 'Video Editing'),
(4, 'Online Marketing');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `thumbnail` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `price`, `instructor_id`, `created_at`, `thumbnail`) VALUES
(3, 'Web Design 101', 'Free Full Course', 0.00, 1, '2025-04-18 15:02:13', 'assets/uploads/course/web-design-coursse.png'),
(4, 'Digital Marketing Course', 'Everything You Need To Know', 100000.00, 2, '2025-04-19 00:02:33', 'assets/uploads/course/digital-marketing-course.png'),
(5, 'Beginners Guide to Graphic Design', '45 Episode FREE Series', 100000.00, 1, '2025-05-22 16:19:57', 'assets/uploads/course/Graphic-design-course.jpg'),
(8, 'âsc', 'How To Edit Trending Reels In 2025 (Full Adobe Tutorial)', 10000.00, 2, '2025-05-25 09:40:56', 'assets/uploads/course_thumbnails/course_2_6832833859b4e-Untitled Diagram_2025-03-31T03_05_43.553Z.png');

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `course_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`course_id`, `category_id`) VALUES
(3, 1),
(4, 4),
(5, 2),
(8, 3);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`) VALUES
(1, 2, 3, '2025-05-23 19:13:27'),
(2, 1, 3, '2025-05-24 20:40:29'),
(3, 6, 3, '2025-05-24 20:42:52'),
(4, 6, 5, '2025-05-24 20:50:39'),
(6, 2, 4, '2025-05-25 09:23:02'),
(7, 2, 5, '2025-05-25 09:33:28'),
(8, 1, 5, '2025-05-25 09:36:44'),
(9, 1, 8, '2025-05-25 09:42:03'),
(10, 1, 4, '2025-05-25 09:44:13'),
(11, 2, 8, '2025-05-25 09:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `google_users`
--

CREATE TABLE `google_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `google_id` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `verified_email` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `google_users`
--

INSERT INTO `google_users` (`id`, `user_id`, `google_id`, `email`, `name`, `avatar`, `verified_email`, `created_at`, `last_login_at`) VALUES
(1, 1, '111386702664268367851', 'hoanganh5923@gmail.com', 'Anh Hoàng', 'https://lh3.googleusercontent.com/a/ACg8ocKaFd6chipZsRC1cw-FtIVWdDHkGCm1RpEIF6Q0UJ7fbkBc3oKGig=s96-c', 1, '2025-04-22 17:31:33', '2025-05-24 02:08:19'),
(2, 8, '100217045134520531144', 'trsmthw11@gmail.com', 'Thư Nguyễn Minh', 'https://lh3.googleusercontent.com/a/ACg8ocJz9mGxVB4Adh0C3tAJt8fgmoSZehwu6jeII0ieySYENFbS5bdt=s96-c', 1, '2025-04-22 17:35:45', '2025-04-24 06:46:26'),
(3, 2, '113811289923558391973', 'hoanganh7195@gmail.com', 'Hoàng Anh', 'https://lh3.googleusercontent.com/a/ACg8ocI8EhIoQlg6Kpf2EilmWphb2Q7WD9NsZGFaV-5u-pDRc2QFJ6UQ=s96-c', 1, '2025-04-22 17:37:43', '2025-05-19 22:16:15'),
(4, 9, '108271373077250138320', 'hoangah5959@gmail.com', 'Anh Hoàng', 'https://lh3.googleusercontent.com/a/ACg8ocK_z2g1qAhz5akRnChHhXM3SKbrl_LC1znCwN3ALUfLVDqDRg=s96-c', 1, '2025-04-22 17:53:26', '2025-05-19 22:16:40');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `youtube_id` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `youtube_id`, `content`, `sort_order`) VALUES
(5, 3, 'Learn Web Design For Beginners - Full Course', 'j6Ule7GXaRs', NULL, NULL),
(6, 3, 'Introduction to Web Design', 'C72WkcUZvco', NULL, NULL),
(7, 3, 'BASIC WEB DESIGN SOFTWARE: Free Web Design Course | Episode 2', 'R_gFhRsWLMw', NULL, NULL),
(8, 3, 'BRIEF HISTORY OF WEB DESIGN: Free Web Design Course | Episode 3', 'mQeplLGXIY4', NULL, NULL),
(9, 3, 'INTRO TO TYPOGRAPHY: Free Web Design Course | Episode 4', 'OUp7ale49lI', NULL, NULL),
(10, 4, 'Digital Marketing Course 2025 | Everything You Need To Know', 'jVgYgN0zcWs', NULL, NULL),
(11, 5, 'What is Graphic Design?', 'dFSia1LZI4Y', NULL, NULL),
(12, 5, '‘Line’ Visual element of Graphic Design / Design theory', 'F0PTse89XIE', NULL, NULL),
(13, 5, '‘Colour’ Visual element of Graphic Design / Design theory', 'byDNMLTuOqI', NULL, NULL),
(14, 5, '‘Shape’ Visual element of Graphic Design / Design theory', '5jprIWG8f5g', NULL, NULL),
(15, 5, '‘Texture’ Visual element of Graphic Design', 'hECQpBM0b0Q', NULL, NULL),
(17, 8, 'RICK ROLL', 'dQw4w9WgXcQ', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_progress`
--

CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson_progress`
--

INSERT INTO `lesson_progress` (`id`, `user_id`, `lesson_id`, `completed`, `completed_at`) VALUES
(1, 2, 5, 1, '2025-05-23 20:09:45'),
(2, 2, 6, 1, '2025-05-23 20:05:01'),
(3, 2, 7, 1, '2025-05-23 20:09:20'),
(4, 6, 5, 1, '2025-05-24 20:51:42'),
(5, 6, 6, 1, '2025-05-24 20:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bank_code` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'VNPAY',
  `vnp_transaction_no` varchar(100) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT 'pending',
  `transaction_date` datetime DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `course_id`, `amount`, `bank_code`, `payment_type`, `ip_address`, `payment_method`, `vnp_transaction_no`, `transaction_status`, `transaction_date`, `response_data`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 'ORD21748139615', 2, 4, 100000.00, 'NCB', NULL, NULL, 'VNPAY', '14977652', 'error', '2025-05-25 09:21:25', NULL, '2025-05-25 09:21:52', '2025-05-25 09:20:15', '2025-05-25 09:21:25'),
(2, 'ORD21748139730', 2, 4, 100000.00, 'NCB', NULL, NULL, 'VNPAY', '14977657', 'success', '2025-05-25 09:23:02', NULL, '2025-05-25 09:23:27', '2025-05-25 09:22:10', '2025-05-25 09:23:02'),
(5, 'ORD21748140321', 2, 5, 100000.00, NULL, NULL, NULL, 'VNPAY', NULL, 'pending', NULL, NULL, NULL, '2025-05-25 09:32:01', '2025-05-25 09:32:01'),
(7, 'ORD21748140373', 2, 5, 100000.00, 'NCB', NULL, NULL, 'VNPAY', '14977662', 'success', '2025-05-25 09:33:28', NULL, '2025-05-25 09:33:54', '2025-05-25 09:32:53', '2025-05-25 09:33:28'),
(12, 'ORD11748140550', 1, 5, 100000.00, NULL, NULL, NULL, 'VNPAY', NULL, 'pending', NULL, NULL, NULL, '2025-05-25 09:35:50', '2025-05-25 09:35:50'),
(13, 'ORD11748140570', 1, 5, 100000.00, 'NCB', NULL, NULL, 'VNPAY', '14977664', 'success', '2025-05-25 09:36:44', NULL, '2025-05-25 09:37:11', '2025-05-25 09:36:10', '2025-05-25 09:36:44'),
(15, 'ORD11748140901', 1, 8, 10000.00, 'NCB', NULL, NULL, 'VNPAY', '14977668', 'success', '2025-05-25 09:42:03', NULL, '2025-05-25 09:42:30', '2025-05-25 09:41:41', '2025-05-25 09:42:03'),
(16, 'ORD11748141013', 1, 4, 100000.00, NULL, NULL, NULL, 'VNPAY', NULL, 'pending', NULL, NULL, NULL, '2025-05-25 09:43:33', '2025-05-25 09:43:33'),
(17, 'ORD11748141030', 1, 4, 100000.00, 'NCB', NULL, NULL, 'VNPAY', '14977671', 'success', '2025-05-25 09:44:13', NULL, '2025-05-25 09:44:40', '2025-05-25 09:43:50', '2025-05-25 09:44:13'),
(18, 'ORD21748141323', 2, 8, 10000.00, 'VNPAY', NULL, NULL, 'VNPAY', '0', 'failed', '2025-05-25 09:49:33', NULL, '2025-05-25 09:49:14', '2025-05-25 09:48:43', '2025-05-25 09:49:33'),
(19, 'ORD21748141391', 2, 8, 10000.00, 'NCB', NULL, NULL, 'VNPAY', '14977680', 'success', '2025-05-25 09:50:17', NULL, '2025-05-25 09:50:44', '2025-05-25 09:49:51', '2025-05-25 09:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','instructor','admin') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `name`, `email`, `avatar`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'anhdeptrai', 'Hehheeeeee', 'hoanganh5923@gmail.com', 'assets/uploads/avatars/avatar_1_1747675845.png', '123', 'admin', 'active', '2025-04-18 15:01:54'),
(2, 'testlogin', 'Hoàng Anh', 'hoanganh7195@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocI8EhIoQlg6Kpf2EilmWphb2Q7WD9NsZGFaV-5u-pDRc2QFJ6UQ=s96-c', '123', 'instructor', 'active', '2025-04-19 00:01:56'),
(6, 'a', 'Hoàng Anh Đẹp Trai', '21057491@student.iuh.edu.vn', 'assets/uploads/avatar_6_1745462542.png', 'a', 'student', 'active', '2025-04-22 00:33:33'),
(7, 'aa', 'Minh Thư Đẹp Gái', 'minhthu@gmail.com', NULL, 'a', 'student', 'active', '2025-04-22 13:19:53'),
(8, 'trsmthw11', 'Thư Nguyễn Minh', 'trsmthw11@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocJz9mGxVB4Adh0C3tAJt8fgmoSZehwu6jeII0ieySYENFbS5bdt=s96-c', '', 'student', 'active', '2025-04-22 17:35:45'),
(9, 'hoangah5959', 'Anh Hoàng', 'hoangah5959@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocK_z2g1qAhz5akRnChHhXM3SKbrl_LC1znCwN3ALUfLVDqDRg=s96-c', '123', 'instructor', 'active', '2025-04-22 17:53:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_category_relations`
--
ALTER TABLE `blog_category_relations`
  ADD PRIMARY KEY (`blog_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_id` (`blog_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`course_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `google_users`
--
ALTER TABLE `google_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `google_users`
--
ALTER TABLE `google_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_category_relations`
--
ALTER TABLE `blog_category_relations`
  ADD CONSTRAINT `blog_category_relations_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_category_relations_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `blog_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `course_categories_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `course_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `google_users`
--
ALTER TABLE `google_users`
  ADD CONSTRAINT `google_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `lesson_progress`
--
ALTER TABLE `lesson_progress`
  ADD CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
