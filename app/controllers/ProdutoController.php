<?php
require_once 'models/ProdutoModel.php';

class ProdutoController {
    public function handleRequest() {
        $action = $_GET['action'] ?? 'form';
        $model = new ProdutoModel();

        if ($action === 'save') {
            $produtoId = $model->salvarProduto($_POST);
            header("Location: index.php");
            exit;
        }

        $produtos = $model->listarProdutos();
        include 'views/produto_form.php';
    }
}
