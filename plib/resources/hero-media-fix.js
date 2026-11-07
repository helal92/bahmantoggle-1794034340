/**
 * Hero media: show poster/fallback first. Do NOT auto-download multi-MB
 * videos on page load (that saturates sitefile/Apache and breaks CSS).
 * Video starts only after user gesture or explicit play control.
 */
(function () {
  function ensureAttrs(video) {
    video.muted = true;
    video.defaultMuted = true;
    video.playsInline = true;
    video.setAttribute("muted", "");
    video.setAttribute("playsinline", "");
    video.setAttribute("webkit-playsinline", "");
    video.setAttribute("preload", "none");
  }

  function stripEager(video) {
    ensureAttrs(video);
    if (video.hasAttribute("src")) {
      try {
        video.removeAttribute("src");
        video.removeAttribute("autoplay");
        video.load();
      } catch (e) {}
    }
  }

  function attachSrc(video) {
    var src = video.getAttribute("data-src");
    if (!src) return;
    var m = src.match(/^(https?:\/\/sitefile\.bahman\.ir)\/(?:v\/)?([0-9a-fA-F-]{36})(?:\.mp4)?\/?$/i);
    if (m) src = m[1] + "/v/" + m[2] + ".mp4";
    if ((video.getAttribute("src") || "") === src) return;
    video.setAttribute("data-src", src);
    video.setAttribute("src", src);
  }

  function pauseOthers(root, activeSlide) {
    root.querySelectorAll("video.hero-media").forEach(function (v) {
      if (activeSlide && activeSlide.contains(v)) return;
      try { v.pause(); } catch (e) {}
      if (v.hasAttribute("src")) {
        try { v.removeAttribute("src"); v.load(); } catch (e2) {}
      }
    });
  }

  function playActive(root) {
    if (!root) return;
    var activeSlide =
      root.querySelector(".swiper-slide-active") ||
      root.querySelector(".swiper-slide-duplicate-active");
    if (!activeSlide) return;
    pauseOthers(root, activeSlide);
    var video = activeSlide.querySelector("video.hero-media");
    if (!video) return;
    ensureAttrs(video);
    attachSrc(video);
    var p = video.play();
    if (p && typeof p.then === "function") {
      p.then(function () { video.classList.add("is-ready"); }).catch(function () {});
    }
  }

  function bindSwiper(host) {
    if (!host || !host.swiper || host.dataset.heroLazyBound === "1") return;
    host.dataset.heroLazyBound = "1";
    var swiper = host.swiper;
    var root = host.closest(".hero, .hero_product, .hero-video") || host;

    // Keep slider moving on posters; do not fetch video until interaction.
    swiper.on("slideChange", function () {
      pauseOthers(root, swiper.slides[swiper.activeIndex]);
    });

    // If user already pressed the hero play control, allow video on slide end.
    swiper.on("slideChangeTransitionEnd", function () {
      if (host.dataset.userPausedHero === "1") return;
      if (host.dataset.heroVideoAllowed !== "1") return;
      playActive(root);
    });
  }

  function allowAndPlay() {
    document.querySelectorAll(".heroSwiper, .hero_product_Swiper").forEach(function (el) {
      el.dataset.heroVideoAllowed = "1";
      playActive(el.closest(".hero, .hero_product, .hero-video") || el);
    });
  }

  function boot() {
    document.querySelectorAll(".heroSwiper, .hero_product_Swiper").forEach(function (el) {
      el.querySelectorAll("video.hero-media").forEach(stripEager);
      if (el.swiper) bindSwiper(el);
    });
    var tries = 0;
    var t = setInterval(function () {
      tries += 1;
      document.querySelectorAll(".heroSwiper, .hero_product_Swiper").forEach(function (el) {
        if (el.swiper) bindSwiper(el);
      });
      if (tries >= 40) clearInterval(t);
    }, 150);
  }

  // Only start video after real user interaction (not on load).
  document.addEventListener("touchstart", allowAndPlay, { capture: true, once: true, passive: true });
  document.addEventListener("click", allowAndPlay, { capture: true, once: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
