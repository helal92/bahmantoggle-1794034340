(function () {
  var section = document.getElementById("home-product-slider");
  if (!section) {
    return;
  }

  var jsonUrl = section.getAttribute("data-menu-json");
  var fileUrl = (section.getAttribute("data-file-url") || "").replace(/\/$/, "");
  var siteUrl = (section.getAttribute("data-site-url") || "").replace(/\/$/, "");
  if (!jsonUrl) {
    return;
  }

  var brandsEl = document.getElementById("home-product-brands");
  var carouselsEl = document.getElementById("home-product-carousels");
  if (!brandsEl || !carouselsEl) {
    return;
  }

  // Only hydrate a few eager images for the first visible brand.
  // Other brands keep data-src until their tab is opened (was loading ~128 thumbs).
  var EAGER_PER_BRAND = 3;

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/"/g, "&quot;");
  }

  function hydrate(root) {
    if (!root) return;
    root.querySelectorAll("img[data-src]").forEach(function (img) {
      var src = img.getAttribute("data-src");
      if (!src) return;
      img.setAttribute("src", src);
      img.removeAttribute("data-src");
    });
  }

  function build(categories) {
    var brandsHtml = "";
    var carouselsHtml = "";
    var n = 0;
    categories.forEach(function (cat) {
      if (!cat || !cat.title || !cat.products || !cat.products.length) {
        return;
      }
      n += 1;
      var key = "item" + n;
      var isFirst = n === 1;
      brandsHtml +=
        '<p class="Item' +
        (isFirst ? " active" : "") +
        '" tab_content_title="' +
        key +
        '">' +
        esc(cat.title) +
        "</p>";

      var slides = cat.products
        .map(function (car, idx) {
          var img = fileUrl + "/thumb/" + encodeURIComponent(car.image) + "/600";
          var href = siteUrl + "/product/" + encodeURIComponent(car.id);
          var eager = isFirst && idx < EAGER_PER_BRAND;
          var imgAttrs = eager
            ? 'src="' + esc(img) + '" loading="' + (idx === 0 ? "eager" : "lazy") + '"'
            : 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" data-src="' +
              esc(img) +
              '" loading="lazy"';
          return (
            '<div class="swiper-slide"><div class="Item">' +
            '<img class="image" ' +
            imgAttrs +
            ' alt="' +
            esc(car.title) +
            '" data-title="' +
            esc(car.title) +
            '" data-show-link="' +
            esc(href) +
            '" data-buy-link="" data-desc="" decoding="async">' +
            "</div></div>"
          );
        })
        .join("");

      carouselsHtml +=
        '<div class="carousel-wrap' +
        (isFirst ? " active" : "") +
        '" tab_content_item="' +
        key +
        '">' +
        '<div class="swiper ProductCategory_Slider">' +
        '<div class="navigation prev"><i class="icon icon-arrow_right"></i></div>' +
        '<div class="swiper-wrapper">' +
        slides +
        "</div>" +
        '<div class="navigation next"><i class="icon icon-arrow_left"></i></div>' +
        "</div></div>";
    });

    if (!n) {
      return false;
    }
    brandsEl.innerHTML = brandsHtml;
    carouselsEl.innerHTML = carouselsHtml;
    section.hidden = false;

    brandsEl.querySelectorAll("[tab_content_title]").forEach(function (tab) {
      tab.addEventListener("click", function () {
        var key = tab.getAttribute("tab_content_title");
        var wrap = carouselsEl.querySelector('[tab_content_item="' + key + '"]');
        hydrate(wrap);
      });
    });

    return true;
  }

  function bootSlider() {
    var src =
      (section.getAttribute("data-slider-module") ||
        "/templates/bahman/assets/slider_car-bahman.js") +
      "?home=" +
      Date.now();
    var hint = document.querySelector('script[src*="slider_car-bahman.js"]');
    if (hint && hint.src) {
      src = hint.src.replace(/(\?.*)?$/, "") + "?home=" + Date.now();
    }
    var preload = document.querySelector('link[href*="slider_car-bahman.js"]');
    if (preload && preload.href) {
      src = preload.href.replace(/(\?.*)?$/, "") + "?home=" + Date.now();
    }
    return import(src).catch(function () {
      /* slider optional */
    });
  }

  function load() {
    fetch(jsonUrl, { credentials: "same-origin", headers: { Accept: "application/json" } })
      .then(function (res) {
        if (!res.ok) {
          throw new Error("status " + res.status);
        }
        return res.json();
      })
      .then(function (data) {
        var cats = (data && data.categories) || [];
        if (!build(cats)) {
          return;
        }
        return bootSlider();
      })
      .catch(function () {
        /* keep homepage usable without slider */
      });
  }

  // Section starts with [hidden], so IntersectionObserver never fires (0×0 box).
  // Load shortly after first paint / idle instead.
  function scheduleLoad() {
    if (typeof window.requestIdleCallback === "function") {
      window.requestIdleCallback(
        function () {
          load();
        },
        { timeout: 1200 }
      );
    } else {
      setTimeout(load, 200);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", scheduleLoad);
  } else {
    scheduleLoad();
  }
})();
