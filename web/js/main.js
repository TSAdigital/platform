window.addEventListener('load', function() {
    const body = document.body;
    const overlay = document.getElementById('loading-overlay');

    body.style.display = 'block';

    setTimeout(() => {
        body.style.opacity = '1';
        if (overlay) {
            overlay.style.opacity = '0';
        }
    }, 0);

    setTimeout(() => {
        if (overlay) {
            overlay.style.display = 'none';
        }
    }, 1000);
});

document.addEventListener("DOMContentLoaded", function() {
    const tabButtons = document.querySelectorAll('.tab-link');

    function getBaseUrl() {
        const url = window.location.href.split('&')[0];
        return url.split('#')[0];
    }

    const baseUrl = getBaseUrl();

    function activateTab(target) {
        tabButtons.forEach(button => {
            button.classList.remove('active');
            const content = document.querySelector(button.getAttribute('data-bs-target'));
            if (content) {
                content.classList.remove('show', 'active');
            }
        });

        const selectedButton = document.querySelector(`button[data-bs-target='${target}']`);
        if (selectedButton) {
            selectedButton.classList.add('active');
            const content = document.querySelector(target);
            if (content) {
                content.classList.add('show', 'active');
            }
        }
    }

    window.addEventListener('popstate', function() {
        const currentBaseUrl = getBaseUrl();
        if (currentBaseUrl !== baseUrl) {
            sessionStorage.removeItem('activeTab');
            activateTab('#pills-home');
        }
    });

    const savedTab = sessionStorage.getItem('activeTab') || window.location.hash;
    if (savedTab) {
        activateTab(savedTab);
    } else {
        activateTab('#pills-home');
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target');
            sessionStorage.setItem('activeTab', target);
            window.location.hash = target;
            activateTab(target);
        });
    });

    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('activeTab');
    });
});




