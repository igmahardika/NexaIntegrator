@extends('layouts.portal')

@section('title', ($siteConfig['brand_name'] ?? 'nexa Hotspot') . ' — Akses Internet')

@section('styles')
<!-- Google Fonts: Outfit (Geometric Display) + Plus Jakarta Sans (UI Body) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ==========================================================================
   UI/UX PRO MAX DESIGN SYSTEM: NEXA HOTSPOT CAPTIVE PORTAL (REDESIGNED)
   Aesthetic: Sleek Frosted Glassmorphism, Zero-Topbar Immersive Canvas,
   Refined Typography Hierarchy, Micro-interactions & Mobile CNA Optimization
========================================================================== */

:root {
    /* Brand Theme Colors */
    --nexa-primary: {{ $siteConfig['primary_color'] ?? '#0284c7' }};
    --nexa-primary-hover: #0369a1;
    --nexa-primary-hover: color-mix(in srgb, var(--nexa-primary) 85%, black);
    --nexa-primary-active: #075985;
    --nexa-primary-active: color-mix(in srgb, var(--nexa-primary) 70%, black);
    --nexa-primary-ring: rgba(2, 132, 199, 0.25);
    --nexa-primary-ring: color-mix(in srgb, var(--nexa-primary) 24%, transparent);
    --nexa-primary-glow: rgba(2, 132, 199, 0.18);
    --nexa-primary-glow: color-mix(in srgb, var(--nexa-primary) 18%, transparent);
    --nexa-accent: {{ $siteConfig['accent_color'] ?? '#0369a1' }};

    /* Neutrals & Surfaces */
    --surface-card: #ffffff;
    --surface-card-glass: rgba(255, 255, 255, 0.96);
    --surface-card-subtle: #f8fafc;
    --surface-input: #f1f5f9;
    --surface-input-hover: #e2e8f0;
    --surface-input-focus: #ffffff;
    
    /* Text Hierarchy */
    --text-heading: #0f172a;
    --text-body: #334155;
    --text-muted: #64748b;
    --text-placeholder: #94a3b8;
    --text-inverse: #ffffff;

    /* Semantic States */
    --state-error-bg: #fef2f2;
    --state-error-border: #fecaca;
    --state-error-text: #b91c1c;
    --state-success: #10b981;

    /* Radii & Elevation Shadows */
    --radius-card: 26px;
    --radius-inner: 18px;
    --radius-pill: 9999px;
    --radius-sm: 10px;
    --shadow-card: 0 25px 65px -15px rgba(0, 0, 0, 0.55), 0 10px 25px -5px rgba(0, 0, 0, 0.28);
    --shadow-btn: 0 4px 14px rgba(2, 132, 199, 0.35);
    --shadow-btn: 0 4px 14px color-mix(in srgb, var(--nexa-primary) 42%, transparent);
    --shadow-btn-hover: 0 8px 22px rgba(2, 132, 199, 0.45);
    --shadow-btn-hover: 0 8px 22px color-mix(in srgb, var(--nexa-primary) 58%, transparent);

    /* Fonts: Outfit & Plus Jakarta Sans with instant native system-ui fallback for offline CNA */
    --font-display: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    --font-ui: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    
    /* Transitions */
    --timing-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    --timing-smooth: 260ms cubic-bezier(0.16, 1, 0.3, 1);
}

/* Screen Reader Accessible Utility */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

:focus-visible {
    outline: 2px solid var(--nexa-primary);
    outline-offset: 2px;
}

html, body {
    width: 100%;
    max-width: 100%;
    min-height: 100vh;
    margin: 0;
    padding: 0;
    font-family: var(--font-ui);
    color: var(--text-body);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
    position: relative;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

html::-webkit-scrollbar, body::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

body {
    background: #090d16 url("{{ $siteConfig['bg_value'] ?? '/images/nexa/bg-cafe.jpg' }}") center center / cover no-repeat fixed;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Atmospheric Viewport Deep Lighting Overlay */
.nexa-viewport-overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at 50% 28%, rgba(14, 116, 144, 0.25) 0%, rgba(9, 13, 22, 0.82) 78%);
    pointer-events: none;
    z-index: 1;
}

/* Ambient Radial Glow Orbs */
.nexa-ambient-glow {
    position: fixed;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--nexa-primary-glow) 0%, transparent 70%);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 1;
    filter: blur(40px);
}

/* ==========================================================================
   1. IMMERSIVE CAPTIVE PORTAL WRAPPER (ZERO-NAVBAR FULL VIEWPORT)
========================================================================== */
.nexa-portal-canvas {
    position: relative;
    z-index: 2;
    width: 100%;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
}

/* 2-Column Redesigned Modal Card */
.nexa-modal-card {
    background: var(--surface-card-glass);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    width: 100%;
    max-width: 860px;
    margin: auto;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    min-height: 480px;
    border: 1px solid rgba(255, 255, 255, 0.8);
    animation: nexaCardPopIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes nexaCardPopIn {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(16px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Subtle Top Card Gradient Accent Line */
.nexa-modal-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--nexa-primary), var(--nexa-accent), var(--nexa-primary));
    z-index: 10;
}

/* --------------------------------------------------------------------------
   2A. LEFT PANEL: Brand Header, Forms, and Footer
-------------------------------------------------------------------------- */
.nexa-card-left {
    flex: 1.15;
    min-width: 0;
    width: 100%;
    padding: 38px 36px 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    text-align: center;
    background: var(--surface-card);
}

/* Live Hotspot Status Pill */
.nexa-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 12px;
    border-radius: var(--radius-pill);
    background: rgba(14, 116, 144, 0.08);
    border: 1px solid rgba(14, 116, 144, 0.16);
    color: var(--nexa-primary);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-bottom: 16px;
}

.nexa-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: nexaPulse 2s infinite;
}

@keyframes nexaPulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

.nexa-brand-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 14px;
    width: 100%;
}

.nexa-brand-logo {
    width: 195px;
    max-width: 100%;
    height: auto;
    margin-bottom: 10px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));
    transition: transform var(--timing-smooth);
}

.nexa-brand-logo:hover {
    transform: scale(1.02);
}

