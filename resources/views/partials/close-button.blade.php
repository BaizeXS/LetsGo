<button id="close-modal" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-white rounded-full shadow-md z-10 hover:bg-gray-100">
    <i class="fas fa-times"></i>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const closeButton = document.getElementById('close-modal');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                // 关闭当前页面，返回上一页
                window.history.back();
            });
        }
    });
</script> 