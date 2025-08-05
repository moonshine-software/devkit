## Installation

1. Comment MoonShineServiceProvider in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    //App\Providers\MoonShineServiceProvider::class,
];
```

2. Installation command

Without docker:
```shell 
make install-local
```
With docker:
```shell 
make install-docker
```

3. Uncomment MoonShineServiceProvider in `bootstrap/providers.php`

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MoonShineServiceProvider::class,
];
```

http://127.0.0.1:8000/admin/login
username: dev@getmoonshine.app
password: 12345

4. Make MoonShine Great Again
