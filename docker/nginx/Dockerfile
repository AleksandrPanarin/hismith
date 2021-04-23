FROM nginx

COPY ./docker/nginx/nginx.conf /etc/nginx/
COPY ./docker/nginx/conf.d/default.conf /etc/nginx/conf.d/

RUN echo "upstream php-upstream { server php7.4:9000; }" > /etc/nginx/conf.d/upstream.conf

CMD ["nginx"]

EXPOSE 80