## Installation
```
$ composer install && npm install
```

Open ```.env``` and enter necessary config for DB.

```
$ php artisan migrate
$ php artisan db:seed
```

## Work Flow

**General Workflow**

```
$ php artisan serve --host=0
```
Open new terminal
```
$ gulp && gulp watch
```

> Default Username/Password: admin@example.com / password


**FAQ**

In case of Node Sass error during gulp compilation try this command:
```
$ npm rebuild node-sass
```