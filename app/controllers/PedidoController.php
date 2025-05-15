// controllers/PedidoController.php
<?php
require_once '../models/PedidoModel.php';

class PedidoController {
    private $pedidoModel;

    public function __construct() {
        $this->pedidoModel = new PedidoModel();
    }

    public function adicionarProdutoAoCarrinho() {
        $produtoId = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];
        $this->pedidoModel->adicionarAoCarrinho($produtoId, $quantidade);
        header('Location: ../views/carrinho.php');
    }

    public function finalizarPedido() {
        $cupom = $_POST['cupom'] ?? '';
        $cep = $_POST['cep'];
        $email = $_POST['email'];

        $produtos = $_SESSION['carrinho'] ?? [];
        $subtotal = 0;

        foreach ($produtos as $id => $qtd) {
            // Recuperar valor unitário, considerando o id do produto, aqui tenho que testar direito o funcionamento
            $stmt = $this->pedidoModel->conn->prepare("SELECT preco FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($preco);
            $stmt->fetch();
            $stmt->close();
            $subtotal += $preco * $qtd;
        }

        $desconto = $this->pedidoModel->aplicarCupom($cupom, $subtotal);
        $frete = $this->pedidoModel->calcularFrete($subtotal);
        $total = ($subtotal - $desconto) + $frete;

        $endereco = $this->pedidoModel->buscarEnderecoPorCep($cep);

        $mensagem = "<h3>Pedido Finalizado</h3>" .
                    "<p>Subtotal: R$" . number_format($subtotal, 2, ',', '.') . "</p>" .
                    "<p>Desconto: R$" . number_format($desconto, 2, ',', '.') . "</p>" .
                    "<p>Frete: R$" . number_format($frete, 2, ',', '.') . "</p>" .
                    "<p>Total: R$" . number_format($total, 2, ',', '.') . "</p>" .
                    "<p>Endereço: {$endereco['logradouro']}, {$endereco['bairro']} - {$endereco['localidade']}/{$endereco['uf']}</p>";

        $this->pedidoModel->enviarEmail($email, 'Confirmação de Pedido', $mensagem);
        unset($_SESSION['carrinho']);
        echo "<p>Pedido finalizado com sucesso! Um e-mail foi enviado para confirmação.</p>";
    }
}

$controller = new PedidoController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['finalizar'])) {
        $controller->finalizarPedido();
    } elseif (isset($_POST['adicionar'])) {
        $controller->adicionarProdutoAoCarrinho();
    }
}
