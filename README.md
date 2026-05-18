# UniRide

Plataforma web de caronas compartilhadas para estudantes universitários.

Projeto desenvolvido para a disciplina de Projeto Integrador do curso de Tecnologia em Análise e Desenvolvimento de Sistemas — SENAC, 2026.

---

## Sobre o projeto

O UniRide conecta motoristas e passageiros da mesma faculdade para compartilhar caronas no trajeto até o campus. Apenas estudantes com e-mail institucional (`.edu.br`) podem se cadastrar, garantindo que todos os usuários sejam da comunidade acadêmica.

A ideia é ajudar os estudantes a economizar dinheiro com transporte, reduzir o tempo de deslocamento e diminuir o número de carros circulando com só uma pessoa.

---

## Demonstração

▶ Vídeo de demonstração: https://www.youtube.com/watch?v=XHwiBwp6ki4

---

## Funcionalidades

**Para qualquer usuário:**
- Cadastro e login com e-mail institucional
- Dashboard com resumo da atividade
- Edição do perfil
- Histórico de viagens

**Para o motorista:**
- Criar oferta de carona (rota, horário, vagas, valor, dados do veículo)
- Ver caronas que ofereceu
- Receber e responder solicitações de passageiros
- Marcar uma carona como concluída ou cancelar

**Para o passageiro:**
- Buscar caronas disponíveis com filtros
- Ver detalhes da carona e do motorista
- Solicitar uma vaga e acompanhar o status

---

## Tecnologias usadas

- **PHP 8** — linguagem do backend
- **MySQL 8** — banco de dados
- **HTML, CSS e JavaScript** — frontend
- **PDO** — acesso ao banco de dados de forma segura
- **XAMPP** — servidor Apache + MySQL para rodar localmente
- **Git e GitHub** — controle de versão

---

## Como rodar o projeto

### O que você precisa ter instalado
- [XAMPP](https://www.apachefriends.org/pt_br/download.html) com PHP 8 ou superior
- Um navegador (Chrome, Firefox ou Edge)

### Passo a passo

**1. Baixe o projeto**

Clone este repositório ou baixe como ZIP:

```bash
git clone https://github.com/SEU-USUARIO/UniRide.git
```

**2. Coloque a pasta no XAMPP**

Mova a pasta `uniride/` para dentro de `C:\xampp\htdocs\`.

O caminho final fica assim: `C:\xampp\htdocs\uniride\`

**3. Inicie o XAMPP**

Abra o XAMPP Control Panel e clique em **Start** para o **Apache** e para o **MySQL**.

**4. Importe o banco de dados**

- Abra `http://localhost/phpmyadmin` no navegador
- Clique em **Importar**
- Selecione o arquivo `uniride/sql/uniride.sql`
- Clique em **Importar** no final da página

Isso cria o banco `uniride` com as tabelas e alguns dados de teste.

**5. Acesse o sistema**

Abra no navegador:

```
http://localhost/uniride/public/index.php
```

**6. Faça login com um usuário de teste**

| E-mail | Senha |
|---|---|
| `lucas.mendes@senac.edu.br` | `senha123` |
| `camila.ferreira@senac.edu.br` | `senha123` |
| `andre.silva@senac.edu.br` | `senha123` |

Ou crie uma conta nova usando qualquer e-mail terminado em `.edu.br`.

---

## Estrutura de pastas

```
uniride/
├── config/         → conexão com o banco
├── includes/       → arquivos PHP reutilizáveis (header, footer, funções)
├── public/         → páginas acessadas pelo navegador
├── assets/
│   ├── css/        → estilos
│   └── js/         → scripts
├── sql/            → script de criação do banco
├── README.md
└── .gitignore
```

---

## Equipe

- José Batista Neto
- Letícia Sá Oliveira
- Lucas Vinicius Silva dos Santos

**Professor orientador:** Anderson Clayton Garcia Lopes

---

**SENAC EAD — Tecnologia em Análise e Desenvolvimento de Sistemas — 2026**
