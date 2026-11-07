(function () {
  var submenu = document.querySelector(".submenu");
  var trigger = document.querySelector("#item");
  if (!submenu || !trigger) {
    return;
  }

  var url = submenu.getAttribute("data-product-menu-url");
  if (!url) {
    return;
  }

  var root = document.getElementById("product-menu-root");
  var loaded = false;
  var loading = false;

  function bindMenuUi() {
    var arrow = document.querySelector("#arrow_down");
    var closeBtn = document.querySelector("#icon_close");
    if (closeBtn && !closeBtn.dataset.bound) {
      closeBtn.dataset.bound = "1";
      closeBtn.addEventListener("click", function () {
        submenu.classList.remove("active");
        if (arrow) {
          arrow.classList.remove("rotated");
        }
      });
    }

    var brands = document.querySelectorAll(".submenu .right .item");
    brands.forEach(function (el) {
      if (el.dataset.bound) {
        return;
      }
      el.dataset.bound = "1";
      el.addEventListener("click", function () {
        brands.forEach(function (y) {
          y.classList.remove("active");
        });
        el.classList.add("active");
      });
    });
  }

  function ensureLoaded() {
    if (loaded || loading || !root) {
      return Promise.resolve(loaded);
    }
    loading = true;
    root.setAttribute("aria-busy", "true");
    root.classList.add("is-loading");

    return fetch(url, {
      credentials: "same-origin",
      headers: { Accept: "text/html" },
    })
      .then(function (res) {
        if (!res.ok) {
          throw new Error("menu " + res.status);
        }
        return res.text();
      })
      .then(function (html) {
        root.innerHTML = html;
        bindMenuUi();
        loaded = true;
      })
      .catch(function () {
        root.innerHTML =
          '<div class="product-menu-error" style="padding:1.5rem;text-align:center">خطا در بارگذاری محصولات</div>';
      })
      .finally(function () {
        loading = false;
        root.removeAttribute("aria-busy");
        root.classList.remove("is-loading");
      });
  }

  trigger.addEventListener(
    "click",
    function () {
      ensureLoaded();
    },
    true
  );

  trigger.addEventListener(
    "mouseenter",
    function () {
      ensureLoaded();
    },
    { once: true }
  );

  trigger.addEventListener(
    "focusin",
    function () {
      ensureLoaded();
    },
    { once: true }
  );
})();
