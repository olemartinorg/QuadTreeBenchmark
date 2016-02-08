FROM php:7-fpm

RUN apt-get update
RUN apt-get install -y git build-essential autoconf
RUN git clone https://github.com/php-ds/ds /php-ds && \
    cd /php-ds && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    echo 'extension=ds.so' > /usr/local/etc/php/conf.d/ds.ini

RUN git clone https://github.com/MarkBaker/QuadTrees.git /mb-quadtrees
