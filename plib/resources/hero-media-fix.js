/**
 * Hero: lazy-load only the active video + keep Swiper autoplay running.
 * First video is deferred so sitefile thumbs/sections are not stalled by a
 * multi-MB autoplay download on the same host (HTTP/1.1 connection limit).
 * Always use direct sitefile URLs (no /media proxy).
 */
(function () {
  var firstPlayScheduled = false;

  function ensureAttrs(video) {
    video.muted = true;
    video.defaultMuted = true;
    video.playsInline = true;
    video.setAttribute("muted", "");
    video.setAttribute("playsinline", "");
    video.setAttribute("webkit-playsinline", "");
    video.setAttribute("preload", "none");
  }

  function attachSrc(video) {
    var src = video.getAttribute("data-src");
    if (!src) return;
    if ((video.getAttribute("src") || "") === src) return;
    // Prefer static offload path when URL is a bare sitefile UUID.
    var m = src.match(/^(https?:\/\/sitefile\.bahman\.ir)\/([0-9a-fA-F-]{36})(?:\.mp4)?\/?$/i);
    if (m) {
      src = m[1] + "/v/" + m[2] + ".mp4";
      video.setAttribute("data-src", src);
    }
    video.setAttribute("src", src);
  }

  function pauseOthers(root, activeSlide) {
    root.querySelectorAll("video.hero-media").forEach(function (v) {
      if (activeSlide && activeSlide.contains(v)) return;
      try {
        v.pause();
      } catch (e) {}
      // Drop src on inactive slides so they release the connection slot.
      if (v.hasAttribute("src")) {
        try {
          v.removeAttribute("src");
          v.load();
        } catch (e2) {}
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
      p.then(function () {
        video.classList.add("is-ready");
      }).catch(function () {});
    }
  }

  function deferFirstPlay(root) {
    if (firstPlayScheduled) {
      playActive(root);
      return;
    }
    firstPlayScheduled = true;
    var run = function () {
      playActive(root);
    };
    // Let HTML/CSS/thumb requests claim connections first.
    if (typeof window.requestIdleCallback === "function") {
      window.requestIdleCallback(run, { timeout: 1500 });
    } else if (document.readyState === "complete") {
      window.setTimeout(run, 400);
    } else {
      window.addEventListener(
        "load",
        function () {
          window.setTimeout(run, 200);
        },
        { once: true }
      );
      window.setTimeout(run, 1200);
    }
  }

  function keepAutoplayAlive(swiper) {
    if (!swiper || !swiper.autoplay) return;
    if (swiper.el && swiper.el.dataset.heroAutoplayGuard === "1") return;
    swiper.el.dataset.heroAutoplayGuard = "1";

    swiper.on("autoplayStop", function () {
      if (swiper.el && swiper.el.dataset.userPausedHero === "1") return;
      window.setTimeout(function () {
        try {
          if (swiper.autoplay && !swiper.autoplay.running) {
            swiper.autoplay.start();
          }
        } catch (e) {}
      }, 80);
    });
  }

  function bindSwiper(host) {
    if (!host || !host.swiper || host.dataset.heroLazyBound === "1") return;
    host.dataset.heroLazyBound = "1";

    var swiper = host.swiper;
    var root = host.closest(".hero, .hero_product, .hero-video") || host;

    keepAutoplayAlive(swiper);

    swiper.on("slideChange", function () {
      pauseOthers(root, swiper.slides[swiper.activeIndex]);
    });

    swiper.on("slideChangeTransitionEnd", function () {
      playActive(root);
    });

    deferFirstPlay(root);

    window.setTimeout(function () {
      try {
        if (swiper.autoplay && !swiper.autoplay.running && host.dataset.userPausedHero !== "1") {
          swiper.autoplay.start();
        }
      } catch (e) {}
    }, 250);
  }

  function boot() {
    document.querySelectorAll(".heroSwiper, .hero_product_Swiper").forEach(function (el) {
      el.querySelectorAll("video.hero-media").forEach(function (video) {
        ensureAttrs(video);
        // Never keep an eager src from HTML — data-src only until play.
        if (video.hasAttribute("src") && video.getAttribute("data-src")) {
          try {
            video.removeAttribute("src");
            video.removeAttribute("autoplay");
            video.load();
          } catch (e) {}
        }
      });
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

  function unlockOnce() {
    document.querySelectorAll(".heroSwiper, .hero_product_Swiper").forEach(function (el) {
      playActive(el.closest(".hero, .hero_product, .hero-video") || el);
    });
  }
  document.addEventListener("touchstart", unlockOnce, { capture: true, once: true, passive: true });
  document.addEventListener("click", unlockOnce, { capture: true, once: true });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
