# GH Archive View

## What is GH Archive project ?
GH Archive is a project to record the public GitHub timeline, archive it, and make it easily accessible for further analysis.
To find out more https://www.gharchive.org.

## What is this project ?
This project have to aim to give you tools to extract data from GH Archive.
- Command line, to extract the data and save it in a database
- An api rest, to display the data extracted (work in progress...)
- A web interface (maybe, I will see)

## How it's build ?
It's a PHP (https://www.php.net) project with Symfony framework (https://symfony.com).
it use Mysql database to store the data. Redis to stream message to queue messages.
Phpunit, for functionnal and unit tests.

## How to use it ?
For local use, you can use this docker https://github.com/sgiberne/docker-php.git.
...

## Design pattern
This project uses:
- ADR pattern

## Documentation
- https://github.com/pmjones/adr


## Todo list
- Functionnal and unit tests
- Api rest
- web interface
