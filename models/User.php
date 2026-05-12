<?php
class User {
    private $conn;

    public $id;
    public $nama;
    public $email;
    public $password;
    public $role;
    public $nip;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function mapData($data) {
        if (!$data) return null;
        return [
            'id' => $data['id'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'role' => $data['role'],
            'nip' => isset($data['nip']) ? $data['nip'] : null,
            'created_at' => isset($data['createdAt']) ? $data['createdAt'] : null
        ];
    }

    // Read all users
    public function readAll() {
        $query = 'query ListUsers {
            users(orderBy: { createdAt: ASC }) {
                id
                nama
                email
                role
                nip
                createdAt
            }
        }';
        
        $res = $this->conn->execute($query);
        $items = isset($res['data']['users']) ? $res['data']['users'] : array();
        
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->mapData($item);
        }
        
        return new QueryResult($results);
    }

    // Get by ID
    public function getById($id) {
        $query = 'query GetUser($id: UUID!) {
            user(id: $id) {
                id nama email role nip
            }
        }';
        
        $res = $this->conn->execute($query, ['id' => (string)$id]);
        return $this->mapData(isset($res['data']['user']) ? $res['data']['user'] : null);
    }

    // Create user
    public function create() {
        $query = 'mutation CreateUser($data: User_Data! @allow(fields: "id nama email password role nip createdAt")) {
            user_insert(data: $data)
        }';
        
        $variables = array(
            'data' => array(
                'nama' => $this->nama,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $this->role,
                'nip' => $this->nip,
                'createdAt' => date('c')
            )
        );

        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['user_insert']['id']) ? $res['data']['user_insert']['id'] : false;
    }

    // Update password
    public function updatePassword($id, $new_hashed_password) {
        $query = 'mutation UpdatePass($id: UUID!, $data: User_Data! @allow(fields: "password")) {
            user_update(id: $id, data: $data)
        }';
        $variables = array(
            'id' => (string)$id,
            'data' => array(
                'password' => $new_hashed_password
            )
        );
        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['user_update']);
    }

    // Update NIP & Nama
    public function updateNip($id, $nama, $nip) {
        $query = 'mutation UpdateNip($id: UUID!, $data: User_Data! @allow(fields: "nama nip")) {
            user_update(id: $id, data: $data)
        }';
        $variables = array(
            'id' => (string)$id,
            'data' => array(
                'nama' => $nama,
                'nip' => $nip
            )
        );
        $res = $this->conn->execute($query, $variables);
        return isset($res['data']['user_update']);
    }

    // Delete user
    public function delete($id) {
        $query = 'mutation DeleteUser($id: UUID!) {
            user_delete(id: $id)
        }';
        $res = $this->conn->execute($query, ['id' => (string)$id]);
        return isset($res['data']['user_delete']);
    }

    // Check if email exists
    public function emailExists($email) {
        $query = 'query CheckEmail($email: String!) {
            users(where: { email: { eq: $email } }) { id }
        }';
        $res = $this->conn->execute($query, ['email' => $email]);
        return !empty($res['data']['users']);
    }
}
?>
