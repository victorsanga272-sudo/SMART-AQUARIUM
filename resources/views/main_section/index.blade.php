<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
        }

        .main-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto minmax(0, 1fr) auto;
            min-height: 100vh;
            min-height: 100svh;
        }

        .header {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) minmax(17rem, 30rem);
            align-items: center;
            gap: clamp(0.75rem, 2vw, 2rem);
            position: relative;
            z-index: 2;
            min-height: 8rem;
            padding: 1rem clamp(1rem, 4vw, 3rem);
            background: #0c4a6e;
            color: white;
            box-shadow: 0 4px 12px rgb(0 0 0 / 20%);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: clamp(3.5rem, 7vw, 6rem);
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 50%;
            background: #1e40af;
            box-shadow: 0 4px 8px rgb(0 0 0 / 20%);
        }

        .logo h1,
        .title h1 {
            margin: 0;
        }

        .logo h1 {
            font-size: clamp(1.75rem, 4vw, 2.25rem);
        }

        .title {
            min-width: 0;
        }

        .title h1 {
            font-size: clamp(1.25rem, 2.5vw, 2rem);
            line-height: 1.2;
        }

        .navigation {
            width: 100%;
            border-radius: 0.5rem;
            background: #075985;
        }

        .menu-toggle {
            display: none;
        }

        .navigation ul {
            display: flex;
            align-items: stretch;
            justify-content: space-around;
            gap: 0.25rem;
            margin: 0;
            padding: 0.25rem;
            list-style: none;
        }

        .navigation a {
            display: flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 0.75rem;
            border-radius: 0.375rem;
            color: white;
            font-size: clamp(0.875rem, 1.2vw, 1rem);
            font-weight: 700;
            line-height: 1.35;
            text-align: center;
            text-decoration: none;
            transition: background-color 150ms ease-in-out;
        }

        .navigation a:hover,
        .navigation a:focus-visible {
            background: #0369a1;
        }

        #hero {
            position: relative;
            overflow: hidden;
            min-height: clamp(26rem, 58svh, 48rem);
            padding: clamp(1rem, 5vw, 4rem);
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 1.2s ease-in-out;
            z-index: 0;
        }

        #hero .text,
        .footer {
            position: relative;
            z-index: 1;
        }

        #hero .hero-copy {
            width: min(100%, 72rem);
            background-color: rgba(0, 0, 0, 0.5);
            padding: clamp(1.25rem, 3.5vw, 2.5rem);
            margin-inline: auto;
            display: grid;
            gap: clamp(0.875rem, 2.5vw, 1.5rem);
            text-shadow: 0 2px 8px rgb(0 0 0 / 65%);
            border-radius: 0.5rem;
        }

        #hero .hero-copy h1 {
            margin: 0;
            font-size: clamp(2rem, 5vw, 4rem);
            line-height: 1.08;
            font-weight: 700;
        }

        #hero .hero-copy p {
            max-width: 65ch;
            margin: 0;
            font-size: clamp(1rem, 2vw, 1.25rem);
            line-height: 1.6;
        }

        .content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .footer {
            width: 100%;
            padding: 1rem;
            background: #0c4a6e;
            color: white;
            text-align: center;
        }

        .footer p {
            margin: 0;
        }

        @media (max-width: 767px) {
            .header {
                grid-template-columns: auto minmax(0, 1fr) auto;
                min-height: 0;
            }

            .navigation {
                position: relative;
                justify-self: end;
                width: auto;
                background: transparent;
            }

            .menu-toggle {
                display: flex;
                width: 44px;
                height: 44px;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 0;
                border: 0;
                border-radius: 0.375rem;
                background: transparent;
                cursor: pointer;
            }

            .menu-toggle:hover,
            .menu-toggle:focus-visible {
                background: #075985;
            }

            .menu-toggle span {
                display: block;
                width: 24px;
                height: 2px;
                border-radius: 2px;
                background: white;
                transition: transform 150ms ease-in-out, opacity 150ms ease-in-out;
            }

            .navigation ul {
                position: absolute;
                top: calc(100% + 0.5rem);
                right: 0;
                display: none;
                width: min(18rem, calc(100vw - 1.5rem));
                flex-direction: column;
                padding: 0.5rem;
                border-radius: 0.5rem;
                background: #075985;
                box-shadow: 0 8px 20px rgb(0 0 0 / 25%);
            }

            .navigation.is-open ul {
                display: grid;
                grid-template-columns: 1fr;
            }

            .navigation.is-open .menu-toggle span:first-child {
                transform: translateY(7px) rotate(45deg);
            }

            .navigation.is-open .menu-toggle span:nth-child(2) {
                opacity: 0;
            }

            .navigation.is-open .menu-toggle span:last-child {
                transform: translateY(-7px) rotate(-45deg);
            }

            #hero {
                min-height: 58svh;
            }
        }

        @media (max-width: 420px) {
            .header {
                gap: 0.75rem;
                padding: 0.75rem;
            }

            #hero {
                min-height: 60svh;
                padding: 1rem;
            }

            #hero .hero-copy {
                padding: 1.25rem;
            }

            .footer {
                font-size: 0.875rem;
                color: white;
            }
        }
    </style>
    <title>Victor Technology</title>
