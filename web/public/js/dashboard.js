/* ════════════════════════════════════════════════════════════
   Smart Farming Dashboard — JavaScript
════════════════════════════════════════════════════════════ */

/* ── Theme ────────────────────────────────────────────── */
const Theme = {
    KEY: "sf_theme",
    current() {
        return localStorage.getItem(this.KEY) || "light";
    },
    apply(mode) {
        document.documentElement.classList.toggle("dark", mode === "dark");
        document.querySelectorAll("[data-theme-icon]").forEach((el) => {
            el.className = mode === "dark" ? "ri-sun-line" : "ri-moon-line";
        });
        localStorage.setItem(this.KEY, mode);
        if (window._charts)
            window._charts.forEach((c) => c && applyChartTheme(c));
    },
    toggle() {
        this.apply(this.current() === "dark" ? "light" : "dark");
    },
    init() {
        this.apply(this.current());
    },
};

/* ── Language ─────────────────────────────────────────── */
const Lang = {
    KEY: "sf_lang",
    current() {
        return localStorage.getItem(this.KEY) || "en";
    },
    toggle() {
        const next = this.current() === "en" ? "id" : "en";
        localStorage.setItem(this.KEY, next);
        document.querySelectorAll("[data-lang-label]").forEach((el) => {
            el.textContent = next.toUpperCase();
        });
    },
    init() {
        const lang = this.current();
        document.querySelectorAll("[data-lang-label]").forEach((el) => {
            el.textContent = lang.toUpperCase();
        });
    },
};

/* ── Sidebar ──────────────────────────────────────────── */
const Sidebar = {
    KEY: "sf_sidebar",
    init() {
        const mini = localStorage.getItem(this.KEY) === "mini";
        if (mini) this.setMini(true);
        document
            .getElementById("sidebar-toggle")
            ?.addEventListener("click", () => this.toggle());

        // Mobile menu toggle
        const mobileBtn = document.getElementById("mobile-menu-btn");
        const backdrop = document.getElementById("sidebar-backdrop");
        const sidebar = document.getElementById("sidebar");

        mobileBtn?.addEventListener("click", () => this.openMobile());
        backdrop?.addEventListener("click", () => this.closeMobile());
    },
    toggle() {
        const sb = document.getElementById("sidebar");
        const isMini = sb.classList.contains("mini");
        this.setMini(!isMini);
    },
    setMini(val) {
        const sb = document.getElementById("sidebar");
        sb.classList.toggle("mini", val);
        localStorage.setItem(this.KEY, val ? "mini" : "full");
        const icon = document.querySelector("#sidebar-toggle .btn-icon i");
        if (icon)
            icon.className = val ? "ri-menu-unfold-line" : "ri-menu-fold-line";
        const text = document.querySelector("#sidebar-toggle .btn-text");
        if (text) text.textContent = val ? "Expand" : "Collapse";
    },
    openMobile() {
        const sb = document.getElementById("sidebar");
        const backdrop = document.getElementById("sidebar-backdrop");
        sb?.classList.add("mobile-open");
        backdrop?.classList.add("visible");
        document.body.style.overflow = "hidden";
    },
    closeMobile() {
        const sb = document.getElementById("sidebar");
        const backdrop = document.getElementById("sidebar-backdrop");
        sb?.classList.remove("mobile-open");
        backdrop?.classList.remove("visible");
        document.body.style.overflow = "";
    },
};

/* ── Top loading bar ──────────────────────────────────── */
const TopBar = {
    el: null,
    timer: null,
    init() {
        this.el = document.getElementById("topbar-bar");
        if (!this.el) return;
        this.start();
        window.addEventListener("load", () =>
            setTimeout(() => this.finish(), 300),
        );
    },
    start() {
        if (!this.el) return;
        this.el.classList.remove("running");
        this.el.style.width = "0%";
        void this.el.offsetWidth;
        this.el.classList.add("running");
    },
    finish() {
        if (!this.el) return;
        this.el.classList.remove("running");
        this.el.style.width = "100%";
        setTimeout(() => {
            this.el.style.width = "0%";
        }, 400);
    },
    refresh(fn) {
        this.start();
        // 3 second delay as requested
        setTimeout(async () => {
            try {
                await fn();
            } finally {
                this.finish();
            }
        }, 3000);
    },
};

