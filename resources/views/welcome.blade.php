<x-moonshine::layout>
    <x-moonshine::layout.html :with-alpine-js="true" :with-themes="true">
        <x-moonshine::layout.head>
            <x-moonshine::layout.meta name="csrf-token" :content="csrf_token()"/>
            <x-moonshine::layout.meta name="viewport" content="width=device-width, initial-scale=1"/>
            <title>AI Автоматизация Бизнеса - Ускорьте свой успех</title>

            <!-- Fonts -->
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

            <x-moonshine::layout.favicon />
            <x-moonshine::layout.assets>
                @vite(['resources/js/app.js'], 'vendor/moonshine')
                @vite(['resources/css/app.css', 'resources/js/app.js'])
            </x-moonshine::layout.assets>
        </x-moonshine::layout.head>

        <x-moonshine::layout.body>
            <!-- Top Navigation Bar -->
            <x-moonshine::layout.top-bar>
                <x-moonshine::layout.logo
                    href="/"
                    :logo="asset('images/logo.svg')"
                    title="AI Business - Автоматизация бизнеса с помощью ИИ"
                >
                    🤖 AI Business
                </x-moonshine::layout.logo>

                <x-moonshine::layout.menu
                    :elements="[
                        ['label' => 'Главная', 'url' => '/'],
                        ['label' => 'Возможности', 'url' => '#features'],
                        ['label' => 'Преимущества', 'url' => '#benefits'],
                        ['label' => 'Демо', 'url' => '#demo']
                    ]"
                    :horizontal="true"
                />

                @if (Route::has('login'))
                    <x-moonshine::layout.div class="flex gap-4">
                        @auth
                            <x-moonshine::link-button href="{{ url('/dashboard') }}" class="btn-secondary">
                                Панель управления
                            </x-moonshine::link-button>
                        @else
                            <x-moonshine::link-button href="{{ route('login') }}" class="btn-outline">
                                Войти
                            </x-moonshine::link-button>
                            @if (Route::has('register'))
                                <x-moonshine::link-button href="{{ route('register') }}" class="btn-primary">
                                    Регистрация
                                </x-moonshine::link-button>
                            @endif
                        @endauth
                    </x-moonshine::layout.div>
                @endif
            </x-moonshine::layout.top-bar>

            <x-moonshine::layout.wrapper>
                <x-moonshine::layout.content id="main">
                    <!-- Hero Section -->
                    <x-moonshine::layout.div id="hero">
                        <x-moonshine::layout.grid>
                            <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                <x-moonshine::layout.box class="py-16">
                                    <x-moonshine::layout.div class="text-center px-6">
                                        <x-moonshine::badge color="primary" size="lg" class="mb-8">
                                            🚀 Будущее автоматизации уже здесь
                                        </x-moonshine::badge>

                                        <h1 class="text-4xl md:text-6xl font-bold mb-8">
                                            Ускорьте свой бизнес с помощью ИИ
                                        </h1>

                                        <p class="text-lg md:text-xl text-gray-600 mb-12 leading-relaxed">
                                            Автоматизируйте рутинные задачи, оптимизируйте процессы и увеличьте прибыль
                                            с помощью искусственного интеллекта нового поколения
                                        </p>

                                        <x-moonshine::layout.flex :justifyAlign="'center'" :itemsAlign="'center'" class="gap-4 mb-12">
                                            <x-moonshine::link-button href="#demo" class="btn-primary">
                                                <x-moonshine::icon icon="s.play"></x-moonshine::icon>
                                                Попробовать бесплатно
                                            </x-moonshine::link-button>
                                            <x-moonshine::link-button href="#features" class="btn-outline">
                                                Узнать больше
                                            </x-moonshine::link-button>
                                        </x-moonshine::layout.flex>

                                        <!-- Statistics -->
                                        <x-moonshine::layout.grid :gap="4">
                                            <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                                <x-moonshine::layout.div class="text-center p-4">
                                                    <div class="text-2xl font-bold text-blue-600 mb-1">300%</div>
                                                    <div class="text-sm text-gray-600">Рост эффективности</div>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.column>
                                            <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                                <x-moonshine::layout.div class="text-center p-4">
                                                    <div class="text-2xl font-bold text-purple-600 mb-1">24/7</div>
                                                    <div class="text-sm text-gray-600">Автоматическая работа</div>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.column>
                                            <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                                <x-moonshine::layout.div class="text-center p-4">
                                                    <div class="text-2xl font-bold text-green-600 mb-1">90%</div>
                                                    <div class="text-sm text-gray-600">Экономия времени</div>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.column>
                                        </x-moonshine::layout.grid>
                                    </x-moonshine::layout.div>
                                </x-moonshine::layout.box>
                            </x-moonshine::layout.column>
                        </x-moonshine::layout.grid>
                    </x-moonshine::layout.div>
                    <x-moonshine::layout.line-break></x-moonshine::layout.line-break>
                    <!-- Features Section -->
                    <x-moonshine::layout.div id="features">
                        <x-moonshine::layout.grid>
                            <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                <x-moonshine::layout.box title="Возможности ИИ автоматизации" class="py-12">
                                    <x-moonshine::layout.div class="text-center mb-8">
                                        <h2 class="text-3xl font-bold mb-4">Что может наш ИИ</h2>
                                        <p class="text-lg text-gray-600">Полный спектр решений для автоматизации вашего бизнеса</p>
                                    </x-moonshine::layout.div>

                                    <x-moonshine::layout.grid :gap="6">
                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'📄 Обработка документов'"
                                                :subtitle="'Автоматическое извлечение данных'"
                                                :values="['Скорость' => '10x быстрее', 'Точность' => '99.5%']"
                                            >
                                                <p class="text-gray-600">Автоматически обрабатывайте счета, договоры, заявки и другие документы с высокой точностью.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'📊 Аналитика и прогнозы'"
                                                :subtitle="'Умное планирование'"
                                                :values="['Точность' => '95%', 'Горизонт' => 'до 12 месяцев']"
                                            >
                                                <p class="text-gray-600">Получайте точные прогнозы продаж, анализ трендов и рекомендации для принятия решений.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'🤖 Чат-боты и поддержка'"
                                                :subtitle="'24/7 обслуживание клиентов'"
                                                :values="['Доступность' => '24/7', 'Языки' => '50+']"
                                            >
                                                <p class="text-gray-600">Умные чат-боты, которые решают 80% вопросов клиентов без участия человека.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'📦 Управление складом'"
                                                :subtitle="'Оптимизация запасов'"
                                                :values="['Экономия' => 'до 30%', 'Автоматизация' => '95%']"
                                            >
                                                <p class="text-gray-600">Автоматическое планирование закупок, оптимизация запасов и прогнозирование спроса.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'📢 Маркетинг и реклама'"
                                                :subtitle="'Персонализированные кампании'"
                                                :values="['Конверсия' => '+200%', 'ROI' => '+150%']"
                                            >
                                                <p class="text-gray-600">Создание персонализированного контента, A/B тестирование и оптимизация рекламных кампаний.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="4" :adaptiveColSpan="12">
                                            <x-moonshine::card
                                                :title="'💰 Финансовая аналитика'"
                                                :subtitle="'Умное планирование бюджета'"
                                                :values="['Точность' => '98%', 'Экономия' => 'до 25%']"
                                            >
                                                <p class="text-gray-600">Автоматический анализ расходов, планирование бюджета и выявление возможностей экономии.</p>
                                            </x-moonshine::card>
                                        </x-moonshine::layout.column>
                                    </x-moonshine::layout.grid>
                                </x-moonshine::layout.box>
                            </x-moonshine::layout.column>
                        </x-moonshine::layout.grid>
                    </x-moonshine::layout.div>
                    <x-moonshine::layout.line-break></x-moonshine::layout.line-break>
                    <!-- Benefits Section -->
                    <x-moonshine::layout.div id="benefits">
                        <x-moonshine::layout.grid>
                            <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                <x-moonshine::layout.box class="py-12">
                                    <x-moonshine::layout.div class="text-center mb-8">
                                        <h2 class="text-3xl font-bold mb-4">Преимущества нашего решения</h2>
                                        <p class="text-lg text-gray-600">Почему выбирают именно нас</p>
                                    </x-moonshine::layout.div>

                                    <x-moonshine::layout.grid :gap="8">
                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                            <x-moonshine::layout.flex :itemsAlign="'start'" class="gap-4 mb-6">
                                                <x-moonshine::badge color="success" size="lg">✓</x-moonshine::badge>
                                                <x-moonshine::layout.div>
                                                    <h3 class="text-xl font-semibold mb-2">Быстрое внедрение</h3>
                                                    <p class="text-gray-600">Настройка и запуск за 24 часа. Никаких сложных интеграций или длительного обучения персонала.</p>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.flex>

                                            <x-moonshine::layout.flex :itemsAlign="'start'" class="gap-4 mb-6">
                                                <x-moonshine::badge color="primary" size="lg">🔒</x-moonshine::badge>
                                                <x-moonshine::layout.div>
                                                    <h3 class="text-xl font-semibold mb-2">Безопасность данных</h3>
                                                    <p class="text-gray-600">Шифрование корпоративного уровня, соответствие GDPR и полное соблюдение конфиденциальности.</p>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.flex>

                                            <x-moonshine::layout.flex :itemsAlign="'start'" class="gap-4">
                                                <x-moonshine::badge color="warning" size="lg">💡</x-moonshine::badge>
                                                <x-moonshine::layout.div>
                                                    <h3 class="text-xl font-semibold mb-2">Непрерывное обучение</h3>
                                                    <p class="text-gray-600">ИИ постоянно адаптируется под ваши процессы, становясь эффективнее со временем.</p>
                                                </x-moonshine::layout.div>
                                            </x-moonshine::layout.flex>
                                        </x-moonshine::layout.column>

                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                            <x-moonshine::layout.box title="Результаты клиентов">
                                                <x-moonshine::progress-bar :value="75" :max="100" color="success" class="mb-4">
                                                    Сокращение времени на рутинные задачи: 75%
                                                </x-moonshine::progress-bar>

                                                <x-moonshine::progress-bar :value="85" :max="100" color="primary" class="mb-4">
                                                    Увеличение точности обработки данных: 85%
                                                </x-moonshine::progress-bar>

                                                <x-moonshine::progress-bar :value="60" :max="100" color="warning" class="mb-4">
                                                    Рост прибыли: 60%
                                                </x-moonshine::progress-bar>

                                                <x-moonshine::alert type="success" :removable="false" class="mt-4">
                                                    <strong>98% клиентов</strong> рекомендуют наше решение коллегам
                                                </x-moonshine::alert>
                                            </x-moonshine::layout.box>
                                        </x-moonshine::layout.column>
                                    </x-moonshine::layout.grid>
                                </x-moonshine::layout.box>
                            </x-moonshine::layout.column>
                        </x-moonshine::layout.grid>
                    </x-moonshine::layout.div>
                    <x-moonshine::layout.line-break></x-moonshine::layout.line-break>
                    <!-- Demo Section -->
                    <x-moonshine::layout.div id="demo">
                        <x-moonshine::layout.grid>
                            <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                <x-moonshine::layout.box class="py-12">
                                    <x-moonshine::layout.div class="text-center">
                                        <h2 class="text-3xl font-bold mb-6">Попробуйте бесплатно уже сегодня</h2>

                                        <x-moonshine::layout.grid>
                                            <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                                <x-moonshine::form name="demo-form" action="/demo" method="POST">
                                                    <x-moonshine::layout.grid :gap="4">
                                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                                            <x-moonshine::form.input
                                                                name="name"
                                                                placeholder="Ваше имя"
                                                                required
                                                            />
                                                        </x-moonshine::layout.column>

                                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                                            <x-moonshine::form.input
                                                                name="email"
                                                                type="email"
                                                                placeholder="Email"
                                                                required
                                                            />
                                                        </x-moonshine::layout.column>

                                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                                            <x-moonshine::form.input
                                                                name="company"
                                                                placeholder="Название компании"
                                                            />
                                                        </x-moonshine::layout.column>

                                                        <x-moonshine::layout.column :colSpan="6" :adaptiveColSpan="12">
                                                            <x-moonshine::form.input
                                                                name="phone"
                                                                type="tel"
                                                                placeholder="Телефон"
                                                            />
                                                        </x-moonshine::layout.column>

                                                        <x-moonshine::layout.column :colSpan="12" :adaptiveColSpan="12">
                                                            <x-moonshine::form.textarea
                                                                name="message"
                                                                placeholder="Расскажите о ваших задачах..."
                                                                rows="4"
                                                            ></x-moonshine::form.textarea>
                                                        </x-moonshine::layout.column>
                                                    </x-moonshine::layout.grid>

                                                    <x-slot:buttons>
                                                        <x-moonshine::form.button class="btn-primary btn-lg px-8 py-4 w-full">
                                                            <x-moonshine::icon icon="s.rocket-launch"></x-moonshine::icon>
                                                            Получить бесплатную консультацию
                                                        </x-moonshine::form.button>
                                                    </x-slot:buttons>
                                                </x-moonshine::form>

                                                <x-moonshine::alert type="info" :removable="false" :icon="false" class="mt-6">
                                                    Ваши данные защищены и не передаются третьим лицам
                                                </x-moonshine::alert>
                                            </x-moonshine::layout.column>
                                        </x-moonshine::layout.grid>
                                    </x-moonshine::layout.div>
                                </x-moonshine::layout.box>
                            </x-moonshine::layout.column>
                        </x-moonshine::layout.grid>
                    </x-moonshine::layout.div>
                    <x-moonshine::layout.line-break></x-moonshine::layout.line-break>
                    <!-- Footer -->
                    <x-moonshine::layout.div>
                        <x-moonshine::layout.divider>
                            <x-moonshine::layout.div class="text-center py-8">
                                <p class="text-gray-600">© 2024 AI Business. Революция автоматизации начинается здесь.</p>
                                <x-moonshine::layout.flex :justifyAlign="'center'" class="gap-6 mt-4">
                                    <x-moonshine::link-button href="/privacy" class="text-sm text-gray-500 hover:text-gray-700">
                                        Политика конфиденциальности
                                    </x-moonshine::link-button>
                                    <x-moonshine::link-button href="/terms" class="text-sm text-gray-500 hover:text-gray-700">
                                        Условия использования
                                    </x-moonshine::link-button>
                                    <x-moonshine::link-button href="/contact" class="text-sm text-gray-500 hover:text-gray-700">
                                        Контакты
                                    </x-moonshine::link-button>
                                </x-moonshine::layout.flex>
                            </x-moonshine::layout.div>
                        </x-moonshine::layout.divider>
                    </x-moonshine::layout.div>
                </x-moonshine::layout.content>
            </x-moonshine::layout.wrapper>
        </x-moonshine::layout.body>
    </x-moonshine::layout.html>
</x-moonshine::layout>