.nexa-ig-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-heading);
    text-decoration: none;
    padding: 4px 12px;
    border-radius: var(--radius-pill);
    background: var(--surface-card-subtle);
    border: 1px solid var(--surface-input-hover);
    transition: all var(--timing-fast);
}

.nexa-ig-badge:hover {
    color: #e1306c;
    border-color: rgba(225, 48, 108, 0.35);
    background: #fff;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(225, 48, 108, 0.15);
}

.nexa-ig-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}

/* Title & Subtitle */
.nexa-intro-box {
    margin-top: 10px;
    margin-bottom: 16px;
    width: 100%;
}

.nexa-intro-title {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-heading);
    letter-spacing: -0.01em;
    margin-bottom: 4px;
}

.nexa-intro-subtitle {
    font-size: 0.8125rem;
    color: var(--text-muted);
    line-height: 1.45;
    max-width: 290px;
    margin: 0 auto;
}

/* Form Container & Pill Inputs */
.nexa-form-wrap {
    width: 100%;
    max-width: 320px;
    margin: 6px auto 14px;
}

.nexa-pill-input-box {
    background: var(--surface-input);
    border-radius: var(--radius-pill);
    display: flex;
    align-items: center;
    padding: 4px 5px 4px 18px;
    width: 100%;
    height: 52px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
    border: 1.5px solid transparent;
    transition: border-color var(--timing-fast), background var(--timing-fast), box-shadow var(--timing-fast);
}

.nexa-pill-input-box:focus-within {
    border-color: var(--nexa-primary);
    background: var(--surface-input-focus);
    box-shadow: 0 0 0 4px var(--nexa-primary-ring);
}

.nexa-pill-input-box.mb-3 {
    margin-bottom: 12px;
}

.nexa-pill-input {
    border: none;
    background: transparent;
    outline: none;
    font-family: inherit;
    font-size: 1rem; /* 16px: prevents iOS Safari forced auto-zoom */
    color: var(--text-heading);
    font-weight: 600;
    flex: 1;
    min-width: 0;
    width: auto;
    padding: 8px 0;
}

.nexa-pill-input::placeholder {
    color: var(--text-placeholder);
    font-weight: 500;
}

.nexa-pill-prefix {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-muted);
    padding-right: 8px;
    user-select: none;
    flex-shrink: 0;
}

/* Circular Submit Arrow Button */
.nexa-circle-arrow-btn {
    background: var(--nexa-primary);
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    min-width: 44px; /* WCAG 2.5.5 / Apple HIG 44px touch target minimum */
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-inverse);
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: var(--shadow-btn);
    transition: transform var(--timing-fast), background var(--timing-fast), box-shadow var(--timing-fast);
}

.nexa-circle-arrow-btn:hover:not(:disabled) {
    background: var(--nexa-primary-hover);
    transform: scale(1.06);
    box-shadow: var(--shadow-btn-hover);
}

.nexa-circle-arrow-btn:hover:not(:disabled) svg {
    transform: translateX(1.5px);
}

.nexa-circle-arrow-btn:active:not(:disabled) {
    background: var(--nexa-primary-active);
    transform: scale(0.96);
}

.nexa-circle-arrow-btn svg {
    width: 18px;
    height: 18px;
    transition: transform var(--timing-fast);
}

.nexa-circle-arrow-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* 1-Click / Wide Pill Action */
.nexa-submit-pill-btn {
    background: var(--nexa-primary);
    border: none;
    color: var(--text-inverse);
    padding: 6px 8px 6px 24px;
    border-radius: var(--radius-pill);
    font-family: var(--font-ui);
    font-size: 0.9375rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
    box-shadow: var(--shadow-btn);
    transition: background var(--timing-fast), transform var(--timing-fast), box-shadow var(--timing-fast);
}

.nexa-submit-pill-btn:hover:not(:disabled) {
    background: var(--nexa-primary-hover);
    box-shadow: var(--shadow-btn-hover);
    transform: translateY(-1px);
}

.nexa-submit-pill-btn:active:not(:disabled) {
    transform: translateY(0) scale(0.98);
}

.nexa-submit-pill-btn .nexa-circle-arrow-btn.sm {
    width: 36px;
    height: 36px;
    min-width: 36px;
    background: #ffffff;
    color: var(--nexa-primary);
    box-shadow: none;
}

.nexa-submit-pill-btn .nexa-circle-arrow-btn.sm svg {
    width: 17px;
    height: 17px;
}

/* Alert Notification in Form */
.nexa-alert-error {
    background: var(--state-error-bg);
    border: 1px solid var(--state-error-border);
    color: var(--state-error-text);
    border-radius: var(--radius-sm);
    padding: 9px 12px;
    font-size: 0.8125rem;
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-align: left;
    animation: nexaShake 0.3s ease;
}

@keyframes nexaShake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

.nexa-alert-error svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Survey Questions Styling */
.nexa-survey-box {
    max-height: 220px;
    overflow-y: auto;
    text-align: left;
    padding-right: 6px;
    margin-bottom: 14px;
    scrollbar-width: thin;
    scrollbar-color: var(--surface-input-hover) transparent;
}

.nexa-survey-box::-webkit-scrollbar {
    width: 4px;
}
.nexa-survey-box::-webkit-scrollbar-thumb {
    background: var(--surface-input-hover);
    border-radius: 4px;
}

.nexa-q-item {
    margin-bottom: 14px;
}

.nexa-q-title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--text-heading);
    margin-bottom: 6px;
}

.nexa-q-opt {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8125rem;
    color: var(--text-body);
    margin-bottom: 4px;
    cursor: pointer;
}

.nexa-rating-row {
    display: flex;
    gap: 6px;
    margin-top: 4px;
}

.nexa-rating-btn {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--surface-input-hover);
    background: var(--surface-card-subtle);
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--text-body);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--timing-fast);
}

.nexa-rating-btn:hover {
    border-color: var(--nexa-primary);
    color: var(--nexa-primary);
}

.nexa-rating-btn.selected {
    background: var(--nexa-primary);
    border-color: var(--nexa-primary);
    color: var(--text-inverse);
    box-shadow: 0 2px 8px color-mix(in srgb, var(--nexa-primary) 35%, transparent);
}

