# Virtual file system

The app is based on PHP 7. It does file system operations and is able to upload files to FTP server. Launch the app on CLI by executing `app.php` and providing arguments. The command below will give you using instructions:
```
php app.php
```

## Configuration
The `properties.conf` file contains path of the virtual file system root folder and FTP credentials.

## Tests
Install PHPUnit framework:
```
composer install
```
Execute unit tests with command:
```
./vendor/bin/phpunit
```
## Notes
If you notice issue with file reading, adjust `NEW_LINE_DELIMITER` in `app/Repository/FileReader.php`, it is recognizing end-of-lines by `\r\n`. Or set EOL type in your input files accordingly.