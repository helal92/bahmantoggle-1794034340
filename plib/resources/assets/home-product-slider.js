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

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/"/g, "&quot;");
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
      brandsHtml +=
        '<p class="Item' +
        (n === 1 ? " active" : "") +
        '" tab_content_title="' +
        key +
        '">' +
        esc(cat.title) +
        "</p>";

      var slides = cat.products
        .map(function (car, idx) {
          var img = fileUrl + "/thumb/" + encodeURIComponent(car.image) + "/600";
          var href = siteUrl + "/product/" + encodeURIComponent(car.id);
          var lazy = n !== 1 || idx > 2 ? ' loading="lazy"' : "";
          return (
            '<div class="swiper-slide"><div class="Item">' +
            '<img class="image" src="' +
            esc(img) +
            '" alt="' +
            esc(car.title) +
            '" data-title="' +
            esc(car.title) +
            '" data-show-link="' +
            esc(href) +
            '" data-buy-link="" data-desc="" decoding="async"' +
            lazy +
            ">" +
            "</div></div>"
          );
        })
        .join("");

      carouselsHtml +=
        '<div class="carousel-wrap' +
        (n === 1 ? " active" : "") +
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
    return true;
  }

  function bootSlider() {
    var src =
      (section.getAttribute("data-slider-module") ||
        "/templates/bahman/assets/slider_car-bahman.js") +
      "?home=" +
      Date.now();
    // Prefer absolute template URL if present on page scripts
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
})();
