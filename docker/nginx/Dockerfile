FROM nginx:1.15.3-alpine
LABEL maintainer="Julien CROCHET <julien@crochet.me>"

RUN apk add --no-cache bash
RUN echo 'alias ll="ls -la"' >> ~/.bashrc

WORKDIR /usr/src

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./public /usr/src/api