(function () {
  var root = document.querySelector('[data-carousel]');
  if (!root) return;

  var track = root.querySelector('[data-carousel-track]');
  var prev = root.querySelector('[data-carousel-prev]');
  var next = root.querySelector('[data-carousel-next]');
  var dots = root.querySelectorAll('[data-carousel-dot]');
  if (!track || !prev || !next) return;

  var index = 0;
  var slides = track.children.length;

  function setActiveDot() {
    dots.forEach(function (dot) {
      var dotIndex = Number(dot.getAttribute('data-carousel-dot'));
      dot.classList.toggle('is-active', dotIndex === index);
    });
  }

  function go(to) {
    index = (to + slides) % slides;
    track.scrollTo({
      left: index * track.clientWidth,
      behavior: 'smooth'
    });
    setActiveDot();
  }

  prev.addEventListener('click', function () { go(index - 1); });
  next.addEventListener('click', function () { go(index + 1); });

  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      var dotIndex = Number(dot.getAttribute('data-carousel-dot'));
      if (!Number.isNaN(dotIndex)) {
        go(dotIndex);
      }
    });
  });

  setInterval(function () {
    go(index + 1);
  }, 5000);
})();
