<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel 8 & Vue HN Scraper

A simple html scraper for HN top posts.

## Installation

### Requirements:

 - PHP 7.3 or higher
 - working `composer` installation
 - working `docker` and `docker compose` installation (optional, but highly recommended)

If you are on a system that supports `docker`, use Laravel's Sail that comes with default laravel 8 distribution. Sail is a pre-configured 
`docker` and `docker compose` setup optimized for ease of use.

### Run application locally

 - `composer install` in project directory
 - `cp .env.example .env`  copy the environment configuration example and modify as needed
 - `./vendor/bin/sail up` run project containers in docker (add `-d` flag to run containers in background)
 - `./vendor/bin/sail artisan migrate` initialize database schema

At this point application should be accessible in your `localhost` address on port 80.

To fetch the newest post data:

- `./vendor/bin/sail artisan fetch::posts` retrieves latest post data from HN using the configured data source.
     There are 2 available data sources, that can be chosen from in the .env configuration file under `HN_DATA_SOURCE`.
     `html` data source fetches the news.ycombinator.com page and scrapes the data from the html document itself, `api` source 
     uses HN's firebase API to retrieve top 30 posts. `api` source was implemented in case the HN front page html structure changes.
    This command is meant to be run on a repeated schedule, for example a cron-job that runs every x hours.

To view saved post data:

- Open the running application at `localhost` or wherever your application is running
- Create a new user using the `Register` button
- Log in with the newly created user
- You can now access the scraped data in the application homepage


### Application deployment

Since this is a quite standard Laravel 8, project, please refer to [Laravel's official documentation](https://laravel.com/docs/8.x/deployment) for various options
for deploying this application on a real-world environment 

### Automated testing

While using docker-based Laravel Sail, running tests is quite simple:

- `./vendor/bin/sail test` to run all defined tests.

Automated tests do not impact the 'live' database state, and do not call any external HN sources.

## Disclaimer

This is an educational side-project, does not represent HackerNews or Ycombinator in any capacity.

## Licensing

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

The Vue framework is open-sourced software licensed under the [MIT license](https://github.com/vuejs/vue/blob/main/LICENSE).

The DataTables Plugin is open-sourced software licenced under the [MIT license](https://datatables.net/license/)
