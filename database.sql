CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL
);

CREATE TABLE variacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    nome VARCHAR(100),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    variacao_id INT NOT NULL,
    quantidade INT NOT NULL,
    FOREIGN KEY (variacao_id) REFERENCES variacoes(id) ON DELETE CASCADE
);

CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL,
    minimo_subtotal DECIMAL(10,2) NOT NULL,
    validade DATE NOT NULL
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente','pago','cancelado') DEFAULT 'pendente',
    subtotal DECIMAL(10,2),
    frete DECIMAL(10,2),
    total DECIMAL(10,2),
    cep VARCHAR(9),
    endereco TEXT,
    email_cliente VARCHAR(255)
);

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    variacao_id INT,
    quantidade INT,
    preco_unitario DECIMAL(10,2),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (variacao_id) REFERENCES variacoes(id)
);
