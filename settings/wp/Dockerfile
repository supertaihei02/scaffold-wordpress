FROM wordpress:php7.1-fpm-alpine
WORKDIR /tmp

# Composer
ADD ./composer.json /tmp/composer.json
ADD ./composer.lock /tmp/composer.lock
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer install --no-scripts --no-autoloader -d /tmp

# update repositories
ADD ./repositories /root/repositories
RUN cp -f /root/repositories /etc/apk/repositories

# WordMove
RUN apk update && \
    apk --no-cache upgrade && \
    apk --no-cache add bash openssh make gcc g++ ruby ruby-rdoc ruby-irb ruby-dev && \ 
    gem update --system && \
    gem install bigdecimal wordmove

WORKDIR /var/www/html
