# Criptografia de Arquivo utilizando o Script Bash compatível [encrypt.sh]
<style>
    img.header-image { margin-top:20px; height:150px; }
</style>
<img src="../../img/icons/Security-Lock.svg" alt="Encryption" class="header-image">

---
## Visão Geral
O FastSitePHP inclui a habilidade de criptografar arquivos utilizando ambos uma Classe PHP e um Script Bash compatível. Bash é o shell padrão em várias distribuições Linux e no macOS; e está também amplamente disponível em outros SOs baseados em Unix como os BSDs (FreeBSD, NetBSD, OpenBSD, DragonFlyBSD etc). Bash pode também [rodar no Windows utilizando o Windows Subsystem for Linux](file-encryption-windows) ou com ferramentas de terceiros.

Esta página mostra como utilizar o script bash e provê detalhes de como isto funciona.

Capturas de tela nesta página foram criadas em um macOS utilizando SSH e um Terminal para conectar a um Serviço Web da Amazon (AWS) em Servidores Lightsail. O endereços de IP e quaisquer informações de servidor mostradas nas telas capturadas, são de servidores temporários que não existem mais.

### API de Criptografia de Arquivo e Script Bash
* [📄 Classe [Security\Crypto\FileEncryption]](../api/Security_Crypto_FileEncryption)
* <a href="https://github.com/fastsitephp/fastsitephp/blob/master/scripts/shell/bash/encrypt.sh">📜 Visualizar o Código Fonte de [encrypt.sh]</a>
* [📥 Baixar o script Bash [encrypt.sh]](../../downloads/encrypt-bash)
* [📑 Criptografia utilizando o script Bash [encrypt.sh] no Windows](file-encryption-windows)

---
## Rodando o Script

Para rodar utilizando o Bash execute o comando `bash encrypt.sh` do seu shell ou terminal ou para rodar diretamente execute o comando `./encrypt.sh`, mas primeiro é necessário torná-lo executável definindo as permissões rodando `chmod +x encrypt.sh` entretanto, tipicamente, isso não será necessário.

Quando você roda o script sem quaisquer opções ou utilizando a Opção de Ajuda `./encrypt.sh -h`, você verá informações para o comando, utilização, opções e exemplos.

![Tela de Ajuda no Bash [encrypt.sh]](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/00_Encrypt_Help.png)

&nbsp;

Este script não tem outras dependências além dos comandos que estão geralmente instalados na maioria das SOs Linux. Os verdadeiros comandos para criptografar e descriptografar funcionam no FreeBSD, porém, o FreeBSD não inclui o Bash por padrão. Red Hat, CentoOS, Fedora e algumas instalações Linux não terão o comando requerido  [xxd] instalado por padrão, então este script provê um alerta e informações de como instalá-lo se necessário.

![Linux sem o comando xxd](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/11_Install_vim_common.png)

![Linux Instalar vim-common](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/12_Install_vim_common.png)

![Linux vim-common Instalado](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/13_vim_common_Installed.png)

---
## Criptografando e Descriptografando Arquivos

A opção Generate Key `./encrypt.sh -g` gerará uma chave hexadecimal segura e única para criptografar e descriptografar. A chave pode ser utilizado com este Script Bash e também com as classes de criptografia do FastSitePHP's [[Security\Crypto\Encryption]](../api/Security_Crypto_Encryption) e [[Security\Crypto\FileEncryption]](../api/Security_Crypto_FileEncryption). Você pode salvar uma chave em um arquivo rodando `./encrypt.sh -g > encryption.key`.

![Gere uma Chave utilizando [encrypt.sh]](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/02_Generate_Key.png)

&nbsp;

Para criptografar um arquivo utilize a opção `-e` e especifique {arquivo-de-entrada} *(arquivo a ser criptografado)* e {arquivo-de-saída} *(arquivo ao ser criptografado)*. Você pode utilizar uma Chave `-k`, `-p` Senha ou deixar o parâmetro em branco para que seja solicitada uma senha.

* `./encrypt.sh -e -i <arquivo-de-entrada> -o <arquivo-de-saída> -k <key>`
* `./encrypt.sh -e -i <arquivo-de-entrada> -o <arquivo-de-saída> -p <senha>`
* `./encrypt.sh -e -i <arquivo-de-entrada> -o <arquivo-de-saída>`

