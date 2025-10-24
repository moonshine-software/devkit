## Installation


1. Copy `.env.example` file:

    ```shell
    cp .env.example .env   
    ```

2. Comment `MoonShineServiceProvider` in `bootstrap/providers.php`:

    ```php
    return [
        App\Providers\AppServiceProvider::class,
        //App\Providers\MoonShineServiceProvider::class,
    ];
    ```

3. Installation command:

    Without docker:
    ```shell 
    make install-local
    ```
    With docker:
    ```shell 
    make install-docker
    ```

4. Uncomment `MoonShineServiceProvider` in `bootstrap/providers.php`:

    ```php
    return [
        App\Providers\AppServiceProvider::class,
        App\Providers\MoonShineServiceProvider::class,
    ];
    ```

5. Make MoonShine Great Again!
