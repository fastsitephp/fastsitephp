# Utilizando a Classe Db2Database com um Servidor IBM iSeries
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='IBM'] { height:70px; margin-top:40px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/IBM_logo.svg" alt="IBM">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Visão Geral
Esta página fornece uma breve visão de desenvolvimento com banco de dados utilizando o FastSitePHP com um Banco de Dados IBM em um Servidor IBM. O FastSitePHP fornece várias classes de banco de dados que reduzem a quantidade de código necessária para consultar bancos de dados utilizando PHP. Uma das classes a [[Data\Db2Database](../api/Data_Db2Database)] é especialmente para bancos e dados IBM DB2 e AS/400.

PHP é suportado em Servidores IBM e tipicamente a versão mais recente do PHP pode ser instalada em velhos Servidores AIX. Isto permite scripts modernos e desenvolvimento bem mais rápido ao utilizar Servidores IBM antigos. Este documento não cobre como instalar o PHP em um Serviro IBM iSeries e assume que você tem acesso a um Servidor IBM e que o PHP já está configura; isto seria tipicamente feito por um administrador do Servidor IBM e não um desenvolvedor.

### Links
* https://www.ibm.com/it-infrastructure/power/os/ibm-i
* https://www.ibm.com/it-infrastructure/power/os/aix
* http://www.zend.com/en/solutions/modernize-ibm-i
* http://files.zend.com/help/Zend-Server/content/i5_installation_guide.htm
* https://en.wikipedia.org/wiki/IBM_AIX

### API e Script de Teste
* [📄 Class [Data\Db2Database]](../api/Data_Db2Database)
* <a href="https://github.com/fastsitephp/fastsitephp/blob/master/scripts/ibm-db2-test.php">📜 Arquivod Script de Teste IBM [scripts/ibm-db2-test.php]</a>

---
## Copiando Arquivos
Um script que vem com o FastSitePHP [scripts\ibm-db2-test.php] fornece detalhes de como copiar arquivos. Muitos programas de FTP não funcionarão com Servidores IBM, porém, ao utilizar Windows, o FTP de Linha de Comando integrada Many funciona com Servidores IBM.

![Copiar Arquivos para o Servidor IBM](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/00_Upload_Using_FTP.png)

---
## Iniciando uma Sessão no Servidor IBM
Se você estiver em um Ambiente IBM então você pode If you are in an IBM Environment then you may this setup or a similar setup from the Windows Start Menu.

![Iniciar uma Sessão IBM](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/01_IBM_Start_Session.png)

&nbsp;

Logue com sua conta uma vez que você iniciar o programa.

![IBM iSeries Login](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/02_IBM_Login.png)

---
## Rodando um Terminal ou Programa de Linha de Comando de um IBM iSeries
O menu padrão será provavelmente personalizado por um distribuidor de software como um sistema de ERP, então o comando pode ser diferente em seu servidor. Neste exemplo o comand [AZ] é utilizado para trazer o [Menu Principal IBM i] padrão.

![Rodar o Comando de Menu no Servidor IBM](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/03_AZ_Command.png)

&nbsp;

À partir do Menu Principal digite [call qp2term] e pressione [enter] para chamar uma interface de linha de comando.

![IBM Call QP2TERM](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/04_QP2TERM.png)

&nbsp;

À partir da linha de comando você pode rodar o script enviado [ibm-db2-test.php] para verificar que a Classe do FastSitePHP [[Db2Database](../api/Data_Db2Database)] funcione em seu servidor. Uma vez que tiver com isso funcionando você pode utilizá-lo como um ponto inicial para scripts e apps personalizados.

![IBM Rodar Script PHP](https://dydn9njgevbmp.cloudfront.net/img/docs/as400/05_Running_Commands.png)
