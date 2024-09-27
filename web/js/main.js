window.addEventListener('load', function() {
    const body = document.body;
    const overlay = document.getElementById('loading-overlay');

    // Показать тело документа
    body.style.display = 'block';

    // Применить эффекты затемнения
    setTimeout(() => {
        body.style.opacity = '1';
        if (overlay) {
            overlay.style.opacity = '0';
        }
    }, 0);

    // Убрать наложение через 1 секунду
    setTimeout(() => {
        if (overlay) {
            overlay.style.display = 'none';
        }
    }, 1000);

    // Добавить обработчики событий для элементов сайдбара, если они существуют
    const sidebarItems = document.querySelectorAll('.sidebar-item > a');

    if (sidebarItems.length) {
        sidebarItems.forEach(item => {
            item.addEventListener('click', function() {
                const toggleIcon = this.querySelector('.dropdown-toggle');
                if (toggleIcon) {
                    toggleIcon.style.transform = toggleIcon.style.transform === 'scaleY(-1)' ? 'scaleY(1)' : 'scaleY(-1)';
                }
            });
        });
    }
});
