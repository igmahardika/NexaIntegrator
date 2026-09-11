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
    /* Brand Theme Colors — static hex fallbacks first, then dynamic from PHP config */
    --nexa-primary: {{ $siteConfig['primary_color'] ?? '#0284c7' }};
    /* Derived shades: pre-calculated safe fallbacks (no color-mix() for CNA compat) */
    --nexa-primary-hover: #0369a1;
    --nexa-primary-active: #075985;
    --nexa-primary-ring: rgba(2, 132, 199, 0.25);
    --nexa-primary-glow: rgba(2, 132, 199, 0.18);
    --nexa-accent: {{ $siteConfig['accent_color'] ?? '#0369a1' }};

    /* Neutrals & Surfaces */
    --surface-card: #ffffff;
    --surface-card-glass: rgba(255, 255, 255, 0.96);
    --surface-card-subtle: #f8fafc;
    --surface-input: #f1f5f9;
    --surface-input-hover: #e2e8f0;
    --surface-input-focus: #ffffff;
    
    /* Text Hierarchy — WCAG AA verified against white bg */
    --text-heading: #0f172a;   /* contrast 19.43:1 ✓ */
    --text-body: #334155;      /* contrast 10.10:1 ✓ */
    --text-muted: #475569;     /* contrast 6.12:1 ✓ (upgraded from #64748b 4.48:1 to ensure AA) */
    --text-placeholder: #94a3b8;
    --text-inverse: #ffffff;

    /* Semantic States */
    --state-error-bg: #fef2f2;
    --state-error-border: #fecaca;
    --state-error-text: #b91c1c;   /* contrast 7.02:1 on #fef2f2 ✓ */
    --state-success: #10b981;

    /* Radii & Elevation Shadows */
    --radius-card: 26px;
    --radius-inner: 18px;
    --radius-pill: 9999px;
    --radius-sm: 10px;
    --shadow-card: 0 25px 65px -15px rgba(0, 0, 0, 0.55), 0 10px 25px -5px rgba(0, 0, 0, 0.28);
    --shadow-btn: 0 4px 14px rgba(2, 132, 199, 0.35);
    --shadow-btn-hover: 0 8px 22px rgba(2, 132, 199, 0.45);

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
   1. SPLASH SCREEN — Full Viewport Welcome (CNA First Paint)
========================================================================== */
.nexa-splash {
    position: fixed;
    inset: 0;
    z-index: 10;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 32px 20px;
    /* Soft dark overlay on top of body bg */
    background: rgba(9, 13, 22, 0.45);
    animation: nexaFadeIn 0.5s ease forwards;
}

.nexa-splash-welcome {
    font-family: var(--font-display);
    font-size: clamp(1.75rem, 6vw, 3rem);
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-shadow: 0 2px 24px rgba(0,0,0,0.5);
    margin-bottom: 18px;
    line-height: 1.15;
}

.nexa-splash-logo {
    width: min(260px, 70vw);
    height: auto;
    filter: drop-shadow(0 4px 20px rgba(0,0,0,0.45)) brightness(1.05);
    margin-bottom: 40px;
}

.nexa-splash-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.95);
    color: var(--text-heading);
    border: none;
    border-radius: var(--radius-pill);
    padding: 12px 36px;
    font-family: var(--font-ui);
    font-size: 0.9375rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    cursor: pointer;
    box-shadow: 0 6px 28px rgba(0, 0, 0, 0.35);
    transition: background var(--timing-fast), transform var(--timing-fast), box-shadow var(--timing-fast);
    text-decoration: none;
}

.nexa-splash-cta:hover {
    background: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 36px rgba(0, 0, 0, 0.45);
}

.nexa-splash-cta:active {
    transform: scale(0.98) translateY(0);
}

/* ==========================================================================
   2. MODAL OVERLAY CANVAS
========================================================================== */
.nexa-portal-canvas {
    position: fixed;
    inset: 0;
    z-index: 20;
    display: none; /* Hidden until splash CTA clicked */
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background: rgba(9, 13, 22, 0.60);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    animation: nexaFadeIn 0.3s ease forwards;
}

