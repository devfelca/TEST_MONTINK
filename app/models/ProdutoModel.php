<?php
class ProdutoModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli('host', 'root', '', 'bd');
        if ($this->conn->connect_error) {
            die("Conexão falhou: " . $this->conn->connect_error);
        }
    }

    public function salvarProduto($data) {
        $stmt = $this->conn->prepare("INSERT INTO produtos (nome, preco) VALUES (?, ?)");
        $stmt->bind_param("sd", $data['nome'], $data['preco']);
        $stmt->execute();
        $produtoId = $stmt->insert_id;
        $stmt->close();

        foreach ($data['variacoes'] as $nome => $estoque) {
            $stmtVar = $this->conn->prepare("INSERT INTO variacoes (produto_id, nome) VALUES (?, ?)");
            $stmtVar->bind_param("is", $produtoId, $nome);
            $stmtVar->execute();
            $variacaoId = $stmtVar->insert_id;
            $stmtVar->close();

            $stmtEst = $this->conn->prepare("INSERT INTO estoque (variacao_id, quantidade) VALUES (?, ?)");
            $stmtEst->bind_param("ii", $variacaoId, $estoque);
            $stmtEst->execute();
            $stmtEst->close();
        }

        return $produtoId;
    }

    public function listarProdutos() {
        $result = $this->conn->query("SELECT * FROM produtos");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
