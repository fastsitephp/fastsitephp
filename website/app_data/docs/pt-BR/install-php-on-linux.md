# Instalar o Apache ou o nginx e o PHP em um Servidor Linux ou Unix
<style>
    .logo- ficar preso simplesmente busque na internet por recursos adicionais; margin-right:20px;}
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/linux.svg" alt="Linux">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Visão Geral
Este tutorial provê instruções incluindo comandos de shell, um guia passo a passo e recursos adicionais no rodapé desta página. Existem várias versões diferentes de Linux (e Unix) e instalações PHP variarão de SO para SO. Se você nunca trabalhou com programas Linux ou linha de comando antes isso pode parece desafiador de início, contudo, existem muitos recursos bons e tutoriais online, assim se você ficar preso simplesmente busque na internet por recursos adicionais.

---
## Conectando ao Linux
Se você estiver conectando a um Servidor Linux em Nuvem à partir do Windows ou macOS, exitem vários programas que você pode utilizar. Você pode querer começar com guias de seu provedor de hospedagem sobre como conectar-se. Se possui um Mac, [ssh] é integrado ao terminal assim você pode conectar-se sem ter de instalar nada. Aqui estão alguns recursos para conectar ao Linux.

### Conectando à partir do Windows
* https://docs.microsoft.com/en-us/azure/virtual-machines/linux/ssh-from-windows
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html
* https://winscp.net/eng/index.php *File Transfer*
* https://www.putty.org/
* https://docs.microsoft.com/en-us/windows/wsl/install-win10

### Conectanto à partir do macOS
* http://osxdaily.com/2017/04/28/howto-ssh-client-mac/
* https://panic.com/transmit/ *File Transfer*
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/AccessingInstancesLinux.html


---
## Código de Referência Rápida
Esta seção mostra várias opções de instalação diferentes para Linux utilizando o shell (terminal / command-prompt). Se você estiver familiarizado com o uso do shell e está instalando em um dos SOs listados, você pode utilizar esta referência rápida. Estas instruções atualmente fornecem um visão geral da instalação inicial. Dependendo de qual é o seu SO você pode querer instalar extensões adicionais do Apache para atualizar diversos arquivos de configuração.

### Configuração Rápida

~~~
# Um script bash script está disponível para uma configuração rápida do Apache,
# nginx, PHP e o FastSitePHP com um Site Inicial. Este script funciona para uma
# configuração completa em um SO padrão quando nada estiver instalado.

# Este script  e seguro para rodar várias vezes por que ele verifica se os
# programas, como o php, já estão instalados e pergunta a você antes de
# sobrescrever um site existente.

# Sistemas Operacionais Suportados (mais serão adicionados no futuro):
#   Ubuntu 18.04 LTS

wget https://www.fastsitephp.com/downloads/create-fast-site.sh
sudo bash create-fast-site.sh

# Quando você roda [create-fast-site.sh] você será requisitado a selecionar o
# Servidor Web (Apache ou Nginx) ou você pode rodar com as seguintes opções:

# Para Apache
sudo bash create-fast-site.sh -a

# Para Nginx
sudo bash create-fast-site.sh -n
~~~

### Instalação de Apache e PHP Installation no Ubuntu (Detalhada)
~~~
# Atualize a lista do Gerenciador de Pacotes [apt] com [update]
sudo apt update
# O [upgrade] não é obrigatório mas é recomendado (porém, isso pode levar muitos
# minutos)
sudo apt upgrade

# Instale o Apache e o PHP
sudo apt install apache2 php

# Habilitar o PHP no Apache
sudo apt install libapache2-mod-php

# Uma versão do comando alternativo existe para instalar o Apache, MySQL e PHP.
# Dependendo da versão do comando você será acionado para fornecer uma senha
# ou definir uma mais tarde para MySQL.
#
# sudo apt install lamp-server^

# Adicione Extensões PHP. Existe um grande número de extensões e o número da
# versão do PHP instalado precisa ser incluído. As extensões abaixo são
# necessárias para todas as funcionalidades do FastSitePHP funcionarem e para
# todos os Testes de Unidade serem bem sucedidos, contudo, eles não são
# requisitos para utilizar o FastSitePHP.
sudo apt install php7.2-sqlite php7.2-gd php7.2-bc php7.2-simplexml

# A extensão zip é requisitada para que o script de instalação do FastSitePHP rode.
sudo apt install php7.2-zip

# Opcional - Ative página Fallback para que o [index.php] não seja mostrado na
# URL.
sudo nano /etc/apache2/apache2.conf
# Role pelo arquivo e procure pela linha:
#    <Directory /var/www/>
# Abaixo disso adicione a linha:
#    FallbackResource /index.php
# Salve utilizando:
#    {control+s} -> {control+x}
#    ou {control+x} -> {y} -> {enter}

