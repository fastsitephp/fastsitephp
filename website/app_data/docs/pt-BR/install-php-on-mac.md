# Instalar o Apache e o PHP no macOS
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='Apple'] { height:80px; width:80px; margin-top:30px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/apple.svg" alt="Apple">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Visão Geral
Apple macOS vem com o Apache e o PHP já instalados, contudo o Apache e a utilização do PHP com ele não está habilitado por padrão. Este tutorial fornece uma visão geral às tarefas chaves relacionadas a habilitar o Apache e PHP; contudo, os passos necessários para cada versão do macOS pode variar muito, sendo assim será necessário que você visite muitas páginas relacionadas a este tópico.

<div class="quick-tip">
    <h3>Dica Rápida</h3>
    <p>Por que o PHP já está instalado você pode utilizar o <a href="https://www.php.net/manual/en/features.commandline.webserver.php" target="_blank" rel="noopener">Servidor Web Integrado do PHP</a> para um desenvolvimento local e assim não há necessidade de uma configuração complexa do Apache. Para informações de como fazer isso veja <a href="edit-with-vs-code">Utilize o Visual Studio Code para Desenvolvimento PHP</a> ou <a href="edit-with-atom">Utilize o Editor Atom do GitHub para Desenvolvimento PHP</a>.</p>
</div>

### Recursos sobre Instalação do Apache, PHP e MySQL para macOS
* https://coolestguidesontheplanet.com/install-apache-mysql-php-on-macos-mojave-10-14/
* https://coolestguidesontheplanet.com/?s=mac+php
* https://websitebeaver.com/set-up-localhost-on-macos-high-sierra-apache-mysql-and-php-7-with-sslhttps
* http://osxdaily.com/2012/09/02/start-apache-web-server-mac-os-x/
* http://osxdaily.com/2012/09/10/enable-php-apache-mac-os-x/
* https://discussions.apple.com/docs/DOC-3083
* https://www.php.net/manual/en/install.macosx.bundled.php


---
## Abrindo o Terminal

O Terminal é comumente utilizado para Desenvolvimento em macOS. Para abrí-lo procure por  “terminal” no Spotlight.

![Abrir o Terminal](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/00_Open_Terminal.png)

&nbsp;

Do terminal você pode iniciar o Apache utilizando o seguinte comando `sudo apachectl start`. Assim que confirmar uma solicitação de inserção de senha será exibida. Utilizando este comando e habilitando o PHP para o Apache, requer privilégios administrativos. Assim que o Apache for iniciado você deve conseguir ver `http://localhost/` em um navegador.

![(Iniciar o Apache)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/01_Start_Apache.png)

---
## Editando a Configuração do Apache para habilitar o PHP

Como mencionado antes, você pode utilizar o servidor integrado do PHP por padrão, porém, para ativar o PHP para o Apache você tem de editar o arquivo de Configuração do Apache. O método mais comum de para fazer isso é utilizando um editor baseado em terminal, como o nano. Para editar as configurações do Apache como nano entre com o comando `sudo
nano /etc/apache2/httpd.conf`.

![Editar o httpd.conf com o nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/02_Edit_httpd_with_nano.png)

&nbsp;

Você então verá o arquivo no terminal juntamente com uma lista de opções de comandos para editar o arquivo.

![Visualize o httpd.conf com o nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/03_httpd_in_nano.png)

&nbsp;

O arquivo será possivelmente um pouco maior que 500 linhas, assim encontrar as linhas para editar sem uma busca pode tomar muito tempo. Para buscar use [control + w], entre “php” e pressione [enter].

![Buscar como nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/04_Search_Nano.png)

&nbsp;

Dependendo da versão do macOS que você tem instalada, você verá uma linha começando com [`#LoadModule php7_module`] ou [`#LoadModule php5_module`]. Remove o caractere [`#`] do início da linha.

![Configurações PHP no httpd.conf](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/05_PHP_Config.png)

&nbsp;

Para salvar use [`control + x`] e digite [`y`].

![Save with nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/06_Save_with_Nano.png)

&nbsp;

Se você não quer utilizar um editor baseado em terminal você pode utilizar o Visual Studio Code para editar o arquivo. O Visual Studio Code também fornece realce a sintaxe do arquivo. Assim que salvar você será questionado se quer tentar novamente como sudo [Retry as Sudo] e então você terá de entrar com sua senha.

![VS Code Salvar como Sudo](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/07_Edit_with_VS_Code.png)

&nbsp;

Um arquivo para cada usuário tem que ser adicionado/editado em [`/etc/apache2/users/{user-name}.conf`].

![Arquivo de Configuração do Apache2](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/08_User_Config.png)

&nbsp;

Dependendo de seu ambiente você talvez você tenha que editar as opções [DocumentRoot] e [Directory] no arquivo [httpd.conf].

![DocumentRoot no httpd.conf](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_mac/09_DocRoot_Config.png)
