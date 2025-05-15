<!-- views/carrinho.php -->
<?php
session_start();
require_once '../models/PedidoModel.php';
$pedidoModel = new PedidoModel();

$produtos = $_SESSION['carrinho'] ?? [];
$subtotal = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Carrinho de Compras</h2>
    <form method="post" action="../controllers/PedidoController.php">
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $id => $qtd): ?>
                    <?php
                        $stmt = $pedidoModel->getConnection()->prepare("SELECT nome, preco FROM produtos WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $stmt->bind_result($nome, $preco);
                        $stmt->fetch();
                        $stmt->close();
                        $totalItem = $preco * $qtd;
                        $subtotal += $totalItem;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($nome) ?></td>
                        <td><?= $qtd ?></td>
                        <td>R$ <?= number_format($preco, 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($totalItem, 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Subtotal:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></p>

        <div class="mb-3">
            <label for="cupom" class="form-label">Cupom de Desconto</label>
            <input type="text" class="form-control" id="cupom" name="cupom">
        </div>

        <div class="mb-3">
            <label for="cep" class="form-label">CEP</label>
            <input type="text" class="form-control" id="cep" name="cep" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <button type="submit" name="finalizar" class="btn btn-success">Finalizar Pedido</button>
    </form>
</body>
</html>

<?php
class PedidoModel {
    private $conn;

    public function getConnection() {
        return $this->conn;
    }
}
