<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List App</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4ff; display: flex; }

        /* Sidebar */
        .sidebar { width: 200px; background: #1a237e; min-height: 100vh; padding: 20px 0; position: fixed; }
        .sidebar h2 { color: white; text-align: center; padding: 10px; font-size: 18px; border-bottom: 1px solid #3949ab; margin-bottom: 20px; }
        .sidebar a { display: block; color: #90caf9; padding: 12px 20px; text-decoration: none; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: #3949ab; color: white; }

        /* Main Content */
        .main { margin-left: 200px; padding: 20px; width: calc(100% - 200px); }

        /* Top Bar */
        .topbar { background: white; padding: 15px 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .topbar h1 { font-size: 20px; color: #1a237e; }
        .topbar span { color: #555; font-size: 14px; }

        /* Stats Cards */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { font-size: 28px; color: #1a237e; }
        .card p { font-size: 12px; color: #777; margin-top: 5px; }
        .card.green h3 { color: #2e7d32; }
        .card.red h3 { color: #c62828; }
        .card.orange h3 { color: #e65100; }

        /* Charts */
        .charts { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .chart-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .chart-box h3 { font-size: 15px; color: #1a237e; margin-bottom: 15px; }

        /* Add Task Form */
        .form-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-box h3 { color: #1a237e; margin-bottom: 15px; }
        .form-box input, .form-box textarea, .form-box select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .btn-add { background: #1a237e; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-add:hover { background: #3949ab; }

        /* Tasks Table */
        .table-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .table-box h3 { color: #1a237e; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #1a237e; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f5f5f5; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge.pending { background: #fff3e0; color: #e65100; }
        .badge.completed { background: #e8f5e9; color: #2e7d32; }
        .btn-complete { background: #1565c0; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-delete { background: #c62828; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-left: 5px; }
        .success-msg { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>📝 To-Do App</h2>
    <a href="{{ route('tasks.index') }}" class="active">🏠 Dashboard</a>
    <a href="{{ route('tasks.index') }}">📋 My Tasks</a>
    <a href="{{ route('tasks.index') }}">✅ Completed</a>
    <a href="{{ route('tasks.index') }}">📊 Reports</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="width:100%; background:none; border:none; text-align:left; padding: 12px 20px; color:#90caf9; cursor:pointer; font-size:14px;">🚪 Logout</button>
    </form>
</div>

<!-- Main Content -->
<div class="main">
    <!-- Top Bar -->
    <div class="topbar">
        <h1>Dashboard</h1>
        <span>Welcome, {{ Auth::user()->name }}! 👋</span>
    </div>

    @if(session('success'))
        <div class="success-msg">✅ {{ session('success') }}</div>
    @endif

    <!-- Stats Cards -->
    <div class="stats">
        <div class="card">
            <h3>{{ count($tasks) }}</h3>
            <p>Total Tasks</p>
        </div>
        <div class="card green">
            <h3>{{ $completed }}</h3>
            <p>Completed Tasks</p>
        </div>
        <div class="card red">
            <h3>{{ $pending }}</h3>
            <p>Pending Tasks</p>
        </div>
        <div class="card orange">
            <h3>{{ count($tasks) > 0 ? round(($completed/count($tasks))*100) : 0 }}%</h3>
            <p>Completion Rate</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts">
        <div class="chart-box">
            <h3>Tasks Overview</h3>
            <canvas id="pieChart" height="200"></canvas>
        </div>
        <div class="chart-box">
            <h3>Completed vs Pending</h3>
            <canvas id="barChart" height="200"></canvas>
        </div>
    </div>

    <!-- Add Task Form -->
    <div class="form-box">
        <h3>➕ Add New Task</h3>
        <form method="POST" action="{{ route('tasks.store') }}">
            @csrf
            <input type="text" name="title" placeholder="Task Title" required>
            <textarea name="description" placeholder="Description (optional)" rows="2"></textarea>
            <button type="submit" class="btn-add">Add Task</button>
        </form>
    </div>

    <!-- Tasks Table -->
    <div class="table-box">
        <h3>📋 My Tasks ({{ count($tasks) }})</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $i => $task)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->description ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $task->is_completed ? 'completed' : 'pending' }}">
                            {{ $task->is_completed ? 'Completed' : 'Pending' }}
                        </span>
                    </td>
                    <td>{{ $task->created_at->format('M d, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('tasks.update', $task) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-complete">
                                {{ $task->is_completed ? 'Undo' : 'Complete' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:#999">No tasks yet! Add one above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const completed = {{ $completed }};
    const pending = {{ $pending }};

    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{ data: [completed, pending], backgroundColor: ['#2e7d32', '#c62828'] }]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                label: 'Tasks',
                data: [completed, pending],
                backgroundColor: ['#2e7d32', '#c62828']
            }]
        },
        options: { responsive: true }
    });
</script>
</body>
</html>