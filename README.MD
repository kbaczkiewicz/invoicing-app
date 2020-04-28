# Invoicing app

An app allowing issuing invoices, created entirely as a semester project for a university. It is also some kind of my
approach towards event sourcing (with simple ES mechanism created by me).

## Installation

Simply cd into _phpdocker_ folder and run

`docker-compose up -d`

Then just run

```docker exec invoices-php-fpm bash -c "cd /application/invoices; composer install; php bin/console doctrine:schema:update --force"``` 

And you should be ready to go - head on to _localhost:8080_ to enter app.

## Overview

At first, this project was meant to be split into separate domains, each with it's own API. That is why the most logic
lies underneath the _Domain_ namespace. However, as the time went by and I've lost interest in this project, I decided to
pack it all and wrap into a single controller app, making it a monolith MVP. However, it is still possible, with necessary 
refactoring and chunking it, to make this application domain-driven, with microservices and so on.

I gratuated a semester with this app, which was my main intention. Maybe someday I will refactor it.

###### Created by Kamil Bączkiewicz
