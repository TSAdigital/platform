document.querySelectorAll('.custom-scroll').forEach(customScroll => {
    customScroll.addEventListener('wheel', function(e) {
        e.preventDefault();
        this.scrollLeft += e.deltaY;
    });
});