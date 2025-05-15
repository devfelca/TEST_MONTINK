// models/PedidoModel.php
<?php
class PedidoModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'erp');
        if ($this->conn->connect_error) {
            die("Conexão falhou: " . $this->conn->connect_error);
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function removerPedido($id) {
        $stmt = $this->conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function atualizarStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function webhookHandler($data) {
        if (!isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }

        $id = (int)$data['id'];
        $status = $data['status'];

        if ($status === 'cancelado') {
            $this->removerPedido($id);
            echo json_encode(['success' => 'Pedido cancelado e removido']);
        } else {
            $this->atualizarStatus($id, $status);
            echo json_encode(['success' => 'Status atualizado']);
        }
    }

    public function buscarEnderecoPorCep($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) !== 8) {
            return ['error' => 'CEP inválido'];
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $resposta = file_get_contents($url);

        if ($resposta === false) {
            return ['error' => 'Não foi possível consultar o CEP'];
        }

        return json_decode($resposta, true);
    }

    public function adicionarAoCarrinho($produtoId, $quantidade) {
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        if (!isset($_SESSION['carrinho'][$produtoId])) {
            $_SESSION['carrinho'][$produtoId] = 0;
        }

        $_SESSION['carrinho'][$produtoId] += $quantidade;
    }

    public function calcularFrete($subtotal) {
        if ($subtotal > 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        } else {
            return 20;
        }
    }

    public function aplicarCupom($codigo, $subtotal) {
        $stmt = $this->conn->prepare("SELECT desconto, minimo, validade FROM cupons WHERE codigo = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $stmt->bind_result($desconto, $minimo, $validade);
        if ($stmt->fetch()) {
            if (strtotime($validade) >= time() && $subtotal >= $minimo) {
                $stmt->close();
                return $desconto;
            }
        }
        $stmt->close();
        return 0;
    }

    public function enviarEmail($destinatario, $assunto, $mensagem) {
        $headers = "Content-Type: text/html; charset=UTF-8\r\n" . "From: ERP <no-reply@erp.com>\r\n";

        return mail($destinatario, $assunto, $mensagem, $headers);
    }
}