/* ── Toast ────────────────────────────────────────────── */
const Toast = {
    container: null,
    icons: {
        success: "ri-checkbox-circle-fill",
        warning: "ri-error-warning-fill",
        danger: "ri-close-circle-fill",
        info: "ri-information-fill",
    },
    init() {
        this.container = document.getElementById("toast-container");
    },
    show(type = "info", title = "", msg = "", duration = 4000) {
        const t = document.createElement("div");
        t.className = `toast toast-${type}`;
        t.innerHTML = `
      <i class="toast-icon ${this.icons[type] || "ri-information-fill"}"></i>
      <div class="toast-content">
        <div class="toast-title">${title}</div>
        ${msg ? `<div class="toast-msg">${msg}</div>` : ""}
      </div>
      <button class="toast-close" aria-label="Close"><i class="ri-close-line"></i></button>`;
        this.container.appendChild(t);
        requestAnimationFrame(() => {
            requestAnimationFrame(() => t.classList.add("show"));
        });
        const dismiss = () => {
            t.classList.remove("show");
            t.classList.add("hide");
            setTimeout(() => t.remove(), 450);
        };
        t.querySelector(".toast-close").addEventListener("click", dismiss);
        if (duration > 0) setTimeout(dismiss, duration);
        return t;
    },
};

/* ── Modal ────────────────────────────────────────────── */
const Modal = {
    open(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add("open");
        document.body.style.overflow = "hidden";
        el.addEventListener(
            "click",
            (e) => {
                if (e.target === el) this.close(id);
            },
            { once: true },
        );
    },
    close(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove("open");
        document.body.style.overflow = "";
    },
    init() {
        document.querySelectorAll("[data-modal-close]").forEach((btn) => {
            btn.addEventListener("click", () =>
                this.close(btn.dataset.modalClose),
            );
        });
        document.querySelectorAll("[data-modal-open]").forEach((btn) => {
            btn.addEventListener("click", () =>
                this.open(btn.dataset.modalOpen),
            );
        });
    },
};

/* ── Dropdown ─────────────────────────────────────────── */
const Dropdown = {
    init() {
        document.addEventListener("click", (e) => {
            const trigger = e.target.closest("[data-dropdown]");
            if (trigger) {
                const target = document.getElementById(
                    trigger.dataset.dropdown,
                );
                if (!target) return;
                const isOpen = target.classList.contains("open");
                document
                    .querySelectorAll(".dropdown-menu.open")
                    .forEach((m) => m.classList.remove("open"));
                if (!isOpen) target.classList.add("open");
                return;
            }
            document
                .querySelectorAll(".dropdown-menu.open")
                .forEach((m) => m.classList.remove("open"));
        });
    },
};

/* ── Carousel ─────────────────────────────────────────── */
class Carousel {
    constructor(el) {
        this.el = el;
        this.track = el.querySelector(".carousel-track");
        this.slides = el.querySelectorAll(".carousel-slide");
        this.dots = el.querySelectorAll(".carousel-dot");
        this.total = this.slides.length;
        this.idx = 0;
        el.querySelector(".carousel-prev")?.addEventListener("click", () =>
            this.prev(),
        );
        el.querySelector(".carousel-next")?.addEventListener("click", () =>
            this.next(),
        );
        this.dots.forEach((d, i) =>
            d.addEventListener("click", () => this.goto(i)),
        );
    }
    goto(i) {
        this.idx = (i + this.total) % this.total;
        this.track.style.transform = `translateX(-${this.idx * 100}%)`;
        this.dots.forEach((d, j) =>
            d.classList.toggle("active", j === this.idx),
        );
    }
    prev() {
        this.goto(this.idx - 1);
    }
    next() {
        this.goto(this.idx + 1);
    }
}

/* ── Notification panel ───────────────────────────────── */
const Notif = {
    init() {
        document.getElementById("notif-btn")?.addEventListener("click", (e) => {
            e.stopPropagation();
            document.getElementById("notif-panel")?.classList.toggle("open");
        });
    },
};

/* ── Skeleton loading ─────────────────────────────────── */
const Skeleton = {
    show(containerId) {
        const el = document.getElementById(containerId);
        if (el) el.setAttribute("data-loading", "true");
    },
    hide(containerId) {
        const el = document.getElementById(containerId);
        if (el) el.removeAttribute("data-loading");
    },
    row(cols = 4) {
        return `<tr>${Array.from(
            { length: cols },
            () =>
                `<td><div class="skeleton" style="height:14px;border-radius:4px;"></div></td>`,
        ).join("")}</tr>`;
    },
};

