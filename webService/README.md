- Liberar Apache
<hr>
<h1>Servico rodando na porta 1000</h1>
<h2>Altere arquivo /etc/apache2/port.conf</h2>
<h2>Copiando configurações</h2>
<p>Copie o arquivo cyracris-default.conf para /etc/apache2/sites-avaliable/.</p>
<h2>Habilitando o apache</h2>
a2ensite cyracris.default.conf

<h2>Libere acesso ao user www-data
sydo chown www-data /data/CyraCris/webservice -R<br>
sudo chmod -R o+rx /data/CyraCris/webservice<br>
sudo chmod -R o+r /data/CyraCris/webservice<br>

<h2>Reinicie o apache</h2>
service apache2 restart
