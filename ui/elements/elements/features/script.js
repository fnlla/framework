(function () {
  var pills = Array.prototype.slice.call(document.querySelectorAll('.feature-pill'));
  if (pills.length === 0) return;
  var index = 0;
  setInterval(function () {
    pills.forEach(function (pill) { pill.classList.remove('is-active'); });
    pills[index % pills.length].classList.add('is-active');
    index += 1;
  }, 3500);
})();
