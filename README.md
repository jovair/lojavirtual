# Projeto E-commerce

Este projeto está sendo desenvolvido com base no conhecimento adquirido no curso de PHP oferecido pela HCODE que apresenta o desenvolvimento do zero no [Curso de PHP 7](https://www.udemy.com/curso-completo-de-php-7/) disponível na plataforma da Udemy e no site do [HTML5dev.com.br](https://www.html5dev.com.br/curso/curso-completo-de-php-7). O template usado no projeto [Almsaeed Studio](https://almsaeedstudio.com)

Os comentários do projeto serão numerados de acordo com o número das aulas do curso da Hcode para facilitar buscas futuras

98 - Banco de Dados: O primeiro passo para o desenvolvimento é a projeção do Banco de Dados. Neste projeto o BD já veio pronto, inclusive com alguns dados inseridos para ficar mais fácil didaticamente. Como o foco aqui é o desenvolvimento do site, não foi dada a ênfase de construção ao BD.

99 - Configurando o projeto: Esse conteúdo é sobre a configuração do Composer, que faz o gerenciamento de aplicações de terceiros e classes locais do projeto e também de um servidor web local. Neste momento foi criada a estrutura de pastas do projeto. As minhas pastas estão em D:\projetos\lojavirtual. Para a execução do Composer foi fornecido o script que foi executado o comando composer update com o bash here console, do Github, dentro da pasta lojavirtual, que automaticamente criou a pasta vendor e dentro dela foram baixados e instalados outros pacotes que serão usados pelo projeto.
O projeto será executado com todas as nuances de um site remoto, mas plenamente no servidor local, no meu caso, o Xampp com o servidor Apache e o MariaDb para o MySql. Tanto na configuração do Composer como do servidor local foram fornecidos os os scripts, sendo necessário apenas os ajustes para a minha conta no Github para o funcionamento do Composer. Na montagem do servidor local foram feistos os ajustes no arquivo D:\xampp\apache\conf\extra\httpd-vhosts.conf do servidor apache e também o arquivo C:\Windows\System32\drivers\etc\hosts. Ambos permitem que sejam criados servidores web locais. O segundo permite que sejam dados diferentes nomes de domínio para o endereço localhost no sistema operacional e o primeiro permite que sejam criadas várias conexões com os mesmos nomes nomes para o servidor apache. Após fazer as configurações com os scripts enviados, foi necessário reiniciar os serviços do xampp.

100 - Autoload das classes do projeto: Iniciando o projeto propriamente dito, começamos pela sua estrutura física, começando pelo posicionamento da primeira classe, já pronta, com o namespace DB, que será localizada, por meio do autoload, que vai permitir a conexão com o banco de dados, a saber: No meu caso, o projeto está dentro da pasta D:\projetos\lojavirtual. Dentro desta pasta já havia sido criada na aula anterior, a estrutura básica com o Compuser, o Slim e o Raintpl, cada uma destas estruturas dentro da pasta vendor, que é por onde o Composer gerencia todo o projeto. O primeiro é o próprio Composer, framework de gestão de aplicações proprietárias e de terceiros, o segundo é o framework para a criação de rotas e o terceiro é o template que será usado no projeto. Dentro da pasta vendor foi copiada a pasta hcodebr fornecida pelos professores; depois da pasta copiada, de dentro do diretório raiz do projeto, que é o lojavirtual, foi aberto o bash here e executado o compando composer dump-outoload para que ela seja reconhecida pelo Composer. No script do Composer tem uma linha de comando do autoload psr-4 que é um gerenciador de autoloads de classes, com a orientação de criar o caminho para as pastas de classes: "Hcode\\": "vendor\\hcodebr\\php-classes\\src". 

101 - Classe Page: A classe Page é responsável pelo gerenciamento das páginas do site. É ela quem recebe o conteúdo do header, body e footer do código HTML e também os códigos PHP e faz o merge desses dados, gerando o conteúdo dinamicamente. O conteúdo HTML fica armazenado na pasta ..\lojavirtual\views e depois de mesclado vai para ..\lojavirtual\views-cache. Essa classe tem o método mágico __construct e __destruct para construir e destruir as páginas dinâmicas. Na construção da classe Page, ela carrega o arquivo header.html no método __construct, depois ela carrega o método setTpl que é responsável pela parte do body do site e na saída, quando ela chamar o método __destruct, ela carrega o arquivo footer.html. como existe mais de uma classe que precisa da mesma estrutura de foreach, foi criado um foreach que até aqui está sendo usado pelos métodos configure e setTpl. O método configure é um método estático da classe Tpl. A última etapa é a criação da rota dentro do index.php, que, ao ser digitado o endereço do site no browser, segue todas as rotas existentes no projeto e abre a página.

102 - Template do site: O template do projeto em HTML/CSS/JS foi fornecido pela Hcode e servirá de base para toda a codificação PHP, tornando-o em um site dinâmico. a estrutura do site foi armazenada na pasta ..\lojavirtual\res\site. O arquivo index.html dentro desta pasta foi separado em três partes: header/body/footer e os conteúdos correspondentes foram inseridos nos arquivos html com o mesmo nome dentro da pasta ..\lojavirtual\views. Por último foram ajustados os caminhos referentes a Javascript, css, etc para a pasta \res\site, onde está aramazenada a estrutura do site.

103 - Template Admin: O template de administração do site é um Open Source fornecido pela AdminLTE. Ele será executado pela classe PageAdmin, que é uma herança da classe Page, porque a maior parte dos recursos necessários já estão prontos na classe pai. A única coisa que precisa ser feita de diferente para a chamada da página de admin é o caminho onde está a estrutura HTML. Essa estrutura está dentro da pasta 
..\lojavirtual\views\admin, onde estão o header/body/footer da página de administração e o template está em 
..\lojavirtual\res\admin. Para que seja carregada a página de administração e não do site, foi necessário fazer alterações, tanto na rota executada pelo index.php quanto na classe Page. Na classe Page é definido que o valor default para carregar o conteúdo do site é true e para que não haja erro na execução da página de administração, na rota alguns valores são alterados para false e assim a página de administração é carregada. O arquivo starter.html é o arquivo mais limpo fornecido pela AdminLTE para construir a estrutura de administração e foi ele quem foi fatiado em header/body/footer, assim como o site foi feito anteriormente.

104 - Admin login: Na pasta res\admin\exemples tem o arquivo login, do template da pasta de administração que é usado como modelo. Ele foi copiado para a pasta views\admin para ser alterado e atender à programação PHP. No index.php foram criadas duas rotas para a classe PageAdmin: uma get para a abertura da tela de login e outra post, quando os dados do usuários estão sendo enviados para a aplicação. No get foi feita a alteração default como explicado na aula anterior, para que seja aberta a tela de login e não a do site. Do mesmo modo, foram feitas alterações na classe Page. No diretório src onde ficam as classes foi criada a pasta model e dentro dela a classe estática User para validar o login. Ela recebe os dados do Banco de Dados e também o login e senha digitados pelo usuário e compara para ver se ele existe e se é válido. Essa classe é uma herança da classe Model, que está dentro da pasta src, que por sua vez foi criada para fazer getters and setters de todos os Models que serão desenvolvidos pelo projeto. Com o código de login finalizado, foi alterado o caminho do href dentro do arquivo header.html de administração, na div Sign Out para /admin/logout. Na tela administração, na aba do usuário, ao clicar no botão Sign Out, o usuário sai da tela e volta para a tela de login.

105 - CRUD: Este conteúdo se refere à inserção, atualização e exclusão de usuários. Para as atividades desta aula foram enviados três arquivos HTML, templates das páginas, de acordo com a função de cada um deles, que foram salvos na pasta ..\lojavirtual\views\admin (users, users-create e users-update). Os arquivos HTML foram alterados, tanto o caminho em href para as suas devidas pastas de referência, quanto para a inserção ou recepção de dados do Banco de Dados. Primeiro foram construídas as bases de todas as rotas necessárias no arquivo index.php. Essas rotas foram passando por ajustes para atender as funcionalidades da aplicação. A primeira rota a ser trabalhada foi a de listagem dos usuários na tela de usuários do sistema; o arquivo header.html foi alterado para que lista de usuários seja carregada. Do lado esquerdo da tela foi trocado o LINK para Usuários, de modo que, ao ser clicado, a lista de usuários é carregada na página. Na classe User foi criado o método estático listAll() para buscar os dados no Banco de Dados; este método é invocado na rota /admin/users. O arquivo users-create.html foi alterado trazer o formulário de usuários vazios e permitir que sejam preenchidos os dados para a criação de um novo usuário. Os dados coletados pertencem a duas tabelas no Banco de Dados: tb_users e tb_persons, neste caso, foi criada a procedure sp_users_save para fazer a distribuição dos dados. O método save() chama a procedure, passando os dados que serão armazenados no Banco de Dados. Para fazer a alteração de um um usuário já existente, a rota busca esses usuários no banco pelo método get e carrega na tela de edição de usuários. Ao fazer as alterações na tela, os dados são enviados para o banco pelo método update. Esse método chama a procedure sp_usersupdate_save que faz a inserção dos dados nas duas tabelas do Banco de Dados e faz um select, trazendo os dados atualizados de volta para a aplicação. A exclusão de um usuário se dá pelo método delete, na rota de exclusão, que chama a procedure sp_users_delete, que exclui o usuário do Banco de Dados.