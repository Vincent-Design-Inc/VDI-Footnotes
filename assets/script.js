// script.js

document.addEventListener('DOMContentLoaded', () => {
  const refs = document.querySelectorAll('.footnote-ref a');

  refs.forEach(ref => {
    const container = ref.closest('.footnote-ref');
    const content = container?.dataset.note?.trim();
    if (!content) return;

    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'footnote-tooltip';
    tooltip.setAttribute('role', 'dialog');
    tooltip.setAttribute('aria-modal', 'true');
    tooltip.setAttribute('tabindex', '-1');

    const close = document.createElement('button');
    close.className = 'tooltip-close';
    close.innerHTML = '&times;';
    close.setAttribute('aria-label', 'Close footnote');

    close.addEventListener('click', () => tooltip.remove());
    close.addEventListener('keydown', e => {
      if (e.key === 'Escape') tooltip.remove();
    });

    const tooltipContent = document.createElement('div');
    tooltipContent.className = 'tooltip-content';
    tooltipContent.innerHTML = content;

    tooltip.appendChild(close);
    tooltip.appendChild(tooltipContent);

    // Append once to avoid re-adding
    document.body.appendChild(tooltip);
    tooltip.remove();

    function openTooltip(e) {
      e.preventDefault();

      document.querySelectorAll('.footnote-tooltip').forEach(t => t.remove());

      const rect = ref.getBoundingClientRect();
      tooltip.style.top = `${window.scrollY + rect.bottom + 5}px`;
      tooltip.style.left = `${window.scrollX + rect.left}px`;

      document.body.appendChild(tooltip);
      tooltip.focus();

      // Outside click handler
      const handleClickOutside = event => {
        if (!tooltip.contains(event.target) && event.target !== ref) {
          tooltip.remove();
          document.removeEventListener('click', handleClickOutside);
        }
      };
      setTimeout(() => document.addEventListener('click', handleClickOutside));
    }

    ref.addEventListener('click', openTooltip);
    ref.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        openTooltip(e);
      }
    });

    ref.setAttribute('tabindex', '0');
    ref.setAttribute('role', 'button');
    ref.setAttribute('aria-haspopup', 'dialog');
  });
});
