<x-filament-widgets::widget>
    <style>
        .grading-guide-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 0.5rem;
            font-family: inherit;
        }
        .dark .grading-guide-card {
            background: #111827;
            border-color: #374151;
        }
        .grading-guide-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .dark .grading-guide-header {
            border-bottom-color: #1f2937;
        }
        .grading-guide-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }
        .dark .grading-guide-title {
            color: #f3f4f6;
        }
        .grading-guide-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: #1e40af;
            display: inline-block;
        }
        .grading-guide-formula {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #eff6ff;
            color: #1e40af;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            border: 1px solid #dbeafe;
        }
        .dark .grading-guide-formula {
            background: rgba(30, 64, 175, 0.25);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.3);
        }
        .grading-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            padding-top: 0.75rem;
        }
        @media (min-width: 640px) {
            .grading-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .grading-grid {
                grid-template-columns: repeat(8, minmax(0, 1fr));
            }
        }
        .grade-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.5rem 0.25rem;
            border-radius: 0.5rem;
            border-width: 1px;
            border-style: solid;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .grade-item:hover {
            transform: translateY(-1px);
        }
        .grade-badge-letter {
            font-size: 0.9375rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .grade-badge-range {
            font-size: 0.6875rem;
            font-weight: 600;
            margin-top: 0.125rem;
        }
        .grade-badge-desc {
            font-size: 0.5625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.125rem;
            opacity: 0.85;
        }

        /* Color Themes for each Grade */
        .grade-a {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .dark .grade-a {
            background-color: rgba(6, 95, 70, 0.25);
            border-color: rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }

        .grade-ab {
            background-color: #f0fdfa;
            border-color: #99f6e4;
            color: #115e59;
        }
        .dark .grade-ab {
            background-color: rgba(17, 94, 89, 0.25);
            border-color: rgba(20, 184, 166, 0.35);
            color: #5eead4;
        }

        .grade-b {
            background-color: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }
        .dark .grade-b {
            background-color: rgba(30, 64, 175, 0.25);
            border-color: rgba(59, 130, 246, 0.35);
            color: #93c5fd;
        }

        .grade-bc {
            background-color: #f0f9ff;
            border-color: #bae6fd;
            color: #0369a1;
        }
        .dark .grade-bc {
            background-color: rgba(3, 105, 161, 0.25);
            border-color: rgba(14, 165, 233, 0.35);
            color: #7dd3fc;
        }

        .grade-c {
            background-color: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .dark .grade-c {
            background-color: rgba(146, 64, 14, 0.25);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fcd34d;
        }

        .grade-cd {
            background-color: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }
        .dark .grade-cd {
            background-color: rgba(154, 52, 18, 0.25);
            border-color: rgba(249, 115, 22, 0.35);
            color: #fdba74;
        }

        .grade-d {
            background-color: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }
        .dark .grade-d {
            background-color: rgba(159, 18, 57, 0.25);
            border-color: rgba(244, 63, 94, 0.35);
            color: #fda4af;
        }

        .grade-e {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .dark .grade-e {
            background-color: rgba(153, 27, 27, 0.25);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }
    </style>

    <div class="grading-guide-card">
        <div class="grading-guide-header">
            <div class="grading-guide-title">
                <span class="grading-guide-dot"></span>
                <span>Konversi Nilai Huruf (Grading Standard PPL)</span>
            </div>
            <div class="grading-guide-formula">
                <span style="opacity: 0.85;">Formula Nilai Akhir:</span>
                <strong>(Nilai Mitra × 60%) + (Nilai Laporan DPL × 40%)</strong>
            </div>
        </div>

        <div class="grading-grid">
            <div class="grade-item grade-a">
                <div class="grade-badge-letter">A</div>
                <div class="grade-badge-range">81,0 – 100</div>
                <div class="grade-badge-desc">Sangat Baik</div>
            </div>

            <div class="grade-item grade-ab">
                <div class="grade-badge-letter">AB</div>
                <div class="grade-badge-range">75,0 – 80,9</div>
                <div class="grade-badge-desc">Baik Sekali</div>
            </div>

            <div class="grade-item grade-b">
                <div class="grade-badge-letter">B</div>
                <div class="grade-badge-range">69,0 – 74,9</div>
                <div class="grade-badge-desc">Baik</div>
            </div>

            <div class="grade-item grade-bc">
                <div class="grade-badge-letter">BC</div>
                <div class="grade-badge-range">63,0 – 68,9</div>
                <div class="grade-badge-desc">Cukup Baik</div>
            </div>

            <div class="grade-item grade-c">
                <div class="grade-badge-letter">C</div>
                <div class="grade-badge-range">57,0 – 62,9</div>
                <div class="grade-badge-desc">Cukup</div>
            </div>

            <div class="grade-item grade-cd">
                <div class="grade-badge-letter">CD</div>
                <div class="grade-badge-range">51,0 – 56,9</div>
                <div class="grade-badge-desc">Kurang</div>
            </div>

            <div class="grade-item grade-d">
                <div class="grade-badge-letter">D</div>
                <div class="grade-badge-range">45,0 – 50,9</div>
                <div class="grade-badge-desc">Sangat Kurang</div>
            </div>

            <div class="grade-item grade-e">
                <div class="grade-badge-letter">E</div>
                <div class="grade-badge-range">0 – 44,9</div>
                <div class="grade-badge-desc">Tidak Lulus</div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
