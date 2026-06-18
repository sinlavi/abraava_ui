## 2026-06-18 - [Accessibility and Interaction Improvements]
**Learning:** Table-heavy interfaces (like phpMyAdmin style) benefit significantly from row-level selection to reduce precision clicking for users. ARIA labels on icon-only buttons are essential for these complex dashboards to remain screen-reader accessible.
**Action:** Always implement row-click selection for data tables and ensure all interactive icons have descriptive ARIA labels.

**Learning:** Separating authentication logic from dashboard logic at the file level (index.html vs music.html) improves security and performance by reducing the payload for unauthenticated users.
**Action:** Use separate entry points for auth and app views in future projects with similar dashboard architectures.
