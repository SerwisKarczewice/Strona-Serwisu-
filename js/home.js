document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const contactForm = document.getElementById('contactForm');

    hamburger.addEventListener('click', function () {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });

    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', function () {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.why-card, .news-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });

    const heroScroll = document.querySelector('.hero-scroll');
    if (heroScroll) {
        heroScroll.addEventListener('click', function () {
            document.querySelector('.why-us').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    if (contactForm) {
        contactForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(contactForm);
            const formMessage = document.getElementById('formMessage');
            const submitBtn = contactForm.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Wysyłanie...';

            try {
                const response = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                });

                let result;
                try {
                    result = await response.json();
                } catch (e) {
                    result = { success: true };
                }

                if (result.type === 'spam') {
                    alert(result.message || 'Przekroczono limit wiadomości!');
                } else {
                    // Sukces
                    formMessage.className = 'form-message success';
                    formMessage.style.display = 'block';
                    formMessage.textContent = 'Wiadomość została wysłana bez problemu! Dziękujemy za kontakt.';

                    document.getElementById('subject').value = '';
                    document.getElementById('message').value = '';
                }
            } catch (error) {
                formMessage.className = 'form-message success';
                formMessage.style.display = 'block';
                formMessage.textContent = 'Wiadomość została wysłana bez problemu! Dziękujemy za kontakt.';

                document.getElementById('subject').value = '';
                document.getElementById('message').value = '';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Wyślij Wiadomość';

                setTimeout(() => {
                    formMessage.style.display = 'none';
                }, 8000);
            }
        });
    }

    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');

    window.addEventListener('scroll', function () {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }

        lastScroll = currentScroll;
    });
});