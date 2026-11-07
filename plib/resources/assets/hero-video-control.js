(function () {
  const heroSwiperEl = document.querySelector(".heroSwiper");
  const toggleBtn = document.querySelector(".hero-video-toggle");

  if (!heroSwiperEl || !toggleBtn) return;

  const progressBar = toggleBtn.querySelector(".hero-video-toggle__progress-bar");
  const PROGRESS_LENGTH = 2 * Math.PI * 19.5; // ~122.52

  let userPausedHero = false;
  let pausedProgress = 0;

  if (progressBar) {
    progressBar.style.strokeDasharray = String(PROGRESS_LENGTH);
    progressBar.style.strokeDashoffset = String(PROGRESS_LENGTH);
  }

  function setUserPaused(value) {
    userPausedHero = value;
    heroSwiperEl.dataset.userPausedHero = value ? "1" : "0";
  }

  function getSwiper() {
    return heroSwiperEl.swiper || null;
  }

  function getActiveVideo() {
    const swiper = getSwiper();
    if (!swiper) return null;

    const slide = swiper.slides[swiper.activeIndex];
    if (!slide) return null;

    return slide.querySelector("video.hero-media");
  }

  function setProgress(elapsedRatio) {
    if (!progressBar) return;
    const ratio = Math.max(0, Math.min(1, elapsedRatio));
    pausedProgress = ratio;
    progressBar.style.strokeDashoffset = String(PROGRESS_LENGTH * (1 - ratio));
  }

  function pauseHeroAutoplay() {
    const swiper = getSwiper();
    if (!swiper?.autoplay) return;

    if (swiper.autoplay.running && !swiper.autoplay.paused) {
      swiper.autoplay.pause();
    }
  }

  function resumeHeroAutoplay() {
    const swiper = getSwiper();
    if (!swiper?.autoplay) return;

    if (!swiper.autoplay.running) {
      swiper.autoplay.start();
      return;
    }

    if (swiper.autoplay.paused) {
      swiper.autoplay.resume();
    }
  }

  function isHeroPlaying() {
    const swiper = getSwiper();
    if (userPausedHero) return false;

    // Prefer autoplay state — video may be paused/black without stopping the slider
    if (swiper?.autoplay) {
      return !!(swiper.autoplay.running && !swiper.autoplay.paused);
    }

    const video = getActiveVideo();
    if (video) return !video.paused;
    return false;
  }

  function setPlayingState(isPlaying) {
    toggleBtn.classList.toggle("is-paused", !isPlaying);
    toggleBtn.setAttribute("aria-pressed", isPlaying ? "true" : "false");
    toggleBtn.setAttribute("aria-label", isPlaying ? "توقف اسلایدر" : "پخش اسلایدر");
  }

  function syncToggle() {
    const video = getActiveVideo();
    // Image-only slides: no play/pause control (poster/autoplay needs no button).
    if (!video) {
      toggleBtn.hidden = true;
      return;
    }

    toggleBtn.hidden = false;
    setPlayingState(isHeroPlaying());

    if (userPausedHero && !video.paused) {
      video.pause();
    }
  }

  toggleBtn.addEventListener("click", function () {
    const video = getActiveVideo();
    const playing = isHeroPlaying();

    if (playing) {
      setUserPaused(true);
      if (video) video.pause();
      setPlayingState(false);
      pauseHeroAutoplay();
      setProgress(pausedProgress);
      return;
    }

    setUserPaused(false);
    if (video) {
      if (!video.getAttribute("src") && video.getAttribute("data-src")) {
        video.setAttribute("src", video.getAttribute("data-src"));
      }
      video.play().catch(function () {});
    }
    setPlayingState(true);
    resumeHeroAutoplay();
  });

  function bindVideoEvents(video) {
    if (!video || video.dataset.heroToggleBound === "1") return;

    video.dataset.heroToggleBound = "1";
    video.addEventListener("play", syncToggle);
    video.addEventListener("pause", syncToggle);
  }

  function bindAllVideos() {
    heroSwiperEl.querySelectorAll("video.hero-media").forEach(bindVideoEvents);
  }

  function bindSwiper() {
    const swiper = getSwiper();
    if (!swiper) {
      requestAnimationFrame(bindSwiper);
      return;
    }

    bindAllVideos();
    syncToggle();
    setProgress(0);
    setUserPaused(false);

    swiper.on("autoplayTimeLeft", function (_swiper, _timeLeft, percentage) {
      if (userPausedHero) {
        setProgress(pausedProgress);
        return;
      }
      setProgress(1 - percentage);
    });

    swiper.on("slideChange", function () {
      pausedProgress = 0;
      setProgress(0);
      requestAnimationFrame(syncToggle);
    });

    swiper.on("autoplayStart", function () {
      if (!userPausedHero) setPlayingState(true);
    });

    swiper.on("autoplayStop", function () {
      if (userPausedHero) {
        setPlayingState(false);
        return;
      }
      // Unexpected stop — restart so ring/slides keep moving
      window.setTimeout(function () {
        if (!userPausedHero && swiper.autoplay && !swiper.autoplay.running) {
          swiper.autoplay.start();
          setPlayingState(true);
        }
      }, 100);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bindSwiper);
  } else {
    bindSwiper();
  }
})();