/* Terms Note */
.nexa-tos-note {
    font-size: 0.72rem;
    color: var(--text-muted);
    line-height: 1.4;
    margin-top: 4px;
    max-width: 290px;
}

/* Left Column Footer: "Internet By [nexa]" */
.nexa-card-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin-top: 20px;
    padding-top: 14px;
    border-top: 1px solid rgba(241, 245, 249, 0.85);
    width: 100%;
}

.nexa-by-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.nexa-by-logo {
    height: 20px;
    width: auto;
    display: inline-block;
    vertical-align: middle;
}

/* --------------------------------------------------------------------------
   2B. RIGHT PANEL: Promotional Slider Banner & Showcase
-------------------------------------------------------------------------- */
.nexa-card-right {
    flex: 0.95;
    min-width: 0;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border-left: 1px solid rgba(241, 245, 249, 0.9);
    position: relative;
}

.nexa-slider-wrap {
    width: 100%;
    max-width: 320px;
    aspect-ratio: 4/5;
    border-radius: var(--radius-inner);
    overflow: hidden;
    position: relative;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12);
    background: var(--surface-input);
}

.nexa-slide-item {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}

.nexa-slide-item.active {
    opacity: 1;
    pointer-events: auto;
}

.nexa-slide-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Overlay Badge on Flyer */
.nexa-promo-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 5;
    background: rgba(15, 23, 42, 0.78);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: var(--radius-pill);
    border: 1px solid rgba(255, 255, 255, 0.2);
    letter-spacing: 0.02em;
}

/* Carousel Pagination Dots */
.nexa-carousel-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin-top: 14px;
}

.nexa-dot {
    position: relative;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: none;
    background: var(--surface-input-hover);
    padding: 0;
    cursor: pointer;
    transition: all var(--timing-fast);
}

.nexa-dot.active {
    background: var(--nexa-primary);
    width: 22px;
    border-radius: var(--radius-pill);
}

/* ==========================================================================
   3. SUCCESS STATE (WITH ANIMATED CHECKMARK & PROGRESS)
========================================================================== */
.nexa-success-panel {
    display: none;
    padding: 48px 28px;
    text-align: center;
    width: 100%;
    animation: nexaFadeIn 0.35s ease;
}

@keyframes nexaFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.nexa-success-icon {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: #ecfdf5;
    color: var(--state-success);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25);
    border: 3px solid #d1fae5;
}

.nexa-success-icon svg {
    width: 40px;
    height: 40px;
}

.nexa-success-title {
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-heading);
    margin-bottom: 6px;
}

.nexa-success-sub {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 24px;
}

.nexa-progress-track {
    width: 100%;
    max-width: 260px;
    height: 6px;
    background: var(--surface-input);
    border-radius: var(--radius-pill);
    overflow: hidden;
    margin: 0 auto;
}

.nexa-progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--nexa-primary), var(--nexa-accent));
    border-radius: var(--radius-pill);
    transition: width 3.2s linear;
}

/* ==========================================================================
   4. TEMPLATE STUDIO FLOATING TOOLBAR (FOR ADMIN PREVIEW / SIMULATION)
========================================================================== */
.studio-bar {
    position: fixed;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.94);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 14px;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.55);
    z-index: 99999;
    width: min(640px, 94vw);
    max-width: 94vw;
}

.studio-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 800;
    color: #38bdf8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-right: 10px;
    border-right: 1px solid rgba(255, 255, 255, 0.12);
    flex-shrink: 0;
}

.studio-badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #38bdf8;
    box-shadow: 0 0 8px #38bdf8;
}

.studio-pills {
    display: flex;
    align-items: center;
    gap: 4px;
    overflow-x: auto;
    flex: 1;
    min-width: 0;
    scrollbar-width: none;
}
.studio-pills::-webkit-scrollbar { display: none; }

.studio-pill {
    padding: 5px 12px;
    border-radius: 8px;
    color: #cbd5e1;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all var(--timing-fast);
    background: transparent;
    border: none;
    white-space: nowrap;
    flex-shrink: 0;
}

.studio-pill:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.studio-pill.active {
    color: #fff;
    background: var(--nexa-primary);
    font-weight: 800;
    box-shadow: 0 2px 8px color-mix(in srgb, var(--nexa-primary) 50%, transparent);
}

.studio-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all var(--timing-fast);
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
}

