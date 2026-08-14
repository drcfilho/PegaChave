<?php
namespace App\Models;

class OperadorRepository extends BaseRepository {
    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, usuario, nome, role, permissoes, criado_em FROM administradores ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, usuario, nome, role, permissoes FROM administradores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insert($usuario, $senha, $nome, $role, $permissoes) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $permsJson = json_encode($permissoes);
        
        $stmt = $this->pdo->prepare("INSERT INTO administradores (usuario, senha, nome, role, permissoes) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$usuario, $senhaHash, $nome, $role, $permsJson]);
    }

    public function update($id, $usuario, $senha, $nome, $role, $permissoes) {
        $permsJson = json_encode($permissoes);
        
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE administradores SET usuario = ?, senha = ?, nome = ?, role = ?, permissoes = ? WHERE id = ?");
            return $stmt->execute([$usuario, $senhaHash, $nome, $role, $permsJson, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE administradores SET usuario = ?, nome = ?, role = ?, permissoes = ? WHERE id = ?");
            return $stmt->execute([$usuario, $nome, $role, $permsJson, $id]);
        }
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM administradores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
