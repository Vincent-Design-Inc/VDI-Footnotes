document.addEventListener('DOMContentLoaded', function () {
  let activeTooltip = null;
  let originalFocusElement = null;

  function closeTooltip() {
    if (activeTooltip) {
      activeTooltip.remove();
      activeTooltip = null;
      if (originalFocusElement) {
        originalFocusElement.focus();
        originalFocusElement = null;
      }
    }
  }

  function handleKeydown(e) {
    if (!activeTooltip) return;

    const focusable = Array.from(activeTooltip.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    ));
    const firstFocusable = focusable[0];
    const lastFocusable = focusable[focusable.length - 1];

    if (e.key === 'Escape') {
      closeTooltip();
      return;
    }

    if (e.key === 'Tab') {
      if (focusable.length === 0) {
        e.preventDefault();
        return;
      }

      if (e.shiftKey) {
        if (document.activeElement === firstFocusable) {
          lastFocusable.focus();
          e.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusable) {
          firstFocusable.focus();
          e.preventDefault();
        }
      }
    }
  }

  document.querySelectorAll('.footnote-ref a').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      originalFocusElement = this;

      if (activeTooltip) closeTooltip();

      const targetId = this.getAttribute('href');
      const footnoteContent = document.querySelector(targetId).innerHTML;

      const tooltip = document.createElement('div');
      tooltip.className = 'footnote-tooltip';
      tooltip.setAttribute('role', 'dialog');
      tooltip.setAttribute('aria-labelledby', 'tooltip-content');
      tooltip.innerHTML = `
                <div class="tooltip-content" tabindex="0" id="tooltip-content">
                    ${footnoteContent}
                    <button class="tooltip-close" aria-label="Close Tooltip">&times;</button>
                </div>
            `;

      document.body.appendChild(tooltip);
      activeTooltip = tooltip;

      // Position tooltip
      const linkRect = this.getBoundingClientRect();
      tooltip.style.left = `${window.scrollX + linkRect.left}px`;
      tooltip.style.top = `${window.scrollY + linkRect.top - tooltip.offsetHeight - 10}px`;

      // Set up close button
      const closeBtn = tooltip.querySelector('.tooltip-close');
      closeBtn.addEventListener('click', closeTooltip);

      // Focus management
      closeBtn.focus();

      // Event listeners
      document.addEventListener('keydown', handleKeydown);
      document.addEventListener('click', function clickOutside(e) {
        if (!tooltip.contains(e.target)) {
          closeTooltip();
        }
      });
    });
  });
});
