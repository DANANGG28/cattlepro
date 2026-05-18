<?php
// Restore tanggal IB dari log aktivitas untuk sapi yang bunting tapi tanggalIb null

require_once '../controllers/main.php';

echo "<h2>Restore Tanggal IB dari Log Aktivitas</h2>";

// Get all sapi yang bunting tapi tanggalIb null
$query = 'query GetBuntingNoIB {
    cattles(where: { 
        statusReproduksi: { eq: "Bunting" }
    }) {
        id
        kodeSapi
        tanggalIb
    }
}';

$res = $sapi->conn->execute($query);
$sapi_list = $res['data']['cattles'] ?? [];

echo "<p>Found " . count($sapi_list) . " sapi bunting</p>";

foreach ($sapi_list as $s) {
    echo "<hr>";
    echo "<h3>Sapi: {$s['kodeSapi']}</h3>";
    echo "TanggalIb: " . ($s['tanggalIb'] ?? 'NULL') . "<br>";
    
    if (empty($s['tanggalIb'])) {
        // Cari log aktivitas inseminasi untuk sapi ini
        $query_log = 'query GetIBLog($kode: String!) {
            activities(
                where: { 
                    jenisAktivitas: { eq: "inseminasi" }
                    deskripsi: { contains: $kode }
                }
                orderBy: { createdAt: DESC }
                limit: 1
            ) {
                id
                createdAt
                deskripsi
            }
        }';
        
        $res_log = $sapi->conn->execute($query_log, ['kode' => $s['kodeSapi']]);
        $log = $res_log['data']['activities'][0] ?? null;
        
        if ($log) {
            echo "✅ Found IB log: {$log['createdAt']}<br>";
            echo "Deskripsi: {$log['deskripsi']}<br>";
            
            // Update tanggalIb
            $sapi->setTanggalIB($s['id'], $log['createdAt'], 'Bunting');
            echo "<strong>✅ Restored tanggalIb!</strong><br>";
        } else {
            echo "❌ No IB log found<br>";
        }
    } else {
        echo "✅ TanggalIb already set<br>";
    }
}

echo "<hr>";
echo "<p><strong>Done!</strong></p>";
echo "<p><a href='../pages/dashboard.php'>Back to Dashboard</a></p>";
