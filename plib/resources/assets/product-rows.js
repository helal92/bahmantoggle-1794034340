(function (global) {
  var BTN_CLASS = "product-show-more";
  var HIDDEN_CLASS = "is-row-collapsed";

  function columnCount(box) {
    var cols = global.getComputedStyle(box).gridTemplateColumns || "";
    if (!cols || cols === "none") {
      return 4;
    }
    return cols.split(" ").filter(Boolean).length || 4;
  }

  function deferImage(car) {
    var img = car.querySelector("img.img, img");
    if (!img) {
      return;
    }
    var src = img.getAttribute("src");
    if (src && src.indexOf("data:") !== 0) {
      img.setAttribute("data-src", src);
      img.removeAttribute("src");
      img.setAttribute("loading", "lazy");
    }
  }

  function loadImage(car) {
    var img = car.querySelector("img.img, img");
    if (!img) {
      return;
    }
    var src = img.getAttribute("data-src");
    if (src && !img.getAttribute("src")) {
      img.setAttribute("src", src);
    }
  }

  function ensureButton(section, box) {
    var btn = section.querySelector("." + BTN_CLASS);
    if (btn) {
      return btn;
    }
    btn = document.createElement("button");
    btn.type = "button";
    btn.className = BTN_CLASS;
    btn.textContent = "نمایش بیشتر";
    btn.setAttribute("aria-label", "نمایش ردیف بعدی محصولات");
    section.appendChild(btn);
    btn.addEventListener("click", function () {
      revealNextRow(section, box);
    });
    return btn;
  }

  function visibleCars(cars) {
    return cars.filter(function (car) {
      return !car.classList.contains("is-search-hidden");
    });
  }

  function applyLimit(section, box, forceVisible) {
    var cars = Array.prototype.slice.call(box.querySelectorAll(":scope > .car"));
    var eligible = visibleCars(cars);
    var cols = columnCount(box);
    var limit =
      typeof forceVisible === "number"
        ? forceVisible
        : section._bahmanRowVisible || cols;

    if (limit < cols) {
      limit = cols;
    }
    section._bahmanRowVisible = limit;

    eligible.forEach(function (car, idx) {
      if (idx < limit) {
        car.classList.remove(HIDDEN_CLASS);
        loadImage(car);
      } else {
        car.classList.add(HIDDEN_CLASS);
        deferImage(car);
      }
    });

    // Cars filtered out by search stay deferred if collapsed previously.
    cars.forEach(function (car) {
      if (car.classList.contains("is-search-hidden")) {
        return;
      }
      if (!car.classList.contains(HIDDEN_CLASS)) {
        loadImage(car);
      }
    });

    var remaining = Math.max(0, eligible.length - limit);
    var btn = ensureButton(section, box);
    if (remaining <= 0) {
      btn.hidden = true;
    } else {
      btn.hidden = false;
      btn.textContent =
        remaining <= cols ? "نمایش بیشتر" : "نمایش بیشتر";
    }
  }

  function revealNextRow(section, box) {
    var cols = columnCount(box);
    var next = (section._bahmanRowVisible || cols) + cols;
    applyLimit(section, box, next);
  }

  function initBox(box) {
    var section = box.closest(".item, section");
    if (!section || box.dataset.rowsInit === "1") {
      // Re-apply when called again (e.g. after search).
      if (section) {
        applyLimit(section, box, section._bahmanRowVisible);
      }
      return;
    }
    box.dataset.rowsInit = "1";
    section._bahmanRowVisible = columnCount(box);
    applyLimit(section, box, section._bahmanRowVisible);
  }

  function init(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var boxes = scope.querySelectorAll(".box_cars");
    boxes.forEach(initBox);
  }

  function expandAll(root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll(".box_cars").forEach(function (box) {
      var section = box.closest(".item, section");
      if (!section) {
        return;
      }
      var cars = box.querySelectorAll(":scope > .car");
      section._bahmanRowVisible = cars.length;
      applyLimit(section, box, cars.length);
    });
  }

  function resetRows(root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll(".box_cars").forEach(function (box) {
      var section = box.closest(".item, section");
      if (!section) {
        return;
      }
      section._bahmanRowVisible = columnCount(box);
      applyLimit(section, box, section._bahmanRowVisible);
    });
  }

  var resizeTimer = 0;
  global.addEventListener("resize", function () {
    global.clearTimeout(resizeTimer);
    resizeTimer = global.setTimeout(function () {
      document.querySelectorAll(".box_cars[data-rows-init='1']").forEach(function (box) {
        var section = box.closest(".item, section");
        if (!section) {
          return;
        }
        var cols = columnCount(box);
        var visible = section._bahmanRowVisible || cols;
        // Keep at least one full row after resize; don't shrink already-revealed rows.
        if (visible < cols) {
          visible = cols;
        }
        applyLimit(section, box, visible);
      });
    }, 150);
  });

  global.BahmanProductRows = {
    init: init,
    expandAll: expandAll,
    resetRows: resetRows,
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      init(document);
    });
  } else {
    init(document);
  }
})(window);
