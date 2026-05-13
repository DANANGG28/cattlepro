<?php
class Sapi {
    private $conn;

    public $id;
    public $kode_sapi;
    public $jenis;
    public $tanggal_lahir;
    public $berat;
    public $status_reproduksi;
    public $tanggal_ib;
    public $admin_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Helper untuk memetakan data Firebase ke format array CattlePro lama
     */
    private function mapData($data) {
        if (!$data) return null;
        return [
            'id'               => isset($data['id'])               ? $data['id']               : null,
            'kode_sapi'        => isset($data['kodeSapi'])         ? $data['kodeSapi']         : '',
            'jenis'            => isset($data['jenis'])            ? $data['jenis']            : '',
            'tanggal_lahir'    => isset($data['tanggalLahir'])     ? $data['tanggalLahir']     : null,
            'berat'            => isset($data['berat'])            ? $data['berat']            : 0,
            'status_reproduksi'=> isset($data['statusReproduksi']) ? $data['statusReproduksi'] : 'Kosong',
            'tanggal_ib'       => isset($data['tanggalIb'])        ? $data['tanggalIb']        : null,
            'admin_id'         => isset($data['adminId'])          ? $data['adminId']          : null,
            'last_admin'       => isset($data['admin']['nama'])    ? $data['admin']['nama']    : 'System',
            'admin_role'       => isset($data['admin']['role'])    ? $data['admin']['role']    : 'admin'
        ];
    }

    // Read all sapi
    public function readAll() {
        $query = 'query ListCattle {
            cattles(orderBy: { createdAt: DESC }) {
                id
                kodeSapi
                jenis
                tanggalLahir
                berat
                statusReproduksi
                tanggalIb
                adminId
                admin {
                    nama
                    role
                }
            }
        }';
        
        $res = $this->conn->execute($query);
        $items = isset($res['data']['cattles']) ? $res['data']['cattles'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->mapData($item);
        }
        
