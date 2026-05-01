<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Maintenance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="antialiased bg-white text-gray-900">
    <main class="min-h-screen flex flex-col px-8 py-8 max-w-6xl mx-auto">

        {{--? MARK: APP NAME --}}
        <div class="mb-auto">
            <span class="text-base font-semibold text-gray-900">{{ config('app.name') }}</span>
        </div>

        <div class="flex flex-col lg:flex-row items-center justify-between gap-12 py-16">

            {{--? MARK: LEFT CONTENT --}}
            <div class="w-full lg:w-1/2 order-last lg:order-first">

                <!-- Main Title -->
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                    We'll Be Right Back!
                </h1>

                <!-- Sub Title -->
                <h2 class="text-3xl sm:text-4xl font-medium text-gray-900 mb-1 leading-tight">
                    {{ app('maintenance')->current()->title ?? 'Scheduled Maintenance' }}
                </h2>

                <!-- Description -->
                <div class="text-gray-500 text-base leading-7 mb-8">
                    {!! app('maintenance')->current()->description ?? 'We\'re currently performing scheduled maintenance to improve your experience. We apologize for the inconvenience and appreciate your patience.' !!}
                </div>

                <!-- Countdown -->
                @if(app('maintenance')->current()?->ends_at)
                    <p class="text-gray-600 text-base mb-1">
                        Our site will be available in:
                        <span class="font-bold text-gray-900 ml-1">
                            <span id="hours">00</span> : <span id="minutes">00</span> : <span id="seconds">00</span>
                        </span>
                    </p>

                    <p class="text-sm text-gray-400 mt-1">
                        We will be back online on
                        <span class="font-medium text-gray-600">
                            {{ app('maintenance')->current()->ends_at->format('F jS, \a\t g:ia') }}
                        </span>
                    </p>

                    <script>
                        const endsAt = new Date("{{ app('maintenance')->current()->ends_at->toIso8601String() }}").getTime();
                        function updateTimer() {
                            const now = new Date().getTime();
                            const diff = endsAt - now;
                            if (diff <= 0) {
                                document.getElementById('hours').textContent = '00';
                                document.getElementById('minutes').textContent = '00';
                                document.getElementById('seconds').textContent = '00';
                                return;
                            }
                            const hours = Math.floor(diff / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
                        }

                        updateTimer();
                        setInterval(updateTimer, 1000);
                    </script>
                @endif

            </div>

            {{--? MARK: RIGHT CONTENT --}}
            <div class="w-full lg:w-1/2 flex justify-center lg:justify-end order-first lg:order-last">
                <img
                    src="{{ asset('vendor/scheduled-maintenance/maintenance.svg') }}"
                    alt="Maintenance Illustration"
                    class="w-full max-w-md pointer-events-none select-none"
                    draggable="false"
                />
            </div>

        </div>

        <div class="mt-auto"></div>

    </main>
</body>
</html>