// Enhanced micro-interactions for listing page
document.addEventListener('DOMContentLoaded', function() {
    // Add intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all fade-in elements
    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Enhanced gallery interactions
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            // Simple lightbox implementation
            const imgSrc = this.querySelector('img').src;
            const lightbox = document.createElement('div');
            lightbox.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                cursor: pointer;
            `;
            lightbox.innerHTML = `
                <img src="${imgSrc}" style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 12px;">
            `;
            lightbox.addEventListener('click', () => lightbox.remove());
            document.body.appendChild(lightbox);
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Heart Favorite Button functionality with floating hearts
    const favoriteBtn = document.getElementById('favoriteBtn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const listingId = this.dataset.listingId;
            const isCurrentlyFavorited = this.classList.contains('favorited');
            
            // Add pulse animation
            this.classList.add('pulse');
            setTimeout(() => this.classList.remove('pulse'), 600);
            
            // Create floating hearts effect when adding to favorites
            if (!isCurrentlyFavorited) {
                createFloatingHearts(this);
            }
            
            // Toggle UI immediately for better UX
            this.classList.toggle('favorited');
            
            const heartIcon = this.querySelector('i');
            const favoriteText = this.parentElement.querySelector('.favorite-text');
            
            if (this.classList.contains('favorited')) {
                heartIcon.className = 'bi bi-heart-fill';
                this.title = 'Remove from favorites';
                if (favoriteText) favoriteText.textContent = 'Saved to favorites';
            } else {
                heartIcon.className = 'bi bi-heart';
                this.title = 'Add to favorites';
                if (favoriteText) favoriteText.textContent = 'Save to favorites';
            }
            
            // Send AJAX request
            fetch('/board-in/backend/toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    listing_id: listingId,
                    action: isCurrentlyFavorited ? 'remove' : 'add'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert UI if failed
                    this.classList.toggle('favorited');
                    
                    if (this.classList.contains('favorited')) {
                        heartIcon.className = 'bi bi-heart-fill';
                        this.title = 'Remove from favorites';
                        if (favoriteText) favoriteText.textContent = 'Saved to favorites';
                    } else {
                        heartIcon.className = 'bi bi-heart';
                        this.title = 'Add to favorites';
                        if (favoriteText) favoriteText.textContent = 'Save to favorites';
                    }
                    
                    showToast('Error: ' + data.message, 'error');
                } else {
                    // Show success toast
                    showToast(
                        isCurrentlyFavorited ? 'Removed from favorites!' : 'Added to favorites!',
                        isCurrentlyFavorited ? 'warning' : 'success'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert UI on error
                this.classList.toggle('favorited');
                
                if (this.classList.contains('favorited')) {
                    heartIcon.className = 'bi bi-heart-fill';
                    this.title = 'Remove from favorites';
                    if (favoriteText) favoriteText.textContent = 'Saved to favorites';
                } else {
                    heartIcon.className = 'bi bi-heart';
                    this.title = 'Add to favorites';
                    if (favoriteText) favoriteText.textContent = 'Save to favorites';
                }
                
                showToast('Network error. Please try again.', 'error');
            });
        });
    }
    
    // Create floating hearts animation
    function createFloatingHearts(button) {
        const numHearts = 5;
        const buttonRect = button.getBoundingClientRect();
        
        for (let i = 0; i < numHearts; i++) {
            setTimeout(() => {
                const heart = document.createElement('i');
                heart.className = 'bi bi-heart-fill floating-heart';
                heart.style.left = buttonRect.left + (buttonRect.width / 2) + (Math.random() - 0.5) * 40 + 'px';
                heart.style.top = buttonRect.top + (buttonRect.height / 2) + 'px';
                heart.style.animationDelay = (i * 0.1) + 's';
                document.body.appendChild(heart);
                
                setTimeout(() => heart.remove(), 1500);
            }, i * 100);
        }
    }
    
    // Toast notification function
    function showToast(message, type = 'success') {
        const bgColor = type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#ef4444';
        const icon = type === 'success' ? 'check-circle-fill' : type === 'warning' ? 'heart' : 'x-circle-fill';
        
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            animation: slideInRight 0.3s ease;
        `;
        toast.innerHTML = `
            <i class="bi bi-${icon}" style="font-size: 1.25rem;"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});