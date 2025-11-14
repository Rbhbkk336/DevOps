#Выбрал базовый образ Ubuntu
FROM nginx:latest

#Указал себя как создателя образа
MAINTAINER Pesterev_KM

#Установка Nginx и Postgres
RUN apt-get update &&\
    apt-get install -y --no-install-recommends postgresql &&\
    rm -rf /var/lib/apt/lists/*

#Указание рабочей директории
WORKDIR /usr/share/nginx/html

#Переменные окружения для postgres
ENV POSTGRES_USER=user
ENV POSTGRES_PASSWORD=password
ENV POSTGRES_DB=mydb

#Копирование конф. файлов nginx в контейнер
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

#Копирование src
COPY ./src/ .

#Добавление дополнительных файлов
ADD additional_files /usr/share/nginx/html/additional_files

#Указание точки монтирования
VOLUME ["/usr/share/nginx/html/data"]

#Открытие порта
EXPOSE 80

#Запуск контейнера от "дефолтного" пользователя веб-сервера
USER nginx

#Запуск
CMD ["nginx", "-g", "daemon off;"]
