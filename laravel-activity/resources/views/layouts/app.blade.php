<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --surface: #1e293b;
            --surface-hover: #334155;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Modern Dashboard Elements */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #fff;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            display: block;
        }

        .glass-card {
            background: var(--surface);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            margin-bottom: 2.5rem;
            border: 1px solid var(--border);
        }

        .tabs {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            overflow-x: auto;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 1rem;
            width: fit-content;
        }

        .tab {
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .tab:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .tab.active {
            color: #fff;
            background: var(--primary);
            box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.39);
        }

        /* Forms */
        label {
            display: block;
            margin-bottom: 0.625rem;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.8125rem;
        }

        input, select {
            width: 100%;
            padding: 0 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            color: var(--text);
            font-family: inherit;
            transition: all 0.2s;
            box-sizing: border-box;
            height: 48px;
            font-size: 0.9375rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        select {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.1rem;
            padding-right: 3rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem;
            height: 48px;
            border-radius: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9375rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .btn-success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .btn-success:hover { background: var(--success); color: #fff; }
        
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        
        .btn-sm {
            height: 36px;
            padding: 0 1rem;
            font-size: 0.8125rem;
            border-radius: 0.625rem;
        }

        /* Modern List */
        .task-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .task-item {
            display: grid;
            grid-template-columns: 48px 1fr auto auto auto auto;
            align-items: center;
            background: var(--surface);
            padding: 1.25rem 1.5rem;
            border-radius: 1.25rem;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .task-item:hover {
            transform: scale(1.01);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
        }

        .task-sn {
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.875rem;
        }

        .task-name {
            font-weight: 600;
            font-size: 1.0625rem;
            color: #fff;
        }

        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 0.625rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-High { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .badge-Medium { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .badge-Low { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        
        .badge-status { 
            background: rgba(99, 102, 241, 0.1); 
            color: #a5b4fc; 
            border: 1px solid rgba(99, 102, 241, 0.15);
        }

        .task-deadline {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 120px;
            text-align: right;
            padding-right: 2rem;
        }

        .flex { display: flex; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        
        .alert { 
            padding: 1.25rem; 
            border-radius: 1rem; 
            margin-bottom: 2rem; 
            background: rgba(16, 185, 129, 0.1); 
            color: #10b981; 
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-weight: 600;
        }
        .alert-error { 
            background: rgba(239, 68, 68, 0.1); 
            color: #ef4444; 
            border: 1px solid rgba(239, 68, 68, 0.2); 
        }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
