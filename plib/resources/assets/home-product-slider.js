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
  if (!brandsEl) {
    return;
  }

  // First brand: real src for all slides (Swiper loop clones need real images).
  var EAGER_FIRST_BRAND = true;

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

  function clearCarousels() {
    section.querySelectorAll(":scope > .carousel-wrap").forEach(function (el) {
      el.remove();
    });
    var holder = document.getElementById("home-product-carousels");
    if (holder) {
      holder.innerHTML = "";
    }
  }

  function insertCarousels(html) {
    clearCarousels();
    var info = section.querySelector(":scope > .info");
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    while (tmp.firstChild) {
      // CSS expects .slider_car > .carousel-wrap (direct child), not nested in a holder.
      if (info) {
        section.insertBefore(tmp.firstChild, info);
      } else {
        section.appendChild(tmp.firstChild);
      }
    }
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
          var eager = isFirst && EAGER_FIRST_BRAND;
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
    insertCarousels(carouselsHtml);
    section.hidden = false;
    void section.offsetWidth;

    brandsEl.querySelectorAll("[tab_content_title]").forEach(function (tab) {
      tab.addEventListener("click", function () {
        var key = tab.getAttribute("tab_content_title");
        var wrap = section.querySelector('[tab_content_item="' + key + '"]');
        hydrate(wrap);
      });
    });

    return true;
  }

  function runInit() {
    if (typeof window.initBahmanProductSlider === "function") {
      window.initBahmanProductSlider();
    }
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
    return import(src)
      .then(runInit)
      .catch(runInit);
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
        return new Promise(function (resolve) {
          requestAnimationFrame(function () {
            requestAnimationFrame(function () {
              resolve(bootSlider());
            });
          });
        });
      })
      .catch(function () {
        /* keep homepage usable without slider */
      });
  }

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
