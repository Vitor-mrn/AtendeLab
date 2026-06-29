<?php

class UsuariosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT id, nome, email, perfil, status, criado_em
                FROM usuarios
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(
            $usuarios,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'ID inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        $sql = 'SELECT id, nome, email, perfil, status, criado_em
                FROM usuarios
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(404);

            echo json_encode(
                ['erro' => 'Usuário não encontrado.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        echo json_encode(
            $usuario,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            echo json_encode(
                ['erro' => 'Método não permitido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $email === '' || $senha === '') {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'Nome, e-mail e senha são obrigatórios.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'E-mail inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'Perfil inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'Status inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = 'INSERT INTO usuarios (
                        nome,
                        email,
                        senha,
                        perfil,
                        status
                    ) VALUES (
                        :nome,
                        :email,
                        :senha,
                        :perfil,
                        :status
                    )';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha', $senhaHash);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);

            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'mensagem' => 'Usuário cadastrado com sucesso.',
                'id' => (int) $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);

                echo json_encode(
                    ['erro' => 'Já existe um usuário com este e-mail.'],
                    JSON_UNESCAPED_UNICODE
                );

                return;
            }

            http_response_code(500);

            echo json_encode(
                ['erro' => 'Erro ao cadastrar usuário.'],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            echo json_encode(
                ['erro' => 'Método não permitido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $email === '') {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'ID, nome e e-mail são obrigatórios.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'E-mail inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'Perfil inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'Status inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        try {
            $sql = 'UPDATE usuarios
                    SET nome = :nome,
                        email = :email,
                        perfil = :perfil,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':perfil', $perfil);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $consulta = $this->pdo->prepare(
                    'SELECT id FROM usuarios WHERE id = :id'
                );

                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->execute();

                if (!$consulta->fetch()) {
                    http_response_code(404);

                    echo json_encode(
                        ['erro' => 'Usuário não encontrado.'],
                        JSON_UNESCAPED_UNICODE
                    );

                    return;
                }
            }

            echo json_encode(
                ['mensagem' => 'Usuário atualizado com sucesso.'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);

                echo json_encode(
                    ['erro' => 'Já existe outro usuário com este e-mail.'],
                    JSON_UNESCAPED_UNICODE
                );

                return;
            }

            http_response_code(500);

            echo json_encode(
                ['erro' => 'Erro ao atualizar usuário.'],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            echo json_encode(
                ['erro' => 'Método não permitido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);

            echo json_encode(
                ['erro' => 'ID inválido.'],
                JSON_UNESCAPED_UNICODE
            );

            return;
        }

        try {
            $sql = 'DELETE FROM usuarios WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);

                echo json_encode(
                    ['erro' => 'Usuário não encontrado.'],
                    JSON_UNESCAPED_UNICODE
                );

                return;
            }

            echo json_encode(
                ['mensagem' => 'Usuário excluído com sucesso.'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode(
                ['erro' => 'Erro ao excluir usuário.'],
                JSON_UNESCAPED_UNICODE
            );
        }
    }
}