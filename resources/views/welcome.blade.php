<x-moonshine::layout>
    <x-moonshine::layout.html :with-alpine-js="true" :with-themes="true">
        <x-moonshine::layout.head>
            <x-moonshine::layout.meta name="csrf-token" :content="csrf_token()"/>
            <x-moonshine::layout.favicon />
            <x-moonshine::layout.assets>
                @vite(['resources/css/main.css', 'resources/js/app.js'], 'vendor/moonshine')
            </x-moonshine::layout.assets>
        </x-moonshine::layout.head>

        <x-moonshine::layout.body>
            <x-moonshine::layout.wrapper>
                {{-- Сайдбар с меню --}}
                <x-moonshine::layout.sidebar :collapsed="true">
                    <x-moonshine::layout.div class="menu-header">
                        <x-moonshine::layout.div class="menu-logo">
                            <x-moonshine::layout.logo
                                href="/"
                                logo="/vendor/moonshine/logo-small.svg"
                                :minimized="true"
                            />
                        </x-moonshine::layout.div>

                        <x-moonshine::layout.div class="menu-actions">
                            <x-moonshine::layout.theme-switcher/>
                        </x-moonshine::layout.div>

                        <x-moonshine::layout.div class="menu-burger">
                            <x-moonshine::layout.burger sidebar />
                        </x-moonshine::layout.div>
                    </x-moonshine::layout.div>

                    <x-moonshine::layout.div class="menu menu--vertical">
                        <x-moonshine::layout.menu :elements="[
                            ['label' => 'Пользователи', 'url' => '/', 'icon' => 'users'],
                            ['label' => 'Статьи', 'url' => '/articles', 'icon' => 'document-text'],
                            ['label' => 'Категории', 'url' => '/categories', 'icon' => 'tag']
                        ]"/>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.sidebar>

                {{-- Основной контент --}}
                <x-moonshine::layout.div class="layout-main">
                    <x-moonshine::layout.div class="layout-page">
                        <x-moonshine::layout.header>
                            <x-moonshine::layout.div class="menu-burger">
                                <x-moonshine::layout.burger sidebar />
                            </x-moonshine::layout.div>
                            <x-moonshine::breadcrumbs :items="['/' => 'Главная', '#' => 'Пользователи']"/>
                        </x-moonshine::layout.header>

                        <x-moonshine::layout.content>
                            {{-- Заголовок и кнопки действий --}}
                            <x-moonshine::layout.flex :justifyAlign="'between'" :itemsAlign="'center'">
                                <x-moonshine::heading h="1">
                                    Пользователи
                                </x-moonshine::heading>

                                <x-moonshine::layout.flex :justifyAlign="'end'" :withoutSpace="false">
                                    {{-- Кнопка фильтров (открывает offcanvas) --}}
                                    <x-moonshine::off-canvas title="Фильтры">
                                        <x-slot:toggler>
                                            <x-moonshine::icon icon="funnel" />
                                            Фильтры
                                        </x-slot:toggler>

                                        <div class="space-y-4">
                                            <x-moonshine::form.input
                                                name="filter_name"
                                                placeholder="Имя пользователя"
                                            />
                                            <x-moonshine::form.input
                                                name="filter_email"
                                                type="email"
                                                placeholder="Email"
                                            />
                                            <x-moonshine::form.input
                                                name="filter_role"
                                                placeholder="Роль"
                                            />
                                            <x-moonshine::layout.divider />
                                            <x-moonshine::layout.flex :justifyAlign="'between'">
                                                <button class="btn btn-secondary">Сбросить</button>
                                                <button class="btn btn-primary">Применить</button>
                                            </x-moonshine::layout.flex>
                                        </div>
                                    </x-moonshine::off-canvas>

                                    {{-- Кнопка добавления (открывает модальное окно) --}}
                                    <x-moonshine::modal title="Добавить пользователя">
                                        <x-moonshine::form name="add-user-form" action="/users" method="POST">
                                            <div class="space-y-4">
                                                <x-moonshine::form.input
                                                    name="name"
                                                    placeholder="Имя пользователя"
                                                    required
                                                />
                                                <x-moonshine::form.input
                                                    name="email"
                                                    type="email"
                                                    placeholder="Email"
                                                    required
                                                />
                                                <x-moonshine::form.input
                                                    name="password"
                                                    type="password"
                                                    placeholder="Пароль"
                                                    required
                                                />
                                                <x-moonshine::form.input
                                                    name="role"
                                                    placeholder="Роль"
                                                />
                                            </div>

                                            <x-slot:buttons>
                                                <x-moonshine::form.button type="reset">Отмена</x-moonshine::form.button>
                                                <x-moonshine::form.button class="btn-primary">Добавить</x-moonshine::form.button>
                                            </x-slot:buttons>
                                        </x-moonshine::form>

                                        <x-slot name="outerHtml">
                                            <button class="btn btn-primary" @click.prevent="toggleModal">
                                                <x-moonshine::icon icon="plus" />
                                                Добавить
                                            </button>
                                        </x-slot>
                                    </x-moonshine::modal>
                                </x-moonshine::layout.flex>
                            </x-moonshine::layout.flex>

                            {{-- Таблица пользователей --}}
                            <x-moonshine::layout.box>
                                <x-moonshine::table>
                                    <x-slot:thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </x-slot:thead>

                                    <x-slot:tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Ivan Ivanov</td>
                                            <td>ivan@gmail.com</td>
                                            <td>Editor</td>
                                            <td>01.01.2025</td>
                                            <td>
                                                <x-moonshine::badge color="success">Active</x-moonshine::badge>
                                            </td>
                                            <td>
                                                <x-moonshine::layout.flex justify-align="end" without-space class="gap-2">
                                                    <x-moonshine::link-button href="/" class="btn-square">
                                                        <x-moonshine::icon icon="eye"></x-moonshine::icon>
                                                    </x-moonshine::link-button>

                                                    <x-moonshine::link-button href="/" class="btn-square btn-secondary">
                                                        <x-moonshine::icon icon="pencil"></x-moonshine::icon>
                                                    </x-moonshine::link-button>

                                                    <x-moonshine::link-button href="/" class="btn-square btn-error">
                                                        <x-moonshine::icon icon="trash"></x-moonshine::icon>
                                                    </x-moonshine::link-button>
                                                </x-moonshine::layout.flex>

                                            </td>
                                        </tr>
                                    </x-slot:tbody>
                                    <x-slot:tfoot></x-slot:tfoot>
                                </x-moonshien::table>
                            </x-moonshine::layout.box>

                            {{-- Пагинация --}}
                            <x-moonshine::layout.flex :justifyAlign="'center'">
                                <div class="pagination">
                                    <button class="btn btn-sm btn-secondary">Предыдущая</button>
                                    <button class="btn btn-sm btn-primary">1</button>
                                    <button class="btn btn-sm">2</button>
                                    <button class="btn btn-sm">3</button>
                                    <button class="btn btn-sm btn-secondary">Следующая</button>
                                </div>
                            </x-moonshine::layout.flex>
                        </x-moonshine::layout.content>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.div>
            </x-moonshine::layout.wrapper>
        </x-moonshine::layout.body>
    </x-moonshine::layout.html>
</x-moonshine::layout>
