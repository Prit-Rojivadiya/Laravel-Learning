# Running Locally

## First Use

Run this command after cloning (assuming you have docker installed)

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs
```

Do some setup
```bash
./vendor/bin/sail artisan jwt:secret
```

Run client:
```bash
cd nuxt-client
npm install
npm run dev
```

Access front-end at http://localhost:3000/
API runs at http://localhost:8000

Default login is: developer@local.com / secret

## Subsequent Runs

```bash
./vendor/bin/sail up -d
```

## Running Commands Locally

```bash
./vendor/bin/sail artisan make:migration <name>
```

## Further Reading

https://laravel.com/docs/8.x/sail