# Opcional - Ative a Compressão Gzip para Respostas JSON
#   (Isto não é ativado por padrão no Apache)
sudo nano /etc/apache2/mods-available/deflate.conf
# Adicione o seguinte sob comandos similares:
#       AddOutputFilterByType DEFLATE application/json

# Reinicie o Apache
sudo service apache2 restart

# Defina Permissões
# Isto assume que o usuário [ubuntu] existe e
# é utilizado para criar e atualizar arquivos no site.
sudo adduser ubuntu www-data
sudo chown ubuntu:www-data -R /var/www
sudo chmod 0775 -R /var/www

# Criar e visualizar um arquivo teste PHP
cd /var/www/html
echo "<?php phpinfo(); ?>" | sudo tee phpinfo.php
# http://your-server.example.com/phpinfo.php

# Depois que você vir o link do arquivo [phpinfo.php], é uma boa ideia deletá-lo:
sudo rm phpinfo.php

# Bonus! - Instale o Site Inicial do FastSitePHP

# Navegue para seu diretório home e baixe o Site Inicial
# Isso é um pequeno download (~32 kb)
cd ~
wget https://github.com/fastsitephp/starter-site/archive/master.zip
sudo apt install unzip
unzip master.zip

# Copiar Arquivos
cp -r ~/starter-site-master/app /var/www/app
cp -r ~/starter-site-master/app_data /var/www/app_data
cp -r ~/starter-site-master/scripts /var/www/scripts
cp -r ~/starter-site-master/public/. /var/www/html
ls /var/www
ls -la /var/www/html

# Instalar o FastSitePHP (~470 kb) e suas Dependências (~20 - 40 kb)
php /var/www/scripts/install.php

# Apague os arquivos que não sejam necessários incluindo a página padrão do Apache
# O arquivo [.htaccess]  sendo apagado é uma versão local para desenvolvimento
# que é copiada para o site inicial (Não é necessário para produção).
sudo rm /var/www/html/.htaccess
sudo rm /var/www/html/Web.config
sudo rm /var/www/html/index.html

# Remova os arquivos baixados
rm -r ~/starter-site-master
rm master.zip
ls ~

# Visualize o seu site
# http://your-server.example.com/
~~~

### Instalação do Apache e PHP no Red Hat, CentoOS e Fedora
~~~
# Atualize a lista do Gerenciador de Pacote
sudo yum update –y

# Instalação opcional do [vim-common] para suporte ao comando hex [xxd].
# Isto é necessário se utilizar o FastSitePHP ou o script de shell
# incluído para criptografia de arquivo.
sudo yum install vim-common

# Instalar Apache e PHP
sudo su
yum install httpd
yum install php
apachectl start

