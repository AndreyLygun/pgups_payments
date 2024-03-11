FROM alpine:3.16.2

RUN apk update && apk upgrade

RUN apk add --no-cache \
        bash \
        php8 \
        php8-fpm \
        php8-opcache \
        php8-gd \
        php8-zlib \
        php8-curl \
        php8-bcmath \
        php8-ctype \
        php8-iconv \
        php8-intl \
        php8-json \
        php8-mbstring \
        php8-mysqlnd \
        php8-openssl \
        php8-pdo \
        php8-pdo_mysql \
        php8-pdo_pgsql \
        php8-pdo_sqlite \
        php8-phar \
        php8-posix \
        php8-session \
        php8-soap \
        php8-xml \
        php8-zip \
        libmcrypt-dev \
        libltdl

RUN apk add openssl curl ca-certificates

RUN printf "%s%s%s\n" \
    "http://nginx.org/packages/alpine/v" \
    `egrep -o '^[0-9]+\.[0-9]+' /etc/alpine-release` \
    "/main" \
    | tee -a /etc/apk/repositories

RUN curl -o /tmp/nginx_signing.rsa.pub https://nginx.org/keys/nginx_signing.rsa.pub

RUN openssl rsa -pubin -in /tmp/nginx_signing.rsa.pub -text -noout

RUN mv /tmp/nginx_signing.rsa.pub /etc/apk/keys/

RUN apk add nginx

RUN echo "\
    server {\n\
        listen 80;\n\
        listen [::]:80;\n\
        root /var/www/html/public;\n\
        add_header X-Frame-Options \"SAMEORIGIN\";\n\
        add_header X-Content-Type-Options \"nosniff\";\n\
        index index.php;\n\
        charset utf-8;\n\
        location / {\n\
            try_files \$uri \$uri/ /index.php?\$query_string;\n\
        }\n\
        location = /favicon.ico { access_log off; log_not_found off; }\n\
        location = /robots.txt  { access_log off; log_not_found off; }\n\
        error_page 404 /index.php;\n\
        location ~ \.php$ {\n\
            fastcgi_pass unix:/run/php/php8.2-fpm.sock;\n\
            fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;\n\
            include fastcgi_params;\n\
        }\n\
        location ~ /\.(?!well-known).* {\n\
            deny all;\n\
        }\n\
    }\n" > /etc/nginx/sites-available/default

RUN echo "\
    #!/bin/sh\n\
    echo \"Starting services...\"\n\
    service php8.2-fpm start\n\
    nginx -g \"daemon off;\" &\n\
    echo \"Ready.\"\n\
    tail -s 1 /var/log/nginx/*.log -f\n\
    " > /start.sh

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /usr/share/nginx/html/project

COPY ./nginx-configs/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
EXPOSE 443

STOPSIGNAL SIGTERM

CMD ["/bin/bash", "-c", "php-fpm8 && chmod 755 /usr/share/nginx/html/* && nginx -g 'daemon off;'"]