        // Kita return object QueryResult agar fetchAll() di dashboard tidak error
        return new QueryResult($results);
    }

    // Search sapi
    public function search($keyword) {
        $query = 'query SearchCattle($kw: String!) {
            cattles(where: { 
                or: [
                    { kodeSapi: { contains: $kw } },
                    { jenis: { contains: $kw } }
                ]
            }, orderBy: { createdAt: DESC }) {
                id kodeSapi jenis tanggalLahir berat statusReproduksi tanggalIb adminId
                admin { nama role }
            }
        }';
        
        $res = $this->conn->execute($query, ['kw' => $keyword]);
        $items = isset($res['data']['cattles']) ? $res['data']['cattles'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->mapData($item);
        }
        
        return new QueryResult($results);
    }

    // Get by ID
    public function getById($id) {
        $query = 'query GetCattle($id: UUID! @allow(fields: "id")) {
            cattle(id: $id) {
                id kodeSapi jenis tanggalLahir berat statusReproduksi tanggalIb adminId
                admin { nama role }
            }
        }';
        
        $res = $this->conn->execute($query, ['id' => (string)$id]);
        return $this->mapData(isset($res['data']['cattle']) ? $res['data']['cattle'] : null);
    }

    // Create new sapi
    public function create() {
        $query = 'mutation CreateCattle($data: Cattle_Data! @allow(fields: "id kodeSapi jenis tanggalLahir berat statusReproduksi tanggalIb adminId createdAt updatedAt")) {
            cattle_insert(data: $data)
        }';
        
        $now = date('c');
        $variables = [
            'data' => [
                'kodeSapi' => $this->kode_sapi,
                'jenis' => $this->jenis,
                'tanggalLahir' => $this->tanggal_lahir,
                'berat' => (int)$this->berat,
                'statusReproduksi' => $this->status_reproduksi ?: 'Kosong',
                'tanggalIb' => isset($this->tanggal_ib) ? $this->tanggal_ib : null,
                'adminId' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
                'createdAt' => $now,
                'updatedAt' => $now
            ]
        ];

        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['cattle_insert']['id']) ? $res['data']['cattle_insert']['id'] : false;
    }

    // Cek apakah kode_sapi sudah ada (untuk validasi duplikat saat import)
    public function kodeSapiExists($kode_sapi) {
        $query = 'query CheckKode($kode: String!) {
            cattles(where: { kodeSapi: { eq: $kode } }) { id }
        }';
        $res = $this->conn->execute($query, ['kode' => $kode_sapi]);
        return !empty($res['data']['cattles']);
    }

    // Update sapi
    public function update() {
        $current = $this->getById($this->id);
        if (!$current) return false;

        $query = 'mutation UpdateCattle($id: UUID!, $data: Cattle_Data! @allow(fields: "kodeSapi jenis tanggalLahir berat statusReproduksi tanggalIb updatedAt")) {
            cattle_update(id: $id, data: $data)
        }';
        
        $variables = array(
            'id' => (string)$this->id,
            'data' => array(
                'kodeSapi' => $this->kode_sapi,
                'jenis' => $this->jenis,
                'tanggalLahir' => $this->tanggal_lahir,
                'berat' => (int)$this->berat,
                'statusReproduksi' => isset($this->status_reproduksi) ? $this->status_reproduksi : $current['status_reproduksi'],
                'tanggalIb' => isset($this->tanggal_ib) ? $this->tanggal_ib : $current['tanggal_ib'],
                'updatedAt' => date('c')
            )
        );

        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['cattle_update']);
    }

    // Delete sapi
    // --- Birahi Methods ---
    // Get latest birahi for all cows (for dashboard)
    public function getAllLatestBirahi() {
        $query = 'query AllBirahis {
            heatCycles(orderBy: { tanggalBirahi: DESC }) {
                cattleId
                tanggalBirahi
            }
        }';
        
        $res = $this->conn->execute($query);
        $items = isset($res['data']['heatCycles']) ? $res['data']['heatCycles'] : array();
        
        $latest = [];
        foreach ($items as $item) {
            // Because we sort by date DESC, the first one we encounter for each cow is the latest
            if (!isset($latest[$item['cattleId']])) {
                $latest[$item['cattleId']] = [
                    'id_sapi' => $item['cattleId'],
                    'tanggal_birahi' => $item['tanggalBirahi']
                ];
            }
        }
        return $latest;
    }

    public function createBirahi($id_sapi, $tanggal_birahi) {
        $id_sapi = $this->formatUUID($id_sapi);
        $query = 'mutation CreateBirahi($data: HeatCycle_Data! @allow(fields: "cattleId tanggalBirahi createdAt")) {
            heatCycle_insert(data: $data)
        }';
        $variables = array(
            'data' => array(
                'cattleId' => (string)$id_sapi,
                'tanggalBirahi' => $tanggal_birahi,
                'createdAt' => date('c')
            )
        );
        $res = $this->conn->execute($query, $variables);
        if (isset($res['data']['heatCycle_insert'])) {
            // Update status sapi menjadi "Sudah Birahi"
            $this->updateStatusReproduksi($id_sapi, 'Sudah Birahi');
            return true;
        }
        return false;
    }

    private function formatUUID($uuid) {
        if (strlen($uuid) == 32 && strpos($uuid, '-') === false) {
            return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
        }
        return $uuid;
    }

    public function getBirahiByIdSapi($id_sapi) {
        $id_sapi = $this->formatUUID($id_sapi);
        $query = 'query GetBirahi($id: UUID!) {
            heatCycles(where: { cattleId: { eq: $id } }, orderBy: { tanggalBirahi: DESC }) {
                id
                tanggalBirahi
                createdAt
            }
        }';
        $res = $this->conn->execute($query, ['id' => (string)$id_sapi]);
        $items = isset($res['data']['heatCycles']) ? $res['data']['heatCycles'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'tanggal_birahi' => $item['tanggalBirahi'],
                'created_at' => $item['createdAt']
            ];
        }
        return new QueryResult($results);
    }

    public function getLatestBirahi($id_sapi) {
        $id_sapi = $this->formatUUID($id_sapi);
        $query = 'query GetLatestBirahi($id: UUID!) {
            heatCycles(where: { cattleId: { eq: $id } }, orderBy: { tanggalBirahi: DESC }, limit: 1) {
                id
                tanggalBirahi
            }
        }';
        $res = $this->conn->execute($query, ['id' => (string)$id_sapi]);
        return isset($res['data']['heatCycles'][0]) ? $res['data']['heatCycles'][0] : null;
    }

    public function deleteBirahi($id) {
        $query = 'mutation DeleteBirahi($id: UUID!) {
            heatCycle_delete(id: $id)
        }';
        $res = $this->conn->execute($query, ['id' => (string)$id]);
        return isset($res['data']['heatCycle_delete']);
    }

    // --- Activity Log Methods ---
    public function logActivity($user_id, $jenis_aktivitas, $deskripsi) {
        $user_id = $this->formatUUID($user_id);
        $query = 'mutation CreateLog($data: ActivityLog_Data! @allow(fields: "userId jenisAktivitas deskripsi createdAt")) {
            activityLog_insert(data: $data)
        }';
        $variables = array(
            'data' => array(
                'userId' => (string)$user_id,
                'jenisAktivitas' => $jenis_aktivitas,
                'deskripsi' => $deskripsi,
                'createdAt' => date('c')
            )
        );
        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['activityLog_insert']);
    }

    public function getHistoryBySapi($id_sapi) {
        // Karena di GraphQL kita mungkin filter via deskripsi (like old way) atau field cattleId
        // Kita asumsikan deskripsi mengandung kode_sapi atau ada cattleId
        $sapi_data = $this->getById($id_sapi);
        $kode = isset($sapi_data['kode_sapi']) ? $sapi_data['kode_sapi'] : '';
        
        $query = 'query GetHistory($kode: String!) {
            activityLogs(where: { deskripsi: { contains: $kode } }, orderBy: { createdAt: DESC }) {
                id
                jenisAktivitas
                deskripsi
                createdAt
                user {
                    nama
                    role
                }
            }
        }';
        
        $res = $this->conn->execute($query, ['kode' => $kode]);
        $items = isset($res['data']['activityLogs']) ? $res['data']['activityLogs'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'jenis' => $item['jenisAktivitas'],
                'deskripsi' => $item['deskripsi'],
                'created_at' => $item['createdAt'],
                'nama' => isset($item['user']['nama']) ? $item['user']['nama'] : 'System',
                'role' => isset($item['user']['role']) ? $item['user']['role'] : 'admin'
            ];
        }
        return $results;
    }

    // Update status reproduksi
    public function updateStatusReproduksi($id, $status) {
        $id = $this->formatUUID($id);
        $query = 'mutation UpdateStatus($id: UUID!, $data: Cattle_Data! @allow(fields: "statusReproduksi updatedAt")) {
            cattle_update(id: $id, data: $data)
        }';
        $variables = array(
            'id' => (string)$id,
            'data' => array(
                'statusReproduksi' => $status,
                'updatedAt' => date('c')
            )
        );
        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['cattle_update']);
    }

    // Set tanggal IB (status opsional agar bisa preserve Bunting)
    public function setTanggalIB($id, $tanggal, $status = null) {
        $id = $this->formatUUID($id);
        $new_status = $status ?: ($tanggal ? "Sudah IB" : "Kosong");
        $query = 'mutation SetIB($id: UUID!, $data: Cattle_Data! @allow(fields: "tanggalIb statusReproduksi updatedAt")) {
            cattle_update(id: $id, data: $data)
        }';
        $variables = array(
            'id' => (string)$id,
            'data' => array(
                'tanggalIb' => $tanggal,
                'statusReproduksi' => $new_status,
                'updatedAt' => date('c')
            )
        );
        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['cattle_update']);
    }

    public function delete($id) {
        $id = $this->formatUUID($id);

        // Hapus semua HeatCycle terkait terlebih dahulu (FK constraint)
        $hcQuery = 'query GetHCIds($cid: UUID!) {
            heatCycles(where: { cattleId: { eq: $cid } }) { id }
        }';
        $hcRes = $this->conn->execute($hcQuery, ['cid' => (string)$id]);
        if (!empty($hcRes['data']['heatCycles'])) {
            foreach ($hcRes['data']['heatCycles'] as $hc) {
                $delHC = 'mutation DelHC($hid: UUID!) { heatCycle_delete(id: $hid) }';
                $this->conn->execute($delHC, ['hid' => (string)$hc['id']]);
                usleep(150000); // 150ms jeda
            }
        }

        // Baru hapus cattle
        $query = 'mutation DeleteCattle($id: UUID! @allow(fields: "id")) {
            cattle_delete(id: $id)
        }';
        $res = $this->conn->execute($query, ['id' => (string)$id]);
        return isset($res['data']['cattle_delete']);
    }

    // Get recent activities
    public function getRecentActivities($limit = 5) {
        $query = 'query GetRecentLogs($limit: Int!) {
            activityLogs(orderBy: { createdAt: DESC }, limit: $limit) {
                id
                jenisAktivitas
                deskripsi
                createdAt
                user {
                    nama
                    role
                }
            }
        }';
        
        $res = $this->conn->execute($query, array('limit' => $limit));
        $items = isset($res['data']['activityLogs']) ? $res['data']['activityLogs'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'jenis_aktivitas' => $item['jenisAktivitas'],
                'deskripsi' => $item['deskripsi'],
                'created_at' => $item['createdAt'],
                'nama' => isset($item['user']['nama']) ? $item['user']['nama'] : 'System',
                'role' => isset($item['user']['role']) ? $item['user']['role'] : 'admin'
            ];
        }
        
        return new QueryResult($results);
    }

    // Get all activities
    public function getAllActivities() {
        $query = 'query GetAllLogs {
            activityLogs(orderBy: { createdAt: DESC }) {
                id
                jenisAktivitas
                deskripsi
                createdAt
                user {
                    nama
                    role
                }
            }
        }';
        
        $res = $this->conn->execute($query);
        $items = isset($res['data']['activityLogs']) ? $res['data']['activityLogs'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item['id'],
                'jenis_aktivitas' => $item['jenisAktivitas'],
                'deskripsi' => $item['deskripsi'],
                'created_at' => $item['createdAt'],
                'nama' => isset($item['user']['nama']) ? $item['user']['nama'] : 'System',
                'role' => isset($item['user']['role']) ? $item['user']['role'] : 'admin'
            ];
        }
        
        return new QueryResult($results);
    }

    public function getReproductionNotifications() {
        $semua_sapi = $this->readAll()->fetchAll(PDO::FETCH_ASSOC);
        $all_latest_birahi = $this->getAllLatestBirahi();
        $notifikasi = [];

        foreach($semua_sapi as $s) {
            $status = isset($s['status_reproduksi']) ? $s['status_reproduksi'] : 'Kosong';
            
            if ($status == 'Sudah Birahi') {
                $latest = isset($all_latest_birahi[$s['id']]) ? $all_latest_birahi[$s['id']] : null;
                if ($latest) {
                     $waktu_birahi = strtotime($latest['tanggal_birahi']);
                     $sisa_jam = round((($waktu_birahi + (12 * 3600)) - time()) / 3600);
                     if ($sisa_jam > 0 && $sisa_jam <= 12) {
                          $notifikasi[] = [
                             'id_sapi' => $s['id'],
                             'kode_sapi' => $s['kode_sapi'],
                             'type' => 'birahi',
                             'icon' => 'fas fa-exclamation-circle text-orange-500',
                             'bg' => 'bg-orange-50 border-orange-100/50',
                             'msg' => "Segera lakukan Inseminasi Buatan! Sapi <b>{$s['kode_sapi']}</b> sedang dalam masa birahi optimal (Sisa {$sisa_jam} Jam).",
                             'created_at' => $latest['tanggal_birahi']
                         ];
                     }
                }
            } elseif ($status == 'Sudah IB') {
                if (!empty($s['tanggal_ib'])) {
                     $waktu_ib = strtotime($s['tanggal_ib']);
                     $waktu_pkb = $waktu_ib + (60 * 24 * 3600);
                     $sisa_hari_pkb = round(($waktu_pkb - time()) / (24 * 3600));
                     
                     if ($sisa_hari_pkb <= 15 && $sisa_hari_pkb > 0) {
                          $notifikasi[] = [
                             'id_sapi' => $s['id'],
                             'kode_sapi' => $s['kode_sapi'],
                             'type' => 'pkb_near',
                             'icon' => 'fas fa-info-circle text-blue-500',
                             'bg' => 'bg-blue-50/70 border-blue-100',
                             'msg' => "Sapi <b>{$s['kode_sapi']}</b> mendekati jadwal Pemeriksaan Kebuntingan (H-{$sisa_hari_pkb}).",
                             'created_at' => $s['tanggal_ib'],
                             'target_date' => date('Y-m-d', $waktu_pkb)
                         ];
                     } elseif ($sisa_hari_pkb <= 0) {
                          $notifikasi[] = [
                             'id_sapi' => $s['id'],
                             'kode_sapi' => $s['kode_sapi'],
                             'type' => 'pkb_now',
                             'icon' => 'fas fa-stethoscope text-blue-600',
                             'bg' => 'bg-blue-100 border-blue-200',
                             'msg' => "Sudah masuk jadwal PKB untuk sapi <b>{$s['kode_sapi']}</b>. Segera lakukan pemeriksaan!",
                             'created_at' => $s['tanggal_ib']
                         ];
                     }
                }
            } elseif ($status == 'Bunting') {
                if (!empty($s['tanggal_ib'])) {
                     $waktu_ib = strtotime($s['tanggal_ib']);
                     $waktu_hpl = $waktu_ib + (283 * 24 * 3600);
                     $sisa_hari_hpl = round(($waktu_hpl - time()) / (24 * 3600));
                     
                      if ($sisa_hari_hpl <= 30 && $sisa_hari_hpl > 0) {
                          $notifikasi[] = [
                             'id_sapi' => $s['id'],
                             'kode_sapi' => $s['kode_sapi'],
                             'type' => 'hpl_near',
                             'icon' => 'fas fa-leaf text-emerald-500',
                             'bg' => 'bg-emerald-50/80 border-emerald-100/50',
                             'msg' => "Persiapan kelahiran! Sapi <b>{$s['kode_sapi']}</b> diestimasi melahirkan (H-{$sisa_hari_hpl}).",
                             'created_at' => $s['tanggal_ib'],
                             'target_date' => date('Y-m-d', $waktu_hpl)
                         ];
                     } elseif ($sisa_hari_hpl <= 0) {
                          $notifikasi[] = [
                             'id_sapi' => $s['id'],
                             'kode_sapi' => $s['kode_sapi'],
                             'type' => 'hpl_now',
                             'icon' => 'fas fa-baby text-emerald-700',
                             'bg' => 'bg-emerald-100 border-emerald-200',
                             'msg' => "Sapi <b>{$s['kode_sapi']}</b> telah melewati Hari Perkiraan Lahir / Sedang proses kelahiran. Segera laporkan kelahiran.",
                             'created_at' => $s['tanggal_ib']
                         ];
                     }
                }
            }
        }
        return $notifikasi;
    }
}
?>
