## 2026-06-25 - [Loading States & Tab Delegation]
**Learning:** Standardizing asynchronous feedback via a `.btn-loading` class prevents duplicate form submissions and provides immediate visual confirmation without breaking the established phpMyAdmin-inspired theme. Delegating tab navigation to a central `switchTab` function ensures that side-effects like data fetching (e.g., loading the user list for the Admin tab) are consistently executed regardless of how the tab was activated.
**Action:** Always use `switchTab` for navigation and apply `.btn-loading` to any button triggering an API call.
