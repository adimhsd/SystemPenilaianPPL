<x-filament-panels::page.simple>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        /* Force page body & container styling */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: linear-gradient(135deg, #f0f4f9 0%, #e2e8f0 100%) !important;
            min-height: 100vh;
        }

        .fi-simple-main-ctn {
            max-width: 440px !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding: 1.5rem 1rem !important;
        }

        .fi-simple-main {
            max-width: 440px !important;
            width: 100% !important;
            padding: 0 !important;
        }

        .fi-simple-page {
            padding: 0 !important;
        }

        .fi-simple-page-content {
            padding: 0 !important;
        }

        /* Auth Card matching febuniku.dpdns.org/login */
        .auth-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08), 0 5px 15px rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
            width: 100%;
        }

        .dark .auth-card {
            background: #1e293b;
            border-color: rgba(51, 65, 85, 0.8);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .auth-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #ffffff;
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }

        .auth-header .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            padding: 0.35rem 0.85rem;
            border-radius: 50rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }

        .auth-header h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
            color: #ffffff;
            line-height: 1.3;
        }

        .auth-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.875rem;
        }

        .auth-body {
            padding: 1.5rem 1.75rem;
        }

        /* Form elements styling */
        .auth-body input[type="text"],
        .auth-body input[type="password"] {
            border-radius: 0.65rem !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.95rem !important;
            border-color: #cbd5e1 !important;
        }

        .auth-body input[type="text"]:focus,
        .auth-body input[type="password"]:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
        }

        /* Custom Submit Button */
        .auth-body button[type="submit"] {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            border: none !important;
            border-radius: 0.65rem !important;
            padding: 0.85rem 1.25rem !important;
            font-weight: 600 !important;
            min-height: 48px !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25) !important;
            transition: all 0.2s ease-in-out !important;
            color: #ffffff !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.95rem !important;
            margin-top: 0.75rem !important;
        }

        .auth-body button[type="submit"]:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.35) !important;
        }

        /* Footer */
        .auth-footer {
            background: #f8fafc;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .dark .auth-footer {
            background: #0f172a;
            border-top-color: #334155;
        }

        .auth-footer .copyright {
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .dark .auth-footer .copyright {
            color: #cbd5e1;
        }

        .auth-footer .credits {
            font-size: 0.6875rem;
            color: #64748b;
        }

        .auth-footer .credits a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer .credits a:hover {
            text-decoration: underline;
        }
    </style>

    <div class="auth-card">
        <div class="auth-header">
            <div style="margin-bottom: 0.5rem; text-align: center;">
                <img 
                    src="{{ asset('images/logo-uniku.png') }}" 
                    alt="Logo Universitas Kuningan" 
                    style="height: 75px; width: auto; margin: 0 auto; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.2)); display: block;"
                >
            </div>
            <span class="brand-badge">FEB UNIKU</span>
            <h4>Sistem Penilaian PPL</h4>
            <p>Praktik Pengalaman Lapangan</p>
        </div>

        <div class="auth-body">
            {{ $this->content }}
        </div>

        <div class="auth-footer">
            <div class="copyright">
                &copy; {{ date('Y') }} FEB - Universitas Kuningan
            </div>
            <div class="credits">
                Developed by <a href="https://adi-muhamad.web.app/" target="_blank" rel="noopener noreferrer">Dosen Sontoloyo</a>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
