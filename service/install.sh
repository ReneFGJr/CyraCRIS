echo "Install"
cp cyracris.env /etc/cyracris.env


sudo systemctl daemon-reload
sudo systemctl enable --now myapi.service
sudo systemctl status myapi.service
