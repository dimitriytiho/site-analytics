# Site Analytics для Laravel и Filament

Агрегированные счётчики просмотров и событий с отчётом в Filament 5. В таблице статистики хранятся час UTC, код страницы, код события и количество. Отчёт показывает сутки по часам, неделю и 30 дней по дням. При открытии выбрана неделя.

Пакет рассчитан на PHP 8.4, Laravel 12, Filament 5 и PostgreSQL. Браузерный адаптер использует Inertia. Он отправляет коды страниц и событий. Подключение Яндекс Метрики, формы согласия, юридические документы и журналы веб-сервера настраивает приложение.

## Установка через Composer

После добавления репозитория `https://github.com/Omegakontur/site-analytics` в Packagist пакет устанавливается командой.

```sh
composer require omegakontur/site-analytics:^0.1
php artisan migrate
```

До подключения Packagist можно указать в приложении репозиторий типа `vcs` с этим адресом.

## Локальная разработка из исходников

Скопируйте пакет в `packages/site-analytics` своего приложения и добавьте в корневой `composer.json` репозиторий.

```json
{
  "repositories": [{
    "type": "path",
    "url": "packages/site-analytics",
    "options": {"versions": {"omegakontur/site-analytics": "0.1.0"}}
  }]
}
```

```sh
composer require omegakontur/site-analytics:^0.1
php artisan migrate
```

Laravel обнаруживает провайдер автоматически. При сборке Docker зависимости из path-репозитория должны быть скопированы до `composer install`.

## Настройка приложения

Создайте `config/site-analytics.php`.

```php
<?php

return [
    'pages' => [
        'home' => ['label' => 'Главная'],
        'tools' => ['label' => 'Инструменты'],
    ],
    'events' => [
        'page_view' => 'Просмотры',
        'tool_open' => 'Переходы к инструментам',
        'registration' => 'Регистрации',
    ],
    'browser_events' => ['tool_open' => ['home']],
    'highlights' => ['page_view', 'registration'],
];
```

События из браузера проверяются по белому списку. `page_view` разрешён для каждой настроенной страницы. Сервер может записывать события через `Counter::record()`.

```php
use Omegakontur\SiteAnalytics\Counter;

app(Counter::class)->record('home', 'registration');
```

Вызывайте запись после успешной операции. Если она выполняется внутри транзакции, используйте `DB::afterCommit()`. `record()` сообщает о сбое через обработчик исключений приложения и позволяет основной операции завершиться. Счётчики обновляются атомарно через upsert.

## Переводы

Интерфейс отчёта переведён на `ru`, `en`, `zh` и `ar`. Названия страниц и событий в конфигурации могут быть строками или ключами переводов приложения, например `analytics.pages.home`. При отображении они проходят через `__()`. Коды страниц и событий одинаковы для всех языков.

## Панель Filament

Зарегистрируйте плагин и задайте право в приложении.

```php
use Illuminate\Support\Facades\Gate;
use Omegakontur\SiteAnalytics\AnalyticsPlugin;

// Метод panel() провайдера Filament.
$panel->plugins([new AnalyticsPlugin()]);

// Метод boot() провайдера приложения. Замените проверку на свою модель прав.
Gate::define('view-site-analytics', fn ($user) => $user->can('viewAnalytics'));
```

Добавьте шаблоны пакета к источникам Tailwind темы панели.

```css
@source '../../../../vendor/omegakontur/site-analytics/resources/views/**/*';
```

Путь зависит от расположения файла темы. Соберите фронтенд и тему Filament. Страница отчёта находится в панели по адресу `site-analytics`.

## Просмотры и клики в Inertia

Импортируйте `resources/js/track.ts` пакета в браузерную точку входа. Сервер передаёт `analyticsPage` как постоянный prop Inertia. Значение — код текущей страницы из настроек или `null`.

```ts
import { router } from '@inertiajs/react';
import { trackSiteAnalytics } from '../../vendor/omegakontur/site-analytics/resources/js/track';

// В setup() createInertiaApp, в браузере.
trackSiteAnalytics(router, props.initialPage.props.analyticsPage);
```

Для учёта разрешённого клика добавьте `data-site-analytics="tool_open"` к элементу. Просмотры учитываются и при переходах Inertia, и на страницах из гостевого кэша.

`POST /site-analytics/event` принимает JSON с полями `page` и `event`, возвращает 204 и ограничен 120 запросами в минуту для одного адреса. Для ограничения используется HMAC от IP с ключом приложения. JSON-запросы с заголовком `Sec-Fetch-Site: cross-site` отклоняются. Статистика отражает полученные события и может включать автоматические обращения. Настройте правила прокси и срок хранения счётчиков под свой проект.

## Лицензия

MIT. Текст лицензии находится в файле LICENSE.
