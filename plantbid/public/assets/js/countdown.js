document.addEventListener("DOMContentLoaded", function () {
  function updateCountdown() {
    const countdowns = document.querySelectorAll('.countdown');
    countdowns.forEach(span => {
      const endTime = new Date(span.getAttribute('data-endtime')).getTime();
      const now = new Date().getTime();
      const distance = endTime - now;

      if (distance <= 0) {
        span.textContent = "Aukce skoncila";
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      span.textContent = `Zbyva: ${days}d ${hours}h ${minutes}m ${seconds}s`;
    });
  }

  updateCountdown();
  setInterval(updateCountdown, 1000);
});
