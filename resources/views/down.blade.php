<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Maintenance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="antialiased bg-gray-100 text-gray-900">
    <main class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-2xl rounded-md border border-gray-200 bg-white p-6">
            <h1 class="text-xl font-semibold">
                {{ app('maintenance')->current()->title ?? 'Maintenance' }}
            </h1>

            <div class="mt-4 text-sm leading-6 text-gray-700">
                {!! app('maintenance')->current()->description ?? 'We are currently performing scheduled maintenance. Please check back soon.' !!}
            </div>

            @if(app('maintenance')->current()?->ends_at)
                <p class="mt-6 text-sm text-gray-600">
                    We'll be back up by {{ app('maintenance')->current()->ends_at->format('F jS, \a\t g:ia') }}.
                </p>
            @endif
        </div>
    </main>
</body>
</html>