/**
 * Form Handler - POST/Redirect/GET Pattern
 * Xử lý tự động các form để tránh resubmission
 */
(function() {
    'use strict';
    
    // Configuration
    const PROCESSORS_PATH = 'processors/';
    const DEFAULT_REDIRECT = 'index.php';
    
    // Form mappings để tự động áp dụng processors
    const FORM_PROCESSORS = {
        'login': 'login_process.php',
        'register': 'register_process.php',
        'personal-info': 'personal_info_process.php',
        'course': 'course_process.php',
        'lesson': 'lesson_process.php',
        'forgot-password': 'forgot_password_process.php'
    };
    
    /**
     * Tự động cập nhật action cho các form
     */
    function updateFormActions() {
        const forms = document.querySelectorAll('form[method="POST"], form[method="post"]');
        
        forms.forEach(form => {
            const currentAction = form.getAttribute('action') || '';
            
            // Nếu đã có processor, skip
            if (currentAction.includes('processors/')) {
                return;
            }
            
            // Xác định loại form dựa trên các indicators
            let formType = null;
            
            // Check by form fields
            if (form.querySelector('[name="user"]') && form.querySelector('[name="password"]')) {
                formType = 'login';
            } else if (form.querySelector('[name="username"]') && form.querySelector('[name="email"]')) {
                formType = 'register';
            } else if (form.querySelector('[name="update_personal"]') || form.querySelector('[name="change_password"]')) {
                formType = 'personal-info';
            } else if (form.querySelector('[name="title"]') && form.querySelector('[name="description"]')) {
                formType = 'course';
            } else if (form.querySelector('[name="request_reset"]') || form.querySelector('[name="verify_code"]')) {
                formType = 'forgot-password';
            }
            
            // Check by form ID or class
            if (!formType) {
                const formId = form.id || '';
                const formClass = form.className || '';
                
                if (formId.includes('login') || formClass.includes('login')) {
                    formType = 'login';
                } else if (formId.includes('register') || formClass.includes('register')) {
                    formType = 'register';
                } else if (formId.includes('course') || formClass.includes('course')) {
                    formType = 'course';
                } else if (formId.includes('lesson') || formClass.includes('lesson')) {
                    formType = 'lesson';
                }
            }
            
            // Áp dụng processor nếu tìm thấy
            if (formType && FORM_PROCESSORS[formType]) {
                const newAction = getProcessorPath(FORM_PROCESSORS[formType]);
                form.setAttribute('action', newAction);
                
                // Thêm redirect_to field nếu chưa có
                if (!form.querySelector('[name="redirect_to"]')) {
                    addRedirectField(form);
                }
                
                console.log(`Updated form action: ${formType} -> ${newAction}`);
            }
        });
    }
    
    /**
     * Lấy đường dẫn processor đúng dựa trên vị trí hiện tại
     */
    function getProcessorPath(processorFile) {
        const currentPath = window.location.pathname;
        let basePath = '';
        
        // Xác định số lượng ../ cần thiết
        if (currentPath.includes('/instructor/')) {
            basePath = '../';
        } else if (currentPath.includes('/admin/')) {
            basePath = '../';
        } else if (currentPath.includes('/public/')) {
            basePath = '';
        } else {
            basePath = '';
        }
        
        return basePath + PROCESSORS_PATH + processorFile;
    }
    
    /**
     * Thêm hidden field redirect_to
     */
    function addRedirectField(form) {
        const currentPage = getCurrentPage();
        const redirectInput = document.createElement('input');
        redirectInput.type = 'hidden';
        redirectInput.name = 'redirect_to';
        redirectInput.value = currentPage;
        form.appendChild(redirectInput);
    }
    
    /**
     * Lấy tên trang hiện tại
     */
    function getCurrentPage() {
        const path = window.location.pathname;
        const segments = path.split('/');
        const filename = segments[segments.length - 1];
        
        // Nếu có query string, giữ lại
        const search = window.location.search;
        return filename + search;
    }
    
    /**
     * Xử lý form submission với loading state
     */
    function handleFormSubmission() {
        const forms = document.querySelectorAll('form[method="POST"], form[method="post"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('[type="submit"]');
                
                if (submitBtn) {
                    // Lưu text gốc
                    const originalText = submitBtn.textContent || submitBtn.value;
                    
                    // Hiển thị loading state
                    submitBtn.disabled = true;
                    
                    if (submitBtn.tagName === 'BUTTON') {
                        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
                    } else {
                        submitBtn.value = 'Đang xử lý...';
                    }
                    
                    // Khôi phục sau 10 giây (fallback)
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        if (submitBtn.tagName === 'BUTTON') {
                            submitBtn.textContent = originalText;
                        } else {
                            submitBtn.value = originalText;
                        }
                    }, 10000);
                }
            });
        });
    }
    
    /**
     * Hiển thị thông báo từ URL parameters
     */
    function handleURLMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        const type = urlParams.get('type') || 'info';
        
        if (message) {
            showNotification(decodeURIComponent(message), type);
            
            // Xóa parameters khỏi URL
            const newUrl = window.location.protocol + "//" + 
                          window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }
    
    /**
     * Hiển thị notification
     */
    function showNotification(message, type) {
        // Tạo notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    /**
     * Debug function - hiển thị thông tin các form đã xử lý
     */
    function debugForms() {
        if (window.location.search.includes('debug=forms')) {
            console.group('Form Handler Debug');
            console.log('Current page:', getCurrentPage());
            console.log('Processor path:', getProcessorPath('test.php'));
            
            const forms = document.querySelectorAll('form[method="POST"], form[method="post"]');
            forms.forEach((form, index) => {
                console.log(`Form ${index + 1}:`, {
                    action: form.getAttribute('action'),
                    fields: Array.from(form.querySelectorAll('input, select, textarea')).map(el => el.name).filter(Boolean)
                });
            });
            console.groupEnd();
        }
    }
    
    // Khởi tạo khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        updateFormActions();
        handleFormSubmission();
        handleURLMessages();
        debugForms();
    }
    
    // Export functions for manual use
    window.FormHandler = {
        updateFormActions,
        addRedirectField,
        showNotification
    };
})(); 