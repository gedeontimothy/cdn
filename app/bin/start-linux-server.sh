@echo off

current_dir=$(basename "$PWD")

if [ "$current_dir" == "bin" ]; then
	cd ../..
fi

# Lancer la commande 'php artisan queue:work' en arrière-plan avec un titre "Queue Worker"
nohup ./app/bin/queue-worker.sh > /dev/null 2>&1 &

# Lancer la commande 'php artisan serve' en arrière-plan avec un titre "PHP Server"
nohup ./app/bin/server.sh > /dev/null 2>&1 &

exit