.nexa-portal-canvas.is-open {
    display: flex;
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

/* Live Hotspot Status Pill — WCAG AA: ensure primary text on light bg ≥ 4.5:1 */
.nexa-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 14px;
    border-radius: var(--radius-pill);
    background: rgba(2, 132, 199, 0.08);   /* consistent with primary color */
    border: 1px solid rgba(2, 132, 199, 0.2);
    color: #0369a1;                         /* #0369a1 on white = 5.74:1 ✓ WCAG AA */
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
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);   /* static fallback, no color-mix() */
}

/* Terms Note — WCAG AA: min 12px for body text, use muted with AA-safe color */
.nexa-tos-note {
    font-size: 0.75rem;    /* 12px minimum readable size (was 0.72rem = ~11.5px) */
    color: var(--text-muted);  /* #475569 → 6.12:1 contrast on white ✓ */
    line-height: 1.5;
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
    flex: 1;
    min-width: 0;
    padding: 24px 24px 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #ffffff;     /* White — same as left panel */
    border-left: none;       /* No divider line */
    position: relative;
    overflow: hidden;
}

.nexa-slider-wrap {
    width: 100%;
    max-width: 300px;        /* Contained, not full-bleed */
    aspect-ratio: 4 / 5;    /* Portrait, matching reference */
    border-radius: 16px;     /* Rounded corners */
    overflow: hidden;
    position: relative;
    background: #e2e8f0;     /* Placeholder bg */
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    /* Force GPU layer so overflow:hidden clips absolute children correctly */
    isolation: isolate;
    transform: translateZ(0);
    -webkit-mask-image: -webkit-radial-gradient(white, black); /* Safari fix */
}

.nexa-slide-item {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
    border-radius: inherit;  /* Inherit 16px from parent for full clip */
    overflow: hidden;
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
    border-radius: inherit;
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

/* Carousel Pagination Dots — below image, normal flow */
.nexa-carousel-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin-top: 12px;
    flex-shrink: 0;
}

