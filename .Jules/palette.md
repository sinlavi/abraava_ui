## 2026-06-21 - [ARIA Labels for Icon-Only Buttons]
**Learning:** Icon-only buttons without ARIA labels are inaccessible to screen readers. In a phpMyAdmin-inspired interface with many compact action buttons, descriptive ARIA labels are essential for usability.
**Action:** Always provide `aria-label` to buttons that only contain `<i>` or `<span>` icons.

## 2026-06-21 - [Focus-Visible for Keyboard Navigation]
**Learning:** Default browser focus rings can be inconsistent or poorly visible against certain backgrounds. Using `:focus-visible` ensures that keyboard users have a clear indicator of their current interactive element without cluttering the UI for mouse users.
**Action:** Include `:focus-visible` styles with sufficient contrast for all interactive elements.
