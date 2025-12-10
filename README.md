# CineLive
TesP Programação de Sistemas de Informação – `2025/26` | `PL2`
> Projeto em Sistemas de Informação **[PSI]**:
> - Plataformas de Sistemas de Informação **[PLAT]**
> - Serviços e Interoperabilidade de Sistemas **[SIS]**
> - Acesso Móvel a Sistemas de Informação **[AMSI]**

**– Grupo de Trabalho:**
* Eduardo Carvalho `2024147217`
* Diego Teixeira `2024118127`

---
**– Descrição do Projeto:**
* O **CineLive** é um sistema concebido para modernizar a experiência de ida ao cinema, através da integração de um website, uma aplicação móvel e um back-office de gestão. O sistema procura responder às necessidades dos clientes e dos administradores de cinema, oferecendo uma plataforma intuitiva e completa para a compra de bilhetes, consulta de sessões e gestão de operações internas.
* Do ponto de vista do **cliente**, o sistema disponibiliza um front-office no website e uma aplicação móvel que permitem consultar filmes em exibição, brevemente e destinados a crianças, bem como aceder aos detalhes de cada cinema e filme (sessões, trailers, duração, classificação etária, géneros, etc.).
* Para os **administradores** e **funcionários**, o CineLive disponibiliza um back-office que centraliza a gestão de filmes, cinemas, salas, sessões e reservas, garantindo maior eficiência no trabalho diário.
---
**– Requisitos de Funcionamento:**

**WEB**
* [x] PHP
* [x] Composer
* [x] Servidor Local (Wamp, Xampp)
* [x] Cliente MySQL

**MOBILE**
* [x] Android Studio
* [x] Emulador de Android
---
**– Instruções de Como Correr o Projeto:**

**WEB**
1. Fazer download do repositório Git `CineLive` e extrair a pasta zipada no ficheiro Web do Servidor Local.
2. Abrir a pasta `CineLive` no terminal e fazer os seguintes comandos:
```
composer install
php init
```
3. Ligar o servidor local.
4. Criar uma base de dados nova no seu cliente de SQL.
5. Transferir e importar a base de dados do ficheiro `cinelive.sql`.
6. Alterar o ficheiro `common/config/main-local.php` e mudar o nome da BD e credenciais.
7. Ir ao browser e aceder a URL de modo a correr a aplicação no servidor local.
```
{localhost}/CineLive/Web/frontend/web
```
**MOBILE**
1. Fazer download do repositório Git `CineLive` e extrair a pasta zipada.
2. Abrir a pasta `Mobile` no Android Studio.
3. Correr o projeto.
---
❓ Qualquer dúvida ou questão, contactar o responsável pelo projeto `Eduardo Carvalho` através do seu email institucional (**2241868@my.ipleiria.pt**) ou no **Microsoft Teams**.

---
![IPLEIRIA](https://upload.wikimedia.org/wikipedia/commons/9/9a/Log%C3%B3tipo_Polit%C3%A9cnico_Leiria_01.png)