# Vários SOs Linux incluindo Red Hat Enterprise Edition and CoreOS utilizam
# Security-Enhanced Linux (SELinux) por padrão. Se utilizá-los o Apache e
# o PHP serão impedidos de escrever aquivos. Para garantir acesso de escrita
# de arquivo em uma pasta ao Apache, rode o seguinte (modificando o caminho
# do diretório) '/var/www/app_data' como for necessário para seu ambiente.
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/app_data(/.*)?"
sudo restorecon -Rv /var/www/app_data
sudo chown apache:apache -R /var/www/app_data/*
~~~

### Instalação do Apache e PHP no FreeBSD
~~~
# [sudo] nem sempre está disponível, então você pode utilizar [su -]
su -

# Instalar e iniciar o Apache
pkg install apache24
sysrc apache24_enable=yes
service apache24 start

# Visualizar o Site
# http://your-server.example.com/

# Instalar o PHP (utilize o número de versão PHP e instale pacotes opcionais)
pkg install mod_php73
pkg install php73-json php73-filter php73-hash php73-ctype
pkg install php73-openssl php73-mbstring

# Crie um novo arquivo:
vi /usr/local/etc/apache24/Includes/php.conf
# entre [i] para Inserir, então, copie/cole ou digite o seguinte:
~~~
~~~
<IfModule dir_module>
    DirectoryIndex index.php index.html
    <FilesMatch "\.php$">
        SetHandler application/x-httpd-php
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
    </FilesMatch>
</IfModule>
~~~
~~~
# Salve utilizando:
# {esc} :wq

# Tenha certeza que o arquivo está correto
cat /usr/local/etc/apache24/Includes/php.conf

# Copie o [php.ini] e crie um arquivo de teste [phpinfo.php]
cp /usr/local/etc/php.ini-production /usr/local/etc/php.ini
cd /usr/local/www/apache24/data
echo "<?php echo phpinfo(); ?>" | tee phpinfo.php
service apache24 restart
# http://your-server.example.com/phpinfo.php

# Opcional: Define permissões de arquivo para que possa copiar arquivos
chown ec2-user /usr/local/www/apache24/*
chown ec2-user /usr/local/www/*
~~~

---
## Exemplo Passo a Passo
Este exemplos foi realizado no Ubuntu utilizando um Servidor Lightsail da Amazon AWS e, comandos digitados no terminal baseado em navegador web fornecido.

&nbsp;

Atualizar a Lista da Ferramenta de Pacote Avançada (APT)
* `sudo apt update`

![Update Advanced Package Tool (APT)](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/00_apt_get_update.png)

&nbsp;

O processo de atualização exibe o log de ações enquanto roda e deve completar rapidamente. Assim que atualizar você será capaz de digitar no terminal novamente.

![APT Atualizado](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/01_apt_get_update_complete.png)

&nbsp;

Install Apache, PHP, and then enable PHP for Apache and PHP. Detailed log info will be displayed when each command runs.
* `sudo apt install apache2`
* `sudo apt install php`
* `sudo apt install libapache2-mod-php`

![Instalar o Apache2](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/02_install_apache2.png)

&nbsp;

Determinar a versão PHP instalada; estará claramente disponível no log do instalador. Isto pode ser utilizado para opcionalmente instalar diversas extensões. Exemplo: `sudo apt install php7.2-sqlite php7.2-gd php7.2-bc php7.2-simplexml`

![Obter Versão do PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/03_install_php.png)

&nbsp;

Opcional - Habilite uma página Fallback para que o [index.php] não seja mostrado na URL. Edite o arquivo de configuração do Apache utilizando: `sudo nano /etc/apache2/apache2.conf`. Assim o arquivo será mostrado no editor editor.

![Comando do Editor Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/04_nano_edit_config.png)

![Veja a Configuração do Apache com o Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/05_apache_config_in_nano.png)

&nbsp;

Role pelo arquivo e procure pela linha: `<Directory /var/www/>`. Adicione a linha `FallbackResource /index.php` abaixo dela. Neste exemplo `CGIPassAuth On` é também adicionada para que o Cabeçalho de Requisição HTTP [Authorization] seja disponibilizado para o PHP ao utilizar `$_SERVER['HTTP_AUTHORIZATION']`; contudo, isto não é necessário ao utilizar o Objeto Request do FastSitePHP. Os menus exit/save/etc do nano serão mostrados no rodapé da tela.

![Edite a Configuração do Apache com o Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/06_edit_apache_config.png)

&nbsp;

Salve o arquivo utilizando `{control}+x` -> `y` -> `{enter}`

![Prompt de Salvamento do Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/07_nano_save.png)

![Prompt de Confirmação do Nano](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/08_nano_confirm.png)

&nbsp;

Reinicie o Apache utilizando `sudo service apache2 restart`

![Reinicie o Apache](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/09_apache_restart.png)

&nbsp;

Defina as Permissões de Usuário para que arquivos web possam ser modificados pelo usuário que você utiliza.
* `sudo adduser ubuntu www-data`
* `sudo chown ubuntu:www-data -R /var/www`
* `sudo chmod 0775 -R /var/www`

&nbsp;

![Definir Permissões de Usuário](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/10_user_permissions.png)

&nbsp;

Crie um arquivo [phpinfo.php] para confirmar que o PHP funciona.
* `cd /var/www/html`
* `echo "<?php phpinfo(); ?>" | sudo tee phpinfo.php`

&nbsp;

![Criar o Arquivo de Informações do PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/11_create_phpinfo_file.png)

&nbsp;

Utilize o IP Público do Servidor para visualizar a página padrão do Apache em um navegador.

![Visualizar a Página Padrão do Apache](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/12_view_apache_default_page.png)

&nbsp;

Confirme que o PHP funciona e veja informações detalhadas de instalação e configuração utilizando a página [phpinfo.php].

![Veja a Página de Informações do PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/install_php_linux/v1/13_view_phpinfo_page.png)

---
## Recursos Adicionais

* https://www.linode.com/docs/web-servers/lamp/
* https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-ubuntu-18-04
* https://www.digitalocean.com/community/tutorials?q=php
* https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ec2-lamp-amazon-linux-2.html
* https://www.vultr.com/docs/how-to-install-apache-mysql-and-php-on-ubuntu-16-04
* https://linuxize.com/post/how-to-install-php-on-ubuntu-18-04/
* https://www.tecmint.com/install-apache-mariadb-and-php-famp-stack-on-freebsd/
* https://www.cyberciti.biz/faq/how-to-install-apache-mysql-php-stack-on-freebsd-unix-server/
* http://wiki.hawkguide.com/wiki/AWS_Redhat_RHEL_Install_LAMP
