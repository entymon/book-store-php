### Setup project

1. Run command: `docker-compose exec server bash` to get in to the server. Be sure is running. 
2. The project should be visable on `var/www/html` sharing directory. to check it run `ls -al` ans you should be able to see the list of directories
3. Run `composer install`
4. If `.env` file will be overriden please run `echo $DATABASE_URL` and the outcome string replace in `.env`