// Framework-free scroll-reveal: toggles `.is-revealed` on any [data-reveal]
// element the first time it enters the viewport. Siblings sharing a
// [data-reveal-group] value get a staggered --reveal-delay.

const STAGGER_STEP_MS = 90;

function initScrollReveal() {
    const targets = document.querySelectorAll('[data-reveal]:not(.is-revealed)');
    if (!targets.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        targets.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const groupCounts = new Map();
    targets.forEach((el) => {
        const group = el.getAttribute('data-reveal-group');
        if (!group) return;
        const index = groupCounts.get(group) ?? 0;
        el.style.setProperty('--reveal-delay', `${index * STAGGER_STEP_MS}ms`);
        groupCounts.set(group, index + 1);
    });

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                obs.unobserve(entry.target);
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -10% 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', initScrollReveal);
document.addEventListener('livewire:navigated', initScrollReveal);