.studio-btn-edit {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
    border: 1px solid rgba(255, 255, 255, 0.18);
}
.studio-btn-edit:hover { background: rgba(255, 255, 255, 0.2); color: #fff; }

/* ==========================================================================
   5. RESPONSIVE OPTIMIZATION (MOBILE CNA & SMARTPHONES)
========================================================================== */
@media (max-width: 768px) {
    .nexa-portal-canvas {
        padding: 16px 12px 32px;
        justify-content: flex-start;
        min-height: 100vh;
    }
    .nexa-modal-card {
        flex-direction: column;
        max-width: 410px;
        width: 100%;
        margin: 0 auto;
        min-height: auto;
        border-radius: 22px;
    }
    .nexa-card-left {
        padding: 26px 20px 20px;
        order: 1;
    }
    .nexa-status-pill {
        margin-bottom: 12px;
    }
    .nexa-brand-logo {
        width: 165px;
    }
    .nexa-intro-title {
        font-size: 1.15rem;
    }
    .nexa-card-right {
        order: 2;
        padding: 0 20px 22px;
        background: transparent;
        border-left: none;
        border-top: none;
    }
    .nexa-slider-wrap {
        max-width: 100%;
        width: 100%;
        height: 160px;
        aspect-ratio: auto;
        border-radius: 14px;
    }
    .nexa-slider-wrap img {
        object-fit: cover;
        object-position: center 20%;
    }
    .nexa-carousel-dots {
        margin-top: 10px;
    }
    .nexa-card-footer {
        margin-top: 14px;
        padding-top: 12px;
    }
    .studio-bar {
        bottom: 8px;
        padding: 6px 10px;
        gap: 8px;
    }
    .studio-badge {
        display: none;
    }
    .studio-pills {
        gap: 3px;
    }
    .studio-pill {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
}
</style>

@if(!empty($siteConfig['custom_css']))
<style id="custom-css-live">
{!! preg_replace('/<\s*\/?\s*(style|script)[^>]*>/i', '', $siteConfig['custom_css']) !!}
</style>
@endif
@endsection

@section('content')

<!-- Ambient Lighting Depth Overlays -->
<div class="nexa-viewport-overlay" aria-hidden="true"></div>
<div class="nexa-ambient-glow" aria-hidden="true"></div>

<!-- ============================================================
     IMMERSIVE CAPTIVE PORTAL CANVAS (ZERO TOPBAR)
============================================================ -->
<main id="nexa-login-modal" class="nexa-portal-canvas" role="main">
    <div class="nexa-modal-card" id="nexa-card-inner">

        <!-- LEFT PANEL: Brand Header, Form Inputs, Internet By Footer -->
        <div class="nexa-card-left" id="nexa-login-column">
            
            <!-- Online Hotspot Status Badge -->
            <div class="nexa-status-pill">
                <span class="nexa-pulse-dot" aria-hidden="true"></span>
                <span>{{ $siteConfig['brand_tagline'] ?? 'High-Speed Guest WiFi' }}</span>
            </div>

            <!-- Brand Logo & Instagram Handle -->
            <div class="nexa-brand-header">
                <img 
                    src="{{ $siteConfig['logo_url'] ?? '/images/nexa/logo-hotspot-color.png' }}" 
                    alt="{{ $siteConfig['brand_name'] ?? 'nexa Hotspot' }}" 
                    class="nexa-brand-logo"
                >
                <a href="https://instagram.com/{{ $siteConfig['instagram'] ?? 'nexanet.id' }}" target="_blank" rel="noopener" class="nexa-ig-badge" title="Kunjungi Instagram Resmi @nexanet.id">
                    <svg class="nexa-ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                    <span>{{ $siteConfig['instagram'] ?? 'nexanet.id' }}</span>
                </a>
            </div>

            <!-- Intro Headline & Subtitle -->
            <div class="nexa-intro-box">
                <h1 class="nexa-intro-title">
                    {{ $siteConfig['hero_title'] ?? ($allTemplates[$activeTemplate]['name'] ?? 'Input Access Code') }}
                </h1>
                <p class="nexa-intro-subtitle">
                    {{ $siteConfig['hero_subtitle'] ?? 'Masukkan kode akses atau voucher Anda untuk terhubung ke internet' }}
                </p>
            </div>

            <!-- Form Wrapper based on active login method -->
            <div class="nexa-form-wrap">

                {{-- METHOD 1: ACCESS CODE (Voucher) --}}
                @if($activeTemplate === 'access-code')
                <form id="voucher-form" onsubmit="submitVoucher(event)">
                    <div id="voucher-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <div class="nexa-pill-input-box">
                        <label for="voucher-code" class="sr-only">Input Access Code</label>
                        <input 
                            type="text" 
                            id="voucher-code" 
                            class="nexa-pill-input" 
                            placeholder="{{ $siteConfig['input_placeholder'] ?? 'Input Access Code' }}" 
                            required 
                            autofocus 
                            autocomplete="off" 
                            spellcheck="false"
                        >
                        <button type="submit" id="voucher-submit" class="nexa-circle-arrow-btn" aria-label="Validasi & Sambungkan Internet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- METHOD 2: USERNAME & PASSWORD (Member / Staff) --}}
                @elseif($activeTemplate === 'username-password')
                <form id="member-form" onsubmit="submitMember(event)">
                    <div id="member-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <div class="nexa-pill-input-box mb-3">
                        <label for="member-username" class="sr-only">Username</label>
                        <input 
                            type="text" 
                            id="member-username" 
                            class="nexa-pill-input" 
                            placeholder="Username" 
                            required 
                            autocomplete="username"
                        >
                    </div>
                    <div class="nexa-pill-input-box">
                        <label for="member-password" class="sr-only">Password</label>
                        <input 
                            type="password" 
                            id="member-password" 
                            class="nexa-pill-input" 
                            placeholder="Password" 
                            required 
                            autocomplete="current-password"
                        >
                        <button type="submit" id="member-submit" class="nexa-circle-arrow-btn" aria-label="Masuk Akun Member">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- METHOD 3: WHATSAPP LOGIN --}}
                @elseif($activeTemplate === 'whatsapp-login')
                <form id="wa-form" onsubmit="submitWhatsapp(event)">
                    <div id="wa-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <div class="nexa-pill-input-box mb-3">
                        <label for="wa-name" class="sr-only">Nama Lengkap</label>
                        <input 
                            type="text" 
                            id="wa-name" 
                            class="nexa-pill-input" 
                            placeholder="Nama Lengkap" 
                            required
                        >
                    </div>
                    <div class="nexa-pill-input-box">
                        <span class="nexa-pill-prefix" aria-hidden="true">+62</span>
                        <label for="wa-phone" class="sr-only">Nomor WhatsApp</label>
                        <input 
                            type="tel" 
                            id="wa-phone" 
                            class="nexa-pill-input" 
                            placeholder="8123456789" 
                            required
                        >
                        <button type="submit" id="wa-submit" class="nexa-circle-arrow-btn" aria-label="Kirim & Hubungkan WhatsApp">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- METHOD 4: BUTTON (1-Click Access) --}}
                @elseif($activeTemplate === 'button')
                <form id="quick-form" onsubmit="submitQuick(event)">
                    <div id="quick-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <button type="submit" id="quick-submit" class="nexa-submit-pill-btn">
                        <span>{{ $siteConfig['button_text'] ?? 'Hubungkan Internet Sekarang' }}</span>
                        <span class="nexa-circle-arrow-btn sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </button>
                </form>

                {{-- METHOD 5: EMAIL LOGIN --}}
                @elseif($activeTemplate === 'email')
                <form id="email-form" onsubmit="submitEmail(event)">
                    <div id="email-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <div class="nexa-pill-input-box mb-3">
                        <label for="email-name" class="sr-only">Nama Lengkap</label>
                        <input 
                            type="text" 
                            id="email-name" 
                            class="nexa-pill-input" 
                            placeholder="Nama Lengkap" 
                            required
                        >
                    </div>
                    <div class="nexa-pill-input-box">
                        <label for="email-address" class="sr-only">Alamat Email</label>
                        <input 
                            type="email" 
                            id="email-address" 
                            class="nexa-pill-input" 
                            placeholder="nama@email.com" 
                            required
                        >
                        <button type="submit" id="email-submit" class="nexa-circle-arrow-btn" aria-label="Kirim & Hubungkan Email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- METHOD 6: HOTEL PMS (Room Number & Guest Last Name) --}}
                @elseif($activeTemplate === 'hotel-pms')
                <form id="pms-form" onsubmit="submitPms(event)">
                    <div id="pms-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    <div class="nexa-pill-input-box mb-3">
                        <label for="pms-room" class="sr-only">Nomor Kamar</label>
                        <input 
                            type="text" 
                            id="pms-room" 
                            class="nexa-pill-input" 
                            placeholder="{{ $siteConfig['input_placeholder'] ?? 'Nomor Kamar (Contoh: 301)' }}" 
                            required 
                            autofocus 
                            autocomplete="off"
                        >
                    </div>
                    <div class="nexa-pill-input-box">
                        <label for="pms-lastname" class="sr-only">Nama Belakang Tamu</label>
                        <input 
                            type="text" 
                            id="pms-lastname" 
                            class="nexa-pill-input" 
                            placeholder="Nama Belakang Tamu" 
                            required 
                            autocomplete="family-name"
                        >
                        <button type="submit" id="pms-submit" class="nexa-circle-arrow-btn" aria-label="Verifikasi Tamu & Hubungkan Internet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- METHOD 7: QUESTION & SURVEY --}}
                @else
                <form id="survey-form" onsubmit="submitSurvey(event)">
                    <div id="survey-error" class="nexa-alert-error" style="display:none;" role="alert"></div>
                    
                    <div class="nexa-survey-box">
                        @forelse($questions as $q)
                        <div class="nexa-q-item">
                            <div class="nexa-q-title">
                                {{ $loop->iteration }}. {{ $q->question_text }}
                                @if($q->is_required)<span style="color:var(--state-error-text)" aria-hidden="true">*</span>@endif
                            </div>

                            @if($q->isChoice())
                            <div>
                                @foreach($q->options ?? [] as $opt)
                                <label class="nexa-q-opt">
                                    <input 
                                        type="{{ $q->question_type === 'single_choice' ? 'radio' : 'checkbox' }}" 
                                        name="answers[{{ $q->id }}]{{ $q->question_type === 'multiple_choice' ? '[]' : '' }}" 
                                        value="{{ $opt }}"
                                        {{ $q->is_required && $q->question_type === 'single_choice' ? 'required' : '' }}
                                    >
                                    <span>{{ $opt }}</span>
                                </label>
                                @endforeach
                            </div>
                            @elseif($q->isRating())
                            <div class="nexa-rating-row" id="rating-row-{{ $q->id }}">
                                @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="nexa-rating-btn" onclick="selectRating('{{ $q->id }}', {{ $i }}, this)">{{ $i }}</button>
                                @endfor
                            </div>
                            <input type="hidden" name="answers[{{ $q->id }}]" id="rating-val-{{ $q->id }}" value="">
                            @else
                            <div class="nexa-pill-input-box">
                                <input type="text" class="nexa-pill-input" name="answers[{{ $q->id }}]" placeholder="Jawaban..." {{ $q->is_required ? 'required' : '' }}>
                            </div>
                            @endif
                        </div>
                        @empty
                        <p style="font-size:0.8125rem;color:var(--text-muted);margin-bottom:12px;">Lengkapi data berikut untuk terhubung ke hotspot.</p>
                        @endforelse
                    </div>

                    <button type="submit" id="survey-submit" class="nexa-submit-pill-btn">
                        <span>{{ $siteConfig['button_text'] ?? 'Kirim & Aktifkan' }}</span>
                        <span class="nexa-circle-arrow-btn sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </button>
                </form>
                @endif

            </div>

            <!-- Terms & Privacy Notice -->
            <p class="nexa-tos-note">
                {{ $siteConfig['tos_text'] ?? 'Dengan melanjutkan, Anda menyetujui syarat & ketentuan jaringan ini.' }}
            </p>

            <!-- Footer: "Internet By [nexa]" -->
            <div class="nexa-card-footer">
                <span class="nexa-by-label">Internet By</span>
                <img 
                    src="{{ $siteConfig['logo_nexa'] ?? '/images/nexa/logo-nexa-color.png' }}" 
                    alt="nexa" 
                    class="nexa-by-logo"
                >
            </div>
        </div>

        <!-- RIGHT PANEL: Promotional Slider Banner & Showcase -->
        <div class="nexa-card-right" id="nexa-promo-column" style="{{ ($siteConfig['promo_enabled'] ?? true) ? '' : 'display:none;' }}">
            <div class="nexa-slider-wrap">
                <!-- Overlay Promo Tag -->
                <span class="nexa-promo-badge">{{ $siteConfig['promo_badge'] ?? 'Ultra-Fast Fiber' }}</span>

                <!-- Slide 1 -->
                <div class="nexa-slide-item active" id="nexa-slide-0">
                    <img src="{{ $siteConfig['promo_image'] ?? '/images/nexa/promo-slide-1.jpg' }}" alt="Promo Ultra High Speed WiFi">
                </div>
                <!-- Slide 2 -->
                <div class="nexa-slide-item" id="nexa-slide-1">
                    <img src="{{ $siteConfig['promo_image_2'] ?? '/images/nexa/promo-slide-2.jpg' }}" alt="Promo Premium Guest WiFi">
                </div>
            </div>

            <!-- Carousel Pagination Dots -->
            <div class="nexa-carousel-dots" role="tablist" aria-label="Slider Promo">
                <button type="button" class="nexa-dot active" onclick="setNexaSlide(0)" title="Slide 1" aria-label="Lihat Slide 1"></button>
                <button type="button" class="nexa-dot" onclick="setNexaSlide(1)" title="Slide 2" aria-label="Lihat Slide 2"></button>
            </div>
        </div>

        <!-- SUCCESS STATE (Overlays card upon connection) -->
        <div class="nexa-success-panel" id="nexa-success-panel">
            <div class="nexa-success-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="nexa-success-title" id="nexa-success-title">Koneksi Berhasil!</h2>
            <p class="nexa-success-sub" id="nexa-success-msg">Internet Anda sedang diaktifkan...</p>
            <div class="nexa-progress-track">
                <div class="nexa-progress-fill" id="nexa-progress-bar"></div>
            </div>
            <div id="nexa-success-actions" style="display:none; margin-top: 24px; width: 100%;">
                <a href="{{ $linkOrig ?: 'https://www.google.com' }}" id="nexa-browse-btn" class="nexa-btn nexa-btn-primary" style="display: flex; justify-content: center; align-items: center; text-decoration: none; width: 100%; padding: 14px; font-weight: 700; border-radius: 12px; gap: 8px;">
                    <span>Mulai Jelajahi Internet</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>