Para criptografar um arquivo utilize a opção `-d` e especifique o {arquivo-de-entrada} *(arquivo ao ser criptografado)* e {arquivo-de-saída} *(arquivo descriptografado)*.

* `./encrypt.sh -d -i <arquivo-de-entrada> -o <arquivo-de-saída> -k <key>`
* `./encrypt.sh -d -i <arquivo-de-entrada> -o <arquivo-de-saída> -p <senha>`
* `./encrypt.sh -d -i <arquivo-de-entrada> -o <arquivo-de-saída>`

Arquivos criptografados são ilegíveis, então o comando `head -c 256 test.enc | hexdump -C -v` é utilizado abaixo para mostrar os bytes do arquivo utilizando um visualizador hexadecimal. Também, na captura de tela, a chave é lida de um arquivo e passada para o comando utilizando `"$(cat encryption.key)"`.

No exemplo abaixo o arquivo [test.txt] é criptografado [test.enc], e então [test.enc] é descriptografado para [test.dec].

![Criptografe e Descriptografe um Arquivo](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/03_Encrypt_and_Decrypt_File.png)

&nbsp;

Esta captura de tela mostra um exemplo de descriptografia de arquivo utilizando uma senha que é inserida de forma oculta via terminal. A opção `-p` está disponível para utilizar senhas, entretanto, isso ela pode ser salva no histórico do shell, então se você está utilizando este script com uma senha, deixar a opção em branco é recomendado. Se utilizar uma Senha ao invés de uma Chave, espere 1 a 3 segundos extras de tempo de processamento por arquivo.

![Criptografe utilizando uma Senha](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/04_Encrypt_with_Password.png)

&nbsp;

O script é seguro para rodar e pergunta a você antes de sobrescrever quaisquer arquivos. Além disso se ocorrer um erro ou o arquivo não puder ser descriptografado uma mensagem de erro clara.

![Erro ao Descriptografar](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/05_Decryption_Error.png)

&nbsp;

Na maioria dos sistema você pode instalar o comando para acesso global utilizando `sudo mv encrypt.sh /usr/local/bin/encrypt`. Você pode então simplesmente utilizar o comando `encrypt` de qualquer luar no shell/terminal.

![Instalar o [encrypt.sh] Globalmente](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/06_Install_Global_Command.png)

---
## Testes de Unidade

Este script tem a habilidade de rodar testes de unidade utilizando  a opção `./encrypt.sh -t`. Teste de Unidade ajuda a verificar se seu sistema funciona apropriadamente.

![Teste de Unidade em Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/07_Encrypt_Unit_Test.png)

&nbsp;

Testes de Unidade normalmente rodam durante 3 a 20 segundos dependendo da velocidade de seu computador. Uma vez completo você verá o resultado.

![Resultado do Teste de Unidade em Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/08_Encrypt_Unit_Test_Result.png)

&nbsp;

A opção `./encrypt.sh -l` pode ser utilizada para verificar se a criptografia de arquivos grandes é suportada por seu sistema. Esta opção criará arquivos com tamanhos de 1GB e 3GB e requer pelo menos 9GB de espaço em disco. A opção `-l` pode levar qualquer coisa entre alguns minutos a acima de 30 minutos dependendo da velocidade de disco de seu sistema. Se os Testes de Unidade principais `-t` funcionarem em seu sistema, então é esperado que o teste de arquivos grandes também funcionem em quase todos os outros sistemas. Isto falharia se seu servidor não permitir que arquivos maiores que 2GB sejam criados (tamanho máximo de 32-bit); muitas instâncias 32-bit do Linux, permitirão arquivos maiores que 2GB.

![Teste de Unidade de Grandes Arquivos em Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/09_Encrypt_Large_File_Test.png)

&nbsp;

Ao utilizar Senhas, Chaves de Criptografia são geradas utilizando PBKDF2 (Função de Derivação de Chave Baseada em Senha 2). Bash/Shell não fornece suporte integrado para PBKDF2, então uma das seguintes linguagens é utilizada para derivar a senha [node, python3, php, python, ruby]. A opção `./encrypt.sh -b` pode ser utilizada para ver quais linguagens são utilizadas e funcionam para PBKDF2; é necessário que somente 1 linguagem esteja instalada para que possa utilizar senhas e a maioria dos sistema terão pelo menos uma dessas linguagens.

