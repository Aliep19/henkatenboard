$(document).ready(function() {
        // Open image modal
        function openImageModal(imageSrc, caption) {
            $('#modalImage').attr('src', imageSrc);
            $('#imageModalLabel').text(caption || 'News Image');
            var imageModal = new bootstrap.Modal(document.getElementById('imageModal'), {
                keyboard: true
            });
            imageModal.show();
        }

        // Attach click event to carousel images
        $('.clickable-image').on('click', function() {
            var imageSrc = $(this).attr('src');
            var caption = $(this).data('caption');
            openImageModal(imageSrc, caption);
        });

        // Pause carousel on hover
        $('.news-carousel').hover(
            function() {
                $(this).carousel('pause');
            },
            function() {
                $(this).carousel('cycle');
            }
        );

        // Add image captions/tooltip effect
        $('.news-carousel').each(function() {
            var $carousel = $(this);
            $carousel.find('.carousel-item').each(function() {
                var $img = $(this).find('img');
                var caption = $img.data('caption');
                if (caption) {
                    var $captionDiv = $('<div class="carousel-caption">').text(caption);
                    $(this).append($captionDiv);
                }
            });
        });

        // Scroll-triggered animations using Intersection Observer
        const animateElements = document.querySelectorAll('.news-section, .portal-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('animate-in');
                    observer.unobserve(entry.target); // Stop observing after animation
                }
            });
        }, {
            threshold: 0.2 // Trigger when 20% of element is visible
        });

        animateElements.forEach(element => {
            observer.observe(element);
        });


        // Accessibility: Enable keyboard navigation for carousel images
        $('.clickable-image').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $(this).trigger('click');
            }
        });

        // Smooth image zoom on carousel active item
        $('.carousel').on('slid.bs.carousel', function() {
            $(this).find('.carousel-item.active img').css({
                transform: 'scale(1.05)',
                transition: 'transform 0.4s ease'
            });
            $(this).find('.carousel-item:not(.active) img').css({
                transform: 'scale(1)',
                transition: 'transform 0.4s ease'
            });
        });
    });

    // jQuery easing for smooth scroll (easeInOutQuad)
    jQuery.extend(jQuery.easing, {
        easeInOutQuad: function (x, t, b, c, d) {
            if ((t /= d / 2) < 1) return c / 2 * t * t + b;
            return -c / 2 * ((--t) * (t - 2) - 1) + b;
        }
    });

    $(document).ready(function() {
            // Handle clickable images for modal/zoom
            $('.clickable-image').on('click', function(e) {
                e.preventDefault();
                var src = $(this).attr('src') || $(this).data('src');
                var caption = $(this).data('caption');
                
                // If modal exists, use it
                if ($('#imageModal').length) {
                    $('#modalImage').attr('src', src);
                    $('#imageModalLabel').text(caption);
                    $('#imageModal').modal('show');
                } else {
                    // Create simple fullscreen overlay
                    var overlay = $(`
                        <div id="imageOverlay" style="
                            position: fixed; 
                            top: 0; 
                            left: 0; 
                            width: 100%; 
                            height: 100%; 
                            background: rgba(0,0,0,0.9); 
                            z-index: 9999; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center;
                            cursor: pointer;
                        ">
                            <img src="${src}" style="max-width: 90%; max-height: 90%; object-fit: contain;">
                            <button style="
                                position: absolute; 
                                top: 20px; 
                                right: 20px; 
                                background: rgba(255,255,255,0.2); 
                                border: none; 
                                color: white; 
                                font-size: 2rem; 
                                cursor: pointer; 
                                border-radius: 50%; 
                                width: 50px; 
                                height: 50px;
                            ">&times;</button>
                        </div>
                    `);
                    
                    $('body').append(overlay);
                    overlay.on('click', function() {
                        $(this).remove();
                    });
                }
            });

            // Progress indicators click handler
            $('.progress-dot').on('click', function() {
                $('.progress-dot').removeClass('active');
                $(this).addClass('active');
            });

            // Update progress indicators on slide change
            $('#newsCarousel').on('slid.bs.carousel', function(e) {
                var activeIndex = $(e.relatedTarget).index();
                $('.progress-dot').removeClass('active');
                $('.progress-dot').eq(activeIndex).addClass('active');
            });

            // Smooth zoom effect on image hover
            $('.fullscreen-image').hover(
                function() {
                    $(this).css('transform', 'scale(1.05)');
                },
                function() {
                    $(this).css('transform', 'scale(1)');
                }
            );

            // Keyboard navigation
            $(document).keydown(function(e) {
                if (e.keyCode === 37) { // Left arrow
                    $('#newsCarousel').carousel('prev');
                } else if (e.keyCode === 39) { // Right arrow
                    $('#newsCarousel').carousel('next');
                } else if (e.keyCode === 27) { // Escape
                    $('#imageOverlay').remove();
                }
            });

            // Touch swipe support for mobile
            var startX, startY;
            $('.fullscreen-container').on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
                startY = e.originalEvent.touches[0].clientY;
            });

            $('.fullscreen-container').on('touchend', function(e) {
                if (!startX || !startY) return;
                
                var endX = e.originalEvent.changedTouches[0].clientX;
                var endY = e.originalEvent.changedTouches[0].clientY;
                
                var diffX = startX - endX;
                var diffY = startY - endY;
                
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    if (Math.abs(diffX) > 50) {
                        if (diffX > 0) {
                            $('#newsCarousel').carousel('next');
                        } else {
                            $('#newsCarousel').carousel('prev');
                        }
                    }
                }
                
                startX = null;
                startY = null;
            });
        });

        $(document).ready(function() {
            var inactivityTimeout;
            var inactivityTime = 600000; // 10 minutes   in milliseconds

            function resetInactivityTimer() {
                clearTimeout(inactivityTimeout);
                inactivityTimeout = setTimeout(function() {
                    var url = 'home.php';
                    var urlParams = new URLSearchParams(window.location.search);
                    var line = urlParams.get('line');
                    var shift = urlParams.get('shift');
                    if (line || shift) {
                        url += '?';
                        if (line) url += 'line=' + encodeURIComponent(line);
                        if (line && shift) url += '&';
                        if (shift) url += 'shift=' + encodeURIComponent(shift);
                    }
                    window.location.href = url;
                }, inactivityTime);
            }

            // Reset timer on user activity
            $(document).on('mousemove keydown click change', function() {
                resetInactivityTimer();
            });

            // Start the timer initially
            resetInactivityTimer();
        });

        //drop information
          let inactivityTimer;

function hideInfoCard() {
    const infoCards = document.querySelectorAll('.info-card');
    infoCards.forEach(card => {
        card.style.transition = 'opacity 0.5s ease';
        card.style.opacity = '0';
        card.style.pointerEvents = 'none'; // Prevent interaction when hidden
    });
}

function showInfoCard() {
    const infoCards = document.querySelectorAll('.info-card');
    infoCards.forEach(card => {
        card.style.transition = 'opacity 0.5s ease';
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto'; // Re-enable interaction
    });
}

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    showInfoCard();
    inactivityTimer = setTimeout(hideInfoCard, 5000); // Hide after 2 seconds of inactivity
}

// Initialize the timer when the page loads
document.addEventListener('DOMContentLoaded', () => {
    resetInactivityTimer();
});

// Add event listeners for user activity
['mousemove', 'click', 'keydown', 'touchstart'].forEach(event => {
    document.addEventListener(event, resetInactivityTimer);
});

// Ensure the carousel navigation also triggers the info-card to show
document.querySelectorAll('.nav-btn, .progress-dot').forEach(element => {
    element.addEventListener('click', resetInactivityTimer);
});