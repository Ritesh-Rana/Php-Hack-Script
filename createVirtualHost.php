<?php
/**
 * Script to interactively create a virtual host in Apache on Ubuntu.
 * Make sure to run this script with sudo or as a user with sudo privileges.
 */

// Function to create a virtual host
function createVirtualHost($domain, $documentRoot)
{
    // Define error and access log filenames
    $domainErrorLog = "$domain.error.log";
    $domainAccessLog = "$domain.access.log";

    // Apache virtual host configuration template
    $vhostConfig = "
        <VirtualHost *:80>
            ServerAdmin webmaster@localhost
            DocumentRoot $documentRoot
            ServerName $domain
            <Directory $documentRoot>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
            </Directory>
            ErrorLog \${APACHE_LOG_DIR}/$domainErrorLog
            CustomLog \${APACHE_LOG_DIR}/$domainAccessLog combined
        </VirtualHost>
    ";

    // Save the virtual host configuration to a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'vhost');
    file_put_contents($tempFile, $vhostConfig);

    // Move the temporary file to Apache's sites-available directory
    $sitesAvailableDir = '/etc/apache2/sites-available/';
    $vhostFileName = "$domain.conf";
    $vhostFile = "$sitesAvailableDir$vhostFileName";
    rename($tempFile, $vhostFile);

    // Enable the virtual host
    shell_exec("sudo a2ensite $vhostFileName");

    // Reload Apache to apply the changes
    shell_exec("sudo systemctl reload apache2");

    // Add entry to /etc/hosts file
    $hostsFile = '/etc/hosts';
    $hostEntry = "127.0.0.1\t$domain.com";
    file_put_contents($hostsFile, PHP_EOL . $hostEntry, FILE_APPEND | LOCK_EX);

    echo "Virtual host created successfully: http://$domain.com\n";
}

// Get user input for the domain and document root
echo "Enter the domain (e.g., example): ";
$domain = trim(fgets(STDIN));

echo "Enter the document root (e.g., /var/www/example): ";
$documentRoot = trim(fgets(STDIN));

// Validate user input (you can add more validation if needed)

// Create the virtual host
createVirtualHost($domain, $documentRoot);
?>
