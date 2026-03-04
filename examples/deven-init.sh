# Example of Deven init script that is executed when 'deven init' is run.

# Exit on errors
set -e

# Install packages that your project requires
apt-get install -y apache2 libapache2-mod-php mariadb-server php

# ... other steps to set up your VM ...

echo "Init script executed successfully!"