/* ── Pagination ───────────────────────────────────────── */
class Pagination {
    constructor({ container, total, perPage = 10, onChange }) {
        this.container =
            typeof container === "string"
                ? document.getElementById(container)
                : container;
        this.total = total;
        this.perPage = perPage;
        this.page = 1;
        this.onChange = onChange;
        this.render();
    }
    get pages() {
        return Math.max(1, Math.ceil(this.total / this.perPage));
    }
    goto(p) {
        if (p < 1 || p > this.pages) return;
        this.page = p;
        this.render();
        this.onChange?.(p);
    }
    render() {
        if (!this.container) return;
        const { page, pages } = this;
        let html = `<button class="pg-btn" onclick="this.closest('[data-pg]').__pg.goto(${page - 1})" ${page === 1 ? "disabled" : ""}><i class="ri-arrow-left-s-line"></i></button>`;
        for (let i = 1; i <= pages; i++) {
            if (
                pages > 7 &&
                ((i > 2 && i < page - 1) || (i > page + 1 && i < pages - 1))
            ) {
                if (i === 3 || i === pages - 2)
                    html += `<button class="pg-btn" disabled>…</button>`;
                continue;
            }
            html += `<button class="pg-btn${i === page ? " active" : ""}" onclick="this.closest('[data-pg]').__pg.goto(${i})">${i}</button>`;
        }
        html += `<button class="pg-btn" onclick="this.closest('[data-pg]').__pg.goto(${page + 1})" ${page === pages ? "disabled" : ""}><i class="ri-arrow-right-s-line"></i></button>`;
        this.container.innerHTML = html;
        this.container.setAttribute("data-pg", "");
        this.container.__pg = this;
    }
}

/* ── Chart theme helper ──────────────────────────────── */
function chartColors() {
    const dark = document.documentElement.classList.contains("dark");
    return {
        grid: dark ? "rgba(255,255,255,0.06)" : "rgba(0,0,0,0.05)",
        text: dark ? "#6E956F" : "#7A8E7C",
        green: ["#52B788", "#2D6A4F", "#95CCA0"],
        yellow: ["#F4C430", "#E6A816", "#FFF2A8"],
        zones: ["#2D6A4F", "#F4C430", "#52B788"],
    };
}
function applyChartTheme(chart) {
    const c = chartColors();
    if (chart.options.scales?.x) {
        chart.options.scales.x.grid.color = c.grid;
        chart.options.scales.x.ticks.color = c.text;
    }
    if (chart.options.scales?.y) {
        chart.options.scales.y.grid.color = c.grid;
        chart.options.scales.y.ticks.color = c.text;
    }
    chart.update("none");
}

/* ── Chart defaults ───────────────────────────────────── */
function setupChartDefaults() {
    if (!window.Chart) return;
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor =
        document.documentElement.classList.contains("dark")
            ? "#1E2D20"
            : "#1A1F1B";
    Chart.defaults.plugins.tooltip.titleFont = {
        family: "'Sora', sans-serif",
        size: 12,
        weight: "600",
    };
    Chart.defaults.plugins.tooltip.bodyFont = {
        family: "'DM Sans', sans-serif",
        size: 11,
    };
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.titleColor = "#fff";
    Chart.defaults.plugins.tooltip.bodyColor = "rgba(255,255,255,0.72)";
    Chart.defaults.animation.duration = 600;
    Chart.defaults.animation.easing = "easeInOutQuart";
}

/* ── Build moisture trend chart ──────────────────────── */
function buildMoistureChart(canvasId, labels, datasets) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !window.Chart) return null;
    const c = chartColors();
    const chart = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets: datasets.map((ds, i) => ({
                label: ds.label,
                data: ds.data,
                borderColor: c.zones[i],
                backgroundColor: hexToRgba(c.zones[i], 0.08),
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: c.zones[i],
                fill: true,
                tension: 0.4,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        font: { family: "'DM Sans', sans-serif", size: 11 },
                        boxWidth: 10,
                        boxHeight: 10,
                        color: c.text,
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: "circle",
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            ` ${ctx.dataset.label}: ${ctx.parsed.y}%`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: c.grid, drawBorder: false },
                    ticks: { color: c.text, font: { size: 11 } },
                },
                y: {
                    min: 0,
                    max: 100,
                    grid: { color: c.grid, drawBorder: false },
                    ticks: {
                        color: c.text,
                        font: { size: 11 },
                        callback: (v) => v + "%",
                        stepSize: 25,
                    },
                },
            },
        },
    });
    return chart;
}

