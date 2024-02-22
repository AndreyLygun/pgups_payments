FROM nginx:latest

# Устанавливаем PHP и дополнительные пакеты
RUN apt-get update && apt-get install -y php-fpm php-mysql

# Копируем конфигурацию PHP-FPM
COPY php-fpm.conf /etc/php/7.4/fpm/php-fpm.conf

# Копируем конфигурацию Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Копируем исходный код из репозитория на GitHub
RUN apt-get install -y git
RUN git clone https://github.com/your-github-repo.git /usr/share/nginx/html/

# Открываем порт 80 для веб-трафика
EXPOSE 80

# Запускаем Nginx и PHP-FPM
CMD service php7.4-fpm start && nginx -g 'daemon off;'

