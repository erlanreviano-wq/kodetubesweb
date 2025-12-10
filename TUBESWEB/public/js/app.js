// public/js/app.js
(function () {
    'use strict';

    console.groupCollapsed('ResepSehat · app.js loaded');
    console.log('app.js loaded — timestamp:', new Date().toISOString());
    console.groupEnd();

    document.addEventListener('DOMContentLoaded', function () {
        try {
            const authCard = document.getElementById('authCard');
            if (authCard) {
                const switchButtons = authCard.querySelectorAll('[data-auth-mode]');
                switchButtons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const mode = btn.getAttribute('data-auth-mode');
                        if (!mode) return;
                        if (mode === 'signup') {
                            authCard.classList.add('auth-card--signup');
                        } else {
                            authCard.classList.remove('auth-card--signup');
                        }
                    });
                });
            }
        } catch (e) {
            console.warn('authCard init error', e);
        }

        try {
            const recipeGrid = document.querySelector('.recipe-grid');
            const filterButtons = document.querySelectorAll('.menu-filter .filter-pill');

            if (recipeGrid && filterButtons.length > 0) {
                const cards = Array.from(recipeGrid.querySelectorAll('.recipe-card'));

                const applyFilter = (type) => {
                    cards.forEach((card) => {
                        const cardType = (card.dataset.type || 'all').toString();
                        const show = (type === 'all' || type === cardType);
                        card.style.display = show ? '' : 'none';
                    });

                    filterButtons.forEach((btn) => btn.classList.remove('active'));
                    const activeBtn = document.querySelector('.menu-filter .filter-pill[data-filter="' + type + '"]');
                    if (activeBtn) activeBtn.classList.add('active');
                };

                filterButtons.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const type = (btn.dataset.filter || 'all').toString();
                        applyFilter(type);
                    });
                });

                applyFilter('all');
            }
        } catch (e) {
            console.warn('recipe filter init error', e);
        }

        try {
            const addBtn = document.querySelector('.btn-main.add-recipe-side');
            if (addBtn && !document.querySelector('.page-container')) {
                addBtn.style.display = 'none';
            }
        } catch (e) {
            // noop
        }

        try {
            document.body.addEventListener('click', function (ev) {
                const a = ev.target.closest && ev.target.closest('a');
                if (!a) return;
                const href = a.getAttribute('href') || '';
                if (href.includes('index.php//')) {
                    const fixed = href.replace('index.php//', 'index.php?url=');
                    a.setAttribute('href', fixed);
                    console.info('normalized link href', fixed);
                }
            }, true);
        } catch (e) {
            // noop
        }
    });
})();