![Teste de PBKDF2 em Bash](https://dydn9njgevbmp.cloudfront.net/img/docs/encrypt_sh_bash/10_PBKDF2_Testing.png)

---
## Detalhes de Criptografia e Descriptografia

Internamente o script utiliza [openssl] para realizar ambos a criptografia e a autenticação HMAC. [openssl] está inclusa em todos os computadores Linux e Unix. O código abaixo mostra passo a passo os comandos necessários para realizar criptografia e descriptografia. O próprio script bash é bem grande (~1,500 lines of code) por que contém muitas verificações de segurança, incluindo ajuda, validação, testes de unidade e suportar senhas.

Entendimento completo desses comandos requer bom conhecimento de termos de criptografia e como criptografia funciona, porém, este código inclui vários comentários e pode ser simplesmente copiado passo a passo para ver como funciona.

~~~
# -----------------------------------
# Crie um arquivos de Teste
# -----------------------------------

# Primeiro crie um arquivo vazio de 10MB como nome "crypto_test_10mb" para teste.
# macOS utiliza [mkfile] enquanto Linux ou Unix usará um dos outros comandos.
# [dd] está incluso para o propósito de documentação, porém, ele é muito lento para isso e
# geralmente o [dd], comumente chamado de "destruidor de disco", deve ser usado com cautela.
mkfile -n 10m crypto_test_10mb
xfs_mkfile 10m crypto_test_10mb
fallocate -l 10m crypto_test_10mb
truncate -s 10m crypto_test_10mb
dd if=/dev/zero of=crypto_test_10mb bs=10m count=1

# Calcule uma Hash MD5. macOS e FreeBSD utilizam [md5] enquanto Linux utilizará [md5sum].
# Hash: f1c9645dbc14efddc7d8a322685f26eb
md5 crypto_test_10mb
md5sum crypto_test_10mb

# Visualizar o início do arquivo
head -c 256 crypto_test_10mb | hexdump -C -v

# ---------------------------------------------------
# Gere uma Chave para Criptografia e Descriptografia
# ---------------------------------------------------

# Utilize a CSPRNG (Gerador de Números Pseudoaleatórios Criptograficamente Seguro) do sistema
# para gerar a chave que tem 64-bytes de comprimento. Os primeiros 32-bytes (256-bits)
# serão utilizados para criptografar e os últimos 32-bytes serão para autenticação.
# Cada vez que esse comando rodar uma chave diferente será gerada.
#
# Para este exemplo nós utilizaremos a chave:
# b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab
#
# IMPORTANTE - Não copie e utilize a chave em suas aplicações,
# ao invés disso, gere uma nova chave sempre que você precisar de uma.
xxd -l 64 -c 64 -p /dev/urandom

# Se o comando acima não funcionar primeiro rode
# um dos seguintes comandos e tente novamente:
#
# Linux (Red Hat, CentoOS, Fedora etc)
sudo yum install vim-common
# FreeBSD
su -
pkg install vim-console

# Uma chave pode ser atribuída a uma variável [key] e
# então dividia nas duas chaves necessárias. Exemplo:
key=$(xxd -l 64 -c 64 -p /dev/urandom)
enc_key=${key:0:64}
hmac_key=${key:64}
#
# Ou para o FreeBSD, se a sintaxe acima não for suportada
set key = `xxd -l 64 -c 64 -p /dev/urandom`
set enc_key = `echo $key | cut -c1-64`
set hmac_key = `echo $key | cut -c65-128`

# Resultado Exemplo:
# enc_key  = b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f
# hmac_key = 6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab
echo $enc_key
echo $hmac_key

# -----------------------------------
# Criptografe
# -----------------------------------

# Gere o Vetor de Inicialização (IV).
# O IV é de 16 bytes aleatórios seguros que é o tamanho do IV para 'aes-256-cbc'.
# O valor muda toda vez que a função é chamada e quando utilizada corretamente
# como é feito com [encrypt.sh]; isto resulta no texto criptografado ser
# diferente cada vez que os dados são criptografados mesmo que a mesma chave
# seja utilizada.
xxd -l 16 -p /dev/urandom
# Valor para esta Demonstração: 0ee221ef9e00dfa69efb3b1112bfbb2f

# Criptografe (cria um novo arquivo "crypto_test_10mb.enc")
# O algorítmo 'aes-256-cbc' é utilizado, alguns sistema também suportam o
# algorítmo seguro 'aes-256-ctr', contudo, isto não funcionará em todos os
# sistemas, que é porque [encrypt.sh] suporta somente 'aes-256-cbc'.
openssl enc -aes-256-cbc \
    -in crypto_test_10mb \
    -out crypto_test_10mb.enc \
    -iv 0ee221ef9e00dfa69efb3b1112bfbb2f \
    -K b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f

# Hash: afac5edb3cda97a31f4a67bc3c34bf13
md5 crypto_test_10mb.enc
md5sum crypto_test_10mb.enc

# Visualizar o final do arquivo
tail -c 32 crypto_test_10mb.enc | hexdump -C -v

# Anexar IV ao final do arquivo
# Tipicamente em aplicações e sites seguros o IV é salvo com os dados
# criptografados enquanto somente a chave necessita ser secreta.
echo 0ee221ef9e00dfa69efb3b1112bfbb2f | xxd -r -p >> crypto_test_10mb.enc

# Agora visualize o final do arquivo depois de adicionar o IV
tail -c 32 crypto_test_10mb.enc | hexdump -C -v

# Hash: d257ac3640eb35d82591facd8c7ddb25
md5 crypto_test_10mb.enc
md5sum crypto_test_10mb.enc

# Primeiro veja o que seria o HMAC:
# Resultado: 2d525a248488d6551bbd87db93f5b8efd38e35848c517cf66c2aed7583ea8744
cat crypto_test_10mb.enc \
    | openssl dgst \
        -sha256 \
        -mac hmac \
        -macopt hexkey:6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab \
        -binary \
    | xxd -l 32 -c 32 -p

# Calcule e anexe HMAC ao final do arquivo.
# O HMAC é lido e utilizado durante a descriptografia para autenticar
# que o arquivo não foi adulterado.
cat crypto_test_10mb.enc \
    | openssl dgst \
        -sha256 \
        -mac hmac \
        -macopt hexkey:6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab \
        -binary \
    >> crypto_test_10mb.enc

# Hash: 371b4aad41c87bc27bb6cdd58c2c7c48
md5 crypto_test_10mb.enc
md5sum crypto_test_10mb.enc

# View the appended IV and HMAC
tail -c 64 crypto_test_10mb.enc | hexdump -C -v

# -----------------------------------
# Descriptografar
# -----------------------------------

# Copie o arquivo criptografado original, para que assim ele não seja modificado
cp crypto_test_10mb.enc crypto_test_10mb.enc.tmp

# Hash: 371b4aad41c87bc27bb6cdd58c2c7c48
md5 crypto_test_10mb.enc.tmp
md5sum crypto_test_10mb.enc.tmp

# Obtenha o HMAC do final do arquivo
# Result: 2d525a248488d6551bbd87db93f5b8efd38e35848c517cf66c2aed7583ea8744
tail -c 32 crypto_test_10mb.enc.tmp | xxd -l 32 -c 32 -p

# Para uma comparaçao posterior, nós também salvaremos em uma variável:
file_hmac=$(tail -c 32 crypto_test_10mb.enc.tmp | xxd -l 32 -c 32 -p)
echo $file_hmac
#
# FreeBSD
set file_hmac = `tail -c 32 crypto_test_10mb.enc.tmp | xxd -l 32 -c 32 -p`
echo $file_hmac

# Trunque o HMAC do final do arquivo
#
# Truncar bytes do final do arquivo acontece quase que instantaneamente com os
# comandos corretos enquanto remover bytes do início de um arquivo requereria
# que o arquivo inteiro fosse copiado, que é porque o IV e o HMAC são anexados
# ao final do arquivo ao invés do início do arquivo. Em Linux e na maioria dos
# computadores Unix o comando [truncate] existirá enquanto em macOS isso não
# existirá a não ser que seja manualmente instalado, então um script Ruby de uma
# linha é utilizado.
#
# O programa [stat] terá opções diferentes dependendo do SO.
# O "2>/dev/null ||" faz com que erros sejam ignorados e que outras opções rodem.
# Em bash "$(( expressão ))" é utilizado para cálculos matemáticos.
#
# Linux e algumas instalações FreeBSD:
truncate -s $(( $(stat -c%s crypto_test_10mb.enc.tmp 2>/dev/null \
    || stat -f%z crypto_test_10mb.enc.tmp) - 32 )) crypto_test_10mb.enc.tmp
#
# macOS:
ruby -e 'File.truncate("crypto_test_10mb.enc.tmp", File.size("crypto_test_10mb.enc.tmp")-32)'
#
# FreeBSD
set length = `stat -f%z crypto_test_10mb.enc.tmp`
set new_length = `expr $length - 32`
truncate -s $new_length crypto_test_10mb.enc.tmp

# Calcular e visualizar o arquivo HMAC depois de removê-lo
# Result: 2d525a248488d6551bbd87db93f5b8efd38e35848c517cf66c2aed7583ea8744
cat crypto_test_10mb.enc.tmp \
    | openssl dgst \
        -sha256 \
        -mac hmac \
        -macopt hexkey:6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab \
        -binary \
    | xxd -l 32 -c 32 -p

# Também salvê-o em uma variável para comparação
calc_hmac=$(cat crypto_test_10mb.enc.tmp \
    | openssl dgst \
        -sha256 \
        -mac hmac \
        -macopt hexkey:6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab \
        -binary \
    | xxd -l 32 -c 32 -p)
echo $calc_hmac
#
# FreeBSD
set calc_hmac = `cat crypto_test_10mb.enc.tmp \
    | openssl dgst \
        -sha256 \
        -mac hmac \
        -macopt hexkey:6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab \
        -binary \
    | xxd -l 32 -c 32 -p`
echo $calc_hmac

# Verificar se o HMAC Salvo e o Calculado são iguais.
# IMPORTANTE - ao comparar hashes em um app ou site, um método de comparação
# que economize tempo seria utilizado em um app ou site seguro. Por exemplo,
# utilizando [PHP:hash_equals()] ou [Python:hmac.compare_digest()]. Já que nós
# estamos manualmente digitando um simples teste 'se' é utilizado por que
# uma comparação com economia de tempo não é relevante.
[ $file_hmac = $calc_hmac ] && echo 'equal' || echo 'not equal'

# Obtenha o IV do final do arquivo
# Result: 0ee221ef9e00dfa69efb3b1112bfbb2f
tail -c 16 crypto_test_10mb.enc.tmp | xxd -l 16 -c 16 -p

# Truncar o IV do final do arquivo
#
# Linux:
truncate -s $(( $(stat -c%s crypto_test_10mb.enc.tmp 2>/dev/null \
    || stat -f%z crypto_test_10mb.enc.tmp) - 16 )) crypto_test_10mb.enc.tmp
#
# macOS:
ruby -e 'File.truncate("crypto_test_10mb.enc.tmp", File.size("crypto_test_10mb.enc.tmp")-16)'
#
# FreeBSD
set length = `stat -f%z crypto_test_10mb.enc.tmp`
set new_length = `expr $length - 16`
truncate -s $new_length crypto_test_10mb.enc.tmp

# Hash: afac5edb3cda97a31f4a67bc3c34bf13
md5 crypto_test_10mb.enc.tmp
md5sum crypto_test_10mb.enc.tmp

# Descriptografar o arquivo utilizando a mesma chave utilizada para criptografar
# e o IV que foi salvo no final do arquivo.
# Isto criará um novo arquivo "crypto_test_10mb.dec".
openssl enc -aes-256-cbc \
    -d \
    -in crypto_test_10mb.enc.tmp \
    -out crypto_test_10mb.dec \
    -iv 0ee221ef9e00dfa69efb3b1112bfbb2f \
    -K b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f

# Hash: f1c9645dbc14efddc7d8a322685f26eb
md5 crypto_test_10mb.dec
md5sum crypto_test_10mb.dec

# Visualize os últimos 256 bytes do arquivo utilizando um visualizador hex.
# Eles serão todos bytes núlos (ASCII 0 / Hex 00).
tail -c 256 crypto_test_10mb.dec | hexdump -C -v

# Se utilizar [encrypt.sh] o arquivo criptografado pode ser descriptografado
# utilizando os seguintes comandos:
key=b2e8ff4746c1006adafeb42235554363acf22391941b86b22a7b28c8a591ea4f6c3516271b9c008ab4279e5904995aa943117331e3e968560cedb5c7c17266ab
./encrypt.sh -d -i crypto_test_10mb.enc -o crypto_test_10mb.dec -k "$key"

# Visualize arquivos e então delete os arquivos "crypto*" criados
ls crypto*
rm crypto*
ls
~~~