/* ── Build doughnut chart ─────────────────────────────── */
function buildStatusChart(canvasId, data, labels, colors) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !window.Chart) return null;
    return new Chart(ctx, {
        type: "doughnut",
        data: {
            labels,
            datasets: [
                {
                    data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "72%",
            plugins: {
                legend: {
                    display: true,
                    position: "bottom",
                    labels: {
                        font: { family: "'DM Sans', sans-serif", size: 11 },
                        boxWidth: 8,
                        boxHeight: 8,
                        padding: 12,
                        usePointStyle: true,
                        pointStyle: "circle",
                    },
                },
            },
        },
    });
}

/* ── Build bar chart ──────────────────────────────────── */
function buildCommandsChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !window.Chart) return null;
    const c = chartColors();
    return new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Commands",
                    data,
                    backgroundColor: hexToRgba("#52B788", 0.7),
                    borderColor: "#2D6A4F",
                    borderWidth: 1,
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: c.text, font: { size: 10 } },
                },
                y: {
                    grid: { color: c.grid, drawBorder: false },
                    ticks: { color: c.text, font: { size: 10 }, precision: 0 },
                },
            },
        },
    });
}

function hexToRgba(hex, a) {
    const r = parseInt(hex.slice(1, 3), 16),
        g = parseInt(hex.slice(3, 5), 16),
        b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${a})`;
}

/* ── Polling (auto-refresh data) ──────────────────────── */
class DataPoller {
    constructor(url, callback, interval = 15000) {
        this.url = url;
        this.callback = callback;
        this.interval = interval;
        this.timer = null;
    }
    async fetch() {
        try {
            const res = await fetch(this.url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (!res.ok) throw new Error(res.statusText);
            const data = await res.json();
            this.callback(data);
        } catch (e) {
            console.warn("[Poller] fetch error:", e.message);
        }
    }
    start() {
        this.fetch();
        this.timer = setInterval(() => this.fetch(), this.interval);
    }
    stop() {
        clearInterval(this.timer);
    }
}

/* ── Gauge render ─────────────────────────────────────── */
function renderGauge(el, pct) {
    if (!el) return;
    const arc = el.querySelector(".gauge-arc");
    const val = el.querySelector(".gauge-val");
    if (arc) arc.style.setProperty("--gauge-pct", `${pct / 2}%`);
    if (val) val.textContent = pct + "%";
}

/* ── Command send ─────────────────────────────────────── */
async function sendCommand(deviceId, type) {
    try {
        const res = await fetch("/dashboard/command", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                )?.content,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ device_id: deviceId, command_type: type }),
        });
        const data = await res.json();
        if (res.ok) {
            Toast.show(
                "success",
                type === "start_pump" ? "Pump Started" : "Pump Stopped",
                `Command sent for device #${deviceId}`,
            );
        } else {
            Toast.show(
                "danger",
                "Command Failed",
                data.message || "Unknown error",
            );
        }
    } catch (e) {
        Toast.show("danger", "Connection Error", e.message);
    }
}

/* ── Keyboard shortcuts ───────────────────────────────── */
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        document
            .querySelectorAll(".modal-overlay.open")
            .forEach((m) => m.classList.remove("open"));
        document
            .querySelectorAll(".dropdown-menu.open")
            .forEach((d) => d.classList.remove("open"));
        document
            .querySelectorAll(".notif-panel.open")
            .forEach((d) => d.classList.remove("open"));
        document.body.style.overflow = "";
    }
});

/* ── Init ─────────────────────────────────────────────── */
document.addEventListener("DOMContentLoaded", () => {
    Theme.init();
    Lang.init();
    Sidebar.init();
    TopBar.init();
    Toast.init();
    Modal.init();
    Dropdown.init();
    Notif.init();

    document.querySelectorAll(".carousel").forEach((el) => new Carousel(el));

    // Close mobile sidebar when nav item tapped
    document.querySelectorAll(".nav-item").forEach((item) => {
        item.addEventListener("click", () => {
            if (window.innerWidth <= 768) Sidebar.closeMobile();
        });
    });

    document
        .getElementById("theme-toggle")
        ?.addEventListener("click", () => Theme.toggle());
    document
        .getElementById("lang-toggle")
        ?.addEventListener("click", () => Lang.toggle());

    window._charts = [];
    setupChartDefaults();

    // Expose globals
    window.Toast = Toast;
    window.Modal = Modal;
    window.TopBar = TopBar;
    window.Skeleton = Skeleton;
    window.Pagination = Pagination;
    window.sendCommand = sendCommand;
    window.buildMoistureChart = buildMoistureChart;
    window.buildStatusChart = buildStatusChart;
    window.buildCommandsChart = buildCommandsChart;
    window.DataPoller = DataPoller;
    window.renderGauge = renderGauge;
});
