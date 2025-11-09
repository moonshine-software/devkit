<x-moonshine::layout>
    <x-moonshine::layout.html :with-alpine-js="true" :with-themes="true">
        <x-moonshine::layout.head>
            <x-moonshine::layout.meta name="csrf-token" :content="csrf_token()"/>
            <x-moonshine::layout.favicon />
            <x-moonshine::layout.assets>
                @vite([
                    'resources/css/main.css',
                    'resources/js/app.js',
                ], 'vendor/moonshine')
            </x-moonshine::layout.assets>
        </x-moonshine::layout.head>
        <x-moonshine::layout.body>
            <x-moonshine::layout.wrapper>

                <x-moonshine::layout.mobile-bar>
                    <x-moonshine::layout.div class="menu-logo">
                        <x-moonshine::layout.logo
                            href="/"
                            logo="/vendor/moonshine/logo-app.svg"
                            logo-small="/vendor/moonshine/logo-app.svg"
                            :minimized="true"
                        />
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu menu--horizontal">
                        <x-moonshine::layout.divider label="Mobile bar" />

                        <x-moonshine::layout.menu
                            :top="true"
                            :elements="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Section', 'url' => '/section']]"
                        />
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu-actions">
                        <div class="menu-divider menu-divider--vertical"></div>
                        <x-moonshine::layout.theme-switcher/>
                        <x-moonshine::layout.div class="menu-burger">
                            <x-moonshine::layout.burger mobile-bar />
                        </x-moonshine::layout.div>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.mobile-bar>


                <x-moonshine::layout.top-bar>
                    <x-moonshine::layout.div class="menu-logo">
                        <x-moonshine::layout.logo
                            href="/"
                            logo="/vendor/moonshine/logo-app.svg"
                            logo-small="/vendor/moonshine/logo-app.svg"
                            :minimized="true"
                        />
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu menu--horizontal">
                        <x-moonshine::layout.menu
                            :top="true"
                            :elements="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Section', 'url' => '/section']]"
                        />
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu-actions">
                        <div class="menu-divider menu-divider--vertical"></div>
                        <x-moonshine::layout.theme-switcher/>
                        <x-moonshine::layout.div class="menu-burger">
                            <x-moonshine::layout.burger topbar />
                        </x-moonshine::layout.div>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.top-bar>

                <x-moonshine::layout.sidebar :collapsed="true">
                    <x-moonshine::layout.div class="menu-header">
                        <x-moonshine::layout.div class="menu-logo">
                            <x-moonshine::layout.logo href="/" logo="/vendor/moonshine/logo-app.svg" logo-small="/vendor/moonshine/logo-app.svgg" :minimized="true"/>
                        </x-moonshine::layout.div>

                        <x-moonshine::layout.div class="menu-actions">
                            <x-moonshine::layout.theme-switcher/>
                        </x-moonshine::layout.div>

                        <x-moonshine::layout.div class="menu-burger">
                            <x-moonshine::layout.burger sidebar />
                        </x-moonshine::layout.div>
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu menu--vertical">
                        <x-moonshine::layout.menu :elements="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Section', 'url' => '/section']]"/>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.sidebar>



                <x-moonshine::layout.div class="layout-main <!--layout-main-centered-->">
                    <x-moonshine::layout.div class="layout-page <!--layout-page-simple-->">
                        <x-moonshine::layout.header>
                            <x-moonshine::layout.div class="menu-burger">
                                <x-moonshine::layout.burger/>
                            </x-moonshine::layout.div>
                            <x-moonshine::breadcrumbs :items="['#' => 'Home']"/>
                            <x-moonshine::layout.search placeholder="Search" />
                            <x-moonshine::layout.locales :locales="collect()"/>
                        </x-moonshine::layout.header>
                        <x-moonshine::layout.content>
                            <article class="article">
                                Your content
                            </article>
                        </x-moonshine::layout.content>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.div>

            </x-moonshine::layout.wrapper>
        </x-moonshine::layout.body>
    </x-moonshine::layout.html>
</x-moonshine::layout>
