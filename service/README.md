

/opt/cyracris/
├─ app.py              # seu arquivo Flask (app:app)
├─ venv/               # virtualenv
├─ requirements.txt
├─ .env                # variáveis da aplicação (opcional)

echo "Install"
echo "Cria diretório"
mkdir /opt/cyracris

echo "Copia Arquivos"
cp *.py /opt/cyracris/.
cp requirements.txt /opt/cyracris/.

echo "Usuário"
sudo mkdir -p /opt/cyracris && sudo chown $USER:$USER /opt/cyracris

echo "NEVE"
cd /opt/cyracris
python3 -m venv venv

source venv/bin/activate
#.\venv\Scripts\activate
pip install --upgrade pip
pip install -r requirements.txt
pip install Flask gunicorn flask-cors
#pip freeze > requirements.txt


cp cyracris.env /etc/cyracris.env


#sudo systemctl daemon-reload
#sudo systemctl enable --now myapi.service
#sudo systemctl status myapi.service

#flask --app app run --debug