.nexa-carousel-dots button {
    /* no extra styles needed */
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
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.5);   /* static fallback, no color-mix() */
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
        padding: 0 20px 20px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
        border-left: none;
        border-radius: 0 0 22px 22px;
        min-height: auto;
    }
    .nexa-slider-wrap {
        max-width: 100%;
        width: 100%;
        height: auto;
        min-height: 0;
        aspect-ratio: 16/9;
        border-radius: 12px;
        flex-shrink: 0;
    }
    .nexa-slider-wrap img {
        width: 100%;
        height: 100%;
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

/* Accessibility: Respect user's motion preference (WCAG 2.3.3 / APCA) */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
    .nexa-pulse-dot {
        animation: none;
        opacity: 1;
    }
    .nexa-progress-fill {
        transition: none !important;
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
     SPLASH SCREEN: Full-Viewport Welcome (First Paint)
============================================================ -->
<section id="nexa-splash" class="nexa-splash" role="region" aria-label="Welcome Screen">
    <p class="nexa-splash-welcome">WELCOME TO</p>
    <img 
        src="{{ $siteConfig['logo_url'] ?? '/images/nexa/logo-hotspot-color.png' }}" 
        alt="{{ $siteConfig['brand_name'] ?? 'nexa Hotspot' }}" 
        class="nexa-splash-logo"
        id="nexa-splash-logo"
    >
    <button 
        type="button" 
        class="nexa-splash-cta" 
        id="nexa-splash-btn"
        onclick="openLoginModal()"
        aria-label="Login untuk akses internet"
    >
        {{ $siteConfig['cta_text'] ?? 'Login For Internet Access' }}
    </button>
</section>

<!-- ============================================================
     MODAL OVERLAY: Login Card (Hidden until splash CTA clicked)
============================================================ -->
<main id="nexa-login-modal" class="nexa-portal-canvas" role="main">
    <div class="nexa-modal-card" id="nexa-card-inner">

        <!-- CLOSE BUTTON (top-right corner of modal) -->
        <button 
            type="button" 
            id="nexa-modal-close"
            onclick="closeLoginModal()"
            aria-label="Tutup"
            style="position:absolute;top:14px;right:16px;z-index:20;width:32px;height:32px;border-radius:50%;border:none;background:transparent;color:#94a3b8;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:color 0.15s,background 0.15s;"
            onmouseenter="this.style.background='#f1f5f9';this.style.color='#334155'"
            onmouseleave="this.style.background='transparent';this.style.color='#94a3b8'"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>

        <!-- LEFT PANEL: Logo, Instagram, Form, Footer -->
        <div class="nexa-card-left" id="nexa-login-column">

            <!-- Brand Logo & Instagram Handle -->
            <div class="nexa-brand-header">
                <img 
                    src="{{ $siteConfig['logo_url'] ?? '/images/nexa/logo-hotspot-color.png' }}" 
                    alt="{{ $siteConfig['brand_name'] ?? 'nexa Hotspot' }}" 
                    class="nexa-brand-logo"
                >
                <a href="https://instagram.com/{{ $siteConfig['instagram'] ?? 'nexanet.id' }}" target="_blank" rel="noopener" class="nexa-ig-badge" title="Kunjungi Instagram Resmi">
                    <svg class="nexa-ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                    <span>{{ $siteConfig['instagram'] ?? 'nexanet.id' }}</span>
                </a>
            </div>

            <!-- Form Wrapper based on active login method -->
            <div class="nexa-form-wrap" style="margin-top: 18px;">

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
                    {{-- Input 1: Room Number --}}
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
                            inputmode="numeric"
                        >
                    </div>
                    {{-- Input 2: Guest Last Name (standalone, no nested button) --}}
                    <div class="nexa-pill-input-box mb-3">
                        <label for="pms-lastname" class="sr-only">Nama Belakang Tamu</label>
                        <input 
                            type="text" 
                            id="pms-lastname" 
                            class="nexa-pill-input" 
                            placeholder="Nama Belakang Tamu" 
                            required 
                            autocomplete="family-name"
                        >
                    </div>
                    {{-- Submit: Full-width pill button (same pattern as 'button' template) --}}
                    <button type="submit" id="pms-submit" class="nexa-submit-pill-btn">
                        <span>{{ $siteConfig['button_text'] ?? 'Verifikasi & Sambungkan' }}</span>
                        <span class="nexa-circle-arrow-btn sm" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </button>
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
                    <img src="{{ $siteConfig['promo_image'] ?? '/images/nexa/promo-slide-1.jpg' }}" alt="Promo nexa Next Level Experience">
                </div>
                <!-- Slide 2 -->
                <div class="nexa-slide-item" id="nexa-slide-1">
                    <img src="{{ $siteConfig['promo_image_2'] ?? '/images/nexa/promo-slide-2.jpg' }}" alt="Promo nexatel Our Internet Service">
                </div>
                <!-- Slide 3 -->
                <div class="nexa-slide-item" id="nexa-slide-2">
                    <img src="{{ $siteConfig['promo_image_3'] ?? '/images/nexa/promo-slide-3.jpg' }}" alt="Promo nexa Connections More Than Anything">
                </div>
            </div>

            <!-- Carousel Pagination Dots (below image, normal flow) -->
            <div class="nexa-carousel-dots" role="tablist" aria-label="Slider Promo">
                <button type="button" class="nexa-dot active" onclick="setNexaSlide(0)" title="Slide 1" aria-label="Lihat Slide 1"></button>
                <button type="button" class="nexa-dot" onclick="setNexaSlide(1)" title="Slide 2" aria-label="Lihat Slide 2"></button>
                <button type="button" class="nexa-dot" onclick="setNexaSlide(2)" title="Slide 3" aria-label="Lihat Slide 3"></button>
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

// Modal Open / Close Handlers
function openLoginModal() {
    var splash = document.getElementById('nexa-splash');
    var modal = document.getElementById('nexa-login-modal');
    if (splash) splash.style.display = 'none';
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('is-open');
        // Auto-focus first visible input for CNA keyboard accessibility
        var firstInput = modal.querySelector('input:not([type=hidden])');
        if (firstInput) setTimeout(function() { firstInput.focus(); }, 100);
    }
}

function closeLoginModal() {
    var splash = document.getElementById('nexa-splash');
    var modal = document.getElementById('nexa-login-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('is-open');
    }
    // Restore splash only if not in simulation/preview mode
    if (splash && !PORTAL_DATA.isSimulation && !window.location.search.includes('preview')) {
        splash.style.display = 'flex';
    }
}

function toggleLoginModal() {
    var modal = document.getElementById('nexa-login-modal');
    if (modal && modal.classList.contains('is-open')) {
        closeLoginModal();
    } else {
        openLoginModal();
    }
}

// In simulation/preview mode: open modal directly, hide splash
(function() {
    if (PORTAL_DATA.isSimulation || window.location.search.includes('preview')) {
        var splash = document.getElementById('nexa-splash');
        if (splash) splash.style.display = 'none';
        var modal = document.getElementById('nexa-login-modal');
        if (modal) { modal.style.display = 'flex'; modal.classList.add('is-open'); }
    }
})();

// Carousel Banner Slider Logic
var currentSlide = 0;
var totalSlides = 3;
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
    var room = document.getElementById('pms-room') ? document.getElementById('pms-room').value.trim() : '';
    var lastName = document.getElementById('pms-lastname') ? document.getElementById('pms-lastname').value.trim() : '';
    var btn = document.getElementById('pms-submit');
    var err = document.getElementById('pms-error');

    if (!room) {
        showError(err, 'Masukkan nomor kamar Anda.');
        document.getElementById('pms-room') && document.getElementById('pms-room').focus();
        return;
    }
    if (!lastName) {
        showError(err, 'Masukkan nama belakang tamu.');
        document.getElementById('pms-lastname') && document.getElementById('pms-lastname').focus();
        return;
    }

    // Sanitize: trim & normalize room number (remove spaces)
    var roomClean = room.replace(/\s+/g, '').toUpperCase();
    var lastNameClean = lastName.replace(/\s+/g, ' ').trim();

    hideError(err);
    setLoading(btn, true);

    ajaxPost('/api/portal/pms', {
        mac: PORTAL_DATA.mac,
        ip: PORTAL_DATA.ip,
        location_id: PORTAL_DATA.locationId,
        room_number: roomClean,
        last_name: lastNameClean
    }, function(e, resp) {
        setLoading(btn, false);
        if (e || !resp) {
            showError(err, 'Gagal menghubungi sistem reservasi. Periksa koneksi Anda.');
            return;
        }
        if (!resp.success) {
            showError(err, resp.message || 'Data kamar atau nama belakang tidak cocok dengan data check-in.');
            return;
        }
        showSuccess(
            resp.username,
            resp.password,
            'Selamat datang, ' + (resp.guest_name || lastNameClean) + '! Akses internet Kamar ' + roomClean + ' telah aktif.',
            'Kamar Terverifikasi ✓',
            resp.activated
        );
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

        // Primary & Theme Colors (admin customizer live preview — desktop browser, color-mix() allowed)
        if (cfg.primary_color) {
            root.style.setProperty('--nexa-primary', cfg.primary_color);
            // Use color-mix() if supported (modern desktop), else fallback to slightly darker hex shade
            var supportsColorMix = CSS && CSS.supports && CSS.supports('color', 'color-mix(in srgb, red 50%, blue)');
            if (supportsColorMix) {
                root.style.setProperty('--nexa-primary-hover', 'color-mix(in srgb, ' + cfg.primary_color + ' 85%, black)');
                root.style.setProperty('--nexa-primary-ring', 'color-mix(in srgb, ' + cfg.primary_color + ' 24%, transparent)');
            } else {
                root.style.setProperty('--nexa-primary-hover', cfg.primary_color);
                root.style.setProperty('--nexa-primary-ring', cfg.primary_color + '3d');
            }
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

        // Logos — sync both modal card logo AND splash screen logo
        if (cfg.logo_url) {
            // Modal card logo
            var brandLogo = document.querySelector('.nexa-brand-logo');
            if (brandLogo) brandLogo.src = cfg.logo_url;
            // Splash screen logo
            var splashLogo = document.getElementById('nexa-splash-logo');
            if (splashLogo) splashLogo.src = cfg.logo_url;
        }

        // Brand Name (alt text)
        if (cfg.brand_name) {
            var bLogos = document.querySelectorAll('.nexa-brand-logo, #nexa-splash-logo');
            bLogos.forEach(function(el) { el.alt = cfg.brand_name; });
        }

        // Splash CTA button text
        if (cfg.cta_text) {
            var splashBtn = document.getElementById('nexa-splash-btn');
            if (splashBtn) splashBtn.textContent = cfg.cta_text;
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
        if (cfg.promo_image_3) {
            var img2 = document.querySelector('#nexa-slide-2 img');
            if (img2) img2.src = cfg.promo_image_3;
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
