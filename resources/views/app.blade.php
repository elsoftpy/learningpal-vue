<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        :root {
            color-scheme: light;
            --boot-bg: #1e3a8a;
            --boot-bg-deep: #172554;
            --boot-accent: rgba(147, 197, 253, 0.22);
            --boot-surface: rgba(255, 255, 255, 0.16);
            --boot-border: rgba(255, 255, 255, 0.22);
            --boot-text: rgba(255, 255, 255, 0.96);
            --boot-subtle: rgba(219, 234, 254, 0.78);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            background:
                radial-gradient(circle at top, rgba(96, 165, 250, 0.26), transparent 34%),
                linear-gradient(160deg, var(--boot-bg) 0%, var(--boot-bg-deep) 100%);
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        #app {
            min-height: 100vh;
        }

        .boot-screen {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 2rem;
            background:
                radial-gradient(circle at top, rgba(96, 165, 250, 0.26), transparent 34%),
                linear-gradient(160deg, var(--boot-bg) 0%, var(--boot-bg-deep) 100%);
        }

        .boot-screen::before,
        .boot-screen::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            background: var(--boot-accent);
            filter: blur(6px);
            animation: bootFloat 6s ease-in-out infinite;
        }

        .boot-screen::before {
            width: 18rem;
            height: 18rem;
            top: -4rem;
            left: -5rem;
        }

        .boot-screen::after {
            width: 14rem;
            height: 14rem;
            right: -3rem;
            bottom: -2rem;
            animation-delay: -3s;
        }

        .boot-panel {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            width: min(100%, 22rem);
            text-align: center;
        }

        .boot-logo-shell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 10rem;
            height: 10rem;
            border-radius: 2rem;
            border: 1px solid var(--boot-border);
            background: var(--boot-surface);
            box-shadow: 0 25px 80px rgba(15, 23, 42, 0.34);
            backdrop-filter: blur(12px);
            animation: bootRise 1.2s ease-out forwards, bootDrift 4.2s ease-in-out infinite 1.2s;
        }

        .boot-logo-shell::before {
            content: "";
            position: absolute;
            inset: -0.9rem;
            border-radius: 2.7rem;
            border: 1px solid rgba(191, 219, 254, 0.24);
            animation: bootPulse 2.4s ease-out infinite;
        }

        .boot-logo {
            width: 6.3rem;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 12px 24px rgba(15, 23, 42, 0.22));
        }

        .boot-copy {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .boot-title {
            margin: 0;
            color: var(--boot-text);
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .boot-subtitle {
            margin: 0;
            color: var(--boot-subtle);
            font-size: 0.96rem;
            line-height: 1.55;
        }

        .boot-dots {
            display: inline-flex;
            gap: 0.4rem;
            justify-content: center;
            align-items: center;
        }

        .boot-dots span {
            width: 0.52rem;
            height: 0.52rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.86);
            animation: bootBlink 1.25s ease-in-out infinite;
        }

        .boot-dots span:nth-child(2) {
            animation-delay: 0.18s;
        }

        .boot-dots span:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes bootRise {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes bootDrift {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes bootPulse {
            0% {
                opacity: 0.72;
                transform: scale(0.92);
            }

            70%,
            100% {
                opacity: 0;
                transform: scale(1.12);
            }
        }

        @keyframes bootBlink {
            0%,
            80%,
            100% {
                opacity: 0.28;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-3px);
            }
        }

        @keyframes bootFloat {
            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(0, 14px, 0);
            }
        }
    </style>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body>
    <div id="app">
        <div class="boot-screen" role="status" aria-live="polite" aria-label="Loading application">
            <div class="boot-panel">
                <div class="boot-logo-shell">
                    <img
                        class="boot-logo"
                        src="{{ Vite::asset('resources/js/images/brandLogo.png') }}"
                        alt="{{ config('app.name', 'IPL LearningPal') }}"
                    >
                </div>
                <div class="boot-copy">
                    <p class="boot-title">{{ config('app.name', 'IPL LearningPal') }}</p>
                    <p class="boot-subtitle">Preparing your workspace</p>
                </div>
                <div class="boot-dots" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
