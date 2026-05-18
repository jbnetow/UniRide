-- =====================================================
-- UniRide - Banco de Dados
-- Projeto Integrador SENAC - 2026
-- Stack: PHP 8 + MySQL 8
--
-- Senha dos usuarios de teste: senha123
-- =====================================================

DROP DATABASE IF EXISTS uniride;
CREATE DATABASE uniride
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE uniride;

-- =====================================================
-- Tabela: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    senha           VARCHAR(255) NOT NULL,
    cpf             VARCHAR(14)  NOT NULL,
    telefone        VARCHAR(20),
    instituicao     VARCHAR(150),
    curso           VARCHAR(100),
    data_cadastro   DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo           TINYINT(1) DEFAULT 1,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- =====================================================
-- Tabela: caronas
-- =====================================================
CREATE TABLE caronas (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    motorista_id         INT NOT NULL,
    origem               VARCHAR(200) NOT NULL,
    destino              VARCHAR(200) NOT NULL,
    data_viagem          DATE NOT NULL,
    horario_saida        TIME NOT NULL,
    vagas_total          INT  NOT NULL,
    vagas_disponiveis    INT  NOT NULL,
    valor_por_passageiro DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    veiculo_modelo       VARCHAR(100) NOT NULL,
    veiculo_placa        VARCHAR(10)  NOT NULL,
    veiculo_cor          VARCHAR(30)  NOT NULL,
    observacoes          TEXT,
    status               ENUM('ativa','concluida','cancelada') DEFAULT 'ativa',
    data_criacao         DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_caronas_motorista
        FOREIGN KEY (motorista_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    INDEX idx_status (status),
    INDEX idx_data_viagem (data_viagem)
) ENGINE=InnoDB;

-- =====================================================
-- Tabela: solicitacoes
-- =====================================================
CREATE TABLE solicitacoes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    carona_id         INT NOT NULL,
    passageiro_id     INT NOT NULL,
    status            ENUM('pendente','aceita','recusada','cancelada') DEFAULT 'pendente',
    data_solicitacao  DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_resposta     DATETIME NULL,

    CONSTRAINT fk_solicit_carona
        FOREIGN KEY (carona_id) REFERENCES caronas(id) ON DELETE CASCADE,

    CONSTRAINT fk_solicit_passageiro
        FOREIGN KEY (passageiro_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    UNIQUE KEY uk_carona_passageiro (carona_id, passageiro_id),
    INDEX idx_status_sol (status)
) ENGINE=InnoDB;

-- =====================================================
-- Dados de teste — senha = "senha123" para todos
-- (hashes bcrypt válidos para password_verify do PHP)
-- =====================================================
INSERT INTO usuarios (nome, email, senha, cpf, telefone, instituicao, curso) VALUES
('Lucas Mendes',    'lucas.mendes@senac.edu.br',
 '$2b$10$6Mapi6/rw7oEXgs01RJUZO6zryyC6UMq6l0862h51wbWNY2Fa7ba2',
 '111.222.333-44', '(11) 98888-1111', 'SENAC', 'Engenharia de Software'),

('Camila Ferreira', 'camila.ferreira@senac.edu.br',
 '$2b$10$e/gt3HCbMk6eLlc91EpnhO6KbAjWf4Ro.uV/xe.8u6y9ScwWANxtO',
 '222.333.444-55', '(11) 97777-2222', 'SENAC', 'Administração'),

('André Silva',     'andre.silva@senac.edu.br',
 '$2b$10$REFZJO2tfPqM8ffP1y8MMuJZZV9uxWMjsVoR290MFMDJ8nuUdIhcy',
 '333.444.555-66', '(11) 96666-3333', 'SENAC', 'Análise e Desenvolvimento de Sistemas');

INSERT INTO caronas
   (motorista_id, origem, destino, data_viagem, horario_saida,
    vagas_total, vagas_disponiveis, valor_por_passageiro,
    veiculo_modelo, veiculo_placa, veiculo_cor, observacoes)
VALUES
(1, 'Zona Leste - Itaquera', 'SENAC Santo Amaro', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '17:30:00',
 3, 3, 6.00, 'VW Gol 2018', 'ABC-1D23', 'Prata', 'Não fumante, aceita pet pequeno.'),

(1, 'Zona Leste - Itaquera', 'SENAC Santo Amaro', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '17:30:00',
 3, 3, 6.00, 'VW Gol 2018', 'ABC-1D23', 'Prata', 'Saída pontual.'),

(3, 'Guarulhos - Centro',    'SENAC Santo Amaro', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '08:00:00',
 2, 2, 12.00, 'Fiat Mobi 2020', 'XYZ-4E56', 'Branco', 'Aceito conversa boa no trajeto.');

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
