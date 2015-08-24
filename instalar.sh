#!/bin/bash
# 18/08/2015 @autor Jurandir Dacol Junior <jurandir.dacol@catho.com>
type -P php &> /dev/null || (echo "php nao esta acessivel ao script"; exit 1;)
if [ -f "composer.phar" ]
then
        php composer.phar self-update
else
        type -P curl &> /dev/null || (echo "Curl nao acessivel ao script"; exit 1;)
        curl -sS https://getcomposer.org/installer | php || (echo "Erro ao baixar composer"; exit 1;)
fi
php composer.phar install || (echo "Erro ao baixar dependencias via composer";)
echo "Dependencias atualizadas com sucesso"
