document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       MOBILE MENU
    ========================================= */

    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {

        menuToggle.addEventListener('click', function (e) {

            e.preventDefault();
            e.stopPropagation();

            document.body.classList.toggle('mobile-menu-open');

            const isOpen =
                document.body.classList.contains('mobile-menu-open');

            menuToggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );

            menuToggle.setAttribute(
                'aria-label',
                isOpen ? 'Close Menu' : 'Open Menu'
            );

        });


        /* Close menu after clicking a navigation link */

        mainNav.querySelectorAll('a').forEach(function (link) {

            link.addEventListener('click', function () {

                document.body.classList.remove('mobile-menu-open');

                menuToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

                menuToggle.setAttribute(
                    'aria-label',
                    'Open Menu'
                );

            });

        });


        /* Close when clicking outside */

        document.addEventListener('click', function (e) {

            if (
                document.body.classList.contains('mobile-menu-open') &&
                !mainNav.contains(e.target) &&
                !menuToggle.contains(e.target)
            ) {

                document.body.classList.remove('mobile-menu-open');

                menuToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

                menuToggle.setAttribute(
                    'aria-label',
                    'Open Menu'
                );
            }

        });


        /* Close menu with Escape */

        document.addEventListener('keydown', function (e) {

            if (e.key === 'Escape') {

                document.body.classList.remove('mobile-menu-open');

                menuToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );

                menuToggle.setAttribute(
                    'aria-label',
                    'Open Menu'
                );

            }

        });

    }


    /* =========================================
       HEADER SCROLL EFFECT
    ========================================= */

    const siteHeader = document.getElementById('siteHeader');

    if (siteHeader) {

        function handleHeaderScroll() {

            if (window.scrollY > 30) {
                siteHeader.classList.add('scrolled');
            } else {
                siteHeader.classList.remove('scrolled');
            }

        }

        handleHeaderScroll();

        window.addEventListener(
            'scroll',
            handleHeaderScroll,
            { passive: true }
        );

    }


    /* =========================================
       REVEAL ANIMATIONS
    ========================================= */

    const revealElements =
        document.querySelectorAll(
            '.reveal-up, .reveal-right, .reveal-left'
        );

    if (revealElements.length) {

        const revealObserver =
            new IntersectionObserver(
                function (entries, observer) {

                    entries.forEach(function (entry) {

                        if (entry.isIntersecting) {

                            entry.target.classList.add('revealed');

                            observer.unobserve(entry.target);

                        }

                    });

                },
                {
                    threshold: 0.12
                }
            );

        revealElements.forEach(function (element) {

            revealObserver.observe(element);

        });

    }

});