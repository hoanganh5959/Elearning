// Login Handler - Xử lý popup login từ URL parameters
(function() {
    'use strict';
    
    // Kiểm tra URL parameter show_login
    function checkShowLogin() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('show_login') === '1') {
            // Xóa parameter khỏi URL để tránh hiển thị popup khi refresh
            var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            var otherParams = [];
            urlParams.forEach(function(value, key) {
                if (key !== 'show_login') {
                    otherParams.push(key + '=' + value);
                }
            });
            if (otherParams.length > 0) {
                newUrl += '?' + otherParams.join('&');
            }
            
            // Cập nhật URL mà không reload trang
            window.history.replaceState({}, document.title, newUrl);
            
            // Hiển thị popup login
            if (typeof showLogin === 'function') {
                setTimeout(function() {
                    showLogin();
                }, 300);
            }
        }
    }
    
    // Chạy khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkShowLogin);
    } else {
        checkShowLogin();
    }
})(); 