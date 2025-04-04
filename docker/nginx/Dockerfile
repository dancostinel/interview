FROM nginx:1.27.4
RUN apt-get update && apt-get install apt-file -y && apt-file update && apt-get install vim -y
COPY ./nginx/default.conf /etc/nginx/conf.d/