</head>
<body>
    <section class="main-content">
        <div class="header">
            <div class="logo">
                <h1 class="text-white font-bold"><image src="{{ asset('images/logo/logo.png') }}" alt="Victor Technology Logo" class="w-full h-full object-contain" width="100" height="100"></h1>
            </div>
            <div class="title text-white ">
                <h1 class="text-white font-bold">Victor Technology</h1>
            </div>
            <nav class="navigation" aria-label="Main navigation">
                <button class="menu-toggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="main-navigation-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul id="main-navigation-menu">
                    <li><a href="{{ route('vivo_users.create') }}" class="text-white">Sign Up / Log In</a></li>
                    <li><a href="{{ route('terms') }}" class="text-white">Terms and Conditions</a></li>
                </ul>
            </nav>
        </div>
        <div id="hero" class="content text-white">
            <div class="text hero-copy">
                <h1 class="text-white font-bold">Welcome to Victor Technology</h1>
                <p class="text-white">This page makes it easy to manage your aquarium.</p>
                <p class="text-white">With our Smart Aquarium Control Panel (SACP), you can monitor and control your connected aquarium from anywhere.</p>
                <p class="text-white">To get started, please log in or sign up for an account.</p>
            </div>
        </div>
        <div class="footer">
            <p class="text-white">&copy; 2024 Victor Technology. All rights reserved.</p>
        </div>
    </section>
    <script>
  const menuToggle = document.querySelector(".menu-toggle");
  const navigation = document.querySelector(".navigation");

  menuToggle.addEventListener("click", () => {
    const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!isExpanded));
    navigation.classList.toggle("is-open", !isExpanded);
  });

  navigation.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menuToggle.setAttribute("aria-expanded", "false");
      navigation.classList.remove("is-open");
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      menuToggle.setAttribute("aria-expanded", "false");
      navigation.classList.remove("is-open");
    }
  });

  const images = [
    "{{ asset('images/1.jpg') }}",
    "{{ asset('images/2.jpg') }}",
    "{{ asset('images/3.jpg') }}",
    "{{ asset('images/4.jpg') }}",
    "{{ asset('images/5.jpg') }}",
    "{{ asset('images/6.jpg') }}",
    "{{ asset('images/7.jpg') }}",
    "{{ asset('images/8.jpg') }}"
  ];

  let current = 0;
  const hero = document.getElementById("hero");

  const firstSlide = document.createElement("div");
  firstSlide.className = "hero-bg";
  firstSlide.style.backgroundImage = `url(${images[current]})`;
  hero.prepend(firstSlide);

  setInterval(() => {
    const previousSlide = hero.querySelector(".hero-bg");
    current = (current + 1) % images.length;

    const nextSlide = document.createElement("div");
    nextSlide.className = "hero-bg";
    nextSlide.style.backgroundImage = `url(${images[current]})`;
    nextSlide.style.transform = "translateX(100%)";
    hero.prepend(nextSlide);

    requestAnimationFrame(() => {
      nextSlide.style.transform = "translateX(0)";
      if (previousSlide) {
        previousSlide.style.transform = "translateX(-100%)";
      }
    });

    setTimeout(() => {
      if (previousSlide) {
        previousSlide.remove();
      }
    }, 1200);
  }, 10000);
    </script>
</body>
</html>