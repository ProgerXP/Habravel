## Habravel

Страница проекта: https://laravel.ru/habravel

### Локальная установка в Workbench

Сначала создайте проект на Laravel с помощью Composer:
* Скачать [composer.phar](https://getcomposer.org/download/) ("Latest snapshot") в папку, где будет создана папка с проектом
* Выполнить: `php composer.phar create-project laravel/laravel laravel-ru` (займёт пару минут)
* Для удобства `composer.phar` можно переместить внутрь созданной папки проекта (`laravel-ru`)
* Убедиться, что среда правильно настроена и Laravel определяет вашу систему как **local** - см. [документацию](https://laravel.ru/docs/v4/configuration#%D1%81%D1%80%D0%B5%D0%B4%D0%B0)

Затем подключите Habravel:
* Создать папку `workbench/proger/habravel` в папке проекта и извлечь туда содержимое [хранилища с GitHub](https://github.com/ProgerXP/Habravel) (т.е. содержимое папки `Habravel-master`, если скачать его архивом). В итоге у вас должен быть файл по такому пути: `/.../your-site/workbench/proger/habravel/src/Habravel/ServiceProvider.php`
* Настроить БД и прочие вещи в `app/config` и применить миграцию Habravel: `php artisan migrate --bench=proger/habravel`
* Добавить поставщика услуг Habravel к проекту: открыть `app/config/app.php` и добавить `'Habravel\\ServiceProvider'` в массив **providers**
* Выполнить в папке `workbench/proger/habravel` команду `php ..\..\..\composer.phar update`
* Добавить в начало `bootstrap/autoload.php`:
```
function e($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'utf-8', true);
}
```

И настроить:
  1. Для форматирования сообщений нужно установить хотя бы один форматтер. Markdown можно добавить, выполнив в папке `workbench/proger/habravel` команду `php ..\..\..\composer.phar require michelf/php-markdown`, а затем раскомментировав строчку с **githubmarkdown** в настройках `habravel/src/config/g.php`
  2. Изначально пользователей нет. Можно зарегистрировать первого пользователя и дать ему полные права, вписав в поле `flags` таблицы `users` значение `+[admin][can.edit]`

#### Настройка public-ресурсов 

Ресурсы пакетов в Laravel 4 располагаются вне папки **public**, корневой для сайта (`DocumentRoot` в Apache). Поэтому один раз после подключения Habravel и затем каждый раз при изменении его ресурсов нужно выполнять эту команду в папке проекта:
```
php artisan asset:publish --bench=proger/habravel
```

Либо можно создать папку-ссылку `public/packages/proger/habravel`, указывающую на `workbench/proger/habravel/public`. В *nix это делается через `ln -s`, в Windows - через `fsutil`.
