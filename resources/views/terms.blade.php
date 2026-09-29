<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | Victor Technology</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100">
    <header class="border-b border-slate-800 bg-slate-950">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-5 py-5 sm:px-8">
            <a href="{{ route('vivo_users.create') }}" class="text-sm font-medium text-slate-300 transition hover:text-white">
                <i class="fa-solid fa-arrow-left mr-2" aria-hidden="true"></i>Back to sign up
            </a>
            <a href="https://wa.me/255799280913" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-300 transition hover:text-emerald-200">
                <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i>
                <span>+255799280913</span>
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
        <section class="mb-12 border-b border-slate-800 pb-10">
            <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-emerald-300">Service terms</p>
            <h1 class="text-4xl font-bold text-white sm:text-5xl">Victor Technology</h1>
            <h2 class="mt-4 max-w-3xl text-xl font-semibold text-sky-300 sm:text-2xl">Smart Aquarium Control Panel (SACP)</h2>
            <p class="mt-2 text-lg text-slate-300">Terms and Conditions</p>
            <p class="mt-5 max-w-2xl text-sm leading-6 text-slate-400">These terms explain the basic conditions for using SACP to monitor and control your connected aquarium.</p>
        </section>

        <div class="grid gap-12 md:grid-cols-[minmax(0,1fr)_16rem]">
            <article class="max-w-2xl space-y-9 text-sm leading-7 text-slate-300">
                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">1. Using the service</h3>
                    <p>By creating an account or using SACP, you agree to these terms. You must provide accurate account details and keep your sign-in information and aquarium device key private.</p>
                </section>

                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">2. Aquarium data and account access</h3>
                    <p>Each account is assigned its own aquarium. Telemetry and actuator commands are associated with that aquarium and are available only through its owner account and authorized device credentials. Do not share your device key. If it may have been exposed, rotate it from dashboard settings.</p>
                </section>

                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">3. Device operation and animal care</h3>
                    <p>SACP readings and remote commands are provided as monitoring and control aids. Sensors, networks, power, and connected hardware can fail or report inaccurate values. Check aquarium conditions and equipment directly, and do not rely on SACP as a substitute for appropriate care or supervision.</p>
                </section>

                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">4. Responsible use</h3>
                    <p>Use the service only with aquariums and devices you are authorized to manage. Do not attempt to access another account's aquarium, interfere with the service, or use actuator controls in a way that could cause harm or damage.</p>
                </section>

                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">5. Service availability</h3>
                    <p>Connectivity, maintenance, or hardware issues may interrupt monitoring and control. You remain responsible for maintaining your aquarium when the service or a connected device is unavailable.</p>
                </section>

                <section>
                    <h3 class="mb-2 text-base font-semibold text-white">6. Contact</h3>
                    <p>For questions about these terms or help with SACP, contact Victor Technology on WhatsApp at <a href="https://wa.me/255799280913" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-300 underline decoration-emerald-700 underline-offset-4 hover:text-emerald-200">+255799280913</a>.</p>
                </section>
            </article>

            <aside class="h-fit border-l-2 border-emerald-400 pl-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Provider</p>
                <p class="mt-2 font-semibold text-white">Victor Technology</p>
                <a href="https://wa.me/255799280913" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-300 hover:text-emerald-200">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>+255799280913
                </a>
            </aside>
        </div>
    </main>
</body>
</html>