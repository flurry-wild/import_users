install:
	docker-compose build
	docker-compose up -d
	sudo chmod -R 777 ./project/storage ./project/bootstrap
	sudo chown -R ${USER} ./project
	cp project/.env.example project/.env
	docker exec excel-jobs-app bash -c "composer install && php artisan key:generate"
	docker exec excel-jobs-app bash -c "php artisan migrate --seed"
	docker exec excel-jobs-app bash -c "npm install && npm run build"
	docker exec excel-jobs-app bash -c "npm run dev"