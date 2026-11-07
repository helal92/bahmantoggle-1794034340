(function () {
  const root = document.getElementById("product-list-page");
  if (!root) return;

  const tabs = Array.from(root.querySelectorAll(".brands_list .right > .item"));
  const sections = Array.from(root.querySelectorAll(".cars_list .card3 > .item"));
  const searchInput = root.querySelector("[data-product-search]");
  const emptyState = root.querySelector("[data-product-empty]");
  const cards = Array.from(root.querySelectorAll("[data-product-card]"));

  function normalizeText(value) {
    return String(value || "")
      .trim()
      .toLowerCase()
      .replace(/ي/g, "ی")
      .replace(/ك/g, "ک")
      .replace(/\s+/g, " ");
  }

  function setActive(tab) {
    tabs.forEach((item) => {
      const isActive = item === tab;
      item.classList.toggle("active", isActive);
      const link = item.querySelector(".link");
      if (link) link.setAttribute("aria-selected", isActive ? "true" : "false");
    });
  }

  function applySearch(query) {
    const q = normalizeText(query);
    let visibleCount = 0;

    cards.forEach((card) => {
      const name = normalizeText(card.dataset.productName || card.querySelector(".model_name")?.textContent);
      const match = !q || name.includes(q);
      card.classList.toggle("is-search-hidden", !match);
      if (match) visibleCount += 1;
    });

    sections.forEach((section) => {
      const hasVisible = section.querySelectorAll("[data-product-card]:not(.is-search-hidden)").length > 0;
      section.classList.toggle("is-search-hidden", !hasVisible);
    });

    if (emptyState) {
      if (visibleCount === 0) {
        emptyState.removeAttribute("hidden");
      } else {
        emptyState.setAttribute("hidden", "");
      }
    }

    // Search: show all matches. Clear: back to one row per category.
    if (window.BahmanProductRows) {
      if (q) {
        window.BahmanProductRows.expandAll(root);
      } else {
        window.BahmanProductRows.resetRows(root);
      }
    }
  }

  tabs.forEach((tab) => {
    const link = tab.querySelector(".link");
    if (!link) return;

    link.addEventListener("click", (event) => {
      const href = link.getAttribute("href");
      if (!href || href.charAt(0) !== "#") return;

      const target = root.querySelector(href);
      if (!target || target.classList.contains("is-search-hidden")) return;

      event.preventDefault();
      setActive(tab);
      target.scrollIntoView({
        behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches
          ? "auto"
          : "smooth",
        block: "start",
      });
      history.replaceState(null, "", href);
    });
  });

  if (sections.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting && !entry.target.classList.contains("is-search-hidden"))
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (!visible) return;

        const id = visible.target.id;
        const match = tabs.find((tab) => {
          const link = tab.querySelector(".link");
          return link && link.getAttribute("href") === "#" + id;
        });
        if (match) setActive(match);
      },
      {
        root: null,
        rootMargin: "-160px 0px -55% 0px",
        threshold: [0.15, 0.35, 0.55],
      }
    );

    sections.forEach((section) => observer.observe(section));
  }

  if (searchInput) {
    let timer = 0;
    searchInput.addEventListener("input", () => {
      window.clearTimeout(timer);
      timer = window.setTimeout(() => {
        applySearch(searchInput.value);
      }, 120);
    });
  }
})();
