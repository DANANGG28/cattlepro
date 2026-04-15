<?php
require_once 'controllers/main.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Get filter: single sapi or all
$id_filter = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id_filter) {
    $sapi_data = $sapi->getById($id_filter);
    if (!$sapi_data) {
        header("Location: sapi.php");
        exit;
    }
    $semua_sapi = [$sapi_data];
} else {
    $semua_sapi = $sapi->readAll()->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch birahi history for each sapi
foreach ($semua_sapi as &$s) {
    $s['birahi_history'] = $sapi->getBirahiByIdSapi($s['id'])->fetchAll(PDO::FETCH_ASSOC);

    // Calculate age
    $lahir = new DateTime($s['tanggal_lahir']);
    $now = new DateTime();
    $diff = $now->diff($lahir);
    $s['umur_tahun'] = $diff->y;
    $s['umur_bulan'] = ($diff->y * 12) + $diff->m;

    // HPL estimate if bunting (283 days from last IB)
    $s['hpl'] = null;
    if ($s['status_reproduksi'] === 'Bunting' && !empty($s['tanggal_ib'])) {
        $ib_date = new DateTime($s['tanggal_ib']);
        $ib_date->modify('+283 days');
        $s['hpl'] = $ib_date->format('d M Y');
    }

    // PKB estimate (60 days from last IB)
    $s['pkb'] = null;
    if ($s['status_reproduksi'] === 'Sudah IB' && !empty($s['tanggal_ib'])) {
        $ib_date = new DateTime($s['tanggal_ib']);
        $ib_date->modify('+60 days');
        $s['pkb'] = $ib_date->format('d M Y');
    }
}
unset($s);

$print_date = date('d F Y');
$print_time = date('H:i');
$company_name = "CattlePro Management System";
$exported_by = $current_user['nama'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data Sapi - CattlePro</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #e8ecf0;
            color: #1a202c;
            font-size: 13px;
        }

        /* ========== PRINT BAR (screen only) ========== */
        .print-bar {
            background: #0A3622;
            color: white;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .print-bar-brand { display: flex; align-items: center; gap: 10px; }
        .print-bar-brand .logo-box {
            width: 36px; height: 36px;
            background: #00D084;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #0A3622;
        }
        .print-bar-brand span { font-weight: 800; font-size: 16px; }
        .print-bar-actions { display: flex; gap: 10px; }
        .btn-back {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            transition: background 0.2s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.2); }
        .btn-print {
            background: #00D084;
            border: none;
            color: #0A3622;
            padding: 8px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #00b872; }

        /* ========== PAPER WRAPPER ========== */
        .paper-wrapper {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 32px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ========== A4 CARD ========== */
        .invoice-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            page-break-after: always;
        }

        /* Header */
        .inv-header {
            background: linear-gradient(135deg, #0A3622 0%, #0f4f2e 60%, #124025 100%);
            padding: 32px 36px 28px;
            position: relative;
            overflow: hidden;
        }
        .inv-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: rgba(0,208,132,0.08);
            border-radius: 50%;
        }
        .inv-header::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 20%;
            width: 120px; height: 120px;
            background: rgba(0,208,132,0.05);
            border-radius: 50%;
        }
        .inv-header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative; z-index: 1;
        }
        .inv-brand { display: flex; align-items: center; gap: 14px; }
        .inv-brand-logo {
            width: 52px; height: 52px;
            background: #00D084;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #0A3622;
            font-weight: 900;
            box-shadow: 0 4px 16px rgba(0,208,132,0.3);
        }
        .inv-brand-text h1 { color: white; font-size: 20px; font-weight: 800; line-height: 1.2; }
        .inv-brand-text p { color: #89A897; font-size: 11px; font-weight: 500; margin-top: 2px; }
        .inv-doc-info { text-align: right; }
        .inv-doc-badge {
            display: inline-block;
            background: rgba(0,208,132,0.15);
            border: 1px solid rgba(0,208,132,0.3);
            color: #00D084;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: 2px;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .inv-doc-info p { color: #89A897; font-size: 11px; }
        .inv-doc-info strong { color: white; }

        .inv-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin: 22px 0;
            position: relative; z-index: 1;
        }

        .inv-sapi-headline {
            display: flex; align-items: center; gap: 16px;
            position: relative; z-index: 1;
        }
        .inv-sapi-code {
            background: #00D084;
            color: #0A3622;
            font-size: 26px;
            font-weight: 800;
            padding: 10px 24px;
            border-radius: 12px;
            letter-spacing: 2px;
            box-shadow: 0 4px 16px rgba(0,208,132,0.3);
        }
        .inv-sapi-meta h2 { color: white; font-size: 15px; font-weight: 700; }
        .inv-sapi-meta p { color: #89A897; font-size: 12px; margin-top: 3px; }

        .inv-status-badge {
            margin-left: auto;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .status-kosong { background: rgba(156,163,175,0.2); color: #9ca3af; border: 1px solid rgba(156,163,175,0.3); }
        .status-birahi { background: rgba(236,72,153,0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.3); }
        .status-ib { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .status-bunting { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .status-gagal { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

        /* Body */
        .inv-body { padding: 28px 36px; }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e8ecf0;
            border-radius: 12px;
            padding: 14px 16px;
        }
        .info-box-label {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-box-value {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
        }
        .info-box-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .info-box.highlight {
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            border-color: #a7f3d0;
        }
        .info-box.highlight .info-box-value { color: #059669; }

        /* Section Title */
        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::before {
            content: '';
            width: 3px;
            height: 14px;
            background: #00D084;
            border-radius: 2px;
        }

        /* Timeline Table */
        .timeline-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .timeline-table thead tr {
            background: #f1f5f9;
        }
        .timeline-table th {
            padding: 10px 14px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        .timeline-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .timeline-table tbody tr:last-child { border-bottom: none; }
        .timeline-table tbody tr:hover { background: #f8fafc; }
        .timeline-table td {
            padding: 10px 14px;
            color: #374151;
            font-size: 12px;
            vertical-align: middle;
        }
        .timeline-table td.no { color: #94a3b8; font-size: 11px; font-weight: 700; }
        .timeline-table td.date { font-weight: 700; color: #1e293b; }
        .timeline-table td.interval { color: #059669; font-weight: 700; }
        .timeline-table td.no-data {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            font-style: italic;
        }

        /* IB / Monitoring Summary */
        .monitoring-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .monitoring-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .monitoring-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .monitoring-box .m-label { font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .monitoring-box .m-value { font-size: 15px; font-weight: 800; color: #1e293b; margin-top: 3px; }
        .monitoring-box .m-note { font-size: 11px; color: #64748b; margin-top: 2px; }

        /* Footer */
        .inv-footer {
            background: #f8fafc;
            border-top: 1px solid #e8ecf0;
            padding: 18px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .inv-footer-left { }
        .inv-footer-left p { font-size: 11px; color: #94a3b8; }
        .inv-footer-left strong { color: #475569; }
        .inv-qr-note {
            text-align: right;
            font-size: 10px;
            color: #cbd5e1;
        }
        .inv-footer-center {
            text-align: center;
            font-size: 10px;
            color: #cbd5e1;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            body { background: white !important; }
            .print-bar { display: none !important; }
            .paper-wrapper { padding: 0 !important; gap: 0 !important; max-width: 100% !important; }
            .invoice-card {
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-after: always;
                break-after: page;
            }
            .invoice-card:last-child { page-break-after: avoid !important; break-after: avoid !important; }
            @page { size: A4 portrait; margin: 14mm 12mm; }
        }
    </style>
</head>
<body>

<!-- Print action bar (screen only) -->
<div class="print-bar">
    <div class="print-bar-brand">
        <div class="logo-box"><i class="fas fa-cow"></i></div>
        <span>CattlePro &mdash; Export Data Sapi</span>
    </div>
    <div class="print-bar-actions">
        <a href="sapi.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak / Simpan PDF</button>
    </div>
</div>

<div class="paper-wrapper">

<?php foreach ($semua_sapi as $idx => $s):
    $status = $s['status_reproduksi'] ?? 'Kosong';
    $status_class_map = [
        'Kosong'        => 'status-kosong',
        'Sudah Birahi'  => 'status-birahi',
        'Sudah IB'      => 'status-ib',
        'Bunting'       => 'status-bunting',
        'Gagal Hamil'   => 'status-gagal',
    ];
    $status_class = $status_class_map[$status] ?? 'status-kosong';

    $birahi_list = $s['birahi_history'];
    $total_birahi = count($birahi_list);

    // Calculate cycle intervals
    $intervals = [];
    for ($i = 0; $i < count($birahi_list) - 1; $i++) {
        $d1 = new DateTime($birahi_list[$i]['tanggal_birahi']);
        $d2 = new DateTime($birahi_list[$i + 1]['tanggal_birahi']);
        $intervals[] = abs($d1->diff($d2)->days);
    }
    $avg_siklus = count($intervals) > 0 ? round(array_sum($intervals) / count($intervals)) : '-';
?>
<div class="invoice-card">

    <!-- ===== HEADER ===== -->
    <div class="inv-header">
        <div class="inv-header-top">
            <div class="inv-brand">
                <div class="inv-brand-logo"><i class="fas fa-cow"></i></div>
                <div class="inv-brand-text">
                    <h1>CattlePro</h1>
                    <p>Cattle Reproduction Management System</p>
                </div>
            </div>
            <div class="inv-doc-info">
                <div class="inv-doc-badge">LAPORAN SAPI</div>
                <p>Dicetak: <strong><?php echo $print_date; ?></strong></p>
                <p>Pukul: <strong><?php echo $print_time; ?> WIB</strong></p>
                <p>Oleh: <strong><?php echo htmlspecialchars($exported_by); ?></strong></p>
            </div>
        </div>

        <hr class="inv-divider">

        <div class="inv-sapi-headline">
            <div class="inv-sapi-code"><?php echo htmlspecialchars($s['kode_sapi']); ?></div>
            <div class="inv-sapi-meta">
                <h2>Sapi <?php echo htmlspecialchars($s['jenis']); ?></h2>
                <p>ID Database: #<?php echo $s['id']; ?> &bull; Lahir: <?php echo date('d M Y', strtotime($s['tanggal_lahir'])); ?></p>
            </div>
            <div class="inv-status-badge <?php echo $status_class; ?>"><?php echo strtoupper($status); ?></div>
        </div>
    </div>

    <!-- ===== BODY ===== -->
    <div class="inv-body">

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-box-label">Jenis Sapi</div>
                <div class="info-box-value"><?php echo htmlspecialchars($s['jenis']); ?></div>
                <div class="info-box-sub">Ras / Breed</div>
            </div>
            <div class="info-box">
                <div class="info-box-label">Umur</div>
                <div class="info-box-value"><?php echo $s['umur_tahun']; ?> Tahun</div>
                <div class="info-box-sub"><?php echo $s['umur_bulan']; ?> bulan</div>
            </div>
            <div class="info-box">
                <div class="info-box-label">Berat Badan</div>
                <div class="info-box-value"><?php echo number_format($s['berat'], 0); ?> kg</div>
                <div class="info-box-sub">Berat terakhir tercatat</div>
            </div>
            <div class="info-box highlight">
                <div class="info-box-label">Status Reproduksi</div>
                <div class="info-box-value"><?php echo $status; ?></div>
                <div class="info-box-sub">Status saat ini</div>
            </div>
        </div>

        <!-- IB & Monitoring -->
        <div class="section-title">Monitoring Inseminasi & Kehamilan</div>
        <div class="monitoring-grid">
            <div class="monitoring-box">
                <div class="monitoring-icon" style="background:#eff6ff; color:#3b82f6;">
                    <i class="fas fa-syringe"></i>
                </div>
                <div>
                    <div class="m-label">Tanggal IB (Inseminasi Buatan)</div>
                    <div class="m-value"><?php echo $s['tanggal_ib'] ? date('d M Y', strtotime($s['tanggal_ib'])) : 'Belum ada'; ?></div>
                    <div class="m-note"><?php echo $s['tanggal_ib'] ? 'Inseminasi terakhir' : 'Sapi belum pernah di-IB'; ?></div>
                </div>
            </div>
            <div class="monitoring-box">
                <div class="monitoring-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div>
                    <div class="m-label">PKB (Pemeriksaan Kebuntingan)</div>
                    <div class="m-value"><?php echo $s['pkb'] ?? ($s['hpl'] ? 'Sudah Bunting' : ($s['tanggal_ib'] ? 'Perlu dicek' : '-')); ?></div>
                    <div class="m-note"><?php echo $s['pkb'] ? 'Estimasi jadwal PKB (60 hari post-IB)' : ($s['hpl'] ? 'Sapi dikonfirmasi bunting' : 'Belum ada data IB'); ?></div>
                </div>
            </div>
            <div class="monitoring-box">
                <div class="monitoring-icon" style="background:#fef9c3; color:#d97706;">
                    <i class="fas fa-child"></i>
                </div>
                <div>
                    <div class="m-label">Estimasi HPL (Hari Perkiraan Lahir)</div>
                    <div class="m-value"><?php echo $s['hpl'] ?? ($s['tanggal_ib'] && $status === 'Bunting' ? 'Hitung PKB dulu' : '-'); ?></div>
                    <div class="m-note"><?php echo $s['hpl'] ? 'Estimasi kelahiran (283 hari post-IB)' : 'Belum bisa diprediksi'; ?></div>
                </div>
            </div>
            <div class="monitoring-box">
                <div class="monitoring-icon" style="background:#fdf4ff; color:#a855f7;">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div>
                    <div class="m-label">Rata-rata Siklus Birahi</div>
                    <div class="m-value"><?php echo $avg_siklus !== '-' ? $avg_siklus . ' hari' : '-'; ?></div>
                    <div class="m-note"><?php echo $avg_siklus !== '-' ? 'Siklus ideal: 18–24 hari' : 'Minimal 2 catatan birahi'; ?></div>
                </div>
            </div>
        </div>

        <!-- Birahi History Table -->
        <div class="section-title">Histori Pencatatan Birahi <?php if ($total_birahi > 0) echo "($total_birahi Catatan)"; ?></div>
        <table class="timeline-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Tanggal Birahi</th>
                    <th>Hari ke-</th>
                    <th>Interval Siklus</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($total_birahi > 0):
                foreach ($birahi_list as $bi => $b):
                    $tgl_birahi = new DateTime($b['tanggal_birahi']);
                    $tgl_lahir_sapi = new DateTime($s['tanggal_lahir']);
                    $hari_ke = $tgl_lahir_sapi->diff($tgl_birahi)->days;

                    // Interval from previous record
                    $interval_txt = '-';
                    if ($bi < count($birahi_list) - 1) {
                        $prev = new DateTime($birahi_list[$bi + 1]['tanggal_birahi']);
                        $interval_days = abs($tgl_birahi->diff($prev)->days);
                        $interval_txt = $interval_days . ' hari';
                    }

                    $is_optimal = ($bi === 0); // Latest record
            ?>
                <tr>
                    <td class="no"><?php echo $bi + 1; ?></td>
                    <td class="date">
                        <?php echo date('d M Y', strtotime($b['tanggal_birahi'])); ?>
                        <?php if ($is_optimal): ?>
                            <span style="background:#dcfce7;color:#15803d;font-size:9px;font-weight:800;padding:2px 8px;border-radius:20px;margin-left:6px;letter-spacing:0.5px;">TERBARU</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#64748b;"><?php echo number_format($hari_ke); ?> hari sejak lahir</td>
                    <td class="interval"><?php echo $interval_txt; ?></td>
                    <td style="color:#94a3b8;font-size:11px;">
                        <?php
                            if ($bi === 0 && !empty($s['tanggal_ib'])) {
                                echo 'Dilanjutkan IB pada ' . date('d M Y', strtotime($s['tanggal_ib']));
                            } elseif ($is_optimal) {
                                echo 'Birahi terkini';
                            } else {
                                echo 'Catatan historis';
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">
                        <i class="fas fa-info-circle" style="margin-right:6px; color:#cbd5e1;"></i>
                        Belum ada catatan birahi untuk sapi ini
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Summary note -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; font-size:11px; color:#64748b; line-height:1.7;">
            <strong style="color:#1e293b; display:block; margin-bottom:4px;"><i class="fas fa-info-circle" style="color:#3b82f6; margin-right:6px;"></i>Catatan Sistem</strong>
            Dokumen ini digenerate otomatis oleh sistem CattlePro pada <strong><?php echo $print_date . ' pukul ' . $print_time; ?> WIB</strong> oleh <strong><?php echo htmlspecialchars($exported_by); ?></strong>.
            Total catatan birahi: <strong><?php echo $total_birahi; ?></strong> &bull;
            Siklus rata-rata: <strong><?php echo $avg_siklus !== '-' ? $avg_siklus . ' hari' : 'Belum dapat dihitung'; ?></strong> &bull;
            Status reproduksi: <strong><?php echo $status; ?></strong>.
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <div class="inv-footer">
        <div class="inv-footer-left">
            <p><strong>CattlePro Management System</strong></p>
            <p>Sistem Manajemen Reproduksi Ternak &bull; Dokumen ini sah tanpa tanda tangan</p>
        </div>
        <div class="inv-footer-center">
            Halaman <?php echo $idx + 1; ?> dari <?php echo count($semua_sapi); ?>
        </div>
        <div class="inv-qr-note">
            Kode: <?php echo htmlspecialchars($s['kode_sapi']); ?> &bull; <?php echo $print_date; ?>
        </div>
    </div>

</div><!-- .invoice-card -->
<?php endforeach; ?>

</div><!-- .paper-wrapper -->
</body>
</html>
