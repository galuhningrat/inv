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

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* ============================================================
           POLISH 1 — PRELOADER
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
           SCROLL CONTAINER
        ============================================================ */
        .scroll-container {
            width: 100%;
            position: relative;
        }

        /* ============================================================
           HERO SECTION
        ============================================================ */
        .hero-section {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
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
            --delay: 3.0s;
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
            --delay: 2.0s;
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
           LAYOUT HERO
        ============================================================ */
        .layout {
            position: relative;
            z-index: 2;
            width: 100%;
            min-height: 100vh;
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
           SCROLL HINT
        ============================================================ */
        .scroll-hint {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--stti-blue);
            opacity: 0;
            animation: fadeUp 0.8s ease-out 2.5s forwards;
        }

        .scroll-hint__arrow {
            width: 20px;
            height: 30px;
            border: 1.5px solid var(--stti-blue);
            border-radius: 12px;
            position: relative;
        }

        .scroll-hint__arrow::after {
            content: '';
            position: absolute;
            top: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 3px;
            height: 6px;
            background: var(--stti-blue);
            border-radius: 2px;
            animation: scrollDot 1.6s ease-in-out infinite;
        }

        @keyframes scrollDot {

            0%,
            100% {
                transform: translate(-50%, 0);
                opacity: 0;
            }

            50% {
                transform: translate(-50%, 8px);
                opacity: 1;
            }
        }

        /* ============================================================
           POLISH 2 — ZOOM TRANSITION SECTION
        ============================================================ */
        .zoom-section {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(180deg, var(--paper) 0%, #eef2f7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .zoom-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
            transform-style: preserve-3d;
        }

        .zoom-text {
            position: relative;
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--stti-blue);
            opacity: 0.8;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            animation: zoomTextFade 1.5s ease-out 0.3s both;
        }

        @keyframes zoomTextFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 0.8;
                transform: translateY(0);
            }
        }

        .zoom-text__highlight {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 900;
            font-size: clamp(48px, 8vw, 96px);
            letter-spacing: -0.02em;
            text-transform: none;
            color: var(--ink);
            line-height: 1;
        }

        /* ============================================================
           CUTAWAY SECTION
        ============================================================ */
        .cutaway-section {
            position: relative;
            width: 100%;
            min-height: 100vh;
            padding: 10vh 6vw 8vh;
            background: linear-gradient(180deg, var(--paper) 0%, #eef2f7 100%);
            overflow: hidden;
        }

        .cutaway-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(var(--stti-blue-rgb), 0.05) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(var(--stti-blue-rgb), 0.04) 0%, transparent 45%);
            pointer-events: none;
        }

        .cutaway-section>* {
            position: relative;
            z-index: 1;
        }

        .cutaway-header {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 6vh;
            opacity: 0;
            transform: translateY(30px);
        }

        .cutaway-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--stti-blue);
            margin-bottom: 16px;
        }

        .cutaway-title {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-style: italic;
            font-size: clamp(32px, 5vw, 56px);
            line-height: 1.05;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin-bottom: 12px;
            text-wrap: balance;
        }

        .cutaway-title .dot {
            color: var(--stti-blue);
            font-style: normal;
        }

        .cutaway-desc {
            font-size: 14px;
            color: var(--ink-soft);
            line-height: 1.6;
        }

        /* ============================================================
           BUILDING CUTAWAY
        ============================================================ */
        .building-cutaway {
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.02),
                0 20px 60px rgba(var(--stti-blue-rgb), 0.08);
        }

        .floor {
            position: relative;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(247, 248, 250, 0.9) 100%);
            border: 1px solid rgba(var(--stti-blue-rgb), 0.08);
            border-radius: 12px;
            padding: 24px 20px 20px;
            opacity: 0;
            transform: translateX(-40px);
        }

        .floor-label {
            position: absolute;
            top: 8px;
            left: 16px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--stti-blue);
            opacity: 0.7;
        }

        .floor-rooms {
            display: grid;
            gap: 16px;
        }

        /* POLISH 3 — Floor 2 Balance: 2 kolom */
        .floor-rooms--pimpinan {
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .floor--1 .floor-rooms {
            grid-template-columns: repeat(4, 1fr);
        }

        /* ============================================================
           ROOM (Karakter)
        ============================================================ */
        .room {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 20px 12px 14px;
            background: linear-gradient(180deg, rgba(var(--stti-blue-rgb), 0.02) 0%, rgba(var(--stti-blue-rgb), 0.05) 100%);
            border: 1px dashed rgba(var(--stti-blue-rgb), 0.15);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            min-height: 180px;
        }

        .room::after {
            content: '';
            position: absolute;
            bottom: 38px;
            left: 12%;
            right: 12%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(var(--stti-blue-rgb), 0.25), transparent);
            pointer-events: none;
        }

        .room::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 100%, rgba(var(--stti-blue-rgb), 0.08) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .room:hover {
            border-color: var(--stti-blue);
            background: rgba(var(--stti-blue-rgb), 0.04);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(var(--stti-blue-rgb), 0.12);
        }

        .room:hover::before {
            opacity: 1;
        }

        .room.active {
            border-style: solid;
            border-color: var(--stti-blue);
            background: rgba(var(--stti-blue-rgb), 0.06);
        }

        .room svg {
            width: 100%;
            max-width: 160px;
            height: auto;
            color: var(--stti-blue);
            filter: drop-shadow(0 8px 16px rgba(var(--stti-blue-rgb), 0.10));
            position: relative;
            z-index: 1;
        }

        .room-label {
            margin-top: 12px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--stti-blue);
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        /* ============================================================
   FLOOR LAYOUTS — 3 & Basement
============================================================ */
        /* Basement — tetap 3 kolom (isinya 3 karakter) */
        .floor--basement .floor-rooms {
            grid-template-columns: repeat(3, 1fr);
        }

        /* Lantai 3 — Pimpinan, hanya 2 karakter, dibatasi lebarnya */
        .floor--3 .floor-rooms {
            grid-template-columns: 1fr 1fr;
            max-width: 700px;
            margin: 0 auto;
        }

        .floor--2 .floor-rooms {
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        /* ============================================================
   FEEDBACK EFEK — Karakter Baru
============================================================ */

        /* Kaprodi — Stamp DISETUJUI */
        .stamp-effect {
            opacity: 0;
            transform: scale(2) rotate(-15deg);
            transform-origin: center;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .room[data-role="kaprodi"].active .stamp-effect {
            opacity: 1;
            transform: scale(1) rotate(-8deg);
        }

        /* Kalab — Flashlight beam */
        .light-beam {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .room[data-role="kalab"].active .light-beam {
            opacity: 1;
            animation: beamPulse 1.2s ease-in-out infinite;
        }

        @keyframes beamPulse {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        /* Aslab — Item baru muncul di rak */
        .item-fill {
            opacity: 0;
            transform: scale(0.5);
            transform-origin: center;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .room[data-role="aslab"].active .item-fill {
            opacity: 1;
            transform: scale(1);
        }

        /* Dosen — Whiteboard writing appears */
        .wb-write {
            stroke-dasharray: 100;
            stroke-dashoffset: 0;
        }

        .room[data-role="dosen"].active .wb-write {
            animation: writeLine 0.5s ease-out;
        }

        .room[data-role="dosen"].active .wb-write--1 {
            animation-delay: 0.05s;
        }

        .room[data-role="dosen"].active .wb-write--2 {
            animation-delay: 0.15s;
        }

        .room[data-role="dosen"].active .wb-write--3 {
            animation-delay: 0.25s;
        }

        .room[data-role="dosen"].active .wb-write--4 {
            animation-delay: 0.35s;
        }

        .room[data-role="dosen"].active .wb-write--5 {
            animation-delay: 0.45s;
        }

        @keyframes writeLine {
            0% {
                stroke-dashoffset: 100;
                opacity: 0.2;
            }

            100% {
                stroke-dashoffset: 0;
                opacity: 0.6;
            }
        }

        /* Mahasiswa — Buku halaman flip */
        .book-page {
            transition: transform 0.3s ease;
            transform-origin: center;
        }

        .room[data-role="mahasiswa"].active .book-page {
            animation: pageFlip 0.6s ease-out;
        }

        @keyframes pageFlip {
            0% {
                transform: scaleX(1);
            }

            50% {
                transform: scaleX(0);
            }

            100% {
                transform: scaleX(1);
            }
        }

        /* Pemeliharaan — Sparks & status change */
        .sparks {
            opacity: 0;
        }

        .room[data-role="pemeliharaan"].active .sparks {
            opacity: 1;
            animation: sparkFlash 0.8s ease-out 3;
        }

        @keyframes sparkFlash {

            0%,
            100% {
                opacity: 0;
            }

            20%,
            60% {
                opacity: 1;
            }
        }

        .status-broken {
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .room[data-role="pemeliharaan"].active .status-broken {
            opacity: 0;
        }

        .status-fixed {
            opacity: 0;
            transition: opacity 0.3s ease 0.8s;
        }

        .room[data-role="pemeliharaan"].active .status-fixed {
            opacity: 1;
        }

        /* Administrasi — Folder terbang masuk */
        .folder-1,
        .folder-2 {
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .room[data-role="administrasi"].active .folder-1 {
            animation: folderFly1 0.8s ease-out;
        }

        .room[data-role="administrasi"].active .folder-2 {
            animation: folderFly2 0.8s ease-out 0.15s;
        }

        @keyframes folderFly1 {
            0% {
                transform: translate(0, 0);
                opacity: 0;
            }

            30% {
                opacity: 1;
            }

            100% {
                transform: translate(30px, 30px);
                opacity: 0;
            }
        }

        @keyframes folderFly2 {
            0% {
                transform: translate(0, 0);
                opacity: 0;
            }

            30% {
                opacity: 1;
            }

            100% {
                transform: translate(30px, 50px);
                opacity: 0;
            }
        }

        /* Karyawan — Steam kopi naik */
        .steam {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .room[data-role="karyawan"].active .steam {
            opacity: 1;
            animation: steamRise 1.5s ease-out infinite;
        }

        @keyframes steamRise {
            0% {
                transform: translateY(0);
                opacity: 0.6;
            }

            100% {
                transform: translateY(-10px);
                opacity: 0;
            }
        }

        /* ============================================================
   FLOOR BASEMENT — Beri latar lebih gelap
============================================================ */
        .floor--basement {
            background: linear-gradient(180deg, rgba(0, 103, 177, 0.05) 0%, rgba(0, 103, 177, 0.10) 100%);
            border-color: rgba(var(--stti-blue-rgb), 0.12);
        }

        .floor--basement .floor-label {
            opacity: 0.9;
        }

        /* ============================================================
   RESPONSIVE — Update untuk 4 lantai
============================================================ */
        @media (max-width: 1024px) {
            .floor--2 .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }

            .floor-rooms--3 {
                grid-template-columns: repeat(2, 1fr);
            }

            .floor--basement .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .floor--2 .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }

            .floor-rooms--3 {
                grid-template-columns: 1fr 1fr;
            }

            .floor--3 .floor-rooms,
            .floor--basement .floor-rooms {
                grid-template-columns: 1fr 1fr;
            }

            .floor--1 .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 420px) {
            .floor-rooms--3 {
                grid-template-columns: 1fr;
            }

            .floor--3 .floor-rooms,
            .floor--2 .floor-rooms,
            .floor--1 .floor-rooms,
            .floor--basement .floor-rooms {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
        }

        /* ============================================================
           ROOM ORNAMENT
        ============================================================ */
        .room-ornament {
            position: absolute;
            color: var(--stti-blue);
            opacity: 0.18;
            pointer-events: none;
            z-index: 0;
            transition: opacity 0.3s ease;
        }

        .room:hover .room-ornament {
            opacity: 0.35;
        }

        /* ============================================================
           KARAKTER ANIMASI
        ============================================================ */
        .char-body {
            animation: charBreath 3s ease-in-out infinite;
            transform-origin: center bottom;
        }

        @keyframes charBreath {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-1.5px);
            }
        }

        .arm-typing-1 {
            animation: typing 1s ease-in-out infinite;
            transform-origin: top center;
        }

        .arm-typing-2 {
            animation: typing 1s ease-in-out infinite 0.5s;
            transform-origin: top center;
        }

        @keyframes typing {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-1.2px);
            }
        }

        .arm-point {
            animation: pointing 3s ease-in-out infinite;
            transform-origin: top left;
        }

        @keyframes pointing {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(-3deg);
            }
        }

        .arm-calc {
            animation: pressing 1.4s ease-in-out infinite;
            transform-origin: top center;
        }

        @keyframes pressing {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(1.5px);
            }
        }

        .arm-sway {
            animation: sway 4s ease-in-out infinite;
            transform-origin: top left;
        }

        @keyframes sway {

            0%,
            100% {
                transform: rotate(0deg);
            }

            50% {
                transform: rotate(1.5deg);
            }
        }

        .monitor-glow {
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.7;
            }
        }

        /* ============================================================
           POLISH 4 — FEEDBACK VISUAL PER KARAKTER
        ============================================================ */

        /* Admin — monitor flash */
        .monitor-flash {
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        .room[data-role="admin"].active .monitor-flash {
            animation: monitorFlash 0.6s ease-out;
        }

        @keyframes monitorFlash {
            0% {
                opacity: 0;
            }

            30% {
                opacity: 0.7;
            }

            100% {
                opacity: 0;
            }
        }

        /* Sarpras — QR muncul di laptop */
        .qr-code-effect {
            opacity: 0;
            transform: scale(0.5);
            transform-origin: center;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .room[data-role="sarpras"].active .qr-code-effect {
            opacity: 1;
            transform: scale(1);
        }

        /* PJ Pengadaan — paket terbuka */
        .box-opened {
            opacity: 0;
            transform: translateY(-8px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .room[data-role="pengadaan"].active .box-opened {
            opacity: 1;
            transform: translateY(0);
        }

        /* Keuangan — koin melayang */
        .coin-float {
            opacity: 0;
            transform: translateY(0);
        }

        .room[data-role="keuangan"].active .coin-float {
            animation: coinFloat 1.4s ease-out;
        }

        .room[data-role="keuangan"].active .coin-float:nth-child(2) {
            animation-delay: 0.15s;
        }

        .room[data-role="keuangan"].active .coin-float:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes coinFloat {
            0% {
                opacity: 0;
                transform: translateY(0);
            }

            30% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateY(-50px);
            }
        }

        /* Rektor — bar chart grow */
        .chart-bar {
            transform-origin: bottom center;
        }

        .room[data-role="rektor"].active .chart-bar--1 {
            animation: growBar 0.6s ease-out 0.05s;
        }

        .room[data-role="rektor"].active .chart-bar--2 {
            animation: growBar 0.6s ease-out 0.10s;
        }

        .room[data-role="rektor"].active .chart-bar--3 {
            animation: growBar 0.6s ease-out 0.15s;
        }

        .room[data-role="rektor"].active .chart-bar--4 {
            animation: growBar 0.6s ease-out 0.20s;
        }

        .room[data-role="rektor"].active .chart-bar--5 {
            animation: growBar 0.6s ease-out 0.25s;
        }

        .room[data-role="rektor"].active .chart-bar--6 {
            animation: growBar 0.6s ease-out 0.30s;
        }

        @keyframes growBar {
            0% {
                transform: scaleY(0.3);
            }

            50% {
                transform: scaleY(1.4);
            }

            100% {
                transform: scaleY(1);
            }
        }

        /* ============================================================
           CHARACTER PANEL (Modal)
        ============================================================ */
        .character-panel {
            position: fixed;
            bottom: 30px;
            right: 30px;
            left: auto;
            transform: translateY(calc(100% + 30px));
            width: 100%;
            max-width: 380px;
            margin: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            padding: 28px 32px 24px;
            box-shadow:
                0 20px 60px rgba(var(--stti-blue-rgb), 0.15),
                0 8px 20px rgba(0, 0, 0, 0.06);
            z-index: 100;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .character-panel.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .character-panel__close {
            position: absolute;
            top: 12px;
            right: 16px;
            width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            color: var(--ink-faint);
            font-size: 20px;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .character-panel__close:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--ink);
        }

        .character-panel__role {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--stti-blue);
            background: var(--stti-blue-soft);
            padding: 4px 10px;
            border-radius: 999px;
            margin-bottom: 12px;
        }

        .character-panel__title {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 22px;
            color: var(--ink);
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .character-panel__desc {
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--ink-soft);
        }

        @media (max-width: 768px) {
            .character-panel {
                right: 12px;
                left: 12px;
                bottom: 12px;
                max-width: none;
                width: auto;
                transform: translateY(calc(100% + 12px));
            }

            .character-panel.active {
                transform: translateY(0);
            }
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

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1024px) {
            .layout {
                padding: 0 5vw;
                gap: 3vw;
            }

            .brand-title {
                font-size: clamp(56px, 8vw, 96px);
            }

            .floor--1 .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }

            .floor-rooms--pimpinan {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
                align-items: flex-start;
                padding: 8vh 6vw 6vh;
                gap: 4vh;
            }

            .brand-side {
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
                justify-content: center;
            }

            .card {
                padding: 32px 24px 24px;
            }

            /* Hide sebagian aset */
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

            .scroll-hint {
                bottom: 20px;
                font-size: 9px;
            }

            .zoom-text__highlight {
                font-size: 18px;
            }

            .cutaway-section {
                padding: 8vh 4vw 6vh;
            }

            .cutaway-title {
                font-size: clamp(26px, 8vw, 38px);
                line-height: 1.1;
                text-wrap: balance;
            }

            .building-cutaway {
                padding: 14px;
                gap: 3px;
            }

            .floor {
                padding: 20px 14px 14px;
            }

            .floor--1 .floor-rooms {
                grid-template-columns: repeat(2, 1fr);
            }

            .floor-rooms--pimpinan {
                grid-template-columns: 1fr;
            }

            .room {
                min-height: 150px;
                padding: 16px 8px 12px;
            }

            .room::after {
                bottom: 32px;
            }

            .room svg {
                max-width: 110px;
            }

            .room-label {
                font-size: 9px;
            }

            .character-panel {
                margin: 0 12px 12px;
                padding: 24px 22px 20px;
            }
        }

        @media (max-width: 420px) {
            .brand-title {
                font-size: 48px;
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

            .floor--1 .floor-rooms {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .room {
                padding: 12px 6px 10px;
            }

            .room svg {
                max-width: 75px;
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
            .corner-mark {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
        }
    </style>
</head>

<body>

    {{-- ============================================================
         POLISH 1 — PRELOADER
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

    <div class="scroll-container">

        {{-- ============================================================
             SECTION 1 — HERO
        ============================================================ --}}
        <section class="hero-section" id="heroSection">

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

            <main class="layout">
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
                                <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <input type="text" id="username" name="username" placeholder="Username"
                                    value="{{ old('username') }}" required autofocus autocomplete="username">
                            </div>
                            <div class="field">
                                <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"
                                        ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <input type="password" id="password" name="password" placeholder="Password"
                                    required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn">Sign In</button>
                        </form>

                        <div class="card-footer">© {{ date('Y') }} STTI Cirebon</div>
                    </div>
                </section>
            </main>

            <div class="scroll-hint">
                <div class="scroll-hint__arrow"></div>
                <span>Scroll</span>
            </div>
        </section>

        {{-- ============================================================
             SECTION 1.5 — POLISH 2: ZOOM TRANSITION
        ============================================================ --}}
        <section class="zoom-section" id="zoomSection">
            <div class="zoom-canvas">
                <div class="zoom-text">
                    <span>Masuk ke dalam</span>
                    <span class="zoom-text__highlight">SINADAS</span>
                </div>
            </div>
        </section>

        {{-- ============================================================
             SECTION 2 — CUTAWAY DIORAMA
        ============================================================ --}}
        {{-- ============================================================
     SECTION 2 — CUTAWAY DIORAMA (13 Karakter)
============================================================ --}}
        <section class="cutaway-section" id="cutawaySection">

            <div class="cutaway-header">
                <span class="cutaway-eyebrow">Tim SINADAS</span>
                <h2 class="cutaway-title">Orang-orang di balik <span style="white-space:nowrap">sistem<span
                            class="dot">.</span></span></h2>
                <p class="cutaway-desc">Klik setiap karakter untuk melihat perannya.</p>
            </div>

            <div class="building-cutaway">

                {{-- ============================================================
             LANTAI 3 — PIMPINAN
        ============================================================ --}}
                <div class="floor floor--3">
                    <span class="floor-label">Lantai 3 — Pimpinan</span>
                    <div class="floor-rooms floor-rooms--3">

                        {{-- REKTOR --}}
                        <div class="room" data-role="rektor">
                            <svg class="room-ornament" style="top:16px;left:20px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 21V4" />
                                <path d="M4 4h12l-3 4 3 4H4" />
                            </svg>
                            <svg class="room-ornament" style="top:16px;right:20px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9" />
                                <polyline points="12 7 12 12 15 14" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="120" y="15" width="65" height="80" rx="3"
                                    fill="rgba(0,103,177,0.04)" />
                                <line x1="128" y1="85" x2="128" y2="70"
                                    class="chart-bar chart-bar--1" />
                                <line x1="136" y1="85" x2="136" y2="55"
                                    class="chart-bar chart-bar--2" />
                                <line x1="144" y1="85" x2="144" y2="40"
                                    class="chart-bar chart-bar--3" />
                                <line x1="152" y1="85" x2="152" y2="60"
                                    class="chart-bar chart-bar--4" />
                                <line x1="160" y1="85" x2="160" y2="45"
                                    class="chart-bar chart-bar--5" />
                                <line x1="168" y1="85" x2="168" y2="70"
                                    class="chart-bar chart-bar--6" />
                                <line x1="122" y1="88" x2="183" y2="88" opacity="0.4" />
                                <line x1="130" y1="30" x2="175" y2="30" opacity="0.4" />
                                <line x1="130" y1="38" x2="160" y2="38" opacity="0.3" />
                                <path d="M55 65 L55 125 L85 125 L85 65 Z" fill="rgba(0,103,177,0.08)" />
                                <circle cx="70" cy="48" r="14" fill="rgba(0,103,177,0.08)" />
                                <path d="M56 43 Q70 32 84 43" fill="none" />
                                <line x1="62" y1="125" x2="62" y2="150" />
                                <line x1="78" y1="125" x2="78" y2="150" />
                                <line x1="58" y1="150" x2="66" y2="150" />
                                <line x1="74" y1="150" x2="82" y2="150" />
                                <line x1="55" y1="80" x2="42" y2="105" />
                                <path class="arm-point" d="M85 80 L105 70 L118 60" fill="none" />
                                <circle cx="120" cy="58" r="2" fill="currentColor" />
                            </svg>
                            <span class="room-label">Rektor</span>
                        </div>

                        {{-- KAPRODI --}}
                        <div class="room" data-role="kaprodi">
                            <svg class="room-ornament" style="top:14px;left:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v13.5" />
                                <line x1="4" y1="19.5" x2="20" y2="19.5" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="9" y1="12" x2="15" y2="12" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Kacamata --}}
                                <circle cx="96" cy="44" r="3" />
                                <circle cx="106" cy="44" r="3" />
                                <line x1="99" y1="44" x2="103" y2="44" />
                                {{-- Meja --}}
                                <line x1="30" y1="100" x2="170" y2="100" />
                                <line x1="35" y1="100" x2="35" y2="150" />
                                <line x1="165" y1="100" x2="165" y2="150" />
                                {{-- Dokumen di meja --}}
                                <rect x="55" y="88" width="30" height="12" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <line x1="58" y1="93" x2="80" y2="93" opacity="0.5" />
                                <line x1="58" y1="97" x2="75" y2="97" opacity="0.4" />
                                {{-- Lengan menandatangani --}}
                                <path class="arm-sign" d="M118 78 L128 85 L135 92" fill="none" />
                                {{-- Stamp (feedback) --}}
                                <g class="stamp-effect">
                                    <rect x="135" y="70" width="34" height="18" rx="2"
                                        fill="rgba(10,93,58,0.9)" stroke="none" />
                                    <text x="152" y="83" text-anchor="middle" font-family="Inter, sans-serif"
                                        font-size="7" font-weight="700" fill="white"
                                        stroke="none">DISETUJUI</text>
                                </g>
                            </svg>
                            <span class="room-label">Kaprodi</span>
                        </div>

                    </div>
                </div>

                {{-- ============================================================
             LANTAI 2 — AKADEMIK
        ============================================================ --}}
                <div class="floor floor--2">
                    <span class="floor-label">Lantai 2 — Akademik</span>
                    <div class="floor-rooms">

                        {{-- KALAB --}}
                        <div class="room" data-role="kalab">
                            <svg class="room-ornament" style="top:14px;right:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 3h6" />
                                <path d="M10 3v6L4 19a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-6-10V3" />
                                <line x1="7" y1="15" x2="17" y2="15" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 L82 105 L118 105 L118 70 Z" fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Jas lab (detail) --}}
                                <line x1="100" y1="55" x2="100" y2="100" opacity="0.4" />
                                {{-- Kaki --}}
                                <line x1="90" y1="105" x2="90" y2="145" />
                                <line x1="110" y1="105" x2="110" y2="145" />
                                <line x1="84" y1="145" x2="96" y2="145" />
                                <line x1="104" y1="145" x2="116" y2="145" />
                                {{-- Meja lab --}}
                                <line x1="40" y1="110" x2="160" y2="110" />
                                <line x1="45" y1="110" x2="45" y2="150" />
                                <line x1="155" y1="110" x2="155" y2="150" />
                                {{-- Alat lab di meja --}}
                                <rect x="55" y="95" width="14" height="15" rx="1" />
                                <path d="M57 95 L57 90 L67 90 L67 95" />
                                <rect x="80" y="98" width="12" height="12" rx="1" />
                                <line x1="135" y1="100" x2="135" y2="110" />
                                <circle cx="135" cy="95" r="5" />
                                {{-- Flashlight beam (feedback) --}}
                                <path class="light-beam" d="M118 78 L145 90 L155 105 L135 100 Z"
                                    fill="rgba(255,200,50,0.3)" stroke="none" />
                                {{-- Lengan memegang flashlight --}}
                                <path class="arm-check" d="M118 78 L130 82 L140 88" fill="none" />
                            </svg>
                            <span class="room-label">Kalab</span>
                        </div>

                        {{-- ASLAB --}}
                        <div class="room" data-role="aslab">
                            <svg class="room-ornament" style="bottom:50px;left:14px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="6" />
                                <rect x="3" y="14" width="18" height="6" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 L82 105 L118 105 L118 70 Z" fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Kaki --}}
                                <line x1="90" y1="105" x2="90" y2="145" />
                                <line x1="110" y1="105" x2="110" y2="145" />
                                <line x1="84" y1="145" x2="96" y2="145" />
                                <line x1="104" y1="145" x2="116" y2="145" />
                                {{-- Rak lab --}}
                                <line x1="130" y1="70" x2="180" y2="70" />
                                <line x1="130" y1="110" x2="180" y2="110" />
                                <line x1="130" y1="70" x2="130" y2="150" />
                                <line x1="180" y1="70" x2="180" y2="150" />
                                {{-- Alat di rak --}}
                                <rect x="138" y="55" width="8" height="15" rx="1" />
                                <rect x="152" y="58" width="10" height="12" rx="1" />
                                <rect x="166" y="55" width="8" height="15" rx="1" />
                                {{-- Item baru muncul (feedback) --}}
                                <rect class="item-fill" x="145" y="95" width="12" height="15" rx="1"
                                    fill="rgba(0,103,177,0.2)" />
                                {{-- Lengan --}}
                                <path class="arm-place" d="M118 78 L130 80 L140 78" fill="none" />
                            </svg>
                            <span class="room-label">Aslab</span>
                        </div>

                        {{-- DOSEN --}}
                        <div class="room" data-role="dosen">
                            <svg class="room-ornament" style="top:14px;left:16px;width:24px;height:24px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="14" rx="1" />
                                <line x1="8" y1="21" x2="16" y2="21" />
                                <line x1="12" y1="18" x2="12" y2="21" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Whiteboard --}}
                                <rect x="110" y="20" width="75" height="60" rx="1"
                                    fill="rgba(0,103,177,0.03)" />
                                <line x1="117" y1="32" x2="160" y2="32" opacity="0.6"
                                    class="wb-write wb-write--1" />
                                <line x1="117" y1="40" x2="175" y2="40" opacity="0.5"
                                    class="wb-write wb-write--2" />
                                <line x1="117" y1="48" x2="150" y2="48" opacity="0.4"
                                    class="wb-write wb-write--3" />
                                <line x1="117" y1="56" x2="170" y2="56" opacity="0.5"
                                    class="wb-write wb-write--4" />
                                <line x1="117" y1="64" x2="140" y2="64" opacity="0.3"
                                    class="wb-write wb-write--5" />
                                {{-- Garis whiteboard stand --}}
                                <line x1="147" y1="80" x2="147" y2="90" />
                                <line x1="135" y1="90" x2="160" y2="90" />

                                {{-- Badan --}}
                                <path d="M55 65 L55 105 L75 105 L75 65 Z" fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="65" cy="48" r="11" fill="rgba(0,103,177,0.08)" />
                                <path d="M54 43 Q65 35 76 43" fill="none" />
                                {{-- Batik accent (garis diagonal) --}}
                                <line x1="56" y1="75" x2="74" y2="75" opacity="0.4" />
                                <line x1="56" y1="82" x2="74" y2="82" opacity="0.3" />
                                {{-- Kaki --}}
                                <line x1="60" y1="105" x2="60" y2="140" />
                                <line x1="70" y1="105" x2="70" y2="140" />
                                <line x1="55" y1="140" x2="65" y2="140" />
                                <line x1="67" y1="140" x2="75" y2="140" />
                                {{-- Lengan menunjuk whiteboard --}}
                                <path class="arm-teach" d="M75 78 L95 78 L108 78" fill="none" />
                                <circle cx="110" cy="78" r="1.5" fill="currentColor" />
                            </svg>
                            <span class="room-label">Dosen</span>
                        </div>

                        {{-- MAHASISWA --}}
                        <div class="room" data-role="mahasiswa">
                            <svg class="room-ornament" style="bottom:50px;right:16px;width:20px;height:20px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 8 L12 4 L22 8 L12 12 Z" />
                                <path d="M6 10 v4 c0 1 2 2 6 2 s6-1 6-2 v-4" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Backpack --}}
                                <path d="M82 75 Q78 85 78 100 L82 100 L82 75 Z" fill="rgba(0,103,177,0.15)"
                                    stroke="none" />
                                {{-- Kaki --}}
                                <line x1="90" y1="100" x2="90" y2="130" />
                                <line x1="110" y1="100" x2="110" y2="130" />
                                <line x1="82" y1="130" x2="96" y2="130" />
                                <line x1="104" y1="130" x2="118" y2="130" />
                                {{-- Bean bag --}}
                                <path d="M60 130 Q55 145 75 150 Q100 155 125 150 Q145 145 140 130 Z"
                                    fill="rgba(0,103,177,0.05)" />
                                {{-- Buku di tangan --}}
                                <rect x="105" y="75" width="20" height="15" rx="1"
                                    fill="rgba(0,103,177,0.06)" />
                                <line class="book-page" x1="115" y1="75" x2="115"
                                    y2="90" />
                                <line x1="108" y1="80" x2="113" y2="80" opacity="0.5" />
                                <line x1="108" y1="84" x2="113" y2="84" opacity="0.4" />
                                <line x1="117" y1="80" x2="122" y2="80" opacity="0.5" />
                                <line x1="117" y1="84" x2="122" y2="84" opacity="0.4" />
                                {{-- Lengan memegang buku --}}
                                <path class="arm-read" d="M118 78 L125 80" fill="none" />
                            </svg>
                            <span class="room-label">Mahasiswa</span>
                        </div>

                    </div>
                </div>

                {{-- ============================================================
             LANTAI 1 — OPERASIONAL
        ============================================================ --}}
                <div class="floor floor--1">
                    <span class="floor-label">Lantai 1 — Operasional</span>
                    <div class="floor-rooms">

                        {{-- ADMIN --}}
                        <div class="room" data-role="admin">
                            <svg class="room-ornament" style="top:14px;left:14px;width:24px;height:24px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="1" />
                                <line x1="3" y1="12" x2="21" y2="12" />
                                <line x1="12" y1="3" x2="12" y2="21" />
                            </svg>
                            <svg class="room-ornament" style="top:16px;right:18px;width:22px;height:22px;"
                                viewBox="0 0 24 24">
                                <circle cx="6" cy="12" r="2" fill="currentColor" opacity="0.9" />
                                <circle cx="12" cy="12" r="2" fill="currentColor" opacity="0.6" />
                                <circle cx="18" cy="12" r="2" fill="currentColor" opacity="0.3" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="20" y="20" width="45" height="30" rx="1.5"
                                    fill="rgba(0,103,177,0.04)" />
                                <rect x="78" y="20" width="45" height="30" rx="1.5"
                                    fill="rgba(0,103,177,0.04)" />
                                <rect x="135" y="20" width="45" height="30" rx="1.5"
                                    fill="rgba(0,103,177,0.04)" />
                                <rect class="monitor-flash" x="20" y="20" width="160" height="30"
                                    rx="1.5" fill="rgba(50,200,100,0.4)" />
                                <line x1="26" y1="28" x2="58" y2="28" opacity="0.5" />
                                <line x1="26" y1="33" x2="48" y2="33"
                                    opacity="0.4" />
                                <line x1="26" y1="38" x2="58" y2="38"
                                    opacity="0.3" />
                                <line x1="26" y1="43" x2="42" y2="43"
                                    opacity="0.4" />
                                <line x1="84" y1="28" x2="116" y2="28"
                                    opacity="0.5" />
                                <line x1="84" y1="33" x2="106" y2="33"
                                    opacity="0.4" />
                                <line x1="84" y1="38" x2="116" y2="38"
                                    opacity="0.3" />
                                <line x1="141" y1="28" x2="173" y2="28"
                                    opacity="0.5" />
                                <line x1="141" y1="33" x2="163" y2="33"
                                    opacity="0.4" />
                                <line x1="141" y1="38" x2="173" y2="38"
                                    opacity="0.3" />
                                <line x1="100" y1="50" x2="100" y2="58" />
                                <line x1="85" y1="58" x2="115" y2="58" />
                                <circle cx="100" cy="72" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 72 Q88 58 100 58 Q112 58 112 72" fill="none" />
                                <circle cx="86" cy="72" r="2.5" fill="rgba(0,103,177,0.15)" />
                                <circle cx="114" cy="72" r="2.5" fill="rgba(0,103,177,0.15)" />
                                <path d="M88 84 L88 122 L112 122 L112 84 Z" fill="rgba(0,103,177,0.08)" />
                                <line x1="50" y1="122" x2="150" y2="122" />
                                <line x1="55" y1="122" x2="55" y2="150" />
                                <line x1="145" y1="122" x2="145" y2="150" />
                                <rect x="80" y="115" width="40" height="7" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <path class="arm-typing-1" d="M88 92 L82 115" fill="none" />
                                <path class="arm-typing-2" d="M112 92 L118 115" fill="none" />
                            </svg>
                            <span class="room-label">Admin</span>
                        </div>

                        {{-- SARPRAS --}}
                        <div class="room" data-role="sarpras">
                            <svg class="room-ornament" style="top:14px;left:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="16" rx="1" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                                <line x1="8" y1="3" x2="8" y2="7" />
                                <line x1="16" y1="3" x2="16" y2="7" />
                            </svg>
                            <svg class="room-ornament" style="bottom:50px;right:14px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 14 L8 20 L16 20 L16 14" />
                                <path d="M12 14 L12 8" />
                                <path d="M12 8 Q8 8 7 5 Q11 4 12 8" />
                                <path d="M12 8 Q16 8 17 5 Q13 4 12 8" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                <line x1="30" y1="100" x2="170" y2="100" />
                                <line x1="35" y1="100" x2="35" y2="150" />
                                <line x1="165" y1="100" x2="165" y2="150" />
                                <path d="M70 90 L130 90 L138 100 L62 100 Z" fill="rgba(0,103,177,0.04)" />
                                <path d="M74 92 L126 92 L126 99 L74 99 Z" opacity="0.5"
                                    fill="rgba(0,103,177,0.08)" />
                                <g class="qr-code-effect">
                                    <rect x="80" y="55" width="40" height="40" rx="3"
                                        fill="rgba(255,255,255,0.9)" stroke="rgba(0,103,177,0.3)"
                                        stroke-width="1" />
                                    <rect x="85" y="60" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="95" y="60" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="105" y="60" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="85" y="70" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="105" y="70" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="85" y="80" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="95" y="80" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                    <rect x="105" y="80" width="8" height="8" fill="currentColor"
                                        opacity="0.7" stroke="none" />
                                </g>
                                <path class="arm-typing-1" d="M82 78 L78 92 L92 95" fill="none" />
                                <path class="arm-typing-2" d="M118 78 L122 92 L108 95" fill="none" />
                            </svg>
                            <span class="room-label">Sarpras</span>
                        </div>

                        {{-- PJ PENGADAAN --}}
                        <div class="room" data-role="pengadaan">
                            <svg class="room-ornament" style="bottom:50px;left:12px;width:24px;height:24px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="6" width="8" height="8" />
                                <rect x="13" y="6" width="8" height="8" />
                                <line x1="3" y1="18" x2="21" y2="18" />
                            </svg>
                            <svg class="room-ornament" style="top:16px;right:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 4h2l2.5 12h11l2-8H6" />
                                <circle cx="9" cy="20" r="1.5" />
                                <circle cx="17" cy="20" r="1.5" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M82 55 L82 105 L118 105 L118 55 Z" fill="rgba(0,103,177,0.08)" />
                                <circle cx="100" cy="38" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 34 Q100 26 112 34" fill="none" />
                                <path d="M88 65 L88 100 L98 100 L98 65 Z" fill="rgba(255,200,50,0.15)"
                                    stroke="none" />
                                <path d="M102 65 L102 100 L112 100 L112 65 Z" fill="rgba(255,200,50,0.15)"
                                    stroke="none" />
                                <line x1="90" y1="105" x2="90" y2="145" />
                                <line x1="110" y1="105" x2="110" y2="145" />
                                <line x1="84" y1="145" x2="96" y2="145" />
                                <line x1="104" y1="145" x2="116" y2="145" />
                                <path class="arm-sway" d="M118 70 L135 75" fill="none" />
                                <rect x="132" y="65" width="22" height="30" rx="1.5"
                                    fill="rgba(0,103,177,0.05)" />
                                <line x1="137" y1="72" x2="149" y2="72"
                                    opacity="0.5" />
                                <line x1="137" y1="78" x2="149" y2="78"
                                    opacity="0.4" />
                                <line x1="137" y1="84" x2="145" y2="84"
                                    opacity="0.3" />
                                <g class="box-opened">
                                    <rect x="140" y="100" width="18" height="12" rx="1"
                                        fill="rgba(255,255,255,0.9)" stroke="rgba(0,103,177,0.4)"
                                        stroke-width="1" />
                                    <path d="M140 100 L149 94 L158 100" fill="none" stroke-width="1" />
                                    <line x1="145" y1="105" x2="153" y2="105"
                                        opacity="0.6" />
                                    <line x1="145" y1="108" x2="150" y2="108"
                                        opacity="0.5" />
                                </g>
                                <path d="M82 70 L68 90" fill="none" />
                            </svg>
                            <span class="room-label">PJ Pengadaan</span>
                        </div>

                        {{-- KEUANGAN --}}
                        <div class="room" data-role="keuangan">
                            <svg class="room-ornament" style="top:16px;right:18px;width:26px;height:26px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <line x1="6" y1="18" x2="6" y2="12" />
                                <line x1="11" y1="18" x2="11" y2="8" />
                                <line x1="16" y1="18" x2="16" y2="14" />
                                <line x1="3" y1="21" x2="21" y2="21" />
                            </svg>
                            <svg class="room-ornament" style="bottom:50px;left:14px;width:20px;height:20px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="8" />
                                <path
                                    d="M12 8v8M15 9.5c0-1-1.5-1.5-3-1.5s-3 .5-3 1.5 1.5 1.5 3 1.5 3 .5 3 1.5-1.5 1.5-3 1.5-3-.5-3-1.5" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                <path d="M100 55 L96 70 L100 90 L104 70 Z" fill="rgba(0,103,177,0.15)"
                                    stroke="none" />
                                <line x1="30" y1="100" x2="170" y2="100" />
                                <line x1="35" y1="100" x2="35" y2="150" />
                                <line x1="165" y1="100" x2="165" y2="150" />
                                <rect x="125" y="82" width="28" height="18" rx="1.5"
                                    fill="rgba(0,103,177,0.05)" />
                                <line x1="129" y1="87" x2="140" y2="87"
                                    opacity="0.5" />
                                <circle cx="137" cy="93" r="0.8" fill="currentColor" />
                                <circle cx="141" cy="93" r="0.8" fill="currentColor" />
                                <circle cx="145" cy="93" r="0.8" fill="currentColor" />
                                <rect x="48" y="86" width="22" height="8" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <rect x="51" y="80" width="22" height="8" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <rect x="54" y="74" width="22" height="8" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <g class="coin-float">
                                    <circle cx="60" cy="70" r="3" fill="currentColor"
                                        opacity="0.6" />
                                    <circle cx="60" cy="70" r="3" fill="none"
                                        stroke="currentColor" stroke-width="0.8" />
                                </g>
                                <g class="coin-float">
                                    <circle cx="100" cy="70" r="3" fill="currentColor"
                                        opacity="0.6" />
                                    <circle cx="100" cy="70" r="3" fill="none"
                                        stroke="currentColor" stroke-width="0.8" />
                                </g>
                                <g class="coin-float">
                                    <circle cx="140" cy="70" r="3" fill="currentColor"
                                        opacity="0.6" />
                                    <circle cx="140" cy="70" r="3" fill="none"
                                        stroke="currentColor" stroke-width="0.8" />
                                </g>
                                <path class="arm-calc" d="M118 78 L128 88" fill="none" />
                                <path d="M82 78 L72 90" fill="none" />
                            </svg>
                            <span class="room-label">Keuangan</span>
                        </div>

                    </div>
                </div>

                {{-- ============================================================
             BASEMENT — SUPPORT
        ============================================================ --}}
                <div class="floor floor--basement">
                    <span class="floor-label">Basement — Support</span>
                    <div class="floor-rooms floor-rooms--3">

                        {{-- TIM PEMELIHARAAN --}}
                        <div class="room" data-role="pemeliharaan">
                            <svg class="room-ornament" style="bottom:50px;right:16px;width:24px;height:24px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-2.8-2.8l9-9" />
                                <path d="M13.5 5.5l5 5" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 L82 105 L118 105 L118 70 Z" fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala + helm --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M86 38 Q100 28 114 38" fill="none" />
                                <path d="M84 40 Q100 30 116 40" stroke-width="2" opacity="0.5" />
                                {{-- Kaki --}}
                                <line x1="90" y1="105" x2="90" y2="145" />
                                <line x1="110" y1="105" x2="110" y2="145" />
                                <line x1="84" y1="145" x2="96" y2="145" />
                                <line x1="104" y1="145" x2="116" y2="145" />
                                {{-- Mesin rusak (kiri bawah) --}}
                                <rect x="40" y="90" width="30" height="40" rx="2"
                                    fill="rgba(0,103,177,0.05)" />
                                <line x1="46" y1="98" x2="64" y2="98"
                                    opacity="0.5" />
                                <line x1="46" y1="104" x2="64" y2="104"
                                    opacity="0.4" />
                                <line x1="46" y1="110" x2="58" y2="110"
                                    opacity="0.4" />
                                {{-- Gear mesin --}}
                                <circle cx="55" cy="122" r="4" />
                                {{-- Status sebelum rusak --}}
                                <text class="status-broken" x="55" y="88" text-anchor="middle" font-family="Inter"
                                    font-size="8" font-weight="700" fill="#dc2626" stroke="none">!</text>
                                {{-- Status setelah OK --}}
                                <text class="status-fixed" x="55" y="88" text-anchor="middle" font-family="Inter"
                                    font-size="8" font-weight="700" fill="#10b981" stroke="none">✓</text>
                                {{-- Sparks --}}
                                <g class="sparks">
                                    <line x1="70" y1="100" x2="78" y2="92"
                                        stroke="rgba(255,200,50,0.9)" stroke-width="1.5" />
                                    <line x1="70" y1="110" x2="80" y2="105"
                                        stroke="rgba(255,200,50,0.9)" stroke-width="1.5" />
                                    <line x1="70" y1="120" x2="78" y2="128"
                                        stroke="rgba(255,200,50,0.9)" stroke-width="1.5" />
                                </g>
                                {{-- Lengan memperbaiki --}}
                                <path class="arm-repair" d="M82 78 L70 95 L70 108" fill="none" />
                            </svg>
                            <span class="room-label">Pemeliharaan</span>
                        </div>

                        {{-- ADMINISTRASI --}}
                        <div class="room" data-role="administrasi">
                            <svg class="room-ornament" style="top:14px;left:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="4" rx="0.5" />
                                <rect x="3" y="10" width="18" height="4" rx="0.5" />
                                <rect x="3" y="16" width="18" height="4" rx="0.5" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Badan --}}
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Kaki --}}
                                <line x1="90" y1="100" x2="90" y2="145" />
                                <line x1="110" y1="100" x2="110" y2="145" />
                                <line x1="84" y1="145" x2="96" y2="145" />
                                <line x1="104" y1="145" x2="116" y2="145" />
                                {{-- Lemari arsip (kanan) --}}
                                <rect x="130" y="60" width="45" height="90" rx="2"
                                    fill="rgba(0,103,177,0.04)" />
                                <line x1="130" y1="85" x2="175" y2="85"
                                    opacity="0.4" />
                                <line x1="130" y1="110" x2="175" y2="110"
                                    opacity="0.4" />
                                <line x1="130" y1="135" x2="175" y2="135"
                                    opacity="0.4" />
                                <circle cx="170" cy="72" r="1.5" fill="currentColor"
                                    opacity="0.6" />
                                <circle cx="170" cy="98" r="1.5" fill="currentColor"
                                    opacity="0.6" />
                                <circle cx="170" cy="123" r="1.5" fill="currentColor"
                                    opacity="0.6" />
                                {{-- Folder terbang masuk (feedback) --}}
                                <g class="folder-1">
                                    <rect x="105" y="60" width="14" height="10" rx="1"
                                        fill="rgba(0,103,177,0.2)" />
                                    <path d="M105 60 L105 57 L109 57 L110 60" />
                                </g>
                                <g class="folder-2">
                                    <rect x="105" y="55" width="14" height="10" rx="1"
                                        fill="rgba(0,103,177,0.2)" />
                                    <path d="M105 55 L105 52 L109 52 L110 55" />
                                </g>
                                {{-- Lengan --}}
                                <path class="arm-sort" d="M118 78 L128 72" fill="none" />
                            </svg>
                            <span class="room-label">Administrasi</span>
                        </div>

                        {{-- KARYAWAN --}}
                        <div class="room" data-role="karyawan">
                            <svg class="room-ornament" style="top:14px;left:16px;width:22px;height:22px;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="12" rx="1.5" />
                                <path d="M2 20h20l-2-3.5H4z" />
                            </svg>

                            <svg viewBox="0 0 200 160" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                {{-- Desktop di meja --}}
                                <rect x="120" y="55" width="40" height="28" rx="1.5"
                                    fill="rgba(0,103,177,0.04)" />
                                <line x1="126" y1="62" x2="148" y2="62"
                                    opacity="0.5" />
                                <line x1="126" y1="68" x2="154" y2="68"
                                    opacity="0.4" />
                                <line x1="126" y1="74" x2="140" y2="74"
                                    opacity="0.3" />
                                <line x1="140" y1="83" x2="140" y2="90" />
                                <line x1="125" y1="90" x2="155" y2="90" />

                                {{-- Badan --}}
                                <path d="M82 70 Q82 55 100 55 Q118 55 118 70 L118 100 L82 100 Z"
                                    fill="rgba(0,103,177,0.08)" />
                                {{-- Kepala --}}
                                <circle cx="100" cy="42" r="12" fill="rgba(0,103,177,0.08)" />
                                <path d="M88 38 Q100 30 112 38" fill="none" />
                                {{-- Meja --}}
                                <line x1="60" y1="100" x2="180" y2="100" />
                                <line x1="65" y1="100" x2="65" y2="150" />
                                <line x1="175" y1="100" x2="175" y2="150" />
                                {{-- Coffee mug --}}
                                <rect x="70" y="90" width="12" height="10" rx="1"
                                    fill="rgba(0,103,177,0.05)" />
                                <path d="M82 92 Q85 92 85 95 Q85 98 82 98" />
                                {{-- Uap kopi (feedback) --}}
                                <g class="steam">
                                    <path d="M73 88 Q71 82 73 76" stroke-width="1" opacity="0.5" />
                                    <path d="M76 88 Q78 82 76 76" stroke-width="1" opacity="0.4" />
                                    <path d="M79 88 Q77 82 79 76" stroke-width="1" opacity="0.5" />
                                </g>
                                {{-- Lengan mengetik --}}
                                <path class="arm-typing-1" d="M88 92 L85 105" fill="none" />
                                <path class="arm-typing-2" d="M112 92 L115 105" fill="none" />
                            </svg>
                            <span class="room-label">Karyawan</span>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </div>

    {{-- Character Panel --}}
    <div class="character-panel" id="characterPanel">
        <button class="character-panel__close" id="panelClose" aria-label="Tutup">×</button>
        <span class="character-panel__role" id="panelRole"></span>
        <h3 class="character-panel__title" id="panelTitle"></h3>
        <p class="character-panel__desc" id="panelDesc"></p>
    </div>

    {{-- Corner Marks --}}
    <div class="corner-mark tl"></div>
    <div class="corner-mark tr"></div>

    {{-- ============================================================
         SCRIPTS
    ============================================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.1.14/dist/lenis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ============================================================
            // POLISH 1 — PRELOADER
            // ============================================================
            const preloader = document.getElementById('preloader');
            const preloaderBar = document.getElementById('preloaderBar');
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(progressInterval);
                    setTimeout(() => {
                        preloader.classList.add('hidden');
                    }, 300);
                }
                preloaderBar.style.width = progress + '%';
            }, 150);

            setTimeout(() => {
                preloader.classList.add('hidden');
            }, 3000);

            // ============================================================
            // LENIS — Smooth Scroll
            // ============================================================
            const lenis = new Lenis({
                duration: 1.2,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                smoothWheel: true,
                wheelMultiplier: 1
            });

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // ============================================================
            // GSAP ScrollTrigger
            // ============================================================
            gsap.registerPlugin(ScrollTrigger);
            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => {
                lenis.raf(time * 1000);
            });
            gsap.ticker.lagSmoothing(0);

            // HERO TRANSITION
            gsap.to('.brand-side, .form-side', {
                scrollTrigger: {
                    trigger: '.hero-section',
                    start: 'top top',
                    end: '60% top',
                    scrub: 1
                },
                opacity: 0,
                y: -40,
                ease: 'power2.in'
            });

            gsap.to('.asset', {
                scrollTrigger: {
                    trigger: '.hero-section',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 1
                },
                opacity: 0,
                scale: 0.6,
                ease: 'power2.in'
            });

            // ============================================================
            // POLISH 2 — ZOOM TRANSITION
            // ============================================================
            gsap.to('.zoom-text', {
                scrollTrigger: {
                    trigger: '.zoom-section',
                    start: 'top 60%',
                    end: 'center center',
                    scrub: 1
                },
                opacity: 0,
                y: -20
            });

            // CUTAWAY HEADER REVEAL
            gsap.to('.cutaway-header', {
                scrollTrigger: {
                    trigger: '.cutaway-section',
                    start: 'top 70%',
                    end: 'top 40%',
                    scrub: 1
                },
                opacity: 1,
                y: 0,
                ease: 'power3.out'
            });

            // ============================================================
            // FLOOR REVEAL (Stagger)
            // ============================================================
            gsap.utils.toArray('.floor').forEach((floor, index) => {
                gsap.to(floor, {
                    scrollTrigger: {
                        trigger: floor,
                        start: 'top 85%',
                        end: 'top 50%',
                        scrub: 1
                    },
                    opacity: 1,
                    x: 0,
                    duration: 0.8,
                    delay: index * 0.15,
                    ease: 'power3.out'
                });
            });

            // ============================================================
            // STAGE 4 — CAMERA PAN (Diorama Live)
            // Cutaway bergeser horizontal saat scroll = efek kamera sinematik
            // ============================================================
            gsap.to('.building-cutaway', {
                scrollTrigger: {
                    trigger: '.cutaway-section',
                    start: 'top center',
                    end: 'bottom top',
                    scrub: 1
                },
                x: -30,
                ease: 'none'
            });

            // ============================================================
            // STAGE 4 — RANDOM IDLE (Sesekali karakter bergerak)
            // Setiap 5 detik, salah satu karakter "bernafas" lebih jelas
            // ============================================================
            setInterval(() => {
                const rooms = document.querySelectorAll('.room');
                if (rooms.length === 0) return;

                const randomRoom = rooms[Math.floor(Math.random() * rooms.length)];
                const svg = randomRoom.querySelector('svg');

                if (svg) {
                    gsap.fromTo(svg, {
                        scale: 1
                    }, {
                        scale: 1.03,
                        duration: 0.3,
                        yoyo: true,
                        repeat: 1,
                        ease: 'power2.inOut'
                    });
                }
            }, 5000);

            // ============================================================
            // INTERACTIVE — CLICK KARAKTER
            // ============================================================
            // ============================================================
            // ROLES DATA — Penjelasan untuk setiap karakter
            // ============================================================
            const rolesData = {
                admin: {
                    role: 'Level — Admin',
                    title: 'Administrator Sistem',
                    desc: 'Mengelola seluruh sistem SINADAS — dari manajemen pengguna, konfigurasi aset, hingga pemantauan server. Memastikan sistem selalu online 24/7.'
                },
                rektor: {
                    role: 'Level — Rektor',
                    title: 'Pimpinan Tertinggi',
                    desc: 'Menyetujui pengajuan aset tingkat strategis, memantau anggaran tahunan, dan meninjau laporan inventaris kampus secara berkala.'
                },
                kaprodi: {
                    role: 'Level — Kaprodi',
                    title: 'Ketua Program Studi',
                    desc: 'Menyetujui pengajuan aset di tingkat program studi, mengkoordinasi kebutuhan dosen dan mahasiswa, serta memantau penggunaan aset prodi.'
                },
                kalab: {
                    role: 'Level — Kalab',
                    title: 'Kepala Laboratorium',
                    desc: 'Bertanggung jawab atas seluruh aset laboratorium — memverifikasi kondisi alat, mengawasi penggunaan, dan mengajukan pemeliharaan bila diperlukan.'
                },
                aslab: {
                    role: 'Level — Aslab',
                    title: 'Asisten Laboratorium',
                    desc: 'Membantu Kalab dalam operasional harian lab — menyiapkan alat, mencatat pemakaian, dan memastikan alat kembali ke tempatnya.'
                },
                dosen: {
                    role: 'Level — Dosen',
                    title: 'Tenaga Pendidik',
                    desc: 'Menggunakan aset untuk kegiatan belajar mengajar, mengajukan kebutuhan alat peraga, dan berkoordinasi dengan prodi terkait pengadaan.'
                },
                mahasiswa: {
                    role: 'Level — Mahasiswa',
                    title: 'Pengguna Akhir',
                    desc: 'Dapat melihat katalog aset, mengajukan peminjaman alat untuk praktikum atau penelitian, dan melaporkan kondisi aset yang bermasalah.'
                },
                sarpras: {
                    role: 'Level — Sarpras',
                    title: 'Sarana & Prasarana',
                    desc: 'Pencatat utama aset kampus. Menerima, memverifikasi, dan meregistrasi aset dari pengajuan unit ke inventaris resmi dengan nomor seri dan QR code.'
                },
                pengadaan: {
                    role: 'Level — PJ Pengadaan',
                    title: 'Penanggung Jawab Pengadaan',
                    desc: 'Memverifikasi pengajuan aset, mengelola proses pembelian, dan menerima kiriman barang dari vendor untuk diserahkan ke Sarpras.'
                },
                keuangan: {
                    role: 'Level — Keuangan',
                    title: 'Bagian Keuangan',
                    desc: 'Mengonfirmasi pencairan dana pengadaan, mencatat nilai perolehan aset, dan menyusun laporan keuangan aset kampus.'
                },
                pemeliharaan: {
                    role: 'Level — Tim Pemeliharaan',
                    title: 'Teknisi & Maintenance',
                    desc: 'Memperbaiki aset yang rusak, melakukan pemeliharaan berkala, dan memastikan seluruh fasilitas kampus dalam kondisi prima.'
                },
                administrasi: {
                    role: 'Level — Administrasi',
                    title: 'Staf Administrasi',
                    desc: 'Mengelola dokumen pengajuan, mengarsipkan berita acara serah terima, dan menyusun laporan administrasi aset secara berkala.'
                },
                karyawan: {
                    role: 'Level — Karyawan',
                    title: 'Staf Operasional',
                    desc: 'Menggunakan aset kantor untuk kegiatan operasional harian, melaporkan kerusakan, dan mengajukan kebutuhan alat kerja.'
                }
            };

            const panel = document.getElementById('characterPanel');
            const panelRole = document.getElementById('panelRole');
            const panelTitle = document.getElementById('panelTitle');
            const panelDesc = document.getElementById('panelDesc');
            const panelClose = document.getElementById('panelClose');

            document.querySelectorAll('.room').forEach(room => {
                room.addEventListener('click', () => {
                    const roleKey = room.dataset.role;
                    const data = rolesData[roleKey];
                    if (!data) return;

                    panelRole.textContent = data.role;
                    panelTitle.textContent = data.title;
                    panelDesc.textContent = data.desc;

                    panel.classList.add('active');
                    document.querySelectorAll('.room').forEach(r => r.classList.remove('active'));
                    room.classList.add('active');

                    gsap.fromTo(room.querySelector('svg'), {
                        scale: 1
                    }, {
                        scale: 1.08,
                        duration: 0.25,
                        yoyo: true,
                        repeat: 1,
                        ease: 'power2.inOut'
                    });
                });
            });

            function closePanel() {
                panel.classList.remove('active');
                document.querySelectorAll('.room').forEach(r => r.classList.remove('active'));
            }

            panelClose.addEventListener('click', closePanel);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closePanel();
            });
            document.addEventListener('click', (e) => {
                if (panel.classList.contains('active') && !panel.contains(e.target) && !e.target.closest(
                        '.room')) {
                    closePanel();
                }
            });

            // ============================================================
            // POLISH 5 — SOUND DESIGN (Web Audio API)
            // ============================================================
            let audioCtx = null;
            let soundEnabled = true;

            function initAudio() {
                if (!audioCtx) {
                    try {
                        audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    } catch (e) {
                        soundEnabled = false;
                    }
                }
            }

            function playTone(frequency, duration, type = 'sine', volume = 0.05) {
                if (!soundEnabled || !audioCtx) return;
                try {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = type;
                    osc.frequency.value = frequency;
                    gain.gain.setValueAtTime(0, audioCtx.currentTime);
                    gain.gain.linearRampToValueAtTime(volume, audioCtx.currentTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + duration);
                } catch (e) {}
            }

            document.querySelectorAll('.room').forEach(room => {
                room.addEventListener('mouseenter', () => playTone(1200, 0.04, 'sine', 0.02));
                room.addEventListener('click', () => {
                    playTone(880, 0.08, 'triangle', 0.05);
                    setTimeout(() => playTone(1320, 0.1, 'sine', 0.03), 30);
                });
            });

            let lastScrollSound = 0;
            window.addEventListener('scroll', () => {
                const now = Date.now();
                if (now - lastScrollSound > 400) {
                    playTone(600, 0.03, 'sine', 0.01);
                    lastScrollSound = now;
                }
            }, {
                passive: true
            });

            document.addEventListener('click', initAudio, {
                once: true
            });
            document.addEventListener('scroll', initAudio, {
                once: true
            });
            document.addEventListener('keydown', initAudio, {
                once: true
            });

        });
    </script>

</body>

</html>
