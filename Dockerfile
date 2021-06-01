FROM php:7.4-cli

MAINTAINER devgeniem

RUN apt-get update
RUN apt-get install -y python-pip
RUN pip install -U pip
RUN pip install pywatch

WORKDIR /opt

ENTRYPOINT ["pywatch"]