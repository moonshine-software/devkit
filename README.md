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
4. Make MoonShine Great Again
