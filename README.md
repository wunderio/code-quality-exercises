DrupalCamp Baltics 2019 Static Analysis workshop
================================================

Exercises for the https://github.com/wunderio/code-quality tool.

## Installation

It's preferred to use Lando https://docs.lando.dev/basics/installation.html to
get get the environment up and running fast:
       
    git clone git@github.com:wunderio/code-quality-exercises.git
    lando start
    lando db-import config/init.sql
    lando drush uli
   
But if you have just Composer installed, then you can also try:
    
    git@github.com:wunderio/code-quality-exercises.git
    composer install