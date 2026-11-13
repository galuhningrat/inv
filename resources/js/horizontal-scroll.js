/**
 * Horizontal Scroll & Tooltip Implementation dengan Pop-up
 * File: public/js/horizontal-scroll.js
 */

(function () {
    "use strict";

    /**
     * Create and inject hint element PAKSA
     */
    function createHintElement() {
        // HAPUS yang lama jika ada
        let oldHint = document.getElementById("horizontalScrollHint");
        if (oldHint) {
            oldHint.remove();
        }

        // BUAT BARU
        const hintElement = document.createElement("div");
        hintElement.id = "horizontalScrollHint";
        hintElement.className = "scroll-hint";
        hintElement.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 14px;
            opacity: 0;
            transform: translateY(20px) scale(0.9);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 99999;
            pointer-events: none;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            max-width: 320px;
            text-align: center;
            font-family: inherit;
        `;
        hintElement.innerHTML =
            "🔍 Gunakan <strong>Shift + Scroll mouse</strong> untuk melihat konten lebih lengkap";

        document.body.appendChild(hintElement);

        console.log("✅ Hint element created and injected");
        return hintElement;
    }

    /**
     * Show hint dengan animasi
     */
    function showScrollHint() {
        // Cek apakah hint sudah pernah ditampilkan dalam session ini
        if (sessionStorage.getItem("scrollHintShown") === "true") {
            return;
        }

        const scrollableElements = document.querySelectorAll(
            ".table-wrapper, .data-table-container, .content-area, .report-container"
        );

        let hasHorizontalScroll = false;
        scrollableElements.forEach((element) => {
            if (element.scrollWidth > element.clientWidth + 10) {
                // +10 untuk tolerance
                hasHorizontalScroll = true;
            }
        });

        if (hasHorizontalScroll) {
            const hintElement = createHintElement();

            // Tampilkan hint
            setTimeout(() => {
                hintElement.classList.add("show");
            }, 500);

            // Auto-hide setelah 6 detik
            setTimeout(() => {
                hideScrollHint();
                sessionStorage.setItem("scrollHintShown", "true");
            }, 6000);
        }
    }

    /**
     * Hide scroll hint
     */
    function hideScrollHint() {
        const hint = document.getElementById("horizontalScrollHint");
        if (hint) {
            hint.classList.remove("show");
        }
    }

    /**
     * Setup horizontal scroll untuk semua container
     */
    function setupHorizontalScroll() {
        const scrollableContainers = document.querySelectorAll(
            ".table-wrapper, .data-table-container, .content-area, .report-container, .modal-content"
        );

        scrollableContainers.forEach((container) => {
            // Tambahkan event listener untuk scroll horizontal
            container.addEventListener("wheel", handleHorizontalScroll, {
                passive: false,
            });

            // Tambahkan indikator scroll jika diperlukan
            if (container.scrollWidth > container.clientWidth + 10) {
                addScrollIndicator(container);
            }
        });
    }

    /**
     * Handler untuk scroll horizontal dengan Shift + Wheel
     */
    function handleHorizontalScroll(event) {
        if (event.shiftKey && this.scrollWidth > this.clientWidth) {
            event.preventDefault();

            // Smooth scroll
            const scrollAmount = event.deltaY;
            this.scrollBy({
                left: scrollAmount,
                behavior: "smooth",
            });

            // Hide hint saat user mulai scroll
            hideScrollHint();
        }
    }

    /**
     * Tambahkan indikator visual untuk scroll horizontal
     */
    function addScrollIndicator(container) {
        // Cek apakah sudah ada indikator
        if (container.querySelector(".scroll-indicator")) return;

        const indicator = document.createElement("div");
        indicator.className = "scroll-indicator";
        indicator.innerHTML = "← Geser →";
        indicator.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(37, 99, 235, 0.95);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        `;

        // Tambahkan indikator ke container
        const parent = container.closest(".data-table-container") || container;
        if (
            parent.style.position !== "relative" &&
            parent.style.position !== "absolute"
        ) {
            parent.style.position = "relative";
        }
        parent.appendChild(indicator);

        // Show indicator on hover
        container.addEventListener("mouseenter", () => {
            if (container.scrollWidth > container.clientWidth + 10) {
                indicator.style.opacity = "1";
            }
        });

        container.addEventListener("mouseleave", () => {
            indicator.style.opacity = "0";
        });

        // Hide indicator after first scroll
        let hasScrolled = false;
        container.addEventListener(
            "scroll",
            () => {
                if (!hasScrolled) {
                    hasScrolled = true;
                    setTimeout(() => {
                        indicator.style.opacity = "0";
                    }, 1000);
                }
            },
            { once: false }
        );
    }

    /**
     * Setup keyboard shortcuts untuk scroll hint
     */
    function setupKeyboardShortcuts() {
        document.addEventListener("keydown", (event) => {
            if (event.shiftKey) {
                // Berikan visual feedback bahwa Shift ditekan
                const tables = document.querySelectorAll(".table-wrapper");
                tables.forEach((table) => {
                    if (table.scrollWidth > table.clientWidth) {
                        table.style.cursor = "ew-resize";
                    }
                });
            }
        });

        document.addEventListener("keyup", (event) => {
            if (event.key === "Shift") {
                const tables = document.querySelectorAll(".table-wrapper");
                tables.forEach((table) => {
                    table.style.cursor = "";
                });
            }
        });
    }

    /**
     * Check scroll visibility on resize
     */
    function setupResizeObserver() {
        let resizeTimeout;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Re-check scroll hint
                if (sessionStorage.getItem("scrollHintShown") !== "true") {
                    showScrollHint();
                }

                // Re-check indicators
                setupHorizontalScroll();
            }, 500);
        });
    }

    /**
     * Initialize all features
     */
    function init() {
        // Setup horizontal scroll
        setupHorizontalScroll();

        // Setup keyboard shortcuts
        setupKeyboardShortcuts();

        // Setup resize observer
        setupResizeObserver();

        // Show hint on load (dengan delay)
        setTimeout(showScrollHint, 1000);

        console.log("✅ Horizontal scroll with pop-up initialized");
    }

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    // Re-initialize on dynamic content changes (for AJAX loaded content)
    window.reinitializeHorizontalScroll = function () {
        setupHorizontalScroll();

        // Reset hint shown flag untuk halaman baru
        sessionStorage.removeItem("scrollHintShown");
        showScrollHint();
    };

    // Reset hint pada navigation
    window.addEventListener("beforeunload", function () {
        sessionStorage.removeItem("scrollHintShown");
    });
})();
