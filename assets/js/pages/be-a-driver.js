// Be a Driver Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px'
    };
    
    const statObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    animateNumber(stat);
                });
                statObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    const statsSection = document.querySelector('.statistics-section');
    if (statsSection) {
        statObserver.observe(statsSection);
    }
    
    function animateNumber(element) {
        const text = element.textContent;
        const hasComma = text.includes(',');
        const hasPeso = text.includes('₱');
        const hasStar = text.includes('★');
        const hasPlus = text.includes('+');
        
        // Extract number
        const numText = text.replace(/[^\d.]/g, '');
        const finalNumber = parseFloat(numText);
        
        if (isNaN(finalNumber)) return;
        
        const duration = 2000; // 2 seconds
        const steps = 60;
        const stepValue = finalNumber / steps;
        let currentNumber = 0;
        
        const timer = setInterval(() => {
            currentNumber += stepValue;
            if (currentNumber >= finalNumber) {
                currentNumber = finalNumber;
                clearInterval(timer);
            }
            
            let displayText = Math.floor(currentNumber).toString();
            if (hasComma && displayText.length > 3) {
                displayText = displayText.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
            if (hasPeso) displayText = '₱' + displayText;
            if (hasPlus) displayText += '+';
            if (hasStar && currentNumber === finalNumber) displayText = '4.8★';
            
            element.textContent = displayText;
        }, duration / steps);
    }

    // Add animation on scroll for cards
    const cardObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    const cards = document.querySelectorAll('.benefit-card, .step-card');
    cards.forEach(card => {
        cardObserver.observe(card);
    });

    // Active navbar link highlighting
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
});
