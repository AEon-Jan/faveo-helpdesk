(function () {
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var heroes = document.querySelectorAll('.cs-hero--parallax');

  if (!heroes.length) {
    return;
  }

  var latestScroll = 0;
  var ticking = false;

  function update() {
    heroes.forEach(function (hero) {
      var speed = parseFloat(hero.getAttribute('data-parallax-speed')) || 0.25;
      var offset = prefersReducedMotion ? 0 : -(latestScroll * speed);
      hero.style.setProperty('--cs-hero-offset', offset + 'px');
    });
    ticking = false;
  }

  function requestTick() {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  }

  function onScroll() {
    latestScroll = window.scrollY || window.pageYOffset || 0;
    requestTick();
  }

  if (!prefersReducedMotion) {
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  window.addEventListener('resize', requestTick);
  requestTick();
})();
