// Spinner handler - Xử lý spinner một cách chuyên biệt
(function() {
    'use strict';
    
    function forceHideSpinner() {
        var spinner = document.getElementById('spinner');
        if (spinner) {
            spinner.classList.remove('show');
            spinner.classList.add('spinner-force-hide');
            spinner.style.display = 'none';
            spinner.style.opacity = '0';
            spinner.style.visibility = 'hidden';
        }
    }
    
    // Ẩn ngay lập tức
    forceHideSpinner();
    
    // Ẩn khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceHideSpinner);
    } else {
        forceHideSpinner();
    }
    
    // Ẩn khi window load
    window.addEventListener('load', forceHideSpinner);
    
    // Ẩn định kỳ trong 5 giây đầu
    var attempts = 0;
    var maxAttempts = 20;
    var interval = setInterval(function() {
        forceHideSpinner();
        attempts++;
        if (attempts >= maxAttempts) {
            clearInterval(interval);
        }
    }, 250);
    
    // Export function để có thể gọi từ bên ngoài
    window.hideSpinnerNow = forceHideSpinner;
})(); 