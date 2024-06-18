Программа через очередь построчно грузит excel файл в базу. Для хранения информации используется Redis. Процент загрузки передается через сокет на Vue.js.
В панели управления предусмотрена авторизация.

<img src="screenshots/screen1.png">
<img src="screenshots/screen2.png">
<img src="screenshots/screen3.png">

Как поднять на linux:
Клонировать репозиторий, в папке прописать <pre>make install</pre>

<img src="screenshots/screen5.png">

Войти в панель можно с этим доступом:
<img src="screenshots/screen4.png">

Нажать кнопку "Запустить импорт". Далее можно либо вручную запускать очередь в отдельном окне:
<pre>docker exec excel-jobs-app bash -c "php artisan schedule:run"</pre>
Либо прописать через
<pre>sudo crontab -e</pre>
<pre>* * * * * docker exec excel-jobs-app bash -c "php artisan schedule:run"</pre>