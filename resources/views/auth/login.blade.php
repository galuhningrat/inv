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
            --paper-warm: #fafbfc;
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
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(var(--stti-blue-rgb), 0.04) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(var(--stti-blue-rgb), 0.03) 0%, transparent 50%);
            z-index: 0;
            pointer-events: none;
        }

        /* ============================================================
           FLOATING ASSETS — 3D CINEMATIC
        ============================================================ */
        .asset-field {
            position: fixed;
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
                transform:
                    translate3d(0, 0, var(--z, 0px)) rotateY(var(--rotate-y, 0deg)) rotateX(0deg) rotateZ(var(--rotate-z, 0deg));
            }

            25% {
                transform:
                    translate3d(var(--drift-x, 10px), var(--drift-y, -15px), calc(var(--z, 0px) + 24px)) rotateY(calc(var(--rotate-y, 0deg) + 18deg)) rotateX(6deg) rotateZ(calc(var(--rotate-z, 0deg) + 2deg));
            }

            50% {
                transform:
                    translate3d(calc(var(--drift-x, 10px) * -0.4), calc(var(--drift-y, -15px) * 0.6), var(--z, 0px)) rotateY(calc(var(--rotate-y, 0deg) - 8deg)) rotateX(-4deg) rotateZ(var(--rotate-z, 0deg));
            }

            75% {
                transform:
                    translate3d(calc(var(--drift-x, 10px) * -1), calc(var(--drift-y, -15px) * 0.3), calc(var(--z, 0px) - 16px)) rotateY(calc(var(--rotate-y, 0deg) + 10deg)) rotateX(3deg) rotateZ(calc(var(--rotate-z, 0deg) - 2deg));
            }
        }

        /* ============================================================
           BAGIAN KIRI — 12 aset
        ============================================================ */

        /* 1. Router/Wifi (JAR) — pojok kiri atas */
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

        /* 2. Laptop (KOM) — kiri atas */
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

        /* 3. Monitor (KOM) — kiri tengah atas */
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

        /* 4. Keyboard (KOM) — kiri tengah */
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

        /* 5. Buku (ARS) — kiri tengah bawah */
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

        /* 6. Chip/Circuit (ELK) — kiri bawah */
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

        /* 7. Toolbox (ATK) — kiri bawah */
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

        /* 8. Mobil (OTO) — pojok kiri bawah */
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

        /* 9. Kursi (FUR) — kiri bawah (dekat mobil) */
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

        /* 10. Speaker (ELK) — tengah bawah kiri */
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

        /* 11. Mouse (KOM) — kiri tengah (kecil) */
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

        /* 12. Pliers (ATK) — kiri tengah */
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

        /* ============================================================
           BAGIAN TENGAH — 5 aset
        ============================================================ */

        /* 13. Telepon (ELK) — tengah atas */
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

        /* 14. Lampu (ELK) — tengah */
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

        /* 15. Bola Lampu (ELK) — tengah */
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

        /* 16. Palu (ATK) — tengah bawah */
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

        /* 17. Kunci (LAN) — tengah bawah */
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

        /* ============================================================
           BAGIAN KANAN — 12 aset
        ============================================================ */

        /* 18. Gedung (GDG) — pojok kanan atas */
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

        /* 19. Labu/Flask (LAB) — kanan atas */
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

        /* 20. Mikroskop (LAB) — kanan tengah atas */
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

        /* 21. Tabung Reaksi (LAB) — kanan tengah */
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

        /* 22. Meja (FUR) — kanan tengah */
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

        /* 23. Mikrofon (ELK) — kanan bawah atas */
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

        /* 24. Printer (KOM) — kanan bawah */
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

        /* 25. Kunci Pas (ATK) — kanan bawah */
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

        /* 26. Obeng (ATK) — kanan bawah */
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

        /* 27. Server Rack (KOM) — kanan bawah pojok */
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

        /* 28. Flashdisk/Drive (KOM) — tengah kanan */
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

        /* 29. Kompas (LAN) — tengah atas kanan */
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
           LAYOUT
        ============================================================ */
        .layout {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100vh;
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
            font-family: 'Inter', sans-serif;
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
            font-family: 'Inter', sans-serif;
            font-size: clamp(14px, 1.1vw, 16px);
            line-height: 1.55;
            color: var(--ink-soft);
            max-width: 420px;
            letter-spacing: 0.1px;
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

        .card-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
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
            opacity: 0;
            animation: fadeUp 0.4s ease-out 1.7s forwards;
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

        .btn:active {
            transform: scale(0.995);
        }

        .btn:focus-visible {
            outline: 2px solid var(--stti-blue);
            outline-offset: 2px;
        }

        .card-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            color: var(--ink-faint);
            letter-spacing: 0.5px;
            opacity: 0;
            animation: fadeUp 0.5s ease-out 2.05s forwards;
        }

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
            left: 24px;
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
           RESPONSIVE — TABLET
        ============================================================ */
        @media (max-width: 1024px) {
            .layout {
                padding: 0 5vw;
                gap: 3vw;
            }

            .brand-title {
                font-size: clamp(56px, 8vw, 96px);
            }

            .brand-subtitle {
                font-size: 13px;
            }

            /* Sembunyikan aset kecil di tablet */
            .asset--mouse,
            .asset--bulb,
            .asset--compass,
            .asset--drive,
            .asset--pliers,
            .asset--screwdriver,
            .asset--testtube,
            .asset--speaker {
                display: none;
            }
        }

        /* ============================================================
           RESPONSIVE — MOBILE
        ============================================================ */
        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
                align-items: flex-start;
                padding: 6vh 6vw 4vh;
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
                text-shadow:
                    0 12px 40px rgba(var(--stti-blue-rgb), 0.10);
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

            /* Sembunyikan hanya aset kecil yang tidak penting */
            .asset--pliers,
            .asset--screwdriver,
            .asset--testtube,
            .asset--compass,
            .asset--toolbox,
            .asset--speaker,
            .asset--desk,
            .asset--chair {
                display: none;
            }

            /* ============================================================
       ATAS — Sekitar brand
    ============================================================ */
            .asset--router {
                width: 46px;
                height: 46px;
                top: 1.5%;
                left: 4%;
                --z: -20px;
            }

            .asset--laptop {
                width: 52px;
                height: 52px;
                top: 1.5%;
                right: 4%;
                left: auto;
                --z: 40px;
            }

            .asset--monitor {
                width: 42px;
                height: 42px;
                top: 9%;
                left: 2%;
                --z: -60px;
            }

            .asset--flask {
                width: 46px;
                height: 46px;
                top: 8%;
                right: 3%;
                --z: 20px;
            }

            .asset--bulb {
                width: 38px;
                height: 38px;
                top: 17%;
                left: 42%;
                --z: -80px;
            }

            /* ============================================================
       SAMPING CARD
    ============================================================ */
            .asset--book {
                width: 40px;
                height: 40px;
                top: 42%;
                left: 1%;
                --z: 20px;
            }

            .asset--phone {
                width: 42px;
                height: 42px;
                top: 40%;
                right: 1%;
                --z: -40px;
            }

            .asset--lamp {
                width: 42px;
                height: 42px;
                top: 54%;
                left: 2%;
                --z: -60px;
            }

            .asset--chip {
                width: 38px;
                height: 38px;
                top: 56%;
                right: 1%;
                --z: 40px;
            }

            /* ============================================================
       BAWAH — Setelah card
    ============================================================ */
            .asset--car {
                width: 52px;
                height: 52px;
                bottom: 3%;
                left: 3%;
                --z: 60px;
            }

            .asset--building {
                width: 50px;
                height: 50px;
                bottom: 3%;
                right: 4%;
                --z: -20px;
            }

            .asset--keyboard {
                width: 46px;
                height: 46px;
                bottom: 13%;
                left: 6%;
                --z: -40px;
            }

            .asset--printer {
                width: 48px;
                height: 48px;
                bottom: 13%;
                right: 5%;
                --z: 20px;
            }

            .asset--mouse {
                width: 36px;
                height: 36px;
                bottom: 3%;
                left: 40%;
                --z: -60px;
            }

            .asset--hammer {
                width: 40px;
                height: 40px;
                bottom: 13%;
                left: 42%;
                --z: 40px;
            }

            .asset--key {
                width: 42px;
                height: 42px;
                bottom: 22%;
                right: 3%;
                --z: -80px;
            }

            .asset--wrench {
                width: 44px;
                height: 44px;
                bottom: 22%;
                left: 3%;
                --z: 60px;
            }
        }

        @media (max-width: 420px) {
            .brand-title {
                font-size: 48px;
            }

            .brand-subtitle {
                font-size: 12px;
            }

            .card {
                padding: 28px 20px 22px;
                border-radius: 10px;
            }

            .card-logo {
                width: 72px;
                height: 72px;
            }

            .field input,
            .btn {
                height: 44px;
            }

            /* Sembunyikan aset tengah bawah biar tidak crowded di HP kecil */
            .asset--mouse,
            .asset--hammer {
                display: none;
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
         FLOATING ASSETS — 29 aset kampus
    ============================================================ --}}
    <div class="asset-field" aria-hidden="true">

        {{-- 1. Router --}}
        <div class="asset asset--router">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="14" width="20" height="6" rx="1.5" />
                <line x1="6" y1="17" x2="6" y2="17.01" />
                <line x1="9" y1="17" x2="9" y2="17.01" />
                <line x1="12" y1="17" x2="12" y2="17.01" />
                <path d="M8 11a6 6 0 0 1 8 0" />
                <path d="M10.5 8.5a8.5 8.5 0 0 1 3 0" />
            </svg>
        </div>

        {{-- 2. Laptop --}}
        <div class="asset asset--laptop">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="12" rx="1.5" />
                <path d="M2 20h20l-2-3.5H4z" />
            </svg>
        </div>

        {{-- 3. Monitor --}}
        <div class="asset asset--monitor">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="1.5" />
                <line x1="8" y1="21" x2="16" y2="21" />
                <line x1="12" y1="17" x2="12" y2="21" />
            </svg>
        </div>

        {{-- 4. Keyboard --}}
        <div class="asset asset--keyboard">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="11" rx="1.5" />
                <line x1="6" y1="11" x2="6" y2="11.01" />
                <line x1="10" y1="11" x2="10" y2="11.01" />
                <line x1="14" y1="11" x2="14" y2="11.01" />
                <line x1="18" y1="11" x2="18" y2="11.01" />
                <line x1="7" y1="14.5" x2="17" y2="14.5" />
            </svg>
        </div>

        {{-- 5. Buku --}}
        <div class="asset asset--book">
            <svg viewBox="0 0 24 24">
                <path d="M4 5a2 2 0 0 1 2-2h11v18H6a2 2 0 0 1-2-2z" />
                <path d="M9 7h5M9 11h5" />
            </svg>
        </div>

        {{-- 6. Chip --}}
        <div class="asset asset--chip">
            <svg viewBox="0 0 24 24">
                <rect x="6" y="6" width="12" height="12" rx="1" />
                <rect x="9.5" y="9.5" width="5" height="5" />
                <path d="M9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4" />
            </svg>
        </div>

        {{-- 7. Toolbox --}}
        <div class="asset asset--toolbox">
            <svg viewBox="0 0 24 24">
                <rect x="2" y="7" width="20" height="14" rx="1.5" />
                <path d="M8 7V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v3" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <line x1="11" y1="12" x2="13" y2="12" />
            </svg>
        </div>

        {{-- 8. Mobil --}}
        <div class="asset asset--car">
            <svg viewBox="0 0 24 24">
                <path d="M5 11l2-5h10l2 5" />
                <rect x="3" y="11" width="18" height="6" rx="1" />
                <circle cx="7" cy="18" r="1.5" />
                <circle cx="17" cy="18" r="1.5" />
                <path d="M6 14h2M16 14h2" />
            </svg>
        </div>

        {{-- 9. Kursi --}}
        <div class="asset asset--chair">
            <svg viewBox="0 0 24 24">
                <path d="M6 4v10" />
                <path d="M6 14h10" />
                <path d="M16 4v14" />
                <path d="M10 20h6" />
            </svg>
        </div>

        {{-- 10. Speaker --}}
        <div class="asset asset--speaker">
            <svg viewBox="0 0 24 24">
                <rect x="5" y="2" width="14" height="20" rx="2" />
                <circle cx="12" cy="8" r="2" />
                <circle cx="12" cy="16" r="3.5" />
            </svg>
        </div>

        {{-- 11. Mouse --}}
        <div class="asset asset--mouse">
            <svg viewBox="0 0 24 24">
                <rect x="6" y="3" width="12" height="18" rx="6" />
                <line x1="12" y1="3" x2="12" y2="10" />
            </svg>
        </div>

        {{-- 12. Pliers --}}
        <div class="asset asset--pliers">
            <svg viewBox="0 0 24 24">
                <circle cx="8" cy="6" r="3" />
                <circle cx="16" cy="6" r="3" />
                <path d="M9.5 8l5 10" />
                <path d="M14.5 8l-5 10" />
            </svg>
        </div>

        {{-- 13. Telepon --}}
        <div class="asset asset--phone">
            <svg viewBox="0 0 24 24">
                <rect x="6" y="2" width="12" height="20" rx="2" />
                <path d="M12 18h.01" />
            </svg>
        </div>

        {{-- 14. Lampu --}}
        <div class="asset asset--lamp">
            <svg viewBox="0 0 24 24">
                <path d="M9 3h6l2 8H7z" />
                <path d="M12 11v5" />
                <ellipse cx="12" cy="19" rx="4" ry="2" />
            </svg>
        </div>

        {{-- 15. Bola Lampu --}}
        <div class="asset asset--bulb">
            <svg viewBox="0 0 24 24">
                <path d="M9 18a6 6 0 1 1 6 0v2H9z" />
                <line x1="9" y1="22" x2="15" y2="22" />
            </svg>
        </div>

        {{-- 16. Palu --}}
        <div class="asset asset--hammer">
            <svg viewBox="0 0 24 24">
                <path d="M14 4l6 6-3 3-6-6z" />
                <line x1="11" y1="7" x2="3" y2="15" />
                <line x1="9" y1="17" x2="5" y2="13" />
            </svg>
        </div>

        {{-- 17. Kunci --}}
        <div class="asset asset--key">
            <svg viewBox="0 0 24 24">
                <circle cx="8" cy="12" r="4" />
                <path d="M12 12h10" />
                <path d="M18 12v4M21 12v3" />
            </svg>
        </div>

        {{-- 18. Gedung --}}
        <div class="asset asset--building">
            <svg viewBox="0 0 24 24">
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
            </svg>
        </div>

        {{-- 19. Labu/Flask --}}
        <div class="asset asset--flask">
            <svg viewBox="0 0 24 24">
                <path d="M9 3h6" />
                <path d="M10 3v6L4 19a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-6-10V3" />
                <line x1="7" y1="15" x2="17" y2="15" />
            </svg>
        </div>

        {{-- 20. Mikroskop --}}
        <div class="asset asset--microscope">
            <svg viewBox="0 0 24 24">
                <path d="M6 18h12" />
                <path d="M9 14l3-3" />
                <path d="M12 11l3-3" />
                <circle cx="15" cy="6" r="2" />
                <path d="M4 22h16" />
                <line x1="11" y1="18" x2="13" y2="14" />
            </svg>
        </div>

        {{-- 21. Tabung Reaksi --}}
        <div class="asset asset--testtube">
            <svg viewBox="0 0 24 24">
                <path d="M9 2v18a3 3 0 0 0 6 0V2" />
                <line x1="9" y1="8" x2="15" y2="8" />
                <line x1="9" y1="14" x2="15" y2="14" />
            </svg>
        </div>

        {{-- 22. Meja --}}
        <div class="asset asset--desk">
            <svg viewBox="0 0 24 24">
                <path d="M3 10h18" />
                <path d="M4 10v10M20 10v10" />
                <path d="M8 10v4M16 10v4" />
            </svg>
        </div>

        {{-- 23. Mikrofon --}}
        <div class="asset asset--mic">
            <svg viewBox="0 0 24 24">
                <rect x="9" y="2" width="6" height="12" rx="3" />
                <path d="M5 10a7 7 0 0 0 14 0" />
                <line x1="12" y1="17" x2="12" y2="22" />
                <line x1="8" y1="22" x2="16" y2="22" />
            </svg>
        </div>

        {{-- 24. Printer --}}
        <div class="asset asset--printer">
            <svg viewBox="0 0 24 24">
                <rect x="5" y="3" width="14" height="6" rx="1" />
                <rect x="3" y="9" width="18" height="8" rx="1" />
                <path d="M7 17v4h10v-4" />
            </svg>
        </div>

        {{-- 25. Kunci Pas --}}
        <div class="asset asset--wrench">
            <svg viewBox="0 0 24 24">
                <path d="M14.7 6.3a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-2.8-2.8l9-9" />
                <path d="M13.5 5.5l5 5" />
            </svg>
        </div>

        {{-- 26. Obeng --}}
        <div class="asset asset--screwdriver">
            <svg viewBox="0 0 24 24">
                <rect x="9" y="2" width="6" height="3" rx="0.5" />
                <path d="M11 5v9l1 8 1-8V5z" />
            </svg>
        </div>

        {{-- 27. Server Rack --}}
        <div class="asset asset--server">
            <svg viewBox="0 0 24 24">
                <rect x="4" y="3" width="16" height="6" rx="1" />
                <rect x="4" y="10" width="16" height="6" rx="1" />
                <rect x="4" y="17" width="16" height="4" rx="1" />
                <circle cx="18" cy="6" r="0.5" fill="currentColor" />
                <circle cx="18" cy="13" r="0.5" fill="currentColor" />
            </svg>
        </div>

        {{-- 28. Flashdisk --}}
        <div class="asset asset--drive">
            <svg viewBox="0 0 24 24">
                <rect x="4" y="10" width="12" height="6" rx="1" />
                <path d="M16 12v2h4v-4h-4v2z" />
                <line x1="8" y1="13" x2="8" y2="13.01" />
            </svg>
        </div>

        {{-- 29. Kompas --}}
        <div class="asset asset--compass">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polygon points="12,4 14,12 12,20 10,12" />
                <circle cx="12" cy="12" r="1.5" fill="currentColor" />
            </svg>
        </div>

    </div>

    <div class="corner-mark tl"></div>
    <div class="corner-mark tr"></div>
    <div class="corner-mark bl"></div>
    <div class="corner-mark br"></div>

    <main class="layout">

        <section class="brand-side">

            <div class="brand-eyebrow">
                STTI Cirebon
            </div>

            <h1 class="brand-title">
                SINADAS<span class="dot">.</span>
            </h1>

            <p class="brand-subtitle">
                Sistem Informasi Inventaris dan Administrasi Aset
            </p>

        </section>

        <section class="form-side">

            <div class="card">

                <div class="card-header">
                    <div class="card-logo">
                        <img src="{{ asset('assets/logo-stti.png') }}" alt="Logo STTI">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="field">
                        <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <input type="text" id="username" name="username" placeholder="Username"
                            value="{{ old('username') }}" required autofocus autocomplete="username">
                    </div>

                    <div class="field">
                        <svg class="field-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input type="password" id="password" name="password" placeholder="Password" required
                            autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>

                <div class="card-footer">
                    © {{ date('Y') }} STTI Cirebon
                </div>

            </div>

        </section>

    </main>

</body>

</html>
