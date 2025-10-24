## Installation

1. Copy `.env.example` file and edit:

    ```shell
    cp .env.example .env   
    ```
   
2. Comment MoonShineServiceProvider in `bootstrap/providers.php`:

    ```php
    return [
        App\Providers\AppServiceProvider::class,
        //App\Providers\MoonShineServiceProvider::class,
    ];
    ```

3. Installation command

    Without docker:
    ```shell 
    make install-local
    ```
    With docker:

    ```shell 
    make install-docker
    ```

4. Uncomment MoonShineServiceProvider in `bootstrap/providers.php`

    ```php
    return [
        App\Providers\AppServiceProvider::class,
        App\Providers\MoonShineServiceProvider::class,
    ];
    ```
    
    http://127.0.0.1:8000/admin/login
    username: dev@getmoonshine.app
    password: 12345

5. Make MoonShine Great Again
