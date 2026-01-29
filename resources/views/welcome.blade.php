<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontak - CRM collaboratif pour équipes ambitieuses</title>
    <meta name="description" content="Gérez vos contacts, suivez votre pipeline commercial et collaborez en équipe avec Kontak, le CRM moderne et intuitif.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg: #ffffff;
            --color-bg-subtle: #f8fafc;
            --color-bg-muted: #f1f5f9;
            --color-primary: #1e3a5f;
            --color-primary-light: #2d5a8a;
            --color-accent: #3b82f6;
            --color-accent-light: #60a5fa;
            --color-text: #0f172a;
            --color-text-muted: #475569;
            --color-text-subtle: #94a3b8;
            --color-border: #e2e8f0;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 12px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navigation */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-border);
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--color-text);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-family: var(--font-body);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-ghost {
            background: transparent;
            color: var(--color-text);
        }

        .btn-ghost:hover {
            background: var(--color-bg-muted);
        }

        .btn-outline {
            background: transparent;
            color: var(--color-primary);
            border: 1.5px solid var(--color-border);
        }

        .btn-outline:hover {
            border-color: var(--color-primary);
            background: var(--color-bg-subtle);
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-accent {
            background: var(--color-accent);
            color: white;
        }

        .btn-accent:hover {
            background: var(--color-accent-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-large {
            padding: 0.875rem 1.75rem;
            font-size: 0.95rem;
        }

        /* Hero Section */
        .hero {
            padding: 8rem 0 5rem;
            background: linear-gradient(180deg, var(--color-bg-subtle) 0%, var(--color-bg) 100%);
        }

        .hero-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content {
            max-width: 540px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--color-text-muted);
            margin-bottom: 1.5rem;
        }

        .hero-badge-dot {
            width: 6px;
            height: 6px;
            background: var(--color-success);
            border-radius: 50%;
        }

        .hero-title {
            font-size: 3.25rem;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.03em;
            color: var(--color-primary);
            margin-bottom: 1.25rem;
        }

        .hero-title span {
            color: var(--color-accent);
        }

        .hero-subtitle {
            font-size: 1.125rem;
            color: var(--color-text-muted);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .hero-cta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .hero-visual {
            position: relative;
        }

        .hero-image {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Pipeline Preview */
        .pipeline-preview {
            padding: 1.25rem;
        }

        .pipeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--color-border);
        }

        .pipeline-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text);
        }

        .pipeline-value {
            font-size: 0.75rem;
            color: var(--color-success);
            font-weight: 600;
        }

        .pipeline-stages {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
        }

        .pipeline-stage {
            background: var(--color-bg-subtle);
            border-radius: 8px;
            padding: 0.75rem;
        }

        .stage-header {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 0.5rem;
        }

        .stage-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .stage-name {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--color-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .stage-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 6px;
            padding: 0.5rem;
            margin-bottom: 0.375rem;
        }

        .stage-card:last-child {
            margin-bottom: 0;
        }

        .card-name {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 0.125rem;
        }

        .card-value {
            font-size: 0.7rem;
            color: var(--color-success);
            font-weight: 600;
        }

        /* Logos Section */
        .logos {
            padding: 4rem 0;
            border-bottom: 1px solid var(--color-border);
        }

        .logos-title {
            text-align: center;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--color-text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 2rem;
        }

        .logos-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 3rem;
            flex-wrap: wrap;
            opacity: 0.6;
        }

        .logo-item {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text-muted);
            letter-spacing: -0.02em;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
        }

        .section-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 4rem;
        }

        .section-label {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-accent);
            margin-bottom: 0.75rem;
        }

        .section-title {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: var(--color-primary);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.05rem;
            color: var(--color-text-muted);
            line-height: 1.7;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .feature-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            padding: 1.75rem;
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            border-color: var(--color-accent);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: var(--color-bg-subtle);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            color: var(--color-accent);
        }

        .feature-icon svg {
            width: 22px;
            height: 22px;
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .feature-description {
            color: var(--color-text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* How it Works */
        .how-it-works {
            padding: 6rem 0;
            background: var(--color-bg-subtle);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .step {
            text-align: center;
            padding: 2rem 1.5rem;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
        }

        .step-number {
            width: 48px;
            height: 48px;
            background: var(--color-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 auto 1.25rem;
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .step-description {
            color: var(--color-text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Pricing */
        .pricing {
            padding: 6rem 0;
        }

        .pricing-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 800px;
            margin: 3rem auto 0;
        }

        .pricing-card {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            padding: 2rem;
        }

        .pricing-card.featured {
            border-color: var(--color-accent);
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .pricing-card.featured::before {
            content: 'Populaire';
            position: absolute;
            top: -0.75rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--color-accent);
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pricing-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .pricing-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 0.25rem;
        }

        .pricing-price span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--color-text-muted);
        }

        .pricing-description {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--color-border);
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 0.625rem;
        }

        .pricing-features li svg {
            width: 18px;
            height: 18px;
            color: var(--color-success);
            flex-shrink: 0;
        }

        /* CTA Section */
        .cta {
            padding: 6rem 0;
            background: var(--color-primary);
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-title {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
            color: white;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-white {
            background: white;
            color: var(--color-primary);
        }

        .btn-white:hover {
            background: var(--color-bg-subtle);
            transform: translateY(-1px);
        }

        .btn-outline-white {
            background: transparent;
            color: white;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline-white:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Footer */
        .footer {
            padding: 3rem 0;
            border-top: 1px solid var(--color-border);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-link {
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: var(--color-text);
        }

        .footer-copy {
            color: var(--color-text-subtle);
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .hero-inner {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .hero-content {
                max-width: 100%;
                text-align: center;
            }

            .hero-cta {
                justify-content: center;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps {
                grid-template-columns: 1fr;
                max-width: 400px;
                margin-left: auto;
                margin-right: auto;
            }

            .pricing-cards {
                grid-template-columns: 1fr;
            }

            .pipeline-stages {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .nav-links {
                gap: 1rem;
            }

            .nav-link {
                display: none;
            }

            .hero {
                padding: 7rem 0 3rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 1.75rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-inner">
            <a href="/" class="nav-logo">Kontak</a>
            <div class="nav-links">
                <a href="#features" class="nav-link">Fonctionnalités</a>
                <a href="#pricing" class="nav-link">Tarifs</a>
                <a href="/admin/login" class="btn btn-ghost">Connexion</a>
                <a href="/admin/register" class="btn btn-primary">Commencer</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-inner">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        <span>Gestion multi-équipes</span>
                    </div>
                    <h1 class="hero-title">
                        Le CRM qui accélère <span>votre croissance</span>
                    </h1>
                    <p class="hero-subtitle">
                        Gérez vos contacts, suivez votre pipeline commercial et collaborez efficacement.
                        Simple, intuitif et conçu pour les équipes modernes.
                    </p>
                    <div class="hero-cta">
                        <a href="/admin/register" class="btn btn-accent btn-large">
                            Démarrer gratuitement
                        </a>
                        <a href="#features" class="btn btn-outline btn-large">
                            Découvrir
                        </a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-image">
                        <div class="pipeline-preview">
                            <div class="pipeline-header">
                                <span class="pipeline-title">Pipeline commercial</span>
                                <span class="pipeline-value">170 700 €</span>
                            </div>
                            <div class="pipeline-stages">
                                <div class="pipeline-stage">
                                    <div class="stage-header">
                                        <div class="stage-dot" style="background: #3b82f6;"></div>
                                        <span class="stage-name">Nouveau</span>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">Projet Alpha</div>
                                        <div class="card-value">12 500 €</div>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">Site E-commerce</div>
                                        <div class="card-value">8 200 €</div>
                                    </div>
                                </div>
                                <div class="pipeline-stage">
                                    <div class="stage-header">
                                        <div class="stage-dot" style="background: #8b5cf6;"></div>
                                        <span class="stage-name">Qualifié</span>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">Refonte CRM</div>
                                        <div class="card-value">45 000 €</div>
                                    </div>
                                </div>
                                <div class="pipeline-stage">
                                    <div class="stage-header">
                                        <div class="stage-dot" style="background: #f59e0b;"></div>
                                        <span class="stage-name">Proposition</span>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">App Mobile</div>
                                        <div class="card-value">28 000 €</div>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">Consulting</div>
                                        <div class="card-value">15 000 €</div>
                                    </div>
                                </div>
                                <div class="pipeline-stage">
                                    <div class="stage-header">
                                        <div class="stage-dot" style="background: #10b981;"></div>
                                        <span class="stage-name">Gagné</span>
                                    </div>
                                    <div class="stage-card">
                                        <div class="card-name">Migration Cloud</div>
                                        <div class="card-value">62 000 €</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Logos Section -->
    <section class="logos">
        <div class="container">
            <div class="logos-title">Ils nous font confiance</div>
            <div class="logos-grid">
                <span class="logo-item">TechCorp</span>
                <span class="logo-item">Startup.io</span>
                <span class="logo-item">Agence360</span>
                <span class="logo-item">CloudFirst</span>
                <span class="logo-item">DataFlow</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Fonctionnalités</span>
                <h2 class="section-title">Tout ce dont vous avez besoin</h2>
                <p class="section-subtitle">
                    Des outils puissants pour gérer vos relations clients et booster votre croissance commerciale.
                </p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Gestion des contacts</h3>
                    <p class="feature-description">
                        Centralisez entreprises et contacts. Historique complet des interactions et notes partagées.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Pipeline visuel</h3>
                    <p class="feature-description">
                        Tableau Kanban intuitif pour suivre vos opportunités. Glissez-déposez et visualisez en temps réel.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Gestion des tâches</h3>
                    <p class="feature-description">
                        Planifiez appels, réunions et relances. Ne manquez plus jamais une opportunité importante.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Multi-équipes</h3>
                    <p class="feature-description">
                        Créez plusieurs équipes avec des droits d'accès distincts. Chaque espace est isolé et sécurisé.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"/>
                            <line x1="12" y1="20" x2="12" y2="4"/>
                            <line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Statistiques</h3>
                    <p class="feature-description">
                        Tableaux de bord en temps réel. Taux de conversion, valeur pipeline et revenus.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Multilingue</h3>
                    <p class="feature-description">
                        Interface en français, anglais et espagnol. Chaque utilisateur choisit sa langue.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Comment ça marche</span>
                <h2 class="section-title">Prêt en 3 minutes</h2>
                <p class="section-subtitle">
                    Aucune installation requise. Inscrivez-vous et commencez immédiatement.
                </p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Créez votre compte</h3>
                    <p class="step-description">
                        Inscrivez-vous gratuitement avec votre email et créez votre première équipe.
                    </p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Configurez votre pipeline</h3>
                    <p class="step-description">
                        Définissez les étapes de votre cycle de vente selon votre processus.
                    </p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Invitez votre équipe</h3>
                    <p class="step-description">
                        Ajoutez vos collaborateurs et commencez à conclure plus de ventes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Tarifs</span>
                <h2 class="section-title">Simple et transparent</h2>
                <p class="section-subtitle">
                    Commencez gratuitement, évoluez selon vos besoins.
                </p>
            </div>
            <div class="pricing-cards">
                <div class="pricing-card">
                    <div class="pricing-name">Starter</div>
                    <div class="pricing-price">Gratuit</div>
                    <p class="pricing-description">Pour démarrer et tester la plateforme</p>
                    <ul class="pricing-features">
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            1 équipe
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            3 utilisateurs
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Contacts illimités
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Pipeline Kanban
                        </li>
                    </ul>
                    <a href="/admin/register" class="btn btn-outline" style="width: 100%;">Commencer</a>
                </div>
                <div class="pricing-card featured">
                    <div class="pricing-name">Pro</div>
                    <div class="pricing-price">29€ <span>/ mois</span></div>
                    <p class="pricing-description">Pour les équipes en croissance</p>
                    <ul class="pricing-features">
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Équipes illimitées
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Utilisateurs illimités
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Statistiques avancées
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Support prioritaire
                        </li>
                    </ul>
                    <a href="/admin/register" class="btn btn-accent" style="width: 100%;">Essai gratuit 14 jours</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Prêt à transformer votre gestion commerciale ?</h2>
                <p class="cta-subtitle">
                    Rejoignez les équipes qui utilisent Kontak pour développer leur activité.
                </p>
                <div class="cta-buttons">
                    <a href="/admin/register" class="btn btn-white btn-large">
                        Créer mon compte gratuit
                    </a>
                    <a href="/admin/login" class="btn btn-outline-white btn-large">
                        J'ai déjà un compte
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">Kontak</div>
                <div class="footer-links">
                    <a href="#features" class="footer-link">Fonctionnalités</a>
                    <a href="#pricing" class="footer-link">Tarifs</a>
                    <a href="/admin/login" class="footer-link">Connexion</a>
                    <a href="/admin/register" class="footer-link">Inscription</a>
                </div>
                <div class="footer-copy">
                    © {{ date('Y') }} Kontak. Tous droits réservés.
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
