document.addEventListener("DOMContentLoaded", function () {
  const settings = window.vdiFootnoteSettings || {};

  // Smooth scroll behavior
  if (settings.enableSmoothScroll) {
    document.addEventListener("click", function (e) {
      const anchor = e.target.closest('a[href^="#footnote"], a[href^="#footnote-ref"]');
      if (!anchor) return;

      const targetId = anchor.getAttribute("href").slice(1);
      const targetEl = document.getElementById(targetId);

      if (targetEl) {
        e.preventDefault();
        targetEl.scrollIntoView({
          behavior: "smooth",
          block: "center"
        });
        history.pushState(null, "", "#" + targetId);
      }
    });
  }

  // Inline mobile display
  if (settings.enableInlineMobile) {
    const isMobile = window.matchMedia("(max-width: 768px)").matches;
    if (isMobile) {
      const links = document.querySelectorAll(".vdi-footnote-link");
      links.forEach(link => {
        link.addEventListener("click", function (e) {
          e.preventDefault();

          const existing = document.querySelector(".vdi-inline-footnote");
          if (existing) existing.remove();

          const noteText = this.dataset.footnote;
          const span = document.createElement("span");
          span.className = "vdi-inline-footnote inline-block ml-2 px-2 py-1 bg-primary-100 border border-primary-300 rounded text-xs text-primary-800 max-w-[80%]";
          span.textContent = noteText;
          this.parentNode.appendChild(span);
        });
      });

      document.addEventListener("click", function (e) {
        if (!e.target.closest(".vdi-footnote-link")) {
          const inlineNote = document.querySelector(".vdi-inline-footnote");
          if (inlineNote) inlineNote.remove();
        }
      });
    }
  }
});
