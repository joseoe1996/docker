FROM nginx:1.17.8-alpine

COPY build/nginx/nginx.conf  /etc/nginx/
COPY build/nginx/default.conf /etc/nginx/conf.d/
RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

EXPOSE 80
EXPOSE 443

