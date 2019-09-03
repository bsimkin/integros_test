FROM selenium/standalone-chrome-debug:latest
USER root
RUN apt-get update \
    && apt-get install -y software-properties-common curl \
    && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.2-cli php7.2-dev php7.2-mbstring php7.2-xml php7.2-curl php7.2-zip php7.2-bcmath php7.2-pgsql php7.2-mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/local/bin
RUN composer global require --optimize-autoloader \
        "hirak/prestissimo"
COPY ./composer.json /opt/codeception/composer.json
COPY ./composer.lock /opt/codeception/composer.lock
RUN cd /opt/codeception \
    && composer install --prefer-dist --optimize-autoloader

COPY ./codeception.yml /opt/codeception/codeception.yml
COPY ./tests /opt/codeception/tests
COPY run_tests.sh /opt/codeception
RUN chown -R seluser:seluser /opt/codeception

USER seluser
WORKDIR /opt/codeception

ENTRYPOINT /opt/bin/entry_point.sh
