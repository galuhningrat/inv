import "./bootstrap";

// Global utility functions
window.showLoading = function () {
    document.getElementById("loadingOverlay").style.display = "flex";
};

window.hideLoading = function () {
    document.getElementById("loadingOverlay").style.display = "none";
};

window.showToast = function (message, type = "success") {
    let toastContainer = document.getElementById("toastContainer");
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toastContainer";
        toastContainer.style.cssText =
            "position: fixed; top: 20px; right: 20px; z-index: 10000;";
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    const bgColor =
        type === "success"
            ? "#10b981"
            : type === "error"
            ? "#ef4444"
            : "#f59e0b";
    toast.style.cssText = `background: ${bgColor}; color: white; padding: 12px 20px; border-radius: 6px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateX(100%); transition: transform 0.3s ease;`;
    toast.textContent = message;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = "translateX(0)";
    }, 100);
    setTimeout(() => {
        toast.style.transform = "translateX(100%)";
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
};

window.scrollToTop = function () {
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Format currency
window.formatRupiah = function (number) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
};

// Confirm delete
window.confirmDelete = function (
    message = "Apakah Anda yakin ingin menghapus?"
) {
    return confirm(message);
};

console.log("Sistem Inventaris STTI Cirebon - Ready");

// Scroll Hint Functionality
function initializeScrollHint() {
    const tables = document.querySelectorAll(".table-wrapper");
    let hintShown = localStorage.getItem("scrollHintShown");

    tables.forEach((table) => {
        if (table.scrollWidth > table.clientWidth && !hintShown) {
            const hint = document.createElement("div");
            hint.className = "scroll-hint show";
            hint.innerHTML =
                "💡 <strong>Tip:</strong> Gunakan <strong>Shift + Scroll</strong> untuk melihat konten tabel secara horizontal";
            document.body.appendChild(hint);

            setTimeout(() => {
                hint.classList.remove("show");
                setTimeout(() => hint.remove(), 300);
                localStorage.setItem("scrollHintShown", "true");
            }, 5000);
        }
    });
}

// Panggil saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
    initializeScrollHint();
});

// Zoom and Responsive Handling
function handleZoomAndResize() {
    const contentArea = document.querySelector(".content-area");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");

    // Detect zoom level (approximate)
    const zoomLevel = window.devicePixelRatio || 1;

    // Adjust layout based on viewport and zoom
    if (window.innerWidth <= 1024 || zoomLevel > 1.5) {
        // Mobile or high zoom - adjust layout
        if (sidebar && mainContent) {
            sidebar.classList.add("collapsed-mobile");
            mainContent.classList.add("mobile-view");
        }

        // Adjust table responsiveness
        const tables = document.querySelectorAll(".data-table");
        tables.forEach((table) => {
            table.style.minWidth = "800px"; // Wider for high zoom
        });
    } else {
        // Desktop normal zoom
        if (sidebar && mainContent) {
            sidebar.classList.remove("collapsed-mobile");
            mainContent.classList.remove("mobile-view");
        }
    }

    // Handle very small screens
    if (window.innerWidth <= 480) {
        document.body.classList.add("mobile-small");
    } else {
        document.body.classList.remove("mobile-small");
    }
}

// Initialize on load
document.addEventListener("DOMContentLoaded", function () {
    handleZoomAndResize();

    // Handle window resize and zoom
    let resizeTimeout;
    window.addEventListener("resize", function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleZoomAndResize, 250);
    });

    // Handle orientation change
    window.addEventListener("orientationchange", function () {
        setTimeout(handleZoomAndResize, 100);
    });
});
