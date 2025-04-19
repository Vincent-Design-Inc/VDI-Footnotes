document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.footnote-ref a').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      const footnoteContent = document.querySelector(targetId).innerHTML;

      // Remove existing tooltips
      document.querySelectorAll('.footnote-tooltip').forEach(t => t.remove());

      // Create new tooltip
      const tooltip = document.createElement('div');
      tooltip.className = 'footnote-tooltip';
      tooltip.innerHTML = `
                <div class="tooltip-content">
                    ${footnoteContent}
                    <button class="tooltip-close">&times;</button>
                </div>
            `;

      document.body.appendChild(tooltip);

      // Position calculations
      const linkRect = this.getBoundingClientRect();
      const tooltipHeight = tooltip.offsetHeight;
      const viewportHeight = window.innerHeight;

      // Calculate available space
      let topPos = window.scrollY + linkRect.top - tooltipHeight - 10;
      if (topPos < 10 || (topPos + tooltipHeight) > viewportHeight) {
        topPos = window.scrollY + linkRect.bottom + 10;
      }

      tooltip.style.cssText = `
                position: absolute;
                left: ${Math.min(linkRect.left, window.innerWidth - 300)}px;
                top: ${topPos}px;
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            `;

      // Fade in
      setTimeout(() => {
        tooltip.style.opacity = '1';
        tooltip.style.pointerEvents = 'auto';
      }, 50);

      // Close button handler
      const closeBtn = tooltip.querySelector('.tooltip-close');
      closeBtn.addEventListener('click', function () {
        tooltip.style.opacity = '0';
        tooltip.style.pointerEvents = 'none';
        setTimeout(() => tooltip.remove(), 300);
      });

      // Close on outside click with proper timing
      let clickListener;
      const setupClickListener = () => {
        clickListener = function (event) {
          if (!tooltip.contains(event.target)) {
            tooltip.style.opacity = '0';
            tooltip.style.pointerEvents = 'none';
            setTimeout(() => tooltip.remove(), 300);
            document.removeEventListener('click', clickListener);
          }
        };
        document.addEventListener('click', clickListener);
      };

      // Delay click listener to avoid immediate closure
      setTimeout(setupClickListener, 50);
    });
  });
});
