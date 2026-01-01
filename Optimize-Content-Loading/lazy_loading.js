document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const item = entry.target;
                const id = item.getAttribute('data-id');

                fetch(`lazy_load.php?id=${id}`)
                    .then(response => response.text())
                    .then(data => {
                        item.innerHTML = data;
                    });

                observer.unobserve(item);
            }
        });
    });

    document.querySelectorAll('.lazy-item').forEach(item => {
        observer.observe(item);
    });
});