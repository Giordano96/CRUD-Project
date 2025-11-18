
    const tabs = document.querySelectorAll('.tab');
    const sections = document.querySelectorAll('.tab-section');

    tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        sections.forEach(s => s.classList.remove('active-tab'));
        sections[index].classList.add('active-tab');
    });
});