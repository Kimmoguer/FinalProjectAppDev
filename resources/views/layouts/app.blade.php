<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- @yield('title') pulls the title from each child view --}}
    <title>@yield('title', 'TaskFlow') — To Do App</title>

    {{-- Google Fonts: Inter (clean, modern sans-serif) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        /* ============================================================
           CSS DESIGN TOKENS  (Glassmorphism Dark Theme)
           Using CSS custom properties (--variables) means we can
           change the whole colour scheme from one place.
        ============================================================ */
        :root {
            /* Dark background layers */
            --bg-deep:        #0a0a14;    /* page background */
            --bg-mid:         #10101e;
            /* Glass effect surfaces */
            --glass-bg:       rgba(255, 255, 255, 0.06);
            --glass-border:   rgba(255, 255, 255, 0.12);
            --glass-hover:    rgba(255, 255, 255, 0.10);
            /* Accent colours (vibrant on dark backgrounds) */
            --accent-violet:  #7c3aed;
            --accent-cyan:    #06b6d4;
            --accent-green:   #10b981;
            --accent-rose:    #f43f5e;
            --accent-amber:   #f59e0b;
            /* Typography */
            --text-primary:   #f1f5f9;   /* bright white-ish */
            --text-secondary: #94a3b8;   /* muted */
            --text-muted:     #475569;   /* very muted */
            /* Effects */
            --shadow-glow:    0 0 40px rgba(124, 58, 237, 0.15);
            --radius-card:    16px;
            --radius-btn:     10px;
            /* backdrop-filter creates the glass blur effect */
            --blur:           blur(16px);
        }

        /* ============================================================
           RESET  --  Remove browser default margins/padding
        ============================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            /*
             * Glassmorphism background: layered radial gradients create
             * soft "light blobs" visible through the glass cards.
             */
            background-image:
                radial-gradient(ellipse at 15% 20%, rgba(124,58,237,0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 75%, rgba(6,182,212,0.13) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, rgba(16,185,129,0.06) 0%, transparent 70%);
        }

        a { color: inherit; text-decoration: none; }
        ul { list-style: none; }

        /* ============================================================
           PAGE WRAPPER  --  Centres content, adds padding
        ============================================================ */
        .app-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem;
        }

        /* ============================================================
           HEADER / BRAND  --  Top bar with logo + action slot
        ============================================================ */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2.5rem;
        }

        .brand { display: flex; align-items: center; gap: .75rem; }

        /* Gradient square icon behind the checkmark */
        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent-violet), var(--accent-cyan));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 0 20px rgba(124,58,237,0.4);
        }

        /* Gradient text effect for the app name */
        .brand-name {
            font-size: 1.5rem; font-weight: 700;
            background: linear-gradient(90deg, var(--accent-violet), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-sub { font-size: .75rem; color: var(--text-muted); }

        /* ============================================================
           GLASS CARD  --  The core Glassmorphism component.
           backdrop-filter: blur() creates the frosted-glass look by
           blurring whatever is rendered BEHIND the element.
        ============================================================ */
        .glass-card {
            background: var(--glass-bg);          /* semi-transparent */
            backdrop-filter: var(--blur);         /* blur the background */
            -webkit-backdrop-filter: var(--blur); /* Safari */
            border: 1px solid var(--glass-border);/* thin light border */
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-glow), 0 4px 24px rgba(0,0,0,0.35);
        }

        /* ============================================================
           NAVIGATION TABS  --  Status filter pills
        ============================================================ */
        .tab-bar { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.75rem; }

        .tab {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .55rem 1.1rem;
            border-radius: 50px;           /* pill shape */
            font-size: .82rem; font-weight: 500;
            color: var(--text-secondary);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: var(--blur);
            transition: all .2s ease;
            text-decoration: none;
        }

        .tab:hover { background: var(--glass-hover); color: var(--text-primary); }

        /* Active tab: filled gradient instead of glass */
        .tab.active {
            background: linear-gradient(135deg, var(--accent-violet), var(--accent-cyan));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 0 18px rgba(124,58,237,0.45);
        }

        /* Count bubble inside each tab */
        .tab-badge {
            background: rgba(255,255,255,0.18);
            border-radius: 50px; padding: 1px 7px;
            font-size: .72rem; font-weight: 600;
        }

        .tab.active .tab-badge { background: rgba(255,255,255,0.25); }

        /* ============================================================
           FLASH MESSAGES  --  Success/Error banners
           These are session messages set by the controller with:
             ->with('success', 'Task created!')
        ============================================================ */
        .flash {
            display: flex; align-items: center; gap: .75rem;
            padding: .9rem 1.2rem;
            border-radius: var(--radius-btn);
            margin-bottom: 1.5rem;
            font-size: .88rem; font-weight: 500;
            border: 1px solid;
            animation: slideDown .35s ease;
        }
        .flash-success { background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.3); color: #34d399; }
        .flash-error   { background: rgba(244,63,94,.12);  border-color: rgba(244,63,94,.3);  color: #fb7185; }

        /* Slide-in animation for flash messages */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ============================================================
           BUTTONS
        ============================================================ */
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .6rem 1.3rem;
            border-radius: var(--radius-btn);
            font-size: .83rem; font-weight: 600;
            cursor: pointer; border: none;
            transition: all .2s ease;
            text-decoration: none;
        }
        /* Primary (violet gradient) -- used for "Add Task" */
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-violet), #6d28d9);
            color: #fff;
            box-shadow: 0 0 16px rgba(124,58,237,.4);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 0 24px rgba(124,58,237,.6); }

        /* Success (green) -- used for "Update" */
        .btn-success {
            background: linear-gradient(135deg, var(--accent-green), #059669);
            color: #fff;
            box-shadow: 0 0 14px rgba(16,185,129,.35);
        }
        .btn-success:hover { transform: translateY(-2px); }

        /* Danger (red) -- used for "Remove" */
        .btn-danger {
            background: linear-gradient(135deg, var(--accent-rose), #be123c);
            color: #fff;
            box-shadow: 0 0 14px rgba(244,63,94,.35);
        }
        .btn-danger:hover { transform: translateY(-2px); }

        /* Ghost -- transparent, used for "Cancel" and "Back" */
        .btn-ghost {
            background: var(--glass-bg);
            color: var(--text-secondary);
            border: 1px solid var(--glass-border);
        }
        .btn-ghost:hover { background: var(--glass-hover); color: var(--text-primary); }

        /* Small variant for table action buttons */
        .btn-sm { padding: .38rem .85rem; font-size: .78rem; }

        /* ============================================================
           FORM CONTROLS
        ============================================================ */
        .form-group { display: flex; flex-direction: column; gap: .5rem; margin-bottom: 1.4rem; }

        .form-label {
            font-size: .82rem; font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: .06em;
        }

        /* Dark glass-style inputs and selects */
        .form-control {
            width: 100%; padding: .75rem 1rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-btn);
            color: var(--text-primary);
            font-size: .9rem; font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        /* Violet glow on focus */
        .form-control:focus {
            border-color: var(--accent-violet);
            box-shadow: 0 0 0 3px rgba(124,58,237,.2);
        }

        .form-control option { background: #1e1e30; }

        /* Inline validation error text */
        .form-error { font-size: .78rem; color: #fb7185; margin-top: .2rem; }

        /* ============================================================
           PRIORITY BADGES
           These class names are returned by $task->priorityClass
        ============================================================ */
        .badge {
            display: inline-flex; align-items: center;
            padding: .28rem .75rem; border-radius: 50px;
            font-size: .73rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .priority-high   { background: rgba(244,63,94,.18);  color: #fb7185; border: 1px solid rgba(244,63,94,.3); }
        .priority-medium { background: rgba(245,158,11,.15); color: #fbbf24; border: 1px solid rgba(245,158,11,.3); }
        .priority-low    { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(16,185,129,.3); }

        /* ============================================================
           TASK TABLE
        ============================================================ */
        .task-table { width: 100%; border-collapse: collapse; }

        .task-table thead tr { border-bottom: 1px solid var(--glass-border); }

        .task-table th {
            padding: 1rem 1.2rem; text-align: left;
            font-size: .75rem; font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .08em;
        }

        .task-table td {
            padding: .95rem 1.2rem;
            font-size: .87rem; color: var(--text-primary);
            border-bottom: 1px solid rgba(255,255,255,.05);
            vertical-align: middle;
        }

        /* Subtle hover highlight on rows */
        .task-table tbody tr { transition: background .15s; }
        .task-table tbody tr:hover { background: rgba(255,255,255,.04); }
        .task-table tbody tr:last-child td { border-bottom: none; }

        .task-name { font-weight: 500; }
        .task-sn   { color: var(--text-muted); font-weight: 500; width: 52px; }
        .task-deadline { font-size: .82rem; color: var(--text-secondary); }
        .task-actions  { display: flex; gap: .5rem; align-items: center; }

        /* ============================================================
           EMPTY STATE  --  Shown when no tasks exist for a filter
        ============================================================ */
        .empty-state { text-align: center; padding: 4rem 2rem; }
        .empty-icon  { font-size: 3rem; margin-bottom: 1rem; opacity: .4; }
        .empty-title { font-size: 1.1rem; font-weight: 600; color: var(--text-secondary); margin-bottom: .4rem; }
        .empty-sub   { font-size: .85rem; color: var(--text-muted); }

        /* ============================================================
           TABLE SECTION HEADER  --  Title + count above the table
        ============================================================ */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.5rem 1.5rem 0; margin-bottom: 1.2rem;
        }
        .section-title { font-size: 1.05rem; font-weight: 700; color: var(--text-primary); }
        .section-count { font-size: .8rem; color: var(--text-muted); }

        /* ============================================================
           FORM PAGE LAYOUT  --  For create.blade.php & edit.blade.php
        ============================================================ */
        .form-card { padding: 2rem 2.5rem; max-width: 560px; margin: 0 auto; }

        .form-page-title {
            font-size: 1.4rem; font-weight: 700; margin-bottom: .35rem;
            background: linear-gradient(90deg, var(--accent-violet), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-page-sub { font-size: .85rem; color: var(--text-muted); margin-bottom: 2rem; }
        .form-actions  { display: flex; gap: .75rem; margin-top: 1.75rem; }

        /* ============================================================
           RESPONSIVE  --  Adjust layout on small screens
        ============================================================ */
        @media (max-width: 640px) {
            .app-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            /* Hide Priority column on mobile to save space */
            .task-table th:nth-child(3),
            .task-table td:nth-child(3) { display: none; }
            .form-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="app-wrapper">

    {{-- ─── HEADER ────────────────────────────────────────────────────
         @section('header-action') is a slot that child views fill in.
         The index view puts "Add Task" here; form views put "Back".
    ──────────────────────────────────────────────────────────────── --}}
    <header class="app-header">
        {{-- Clicking the brand logo always goes home --}}
        <a href="{{ route('tasks.index') }}" class="brand">
            <div class="brand-icon">✅</div>
            <div>
                <div class="brand-name">TaskFlow</div>
                <div class="brand-sub">Eloquent ORM · Laravel</div>
            </div>
        </a>

        {{-- Child views inject a button here --}}
        @yield('header-action')
    </header>

    {{-- ─── FLASH MESSAGES ────────────────────────────────────────────
         session('success') is set by the controller with:
           ->with('success', 'Task created!')
         It only exists for ONE request then is automatically cleared.
    ──────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="flash flash-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flash flash-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- ─── MAIN CONTENT ──────────────────────────────────────────────
         Each child view (index, create, edit) fills this section.
    ──────────────────────────────────────────────────────────────── --}}
    @yield('content')

</div>

</body>
</html>