// Listeners Lounge — Main JavaScript

document.addEventListener('DOMContentLoaded', () => {

    // ---- Genre Dropdown ----
    const dropdown = document.querySelector('.genre-dropdown');
    if (dropdown) {
        dropdown.addEventListener('mouseenter', () => dropdown.classList.add('open'));
        dropdown.addEventListener('mouseleave', () => dropdown.classList.remove('open'));
    }

    // ---- Auth Tabs ----
    const authTabs = document.querySelectorAll('.auth-tab');
    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            authTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(target)?.classList.add('active');
        });
    });

    // ---- Account Tabs ----
    const accountTabs = document.querySelectorAll('.account-tab');
    accountTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.panel;
            accountTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.account-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(target)?.classList.add('active');
            // Update URL hash
            history.replaceState(null, null, '#' + target);
        });
    });
    // Activate panel from URL hash
    const hash = window.location.hash.slice(1);
    if (hash) {
        const tab = document.querySelector(`.account-tab[data-panel="${hash}"]`);
        if (tab) tab.click();
    }

    // ---- Interactive Star Rating ----
    const interactiveStars = document.querySelectorAll('.stars.interactive');
    interactiveStars.forEach(starsEl => {
        const input = document.querySelector('input[name="rating"]');
        const stars = starsEl.querySelectorAll('.star');

        stars.forEach((star, i) => {
            star.addEventListener('mouseenter', () => {
                stars.forEach((s, j) => {
                    s.classList.toggle('filled', j <= i);
                });
            });
            star.addEventListener('click', () => {
                const rating = i + 1;
                if (input) input.value = rating;
                stars.forEach((s, j) => {
                    s.classList.toggle('filled', j < rating);
                    s.dataset.selected = j < rating ? '1' : '0';
                });
                starsEl.dataset.selected = rating;
            });
        });

        starsEl.addEventListener('mouseleave', () => {
            const selected = parseInt(starsEl.dataset.selected) || 0;
            stars.forEach((s, j) => {
                s.classList.toggle('filled', j < selected);
            });
        });
    });

    // ---- Flash message auto-dismiss ----
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }

    // ---- Confirm delete ----
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
});
