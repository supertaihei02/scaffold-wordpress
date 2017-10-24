FROM wordpress:php7.1-fpm-alpine
WORKDIR /tmp

ADD ./repositories /root/repositories
RUN cp -f /root/repositories /etc/apk/repositories

RUN apk update && \
    apk --no-cache upgrade && \
    apk --no-cache add bash openssh make gcc g++ ruby ruby-rdoc ruby-irb ruby-dev && \ 
    gem update --system && \
    gem install bigdecimal wordmove

WORKDIR /var/www/html
