sudo apt-get update
sudo apt-get install -y docker.io ruby python-pip python-dev build-essential
sudo curl -L https://github.com/docker/compose/releases/download/1.21.0/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

sudo usermod -aG www-data ubuntu
sudo usermod -aG docker   ubuntu

sudo pip install --upgrade pip
sudo pip install --upgrade virtualenv

sudo pip install awscli --upgrade

sudo ./scripts/addswap.sh
