<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Masuk - SINADAS STTI Cirebon</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:ital,wght@0,700;0,900;1,900&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --stti-blue: #0067B1;
            --stti-blue-dark: #005795;
            --stti-blue-deep: #004377;
            --stti-blue-soft: rgba(0, 103, 177, 0.08);
            --stti-blue-rgb: 0, 103, 177;
            --paper: #f7f8fa;
            --ink: #1a1a1a;
            --ink-soft: #6b7280;
            --ink-faint: #d1d5db;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow: hidden;
        }

        /* ============================================================
           PRELOADER
        ============================================================ */
        .preloader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: var(--paper);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }

        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .preloader__inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .preloader__logo {
            width: 64px;
            height: 64px;
            color: var(--stti-blue);
        }

        .preloader__logo svg {
            width: 100%;
            height: 100%;
            stroke-dasharray: 400;
            stroke-dashoffset: 400;
            animation: preloaderDraw 1.5s ease-out forwards;
        }

        @keyframes preloaderDraw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .preloader__text {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-style: italic;
            font-size: 18px;
            letter-spacing: 6px;
            color: var(--stti-blue);
            opacity: 0;
            animation: preloaderText 0.8s ease-out 0.4s forwards;
        }

        @keyframes preloaderText {
            to {
                opacity: 1;
            }
        }

        .preloader__bar {
            width: 140px;
            height: 2px;
            background: rgba(var(--stti-blue-rgb), 0.1);
            border-radius: 999px;
            overflow: hidden;
        }

        .preloader__bar-fill {
            width: 0%;
            height: 100%;
            background: var(--stti-blue);
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        /* ============================================================
           HERO
        ============================================================ */
        .hero-section {
            position: relative;
            width: 100%;
            height: 100vh;
            min-height: 100svh;
            max-height: 100svh;
            overflow: hidden;
            isolation: isolate;
            background: var(--paper);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(var(--stti-blue-rgb), 0.04) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(var(--stti-blue-rgb), 0.03) 0%, transparent 50%);
            z-index: 0;
            pointer-events: none;
        }

        /* ============================================================
           FLOATING ASSETS
        ============================================================ */
        .asset-field {
            position: absolute;
            inset: 0;
            z-index: 1;
            overflow: hidden;
            pointer-events: none;
            perspective: 1400px;
            perspective-origin: 50% 45%;
            transform-style: preserve-3d;
        }

        .asset {
            position: absolute;
            color: var(--stti-blue);
            opacity: 0;
            transform-style: preserve-3d;
            will-change: transform, opacity;
            filter: drop-shadow(0 24px 28px rgba(var(--stti-blue-rgb), 0.12));
            animation:
                assetFadeIn 2.4s cubic-bezier(0.16, 1, 0.3, 1) var(--delay, 0s) forwards,
                assetDrift var(--drift-duration, 16s) cubic-bezier(0.45, 0, 0.55, 1) var(--delay, 0s) infinite;
        }

        .asset svg {
            width: 100%;
            height: 100%;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.4;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
        }

        @keyframes assetFadeIn {
            0% {
                opacity: 0;
                filter: blur(6px) drop-shadow(0 24px 28px rgba(var(--stti-blue-rgb), 0.12));
            }

            100% {
                opacity: var(--final-opacity, 0.35);
                filter: blur(0) drop-shadow(0 24px 28px rgba(var(--stti-blue-rgb), 0.12));
            }
        }

        @keyframes assetDrift {

            0%,
            100% {
                transform: translate3d(0, 0, var(--z, 0px)) rotateY(var(--rotate-y, 0deg)) rotateX(0deg) rotateZ(var(--rotate-z, 0deg));
            }

            25% {
                transform: translate3d(var(--drift-x, 10px), var(--drift-y, -15px), calc(var(--z, 0px) + 24px)) rotateY(calc(var(--rotate-y, 0deg) + 18deg)) rotateX(6deg) rotateZ(calc(var(--rotate-z, 0deg) + 2deg));
            }

            50% {
                transform: translate3d(calc(var(--drift-x, 10px) * -0.4), calc(var(--drift-y, -15px) * 0.6), var(--z, 0px)) rotateY(calc(var(--rotate-y, 0deg) - 8deg)) rotateX(-4deg) rotateZ(var(--rotate-z, 0deg));
            }

            75% {
                transform: translate3d(calc(var(--drift-x, 10px) * -1), calc(var(--drift-y, -15px) * 0.3), calc(var(--z, 0px) - 16px)) rotateY(calc(var(--rotate-y, 0deg) + 10deg)) rotateX(3deg) rotateZ(calc(var(--rotate-z, 0deg) - 2deg));
            }
        }

        .asset--router {
            top: 3%;
            left: 2%;
            width: 80px;
            height: 80px;
            --delay: 0.3s;
            --final-opacity: 0.52;
            --z: 20px;
            --rotate-y: -8deg;
            --rotate-z: -5deg;
            --drift-x: 14px;
            --drift-y: -18px;
            --drift-duration: 19s;
        }

        .asset--laptop {
            top: 12%;
            left: 6%;
            width: 110px;
            height: 110px;
            --delay: 0.6s;
            --final-opacity: 0.62;
            --z: 120px;
            --rotate-y: -12deg;
            --rotate-z: -8deg;
            --drift-x: 18px;
            --drift-y: -22px;
            --drift-duration: 18s;
        }

        .asset--monitor {
            top: 26%;
            left: 1%;
            width: 88px;
            height: 88px;
            --delay: 1.5s;
            --final-opacity: 0.46;
            --z: -60px;
            --rotate-y: 12deg;
            --rotate-z: -6deg;
            --drift-x: 12px;
            --drift-y: -18px;
            --drift-duration: 20s;
        }

        .asset--keyboard {
            top: 36%;
            left: 4%;
            width: 84px;
            height: 84px;
            --delay: 1.2s;
            --final-opacity: 0.44;
            --z: -100px;
            --rotate-y: -10deg;
            --rotate-z: 4deg;
            --drift-x: -12px;
            --drift-y: -14px;
            --drift-duration: 21s;
        }

        .asset--book {
            top: 50%;
            left: 1%;
            width: 84px;
            height: 84px;
            --delay: 0.9s;
            --final-opacity: 0.52;
            --z: 60px;
            --rotate-y: 22deg;
            --rotate-z: -14deg;
            --drift-x: 14px;
            --drift-y: -16px;
            --drift-duration: 17s;
        }

        .asset--chip {
            top: 62%;
            left: 6%;
            width: 76px;
            height: 76px;
            --delay: 1.7s;
            --final-opacity: 0.46;
            --z: -60px;
            --rotate-y: 20deg;
            --rotate-z: -12deg;
            --drift-x: 12px;
            --drift-y: -14px;
            --drift-duration: 18s;
        }

        .asset--toolbox {
            top: 74%;
            left: 2%;
            width: 80px;
            height: 80px;
            --delay: 2.4s;
            --final-opacity: 0.44;
            --z: 40px;
            --rotate-y: -14deg;
            --rotate-z: 8deg;
            --drift-x: 14px;
            --drift-y: -14px;
            --drift-duration: 19s;
        }

        .asset--car {
            bottom: 5%;
            left: 2%;
            width: 96px;
            height: 96px;
            --delay: 2.1s;
            --final-opacity: 0.52;
            --z: 80px;
            --rotate-y: 18deg;
            --rotate-z: -4deg;
            --drift-x: 16px;
            --drift-y: -14px;
            --drift-duration: 19s;
        }

        .asset--chair {
            bottom: 22%;
            left: 12%;
            width: 92px;
            height: 92px;
            --delay: 1.4s;
            --final-opacity: 0.55;
            --z: 100px;
            --rotate-y: 16deg;
            --rotate-z: 8deg;
            --drift-x: 16px;
            --drift-y: -14px;
            --drift-duration: 18s;
        }

        .asset--speaker {
            bottom: 8%;
            left: 22%;
            width: 80px;
            height: 80px;
            --delay: 2.6s;
            --final-opacity: 0.42;
            --z: -40px;
            --rotate-y: 12deg;
            --rotate-z: 6deg;
            --drift-x: 12px;
            --drift-y: -14px;
            --drift-duration: 20s;
        }

        .asset--mouse {
            top: 55%;
            left: 14%;
            width: 60px;
            height: 60px;
            --delay: 2.8s;
            --final-opacity: 0.40;
            --z: -140px;
            --rotate-y: 24deg;
            --rotate-z: -8deg;
            --drift-x: 10px;
            --drift-y: -12px;
            --drift-duration: 17s;
        }

        .asset--pliers {
            top: 88%;
            left: 34%;
            width: 70px;
            height: 70px;
            --delay: 3s;
            --final-opacity: 0.40;
            --z: 60px;
            --rotate-y: -18deg;
            --rotate-z: 12deg;
            --drift-x: 12px;
            --drift-y: -12px;
            --drift-duration: 19s;
        }

        .asset--phone {
            top: 5%;
            left: 48%;
            width: 66px;
            height: 66px;
            --delay: 2s;
            --final-opacity: 0.36;
            --z: -220px;
            --rotate-y: 30deg;
            --rotate-z: -3deg;
            --drift-x: 12px;
            --drift-y: -14px;
            --drift-duration: 16s;
        }

        .asset--lamp {
            top: 68%;
            left: 46%;
            width: 86px;
            height: 86px;
            --delay: 1.1s;
            --final-opacity: 0.44;
            --z: -180px;
            --rotate-y: -18deg;
            --rotate-z: 10deg;
            --drift-x: -14px;
            --drift-y: -18px;
            --drift-duration: 19s;
        }

        .asset--bulb {
            top: 30%;
            left: 42%;
            width: 70px;
            height: 70px;
            --delay: 2.7s;
            --final-opacity: 0.38;
            --z: -80px;
            --rotate-y: 14deg;
            --rotate-z: -6deg;
            --drift-x: 12px;
            --drift-y: -16px;
            --drift-duration: 21s;
        }

        .asset--hammer {
            bottom: 3%;
            left: 40%;
            width: 76px;
            height: 76px;
            --delay: 3.2s;
            --final-opacity: 0.42;
            --z: 80px;
            --rotate-y: -16deg;
            --rotate-z: 10deg;
            --drift-x: 14px;
            --drift-y: -12px;
            --drift-duration: 18s;
        }

        .asset--key {
            bottom: 6%;
            left: 54%;
            width: 66px;
            height: 66px;
            --delay: 2.5s;
            --final-opacity: 0.42;
            --z: -80px;
            --rotate-y: -24deg;
            --rotate-z: 14deg;
            --drift-x: -10px;
            --drift-y: -12px;
            --drift-duration: 17s;
        }

        .asset--building {
            top: 3%;
            right: 2%;
            width: 88px;
            height: 88px;
            --delay: 1.9s;
            --final-opacity: 0.48;
            --z: -140px;
            --rotate-y: 12deg;
            --rotate-z: 4deg;
            --drift-x: -14px;
            --drift-y: 18px;
            --drift-duration: 21s;
        }

        .asset--flask {
            top: 8%;
            right: 14%;
            width: 84px;
            height: 84px;
            --delay: 1.3s;
            --final-opacity: 0.54;
            --z: 60px;
            --rotate-y: -22deg;
            --rotate-z: 8deg;
            --drift-x: -12px;
            --drift-y: 16px;
            --drift-duration: 20s;
        }

        .asset--microscope {
            top: 22%;
            right: 3%;
            width: 86px;
            height: 86px;
            --delay: 2.3s;
            --final-opacity: 0.48;
            --z: -80px;
            --rotate-y: 14deg;
            --rotate-z: -8deg;
            --drift-x: -14px;
            --drift-y: 16px;
            --drift-duration: 19s;
        }

        .asset--testtube {
            top: 36%;
            right: 1%;
            width: 70px;
            height: 70px;
            --delay: 2.9s;
            --final-opacity: 0.42;
            --z: 20px;
            --rotate-y: -20deg;
            --rotate-z: 6deg;
            --drift-x: -10px;
            --drift-y: 14px;
            --drift-duration: 18s;
        }

        .asset--desk {
            top: 48%;
            right: 6%;
            width: 100px;
            height: 100px;
            --delay: 0.7s;
            --final-opacity: 0.44;
            --z: -160px;
            --rotate-y: 10deg;
            --rotate-z: 6deg;
            --drift-x: -16px;
            --drift-y: 20px;
            --drift-duration: 20s;
        }

        .asset--mic {
            top: 62%;
            right: 2%;
            width: 74px;
            height: 74px;
            --delay: 3.1s;
            --final-opacity: 0.42;
            --z: -60px;
            --rotate-y: -16deg;
            --rotate-z: 10deg;
            --drift-x: -12px;
            --drift-y: 14px;
            --drift-duration: 17s;
        }

        .asset--printer {
            bottom: 22%;
            right: 10%;
            width: 96px;
            height: 96px;
            --delay: 1.7s;
            --final-opacity: 0.50;
            --z: -20px;
            --rotate-y: -10deg;
            --rotate-z: -6deg;
            --drift-x: -16px;
            --drift-y: 14px;
            --drift-duration: 20s;
        }

        .asset--wrench {
            bottom: 5%;
            right: 2%;
            width: 80px;
            height: 80px;
            --delay: 2.3s;
            --final-opacity: 0.52;
            --z: 140px;
            --rotate-y: -24deg;
            --rotate-z: 14deg;
            --drift-x: -10px;
            --drift-y: -12px;
            --drift-duration: 17s;
        }

        .asset--screwdriver {
            bottom: 8%;
            right: 20%;
            width: 70px;
            height: 70px;
            --delay: 3.3s;
            --final-opacity: 0.40;
            --z: 100px;
            --rotate-y: 22deg;
            --rotate-z: -10deg;
            --drift-x: -12px;
            --drift-y: -12px;
            --drift-duration: 19s;
        }

        .asset--server {
            bottom: 3%;
            right: 34%;
            width: 78px;
            height: 78px;
            --delay: 3.4s;
            --final-opacity: 0.42;
            --z: -60px;
            --rotate-y: 18deg;
            --rotate-z: 6deg;
            --drift-x: -12px;
            --drift-y: -14px;
            --drift-duration: 21s;
        }

        .asset--drive {
            top: 78%;
            right: 36%;
            width: 66px;
            height: 66px;
            --delay: 2.8s;
            --final-opacity: 0.38;
            --z: -100px;
            --rotate-y: -18deg;
            --rotate-z: 8deg;
            --drift-x: -10px;
            --drift-y: -14px;
            --drift-duration: 18s;
        }

        .asset--compass {
            top: 15%;
            right: 32%;
            width: 70px;
            height: 70px;
            --delay: 3.5s;
            --final-opacity: 0.38;
            --z: -120px;
            --rotate-y: 16deg;
            --rotate-z: -8deg;
            --drift-x: -10px;
            --drift-y: -14px;
            --drift-duration: 20s;
        }

        /* ============================================================
           HERO LAYOUT
        ============================================================ */
        .layout {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            min-height: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            padding: 0 6vw;
            gap: 4vw;
        }

        .brand-side {
            max-width: 560px;
            opacity: 0;
            transform: translateY(20px);
            animation: contentReveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.6s forwards;
        }

        @keyframes contentReveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--stti-blue);
            margin-bottom: 24px;
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(72px, 9vw, 140px);
            line-height: 0.9;
            letter-spacing: -0.04em;
            color: var(--ink);
            margin-bottom: 24px;
            transform: perspective(1200px) rotateX(2deg) rotateY(-3deg);
            transform-origin: left center;
            text-shadow:
                1px 1px 0 rgba(var(--stti-blue-rgb), 0.05),
                2px 2px 0 rgba(var(--stti-blue-rgb), 0.04),
                4px 4px 0 rgba(var(--stti-blue-rgb), 0.03),
                0 12px 40px rgba(var(--stti-blue-rgb), 0.10);
        }

        .brand-title .dot {
            color: var(--stti-blue);
            font-style: normal;
        }

        .brand-subtitle {
            font-size: clamp(14px, 1.1vw, 16px);
            line-height: 1.55;
            color: var(--ink-soft);
            max-width: 420px;
        }

        .form-side {
            display: flex;
            justify-content: center;
            opacity: 0;
            transform: translateY(30px);
            animation: contentReveal 1.2s cubic-bezier(0.16, 1, 0.3, 1) 1s forwards;
        }

        .card {
            width: 100%;
            max-width: 380px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 36px 36px 32px;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.02),
                0 12px 40px rgba(var(--stti-blue-rgb), 0.08),
                0 32px 80px rgba(var(--stti-blue-rgb), 0.06);
        }

        .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .card-logo {
            width: 88px;
            height: 88px;
            margin: 0 auto;
            opacity: 0;
            animation: logoIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 1.5s forwards;
        }

        @keyframes logoIn {
            from {
                opacity: 0;
                transform: scale(0.85);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .card-logo img,
        .card-logo video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .field {
            position: relative;
            margin-bottom: 12px;
            opacity: 0;
            animation: fadeUp 0.5s ease-out forwards;
        }

        .field:nth-of-type(1) {
            animation-delay: 1.75s;
        }

        .field:nth-of-type(2) {
            animation-delay: 1.85s;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            width: 15px;
            height: 15px;
            color: var(--ink-faint);
            transform: translateY(-50%);
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .field input {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 42px;
            font-family: inherit;
            font-size: 13px;
            color: var(--ink);
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 6px;
            outline: none;
            transition: all 0.2s ease;
        }

        .field input::placeholder {
            color: var(--ink-faint);
        }

        .field input:hover {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(0, 0, 0, 0.1);
        }

        .field input:focus {
            background: #ffffff;
            border-color: var(--stti-blue);
            box-shadow: 0 0 0 4px var(--stti-blue-soft);
        }

        .field:focus-within .field-icon {
            color: var(--stti-blue);
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            font-size: 11.5px;
            line-height: 1.5;
        }

        .alert-error {
            background: #fff4f4;
            color: #b42318;
            border: 1px solid #f3c2c2;
        }

        .alert-success {
            background: #f0f7ff;
            color: #005795;
            border: 1px solid #b8d4ed;
        }

        .btn {
            width: 100%;
            height: 46px;
            margin-top: 12px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: var(--stti-blue);
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            opacity: 0;
            animation: fadeUp 0.5s ease-out 1.95s forwards;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn:hover {
            background: var(--stti-blue-dark);
            box-shadow: 0 10px 30px rgba(var(--stti-blue-rgb), 0.3);
            transform: translateY(-1px);
        }

        .btn:hover::before {
            transform: translateX(100%);
        }

        .card-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
            font-size: 10px;
            color: var(--ink-faint);
            letter-spacing: 0.5px;
            opacity: 0;
            animation: fadeUp 0.5s ease-out 2.05s forwards;
        }

        /* ============================================================
           CORNER MARKS
        ============================================================ */
        .corner-mark {
            position: fixed;
            width: 20px;
            height: 20px;
            z-index: 3;
            pointer-events: none;
            opacity: 0;
            animation: fadeIn 1.5s ease-out 2.4s forwards;
        }

        .corner-mark::before,
        .corner-mark::after {
            content: '';
            position: absolute;
            background: var(--stti-blue);
        }

        .corner-mark::before {
            width: 100%;
            height: 1px;
        }

        .corner-mark::after {
            width: 1px;
            height: 100%;
        }

        .corner-mark.tl {
            top: 24px;
            left: 24px;
        }

        .corner-mark.tl::before {
            top: 0;
            left: 0;
        }

        .corner-mark.tl::after {
            top: 0;
            left: 0;
        }

        .corner-mark.tr {
            top: 24px;
            right: 24px;
        }

        .corner-mark.tr::before {
            top: 0;
            right: 0;
        }

        .corner-mark.tr::after {
            top: 0;
            right: 0;
        }

        .corner-mark.bl {
            bottom: 24px;
            left: 90px;
        }

        .corner-mark.bl::before {
            bottom: 0;
            left: 0;
        }

        .corner-mark.bl::after {
            bottom: 0;
            left: 0;
        }

        .corner-mark.br {
            bottom: 24px;
            right: 24px;
        }

        .corner-mark.br::before {
            bottom: 0;
            right: 0;
        }

        .corner-mark.br::after {
            bottom: 0;
            right: 0;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* ============================================================
           AUDIO TOGGLE
        ============================================================ */
        .audio-toggle {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 100;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow:
                0 4px 12px rgba(var(--stti-blue-rgb), 0.12),
                0 12px 32px rgba(var(--stti-blue-rgb), 0.08);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--stti-blue);
            opacity: 0;
            animation: fadeIn 0.8s ease-out 2.5s forwards;
        }

        .audio-toggle:hover {
            transform: scale(1.1);
            box-shadow:
                0 6px 20px rgba(var(--stti-blue-rgb), 0.2),
                0 16px 40px rgba(var(--stti-blue-rgb), 0.12);
        }

        .audio-toggle svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: all 0.2s ease;
        }

        .audio-toggle .icon-on {
            display: block;
        }

        .audio-toggle .icon-off {
            display: none;
        }

        .audio-toggle.muted .icon-on {
            display: none;
        }

        .audio-toggle.muted .icon-off {
            display: block;
        }

        .audio-toggle::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1.5px solid var(--stti-blue);
            opacity: 0;
            animation: audioPulse 2s ease-in-out infinite;
            pointer-events: none;
        }

        .audio-toggle.muted::before {
            animation: none;
            opacity: 0;
        }

        @keyframes audioPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0;
            }

            50% {
                transform: scale(1.15);
                opacity: 0.4;
            }
        }

        /* ============================================================
           RESPONSIVE HERO
        ============================================================ */
        @media (max-width: 1024px) {
            .layout {
                padding: 0 5vw;
                gap: 3vw;
            }

            .brand-title {
                font-size: clamp(56px, 8vw, 96px);
            }
        }

        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
                align-items: center;
                justify-items: center;
                padding: 5vh 6vw;
                gap: 3vh;
            }

            .brand-side {
                width: 100%;
                text-align: center;
                max-width: 100%;
            }

            .brand-eyebrow {
                margin-bottom: 16px;
            }

            .brand-title {
                font-size: clamp(56px, 16vw, 88px);
                margin-bottom: 16px;
                transform: none;
            }

            .brand-subtitle {
                font-size: 13px;
                margin: 0 auto;
            }

            .form-side {
                width: 100%;
                justify-content: center;
            }

            .card {
                max-width: 380px;
                padding: 32px 24px 24px;
            }

            .asset--pliers,
            .asset--screwdriver,
            .asset--testtube,
            .asset--compass,
            .asset--toolbox,
            .asset--speaker,
            .asset--desk,
            .asset--chair,
            .asset--phone,
            .asset--key,
            .asset--book,
            .asset--lamp,
            .asset--chip,
            .asset--printer,
            .asset--wrench,
            .asset--car,
            .asset--monitor,
            .asset--keyboard,
            .asset--microscope,
            .asset--mic,
            .asset--server,
            .asset--hammer {
                display: none;
            }

            .asset--router {
                width: 46px;
                height: 46px;
                top: 2%;
                left: 4%;
            }

            .asset--laptop {
                width: 52px;
                height: 52px;
                top: 2%;
                right: 4%;
                left: auto;
            }

            .asset--flask {
                width: 46px;
                height: 46px;
                top: 10%;
                right: 3%;
            }

            .asset--building {
                width: 50px;
                height: 50px;
                bottom: 4%;
                right: 4%;
                top: auto;
            }

            .asset--bulb {
                width: 38px;
                height: 38px;
                top: 20%;
                left: 42%;
            }

            .audio-toggle {
                bottom: 20px;
                left: 20px;
                width: 40px;
                height: 40px;
            }

            .audio-toggle svg {
                width: 18px;
                height: 18px;
            }

            .corner-mark.bl {
                left: 76px;
            }
        }

        @media (max-width: 420px) {
            .layout {
                padding: 4vh 5vw;
                gap: 2vh;
            }

            .brand-title {
                font-size: 48px;
            }

            .brand-subtitle {
                max-width: 300px;
            }

            .card {
                padding: 28px 20px 22px;
            }

            .card-logo {
                width: 72px;
                height: 72px;
            }

            .field input,
            .btn {
                height: 44px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0s !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .asset,
            .brand-side,
            .form-side,
            .card-logo,
            .field,
            .alert,
            .btn,
            .card-footer,
            .corner-mark,
            .audio-toggle {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        }
    </style>
</head>

<body>

    {{-- ============================================================
    PRELOADER
    ============================================================ --}}
    <div class="preloader" id="preloader">
        <div class="preloader__inner">
            <div class="preloader__logo">
                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M50 10 L60 40 L90 40 L65 60 L75 90 L50 70 L25 90 L35 60 L10 40 L40 40 Z" />
                </svg>
            </div>
            <div class="preloader__text">SINADAS</div>
            <div class="preloader__bar">
                <div class="preloader__bar-fill" id="preloaderBar"></div>
            </div>
        </div>
    </div>

    {{-- ============================================================
    HERO ONLY
    ============================================================ --}}
    <main class="hero-section" id="heroSection">

        <div class="asset-field" aria-hidden="true">
            <div class="asset asset--router"><svg viewBox="0 0 24 24">
                    <rect x="2" y="14" width="20" height="6" rx="1.5" />
                    <line x1="6" y1="17" x2="6" y2="17.01" />
                    <line x1="9" y1="17" x2="9" y2="17.01" />
                    <line x1="12" y1="17" x2="12" y2="17.01" />
                    <path d="M8 11a6 6 0 0 1 8 0" />
                    <path d="M10.5 8.5a8.5 8.5 0 0 1 3 0" />
                </svg></div>
            <div class="asset asset--laptop"><svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="12" rx="1.5" />
                    <path d="M2 20h20l-2-3.5H4z" />
                </svg></div>
            <div class="asset asset--monitor"><svg viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="1.5" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg></div>
            <div class="asset asset--keyboard"><svg viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="11" rx="1.5" />
                    <line x1="6" y1="11" x2="6" y2="11.01" />
                    <line x1="10" y1="11" x2="10" y2="11.01" />
                    <line x1="14" y1="11" x2="14" y2="11.01" />
                    <line x1="18" y1="11" x2="18" y2="11.01" />
                    <line x1="7" y1="14.5" x2="17" y2="14.5" />
                </svg></div>
            <div class="asset asset--book"><svg viewBox="0 0 24 24">
                    <path d="M4 5a2 2 0 0 1 2-2h11v18H6a2 2 0 0 1-2-2z" />
                    <path d="M9 7h5M9 11h5" />
                </svg></div>
            <div class="asset asset--chip"><svg viewBox="0 0 24 24">
                    <rect x="6" y="6" width="12" height="12" rx="1" />
                    <rect x="9.5" y="9.5" width="5" height="5" />
                    <path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4" />
                </svg></div>
            <div class="asset asset--toolbox"><svg viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="14" rx="1.5" />
                    <path d="M8 7V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v3" />
                    <line x1="2" y1="12" x2="22" y2="12" />
                    <line x1="11" y1="12" x2="13" y2="12" />
                </svg></div>
            <div class="asset asset--car"><svg viewBox="0 0 24 24">
                    <path d="M5 11l2-5h10l2 5" />
                    <rect x="3" y="11" width="18" height="6" rx="1" />
                    <circle cx="7" cy="18" r="1.5" />
                    <circle cx="17" cy="18" r="1.5" />
                    <path d="M6 14h2M16 14h2" />
                </svg></div>
            <div class="asset asset--chair"><svg viewBox="0 0 24 24">
                    <path d="M6 4v10" />
                    <path d="M6 14h10" />
                    <path d="M16 4v14" />
                    <path d="M10 20h6" />
                </svg></div>
            <div class="asset asset--speaker"><svg viewBox="0 0 24 24">
                    <rect x="5" y="2" width="14" height="20" rx="2" />
                    <circle cx="12" cy="8" r="2" />
                    <circle cx="12" cy="16" r="3.5" />
                </svg></div>
            <div class="asset asset--mouse"><svg viewBox="0 0 24 24">
                    <rect x="6" y="3" width="12" height="18" rx="6" />
                    <line x1="12" y1="3" x2="12" y2="10" />
                </svg></div>
            <div class="asset asset--pliers"><svg viewBox="0 0 24 24">
                    <circle cx="8" cy="6" r="3" />
                    <circle cx="16" cy="6" r="3" />
                    <path d="M9.5 8l5 10" />
                    <path d="M14.5 8l-5 10" />
                </svg></div>
            <div class="asset asset--phone"><svg viewBox="0 0 24 24">
                    <rect x="6" y="2" width="12" height="20" rx="2" />
                    <path d="M12 18h.01" />
                </svg></div>
            <div class="asset asset--lamp"><svg viewBox="0 0 24 24">
                    <path d="M9 3h6l2 8H7z" />
                    <path d="M12 11v5" />
                    <ellipse cx="12" cy="19" rx="4" ry="2" />
                </svg></div>
            <div class="asset asset--bulb"><svg viewBox="0 0 24 24">
                    <path d="M9 18a6 6 0 1 1 6 0v2H9z" />
                    <line x1="9" y1="22" x2="15" y2="22" />
                </svg></div>
            <div class="asset asset--hammer"><svg viewBox="0 0 24 24">
                    <path d="M14 4l6 6-3 3-6-6z" />
                    <line x1="11" y1="7" x2="3" y2="15" />
                    <line x1="9" y1="17" x2="5" y2="13" />
                </svg></div>
            <div class="asset asset--key"><svg viewBox="0 0 24 24">
                    <circle cx="8" cy="12" r="4" />
                    <path d="M12 12h10" />
                    <path d="M18 12v4M21 12v3" />
                </svg></div>
            <div class="asset asset--building"><svg viewBox="0 0 24 24">
                    <rect x="4" y="3" width="16" height="18" rx="0.5" />
                    <line x1="8" y1="7" x2="8" y2="7.01" />
                    <line x1="12" y1="7" x2="12" y2="7.01" />
                    <line x1="16" y1="7" x2="16" y2="7.01" />
                    <line x1="8" y1="11" x2="8" y2="11.01" />
                    <line x1="12" y1="11" x2="12" y2="11.01" />
                    <line x1="16" y1="11" x2="16" y2="11.01" />
                    <line x1="8" y1="15" x2="8" y2="15.01" />
                    <line x1="12" y1="15" x2="12" y2="15.01" />
                    <line x1="16" y1="15" x2="16" y2="15.01" />
                    <path d="M10 21v-3h4v3" />
                </svg></div>
            <div class="asset asset--flask"><svg viewBox="0 0 24 24">
                    <path d="M9 3h6" />
                    <path d="M10 3v6L4 19a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-6-10V3" />
                    <line x1="7" y1="15" x2="17" y2="15" />
                </svg></div>
            <div class="asset asset--microscope"><svg viewBox="0 0 24 24">
                    <path d="M6 18h12" />
                    <path d="M9 14l3-3" />
                    <path d="M12 11l3-3" />
                    <circle cx="15" cy="6" r="2" />
                    <path d="M4 22h16" />
                    <line x1="11" y1="18" x2="13" y2="14" />
                </svg></div>
            <div class="asset asset--testtube"><svg viewBox="0 0 24 24">
                    <path d="M9 2v18a3 3 0 0 0 6 0V2" />
                    <line x1="9" y1="8" x2="15" y2="8" />
                    <line x1="9" y1="14" x2="15" y2="14" />
                </svg></div>
            <div class="asset asset--desk"><svg viewBox="0 0 24 24">
                    <path d="M3 10h18" />
                    <path d="M4 10v10M20 10v10" />
                    <path d="M8 10v4M16 10v4" />
                </svg></div>
            <div class="asset asset--mic"><svg viewBox="0 0 24 24">
                    <rect x="9" y="2" width="6" height="12" rx="3" />
                    <path d="M5 10a7 7 0 0 0 14 0" />
                    <line x1="12" y1="17" x2="12" y2="22" />
                    <line x1="8" y1="22" x2="16" y2="22" />
                </svg></div>
            <div class="asset asset--printer"><svg viewBox="0 0 24 24">
                    <rect x="5" y="3" width="14" height="6" rx="1" />
                    <rect x="3" y="9" width="18" height="8" rx="1" />
                    <path d="M7 17v4h10v-4" />
                </svg></div>
            <div class="asset asset--wrench"><svg viewBox="0 0 24 24">
                    <path d="M14.7 6.3a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-2.8-2.8l9-9" />
                    <path d="M13.5 5.5l5 5" />
                </svg></div>
            <div class="asset asset--screwdriver"><svg viewBox="0 0 24 24">
                    <rect x="9" y="2" width="6" height="3" rx="0.5" />
                    <path d="M11 5v9l1 8 1-8V5z" />
                </svg></div>
            <div class="asset asset--server"><svg viewBox="0 0 24 24">
                    <rect x="4" y="3" width="16" height="6" rx="1" />
                    <rect x="4" y="10" width="16" height="6" rx="1" />
                    <rect x="4" y="17" width="16" height="4" rx="1" />
                    <circle cx="18" cy="6" r="0.5" fill="currentColor" />
                    <circle cx="18" cy="13" r="0.5" fill="currentColor" />
                </svg></div>
            <div class="asset asset--drive"><svg viewBox="0 0 24 24">
                    <rect x="4" y="10" width="12" height="6" rx="1" />
                    <path d="M16 12v2h4v-4h-4v2z" />
                    <line x1="8" y1="13" x2="8" y2="13.01" />
                </svg></div>
            <div class="asset asset--compass"><svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polygon points="12,4 14,12 12,20 10,12" />
                    <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                </svg></div>
        </div>

        <div class="layout">
            <section class="brand-side">
                <div class="brand-eyebrow">STTI Cirebon</div>
                <h1 class="brand-title">SINADAS<span class="dot">.</span></h1>
                <p class="brand-subtitle">Sistem Informasi Inventaris dan Administrasi Aset</p>
            </section>

            <section class="form-side">
                <div class="card">
                    <div class="card-header">
                        <div class="card-logo">
                            <video autoplay loop muted playsinline preload="auto"
                                poster="{{ asset('assets/logo-stti.png') }}" aria-label="Logo STTI">
                                <source src="{{ asset('assets/logo-stti-animated.webm') }}" type="video/webm">
                                <source src="{{ asset('assets/logo-stti-putar-kiri-kanan-transparan.mov') }}"
                                    type="video/quicktime">
                            </video>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-error">{{ $errors->first() }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="field">
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <input type="text" id="username" name="username" placeholder="Username"
                                value="{{ old('username') }}" required autofocus autocomplete="username">
                        </div>

                        <div class="field">
                            <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Password" required
                                autocomplete="current-password">
                        </div>

                        <button type="submit" class="btn">Sign In</button>
                    </form>

                    <div class="card-footer">© {{ date('Y') }} STTI Cirebon</div>
                </div>
            </section>
        </div>
    </main>

    {{-- Audio Toggle --}}
    <button class="audio-toggle" id="audioToggle" type="button" aria-label="Toggle Audio">
        <svg class="icon-on" viewBox="0 0 24 24">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor" stroke="none" />
            <path d="M15.54 8.46a5 5 0 0 1 0 7.07" />
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14" />
        </svg>
        <svg class="icon-off" viewBox="0 0 24 24">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5" fill="currentColor" stroke="none" />
            <line x1="23" y1="9" x2="17" y2="15" />
            <line x1="17" y1="9" x2="23" y2="15" />
        </svg>
    </button>

    {{-- Corner Marks --}}
    <div class="corner-mark tl"></div>
    <div class="corner-mark tr"></div>
    <div class="corner-mark bl"></div>
    <div class="corner-mark br"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Preloader tetap dipertahankan, tetapi semua logic scroll/section dihapus.
            const preloader = document.getElementById('preloader');
            const preloaderBar = document.getElementById('preloaderBar');

            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 18;

                if (progress >= 100) {
                    progress = 100;
                    clearInterval(progressInterval);
                    setTimeout(() => {
                        preloader.classList.add('hidden');
                    }, 250);
                }

                preloaderBar.style.width = progress + '%';
            }, 140);

            setTimeout(() => {
                clearInterval(progressInterval);
                preloaderBar.style.width = '100%';
                preloader.classList.add('hidden');
            }, 2600);

            // Audio toggle ringan untuk mempertahankan interaksi tombol tanpa engine scroll.
            const audioToggle = document.getElementById('audioToggle');
            let soundEnabled = true;
            let audioCtx = null;

            function initAudio() {
                if (!audioCtx) {
                    try {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    } catch (e) {
                        audioCtx = null;
                    }
                }

                if (audioCtx && audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }

            function playTone(frequency, duration, type = 'sine', volume = 0.025) {
                if (!soundEnabled || !audioCtx) return;

                try {
                    const now = audioCtx.currentTime;
                    const oscillator = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    oscillator.type = type;
                    oscillator.frequency.setValueAtTime(frequency, now);

                    gain.gain.setValueAtTime(0, now);
                    gain.gain.linearRampToValueAtTime(volume, now + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + duration);

                    oscillator.connect(gain);
                    gain.connect(audioCtx.destination);
                    oscillator.start(now);
                    oscillator.stop(now + duration + 0.03);
                } catch (e) {
                    // Audio bersifat opsional.
                }
            }

            audioToggle.addEventListener('click', function () {
                initAudio();
                soundEnabled = !soundEnabled;
                audioToggle.classList.toggle('muted', !soundEnabled);

                if (soundEnabled) {
                    playTone(800, 0.08, 'sine', 0.025);
                    setTimeout(() => playTone(1200, 0.09, 'sine', 0.02), 70);
                } else {
                    playTone(400, 0.09, 'sine', 0.02);
                }
            });

            document.addEventListener('click', initAudio, { once: true });
            document.addEventListener('keydown', initAudio, { once: true });
            document.addEventListener('touchstart', initAudio, { once: true, passive: true });
        });
    </script>

</body>

</html>
