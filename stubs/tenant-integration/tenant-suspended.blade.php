{{-- Copy to resources/views/errors/tenant-suspended.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account suspended</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #fff7ed; color: #7c2d12; }
        .card { max-width: 28rem; padding: 2rem; text-align: center; background: #fff; border-radius: 1rem; border: 1px solid #fed7aa; }
        h1 { font-size: 1.25rem; margin: 0 0 0.5rem; }
        p { margin: 0; color: #9a3412; font-size: 0.875rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Account access restricted</h1>
        <p>{{ $message ?? 'Your account has been suspended or billing is overdue. Please contact your administrator or PradytecAI support.' }}</p>
    </div>
</body>
</html>
