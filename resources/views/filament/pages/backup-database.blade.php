<x-filament-panels::page>
    <style>
        .db-backup-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            font-family: inherit;
        }

        /* Info Guide Card */
        .db-info-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.75rem;
            padding: 1.125rem 1.25rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .dark .db-info-box {
            background: rgba(30, 64, 175, 0.18);
            border-color: rgba(59, 130, 246, 0.35);
        }
        .db-info-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: #1e40af;
            color: #ffffff;
            flex-shrink: 0;
        }
        .dark .db-info-icon {
            background: #3b82f6;
        }
        .db-info-content h4 {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0 0 0.375rem 0;
        }
        .dark .db-info-content h4 {
            color: #bfdbfe;
        }
        .db-info-content p {
            font-size: 0.78125rem;
            line-height: 1.6;
            color: #1e40af;
            margin: 0;
        }
        .dark .db-info-content p {
            color: #93c5fd;
        }

        /* Table Card Container */
        .db-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .dark .db-card {
            background: #111827;
            border-color: #374151;
        }
        .db-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1.25rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .db-card-header {
            background: #1f2937;
            border-bottom-color: #374151;
        }
        .db-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .dark .db-card-title {
            color: #f3f4f6;
        }
        .db-card-path {
            font-size: 0.6875rem;
            color: #6b7280;
            background: #f3f4f6;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
            border: 1px solid #e5e7eb;
        }
        .dark .db-card-path {
            background: #374151;
            color: #9ca3af;
            border-color: #4b5563;
        }

        /* Table Styles */
        .db-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .db-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.8125rem;
        }
        .db-table th {
            padding: 0.75rem 1rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .dark .db-table th {
            background: #1e293b;
            color: #94a3b8;
            border-bottom-color: #334155;
        }
        .db-table td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }
        .dark .db-table td {
            border-bottom-color: #1e293b;
            color: #cbd5e1;
        }
        .db-table tr:hover td {
            background: #f8fafc;
        }
        .dark .db-table tr:hover td {
            background: rgba(30, 41, 59, 0.4);
        }

        /* Format Badges */
        .db-badge-sql {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .dark .db-badge-sql {
            background: rgba(30, 64, 175, 0.25);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.4);
        }

        .db-badge-sqlite {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .dark .db-badge-sqlite {
            background: rgba(6, 95, 70, 0.25);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.4);
        }

        /* Action Buttons */
        .db-actions-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .db-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
        }
        .db-btn-download {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }
        .db-btn-download:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .dark .db-btn-download {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }
        .dark .db-btn-download:hover {
            background: #334155;
            color: #f8fafc;
        }

        .db-btn-restore {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }
        .db-btn-restore:hover {
            background: #fde68a;
            color: #78350f;
        }
        .dark .db-btn-restore {
            background: rgba(146, 64, 14, 0.25);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, 0.4);
        }
        .dark .db-btn-restore:hover {
            background: rgba(146, 64, 14, 0.4);
        }

        .db-btn-delete {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }
        .db-btn-delete:hover {
            background: #fecaca;
            color: #7f1d1d;
        }
        .dark .db-btn-delete {
            background: rgba(153, 27, 27, 0.25);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.4);
        }
        .dark .db-btn-delete:hover {
            background: rgba(153, 27, 27, 0.4);
        }

        .db-empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #64748b;
        }
    </style>

    <div class="db-backup-wrapper">
        <!-- Informasi & Panduan Migrasi Hosting -->
        <div class="db-info-box">
            <div class="db-info-icon">
                <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </div>
            <div class="db-info-content">
                <h4>Panduan Backup & Migrasi Server Hosting</h4>
                <p>
                    • <strong>Format SQL (.sql):</strong> Berisi seluruh struktur tabel (DROP & CREATE) beserta data riil (users, mitras, groups, students) yang siap diimpor langsung ke <em>phpMyAdmin</em>, <em>MySQL</em>, atau <em>MariaDB</em> di cPanel / VPS hosting.<br>
                    • <strong>Format SQLite (.sqlite):</strong> Salinan langsung berkas database aktif. Sangat cocok jika server hosting Anda menjalankan driver SQLite bawaan Laravel 11.<br>
                    • <strong>Restore / Import:</strong> Anda dapat mengunggah berkas cadangan kapan saja melalui tombol di atas untuk memulihkan seluruh data aplikasi.
                </p>
            </div>
        </div>

        <!-- Tabel Riwayat Berkas Cadangan -->
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">
                    Daftar Berkas Cadangan Tersimpan ({{ count($this->backups) }})
                </h3>
                <span class="db-card-path">
                    storage/app/backups/
                </span>
            </div>

            <div class="db-table-container">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 48px;">No</th>
                            <th>Nama Berkas Cadangan</th>
                            <th style="text-align: center; width: 100px;">Format</th>
                            <th style="text-align: center; width: 110px;">Ukuran</th>
                            <th style="text-align: center; width: 160px;">Tanggal Dibuat</th>
                            <th style="text-align: center; width: 250px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->backups as $index => $backup)
                            <tr>
                                <td style="text-align: center; font-family: monospace; font-size: 0.75rem;">
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                                        @if ($backup['format'] === 'SQL')
                                            <span style="display: flex; align-items: center; justify-content: center; width: 1.875rem; height: 1.875rem; border-radius: 0.375rem; background: #dbeafe; color: #1e40af;">
                                                <svg style="width: 1.125rem; height: 1.125rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                                </svg>
                                            </span>
                                        @else
                                            <span style="display: flex; align-items: center; justify-content: center; width: 1.875rem; height: 1.875rem; border-radius: 0.375rem; background: #d1fae5; color: #065f46;">
                                                <svg style="width: 1.125rem; height: 1.125rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                            </span>
                                        @endif
                                        <span style="font-family: monospace; font-size: 0.8125rem; font-weight: 600;">
                                            {{ $backup['filename'] }}
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="{{ $backup['format'] === 'SQL' ? 'db-badge-sql' : 'db-badge-sqlite' }}">
                                        {{ $backup['format'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-family: monospace; font-size: 0.78125rem; font-weight: 600;">
                                    {{ $backup['size'] }}
                                </td>
                                <td style="text-align: center; font-size: 0.75rem; color: #64748b;">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td style="text-align: center;">
                                    <div class="db-actions-group">
                                        <!-- Tombol Unduh -->
                                        <a 
                                            href="{{ route('backup.download', ['filename' => $backup['filename']]) }}"
                                            class="db-btn db-btn-download"
                                            title="Unduh Berkas ke Komputer"
                                        >
                                            <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            Unduh
                                        </a>

                                        <!-- Tombol Pulihkan -->
                                        <button 
                                            type="button"
                                            wire:click="restoreBackupFile('{{ $backup['filename'] }}')"
                                            wire:confirm="Yakin ingin memulihkan database dari berkas {{ $backup['filename'] }}? Tindakan ini akan menimpa seluruh data yang sedang aktif!"
                                            class="db-btn db-btn-restore"
                                            title="Pulihkan Database dari Berkas Ini"
                                        >
                                            <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            Pulihkan
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <button 
                                            type="button"
                                            wire:click="deleteBackupFile('{{ $backup['filename'] }}')"
                                            wire:confirm="Hapus berkas cadangan {{ $backup['filename'] }} dari server?"
                                            class="db-btn db-btn-delete"
                                            title="Hapus Berkas dari Server"
                                        >
                                            <svg style="width: 0.875rem; height: 0.875rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="db-empty-state">
                                        <svg style="width: 2.5rem; height: 2.5rem; opacity: 0.5;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                        </svg>
                                        <div style="font-weight: 600; font-size: 0.875rem;">Belum ada berkas cadangan database yang dibuat.</div>
                                        <div style="font-size: 0.75rem; opacity: 0.8;">Klik tombol <strong>"Buat Backup Baru (SQL)"</strong> di kanan atas untuk membuat cadangan pertama Anda.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
