## 2026-06-22 - Improved Admin UX & Accessibility
**Learning:** Hardcoding browser native `confirm()` and `alert()` feels disconnected from a custom-themed UI like this phpMyAdmin-inspired one. Using a custom modal system ensures visual consistency and better control over the interaction flow. Additionally, asynchronous actions like login/signup or user creation must have immediate visual feedback (loading spinners) and disabled states to prevent double-submissions and provide a smooth feel.
**Action:** Always prefer `showModal()` over `confirm()` for destructive actions and ensure all async buttons use `.btn-loading` class.

**Learning:** Icon-only buttons are invisible to screen readers without ARIA labels. Focus indicators are critical for keyboard navigation but should only be prominent when using the keyboard (`:focus-visible`).
**Action:** Add `aria-label` to all icon buttons and implement `focus-visible` styles in global CSS.
