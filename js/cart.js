document.addEventListener('DOMContentLoaded', function () {
    const timeElements = document.querySelectorAll('[data-timestamp]');

    function updateTimers() {
        timeElements.forEach(element => {
            let secondsRemaining = parseInt(element.dataset.timestamp);

            if (secondsRemaining <= 0) {
                location.reload();
                return;
            }

            secondsRemaining--;
            element.dataset.timestamp = secondsRemaining;

            const minutes = Math.floor(secondsRemaining / 60);
            const seconds = secondsRemaining % 60;

            element.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        });
    }

    setInterval(updateTimers, 1000);
});