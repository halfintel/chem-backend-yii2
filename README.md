# Install with Docker

Run the installation

~~~
composer install
~~~

Start the container

~~~
composer first-start
~~~

Apply migrations

~~~
composer migrate-up
~~~

Check code

~~~
composer stan
~~~

Start tests

~~~
composer test
~~~

Check models coupling (change "$PATH_TO_APP")

~~~
docker run --rm -v $PATH_TO_APP\models:/inspect mihaeu/dephpend:latest text /inspect --exclude-regex='/yii|Yii|interface/'
~~~

~~~
docker run --rm -v $PATH_TO_APP\controllers:/inspect mihaeu/dephpend:latest text /inspect --exclude-regex='/yii|Yii|interface/'
~~~

List of urls

~~~
GET v1/ping
POST v1/registration
POST v1/login
POST v1/logout
POST v1/forgot-password
POST v1/change-password/<token>
~~~

# TODO:

add https  
add projects  
add cron (delete old tokens)  
add swagger  
add pre-commit  
add i18n