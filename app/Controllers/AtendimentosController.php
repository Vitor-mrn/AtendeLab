<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    private function json(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public function listar(): void
    {
        $sql = "SELECT
                    a.id,
                    p.nome AS pessoa_nome,
                    t.nome AS tipo_nome,
                    u.nome AS responsavel_nome,
                    a.descricao,
                    a.status,
                    a.data_atendimento,
                    a.hora_atendimento,
                    a.observacao
                FROM atendimentos a
                INNER JOIN pessoas p
                    ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t
                    ON t.id = a.tipo_atendimento
                INNER JOIN usuarios u
                    ON u.id = a.usuario_id
                ORDER BY a.id DESC";

        $this->json($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function buscar(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $this->json(['erro' => 'ID inválido.'], 400);
            return;
        }

        $stmt = $this->pdo->prepare(
            "SELECT
                a.*,
                p.nome AS pessoa_nome,
                t.nome AS tipo_nome,
                u.nome AS responsavel_nome
            FROM atendimentos a
            INNER JOIN pessoas p
                ON p.id = a.pessoa_id
            INNER JOIN tipos_atendimentos t
                ON t.id = a.tipo_atendimento
            INNER JOIN usuarios u
                ON u.id = a.usuario_id
            WHERE a.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            $this->json([
                'erro' => 'Atendimento não encontrado.'], 404);
            return;
        }

        $this->json($atendimento);
    }

    public function criar(): void
    {
        $pessoaId = filter_var(
            INPUT_POST,
            'pessoa_id',
            FILTER_VALIDATE_INT
        );

        $tipoId = filter_var(
            INPUT_POST,
            'tipo_atendimento_id',
            FILTER_VALIDATE_INT
        );
        $descricao = trim($_POST['descricao'] ?? '');
        $data = $_POST['data_atendimento'] ?? '';
        $hora = $_POST['horario_atendimento'] ?? '';
        $status = $_POST['status'] ?? 'aberto';

        if (!$pessoaId || !$tipoId || !$usuarioId || $descricao === '' ||
            $data === '' || $hora === ''
        ) {
            $this->json(['erro' => 'Preencha todos os campos obrigatórios.'], 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO atendimentos
            (
                pessoa_id,tipo_atendimento,usuario_id,data_atendimento,hora_atendimento,descricao,
            status
            )
            VALUES
            (:pessoa_id, :tipo_atendimento, :usuario_id, :data_atendimento, :hora_atendimento, :descricao,
            :status
            )"
        );
        $stmt->execute([
            'pessoa_id' => $pessoaId,
            'tipo_atendimento' => $tipoId,
            'usuario_id' => $usuarioId, 
            'data_atendimento' => $data,
            'hora_atendimento' => $hora,
            'descricao' => $descricao,
            'status' => $status
        ]);

        $this->json(['mensagem' => 'Atendimento cadastrado com sucesso.'], 201);
    }
    public function alterarStatus(): void
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT
        $status = $_POST['status'] ?? '';
        $observacao = trim($_POST['observacao'] ?? ''));

        if (!$id || !in_array(
                $status,
                ['aberto', 'em_andamento', 'concluido'],
                true
            )
        ) {
            $this->json(['erro' => 'Status inválido.'], 422);
            return;
        }

        if ($status === 'concluido' && $observacao === '') {
            $this->json([
                'erro' => 'Informe a observação final.'
            ], 422);
            return;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE atendimentos
            SET status = :status, observacao = :observacao
            WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'observacao' => $observacao ! ' '? $observacao : null,
        ]);
        $this->json(['mensagem' => 'Status atualizado com sucesso.']);
    }
}