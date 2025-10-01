# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm-alpine AS base

# keep the build shell clean
#SHELL ["/bin/sh", "-euxo", "pipefail", "-c"]

# Common dependencies
RUN apk add --no-cache \
  bash \
  curl \
  git \
  icu-dev \
  libzip-dev \
  zlib-dev \
  oniguruma-dev \
  libpng-dev \
  libjpeg-turbo-dev \
  freetype-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
  intl \
  pdo_mysql \
  bcmath \
  opcache \
  zip \
  gd \
  && apk del --no-network --purge .build-deps || true

WORKDIR /var/www/html

# Proper permissions
RUN mkdir -p var \
  && chown -R www-data:www-data /var/www/html

# Expose FPM port
EXPOSE 9000

# Basic healthcheck to ensure FPM config is valid
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php-fpm -t || exit 1

# Default command from base image (php-fpm)