</main>

<!-- Hidden MikroTik redirect form -->
<form id="mikrotik-form" method="POST" action="{{ $linkLogin }}" style="display:none">
    <input type="hidden" name="username" id="mt-username" value="">
    <input type="hidden" name="password" id="mt-password" value="">
    <input type="hidden" name="dst" value="{{ $linkOrig }}">
    <input type="hidden" name="popup" value="true">
</form>

<!-- ============================================================
     TEMPLATE STUDIO FLOATING BAR (For Preview / Simulation)
============================================================ -->
@if(($isSimulation || request()->has('preview')) && !request()->has('embed'))
<div id="wifipads-studio-bar" class="studio-bar">
    <div class="studio-badge">
        <span class="studio-badge-dot"></span>
        <span>Template Studio</span>
    </div>

    <div class="studio-pills">
        @foreach($allTemplates as $tKey => $tItem)
        <a 
            href="{{ route('portal', ['loc' => $location?->slug, 'preview_template' => $tKey, 'preview' => 1]) }}"
            class="studio-pill {{ $activeTemplate === $tKey ? 'active' : '' }}"
            title="{{ $tItem['description'] }}"
        >
            {{ $tItem['name'] }}
        </a>
        @endforeach
    </div>

    <a 
        href="{{ route('admin.sites.template.customizer', $location->id ?? 1) }}?template={{ $activeTemplate }}" 
        target="_blank" 
        class="studio-btn studio-btn-edit"
        style="display: inline-flex; align-items: center; gap: 5px;"
    >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <span>Edit Konten</span>
    </a>
