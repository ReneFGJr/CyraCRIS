echo "Install"
echo "Cria diretório"
mkdir /opt/cyracris

echo "Copia Arquivos"
cp *.py /opt/cyracris/.
cp requirements.txt /opt/cyracris/.

echo "Usuário"
sudo mkdir -p /opt/myapi && sudo chown $USER:$USER /opt/myapi

echo "NEVE"
cd /opt/cyracris
python3 -m venv venv

source venv/bin/activate
pip install --upgrade pip
pip install Flask gunicorn flask-cors
pip freeze > requirements.txt


cp cyracris.env /etc/cyracris.env


#sudo systemctl daemon-reload
#sudo systemctl enable --now myapi.service
#sudo systemctl status myapi.service
