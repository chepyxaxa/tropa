
<?php

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByLogin(string $login): array|false
    {
        $sql = 'SELECT * FROM users WHERE login = :login LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'login' => $login
        ]);

        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $sql = 'SELECT id, login, full_name, phone, email
                FROM users
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch();
    }

    public function create(
        string $login,
        string $password,
        string $fullName,
        string $phone,
        string $email
    ): bool {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users
                (login, password_hash, full_name, phone, email)
                VALUES
                (:login, :password_hash, :full_name, :phone, :email)';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'login' => $login,
            'password_hash' => $passwordHash,
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email
        ]);
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify(
            $password,
            $user['password_hash']
        );
    }
}
