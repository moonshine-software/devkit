## Installation

1. Comment MoonShineServiceProvider in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    //App\Providers\MoonShineServiceProvider::class,
];
```

2. Installation command

```shell 
make install
```

3. Uncomment MoonShineServiceProvider in `bootstrap/providers.php`

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MoonShineServiceProvider::class,
];
```
4. Make MoonShine Great Again
