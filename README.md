# Formula Test Project

Test project which pulls data from http://ergast.com/mrd/ API and shows the data in Vue table.

## Getting Started

In order to set up the test app, you should first do:

```
php artisan migrate
```

...which will create the necessary db table. Don't forget to create the .env file!

Command for seeding the data is:

```
php artisan races:seed {years}
```

It is flexible so you can put some other number not only 20:

```
php artisan races:seed 20
```

Command for updating the races with new data is:

```
php artisan races:update
```

It is also set in the scheduler to be called each day, but you have to set up a cronjob on your server in order to do that.

To do a quick test of the update command, you can uncomment:

```
//$result = Storage::get('results.json');
```

in the UpdateRaces command file. It will now pull from the local file which is the same as the json returned from the server but it has newer data.