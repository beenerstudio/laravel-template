#sistem informasi manajamen air komplek

container_name=laravel
local_volume=
docker stop $container_name
docker rm $container_name
docker run --name $container_name \
    -e MYSQL_ROOT_PASSWORD=test \
    -e MYSQL_DATABASE=laravel \
    -p 3306:3306 \
    -v $local_volume:/var/lib/mysql \
    -d mysql:5.7.34
