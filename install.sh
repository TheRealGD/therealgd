sudo apt-get install -y docker ruby
sudo curl -L https://github.com/docker/compose/releases/download/1.21.0/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

sudo usermod -aG www-data ubuntu
sudo usermod -aG docker   ubuntu

sudo ./scripts/addswap.sh