</div>
@endif

@endsection

@section('scripts')
<script>
// ============================================================
// NEXA HOTSPOT CLIENT LOGIC & CNA COMPATIBILITY
// ============================================================
var PORTAL_DATA = {
    mac:          "{{ addslashes($mac) }}",
    ip:           "{{ addslashes($ip) }}",
    locationId:   "{{ addslashes($location?->id ?? '') }}",
    campaignId:   "{{ addslashes($campaign?->id ?? '') }}",
    linkLogin:    "{{ addslashes($linkLogin) }}",
    isSimulation: {{ $isSimulation ? 'true' : 'false' }},
};

// Modal Open / Close Handlers (maintained for simulator & event bus)
function openLoginModal() {
    var modal = document.getElementById('nexa-login-modal');
    if (modal) modal.style.display = 'flex';
}

function closeLoginModal() {
    var modal = document.getElementById('nexa-login-modal');
    if (modal) modal.style.display = 'none';
}

function toggleLoginModal() {
    var modal = document.getElementById('nexa-login-modal');
    if (modal) {
        modal.style.display = (modal.style.display === 'none') ? 'flex' : 'none';
    }
}

// Carousel Banner Slider Logic
var currentSlide = 0;
var totalSlides = 2;
var slideTimer = null;

function setNexaSlide(index) {
    currentSlide = index;
    for (var i = 0; i < totalSlides; i++) {
        var slide = document.getElementById('nexa-slide-' + i);
        var dots = document.querySelectorAll('.nexa-carousel-dots .nexa-dot');
        if (slide) {
            slide.classList.toggle('active', i === index);
        }
        if (dots && dots[i]) {
            dots[i].classList.toggle('active', i === index);
        }
    }
}

function startSlideTimer() {
    if (slideTimer) clearInterval(slideTimer);
    slideTimer = setInterval(function() {
        currentSlide = (currentSlide + 1) % totalSlides;
        setNexaSlide(currentSlide);
    }, 5000);
}

startSlideTimer();

// Pause slider on hover
var sliderWrap = document.querySelector('.nexa-slider-wrap');
if (sliderWrap) {
    sliderWrap.addEventListener('mouseenter', function() {
        if (slideTimer) clearInterval(slideTimer);
    });
    sliderWrap.addEventListener('mouseleave', function() {
        startSlideTimer();
    });
}

// Survey Rating Helper
function selectRating(qId, value, btn) {
    var row = document.getElementById('rating-row-' + qId);
    if (row) {
        var btns = row.querySelectorAll('.nexa-rating-btn');
        btns.forEach(function(b) { b.classList.remove('selected'); });
        btn.classList.add('selected');
    }
    var input = document.getElementById('rating-val-' + qId);
    if (input) input.value = value;
}

// AJAX Helper via XMLHttpRequest for CNA Compatibility
function ajaxPost(url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var resp = JSON.parse(xhr.responseText);
                callback(null, resp, xhr.status);
            } catch(e) {
                callback(e, null, xhr.status);
            }
        }
    };
    xhr.onerror = function() { callback(new Error('Network error'), null, 0); };
    xhr.send(JSON.stringify(data));
}

function setLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        btn.dataset.origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></span>';
    } else {
        btn.innerHTML = btn.dataset.origHtml || '';
        btn.disabled = false;
    }
}

function showError(errorEl, msg) {
    if (!errorEl) return;
    errorEl.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>' + msg + '</span>';
    errorEl.style.display = 'flex';
}

function hideError(errorEl) {
    if (errorEl) errorEl.style.display = 'none';
}

