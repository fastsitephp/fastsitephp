# Utilize o Visual Studio Code para Desenvolvimento em PHP
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='Visual Studio Code'] { height:80px; width:80px; margin-top:30px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/vs-code.png" alt="Visual Studio Code">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Visão Geral
De acordo com uma [Pesquisa de 2019 do StackOverflow](https://insights.stackoverflow.com/survey/2019#development-environments-and-tools), Visual Studio Code da Microsoft é o mais popular Editor de Código para Desenvolvedores. Ele pode ser instalado gratuitamente em Windows, Mac e Linux e inclui suporte integrado para PHP com funcionalidades como destaque de sintaxe e IntelliSense (conclusão de código).

Vários plugins amplamente utilizados são recomendados aqui para desenvolvimento com PHP.

https://code.visualstudio.com/

![Visual Studio Code da Microsoft](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/0_VS_Code_Editor.png)

---
## Extensão PHP Server

Quando você instala o PHP em seu computador você pode então utilizar a extensão PHP Server com o VS Code para rodar um site. Isso funciona perfeitamente com o FastSitePHP, simplesmente clique como botão direito no arquivo  [index.php] e selecione [PHP Server: Serve Project] ou clique no ícone do PHP Server no canto superior direito da tela.

https://marketplace.visualstudio.com/items?itemName=brapifra.phpserver

![Extensão PHP Server para o VS Code](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/1_Run_PHP_Server.png)

&nbsp;

Você então verá o FastSitePHP ser iniciado (ou o site inicial) em seu navegador padrão.

![View Site](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/2_View_Site.png)

---
## Extensão Code Runner

https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner

Com a Code Runner você pode rodar arquivos PHP, JavaScript, Python ou scripts mais de 30 outras linguagens. Simplesmente clique como botão direito no arquivo e selecione [Run Code] ou clique no botão [Run] no canto superior direito da tela.

![Extensão Code Runner](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/3_Code_Runner.png)

&nbsp;

A saída do Console será mostrada no painel abaixo de seu código. É muito mais fácil copiar conteúdo daqui do que de um terminal ou prompt de comando e você não tem que alternar para uma ou de janela de terminal ao rodar scripts.

![Saída Code Runner Output](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/4_Code_Runner_Output.png)

---
## Extensões Adicionais

Encontre mais aqui:

https://code.visualstudio.com/docs/languages/php
