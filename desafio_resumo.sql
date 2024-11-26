-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Senha armazenada com hashing
    tipo ENUM('aluno', 'professor', 'admin') NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    setor VARCHAR(100) NOT NULL,
    turma VARCHAR(50), -- Somente para alunos
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela para configurações de reset de senha
CREATE TABLE IF NOT EXISTS solicitacoes_reset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela para solicitações de professores
CREATE TABLE IF NOT EXISTS solicitacoes_professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Senha armazenada com hashing
    cpf VARCHAR(11) UNIQUE NOT NULL, -- Adicionando a coluna CPF
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente'
);


-- Tabela de temas
CREATE TABLE IF NOT EXISTS temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema VARCHAR(255) NOT NULL,
    professor_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de redações
CREATE TABLE IF NOT EXISTS redacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    tema_id INT NOT NULL,
    redacao TEXT NOT NULL,
    nota DECIMAL(3, 1), -- Nota de 0 a 10
    criterios TEXT, -- Critérios de avaliação necessários pelo professor
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_correcao TIMESTAMP NULL,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tema_id) REFERENCES temas(id) ON DELETE CASCADE
);

-- Tabela de mensagens
CREATE TABLE IF NOT EXISTS mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remetente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resposta BOOLEAN DEFAULT 0,
    data_resposta TIMESTAMP NULL,
    resposta_texto TEXT, -- Renomeada para evitar duplicidade
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de logs de ações
CREATE TABLE IF NOT EXISTS logs_acoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(255) NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    redacao_id INT NOT NULL,
    professor_id INT NOT NULL,
    nota DECIMAL(3, 1) NOT NULL, -- Nota de 0 a 10
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (redacao_id) REFERENCES redacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de tentativas de login
CREATE TABLE IF NOT EXISTS login_attempts (
    ip_address VARCHAR(45) NOT NULL,
    attempts INT NOT NULL DEFAULT 1,
    last_attempt DATETIME NOT NULL,
    PRIMARY KEY (ip_address)
);

-- Verificar usuário administrador
SELECT * FROM usuarios WHERE email = 'admin@example.com';

-- Inserir usuário administrador (se não existir)
INSERT INTO usuarios (nome, email, senha, tipo, cpf) 
VALUES ('admin', 'admin@example.com', 'admin123', 'admin', '12345678901')
ON DUPLICATE KEY UPDATE senha = 'admin123';