// Success State and MikroTik Redirection
function showSuccess(username, password, message, title, activated) {
    var leftCol = document.getElementById('nexa-login-column');
    var rightCol = document.getElementById('nexa-promo-column');
    var successPanel = document.getElementById('nexa-success-panel');
    var titleEl = document.getElementById('nexa-success-title');
    var msgEl = document.getElementById('nexa-success-msg');
    var bar = document.getElementById('nexa-progress-bar');
    var actions = document.getElementById('nexa-success-actions');
    var browseBtn = document.getElementById('nexa-browse-btn');

    if (leftCol) leftCol.style.display = 'none';
    if (rightCol) rightCol.style.display = 'none';
    if (successPanel) successPanel.style.display = 'block';

    if (titleEl) titleEl.textContent = title || 'Koneksi Berhasil! 🎉';
    if (msgEl) msgEl.textContent = message || 'Internet Anda sedang diaktifkan...';

    var targetUrl = (PORTAL_DATA.linkOrig && PORTAL_DATA.linkOrig !== 'http://google.com') 
        ? PORTAL_DATA.linkOrig 
        : 'https://www.google.com';
    if (browseBtn) browseBtn.href = targetUrl;

    setTimeout(function() {
        if (bar) bar.style.width = '100%';
    }, 100);

    // After progress animation (1.6s)
    setTimeout(function() {
        if (titleEl) titleEl.textContent = 'Internet Anda Sudah Aktif! 🚀';
        if (msgEl) msgEl.textContent = 'Perangkat Anda telah terhubung ke jaringan internet. Selamat berselancar!';
        if (actions) actions.style.display = 'block';

        // Seamless auto-redirection if not in simulation mode
        if (!PORTAL_DATA.isSimulation) {
            setTimeout(function() {
                try {
                    window.location.href = targetUrl;
                } catch (e) {}
            }, 2500);
        }
    }, 1600);

    // Complementary fallback: Submit hidden form to MikroTik router (if supported by client)
    if (!PORTAL_DATA.isSimulation && PORTAL_DATA.linkLogin && PORTAL_DATA.linkLogin !== '#simulation') {
        var mtUser = document.getElementById('mt-username');
        var mtPass = document.getElementById('mt-password');
        if (mtUser) mtUser.value = username;
        if (mtPass) mtPass.value = password;

        setTimeout(function() {
            try {
                document.getElementById('mikrotik-form').submit();
            } catch (err) {
                console.warn('Form submit fallback skipped or blocked by browser:', err);
            }
        }, 1200);
    }
}

// 1. Submit Voucher / Access Code
function submitVoucher(event) {
    event.preventDefault();
    var code = document.getElementById('voucher-code').value.trim().toUpperCase();
    var btn = document.getElementById('voucher-submit');
    var err = document.getElementById('voucher-error');

    if (!code) {
        showError(err, 'Masukkan kode akses terlebih dahulu.');
        return;
    }

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/voucher', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        code: code
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Kode akses tidak valid.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Kode akses valid (' + (resp.duration || 60) + ' menit). Selamat menikmati internet!', 'Koneksi Terbuka', resp.activated);
    });
}

// 2. Submit Member
function submitMember(event) {
    event.preventDefault();
    var u = document.getElementById('member-username').value.trim();
    var p = document.getElementById('member-password').value;
    var btn = document.getElementById('member-submit');
    var err = document.getElementById('member-error');

    if (!u || !p) {
        showError(err, 'Username dan password harus diisi.');
        return;
    }

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/member', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        username: u,
        password: p
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Username atau password salah.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Login akun berhasil. Selamat datang, ' + u, 'Login Berhasil', resp.activated);
    });
}

// 3. Submit WhatsApp
function submitWhatsapp(event) {
    event.preventDefault();
    var name = document.getElementById('wa-name').value.trim();
    var phone = document.getElementById('wa-phone').value.trim();
    var btn = document.getElementById('wa-submit');
    var err = document.getElementById('wa-error');

    if (!phone) {
        showError(err, 'Masukkan nomor WhatsApp Anda.');
        return;
    }

    var cleanPhone = phone.replace(/^0+/, '').replace(/^\+62/, '');
    cleanPhone = '+62' + cleanPhone;

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/whatsapp', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        name: name || 'Guest WA',
        phone: cleanPhone
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Gagal memproses WhatsApp login.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Nomor terverifikasi. Sesi internet Anda aktif!', 'WhatsApp Terhubung', resp.activated);
    });
}

// 4. Submit Quick (1-Click)
function submitQuick(event) {
    event.preventDefault();
    var btn = document.getElementById('quick-submit');
    var err = document.getElementById('quick-error');

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/quick', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Gagal menyambungkan.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Koneksi 1-Click aktif. Selamat berinternet!', 'Terhubung!', resp.activated);
    });
}

// 5. Submit Email
function submitEmail(event) {
    event.preventDefault();
    var name = document.getElementById('email-name').value.trim();
    var email = document.getElementById('email-address').value.trim();
    var btn = document.getElementById('email-submit');
    var err = document.getElementById('email-error');

    if (!email) {
        showError(err, 'Masukkan alamat email Anda.');
        return;
    }

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/email', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        name: name || 'Guest User',
        email: email
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Gagal mendaftarkan email.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Email diverifikasi. Akses internet Anda telah aktif!', 'Selamat Datang', resp.activated);
    });
}

// 6. Submit Survey
function submitSurvey(event) {
    event.preventDefault();
    var btn = document.getElementById('survey-submit');
    var err = document.getElementById('survey-error');
    var form = document.getElementById('survey-form');

    var answers = {};
    var inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(function(el) {
        if (!el.name || !el.name.startsWith('answers[')) return;
        if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;

        var match = el.name.match(/answers\[([^\]]+)\](\[\])?/);
        if (!match) return;
        var qid = match[1];
        var isArr = !!match[2];

        if (isArr) {
            if (!answers[qid]) answers[qid] = [];
            answers[qid].push(el.value);
        } else {
            answers[qid] = el.value;
        }
    });

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/survey', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        campaign_id: PORTAL_DATA.campaignId,
        answers: answers
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Gagal mengirim survei.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Terima kasih atas jawaban Anda! Internet Anda telah aktif.', 'Survei Terkirim', resp.activated);
    });
}

