<?php
class Sapi {
    private $conn;
    private $table = "sapi";

    public $id;
    public $kode_sapi;
    public $jenis;
    public $tanggal_lahir;
    public $berat;
    public $status_reproduksi;
    public $tanggal_ib;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all sapi
    public function readAll() {
        $query = "SELECT s.*, 
                    (SELECT u.nama FROM users u 
                     JOIN log_aktivitas la ON u.id = la.user_id 
                     WHERE la.deskripsi LIKE CONCAT('%', s.kode_sapi, '%') 
                     ORDER BY la.created_at DESC LIMIT 1) as last_admin
                  FROM " . $this->table . " s ORDER BY s.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new sapi
    public function create() {
        $query = "INSERT INTO " . $this->table . " (kode_sapi, jenis, tanggal_lahir, berat, status_reproduksi) VALUES (:kode_sapi, :jenis, :tanggal_lahir, :berat, :status_reproduksi)";
        $stmt = $this->conn->prepare($query);
        
        $this->kode_sapi = htmlspecialchars(strip_tags($this->kode_sapi));
        $this->jenis = htmlspecialchars(strip_tags($this->jenis));
        $this->tanggal_lahir = htmlspecialchars(strip_tags($this->tanggal_lahir));
        $this->berat = htmlspecialchars(strip_tags($this->berat));
        $this->status_reproduksi = $this->status_reproduksi ?: 'Kosong';

        $stmt->bindParam(':kode_sapi', $this->kode_sapi);
        $stmt->bindParam(':jenis', $this->jenis);
        $stmt->bindParam(':tanggal_lahir', $this->tanggal_lahir);
        $stmt->bindParam(':berat', $this->berat);
        $stmt->bindParam(':status_reproduksi', $this->status_reproduksi);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update sapi
    public function update() {
        $query = "UPDATE " . $this->table . " SET kode_sapi = :kode_sapi, jenis = :jenis, tanggal_lahir = :tanggal_lahir, berat = :berat WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->kode_sapi = htmlspecialchars(strip_tags($this->kode_sapi));
        $this->jenis = htmlspecialchars(strip_tags($this->jenis));
        $this->tanggal_lahir = htmlspecialchars(strip_tags($this->tanggal_lahir));
        $this->berat = htmlspecialchars(strip_tags($this->berat));

        $stmt->bindParam(':kode_sapi', $this->kode_sapi);
        $stmt->bindParam(':jenis', $this->jenis);
        $stmt->bindParam(':tanggal_lahir', $this->tanggal_lahir);
        $stmt->bindParam(':berat', $this->berat);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Delete sapi
    public function delete($id) {
        // Delete related birahi records first
        $query = "DELETE FROM birahi WHERE id_sapi = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Delete related log_aktivitas
        $query = "DELETE FROM log_aktivitas WHERE deskripsi LIKE :kode";
        $stmt = $this->conn->prepare($query);
        $data = $this->getById($id);
        if ($data) {
            $kode = '%' . $data['kode_sapi'] . '%';
            $stmt->bindParam(':kode', $kode);
            $stmt->execute();
        }

        // Delete sapi
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Update status reproduksi
    public function updateStatusReproduksi($id, $status) {
        $query = "UPDATE " . $this->table . " SET status_reproduksi = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Set tanggal IB
    public function setTanggalIB($id, $tanggal) {
        $query = "UPDATE " . $this->table . " SET tanggal_ib = :tanggal WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // --- Birahi Methods ---

    // Create birahi record
    public function createBirahi($id_sapi, $tanggal_birahi) {
        $query = "INSERT INTO birahi (id_sapi, tanggal_birahi) VALUES (:id_sapi, :tanggal_birahi)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sapi', $id_sapi);
        $stmt->bindParam(':tanggal_birahi', $tanggal_birahi);
        return $stmt->execute();
    }

    // Get birahi by sapi ID
    public function getBirahiByIdSapi($id_sapi) {
        $query = "SELECT * FROM birahi WHERE id_sapi = :id_sapi ORDER BY tanggal_birahi DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sapi', $id_sapi);
        $stmt->execute();
        return $stmt;
    }

    // Get latest birahi
    public function getLatestBirahi($id_sapi) {
        $query = "SELECT * FROM birahi WHERE id_sapi = :id_sapi ORDER BY tanggal_birahi DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sapi', $id_sapi);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete birahi
    public function deleteBirahi($id) {
        $query = "DELETE FROM birahi WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // --- Activity Log Methods ---

    // Log activity
    public function logActivity($user_id, $jenis_aktivitas, $deskripsi) {
        $query = "INSERT INTO log_aktivitas (user_id, jenis_aktivitas, deskripsi) VALUES (:user_id, :jenis_aktivitas, :deskripsi)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':jenis_aktivitas', $jenis_aktivitas);
        $stmt->bindParam(':deskripsi', $deskripsi);
        return $stmt->execute();
    }

    // Get recent activities
    public function getRecentActivities($limit = 8) {
        $query = "SELECT la.*, u.nama, u.role 
                  FROM log_aktivitas la 
                  JOIN users u ON la.user_id = u.id 
                  ORDER BY la.created_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // --- Prediction Methods ---

    // Calculate reproduction prediction score
    public function hitungPrediksi($id_sapi) {
        $data = $this->getById($id_sapi);
        if (!$data) return null;

        // Skor Umur (max 40)
        $tanggal_lahir = new DateTime($data['tanggal_lahir']);
        $sekarang = new DateTime();
        $umur_bulan = ($sekarang->diff($tanggal_lahir)->y * 12) + $sekarang->diff($tanggal_lahir)->m;
        
        $skor_umur = 0;
        if ($umur_bulan >= 15 && $umur_bulan <= 18) {
            $skor_umur = 40;
        } elseif ($umur_bulan >= 12 && $umur_bulan < 15) {
            $skor_umur = 30;
        } elseif ($umur_bulan > 18 && $umur_bulan <= 24) {
            $skor_umur = 35;
        } elseif ($umur_bulan > 24 && $umur_bulan <= 36) {
            $skor_umur = 25;
        } elseif ($umur_bulan > 36) {
            $skor_umur = 15;
        } else {
            $skor_umur = 10;
        }

        // Skor Berat (max 30)
        $berat = floatval($data['berat']);
        $skor_berat = 0;
        if ($berat >= 300 && $berat <= 400) {
            $skor_berat = 30;
        } elseif ($berat >= 250 && $berat < 300) {
            $skor_berat = 20;
        } elseif ($berat > 400 && $berat <= 500) {
            $skor_berat = 25;
        } elseif ($berat > 500) {
            $skor_berat = 15;
        } else {
            $skor_berat = 10;
        }

        // Skor Birahi (max 30)
        $birahi_data = $this->getBirahiByIdSapi($id_sapi)->fetchAll(PDO::FETCH_ASSOC);
        $skor_birahi = 0;
        if (count($birahi_data) >= 3) {
            // Calculate average interval
            $intervals = [];
            for ($i = 0; $i < count($birahi_data) - 1; $i++) {
                $d1 = new DateTime($birahi_data[$i]['tanggal_birahi']);
                $d2 = new DateTime($birahi_data[$i + 1]['tanggal_birahi']);
                $intervals[] = abs($d1->diff($d2)->days);
            }
            $avg_interval = array_sum($intervals) / count($intervals);
            
            if ($avg_interval >= 18 && $avg_interval <= 24) {
                $skor_birahi = 30; // Ideal 21 days cycle
            } elseif ($avg_interval >= 15 && $avg_interval < 18) {
                $skor_birahi = 20;
            } elseif ($avg_interval > 24 && $avg_interval <= 30) {
                $skor_birahi = 20;
            } else {
                $skor_birahi = 10;
            }
        } elseif (count($birahi_data) >= 1) {
            $skor_birahi = 15;
        } else {
            $skor_birahi = 5;
        }

        $total_skor = $skor_umur + $skor_berat + $skor_birahi;
        $hasil_status = $total_skor >= 60 ? 'SIAP REPRODUKSI' : 'BELUM OPTIMAL';

        return [
            'skor_umur' => $skor_umur,
            'skor_berat' => $skor_berat,
            'skor_birahi' => $skor_birahi,
            'total_skor' => $total_skor,
            'hasil_status' => $hasil_status,
            'umur_bulan' => $umur_bulan,
            'berat' => $berat
        ];
    }

    // Search sapi
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " WHERE kode_sapi LIKE :keyword OR jenis LIKE :keyword2 ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $keyword = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $keyword);
        $stmt->bindParam(':keyword2', $keyword);
        $stmt->execute();
        return $stmt;
    }
}
?>
