# Instalar o IIS e o PHP em um Servidor Windows ou Destkop
<style>
    .logo-images { display:block; text-align:center; }
    .logo-images img { display:inline; height:150px; }
    .logo-images img[alt='Microsoft'] { display:block; margin:auto; }
    .logo-images img[alt='PHP'] { height:80px; margin-top:40px; margin-right:40px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:700px) {
        .logo-images { display:inline-flex; text-align:left; }
    .logo-images img[alt='Microsoft'] { display:inline; }
    .logo-images img[alt='PHP'] { margin-right:40px; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/microsoft.png" alt="Microsoft">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Visão Geral
Este tutorial fornece instruções com um guia passo a passo de como configurar um servidor web (IIS) e PHP no Windows e algumas opções alternativas. Instalar o PHP no Windows é relativamente rápido e simples por que a Microsoft fornece instaladores fáceis de utilizar.

<div class="quick-tip">
    <h3>Dica Rápida</h3>
    <p>Se você precisa somente do PHP para desenvolvimento local você pode pular o processo de instalação do IIS e ir direto para <a href="#install_php">instalando o  PHP</a> ou ver links adicionais nesta seção da página.</p>
</div>

### Web Platform Installer
O Web Platform Installer (WebPI) da Microsoft pode ser utilizado para instalar múltiplas versões do PHP tanto em desktops (Windows 10 etc) para desenvolvimento quanto em servidores (Windows Servers 2016 etc) para produção.
* https://www.microsoft.com/web/downloads/platform.aspx

### Ambientes de Desenvolvimento Alternativos para PHP em Windows
Este tutorial mostra como utilizar um programa suportado pela Microsoft para instalar o  PHP, contudo, muitas opções existem para desenvolvimento local. Aqui estão algumas:
* https://www.apachefriends.org/ - XAMPP Apache + MariaDB + PHP + Perl - Ambiente Popular de Desenvolvimento PHP, também funciona com macOS e Linux.
* https://windows.php.net/download - Download Direto do PHP, uma vez instalado você pode utilizar o próprio PHP como Servidor de Desenvolvimento

### Recursos de Instalação do PHP Adicionais
Há várias formas formas de instalar o PHP. Para descobrir veja os links adicionais ou busque online.
* https://www.php.net/manual/en/install.windows.php
* https://docs.microsoft.com/en-us/iis/application-frameworks/scenario-build-a-php-website-on-iis/configuring-step-1-install-iis-and-php
* https://www.microsoft.com/en-us/sql-server/developer-get-started/php/windows
* https://docs.microsoft.com/en-us/sql/connect/php/loading-the-php-sql-driver?view=sql-server-2017


---
## Conectar ao Windows Server
Se estiver instalando o PHP em um Servidor Windows você provavelmente utilizará Conexão de Área de Trabalho Remota (RDC) para conectar-se ao servidor.

&nbsp;

Abra o RDC procurando por “Remoto” ou "mstsc" no menu Iniciar; uma vez aberto você uma tela de login.

![Conexão de Área de Trabalho Remota (RDC)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/00_RDC.png)

&nbsp;

Especificando “.\” antes do nome de usuário utilizará a rede local do computador que você está conectando ao invés de seu domínio. Isto pode ou não ser necessário dependendo de onde ou como você está conectando.

![Login com o Conexão de Área de Trabalho Remota (RDC)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/01_RDC_Auth.png)

&nbsp;

Talvez seja exibido um alerta de certificado ao conectar. Isto é um alerta comum e é tipicamente seguro clicar em [Sim/Yes].

![Alerta Remote Desktop Connection (RDC)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/02_RDC_Warning.png)

---
## Instalar o IIS em um Desktop Windows

Se você estiver utilizando um Computador Destkop com Windows com Windows 10, você pode instalar o IIS à partir de [Programas e Recursos] ativando-o na lista como um Recurso do Windows. Utilizando o IIS para desenvolvimento PHP não é necessário apra desenvolvimento PHP por que o PHP possui um Servidor Web integrado.

![Instalar o PHP no Windows Desktop](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/03_Win7_Install.png)

---
## Instalar o IIS no Windows Server

Esta página mostra como instalar o IIS e o PHP em uma versão recente do Windows. Se você possuir uma versão muito antiga do Windows Server (exemplo Windows Server 2003 com IIS 6) você ainda pode instalar o PHP, contudo, você teria de procurar por outros links online por que os passos serão diferentes.

Primeiro abra [Gerenciador do Servidor] from the Start Menu.

![Ícone do Gerenciador do Servidor](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/04_Start_Menu_Server_Manager.png)

&nbsp;

Click [Adicionar funções e recursos]

![Gerenciador do Servidor do Windows Server - Adicionar Funções e Recursos](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/05_Add_Roles_And_Features.png)

&nbsp;

Você passará por um Assistente de Instalação. Clique no botão [Próximo >].

![Assitente de Instalação do Gerenciador do Servidor no Windows Server](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/06_Add_Roles_And_Features.png)

&nbsp;

Você pode deixar nas opções padrão até você chegar na seção [Funções do Servidor]. Então selecione [Servidor web (IIS)].

![Gerenciador do Servidor Windows Server - Selecione IIS](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/07_Selected_IIS.png)

&nbsp;

Para este tutorial nós estamos deixando as opções padrão, porém, talvez você queira modificá-las baseando-se em suas necessidades. Clique [Próximo >] e então finalize a configuração. Uma vez finalizado o IIS estará configurado em seu servidor.

![Gerenciador do Servidor Windows Server - Opções do IIS](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/08_IIS_Options.png)

---
## <a name="install_php">Instalar o Web Platform Installer e o PHP</a>

Baixe o Web Platform Installer da Microsoft. [https://www.microsoft.com/web/downloads/platform.aspx]

![Site da Microsoft para o Web Platform Installer](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/09_Web_Platform_Installer.png)

&nbsp;

Os Serviores Windows tipicamente bloqueiam a maioria dos sites e downloads por padrão, assim talvez você veja este alerta ao utilizar o IE. Para  typically block most sites and downloads by default so you may see this warning if using IE. Para contornar a questão, modifique as Configurações de Segurança do IE, baixe o Web Platform Installer à partir de outro navegador (uma versão portátil, por exemplo) se disponível, ou baixe em outro computador e copie o instalador via RDC.

![Alerta de Download do IE](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/10_Download_Warning.png)

&nbsp;

O Web Platform Installer é um Assistente de Instalação simples com uma tela.

![Assistente de Instalação Web Platform Installer](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/11_Install_Web_Platform_Installer.png)

&nbsp;

Uma vez instalado você o verá no Menu Iniciar.

![Ícone do Web Platform Installer](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/12_Start_Menu_Web_Platform_Installer.png)

&nbsp;

Busque por “php” ou por uma versão específica como “php 7.3”. O Web Platform Installer fornece várias versões diferentes do PHP e várias extensões.

![Web Platform Installer - PHP Search](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/13_Search_For_PHP.png)

&nbsp;

Neste exemplo nós vamos instalar o PHP 7.3.1 que é a última versão do PHP (no momento da criação deste tutorial), e nós vamos instalar os Drivers do SQL Server para PHP no IIS. Você notará que há uma opção para cada versão do PHP para instalar para o [IIS Express]. O IIS Express é utilizado para desenvolvimento local e não a versão copleta do IIS, então nós não o selecionamos aqui.

![Web Platform Installer - Instalar o PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/14_Install_PHP.png)

&nbsp;

![Web Platform Installer - Instalar o PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/15_Install_PHP.png)

&nbsp;

Dpendendo da velocidade de seu computador e internet, a instalação pode levar em torno de um minuto a alguns minutos.

![Web Platform Installer - Instalando o PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/16_PHP_Installing.png)

&nbsp;

Neste exemplo um erro ocorreu durante a instalação, contudo, foi para uma extensão não utilizada que não é necessária e a instalação principal do PHP funcionou.

![Web Platform Installer - PHP Installed](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/17_PHP_Installed.png)

---
## Crie e Visualize uma Página PHP

&nbsp;

A pasta web raiz padrão ao utilizar o IIS é [C:\inetpub\wwwroot]. Aqui, um arquivo [phpinfo.php] é adiciondor utilizando o Notepad. Este arquivo mostrará a versão do PHP, versão de configuração etc.

~~~
<?php
phpinfo();
~~~

![IIS Crie o arquivo phpinfo em wwwroot](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/18_wwwroot_Create_File.png)

&nbsp;

Visualizando a página à partir de localhost mostra que o PHP está instalado e funcionando corretamente.

![Visualizar o Arquivo phpinfo](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/19_View_phpinfo.png)

&nbsp;

O local de instalação pode variar em seu servidor, contudo, aqui está instalado em [C:\Program Files\PHP\v7.3]. Você pode ver que o Web Platform Installer define opções de configurações necessárias como fuzo horário. A pasta de extensão geralmente inclui várias extensões adicionais que não são habilitadas por padrão; se você precisar delas veja os arquivos relacionados para ter certeza que elas existem e então adicione-as ao bloco [ExtensionList] no arquivo [php.ini].

![Pasta de Configuração do PHP no Windows e o arquivo INI](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/20_PHP_Config.png)

---
## Configures o Site Inicial do FastSitePHP

Baixe o Site Inicial do FastSitePHP à partir de https://www.fastsitephp.com/downloads/starter-site ou diretaente do GitHub https://github.com/fastsitephp/starter-site/archive/master.zip

Qualquer um dos links resulta no download do arquivo `starter-site-master.zip`.

Descompacte o arquivo e copie as seguintes pastas:

~~~
Copie as pastas:
    starter-site-master\app
    starter-site-master\app_data
    starter-site-master\scripts

Copie para dentro de:
    C:\inetpub
~~~

Essas três pastas existirão fora da pasta web raiz pública do IIS `wwwroot`.

![Copie as Pastas do Site Inicial](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/21_Copy_Starter_Site_Folders.png)

&nbsp;

Rode o script de instalação, isso leva apenas alguns segundos e instala o framework FastSitePHP em `C:\inetpub\vendor`.

~~~
cd C:\inetpub\scripts
php install.php
~~~

![Script de Instalação do FastSitePHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/22_Run_Install.png)

&nbsp;

Copie arquivos e pastas públicas para pasta raiz da web.

~~~
Copiar de:
    starter-site-master\public\
        css
        img
        js
        index.php
        Web.config
        favicon.ico
        robots.txt

Copie para dentro de:
    C:\inetpub\wwwroot
~~~

![Copie Arquivos Públicos do Site Inicial](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/23_Copy_Starter_Site_Files.png)

&nbsp;

Visualize o site! Parabéns se você seguiu todos esses passos então você configurou um Windows Server com IIS para utilização em produção.

![Visualizar Site](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_windows/24_View_Starter_Site.png)
