class LessonListManager {
  constructor(courseId, listElementId, videoElementId) {
      this.courseId = courseId || this.getCourseIdFromUrl() || 1; // Lấy từ tham số, URL hoặc mặc định là 1
      this.listElementId = listElementId || "lessonList";
      this.videoElementId = videoElementId || "mainVideo";
      this.init();
  }

  // Lấy courseId từ URL
  getCourseIdFromUrl() {
      const urlParams = new URLSearchParams(window.location.search);
      return parseInt(urlParams.get("course_id")) || null;
  }

  // Khởi tạo danh sách bài học
  init() {
      this.fetchLessons()
          .then(lessons => this.renderLessons(lessons))
          .catch(error => console.error("Error loading lessons:", error));
  }

  // Lấy danh sách bài học từ server
  async fetchLessons() {
      const response = await fetch(`get_lessons.php?course_id=${this.courseId}`);
      if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
      }
      return await response.json();
  }

  // Hiển thị danh sách bài học
  renderLessons(lessons) {
      const listDiv = document.getElementById(this.listElementId);
      if (!listDiv) {
          throw new Error(`Element with ID ${this.listElementId} not found`);
      }

      listDiv.innerHTML = ""; // Xóa nội dung cũ
      let firstVideoId = "";

      lessons.forEach((lesson, index) => {
          const div = document.createElement("div");
          div.classList.add("lesson-item", "list-group-item");

          if (index === 0) {
              div.classList.add("active-lesson");
              firstVideoId = lesson.youtube_id;
              this.updateMainVideo(firstVideoId);
          }

          div.textContent = `${index + 1}. ${lesson.title}`;
          div.dataset.videoId = lesson.youtube_id;

          div.addEventListener("click", () => {
              document.querySelectorAll(".lesson-item").forEach(el => el.classList.remove("active-lesson"));
              div.classList.add("active-lesson");
              this.updateMainVideo(div.dataset.videoId);
          });

          listDiv.appendChild(div);
      });
  }

  // Cập nhật video chính
  updateMainVideo(videoId) {
      const mainVideo = document.getElementById(this.videoElementId);
      if (!mainVideo) {
          throw new Error(`Element with ID ${this.videoElementId} not found`);
      }
      mainVideo.src = `https://www.youtube.com/embed/${videoId}`;
  }
}

// Sử dụng
document.addEventListener("DOMContentLoaded", () => {
  new LessonListManager(); // Tự động lấy courseId từ URL hoặc dùng mặc định
});