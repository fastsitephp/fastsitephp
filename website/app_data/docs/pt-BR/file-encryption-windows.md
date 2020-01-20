# Criptografia de Arquivo utilizando o Script Bash [encrypt.sh] no Windows
<style>
    img.header-image { margin-top:20px; height:150px; }
</style>
<img src="../../img/icons/Security-Lock.svg" alt="Encryption" class="header-image">

---
## Visão Geral
O FastSitePHP inclui a habilidade de criptografar arquivos utilizando ambos uma Classe PHP e um Script Bash compatível. Bash é o shell padrão em várias distribuições Linux e no macOS; e está também amplamente disponível em outros SOs baseados em Unix como os BSDs (FreeBSD, NetBSD, OpenBSD, DragonFlyBSD etc)

Windows 10, Windows Server 1709 e mais recentes, fornecem supore ao Subsistema Windows para Linux (WSL) que permite programas Linux, Scripts Bash e mais rodarem no Windows.

Este documento mostra a execução do script [encrypt.sh] no WSL e pode ser utilizado como uma referência geral para rodar Scripts Bash no Windows.

### API de Criptografia de Arquivo e o Script Bash
* [📄 Class [Security\Crypto\FileEncryption]](../api/Security_Crypto_FileEncryption)
* <a href="https://github.com/fastsitephp/fastsitephp/blob/master/scripts/shell/bash/encrypt.sh">📜 Veja o Código Font de [encrypt.sh]</a>
* [📥 Baixe o Arquivo de Script em Bash [encrypt.sh]](../../downloads/encrypt-bash)
* [📑 Mais informações sobre como utilizar o arquivo de script em bash [encrypt.sh]](file-encryption-bash)

### Links sobre o Subsistema Windows para Linux (WSL)
* [Documentação do Subsistema Windows para Linux](https://docs.microsoft.com/en-us/windows/wsl/about)
* [Guia de Instalação para Windows 10  do Subsistema Windows para Linux](https://docs.microsoft.com/en-us/windows/wsl/install-win10)
* [Guia de Instalação do Windows Server](https://docs.microsoft.com/en-us/windows/wsl/install-on-server)
* [Como copiar arquivos para e de um Nano Server utilizando PowerShell](https://msdn.microsoft.com/en-us/library/windows/desktop/mt708806(v=vs.85).aspx)

---
## Copiando Arquivos ou Enviando o Script
Estas capturas de tela foram criadas em uma Instância EC2 do Windows Server 1709 (Nano Server) em um Serviço Web da Amazon (AWS).

Para copiar arquivos para o Windows Nano Server você pode utilizar o Powershell, contudo, se o Powershell não for uma opção e você precisar copiar um arquivo texto, você poe abrir uma instância do Notepad digitando `notepad.exe` na linha de comando do Windows antes de logar no Linux.

Neste exemplo o arquivo [encrypt.sh] foi salvo na pasta [C:\Users\Administrator\Documents]; e no Linux você pode acessar a Unidade C (ou outras unidades) à partir de [/mnt/], being [/mnt/c] for the C: drive.

Para rodar um script bash navegue até o diretório do script e então execute `bash {script}` ou `./{script}`.

Você pode ver que neste exemplo houve um erro quando ao rodar pela primeira vez. Isto aconteceu por que o Windows Notepad salvou o Script Bash com Quebras de Linha Windows (CR/LF) ao invés de Quebras de Linha Unix (LF); para consertar isso rode o comando `sed -i 's/\r$//' encrypt.sh`. Assim, o script rodará como o esperado.

![Windows Nano Server - Abrir o Notepad e Criar o Script](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/01_Create_Encrypt_SH_File.png)

---
## Rodando o Script
Quando você roda o script sem quaisquer opções, utilizando uma das formas `bash encrypt.sh` ou `./encrypt.sh`, ou se você utiliza a opção `-h`, então você verá a tela de ajuda que fornece um visão geral do script, opçãoes de utilização e exemplos de como utilizá-lo.

![Ajuda para Criptografar Script Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/02_Encrypt_Shell_Help.png)

---
## Rodando Testes de Unidade
A opção `-t` rodará Testes de Unidade para confirmar que o script funciona em seu ambiente.

![Testes de Unidade de Criptografia de Script Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/03_Encrypt_Shell_Unit_Tests.png)

![Testes de Unidade de Criptografia de Script Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/04_Encrypt_Shell_Unit_Tests_Result.png)

---
## Criptografando Arquivos Grandes
Um teste com arquivo grande utilizando a opção `-l` criará, criptografará e descriptografará arquivos de 1Gb a 3GB e confirma que seu sistema pode rodar arquivos de qualquer tamanho. Isto é o ideal para criptografar backups por que você pode 'zippar' ou comprimir muitos arquivos para um grande arquivo compactado e então criptgrafa utilizando o script.

![Teste de Criptografia de Arquivos Grandes com Script Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_win/05_Encrypt_Shell_Large_File_Tests.png)
