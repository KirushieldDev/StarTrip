document.addEventListener('DOMContentLoaded', () => {
    const fontToggleButton = document.getElementById('fontToggleBtn');

    if (localStorage.getItem('fontStyle') === 'aurebesh') {
        document.body.classList.add('aurebesh-font');
    }

    // Check if there is not already an event to toggle
    if (!fontToggleButton.hasListener) {
        fontToggleButton.addEventListener('click', () => {
            document.body.classList.toggle('aurebesh-font');

            if (document.body.classList.contains('aurebesh-font')) {
                localStorage.setItem('fontStyle', 'aurebesh');
            } else {
                localStorage.removeItem('fontStyle');
            }
        });

        fontToggleButton.hasListener = true;
    }
});