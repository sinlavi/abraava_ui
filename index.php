<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>localhost &raquo; iTunes_Catalog | phpMyAdmin Core Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 11px;
            background: #f3f3f3;
            color: #222;
            overflow: hidden;
            height: 100vh;
        }

        .pmah-layout {
            display: flex;
            height: 100vh;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        /* ---- Mobile Header ---- */
        .mobile-header-bar {
            display: none;
            background: #235a81;
            color: #fff;
            padding: 10px 15px;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 45px;
            z-index: 100;
        }
        .mobile-header-bar .header-title {
            font-weight: 500;
            font-size: 13px;
        }
        .menu-toggle-btn,
        .props-toggle-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 8px;
            display: flex;
            align-items: center;
        }

        /* ---- Left Nav ---- */
        .left-nav {
            width: 320px;
            background: #f3f3f3;
            border-right: 1px solid #d3d3d3;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 90;
        }
        .left-header {
            background: #235a81;
            color: #fff;
            padding: 10px 12px;
            font-weight: bold;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid #1d4a6b;
            flex-shrink: 0;
        }
        .left-header .badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            margin-left: auto;
        }

        .search-panel {
            padding: 8px 10px;
            background: #e1e1e1;
            border-bottom: 1px solid #ccced1;
            flex-shrink: 0;
        }
        .search-panel input,
        .search-panel select {
            width: 100%;
            margin-bottom: 6px;
            padding: 5px 8px;
            border: 1px solid #aaa;
            border-radius: 2px;
            font-size: 11px;
            background: #fff;
            height: 28px;
        }
        .search-panel input:focus,
        .search-panel select:focus {
            border-color: #235a81;
            outline: none;
        }
        .search-row {
            display: flex;
            gap: 4px;
        }
        .search-row select {
            flex: 1;
            margin-bottom: 0;
        }
        .search-panel button {
            width: 100%;
            justify-content: center;
        }

        .tree-container {
            flex: 1;
            overflow-y: auto;
            background: #f3f3f3;
            padding: 4px;
        }
        .tree-node {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .tree-node li {
            list-style: none;
            margin: 1px 0;
        }
        .tree-toggle {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 6px;
            border-radius: 2px;
            cursor: pointer;
        }
        .tree-toggle:hover {
            background: #eaf2f7;
            color: #b50000;
        }
        .tree-toggle input[type="checkbox"] {
            margin: 0;
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }
        .tree-toggle i {
            width: 14px;
            text-align: center;
            color: #555;
            font-size: 11px;
            flex-shrink: 0;
        }
        .tree-toggle .node-label {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }
        .tree-toggle .node-label .sub-info {
            font-size: 9px;
            color: #666;
            font-weight: normal;
        }
        .tree-toggle .node-count {
            font-size: 9px;
            color: #888;
            margin-left: auto;
            padding-left: 6px;
            flex-shrink: 0;
        }
        .tree-children {
            margin-left: 14px;
            padding-left: 6px;
            border-left: 1px dotted #aaa;
            display: none;
        }
        .tree-node li.open>.tree-children {
            display: block;
        }
        .active-node {
            background: #d0e0ec !important;
            font-weight: bold;
            color: #000 !important;
        }
        .active-node .sub-info {
            color: #000 !important;
        }

        .bulk-bar {
            background: #fff3cd;
            padding: 4px 8px;
            margin: 3px 4px;
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            align-items: center;
            font-size: 10px;
            border: 1px solid #ffeeba;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .bulk-bar .count-badge {
            background: #235a81;
            color: #fff;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 9px;
        }
        .bulk-bar .btn-sm {
            padding: 2px 8px;
            font-size: 10px;
            min-height: 22px;
        }

        /* ---- Main Content ---- */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            overflow: hidden;
            width: 100%;
            padding-bottom: 72px;
            /* room for player */;
        }

        .tabs {
            background: #e1e1e1;
            border-bottom: 1px solid #ccced1;
            display: flex;
            padding-left: 8px;
            padding-top: 6px;
            gap: 2px;
            flex-shrink: 0;
        }
        .tab {
            padding: 5px 14px;
            font-size: 11px;
            background: #eaeaea;
            border: 1px solid #ccced1;
            border-bottom: none;
            border-radius: 3px 3px 0 0;
            cursor: pointer;
            color: #555;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tab.active {
            background: #fff;
            border-bottom: 1px solid #fff;
            font-weight: bold;
            color: #000;
            margin-bottom: -1px;
            position: relative;
            z-index: 2;
        }
        .tab .tab-badge {
            background: #235a81;
            color: #fff;
            padding: 0 6px;
            border-radius: 8px;
            font-size: 8px;
            line-height: 14px;
        }

        .filter-bar {
            background: #f8f9fa;
            padding: 4px 12px;
            border-bottom: 1px solid #d3d3d3;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
            flex-shrink: 0;
        }
        .filter-bar select,
        .filter-bar input {
            padding: 3px 6px;
            border: 1px solid #ccc;
            border-radius: 2px;
            font-size: 10px;
            height: 24px;
            background: #fff;
        }
        .filter-bar label {
            font-size: 10px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .filter-bar .filter-count {
            font-size: 10px;
            color: #555;
            padding: 2px 8px;
            background: #eef3f7;
            border-radius: 2px;
        }
        .filter-bar .btn-sm {
            padding: 2px 8px;
            font-size: 10px;
            min-height: 22px;
        }

        .tab-content {
            flex: 1;
            overflow-y: auto;
        }
        .tab-pane {
            display: none;
            height: 100%;
            flex-direction: column;
        }
        .tab-pane.active-pane {
            display: flex;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            background: #fff;
        }
        .data-table th {
            background: linear-gradient(#fff, #eef3f7);
            font-weight: bold;
            color: #235a81;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #d3d3d3;
            position: sticky;
            top: 0;
            z-index: 5;
            white-space: nowrap;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e3e3e3;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f7f7f7;
        }
        .data-table tr:hover td {
            background-color: #eaf2f7 !important;
        }
        .data-table .checkbox-col {
            width: 24px;
            text-align: center;
        }

        .table-wrapper {
            overflow-y: auto;
            flex: 1;
            padding: 6px 10px;
            position: relative;
        }
        .table-wrapper .data-table {
            margin-bottom: 0;
        }

        .queue-stats {
            background: #eaf2f7;
            padding: 4px 12px;
            font-size: 10px;
            border-bottom: 1px solid #d3d3d3;
            color: #235a81;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .queue-stats .stat-group {
            display: flex;
            gap: 12px;
        }
        .queue-stats .stat-item {
            font-weight: normal;
            color: #555;
        }
        .queue-stats .stat-item strong {
            color: #235a81;
        }

        .toolbar {
            padding: 4px 12px;
            background: #f3f3f3;
            border-bottom: 1px solid #d3d3d3;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .toolbar>div {
            display: flex;
            gap: 3px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ---- Right Properties ---- */
        .right-properties {
            width: 270px;
            background: #f3f3f3;
            border-left: 1px solid #d3d3d3;
            overflow-y: auto;
            padding: 8px;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 85;
        }
        .props-card {
            background: #fff;
            border: 1px solid #d3d3d3;
            padding: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .props-artwork {
            width: 100%;
            height: auto;
            max-width: 120px;
            margin: 0 auto 8px;
            display: block;
            border: 1px solid #ccc;
            padding: 2px;
            background: #fff;
            border-radius: 2px;
        }
        .props-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #235a81;
            text-align: center;
            cursor: pointer;
        }
        .props-title:hover {
            text-decoration: underline;
            color: #b50000;
        }
        .props-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin: 2px 0;
            border-bottom: 1px dashed #eee;
            padding-bottom: 2px;
            gap: 8px;
        }
        .props-row span {
            color: #666;
            text-align: left;
            flex-shrink: 0;
        }
        .props-row strong {
            text-align: right;
            word-break: break-all;
            font-weight: 600;
        }
        .mirror-btn {
            background: #e8f0fe;
            border: 1px solid #b6c8e0;
            color: #1a4b7a;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            text-decoration: none;
        }
        .mirror-btn:hover {
            background: #d0e0ec;
            border-color: #8aa9c9;
        }
        .lyrics-box {
            background: #f9fafb;
            border: 1px solid #e3e8ee;
            padding: 6px;
            max-height: 120px;
            overflow-y: auto;
            font-size: 9px;
            white-space: pre-wrap;
            border-radius: 2px;
            margin-top: 4px;
        }
        .pma-header {
            background: #f3f3f3;
            border: 1px solid #d3d3d3;
            padding: 6px 12px;
            margin: 6px 10px 4px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .pma-header h2,
        .pma-header h3 {
            font-size: 11px;
            color: #235a81;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
        }
        .pma-doc-box {
            background: #fff;
            border: 1px solid #d3d3d3;
            margin: 0 10px 10px;
            padding: 10px;
            overflow-y: auto;
            flex: 1;
        }
        .profile-container {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 12px;
        }
        .profile-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 6px;
            align-content: start;
        }
        .meta-block {
            background: #f8fafd;
            border: 1px solid #dbe3eb;
            padding: 4px 8px;
            border-radius: 2px;
        }
        .meta-block label {
            font-size: 8px;
            color: #718393;
            font-weight: bold;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .meta-block div {
            font-size: 10px;
            font-weight: 600;
            color: #1e364a;
            word-break: break-all;
        }

        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }
        .status-downloading {
            background: #cce5ff;
            color: #004085;
            border-color: #b8daff;
        }
        .status-paused {
            background: #e2e3e5;
            color: #383d41;
            border-color: #d6d8db;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .status-stopped {
            background: #e2e3e5;
            color: #383d41;
            border-color: #d6d8db;
        }
        .status-queued {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        .empty-msg {
            color: #666;
            text-align: center;
            padding: 20px;
            font-style: italic;
            font-size: 11px;
        }
        .error-msg {
            color: #b91c1c;
            font-size: 9px;
            display: inline-block;
        }
        .clickable-link {
            color: #235a81;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }
        .clickable-link:hover {
            text-decoration: underline;
            color: #b50000;
        }
        .action-buttons {
            display: flex;
            gap: 2px;
            flex-wrap: wrap;
        }
        .action-buttons button {
            padding: 1px 6px;
            font-size: 9px;
            min-height: 20px;
            border-radius: 2px;
        }
        .mirror-group {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
            margin-top: 1px;
        }
        .layout-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            z-index: 80;
        }
        .toast-message {
            position: fixed;
            bottom: 90px;
            right: 20px;
            background: #333;
            color: #fff;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
            max-width: 400px;
        }
        .toast-message.show {
            opacity: 1;
        }
        .tree-loading {
            padding: 3px 10px;
            color: #666;
            font-size: 9px;
        }

        .btn,
        button:not(.tab),
        .bulk-btn,
        .add-btn,
        .action-buttons button,
        .search-panel button {
            background: linear-gradient(#fff, #e6e6e6);
            border: 1px solid #b3b3b3;
            border-radius: 2px;
            padding: 4px 8px;
            font-size: 10px;
            font-family: inherit;
            cursor: pointer;
            color: #333;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
            min-height: 24px;
        }
        .btn:hover,
        button:not(.tab):hover,
        .bulk-btn:hover,
        .add-btn:hover,
        .action-buttons button:hover,
        .search-panel button:hover {
            background: linear-gradient(#f2f2f2, #ccc);
            border-color: #888;
        }
        .btn-sm {
            padding: 2px 6px;
            font-size: 9px;
            min-height: 20px;
        }
        .btn-danger,
        #clearFailedBtn,
        #queueBulkDeleteBtn {
            background: linear-gradient(#fff5f5, #ffe0e0);
            border-color: #d99696;
            color: #a91717;
        }
        .btn-danger:hover,
        #clearFailedBtn:hover,
        #queueBulkDeleteBtn:hover {
            background: linear-gradient(#ffe0e0, #ffcccc);
            border-color: #b85c5c;
        }
        .btn-success {
            background: linear-gradient(#e6f5e6, #cce8cc);
            border-color: #8ab88a;
            color: #1a6b1a;
        }
        .btn-success:hover {
            background: linear-gradient(#cce8cc, #b3d9b3);
        }
        .btn-primary {
            background: linear-gradient(#e8f0fe, #d0e0ec);
            border-color: #8aa9c9;
            color: #1a4b7a;
        }
        .btn-primary:hover {
            background: linear-gradient(#d0e0ec, #b6c8e0);
        }
        .btn-warning {
            background: linear-gradient(#fff8e6, #ffebcc);
            border-color: #d4b88a;
            color: #7a5a1a;
        }
        .btn-warning:hover {
            background: linear-gradient(#ffebcc, #ffdeb3);
        }
        .btn-play {
            background: linear-gradient(#1a6b1a, #2a8a2a);
            border-color: #1a6b1a;
            color: #fff;
        }
        .btn-play:hover {
            background: linear-gradient(#2a8a2a, #3aaa3a);
            border-color: #1a6b1a;
        }

        .progress-bar {
            height: 3px;
            background: #e3e3e3;
            border-radius: 2px;
            overflow: hidden;
            width: 60px;
            display: inline-block;
            vertical-align: middle;
        }
        .progress-bar .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #235a81, #4a9bc7);
            border-radius: 2px;
            transition: width 0.3s;
        }

        hr {
            margin: 4px 0;
            border: 0;
            border-top: 1px solid #d3d3d3;
        }
        .text-muted {
            color: #888;
        }
        .text-small {
            font-size: 9px;
        }

        .node-actions {
            display: flex;
            gap: 2px;
            margin-left: auto;
            flex-shrink: 0;
        }
        .node-actions button {
            padding: 0 4px;
            font-size: 8px;
            min-height: 16px;
            background: transparent;
            border: none;
            color: #888;
            cursor: pointer;
        }
        .node-actions button:hover {
            color: #235a81;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #fff;
            border-radius: 4px;
            padding: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .modal-box h3 {
            font-size: 13px;
            margin-bottom: 12px;
            color: #235a81;
        }
        .modal-box .modal-actions {
            display: flex;
            gap: 6px;
            margin-top: 12px;
            justify-content: flex-end;
        }
        .modal-box .modal-actions button {
            padding: 4px 14px;
        }

        .shortcut-hint {
            font-size: 8px;
            color: #999;
            border: 1px solid #ddd;
            border-radius: 2px;
            padding: 0 4px;
        }

        /* ===== AUDIO PLAYER ===== */
        .audio-player-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 72px;
            background: linear-gradient(180deg, #f0f4f8, #dce4ec);
            border-top: 2px solid #235a81;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 14px;
            z-index: 200;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(4px);
            user-select: none;
        }
        .audio-player-bar .ap-artwork {
            width: 52px;
            height: 52px;
            border-radius: 4px;
            background: #ccc;
            flex-shrink: 0;
            border: 1px solid #b3b3b3;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #888;
        }
        .audio-player-bar .ap-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .audio-player-bar .ap-info .ap-title {
            font-size: 12px;
            font-weight: 600;
            color: #1a3a5a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .audio-player-bar .ap-info .ap-artist {
            font-size: 10px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .audio-player-bar .ap-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .audio-player-bar .ap-controls button {
            background: none;
            border: none;
            font-size: 20px;
            color: #235a81;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            min-width: 32px;
            transition: background 0.15s;
        }
        .audio-player-bar .ap-controls button:hover {
            background: rgba(35, 90, 129, 0.12);
        }
        .audio-player-bar .ap-controls button.ap-play-btn {
            font-size: 26px;
            color: #1a6b1a;
        }
        .audio-player-bar .ap-controls button.ap-play-btn:hover {
            background: rgba(26, 107, 26, 0.15);
        }
        .audio-player-bar .ap-progress-wrap {
            flex: 1;
            min-width: 100px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .audio-player-bar .ap-progress-wrap input[type="range"] {
            flex: 1;
            height: 4px;
            -webkit-appearance: none;
            appearance: none;
            background: #c0cddb;
            border-radius: 2px;
            outline: none;
            min-width: 40px;
        }
        .audio-player-bar .ap-progress-wrap input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #235a81;
            cursor: pointer;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }
        .audio-player-bar .ap-progress-wrap input[type="range"]::-moz-range-thumb {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #235a81;
            cursor: pointer;
            border: 2px solid #fff;
        }
        .audio-player-bar .ap-time {
            font-size: 10px;
            color: #555;
            font-variant-numeric: tabular-nums;
            min-width: 70px;
            text-align: center;
            flex-shrink: 0;
        }
        .audio-player-bar .ap-volume-wrap {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .audio-player-bar .ap-volume-wrap i {
            color: #555;
            font-size: 14px;
        }
        .audio-player-bar .ap-volume-wrap input[type="range"] {
            width: 60px;
            height: 3px;
            -webkit-appearance: none;
            appearance: none;
            background: #c0cddb;
            border-radius: 2px;
            outline: none;
        }
        .audio-player-bar .ap-volume-wrap input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #235a81;
            cursor: pointer;
            border: 1px solid #fff;
        }
        .audio-player-bar .ap-volume-wrap input[type="range"]::-moz-range-thumb {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #235a81;
            cursor: pointer;
            border: 1px solid #fff;
        }
        .audio-player-bar .ap-close-btn {
            background: none;
            border: none;
            color: #888;
            font-size: 16px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .audio-player-bar .ap-close-btn:hover {
            color: #b50000;
            background: rgba(185, 0, 0, 0.08);
        }

        /* ---- responsive ---- */
        @media (max-width: 768px) {
            body {
                overflow: visible;
            }
            .mobile-header-bar {
                display: flex;
            }
            .left-nav {
                position: fixed;
                top: 45px;
                left: 0;
                bottom: 0;
                width: 280px;
                transform: translateX(-100%);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            }
            .left-nav.open-drawer {
                transform: translateX(0);
            }
            .right-properties {
                position: fixed;
                top: 45px;
                right: 0;
                bottom: 0;
                width: 260px;
                transform: translateX(100%);
                border-left: none;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            }
            .right-properties.open-drawer {
                transform: translateX(0);
            }
            .layout-overlay.active {
                display: block;
            }
            .data-table,
            .data-table thead,
            .data-table tbody,
            .data-table th,
            .data-table td,
            .data-table tr {
                display: block;
            }
            .data-table thead {
                display: none;
            }
            .data-table tr {
                border: 1px solid #cbd5e1;
                margin-bottom: 8px;
                border-radius: 4px;
                background: #fff;
                padding: 6px;
            }
            .data-table td {
                border: none;
                padding: 3px 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                width: 100% !important;
            }
            .data-table td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #235a81;
                float: left;
                text-align: left;
                padding-right: 8px;
                font-size: 9px;
                text-transform: uppercase;
            }
            .data-table td.checkbox-col {
                text-align: left;
                justify-content: flex-start;
                border-bottom: 1px dashed #eee;
                padding-bottom: 4px;
                margin-bottom: 2px;
            }
            .data-table td.checkbox-col::before {
                content: "Select Row: ";
                margin-right: auto;
            }
            .data-table .action-buttons {
                justify-content: flex-end;
                width: 100%;
                flex-wrap: wrap;
                margin-top: 2px;
                padding-top: 4px;
                border-top: 1px dashed #eee;
            }
            .profile-container {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }
            .profile-meta-grid {
                grid-template-columns: 1fr;
                width: 100%;
            }
            .pma-header,
            .pma-doc-box {
                margin: 4px;
                padding: 6px;
            }
            .pma-header h2 {
                font-size: 10px;
            }
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .toolbar>div {
                width: 100%;
                justify-content: space-between;
            }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .queue-stats {
                flex-direction: column;
                align-items: stretch;
                gap: 4px;
            }
            .queue-stats .stat-group {
                flex-wrap: wrap;
            }
            .bulk-bar {
                flex-wrap: wrap;
            }

            .audio-player-bar {
                flex-wrap: wrap;
                height: auto;
                min-height: 72px;
                padding: 8px 12px;
                gap: 8px;
            }
            .audio-player-bar .ap-artwork {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            .audio-player-bar .ap-progress-wrap {
                order: 10;
                flex-basis: 100%;
                min-width: unset;
            }
            .audio-player-bar .ap-controls {
                gap: 2px;
            }
            .audio-player-bar .ap-controls button {
                font-size: 16px;
                min-width: 28px;
                min-height: 28px;
                padding: 2px 4px;
            }
            .audio-player-bar .ap-controls button.ap-play-btn {
                font-size: 20px;
            }
            .audio-player-bar .ap-volume-wrap input[type="range"] {
                width: 40px;
            }
            .audio-player-bar .ap-time {
                min-width: 50px;
                font-size: 9px;
            }
            .main-content {
                padding-bottom: 100px;
            }
            .toast-message {
                bottom: 110px;
                right: 10px;
                left: 10px;
                max-width: unset;
            }
        }
        @media (max-width: 480px) {
            .audio-player-bar .ap-info .ap-title {
                font-size: 10px;
            }
            .audio-player-bar .ap-info .ap-artist {
                font-size: 9px;
            }
            .audio-player-bar .ap-volume-wrap input[type="range"] {
                width: 30px;
            }
            .audio-player-bar .ap-controls button {
                font-size: 14px;
                min-width: 24px;
                min-height: 24px;
            }
            .audio-player-bar .ap-controls button.ap-play-btn {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Header -->
<div class="mobile-header-bar">
    <button class="menu-toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>
    <span class="header-title">localhost &raquo; iTunes</span>
    <button class="props-toggle-btn" id="propsToggle"><i class="fas fa-info-circle"></i></button>
</div>

<div class="layout-overlay" id="layoutOverlay"></div>

<!-- Main Layout -->
<div class="pmah-layout">
    <!-- Left Nav -->
    <div class="left-nav" id="leftSidebar">
        <div class="left-header">
            <i class="fas fa-server"></i>
            <span>iTunes_Catalog</span>
            <span class="badge" id="treeTotalCount">0 items</span>
        </div>
        <div class="search-panel">
            <input type="text" id="searchTerm" placeholder="Search criteria..." value="Pink Floyd" />
            <div class="search-row">
                <select id="entityType">
                    <option value="musicArtist,album,song">All Types</option>
                    <option value="musicArtist">Artists</option>
                    <option value="album">Albums</option>
                    <option value="song">Tracks</option>
                </select>
                <select id="resultFilter">
                    <option value="all">All Results</option>
                    <option value="artist">Artists Only</option>
                    <option value="collection">Albums Only</option>
                    <option value="track">Tracks Only</option>
                </select>
            </div>
            <button id="doSearchBtn"><i class="fas fa-search"></i> Query Database</button>
        </div>
        <div class="tree-container" id="treeContainer">
            <div class="empty-msg"><i class="fas fa-search"></i> Execute query above to construct tree nodes.</div>
        </div>
        <div id="treeBulkBar" class="bulk-bar" style="display:none;">
            <i class="fas fa-check-square"></i> <span id="treeSelectedCount">0</span> selected
            <button id="treeBulkAddBtn" class="btn-sm"><i class="fas fa-plus"></i> Import</button>
            <button id="treeSelectAllBtn" class="btn-sm btn-primary"><i class="fas fa-check-double"></i> All</button>
            <button id="treeSelectNoneBtn" class="btn-sm"><i class="fas fa-times"></i> Clear</button>
            <button id="treeExpandAllBtn" class="btn-sm"><i class="fas fa-expand"></i> Expand</button>
            <button id="treeCollapseAllBtn" class="btn-sm"><i class="fas fa-compress"></i> Collapse</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="tabs">
            <div class="tab active" data-tab="queue"><i class="fas fa-tasks"></i> Operations Queue <span class="tab-badge" id="queueTabBadge">0</span></div>
            <div class="tab" data-tab="browse" id="browseTabHead"><i class="fas fa-table"></i> Browse Element</div>
            <div class="tab" data-tab="stats"><i class="fas fa-chart-bar"></i> Statistics</div>
        </div>

        <!-- Queue Tab -->
        <div id="queue-tab" class="tab-pane active-pane">
            <div class="queue-stats">
                <span><i class="fas fa-database"></i> <span id="queueStats">Loading...</span></span>
                <div class="stat-group">
                    <span class="stat-item"><i class="fas fa-clock"></i> Pending: <strong id="statPending">0</strong></span>
                    <span class="stat-item"><i class="fas fa-spinner fa-pulse"></i> Downloading: <strong id="statDownloading">0</strong></span>
                    <span class="stat-item"><i class="fas fa-pause"></i> Paused: <strong id="statPaused">0</strong></span>
                    <span class="stat-item"><i class="fas fa-check-circle"></i> Completed: <strong id="statCompleted">0</strong></span>
                    <span class="stat-item"><i class="fas fa-exclamation-triangle"></i> Failed: <strong id="statFailed">0</strong></span>
                </div>
            </div>
            <div class="filter-bar">
                <label><i class="fas fa-filter"></i> Status:</label>
                <select id="queueStatusFilter">
                    <option value="pending">Pending</option>
                    <option value="downloading">Downloading</option>
                    <option value="paused">Paused</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="stopped">Stopped</option>
                    <option value="all" selected>All</option>
                </select>
                <label><i class="fas fa-search"></i> Search:</label>
                <input type="text" id="queueSearchFilter" placeholder="Track or artist..." style="width:140px;" />
                <label><i class="fas fa-sort"></i> Sort:</label>
                <select id="queueSortBy">
                    <option value="id">ID</option>
                    <option value="status">Status</option>
                    <option value="track">Track</option>
                    <option value="added">Added</option>
                </select>
                <span id="queueFilterCount" class="filter-count">Showing: 0</span>
                <button id="clearQueueFilters" class="btn-sm"><i class="fas fa-undo"></i> Reset</button>
                <button id="exportQueueBtn" class="btn-sm btn-primary"><i class="fas fa-file-export"></i> Export</button>
            </div>
            <div class="toolbar">
                <div>
                    <button id="refreshQueueBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
                    <button id="clearFailedBtn" class="btn-danger"><i class="fas fa-trash-alt"></i> Clear Failed</button>
                    <button id="retryFailedBtn" class="btn-warning"><i class="fas fa-redo"></i> Retry Failed</button>
                    <button id="queueSelectAllBtn" class="btn-primary"><i class="fas fa-check-double"></i> Select All</button>
                    <button id="queueSelectNoneBtn" class="btn"><i class="fas fa-times"></i> Deselect</button>
                    <button id="queueInvertBtn" class="btn"><i class="fas fa-exchange-alt"></i> Invert</button>
                </div>
                <div id="queueBulkBar" style="display:none; background:#fff3cd; padding:2px 8px; border:1px solid #ffeeba; align-items:center; gap:4px; border-radius:2px;">
                    <span><i class="fas fa-layer-group"></i> <span id="queueSelectedCount">0</span> selected</span>
                    <button id="queueBulkStartBtn" class="btn-sm btn-success"><i class="fas fa-play"></i></button>
                    <button id="queueBulkPauseBtn" class="btn-sm btn-warning"><i class="fas fa-pause"></i></button>
                    <button id="queueBulkResumeBtn" class="btn-sm btn-primary"><i class="fas fa-redo"></i></button>
                    <button id="queueBulkStopBtn" class="btn-sm btn-danger"><i class="fas fa-stop"></i></button>
                    <button id="queueBulkRetryBtn" class="btn-sm"><i class="fas fa-sync"></i></button>
                    <button id="queueBulkDeleteBtn" class="btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="table-wrapper" id="queueTableWrapper">
                <table class="data-table" id="queueTable">
                    <thead>
                    <tr>
                        <th class="checkbox-col"><input type="checkbox" id="queueSelectAll" /></th>
                        <th>ID</th>
                        <th>Track</th>
                        <th>Artist</th>
                        <th>Quality</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Mirrors</th>
                        <th>Info</th>
                    </tr>
                    </thead>
                    <tbody id="queueBody">
                    <tr><td colspan="9" class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Browse Tab -->
        <div id="browse-tab" class="tab-pane">
            <div id="browseWorkspace" style="overflow-y:auto; flex:1; display:flex; flex-direction:column;">
                <div class="empty-msg" style="margin-top:60px;">
                    <i class="fas fa-table fa-2x" style="color:#235a81; margin-bottom:10px;"></i><br />
                    No relational database element selected for inspection.<br />Select any row item from the left hierarchy node array to query its details.
                </div>
            </div>
        </div>

        <!-- Stats Tab -->
        <div id="stats-tab" class="tab-pane">
            <div id="statsWorkspace" style="overflow-y:auto; flex:1; padding:12px;">
                <div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading statistics...</div>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="right-properties" id="rightPanel">
        <div class="props-card"><div class="empty-msg"><i class="fas fa-info-circle"></i> No node selected.</div></div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast-message"></div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <h3 id="modalTitle">Confirm</h3>
        <div id="modalBody">Are you sure?</div>
        <div class="modal-actions">
            <button id="modalCancelBtn" class="btn">Cancel</button>
            <button id="modalConfirmBtn" class="btn btn-primary">Confirm</button>
        </div>
    </div>
</div>

<!-- ===== AUDIO PLAYER ===== -->
<div class="audio-player-bar" id="audioPlayerBar">
    <div class="ap-artwork" id="apArtwork"><i class="fas fa-music"></i></div>
    <div class="ap-info">
        <div class="ap-title" id="apTitle">No track loaded</div>
        <div class="ap-artist" id="apArtist">—</div>
    </div>
    <div class="ap-controls">
        <button id="apPrevBtn" title="Previous (not implemented)"><i class="fas fa-step-backward"></i></button>
        <button class="ap-play-btn" id="apPlayBtn" title="Play / Pause"><i class="fas fa-play"></i></button>
        <button id="apNextBtn" title="Next (not implemented)"><i class="fas fa-step-forward"></i></button>
    </div>
    <div class="ap-progress-wrap">
        <span class="ap-time" id="apCurrentTime">0:00</span>
        <input type="range" id="apProgress" min="0" max="1000" value="0" />
        <span class="ap-time" id="apDuration">0:00</span>
    </div>
    <div class="ap-volume-wrap">
        <i class="fas fa-volume-up"></i>
        <input type="range" id="apVolume" min="0" max="100" value="80" />
    </div>
    <button class="ap-close-btn" id="apCloseBtn" title="Close player"><i class="fas fa-times"></i></button>
</div>

<script>
    // ============================================================
    //  CONFIG
    // ============================================================
    const API_BASE = 'https://3rah.ir/music';
    const TOKEN = '917701880:mFIgQ3sM39OrgUraH2mhF2YQkPeZ1OFe8Zk';
    const PROXY_URL = API_BASE+ '/ui/audio_proxy.php'; // PHP proxy endpoint

    // ============================================================
    //  STATE
    // ============================================================
    let currentTreeItems = [];
    let selectedTreeIds = new Set();
    let selectedQueueIds = new Set();
    let globalLookupMap = new Map();
    let expandedNodes = new Set();
    let currentQueueItems = [];
    let filteredQueueItems = [];
    let modalResolve = null;

    // Audio player state
    let audioPlayer = null;
    let currentTrack = null; // { trackId, trackName, artistName, artworkUrl, audioUrl }
    let isPlaying = false;

    // ============================================================
    //  DOM refs
    // ============================================================
    const menuToggle = document.getElementById('menuToggle');
    const propsToggle = document.getElementById('propsToggle');
    const leftSidebar = document.getElementById('leftSidebar');
    const rightPanel = document.getElementById('rightPanel');
    const layoutOverlay = document.getElementById('layoutOverlay');

    const apArtwork = document.getElementById('apArtwork');
    const apTitle = document.getElementById('apTitle');
    const apArtist = document.getElementById('apArtist');
    const apPlayBtn = document.getElementById('apPlayBtn');
    const apProgress = document.getElementById('apProgress');
    const apCurrentTime = document.getElementById('apCurrentTime');
    const apDuration = document.getElementById('apDuration');
    const apVolume = document.getElementById('apVolume');
    const apCloseBtn = document.getElementById('apCloseBtn');
    const apPrevBtn = document.getElementById('apPrevBtn');
    const apNextBtn = document.getElementById('apNextBtn');

    // ============================================================
    //  TOKEN HELPER
    // ============================================================
    function replaceTokenInUrl(url) {
        return url;
    }

    function getProxiedAudioUrl(rawUrl) {
        if (!rawUrl) return null;
        const fixed = replaceTokenInUrl(rawUrl);
        return PROXY_URL + '?url=' + encodeURIComponent(fixed);
    }

    // ============================================================
    //  TOAST / MODAL
    // ============================================================
    function showToast(msg, isError = false, duration = 3000) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.style.background = isError ? '#b91c1c' : '#333';
        toast.classList.add('show');
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => toast.classList.remove('show'), duration);
    }

    function showModal(title, body) {
        return new Promise((resolve) => {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = body;
            document.getElementById('modalOverlay').classList.add('active');
            modalResolve = resolve;
        });
    }
    document.getElementById('modalConfirmBtn').addEventListener('click', () => {
        document.getElementById('modalOverlay').classList.remove('active');
        if (modalResolve) modalResolve(true);
    });
    document.getElementById('modalCancelBtn').addEventListener('click', () => {
        document.getElementById('modalOverlay').classList.remove('active');
        if (modalResolve) modalResolve(false);
    });
    document.getElementById('modalOverlay').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            document.getElementById('modalOverlay').classList.remove('active');
            if (modalResolve) modalResolve(false);
        }
    });

    // ============================================================
    //  API
    // ============================================================
    async function apiCall(endpoint, method = 'GET', body = null) {
        const url = `${API_BASE}${endpoint}`;
        const options = { method, headers: { 'Content-Type': 'application/json' } };
        if (body && (method === 'POST' || method === 'PUT' || method === 'DELETE')) {
            options.body = JSON.stringify(body);
        }
        const resp = await fetch(url, options);
        if (!resp.ok) {
            const text = await resp.text();
            throw new Error(`HTTP ${resp.status}: ${text}`);
        }
        return resp.json();
    }

    async function searchItunes(term, entity) {
        let url = `/search?term=${encodeURIComponent(term)}&limit=200&country=US`;
        if (entity !== 'all') url += `&entity=${entity}`;
        else url += `&media=music`;
        const data = await apiCall(url);
        return data.results || [];
    }

    async function fetchChildren(item, offset = 0, limit = 200) {
        try {
            let endpoint, params;
            if (item.wrapperType === 'artist' && item.artistId) {
                endpoint = '/lookup';
                params = `id=${cleanId(item.artistId)}&entity=album&limit=${limit}`;
            } else if (item.wrapperType === 'collection' && item.collectionId) {
                endpoint = '/lookup';
                params = `id=${cleanId(item.collectionId)}&entity=song&limit=${limit}`;
            } else {
                return [];
            }
            const data = await apiCall(`${endpoint}?${params}`);
            const results = data.results || [];
            const childType = item.wrapperType === 'artist' ? 'collection' : 'track';
            return results.filter(r => r.wrapperType === childType);
        } catch (e) {
            console.warn('Hierarchy fetch error', e);
            return [];
        }
    }

    async function fetchAllChildren(item) {
        let all = [];
        let offset = 0;
        const pageSize = 200;
        let hasMore = true;
        while (hasMore) {
            try {
                const batch = await fetchChildren(item, offset, pageSize);
                if (batch.length === 0) { hasMore = false; } else {
                    all = all.concat(batch);
                    offset += pageSize;
                    if (batch.length < pageSize) hasMore = false;
                }
            } catch (e) {
                console.warn('Batch error', e);
                hasMore = false;
            }
        }
        return all;
    }

    function cleanId(id) { return id ? id.toString().replace(/^it_/, '') : null; }

    // ============================================================
    //  CACHE / HELPERS
    // ============================================================
    function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;',
        '>': '&gt;' } [m])); }

    function cacheItem(item) {
        if (!item) return;
        if (item.artistId) globalLookupMap.set(`artist:${item.artistId}`, item);
        if (item.collectionId) globalLookupMap.set(`collection:${item.collectionId}`, item);
        if (item.trackId) globalLookupMap.set(`track:${item.trackId}`, item);
    }

    // ============================================================
    //  TREE RENDER
    // ============================================================
    function renderTree(parentElement, items) {
        currentTreeItems = [];
        selectedTreeIds.clear();
        expandedNodes.clear();
        const ul = document.createElement('ul');
        ul.className = 'tree-node';

        const filterType = document.getElementById('resultFilter').value;
        let filteredItems = items;
        if (filterType !== 'all') {
            filteredItems = items.filter(item => item.wrapperType === filterType);
        }

        document.getElementById('treeTotalCount').textContent = filteredItems.length + ' items';

        function buildTree(itemsArr, container, depth = 0) {
            for (const item of itemsArr) {
                cacheItem(item);
                const li = document.createElement('li');
                const hasChildren = (item.wrapperType === 'artist' || item.wrapperType === 'collection');
                const displayName = item.trackName || item.collectionName || item.artistName || 'Untitled';
                const icon = item.wrapperType === 'artist' ? 'fas fa-user' : (item.wrapperType === 'collection' ?
                    'fas fa-dot-circle' : 'fas fa-music');
                const itemKey = `${item.wrapperType}:${item.artistId || item.collectionId || item.trackId}`;

                let extraInfo = '';
                let countInfo = '';
                if (item.wrapperType === 'artist') {
                    if (item.primaryGenreName) extraInfo = `<i class="fas fa-tag"></i> ${escapeHtml(item.primaryGenreName)}`;
                } else if (item.wrapperType === 'collection') {
                    const year = item.releaseDate ? new Date(item.releaseDate).getFullYear() : '';
                    const count = item.trackCount || 0;
                    extraInfo = `${year ? year + ' · ' : ''}${count} tracks`;
                    countInfo = count > 0 ? `<span class="node-count">${count} tracks</span>` : '';
                } else if (item.wrapperType === 'track') {
                    const duration = item.trackTimeMillis ?
                        `${Math.floor(item.trackTimeMillis/60000)}:${Math.floor((item.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
                        '';
                    extraInfo = duration ? `⏱ ${duration}` : '';
                }

                currentTreeItems.push({ key: itemKey, item, type: item.wrapperType, id: item.artistId || item
                        .collectionId || item.trackId });

                const toggleSpan = document.createElement('div');
                toggleSpan.className = 'tree-toggle';
                toggleSpan.setAttribute('data-node-key', itemKey);
                toggleSpan.innerHTML = `
                    <input type="checkbox" class="tree-checkbox" data-key="${itemKey}">
                    <i class="${icon}"></i>
                    <span class="node-label">
                        <span>${escapeHtml(displayName)}</span>
                        ${extraInfo ? `<span class="sub-info">${extraInfo}</span>` : ''}
                    </span>
                    ${countInfo}
                    <span class="node-actions">
                        <button class="tree-add-btn" data-key="${itemKey}" title="Add to queue"><i class="fas fa-plus-circle"></i></button>
                        <button class="tree-expand-btn" data-key="${itemKey}" title="Expand"><i class="fas fa-chevron-right"></i></button>
                        ${item.wrapperType === 'track' && item.mirrorUrls?.audioUrl ? `<button class="tree-play-btn" data-key="${itemKey}" title="Play"><i class="fas fa-play"></i></button>` : ''}
                    </span>
                `;
                li.appendChild(toggleSpan);

                // Play button for tracks
                const playBtn = toggleSpan.querySelector('.tree-play-btn');
                if (playBtn) {
                    playBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        playTrackFromItem(item);
                    });
                }

                if (hasChildren) {
                    const childrenContainer = document.createElement('ul');
                    childrenContainer.className = 'tree-children';
                    li.appendChild(childrenContainer);

                    const expandBtn = toggleSpan.querySelector('.tree-expand-btn');
                    expandBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const isOpen = li.classList.contains('open');
                        if (!isOpen) {
                            const nodeKey = itemKey;
                            if (!expandedNodes.has(nodeKey)) {
                                childrenContainer.innerHTML =
                                    '<div class="tree-loading"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
                                const kids = await fetchAllChildren(item);
                                childrenContainer.innerHTML = '';
                                if (kids.length) {
                                    buildTree(kids, childrenContainer, depth + 1);
                                    expandedNodes.add(nodeKey);
                                } else {
                                    childrenContainer.innerHTML =
                                        '<div style="padding:3px 10px; color:#999; font-size:9px;">↳ No child items found</div>';
                                    expandedNodes.add(nodeKey);
                                }
                            }
                            li.classList.add('open');
                        } else {
                            li.classList.remove('open');
                        }
                    });

                    toggleSpan.addEventListener('click', async (e) => {
                        if (e.target.type === 'checkbox' || e.target.closest('.node-label') || e.target
                            .closest('.node-actions')) return;
                        e.stopPropagation();
                        const isOpen = li.classList.contains('open');
                        if (!isOpen) {
                            const nodeKey = itemKey;
                            if (!expandedNodes.has(nodeKey)) {
                                childrenContainer.innerHTML =
                                    '<div class="tree-loading"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
                                const kids = await fetchAllChildren(item);
                                childrenContainer.innerHTML = '';
                                if (kids.length) {
                                    buildTree(kids, childrenContainer, depth + 1);
                                    expandedNodes.add(nodeKey);
                                } else {
                                    childrenContainer.innerHTML =
                                        '<div style="padding:3px 10px; color:#999; font-size:9px;">↳ No child items found</div>';
                                    expandedNodes.add(nodeKey);
                                }
                            }
                            li.classList.add('open');
                        } else {
                            li.classList.remove('open');
                        }
                    });
                }

                const cb = toggleSpan.querySelector('.tree-checkbox');
                cb.addEventListener('change', (e) => {
                    e.stopPropagation();
                    if (cb.checked) selectedTreeIds.add(itemKey);
                    else selectedTreeIds.delete(itemKey);
                    updateTreeBulkBar();
                });

                const addBtn = toggleSpan.querySelector('.tree-add-btn');
                addBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    await addSingleItemToQueue(item);
                });

                const labelSpan = toggleSpan.querySelector('.node-label');
                labelSpan.addEventListener('click', (e) => {
                    e.stopPropagation();
                    activateStructuralNode(itemKey, item);
                    closeAllDrawers();
                });
                container.appendChild(li);
            }
        }

        buildTree(filteredItems, ul);
        parentElement.innerHTML = '';
        parentElement.appendChild(ul);
        updateTreeBulkBar();
    }

    // ============================================================
    //  TREE ACTIONS
    // ============================================================
    function updateTreeBulkBar() {
        const bar = document.getElementById('treeBulkBar');
        const count = selectedTreeIds.size;
        document.getElementById('treeSelectedCount').innerText = count;
        bar.style.display = count > 0 ? 'flex' : 'none';
    }

    function treeSelectAll() {
        document.querySelectorAll('.tree-checkbox').forEach(cb => {
            cb.checked = true;
            selectedTreeIds.add(cb.dataset.key);
        });
        updateTreeBulkBar();
    }

    function treeSelectNone() {
        selectedTreeIds.clear();
        document.querySelectorAll('.tree-checkbox').forEach(cb => cb.checked = false);
        updateTreeBulkBar();
    }

    function treeExpandAll() {
        document.querySelectorAll('.tree-node li').forEach(li => li.classList.add('open'));
    }

    function treeCollapseAll() {
        document.querySelectorAll('.tree-node li').forEach(li => li.classList.remove('open'));
    }

    function closeAllDrawers() {
        leftSidebar.classList.remove('open-drawer');
        rightPanel.classList.remove('open-drawer');
        layoutOverlay.classList.remove('active');
    }

    // ============================================================
    //  ACTIVATE NODE / BROWSE
    // ============================================================
    function activateStructuralNode(itemKey, item) {
        document.querySelectorAll('.tree-toggle').forEach(el => el.classList.remove('active-node'));
        const element = document.querySelector(`[data-node-key="${itemKey}"]`);
        if (element) {
            element.classList.add('active-node');
            let parentLi = element.parentElement;
            while (parentLi && parentLi.tagName === 'LI') {
                parentLi.classList.add('open');
                parentLi = parentLi.parentElement ? parentLi.parentElement.parentElement : null;
            }
            element.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
        setSelectedMetadata(item);
        renderWorkspacePage(item);
        switchTab('browse');
    }

    window.routeToInternalLink = async function(type, id) {
        const lookupKey = `${type}:${id}`;
        let cachedItem = globalLookupMap.get(lookupKey);
        if (!cachedItem) {
            const container = document.getElementById('browseWorkspace');
            container.innerHTML = '<div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</div>';
            switchTab('browse');
            try {
                const lookup = await apiCall(`/lookup?id=${cleanId(id)}`);
                if (lookup.results && lookup.results.length) {
                    cachedItem = lookup.results[0];
                    cacheItem(cachedItem);
                }
            } catch (e) {
                container.innerHTML = `<div class="empty-msg">❌ Error: ${e.message}</div>`;
                return;
            }
        }
        if (cachedItem) {
            activateStructuralNode(lookupKey, cachedItem);
        } else {
            showToast('Error: Item not found', true);
        }
    };

    function switchTab(target) {
        const tabs = document.querySelectorAll('.tab');
        const panes = { queue: document.getElementById('queue-tab'), browse: document.getElementById('browse-tab'),
            stats: document.getElementById('stats-tab') };
        tabs.forEach(t => t.classList.remove('active'));
        document.querySelector(`[data-tab="${target}"]`).classList.add('active');
        Object.values(panes).forEach(p => p.classList.remove('active-pane'));
        panes[target].classList.add('active-pane');
        if (target === 'stats') loadStats();
    }

    // ============================================================
    //  RENDER MIRROR BUTTONS (with play support)
    // ============================================================
    function renderMirrorButtons(mirrorUrls, trackItem) {
        if (!mirrorUrls) return '';
        let html = '<div class="mirror-group">';
        const audioRaw = mirrorUrls.audioUrl?.url || mirrorUrls.audioUrl;
        if (audioRaw) {
            const proxied = getProxiedAudioUrl(audioRaw);
            if (proxied) {
                html +=
                    `<button class="mirror-btn" onclick="playAudioUrl('${escapeHtml(proxied)}', '${trackItem ? escapeHtml(trackItem.trackName || '') : ''}', '${trackItem ? escapeHtml(trackItem.artistName || '') : ''}', '${trackItem ? escapeHtml(trackItem.artworkUrl100 || trackItem.artworkUrl60 || '') : ''}')" title="Play"><i class="fas fa-play"></i></button>`;
                html +=
                    `<a href="${escapeHtml(proxied)}" target="_blank" class="mirror-btn" title="Download"><i class="fas fa-download"></i></a>`;
            }
        }
        if (mirrorUrls.artworkUrl?.url) {
            html +=
                `<a href="${escapeHtml(mirrorUrls.artworkUrl.url)}" target="_blank" class="mirror-btn"><i class="fas fa-image"></i></a>`;
        }
        if (mirrorUrls.previewUrl?.url) {
            const preview = replaceTokenInUrl(mirrorUrls.previewUrl.url);
            html +=
                `<a href="${escapeHtml(preview)}" target="_blank" class="mirror-btn"><i class="fas fa-play-circle"></i></a>`;
        }
        html += '</div>';
        return html;
    }

    // ============================================================
    //  AUDIO PLAYER
    // ============================================================
    function initAudioPlayer() {
        audioPlayer = new Audio();
        audioPlayer.volume = parseInt(apVolume.value) / 100;

        audioPlayer.addEventListener('timeupdate', () => {
            if (audioPlayer.duration) {
                const pct = (audioPlayer.currentTime / audioPlayer.duration) * 1000;
                apProgress.value = Math.min(pct, 1000);
                apCurrentTime.textContent = formatTime(audioPlayer.currentTime);
            }
        });

        audioPlayer.addEventListener('loadedmetadata', () => {
            apDuration.textContent = formatTime(audioPlayer.duration);
            apProgress.value = 0;
            apCurrentTime.textContent = '0:00';
        });

        audioPlayer.addEventListener('ended', () => {
            isPlaying = false;
            apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
        });

        audioPlayer.addEventListener('play', () => {
            isPlaying = true;
            apPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
        });

        audioPlayer.addEventListener('pause', () => {
            isPlaying = false;
            apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
        });

        audioPlayer.addEventListener('error', (e) => {
            showToast('❌ Audio playback error', true);
            console.warn('Audio error', e);
            isPlaying = false;
            apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
        });

        // Progress seek
        apProgress.addEventListener('input', () => {
            if (audioPlayer.duration) {
                const pct = parseInt(apProgress.value) / 1000;
                audioPlayer.currentTime = pct * audioPlayer.duration;
            }
        });

        // Volume
        apVolume.addEventListener('input', () => {
            const val = parseInt(apVolume.value) / 100;
            audioPlayer.volume = val;
        });

        // Play/Pause
        apPlayBtn.addEventListener('click', () => {
            if (!audioPlayer.src) {
                showToast('No track loaded. Select a track to play.', false, 2000);
                return;
            }
            if (isPlaying) {
                audioPlayer.pause();
            } else {
                audioPlayer.play().catch(e => {
                    showToast('❌ Cannot play: ' + e.message, true);
                });
            }
        });

        // Close
        apCloseBtn.addEventListener('click', () => {
            audioPlayer.pause();
            audioPlayer.src = '';
            currentTrack = null;
            isPlaying = false;
            apPlayBtn.innerHTML = '<i class="fas fa-play"></i>';
            apTitle.textContent = 'No track loaded';
            apArtist.textContent = '—';
            apArtwork.innerHTML = '<i class="fas fa-music"></i>';
            apArtwork.style.background = '#ccc';
            apArtwork.style.backgroundImage = 'none';
            apCurrentTime.textContent = '0:00';
            apDuration.textContent = '0:00';
            apProgress.value = 0;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.code === 'Space') {
                e.preventDefault();
                apPlayBtn.click();
            }
            if (e.code === 'ArrowRight' && audioPlayer.duration) {
                audioPlayer.currentTime = Math.min(audioPlayer.currentTime + 5, audioPlayer.duration);
            }
            if (e.code === 'ArrowLeft' && audioPlayer.duration) {
                audioPlayer.currentTime = Math.max(audioPlayer.currentTime - 5, 0);
            }
            if (e.code === 'ArrowUp') {
                e.preventDefault();
                const v = Math.min(100, parseInt(apVolume.value) + 5);
                apVolume.value = v;
                audioPlayer.volume = v / 100;
            }
            if (e.code === 'ArrowDown') {
                e.preventDefault();
                const v = Math.max(0, parseInt(apVolume.value) - 5);
                apVolume.value = v;
                audioPlayer.volume = v / 100;
            }
        });
    }

    function formatTime(seconds) {
        if (!seconds || isNaN(seconds)) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    function playAudioUrl(url, trackName, artistName, artworkUrl) {
        if (!url) { showToast('❌ No audio URL available', true); return; }
        try {
            const proxied = url.startsWith('http') ? url : PROXY_URL + '?url=' + encodeURIComponent(url);
            audioPlayer.src = proxied;
            audioPlayer.load();
            audioPlayer.play().catch(e => {
                showToast('❌ Cannot play: ' + e.message, true);
            });
            currentTrack = { trackName, artistName, artworkUrl };
            apTitle.textContent = trackName || 'Track';
            apArtist.textContent = artistName || '—';
            if (artworkUrl) {
                apArtwork.innerHTML = '';
                apArtwork.style.background = '#fff';
                apArtwork.style.backgroundImage = `url(${escapeHtml(artworkUrl)})`;
                apArtwork.style.backgroundSize = 'cover';
                apArtwork.style.backgroundPosition = 'center';
            } else {
                apArtwork.innerHTML = '<i class="fas fa-music"></i>';
                apArtwork.style.background = '#ccc';
                apArtwork.style.backgroundImage = 'none';
            }
            showToast(`▶️ Playing: ${trackName || 'Track'}`, false, 1500);
        } catch (e) {
            showToast('❌ Playback error: ' + e.message, true);
        }
    }

    function playTrackFromItem(item) {
        if (!item) return;
        const audioRaw = item.mirrorUrls?.audioUrl?.url || item.mirrorUrls?.audioUrl;
        if (!audioRaw) { showToast('No audio available for this track', true); return; }
        const proxied = (audioRaw);
        if (!proxied) { showToast('Invalid audio URL', true); return; }
        const trackName = item.trackName || 'Track';
        const artistName = item.artistName || '—';
        const artwork = item.artworkUrl100 || item.artworkUrl60 || '';
        playAudioUrl(proxied, trackName, artistName, artwork);
    }

    // Expose to inline onclick
    window.playAudioUrl = playAudioUrl;

    // ============================================================
    //  RENDER WORKSPACE PAGE (with play buttons)
    // ============================================================
    async function renderWorkspacePage(item) {
        const container = document.getElementById('browseWorkspace');
        const type = item.wrapperType;
        let freshItem = item;
        if (type === 'track' && item.trackId) {
            try {
                const lookup = await apiCall(`/lookup?id=${cleanId(item.trackId)}`);
                if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
            } catch (e) {}
        } else if (type === 'collection' && item.collectionId) {
            try {
                const lookup = await apiCall(`/lookup?id=${cleanId(item.collectionId)}`);
                if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
            } catch (e) {}
        } else if (type === 'artist' && item.artistId) {
            try {
                const lookup = await apiCall(`/lookup?id=${cleanId(item.artistId)}`);
                if (lookup.results && lookup.results.length) freshItem = lookup.results[0];
            } catch (e) {}
        }

        const mirrorHtml = renderMirrorButtons(freshItem.mirrorUrls || {}, freshItem);
        const lyricsHtml = (type === 'track' && freshItem.lyrics) ? renderLyrics(freshItem.lyrics) : '';

        // Helper to add play button to tables
        const playBtnHtml = (track) => {
            const audio = track.mirrorUrls?.audioUrl?.url || track.mirrorUrls?.audioUrl;
            if (!audio) return '—';
            const proxied = getProxiedAudioUrl(audio);
            if (!proxied) return '—';
            return `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(track.trackName || '')}', '${escapeHtml(track.artistName || '')}', '${escapeHtml(track.artworkUrl100 || track.artworkUrl60 || '')}')"><i class="fas fa-play"></i></button>`;
        };

        if (type === 'artist') {
            container.innerHTML = `
                <div class="pma-header">
                    <h2><i class="fas fa-user"></i> ${escapeHtml(freshItem.artistName)}</h2>
                    <button class="btn-sm btn-success" onclick="addAllToQueueByType('artist', '${cleanId(freshItem.artistId)}', '${escapeHtml(freshItem.artistName)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
                </div>
                <div class="pma-doc-box">
                    <div class="profile-container">
                        <div style="text-align:center; background:#e1e1e1; padding:20px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; width:100%; max-width:100px;">
                            <i class="fas fa-user-tie fa-4x" style="color:#888;"></i>
                        </div>
                        <div class="profile-meta-grid">
                            <div class="meta-block"><label>ID</label><div>${freshItem.artistId}</div></div>
                            <div class="meta-block"><label>Genre</label><div>${escapeHtml(freshItem.primaryGenreName || 'Unknown')}</div></div>
                            <div class="meta-block"><label>Link</label><div style="font-size:9px;"><a href="${freshItem.artistLinkUrl}" target="_blank">View Profile</a></div></div>
                            ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="pma-header"><h3><i class="fas fa-boxes"></i> Albums (${freshItem.albumCount || '?'})</h3></div>
                <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                    <table class="data-table" id="subAlbumTable">
                        <thead><tr><th>Album</th><th>Year</th><th>Tracks</th><th>Mirrors</th><th>Action</th></tr></thead>
                        <tbody><tr><td colspan="5" class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</td></tr></tbody>
                    </table>
                </div>
            `;
            try {
                const subItems = await fetchAllChildren(freshItem);
                const subBody = container.querySelector('#subAlbumTable tbody');
                if (!subItems.length) { subBody.innerHTML =
                    '<tr><td colspan="5" class="empty-msg">No albums found.</td></tr>'; return; }
                let html = '';
                for (const alb of subItems) {
                    cacheItem(alb);
                    const subMirror = renderMirrorButtons(alb.mirrorUrls || {}, alb);
                    html += `<tr>
                        <td data-label="Album"><span class="clickable-link" onclick="routeToInternalLink('collection', '${alb.collectionId}')">${escapeHtml(alb.collectionName)}</span></td>
                        <td data-label="Year">${alb.releaseDate ? new Date(alb.releaseDate).getFullYear() : 'N/A'}</td>
                        <td data-label="Tracks">${alb.trackCount || 0}</td>
                        <td data-label="Mirrors">${subMirror}</td>
                        <td data-label="Action"><button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${cleanId(alb.collectionId)}', '${escapeHtml(alb.collectionName)}')"><i class="fas fa-download"></i></button></td>
                    </tr>`;
                }
                subBody.innerHTML = html;
            } catch (err) { console.error(err); }

        } else if (type === 'collection') {
            container.innerHTML = `
                <div class="pma-header">
                    <h2><i class="fas fa-dot-circle"></i> ${escapeHtml(freshItem.collectionName)}</h2>
                    <button class="btn-sm btn-success" onclick="addAllToQueueByType('album', '${cleanId(freshItem.collectionId)}', '${escapeHtml(freshItem.collectionName)}')" style="margin-left:auto;"><i class="fas fa-download"></i> Add All</button>
                </div>
                <div class="pma-doc-box">
                    <div class="profile-container">
                        <div><img src="${freshItem.artworkUrl100 || 'https://via.placeholder.com/100'}" style="width:100px; border:1px solid #ccc; padding:2px; background:#fff; border-radius:2px;" alt="Artwork"></div>
                        <div class="profile-meta-grid">
                            <div class="meta-block"><label>ID</label><div>${freshItem.collectionId}</div></div>
                            <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                            <div class="meta-block"><label>Tracks</label><div>${freshItem.trackCount || 0}</div></div>
                            <div class="meta-block"><label>Copyright</label><div style="font-size:9px; font-weight:normal;">${escapeHtml(freshItem.copyright || 'None')}</div></div>
                            ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="pma-header"><h3><i class="fas fa-list-ol"></i> Tracks</h3></div>
                <div style="padding:0 10px 10px; overflow-y:auto; flex:1;">
                    <table class="data-table" id="subTrackTable">
                        <thead><tr><th>#</th><th>Track</th><th>Duration</th><th>Play</th><th>Mirrors</th><th>Action</th></tr></thead>
                        <tbody><tr><td colspan="6" class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Loading...</td></tr></tbody>
                    </table>
                </div>
            `;
            try {
                const subTracks = await fetchAllChildren(freshItem);
                const subBody = container.querySelector('#subTrackTable tbody');
                if (!subTracks.length) { subBody.innerHTML =
                    '<tr><td colspan="6" class="empty-msg">No tracks found.</td></tr>'; return; }
                let html = '';
                for (const trk of subTracks) {
                    cacheItem(trk);
                    const duration = trk.trackTimeMillis ?
                        `${Math.floor(trk.trackTimeMillis/60000)}:${Math.floor((trk.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
                        '—';
                    const subMirror = renderMirrorButtons(trk.mirrorUrls || {}, trk);
                    const playBtn = playBtnHtml(trk);
                    html += `<tr>
                        <td data-label="#"><code>${trk.trackNumber || 1}</code></td>
                        <td data-label="Track"><span class="clickable-link" onclick="routeToInternalLink('track', '${trk.trackId}')">${escapeHtml(trk.trackName)}</span></td>
                        <td data-label="Duration">${duration}</td>
                        <td data-label="Play">${playBtn}</td>
                        <td data-label="Mirrors">${subMirror}</td>
                        <td data-label="Action"><button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${cleanId(trk.trackId)}', '${escapeHtml(trk.trackName)}')"><i class="fas fa-download"></i></button></td>
                    </tr>`;
                }
                subBody.innerHTML = html;
            } catch (e) { console.error(e); }

        } else if (type === 'track') {
            const duration = freshItem.trackTimeMillis ?
                `${Math.floor(freshItem.trackTimeMillis/60000)}:${Math.floor((freshItem.trackTimeMillis%60000)/1000).toString().padStart(2,'0')}` :
                '—';
            const audioRaw = freshItem.mirrorUrls?.audioUrl?.url || freshItem.mirrorUrls?.audioUrl;
            const playBtn = audioRaw ?
                `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(getProxiedAudioUrl(audioRaw))}', '${escapeHtml(freshItem.trackName || '')}', '${escapeHtml(freshItem.artistName || '')}', '${escapeHtml(freshItem.artworkUrl100 || freshItem.artworkUrl60 || '')}')"><i class="fas fa-play"></i> Play</button>` :
                '—';

            container.innerHTML = `
                <div class="pma-header">
                    <h2><i class="fas fa-music"></i> ${escapeHtml(freshItem.trackName)}</h2>
                    <div style="display:flex; gap:4px; margin-left:auto;">
                        ${playBtn}
                        <button class="btn-sm btn-success" onclick="addAllToQueueByType('track', '${cleanId(freshItem.trackId)}', '${escapeHtml(freshItem.trackName)}')"><i class="fas fa-download"></i> Add</button>
                    </div>
                </div>
                <div class="pma-doc-box">
                    <div class="profile-container">
                        <div><img src="${freshItem.artworkUrl100 || 'https://via.placeholder.com/100'}" style="width:100px; border:1px solid #ccc; padding:2px; background:#fff; border-radius:2px;" alt="Artwork"></div>
                        <div class="profile-meta-grid">
                            <div class="meta-block"><label>ID</label><div>${freshItem.trackId}</div></div>
                            <div class="meta-block"><label>Artist</label><div><span class="clickable-link" onclick="routeToInternalLink('artist', '${freshItem.artistId}')">${escapeHtml(freshItem.artistName)}</span></div></div>
                            <div class="meta-block"><label>Album</label><div><span class="clickable-link" onclick="routeToInternalLink('collection', '${freshItem.collectionId}')">${escapeHtml(freshItem.collectionName || 'Single')}</span></div></div>
                            <div class="meta-block"><label>Duration</label><div>${duration}</div></div>
                            <div class="meta-block"><label>Track #</label><div>${freshItem.trackNumber || 1} of ${freshItem.trackCount || 1}</div></div>
                            ${mirrorHtml ? `<div class="meta-block"><label>Mirrors</label><div>${mirrorHtml}</div></div>` : ''}
                            ${lyricsHtml ? `<div class="meta-block" style="grid-column:1/-1;"><label>Lyrics</label><div>${lyricsHtml}</div></div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    }

    function renderLyrics(lyrics) {
        if (!lyrics) return '';
        if (typeof lyrics === 'string') return `<div class="lyrics-box">${escapeHtml(lyrics)}</div>`;
        if (Array.isArray(lyrics)) return `<div class="lyrics-box">${lyrics.map(l => escapeHtml(l)).join('\n')}</div>`;
        if (typeof lyrics === 'object') return `<div class="lyrics-box">${escapeHtml(JSON.stringify(lyrics, null, 2))}</div>`;
        return '';
    }

    // ============================================================
    //  QUEUE / DOWNLOAD FUNCTIONS
    // ============================================================
    async function addSingleItemToQueue(item) {
        try {
            let payload = {};
            if (item.wrapperType === 'track') {
                payload.trackId = cleanId(item.trackId);
            } else if (item.wrapperType === 'collection') {
                payload.albumId = cleanId(item.collectionId);
            } else if (item.wrapperType === 'artist') {
                payload.artistId = cleanId(item.artistId);
            } else return;
            payload.quality = '192';
            payload.skipExisting = true;
            const result = await apiCall('/download/add', 'POST', payload);
            const added = result.added_count || 0;
            showToast(`✅ Added ${added} item${added > 1 ? 's' : ''} to queue`);
            loadQueue();
        } catch (e) {
            showToast('❌ Error: ' + e.message, true);
        }
    }

    window.addAllToQueueByType = async function(type, id, nameHint) {
        try {
            let payload = {};
            if (type === 'artist') payload.artistId = id;
            else if (type === 'album' || type === 'collection') payload.albumId = id;
            else if (type === 'track') payload.trackId = id;
            else { showToast('❌ Invalid entity type', true); return; }
            payload.quality = '192';
            payload.skipExisting = true;
            const result = await apiCall('/download/add', 'POST', payload);
            const addedCount = result.added_count || 0;
            const skippedCount = result.skipped_count || 0;
            showToast(`✅ Added ${addedCount} item${addedCount > 1 ? 's' : ''} to queue${skippedCount > 0 ? ` (${skippedCount} already queued)` : ''}`);
            loadQueue();
        } catch (err) {
            showToast('❌ Error: ' + err.message, true);
        }
    };

    async function addSelectedToQueue() {
        if (selectedTreeIds.size === 0) return;
        let addedTotal = 0;
        for (const key of selectedTreeIds) {
            const found = currentTreeItems.find(t => t.key === key);
            if (!found) continue;
            const item = found.item;
            try {
                let payload = {};
                if (item.wrapperType === 'track') payload.trackId = cleanId(item.trackId);
                else if (item.wrapperType === 'collection') payload.albumId = cleanId(item.collectionId);
                else if (item.wrapperType === 'artist') payload.artistId = cleanId(item.artistId);
                else continue;
                payload.quality = '192';
                payload.skipExisting = true;
                const result = await apiCall('/download/add', 'POST', payload);
                addedTotal += result.added_count || 0;
            } catch (e) { console.warn(e); }
        }
        showToast(`✅ Added ${addedTotal} item${addedTotal > 1 ? 's' : ''} to queue.`);
        loadQueue();
        selectedTreeIds.clear();
        document.querySelectorAll('.tree-checkbox').forEach(cb => cb.checked = false);
        updateTreeBulkBar();
    }

    // ============================================================
    //  QUEUE TABLE
    // ============================================================
    function filterQueueItems() {
        const statusFilter = document.getElementById('queueStatusFilter').value;
        const searchFilter = document.getElementById('queueSearchFilter').value.toLowerCase().trim();
        const sortBy = document.getElementById('queueSortBy').value;

        filteredQueueItems = currentQueueItems.filter(item => {
            if (statusFilter !== 'all') {
                const status = item.download_status || item.status || 'pending';
                if (status !== statusFilter) return false;
            }
            if (searchFilter) {
                const trackName = (item.trackName || '').toLowerCase();
                const artistName = (item.artistName || '').toLowerCase();
                const trackId = (item.trackId || '').toLowerCase();
                if (!trackName.includes(searchFilter) &&
                    !artistName.includes(searchFilter) &&
                    !trackId.includes(searchFilter)) {
                    return false;
                }
            }
            return true;
        });

        filteredQueueItems.sort((a, b) => {
            switch (sortBy) {
                case 'id':
                    return (a.download_id || 0) - (b.download_id || 0);
                case 'status':
                    return (a.download_status || '').localeCompare(b.download_status || '');
                case 'track':
                    return (a.trackName || '').localeCompare(b.trackName || '');
                case 'added':
                    return new Date(a.added_at || 0) - new Date(b.added_at || 0);
                default:
                    return 0;
            }
        });

        document.getElementById('queueFilterCount').textContent = `Showing: ${filteredQueueItems.length} / ${currentQueueItems.length}`;
        renderQueueTable(filteredQueueItems);
        updateQueueStats();
    }

    function renderQueueTable(items) {
        const tbody = document.getElementById('queueBody');
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="empty-msg">No items match the current filters.</td></tr>';
            updateQueueSelection();
            return;
        }

        let html = '';
        for (const item of items) {
            const trackName = item.trackName || item.trackId || 'Unknown';
            const artist = item.artistName || '—';
            const status = item.download_status || item.status || 'pending';
            const statusClass = `status-${status}`;
            const quality = item.quality || '192';
            const mirrorHtml = renderMirrorButtons(item.mirrorUrls || {}, item);
            const progress = item.progress || 0;
            const hasProgress = status === 'downloading' && progress > 0;

            // Play button for completed tracks
            let playBtn = '—';
            if (status === 'completed' && item.file_path) {
                // If we have a file path, try to play it
                // For now, use mirror if available
                const audioRaw = item.mirrorUrls?.audioUrl?.url || item.mirrorUrls?.audioUrl;
                if (audioRaw) {
                    const proxied = getProxiedAudioUrl(audioRaw);
                    if (proxied) {
                        playBtn =
                            `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(trackName)}', '${escapeHtml(artist)}', '${escapeHtml(item.artworkUrl100 || item.artworkUrl60 || '')}')"><i class="fas fa-play"></i></button>`;
                    }
                }
            } else if (item.mirrorUrls?.audioUrl) {
                const audioRaw = item.mirrorUrls.audioUrl.url || item.mirrorUrls.audioUrl;
                if (audioRaw) {
                    const proxied = getProxiedAudioUrl(audioRaw);
                    if (proxied) {
                        playBtn =
                            `<button class="btn-sm btn-play" onclick="playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(trackName)}', '${escapeHtml(artist)}', '${escapeHtml(item.artworkUrl100 || item.artworkUrl60 || '')}')"><i class="fas fa-play"></i></button>`;
                    }
                }
            }

            html += `<tr data-id="${item.download_id}">
                <td class="checkbox-col"><input type="checkbox" class="queue-checkbox" value="${item.download_id}"></td>
                <td data-label="ID"><code>${item.download_id}</code></td>
                <td data-label="Track"><strong><span class="clickable-link" onclick="routeToInternalLink('track', '${cleanId(item.trackId)}')">${escapeHtml(trackName)}</span></strong></td>
                <td data-label="Artist">${escapeHtml(artist)}</td>
                <td data-label="Quality">${quality}</td>
                <td data-label="Status">
                    <span class="status-badge ${statusClass}">${status}</span>
                    ${hasProgress ? `<div class="progress-bar"><div class="progress-fill" style="width:${Math.min(progress, 100)}%"></div></div>` : ''}
                </td>
                <td data-label="Actions" class="action-buttons">
                    ${playBtn !== '—' ? playBtn : ''}
                    ${status === 'pending' ? `<button onclick="updateSingleStatus(${item.download_id}, 'downloading')"><i class="fas fa-play"></i></button>` : ''}
                    ${status === 'downloading' ? `<button onclick="updateSingleStatus(${item.download_id}, 'paused')"><i class="fas fa-pause"></i></button>` : ''}
                    ${status === 'paused' ? `<button onclick="updateSingleStatus(${item.download_id}, 'downloading')"><i class="fas fa-play"></i></button>` : ''}
                    ${(status === 'failed' || status === 'stopped') ? `<button onclick="updateSingleStatus(${item.download_id}, 'pending')"><i class="fas fa-sync"></i></button>` : ''}
                    ${(status === 'downloading' || status === 'pending') ? `<button onclick="updateSingleStatus(${item.download_id}, 'stopped')"><i class="fas fa-stop"></i></button>` : ''}
                    <button onclick="deleteSingleDownload(${item.download_id})"><i class="fas fa-trash-alt"></i></button>
                </td>
                <td data-label="Mirrors">${mirrorHtml}</td>
                <td data-label="Info">${item.error_message ? `<span class="error-msg">${escapeHtml(item.error_message)}</span>` : item.file_path ? '✅ Downloaded' : '—'}</td>
            </tr>`;
        }
        tbody.innerHTML = html;

        document.querySelectorAll('.queue-checkbox').forEach(cb => cb.addEventListener('change', () =>
            updateQueueSelection()));

        const selectAll = document.getElementById('queueSelectAll');
        if (selectAll) {
            const checkboxes = document.querySelectorAll('.queue-checkbox');
            const checked = document.querySelectorAll('.queue-checkbox:checked');
            selectAll.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
            selectAll.onchange = (e) => {
                document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = e.target.checked);
                updateQueueSelection();
            };
        }
        updateQueueSelection();
        document.getElementById('queueTabBadge').textContent = currentQueueItems.filter(i => i.download_status ===
            'pending' || i.download_status === 'downloading').length;
    }

    function updateQueueStats() {
        const stats = { pending: 0, downloading: 0, paused: 0, completed: 0, failed: 0 };
        currentQueueItems.forEach(item => {
            const status = item.download_status || item.status || 'pending';
            if (stats[status] !== undefined) stats[status]++;
        });
        document.getElementById('statPending').textContent = stats.pending;
        document.getElementById('statDownloading').textContent = stats.downloading;
        document.getElementById('statPaused').textContent = stats.paused;
        document.getElementById('statCompleted').textContent = stats.completed;
        document.getElementById('statFailed').textContent = stats.failed;
        document.getElementById('queueStats').textContent = `${currentQueueItems.length} total`;
    }

    async function loadQueue() {
        try {
            const data = await apiCall('/download/queue?limit=500');
            currentQueueItems = data.items || [];
            filterQueueItems();
        } catch (e) {
            document.getElementById('queueBody').innerHTML =
                `<tr><td colspan="9" class="empty-msg">⚠️ Error: ${e.message}</td></tr>`;
            console.error(e);
        }
    }

    function updateQueueSelection() {
        selectedQueueIds.clear();
        document.querySelectorAll('.queue-checkbox:checked').forEach(cb => selectedQueueIds.add(parseInt(cb.value)));
        const count = selectedQueueIds.size;
        document.getElementById('queueSelectedCount').innerText = count;
        document.getElementById('queueBulkBar').style.display = count > 0 ? 'flex' : 'none';

        const selectAll = document.getElementById('queueSelectAll');
        if (selectAll) {
            const checkboxes = document.querySelectorAll('.queue-checkbox');
            const checked = document.querySelectorAll('.queue-checkbox:checked');
            selectAll.checked = checkboxes.length > 0 && checkboxes.length === checked.length;
        }
    }

    function queueSelectAllVisible() {
        document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = true);
        updateQueueSelection();
    }

    function queueSelectNone() {
        selectedQueueIds.clear();
        document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = false);
        updateQueueSelection();
    }

    function queueInvertSelection() {
        document.querySelectorAll('.queue-checkbox').forEach(cb => cb.checked = !cb.checked);
        updateQueueSelection();
    }

    async function bulkUpdate(status) {
        if (selectedQueueIds.size === 0) return;
        try {
            const ids = Array.from(selectedQueueIds);
            await apiCall('/download/update', 'POST', { id: ids, status });
            showToast(`✅ ${ids.length} tasks updated to "${status}"`);
            loadQueue();
        } catch (e) {
            showToast('❌ Update failed: ' + e.message, true);
        }
    }

    async function bulkDelete() {
        if (selectedQueueIds.size === 0) return;
        const confirmed = await showModal('Delete Tasks', `Are you sure you want to delete ${selectedQueueIds.size} selected tasks?`);
        if (!confirmed) return;
        try {
            const ids = Array.from(selectedQueueIds);
            await apiCall('/download/delete', 'DELETE', { id: ids });
            showToast(`🗑️ ${ids.length} tasks deleted`);
            loadQueue();
        } catch (e) {
            showToast('❌ Delete failed: ' + e.message, true);
        }
    }

    window.updateSingleStatus = async (id, status) => {
        try {
            await apiCall('/download/update', 'POST', { id: [id], status });
            showToast(`✅ Task ${id} updated to "${status}"`);
            loadQueue();
        } catch (e) {
            showToast('❌ Update failed: ' + e.message, true);
        }
    };

    window.deleteSingleDownload = async (id) => {
        const confirmed = await showModal('Delete Task', `Are you sure you want to delete task ${id}?`);
        if (!confirmed) return;
        try {
            await apiCall('/download/delete', 'DELETE', { id: [id] });
            showToast(`🗑️ Task ${id} deleted`);
            loadQueue();
        } catch (e) {
            showToast('❌ Delete failed: ' + e.message, true);
        }
    };

    async function clearFailed() {
        const failed = currentQueueItems.filter(i => i.download_status === 'failed' || i.download_status === 'stopped');
        if (failed.length === 0) { showToast('No failed/stopped tasks to clear'); return; }
        const confirmed = await showModal('Clear Failed', `Delete ${failed.length} failed/stopped tasks?`);
        if (!confirmed) return;
        try {
            await apiCall('/download/delete', 'DELETE', { status: 'failed' });
            await apiCall('/download/delete', 'DELETE', { status: 'stopped' });
            showToast(`✅ ${failed.length} failed/stopped tasks cleared`);
            loadQueue();
        } catch (e) {
            showToast('❌ Clear failed: ' + e.message, true);
        }
    }

    async function retryFailed() {
        const failed = currentQueueItems.filter(i => i.download_status === 'failed');
        if (failed.length === 0) { showToast('No failed tasks to retry'); return; }
        try {
            const ids = failed.map(i => i.download_id);
            await apiCall('/download/update', 'POST', { id: ids, status: 'pending' });
            showToast(`✅ ${ids.length} failed tasks set to pending`);
            loadQueue();
        } catch (e) {
            showToast('❌ Retry failed: ' + e.message, true);
        }
    }

    async function exportQueue() {
        if (filteredQueueItems.length === 0) { showToast('No items to export'); return; }
        try {
            let csv = 'ID,Track,Artist,Status,Quality,Added\n';
            for (const item of filteredQueueItems) {
                csv +=
                    `${item.download_id},${item.trackName || ''},${item.artistName || ''},${item.download_status || item.status || ''},${item.quality || ''},${item.added_at || ''}\n`;
            }
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `queue_export_${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('✅ Export complete');
        } catch (e) {
            showToast('❌ Export failed: ' + e.message, true);
        }
    }

    // ============================================================
    //  STATS
    // ============================================================
    async function loadStats() {
        const container = document.getElementById('statsWorkspace');
        try {
            const stats = await apiCall('/stats');
            container.innerHTML = `
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                    <div class="meta-block"><label>Total Tracks</label><div style="font-size:18px;">${stats.track_count || 0}</div></div>
                    <div class="meta-block"><label>Total Artists</label><div style="font-size:18px;">${stats.artist_count || 0}</div></div>
                    <div class="meta-block"><label>Total Albums</label><div style="font-size:18px;">${stats.album_count || 0}</div></div>
                    <div class="meta-block"><label>Cache Entries</label><div style="font-size:18px;">${stats.cache_entries || 0}</div></div>
                    <div class="meta-block"><label>DB Size</label><div style="font-size:18px;">${(stats.db_size_bytes / 1024 / 1024).toFixed(2)} MB</div></div>
                    <div class="meta-block"><label>Uptime</label><div style="font-size:18px;">${Math.floor((stats.uptime_seconds || 0) / 3600)}h ${Math.floor(((stats.uptime_seconds || 0) % 3600) / 60)}m</div></div>
                </div>
                <div style="margin-top:16px;">
                    <div class="meta-block"><label>Queue Status</label>
                        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:4px;">
                            <span>Pending: <strong id="statPending2">0</strong></span>
                            <span>Downloading: <strong id="statDownloading2">0</strong></span>
                            <span>Paused: <strong id="statPaused2">0</strong></span>
                            <span>Completed: <strong id="statCompleted2">0</strong></span>
                            <span>Failed: <strong id="statFailed2">0</strong></span>
                        </div>
                    </div>
                </div>
            `;
            const stats2 = { pending: 0, downloading: 0, paused: 0, completed: 0, failed: 0 };
            currentQueueItems.forEach(item => {
                const status = item.download_status || item.status || 'pending';
                if (stats2[status] !== undefined) stats2[status]++;
            });
            document.getElementById('statPending2').textContent = stats2.pending;
            document.getElementById('statDownloading2').textContent = stats2.downloading;
            document.getElementById('statPaused2').textContent = stats2.paused;
            document.getElementById('statCompleted2').textContent = stats2.completed;
            document.getElementById('statFailed2').textContent = stats2.failed;
        } catch (e) {
            container.innerHTML = `<div class="empty-msg">❌ Error loading stats: ${e.message}</div>`;
        }
    }

    // ============================================================
    //  SEARCH
    // ============================================================
    async function performSearchAndBuild() {
        const term = document.getElementById('searchTerm').value.trim();
        const entity = document.getElementById('entityType').value;
        if (!term) return;
        const container = document.getElementById('treeContainer');
        container.innerHTML = '<div class="empty-msg"><i class="fas fa-spinner fa-pulse"></i> Searching...</div>';
        try {
            const results = await searchItunes(term, entity);
            if (!results.length) { container.innerHTML = '<div class="empty-msg">No results found.</div>'; return; }
            let topLevel = results.filter(r => r.wrapperType === 'artist');
            if (entity === 'album') topLevel = results.filter(r => r.wrapperType === 'collection');
            else if (entity === 'song') topLevel = results.filter(r => r.wrapperType === 'track');
            else topLevel = [...results.filter(r => r.wrapperType === 'artist'), ...results.filter(r => r
                    .wrapperType === 'collection'), ...results.filter(r => r.wrapperType === 'track')];
            renderTree(container, topLevel);
        } catch (e) {
            container.innerHTML = `<div class="empty-msg">❌ Error: ${e.message}</div>`;
            console.error(e);
        }
    }

    // ============================================================
    //  SET SELECTED METADATA (right panel)
    // ============================================================
    function setSelectedMetadata(item) {
        const rightPanelElement = document.getElementById('rightPanel');
        const type = item.wrapperType;
        const title = item.trackName || item.collectionName || item.artistName || '—';
        const artist = item.artistName || (type === 'artist' ? 'Artist Profile' : '—');
        const genre = item.primaryGenreName || '—';
        const date = item.releaseDate ? new Date(item.releaseDate).toLocaleDateString() : '—';
        let artwork = item.artworkUrl100 || item.artworkUrl60 || null;
        const mirrorHtml = renderMirrorButtons(item.mirrorUrls || {}, item);
        const lyricsHtml = (type === 'track' && item.lyrics) ? renderLyrics(item.lyrics) : '';

        let addHandler = '';
        let addButtonText = 'Add to Queue';
        if (type === 'track' && item.trackId) {
            addHandler =
                `addAllToQueueByType('track', '${cleanId(item.trackId)}', '${escapeHtml(title)}')`;
            addButtonText = 'Add Track';
        } else if (type === 'collection' && item.collectionId) {
            addHandler =
                `addAllToQueueByType('album', '${cleanId(item.collectionId)}', '${escapeHtml(title)}')`;
            addButtonText = 'Add All Tracks';
        } else if (type === 'artist' && item.artistId) {
            addHandler =
                `addAllToQueueByType('artist', '${cleanId(item.artistId)}', '${escapeHtml(title)}')`;
            addButtonText = 'Add All Albums & Tracks';
        }

        // Play button
        let playHandler = '';
        let playButton = '';
        if (type === 'track' && item.mirrorUrls?.audioUrl) {
            const audioRaw = item.mirrorUrls.audioUrl.url || item.mirrorUrls.audioUrl;
            if (audioRaw) {
                const proxied = getProxiedAudioUrl(audioRaw);
                if (proxied) {
                    playHandler =
                        `playAudioUrl('${escapeHtml(proxied)}', '${escapeHtml(title)}', '${escapeHtml(artist)}', '${escapeHtml(artwork || '')}')`;
                    playButton =
                        `<button class="btn btn-play" style="width:100%; justify-content:center; margin-bottom:4px;" onclick="${playHandler}"><i class="fas fa-play"></i> Play Track</button>`;
                }
            }
        }

        rightPanelElement.innerHTML = `
            <div class="props-card">
                ${artwork ? `<img src="${artwork}" class="props-artwork" alt="cover">` : `<div style="height:80px; background:#e1e1e1; margin-bottom:6px; display:flex;align-items:center;justify-content:center;"><i class="fas fa-database fa-2x" style="color:#aaa;"></i></div>`}
                <div class="props-title" onclick="routeToInternalLink('${type === 'collection' ? 'collection' : type}', '${item.collectionId || item.artistId || item.trackId}')">${escapeHtml(title)}</div>
                ${playButton}
                <div class="props-row"><span>Type</span><strong>${type.toUpperCase()}</strong></div>
                <div class="props-row"><span>Artist</span><strong>${escapeHtml(artist)}</strong></div>
                <div class="props-row"><span>Genre</span><strong>${escapeHtml(genre)}</strong></div>
                <div class="props-row"><span>Release</span><strong>${date}</strong></div>
                ${mirrorHtml ? `<div class="props-row"><span>Mirrors</span><strong>${mirrorHtml}</strong></div>` : ''}
                ${lyricsHtml ? `<div class="props-row" style="flex-direction:column;align-items:stretch;"><span>Lyrics</span><strong>${lyricsHtml}</strong></div>` : ''}
                <hr>
                <button class="btn btn-success" style="width:100%; justify-content:center;" onclick="${addHandler}"><i class="fas fa-download"></i> ${addButtonText}</button>
            </div>
        `;
    }

    // ============================================================
    //  INIT TABS
    // ============================================================
    function initTabs() {
        const tabs = document.querySelectorAll('.tab');
        const panes = { queue: document.getElementById('queue-tab'), browse: document.getElementById('browse-tab'),
            stats: document.getElementById('stats-tab') };
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.dataset.tab;
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                Object.values(panes).forEach(p => p.classList.remove('active-pane'));
                panes[tabId].classList.add('active-pane');
                if (tabId === 'queue') loadQueue();
                if (tabId === 'stats') loadStats();
            });
        });
    }

    // ============================================================
    //  KEYBOARD SHORTCUTS
    // ============================================================
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            if (document.querySelector('[data-tab="queue"].active')) loadQueue();
            else if (document.querySelector('[data-tab="stats"].active')) loadStats();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchTerm').focus();
        }
        if (e.key === 'Escape') {
            closeAllDrawers();
        }
    });

    // ============================================================
    //  EVENT BINDINGS
    // ============================================================
    document.getElementById('doSearchBtn').addEventListener('click', performSearchAndBuild);
    document.getElementById('refreshQueueBtn').addEventListener('click', loadQueue);
    document.getElementById('clearFailedBtn').addEventListener('click', clearFailed);
    document.getElementById('retryFailedBtn').addEventListener('click', retryFailed);
    document.getElementById('exportQueueBtn').addEventListener('click', exportQueue);
    document.getElementById('searchTerm').addEventListener('keypress', e => { if (e.key === 'Enter')
        performSearchAndBuild(); });

    document.getElementById('treeBulkAddBtn').addEventListener('click', addSelectedToQueue);
    document.getElementById('treeSelectAllBtn').addEventListener('click', treeSelectAll);
    document.getElementById('treeSelectNoneBtn').addEventListener('click', treeSelectNone);
    document.getElementById('treeExpandAllBtn').addEventListener('click', treeExpandAll);
    document.getElementById('treeCollapseAllBtn').addEventListener('click', treeCollapseAll);

    document.getElementById('queueBulkStartBtn').addEventListener('click', () => bulkUpdate('downloading'));
    document.getElementById('queueBulkPauseBtn').addEventListener('click', () => bulkUpdate('paused'));
    document.getElementById('queueBulkResumeBtn').addEventListener('click', () => bulkUpdate('downloading'));
    document.getElementById('queueBulkStopBtn').addEventListener('click', () => bulkUpdate('stopped'));
    document.getElementById('queueBulkRetryBtn').addEventListener('click', () => bulkUpdate('pending'));
    document.getElementById('queueBulkDeleteBtn').addEventListener('click', bulkDelete);
    document.getElementById('queueSelectAllBtn').addEventListener('click', queueSelectAllVisible);
    document.getElementById('queueSelectNoneBtn').addEventListener('click', queueSelectNone);
    document.getElementById('queueInvertBtn').addEventListener('click', queueInvertSelection);

    document.getElementById('queueStatusFilter').addEventListener('change', filterQueueItems);
    document.getElementById('queueSearchFilter').addEventListener('input', filterQueueItems);
    document.getElementById('queueSortBy').addEventListener('change', filterQueueItems);
    document.getElementById('clearQueueFilters').addEventListener('click', () => {
        document.getElementById('queueStatusFilter').value = 'all';
        document.getElementById('queueSearchFilter').value = '';
        document.getElementById('queueSortBy').value = 'id';
        filterQueueItems();
    });

    document.getElementById('resultFilter').addEventListener('change', () => {
        const term = document.getElementById('searchTerm').value.trim();
        if (term) performSearchAndBuild();
    });

    menuToggle.addEventListener('click', () => {
        rightPanel.classList.remove('open-drawer');
        leftSidebar.classList.toggle('open-drawer');
        layoutOverlay.classList.toggle('active', leftSidebar.classList.contains('open-drawer'));
    });

    propsToggle.addEventListener('click', () => {
        leftSidebar.classList.remove('open-drawer');
        rightPanel.classList.toggle('open-drawer');
        layoutOverlay.classList.toggle('active', rightPanel.classList.contains('open-drawer'));
    });

    layoutOverlay.addEventListener('click', closeAllDrawers);

    // ============================================================
    //  BOOT
    // ============================================================
    window.addEventListener('load', () => {
        initAudioPlayer();
        initTabs();
        performSearchAndBuild();
        loadQueue();
        setInterval(loadQueue, 15000);
        setInterval(() => {
            if (document.querySelector('[data-tab="stats"].active')) loadStats();
        }, 30000);
    });
</script>
</body>
</html>
