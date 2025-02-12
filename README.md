### Page Analyzer

## Free web service for analyzes web pages for SEO suitabilities

## Deployed on Render.com
https://php-project-9-efdl.onrender.com

### Hexlet tests and linter status:
[![Actions Status](https://github.com/vsev92/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/vsev92/php-project-9/actions)

[![linter](https://github.com/vsev92/php-project-9/actions/workflows/linter.yml/badge.svg)](https://github.com/vsev92/php-project-9/actions/workflows/linter.yml)

[![Maintainability](https://api.codeclimate.com/v1/badges/47515ca90f78cd4200ac/maintainability)](https://codeclimate.com/github/vsev92/php-project-9/maintainability)

## Prerequisites

* Linux
* PHP >=8.1
* Composer
* Make
* PostgeSQL

## Setup project
```bash
git clone git@github.com:vsev92/php-project-9.git
cd  php-project-9
make install
```
## Setup database
1. create empty PostgreSql database
2. Copy .env file with variable 'DATABASE_URL' from .env.example
## 
```bash
cp .env.example .env
```
3. modify variable 'DATABASE_URL' in .env  file to actual URL for connect to created at step 1 database 

## Run web server
```bash
make start
```
then web service is avaible on 0.0.0.0:8000