// 7. Submit Hotel PMS (Room Number & Guest Last Name)
function submitPms(event) {
    event.preventDefault();
    var room = document.getElementById('pms-room').value.trim();
    var lastName = document.getElementById('pms-lastname').value.trim();
    var btn = document.getElementById('pms-submit');
    var err = document.getElementById('pms-error');

    if (!room || !lastName) {
        showError(err, 'Nomor kamar dan nama belakang harus diisi.');
        return;
    }

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/pms', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        room_number: room,
        last_name: lastName
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Terjadi kesalahan jaringan saat verifikasi PMS.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Data kamar atau nama belakang tidak cocok dengan data check-in.');
            return;
        }
        showSuccess(resp.username, resp.password, 'Selamat datang, ' + (resp.guest_name || lastName) + '! Akses internet Kamar ' + room + ' telah aktif.', 'Kamar Terverifikasi', resp.activated);
    });
}

// ============================================================
// LIVE PREVIEW SYNCHRONIZATION (FOR ADMIN CUSTOMIZER SIMULATOR)
// ============================================================
window.addEventListener('message', function(event) {
    if (event.origin && event.origin !== window.location.origin) return;
    if (!event.data) return;

    // 1. Live Configuration Update
    if (event.data.type === 'NEXA_LIVE_UPDATE' && event.data.config) {
        var cfg = event.data.config;
        var root = document.documentElement;

        // Primary & Theme Colors
        if (cfg.primary_color) {
            root.style.setProperty('--nexa-primary', cfg.primary_color);
            root.style.setProperty('--nexa-primary-hover', 'color-mix(in srgb, ' + cfg.primary_color + ' 85%, black)');
            root.style.setProperty('--nexa-primary-ring', 'color-mix(in srgb, ' + cfg.primary_color + ' 24%, transparent)');
            root.style.setProperty('--shadow-btn', '0 4px 14px ' + cfg.primary_color + '66');
            root.style.setProperty('--shadow-btn-hover', '0 8px 22px ' + cfg.primary_color + '99');
        }
        if (cfg.accent_color || cfg.topbar_color) {
            root.style.setProperty('--nexa-accent', cfg.accent_color || cfg.topbar_color);
        }

        // Background Styling
        if (cfg.bg_value) {
            var bgVal = cfg.bg_value.trim();
            if (cfg.bg_type === 'color' || cfg.bg_type === 'gradient') {
                document.body.style.background = bgVal;
            } else {
                var bgUrl = bgVal.startsWith('url(') || bgVal.startsWith('data:') ? bgVal : "url('" + bgVal + "')";
                if (bgUrl.startsWith('data:')) bgUrl = "url('" + bgUrl + "')";
                document.body.style.background = "#090d16 " + bgUrl + " center center / cover no-repeat fixed";
            }
        }

        // Logos
        if (cfg.logo_url) {
            var brandLogo = document.querySelector('.nexa-brand-logo');
            if (brandLogo) brandLogo.src = cfg.logo_url;
        }

        // Brand Name
        if (cfg.brand_name) {
            var bLogos = document.querySelectorAll('.nexa-brand-logo');
            bLogos.forEach(function(el) { el.alt = cfg.brand_name; });
        }

        // Intro Title & Subtitle
        if (cfg.hero_title || cfg.topbar_title) {
            var titleEl = document.querySelector('.nexa-intro-title');
            if (titleEl) titleEl.textContent = cfg.hero_title || cfg.topbar_title;
        }
        if (cfg.hero_subtitle) {
            var subEl = document.querySelector('.nexa-intro-subtitle');
            if (subEl) subEl.textContent = cfg.hero_subtitle;
        }
        if (cfg.brand_tagline) {
            var tagEl = document.querySelector('.nexa-status-pill span:last-child');
            if (tagEl) tagEl.textContent = cfg.brand_tagline;
        }

        // Instagram Handle
        if (cfg.instagram !== undefined) {
            var igBadge = document.querySelector('.nexa-ig-badge');
            var igSpan = document.querySelector('.nexa-ig-badge span');
            var igHandle = cfg.instagram || 'nexanet.id';
            if (igSpan) igSpan.textContent = igHandle;
            if (igBadge) {
                igBadge.href = 'https://instagram.com/' + igHandle;
                igBadge.title = 'Kunjungi Instagram Resmi @' + igHandle;
            }
        }

        // Input Placeholder
        if (cfg.input_placeholder) {
            var voucherIn = document.getElementById('voucher-code');
            if (voucherIn) voucherIn.placeholder = cfg.input_placeholder;
        }

        // Action Button Text (if submit button contains text)
        if (cfg.button_text) {
            var pillBtn = document.querySelector('.nexa-submit-pill-btn');
            if (pillBtn) {
                var firstSpan = pillBtn.querySelector('span');
                if (firstSpan) firstSpan.textContent = cfg.button_text;
            }
        }

        // Promo Column & Images
        var promoCol = document.getElementById('nexa-promo-column');
        if (promoCol && cfg.promo_enabled !== undefined) {
            promoCol.style.display = cfg.promo_enabled ? 'flex' : 'none';
        }
        if (cfg.promo_badge) {
            var pBadge = document.querySelector('.nexa-promo-badge');
            if (pBadge) pBadge.textContent = cfg.promo_badge;
        }
        if (cfg.promo_image) {
            var img0 = document.querySelector('#nexa-slide-0 img');
            if (img0) img0.src = cfg.promo_image;
        }
        if (cfg.promo_image_2) {
            var img1 = document.querySelector('#nexa-slide-1 img');
            if (img1) img1.src = cfg.promo_image_2;
        }

        // Custom CSS
        if (cfg.custom_css !== undefined) {
            var liveCss = document.getElementById('nexa-live-css-injection');
            if (!liveCss) {
                liveCss = document.createElement('style');
                liveCss.id = 'nexa-live-css-injection';
                document.head.appendChild(liveCss);
            }
            liveCss.textContent = cfg.custom_css;
        }
    }

    // 2. Interactive Dialog Controls
    if (event.data.type === 'TOGGLE_MODAL') {
        toggleLoginModal();
    } else if (event.data.type === 'OPEN_MODAL') {
        openLoginModal();
    } else if (event.data.type === 'CLOSE_MODAL') {
        closeLoginModal();
    }
});

// Notify parent simulator that iframe is ready
if (window.parent && window.parent !== window) {
    window.parent.postMessage({ type: 'NEXA_PORTAL_READY' }, '*');
}
</script>
@endsection
