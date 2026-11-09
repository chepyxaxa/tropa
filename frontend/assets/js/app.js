document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.hero-slider');

    if (!slider) {
        return;
    }

    const image = slider.querySelector('.slider-image');
    const previousButton = slider.querySelector('[data-slider-prev]');
    const nextButton = slider.querySelector('[data-slider-next]');
    const dots = document.querySelectorAll('.slider-dot');

    if (!image || !previousButton || !nextButton || dots.length === 0) {
        return;
    }

    const slides = [
        '/frontend/assets/images/city.jpg',
        '/frontend/assets/images/museum.jpg',
        '/frontend/assets/images/nature.jpg',
        '/frontend/assets/images/group.jpg'
    ];

    let currentSlide = 0;

    function showSlide(index) {
        currentSlide = (index + slides.length) % slides.length;

        image.src = slides[currentSlide];

        dots.forEach(function(dot, i) {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    previousButton.addEventListener('click', function() {
        showSlide(currentSlide - 1);
    });

    nextButton.addEventListener('click', function() {
        showSlide(currentSlide + 1);
    });

    dots.forEach(function(dot, index) {
        dot.addEventListener('click', function() {
            showSlide(index);
        });
    });

    showSlide(0);

    setInterval(function() {
        showSlide(currentSlide + 1);
    }, 3000);
});