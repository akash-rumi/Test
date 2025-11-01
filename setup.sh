# Stop and remove existing Docker containers

docker stop $(docker ps -a -q)
docker container prune -f

# Start Docker Compose
cd docker
docker-compose -p srtsch up -d
cd ..

# Wait for the containers to fully start (optional, but helps ensure services are ready)
echo "Waiting for services to start..."
sleep 10

# Laravel application setup
docker exec php cp .env.example .env
docker exec php composer install
docker exec php php artisan key:generate
docker exec php php artisan migrate:fresh --seed

# Adjust file permissions for storage (ensure Laravel can write to storage)
sudo chmod -R 777 src

echo "\nAll initialization and setup done\n"