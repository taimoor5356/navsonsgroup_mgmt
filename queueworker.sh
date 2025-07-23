#!/bin/bash

# Load environment variables
source /home/bitnami/.bashrc

# Navigate to the Laravel project directory
cd /opt/bitnami/apache/htdocs

# Run the Laravel queue worker
php artisan queue:work --timeout=1200 >> /opt/bitnami/apache/htdocs/storage/logs/queue.log 2>&1
    