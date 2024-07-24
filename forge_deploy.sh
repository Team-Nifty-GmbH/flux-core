# Change to the site directory
cd $FORGE_SITE_PATH

# Put the application into maintenance mode
$FORGE_PHP artisan down

# Pull the latest code from the repository
git pull origin $FORGE_SITE_BRANCH

# Install/update Composer dependencies
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Check if cachetool is installed
if ! command -v cachetool &> /dev/null
then
    (
        flock -w 10 9 || exit 1
        echo 'Restarting FPM...'
        sudo -S service $FORGE_PHP_FPM reload
    ) 9>/tmp/fpmlock
else
    # Reset OPcache
    /usr/local/bin/cachetool opcache:reset
fi

# Clear application cache
$FORGE_PHP artisan cache:clear

# Run database migrations
$FORGE_PHP artisan migrate --force

# Set the correct permissions
$FORGE_PHP artisan init:permissions

# Optimize the framework for better performance
$FORGE_PHP artisan optimize

# Synchronize index settings for Laravel Scout
$FORGE_PHP artisan scout:sync-index-settings

# Restart Reverb service (if applicable)
$FORGE_PHP artisan reverb:restart

# Restart the queue worker to apply any changes
$FORGE_PHP artisan queue:restart

# Bring the application out of maintenance mode
$FORGE_PHP artisan up
